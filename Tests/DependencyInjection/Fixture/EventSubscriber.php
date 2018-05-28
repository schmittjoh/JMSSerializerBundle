<?php

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            array(
                'event' => 'serializer.pre_serialize',
                'class' => 'Bar',
                'format' => 'json'
            ),
        );
    }
}
