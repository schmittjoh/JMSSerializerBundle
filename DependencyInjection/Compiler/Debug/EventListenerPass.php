<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler\Debug;

use JMS\SerializerBundle\Debug\EventDispatcher\TraceableEventDispatcher;
use JMS\SerializerBundle\Debug\EventDispatcher\TraceableEventListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Should run after RegisterEventListenersAndSubscribersPass
 */
class EventListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $targetId = 'jms_serializer.event_dispatcher';
        $listenerServicesIds = [];

        $eventDispatcher = $container->findDefinition($targetId);
        $listeners = $this->decorateListeners($container, $eventDispatcher, $listenerServicesIds);
        $container->set("$targetId.inner", $eventDispatcher);

        $decorator = $container->register("debug.$targetId", TraceableEventDispatcher::class);
        // rebuild service locator
        if (($firsArgument = $eventDispatcher->getArgument(0)) instanceof Reference) {
            $definition = $container->getDefinition((string) $firsArgument);
            if ($definition && ServiceLocator::class === $definition->getClass()) {
                $refMap = array_combine($listenerServicesIds, array_map(static function (string $id): Reference {
                    return new Reference($id);
                }, $listenerServicesIds));
                $serviceLocator = ServiceLocatorTagPass::register($container, $refMap);
                $eventDispatcher->setArgument(0, $serviceLocator);
                $decorator->addArgument($serviceLocator);
            }
        } else {
            $decorator->addArgument($firsArgument);
        }

        $decorator
            ->addArgument($eventDispatcher)
            ->addArgument($listeners);
    }

    private function decorateListeners(ContainerBuilder $container, Definition $eventDispatcher, array &$listenerServicesIds): array
    {
        //todo should be refactored
        $calls = $eventDispatcher->getMethodCalls();
        foreach ($calls as &$call) {
            if ('setListeners' === $call[0]) {
                $listenersByEvent = $call[1][0];
                foreach ($listenersByEvent as &$listeners) {
                    foreach ($listeners as &$listener) {
                        $listenerServicesIds[] = $listener[0][0] = $this->decorateListener($container, $listener[0][0]);
                    }
                }

                $call[1][0] = $listenersByEvent;
            }
        }

        $eventDispatcher->setMethodCalls($calls);

        return $listenersByEvent; //todo
    }

    /**
     * Decorates event listener to collect information for profiler.
     */
    private function decorateListener(ContainerBuilder $container, string $id): string
    {
        if (0 === strpos($id, $prefix = 'debug.event_listener')) {
            return $id;
        }

        $container
            ->register($newId = "$prefix.$id", TraceableEventListener::class)
            ->addArgument(new Reference($id));

        return $newId;
    }
}
