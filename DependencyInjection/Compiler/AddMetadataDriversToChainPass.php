<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * @internal
 */
final class AddMetadataDriversToChainPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        try {
            $chainDefinition = $container->findDefinition('jms_serializer.metadata_driver');

            foreach ($this->findAndSortTaggedServices('jms_serializer.metadata_driver', $container) as $driver) {
                $chainDefinition->addMethodCall('addDriver', [$driver]);
            }
        } catch (ServiceNotFoundException $exception) {
        }
    }
}
