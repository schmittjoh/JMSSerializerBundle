<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\Serializer\GraphNavigator;
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
        foreach ($this->findAndSortTaggedServices('jms_serializer.handler', $container) as $reference) {
            $id = (string)$reference;
            $definition = $container->getDefinition($id);
            foreach ($definition->getTags() as $serviceTags) {
                $attrs = $serviceTags[0];
                if (!isset($attrs['type'], $attrs['format'])) {
                    throw new \RuntimeException(sprintf('Each tag named "jms_serializer.handler" of service "%s" must have at least two attributes: "type" and "format".', $id));
                }

                $directions = array(GraphNavigator::DIRECTION_DESERIALIZATION, GraphNavigator::DIRECTION_SERIALIZATION);
                if (isset($attrs['direction'])) {
                    if (!defined($directionConstant = 'JMS\Serializer\GraphNavigator::DIRECTION_' . strtoupper($attrs['direction']))) {
                        throw new \RuntimeException(sprintf('The direction "%s" of tag "jms_serializer.handler" of service "%s" does not exist.', $attrs['direction'], $id));
                    }

                    $directions = array(constant($directionConstant));
                }

                foreach ($directions as $direction) {
                    $method = isset($attrs['method']) ? $attrs['method'] : HandlerRegistry::getDefaultMethod($direction, $attrs['type'], $attrs['format']);
                    if (class_exists(ServiceLocatorTagPass::class) || $definition->isPublic()) {
                        $handlerServices[$id] = $reference;
                        $handlers[$direction][$attrs['type']][$attrs['format']] = array($id, $method);
                    } else {
                        $handlers[$direction][$attrs['type']][$attrs['format']] = array($reference, $method);
                    }
                }
            }
        }

        foreach ($this->findAndSortTaggedServices('jms_serializer.subscribing_handler', $container) as $reference) {
            $id = (string)$reference;
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();
            $ref = new \ReflectionClass($class);
            if (!$ref->implementsInterface('JMS\Serializer\Handler\SubscribingHandlerInterface')) {
                throw new \RuntimeException(sprintf('The service "%s" must implement the SubscribingHandlerInterface.', $id));
            }

            foreach (call_user_func(array($class, 'getSubscribingMethods')) as $methodData) {
                if (!isset($methodData['format'], $methodData['type'])) {
                    throw new \RuntimeException(sprintf('Each method returned from getSubscribingMethods of service "%s" must have a "type", and "format" attribute.', $id));
                }

                $directions = array(GraphNavigator::DIRECTION_DESERIALIZATION, GraphNavigator::DIRECTION_SERIALIZATION);
                if (isset($methodData['direction'])) {
                    $directions = array($methodData['direction']);
                }

                foreach ($directions as $direction) {
                    $method = isset($methodData['method']) ? $methodData['method'] : HandlerRegistry::getDefaultMethod($direction, $methodData['type'], $methodData['format']);
                    if (class_exists(ServiceLocatorTagPass::class) || $definition->isPublic()) {
                        $handlerServices[$id] = $reference;
                        $handlers[$direction][$methodData['type']][$methodData['format']] = array($id, $method);
                    } else {
                        $handlers[$direction][$methodData['type']][$methodData['format']] = array($reference, $method);
                    }
                }
            }
        }

        $container->findDefinition('jms_serializer.handler_registry')->addArgument($handlers);

        if (class_exists(ServiceLocatorTagPass::class)) {
            $serviceLocator = ServiceLocatorTagPass::register($container, $handlerServices);
            $container->findDefinition('jms_serializer.handler_registry')->replaceArgument(0, $serviceLocator);
        }
    }

    /**
     * Finds all services with the given tag name and order them by their priority.
     *
     * @param string           $tagName
     * @param ContainerBuilder $container
     *
     * @return Reference[]
     */
    private function findAndSortTaggedServices($tagName, ContainerBuilder $container)
    {
        $services = array();

        foreach ($container->findTaggedServiceIds($tagName, true) as $serviceId => $attributes) {
            $priority = isset($attributes[0]['priority']) ? intval($attributes[0]['priority']) : 0;

            $services[$priority][] = new Reference($serviceId);
        }

        if ($services) {
            krsort($services);
            $services = call_user_func_array('array_merge', $services);
        }

        return $services;
    }
}
