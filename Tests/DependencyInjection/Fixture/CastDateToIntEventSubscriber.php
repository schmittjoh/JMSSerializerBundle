<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;

class CastDateToIntEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.pre_serialize',
                'class' => 'DateTime',
                'format' => 'json',
                'method' => 'onDateTime',
            ],
        ];
    }

    public function onDateTime(PreSerializeEvent $preSerialize)
    {
        $preSerialize->setType('DateTime', ['m-d-Y']);
    }
}
