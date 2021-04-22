<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler\Debug;

use JMS\SerializerBundle\Debug\StopwatchSerializer;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Decorates Serializer for measure performance time.
 */
class StopwatchPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('debug.stopwatch') || !$container->hasDefinition($id = 'jms_serializer.serializer')) {
            return;
        }

        $decoratorId = "debug.$id";
        $inner = $container->getDefinition('jms_serializer.serializer');
        $container->setDefinition($innerId = "$decoratorId.inner", $inner);
        $decorator = $container->register($decoratorId, StopwatchSerializer::class);
        $decorator
            ->addArgument(new Reference($innerId))
            ->addArgument(new Reference('debug.stopwatch'))
            ->addArgument(new Reference('debug.jms_serializer.runs_collector'));

        $container->setAlias($id, new Alias($decoratorId));
    }
}
