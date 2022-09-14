<?php

declare(strict_types=1);

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
        return [
            ['event' => Events::PRE_SERIALIZE, 'method' => 'onPreSerialize', 'priority' => -1000],
            ['event' => Events::POST_SERIALIZE, 'method' => 'onPostSerialize', 'priority' => 1000],
        ];
    }

    /**
     * A stopwatch object which exposes a start($name) and a stop($name) method.
     *
     * @var object
     */
    private $stopwatch;

    /**
     * @var string
     */
    private $name;

    /**
     * @var object
     */
    private $rootObject;

    public function __construct($stopwatch, $name = 'jms_serializer')
    {
        $this->stopwatch = $stopwatch;
        $this->name = $name;
    }

    public function onPreSerialize(ObjectEvent $event)
    {
        if ($event->getContext()->getDepth() > 1 || null !== $this->rootObject) {
            return;
        }

        $this->stopwatch->start($this->name);
        $this->rootObject = $event->getObject();
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        if (null === $this->rootObject || $event->getObject() !== $this->rootObject) {
            return;
        }

        $this->stopwatch->stop($this->name);
        $this->rootObject = null;
    }
}
