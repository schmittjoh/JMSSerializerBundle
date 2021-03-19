<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;

class SubscribingHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'DateTime',
                'method' => 'onDateTime',
            ],
        ];
    }
}
