<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\SerializerBundle\DependencyInjection\ScopedContainer;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class AssignVisitorsPass extends PerInstancePass
{
    protected function processInstance(ScopedContainer $container): void
    {
        $def = $container->getDefinition('jms_serializer.serializer');
        $serializers = [];
        foreach ($container->findTaggedServiceIds('jms_serializer.serialization_visitor') as $id => $multipleTags) {
            foreach ($multipleTags as $attributes) {
                $serializers[$attributes['format']] = new Reference($id);
            }
        }

        $def->replaceArgument(2, $serializers);

        $deserializers = [];
        foreach ($container->findTaggedServiceIds('jms_serializer.deserialization_visitor') as $id => $multipleTags) {
            foreach ($multipleTags as $attributes) {
                $deserializers[$attributes['format']] = new Reference($id);
            }
        }

        $def->replaceArgument(3, $deserializers);
    }
}
