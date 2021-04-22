<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler\Debug;

use JMS\SerializerBundle\Debug\DataCollector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds a data_collector to the profiler here because ProfilerPass already finished processing.
 */
class DataCollectorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('profiler')) {
            return;
        }

        $profiler = $container->getDefinition('profiler');

        $collector = $container->register($id = 'data_collector.jms_serializer', DataCollector::class);
        $collector
            ->addArgument(new Reference('debug.jms_serializer.runs_collector'))
            ->addArgument(new Reference('debug.jms_serializer.event_dispatcher'))
            ->addArgument(new Reference('debug.jms_serializer.handler_registry'));

        $profiler->addMethodCall('add', [new Reference($id)]);

        // add template
        $templates = $container->getParameter('data_collector.templates');
        $templates[$id] = ['jms_serializer', '@JMSSerializer/Collector/panel.html.twig'];
        $container->setParameter('data_collector.templates', $templates);
    }
}
