<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\HandlerRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CustomHandlersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $handlers = array();
        $handlerServices = array();
        foreach ($container->findTaggedServiceIds('jms_serializer.handler') as $id => $tags) {
            foreach ($tags as $attrs) {
                if (!isset($attrs['type'], $attrs['format'])) {
                    throw new \RuntimeException(sprintf('Each tag named "jms_serializer.handler" of service "%s" must have at least two attributes: "type" and "format".', $id));
                }

                $directions = array(GraphNavigatorInterface::DIRECTION_DESERIALIZATION, GraphNavigatorInterface::DIRECTION_SERIALIZATION);
                if (isset($attrs['direction'])) {
                    if (!defined($directionConstant = 'JMS\Serializer\GraphNavigatorInterface::DIRECTION_' . strtoupper($attrs['direction']))) {
                        throw new \RuntimeException(sprintf('The direction "%s" of tag "jms_serializer.handler" of service "%s" does not exist.', $attrs['direction'], $id));
                    }

                    $directions = array(constant($directionConstant));
                }

                foreach ($directions as $direction) {
                    $method = isset($attrs['method']) ? $attrs['method'] : HandlerRegistry::getDefaultMethod($direction, $attrs['type'], $attrs['format']);
                    $priority = isset($attrs['priority']) ? intval($attrs['priority']) : 0;
                    $ref = new Reference($id);
                    if (class_exists(ServiceLocatorTagPass::class) || $container->getDefinition($id)->isPublic()) {
                        $handlerServices[$id] = $ref;
                        $handlers[] = array($direction, $attrs['type'], $attrs['format'], $priority, $id, $method);
                    } else {
                        $handlers[] = array($direction, $attrs['type'], $attrs['format'], $priority, $ref, $method);
                    }
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

            foreach (call_user_func(array($class, 'getSubscribingMethods')) as $methodData) {
                if (!isset($methodData['format'], $methodData['type'])) {
                    throw new \RuntimeException(sprintf('Each method returned from getSubscribingMethods of service "%s" must have a "type", and "format" attribute.', $id));
                }

                $directions = array(GraphNavigatorInterface::DIRECTION_DESERIALIZATION, GraphNavigatorInterface::DIRECTION_SERIALIZATION);
                if (isset($methodData['direction'])) {
                    $directions = array($methodData['direction']);
                }

                foreach ($directions as $direction) {
                    $priority = isset($methodData['priority']) ? intval($methodData['priority']) : 0;
                    $method = isset($methodData['method']) ? $methodData['method'] : HandlerRegistry::getDefaultMethod($direction, $methodData['type'], $methodData['format']);

                    $ref = new Reference($id);
                    if (class_exists(ServiceLocatorTagPass::class) || $def->isPublic()) {
                        $handlerServices[$id] = $ref;
                        $handlers[] = array($direction, $methodData['type'], $methodData['format'], $priority, $id, $method);
                    } else {
                        $handlers[] = array($direction, $methodData['type'], $methodData['format'], $priority, $ref, $method);
                    }
                }
            }
        }

        $handlers = $this->sortAndFlattenHandlersList($handlers);

        $container->findDefinition('jms_serializer.handler_registry')
            ->addArgument($handlers);

        if (class_exists(ServiceLocatorTagPass::class)) {
            $serviceLocator = ServiceLocatorTagPass::register($container, $handlerServices);
            $container->findDefinition('jms_serializer.handler_registry')->replaceArgument(0, $serviceLocator);
        }
    }

    private function sortAndFlattenHandlersList(array $allHandlers)
    {
        $sorter = function ($a, $b) {
            return $b[3] == $a[3] ? 0 : ($b[3] > $a[3] ? 1 : -1);
        };
        self::stable_uasort($allHandlers, $sorter);
        $handlers = [];
        foreach ($allHandlers as $handler) {
            list ($direction, $type, $format, $priority, $service, $method) = $handler;
            $handlers[$direction][$type][$format] = [$service, $method];
        }

        return $handlers;
    }

    /**
     * Performs stable sorting. Copied from http://php.net/manual/en/function.uasort.php#121283
     *
     * @param array $array
     * @param $value_compare_func
     * @return bool
     */
    private static function stable_uasort(array &$array, $value_compare_func)
    {
        $index = 0;
        foreach ($array as &$item) {
            $item = array($index++, $item);
        }
        $result = uasort($array, function ($a, $b) use ($value_compare_func) {
            $result = call_user_func($value_compare_func, $a[1], $b[1]);
            return $result == 0 ? $a[0] - $b[0] : $result;
        });
        foreach ($array as &$item) {
            $item = $item[1];
        }
        return $result;
    }
}
