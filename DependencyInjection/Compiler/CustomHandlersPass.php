<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\LazyHandlerRegistry;
use JMS\SerializerBundle\DependencyInjection\ScopedContainer;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class CustomHandlersPass extends PerInstancePass
{
    protected function processInstance(ScopedContainer $container): void
    {
        $handlersByDirection = $this->findHandlers($container);
        $handlerRegistryDef = $container->findDefinition('jms_serializer.handler_registry');

        $isLazyHandlerRegistry = is_a($handlerRegistryDef->getClass(), LazyHandlerRegistry::class, true);

        $handlerServices = [];
        $handlers = [];
        foreach ($handlersByDirection as $direction => $handlersByType) {
            foreach ($handlersByType as $type => $handlersByFormat) {
                foreach ($handlersByFormat as $format => $handlerCallable) {
                    $id = (string) $handlerCallable[0];

                    $handlerServices[$id] = new ServiceClosureArgument($handlerCallable[0]);
                    $handlerCallable[0] = $id;

                    if (!$isLazyHandlerRegistry) {
                        $handlerRegistryDef->addMethodCall('registerHandler', [$direction, $type, $format, $handlerCallable]);
                    } else {
                        $handlers[$direction][$type][$format] = $handlerCallable;
                    }
                }
            }
        }

        if ($isLazyHandlerRegistry) {
            $handlerRegistryDef->addArgument($handlers);
        }

        $container->findDefinition('jms_serializer.handler_registry.service_locator')
            ->setArgument(0, $handlerServices);
    }

    private function findHandlers(ScopedContainer $container): array
    {
        $handlers = [];
        foreach ($container->findTaggedServiceIds('jms_serializer.handler') as $id => $tags) {
            foreach ($tags as $attrs) {
                if (!isset($attrs['type'], $attrs['format'])) {
                    throw new \RuntimeException(sprintf('Each tag named "jms_serializer.handler" of service "%s" must have at least two attributes: "type" and "format".', $id));
                }

                $directions = [GraphNavigatorInterface::DIRECTION_DESERIALIZATION, GraphNavigatorInterface::DIRECTION_SERIALIZATION];
                if (isset($attrs['direction'])) {
                    if (!defined($directionConstant = 'JMS\Serializer\GraphNavigatorInterface::DIRECTION_' . strtoupper($attrs['direction']))) {
                        throw new \RuntimeException(sprintf('The direction "%s" of tag "jms_serializer.handler" of service "%s" does not exist.', $attrs['direction'], $id));
                    }

                    $directions = [constant($directionConstant)];
                }

                foreach ($directions as $direction) {
                    $method = $attrs['method'] ?? HandlerRegistry::getDefaultMethod($direction, $attrs['type'], $attrs['format']);
                    $priority = isset($attrs['priority']) ? intval($attrs['priority']) : 0;

                    $handlers[] = [$direction, $attrs['type'], $attrs['format'], $priority, new Reference($id), $method];
                }
            }
        }

        foreach ($container->findTaggedServiceIds('jms_serializer.subscribing_handler') as $id => $tags) {
            $def = $container->getDefinition($id);
            $class = $def->getClass();

            $ref = new \ReflectionClass($class);
            if (!$ref->implementsInterface('JMS\Serializer\Handler\SubscribingHandlerInterface')) {
                throw new \RuntimeException(sprintf('The service "%s" must implement the SubscribingHandlerInterface.', $id));
            }

            foreach (call_user_func([$class, 'getSubscribingMethods']) as $methodData) {
                if (!isset($methodData['format'], $methodData['type'])) {
                    throw new \RuntimeException(sprintf('Each method returned from getSubscribingMethods of service "%s" must have a "type", and "format" attribute.', $id));
                }

                $directions = [GraphNavigatorInterface::DIRECTION_DESERIALIZATION, GraphNavigatorInterface::DIRECTION_SERIALIZATION];
                if (isset($methodData['direction'])) {
                    $directions = [$methodData['direction']];
                }

                foreach ($directions as $direction) {
                    $priority = isset($methodData['priority']) ? intval($methodData['priority']) : 0;
                    $method = $methodData['method'] ?? HandlerRegistry::getDefaultMethod($direction, $methodData['type'], $methodData['format']);

                    $handlers[] = [$direction, $methodData['type'], $methodData['format'], $priority, new Reference($id), $method];
                }
            }
        }

        return $this->sortAndFlattenHandlersList($handlers);
    }

    private function sortAndFlattenHandlersList(array $allHandlers)
    {
        $sorter = static function ($a, $b) {
            return $b[3] === $a[3] ? 0 : ($b[3] > $a[3] ? 1 : -1);
        };
        self::stable_uasort($allHandlers, $sorter);
        $handlers = [];
        foreach ($allHandlers as $handler) {
            [$direction, $type, $format, $priority, $service, $method] = $handler;
            $handlers[$direction][$type][$format] = [$service, $method];
        }

        return $handlers;
    }

    /**
     * Performs stable sorting. Copied from http://php.net/manual/en/function.uasort.php#121283
     *
     * @param array $array
     * @param callable $value_compare_func
     *
     * @return bool
     */
    private static function stable_uasort(array &$array, callable $value_compare_func)
    {
        $index = 0;
        foreach ($array as &$item) {
            $item = [$index++, $item];
        }

        $result = uasort($array, static function ($a, $b) use ($value_compare_func) {
            $result = call_user_func($value_compare_func, $a[1], $b[1]);

            return 0 === $result ? $a[0] - $b[0] : $result;
        });
        foreach ($array as &$item) {
            $item = $item[1];
        }

        return $result;
    }
}
