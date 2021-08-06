<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection\Fixture\IncludeInterfaces;

use JMS\Serializer\Annotation\Discriminator;

/**
 * @Discriminator(
 *     field="type",
 *     map={
 *         "a": "JMS\SerializerBundle\Tests\DependencyInjection\Fixture\IncludeInterfaces\AnInterfaceImplementation"
 *     }
 * )
 */
interface AnInterface
{
    public function execute(): void;
}
