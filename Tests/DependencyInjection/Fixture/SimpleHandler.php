<?php

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;

class SimpleHandler implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ['event' => 'the-event-name', 'method' => 'onEventName', 'class' => 'some-class', 'format' => 'json'],
        ];
    }

    public function onEventName()
    {

    }
}
