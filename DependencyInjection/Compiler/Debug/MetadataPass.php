<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler\Debug;

use JMS\SerializerBundle\Debug\Metadata\TraceableAnnotationDriver;
use JMS\SerializerBundle\Debug\Metadata\TraceableXmlDriver;
use JMS\SerializerBundle\Debug\Metadata\TraceableYamlDriver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MetadataPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->replaceDriver($container, 'jms_serializer.metadata.annotation_driver', TraceableAnnotationDriver::class);
        $this->replaceDriver($container, 'jms_serializer.metadata.xml_driver', TraceableXmlDriver::class);
        $this->replaceDriver($container, 'jms_serializer.metadata.yaml_driver', TraceableYamlDriver::class);
    }

    private function replaceDriver(ContainerBuilder $container, string $id, string $class): void
    {
        if (!$container->hasDefinition($id)) {
            return;
        }

        $definition = $container->getDefinition($id);
        $container->removeDefinition($id);

        $container
            ->register($id, $class)
            ->setArguments($definition->getArguments())
            ->addMethodCall('setCollector', [new Reference('debug.jms_serializer.runs_collector')]);
    }
}
