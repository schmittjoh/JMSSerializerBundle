<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * SetHandlersPass
 */
class SetHandlersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('jms_serializer.serializer')) {
            return;
        }
        
        foreach ($container->findTaggedServiceIds('jms_serializer.serialization_visitor') as $id => $options) {
            $container
                ->getDefinition($id)
                ->replaceArgument(1, $container->getDefinition('jms_serializer.serialization_handlers'))
            ;
        }
        foreach ($container->findTaggedServiceIds('jms_serializer.deserialization_visitor') as $id => $options) {
            $container
                ->getDefinition($id)
                ->replaceArgument(1, $container->getDefinition('jms_serializer.deserialization_handlers'))
            ;
        }
    }
}