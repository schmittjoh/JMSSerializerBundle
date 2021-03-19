<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.pre_serialize',
                'class' => 'Bar',
                'format' => 'json',
            ],
        ];
    }
}
