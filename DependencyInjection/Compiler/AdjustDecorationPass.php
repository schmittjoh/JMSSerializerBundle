<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\SerializerBundle\DependencyInjection\DIUtils;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This step changes the service decoration targets to match the serializer instance
 * In this way when calling
 *
 * $container->getDefinition('xxx')
 *    ->setDecoratedService('jms_serializer.object_constructor')
 *
 * You do not need to worry to which serializer instance jms_serializer.object_constructor refers to.
 *
 * @internal
 */
final class AdjustDecorationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        DIUtils::adjustDecorators($container);
    }
}
