<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\SerializerBundle\DependencyInjection\ScopedContainer;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class ExpressionFunctionProviderPass extends PerInstancePass
{
    /**
     * {@inheritdoc}
     */
    protected function processInstance(ScopedContainer $container): void
    {
        try {
            $registryDefinition = $container->findDefinition('jms_serializer.expression_language');

            foreach (array_keys($container->findTaggedServiceIds('jms.expression.function_provider')) as $id) {
                $registryDefinition->addMethodCall('registerProvider', [new Reference($id)]);
            }
        } catch (ServiceNotFoundException $exception){

        }
    }
}
