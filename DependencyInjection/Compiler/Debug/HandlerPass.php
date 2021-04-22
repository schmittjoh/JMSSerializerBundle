<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler\Debug;

use JMS\SerializerBundle\Debug\Handler\TraceableHandler;
use JMS\SerializerBundle\Debug\Handler\TraceableHandlerRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Should run after CustomHandlersPass
 */
class HandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $targetId = 'jms_serializer.handler_registry';
        $listenerServicesIds = [];

        $handlerRegistry = $container->findDefinition($targetId);
        $this->decorateHandlers($container, $handlerRegistry, $listenerServicesIds);
        $container->set("$targetId.inner", $handlerRegistry);

        $decorator = $container->register("debug.$targetId", TraceableHandlerRegistry::class);

        // rebuild service locator
        if (($firsArgument = $handlerRegistry->getArgument(0)) instanceof Reference) {
            $definition = $container->getDefinition((string) $firsArgument);
            if ($definition && ServiceLocator::class === $definition->getClass()) {
                $refMap = array_combine($listenerServicesIds, array_map(static function (string $id): Reference {
                    return new Reference($id);
                }, $listenerServicesIds));
                $serviceLocator = ServiceLocatorTagPass::register($container, $refMap);
                $handlerRegistry->setArgument(0, $serviceLocator);
                $decorator->addArgument($serviceLocator);
            }
        } else {
            $decorator->addArgument($firsArgument);
        }

        $decorator->addArgument($handlerRegistry);
    }

    private function decorateHandlers(ContainerBuilder $container, Definition $handlerRegistry, array &$listenerServicesIds): void
    {
        $handlersByDirection = $handlerRegistry->getArgument(1);
        foreach ($handlersByDirection as &$handlers) {
            foreach ($handlers as &$handler) {
                foreach ($handler as &$config) {
                    $listenerServicesIds[] = $config[0] = $this->decorateHandler($container, $config[0]);
                }
            }
        }

        $handlerRegistry->setArgument(1, $handlersByDirection);
    }

    /**
     * Decorates event listener to collect information for profiler.
     */
    private function decorateHandler(ContainerBuilder $container, string $id): string
    {
        if (0 === strpos($id, $prefix = 'debug.handler')) {
            return $id;
        }

        $container
            ->register($newId = "$prefix.$id", TraceableHandler::class)
            ->addArgument(new Reference($id))
            ->addArgument(new Reference('debug.jms_serializer.runs_collector'));

        return $newId;
    }
}
