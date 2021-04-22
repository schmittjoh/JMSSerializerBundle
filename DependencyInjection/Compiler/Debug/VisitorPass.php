<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler\Debug;

use JMS\SerializerBundle\Debug\RunsCollector;
use JMS\SerializerBundle\Debug\Visitor\Factory\TraceableDeserializationVisitorFactory;
use JMS\SerializerBundle\Debug\Visitor\Factory\TraceableSerializationVisitorFactory;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class VisitorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->register($collectorId = 'debug.jms_serializer.runs_collector', RunsCollector::class);

        foreach ($container->findTaggedServiceIds('jms_serializer.serialization_visitor') as $id => $tags) {
            $this->decorate($container, $id, TraceableSerializationVisitorFactory::class, $collectorId);
        }

        foreach ($container->findTaggedServiceIds('jms_serializer.deserialization_visitor') as $id => $tags) {
            $this->decorate($container, $id, TraceableDeserializationVisitorFactory::class, $collectorId);
        }
    }

    private function decorate(ContainerBuilder $container, string $id, string $decoratorClass, string $collectorId): void
    {
        $decoratorId = "debug.$id";
        $factory = $container->getDefinition($id);
        $container->setDefinition($innerId = "$decoratorId.inner", $factory);
        $decorator = $container->register($decoratorId, $decoratorClass);
        $decorator
            ->addArgument(new Reference($innerId))
            ->addArgument(new Reference($collectorId));

        $container->setAlias($id, new Alias($decoratorId));
    }
}
