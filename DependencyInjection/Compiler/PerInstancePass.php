<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\SerializerBundle\DependencyInjection\DIUtils;
use JMS\SerializerBundle\DependencyInjection\ScopedContainer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
abstract class PerInstancePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
         foreach (DIUtils::getSerializers($container) as $serializerName => $serializerId) {
             $scopedContainer = new ScopedContainer($container, $serializerName);
             $this->processInstance($scopedContainer);
         }
    }

    protected abstract function processInstance(ScopedContainer $container): void;
}
