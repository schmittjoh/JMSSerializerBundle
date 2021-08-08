<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class ExpressionFunctionProviderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        try {
            $registryDefinition = $container->findDefinition('jms_serializer.expression_language');

            foreach (array_keys($container->findTaggedServiceIds('jms.expression.function_provider')) as $id) {
                $registryDefinition->addMethodCall('registerProvider', [new Reference($id)]);
            }
        } catch (ServiceNotFoundException $exception) {
        }

        if ($container->has('security.authorization_checker')) {
            $container->setAlias('jms_serializer.authorization_checker', 'security.authorization_checker')
                ->setPublic(true);
        }
    }
}
