<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\SerializerBundle\DependencyInjection\DIUtils;
use JMS\SerializerBundle\DependencyInjection\ScopedContainer;
use Symfony\Component\DependencyInjection\Reference;

/** @internal */
final class DoctrinePass extends PerInstancePass
{
    protected function processInstance(ScopedContainer $container): void
    {
        $registries = [
            'doctrine.orm.entity_manager' => 'doctrine',
            'doctrine_phpcr.odm.document_manager' => 'doctrine_phpcr',
        ];

        foreach ($registries as $managerId => $registry) {
            if (!$container->has($managerId)) {
                $container->removeDefinition(sprintf('jms_serializer.metadata.%s_type_driver', $registry));
                unset($registries[$managerId]);
            }
        }

        foreach ($registries as $registry) {
            if ($container->hasDefinition(sprintf('jms_serializer.metadata.%s_type_driver', $registry))) {
                $container->getDefinition(sprintf('jms_serializer.metadata.%s_type_driver', $registry))
                    ->setDecoratedService('jms_serializer.metadata_driver')
                    ->replaceArgument(0, new Reference($this->getInnerId($container, sprintf('jms_serializer.metadata.%s_type_driver', $registry))));
            }

            if ($container->hasDefinition(sprintf('jms_serializer.%s_object_constructor', $registry))) {
                $container->getDefinition(sprintf('jms_serializer.%s_object_constructor', $registry))
                    ->setDecoratedService('jms_serializer.object_constructor')
                    ->replaceArgument(1, new Reference($this->getInnerId($container, sprintf('jms_serializer.%s_object_constructor', $registry))));
            }
        }
    }

    /**
     * The scoped container translates the ids it is given, but not the ids held by a Reference.
     */
    private function getInnerId(ScopedContainer $container, string $decoratorId): string
    {
        return DIUtils::getRealId($container->getInstanceName(), $decoratorId) . '.inner';
    }
}
