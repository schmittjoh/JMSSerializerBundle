<?php

namespace JMS\SerializerBundle\Serializer;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class StopwatchEventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            array('event' => Events::PRE_SERIALIZE, 'method' => 'onPreSerialize', 'priority' => -1000),
            array('event' => Events::POST_SERIALIZE, 'method' => 'onPostSerialize', 'priority' => 1000),
        );
    }

    /**
     * A stopwatch object which exposes a start($name) and a stop($name) method.
     * 
     * @var object
     */
    private $stopwatch;

    /**
     * @var object
     */
    private $rootObject;

    public function __construct($stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    public function onPreSerialize(ObjectEvent $event)
    {
        if ($event->getContext()->getDepth() > 1 || null !== $this->rootObject) {
            return;
        }

        $this->stopwatch->start('jms_serializer');
        $this->rootObject = $event->getObject();
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        if (null === $this->rootObject || $event->getObject() !== $this->rootObject) {
            return;
        }

        $this->stopwatch->stop('jms_serializer');
        $this->rootObject = null;
    }
}
