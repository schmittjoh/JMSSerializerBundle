<?php
 
namespace JMS\SerializerBundle\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use ReflectionProperty;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class ConstraintViolationListSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            array('event' => Events::PRE_SERIALIZE, 'method' => 'onPreSerialize', 'priority' => 512),
        );
    }

    /**
     * Converts a constraint violation list to an array
     *
     * @param PreSerializeEvent $event
     */
    public function onPreSerialize(PreSerializeEvent $event)
    {
        if (!class_exists('Symfony\\Component\\Validator\\ConstraintViolationListInterface')) {
            return;
        }

        if (null === $object = $this->getRootObjectByGraph($event->getContext())) {
            return;
        }

        if (!$object instanceof ConstraintViolationListInterface) {
            return;
        }

        $newObject = array();

        foreach ($object as $constraintViolation) {
            if (!$constraintViolation instanceof ConstraintViolationInterface) {
                throw new \LogicException(
                    sprintf(
                        'Every violation must be an instance of "%s"',
                        'Symfony\\Component\\Validator\\ConstraintViolationInterface'
                    )
                );
            }

            $property = $constraintViolation->getPropertyPath();

            if (!isset($newObject[$property])) {
                $newObject[$property] = array();
            }

            $newObject[$property][] = $constraintViolation->getMessage();
        }

        // the object of the preserialize event should be changeable
        // this is just workaround
        // I'll open a pull request on jms/serializer in order to fix this, but
        // until that, this workaround should help
        $reflection = new ReflectionProperty('JMS\\Serializer\\EventDispatcher\\PreSerializeEvent', 'object');

        $reflection->setAccessible(true);
        $reflection->setValue($event, $newObject);
        $reflection->setAccessible(false);
    }

    /**
     * Returns the root object or null
     *
     * @param Context $context
     *
     * @return mixed
     */
    private function getRootObjectByGraph(Context $context)
    {
        if ($context->getDepth() > 1) {
            return;
        }

        return $context->getObject();
    }
}
