<?php

namespace JMS\SerializerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class DIUtils
{
    public static function cloneDefinitions(ContainerBuilder $container, array $instances)
    {
        $definitions = $container->getDefinitions();
        $aliases = $container->getAliases();

        foreach ($instances as $instance) {
            foreach ($definitions as $id => $definition) {
                self::cloneDefinition($instance, $id, $definition, $container);
            }
            foreach ($aliases as $alias => $aliasDef) {

                if (!self::shouldTranslateId($instance, (string)$alias)) {
                    continue;
                }

                if (!self::shouldTranslateId($instance, (string)$aliasDef)) {
                    $newAliasDef = $aliasDef;
                } else {
                    $newAliasDef = new Alias(self::translateId($instance, (string)$aliasDef), $aliasDef->isPublic());
                }
                $container->setAlias(self::translateId($instance, $alias), $newAliasDef);
            }
        }
    }

    private static function handleRef(Reference $argument, string $instance): Reference
    {
        if (!self::shouldTranslateId($instance, (string)$argument)) {
            return $argument;
        }

        $target = self::getRealId($instance, (string)$argument);
        return new Reference($target, $argument->getInvalidBehavior());
    }

    public static function getRealId(string $instance, string $id): string
    {
        if (!self::shouldTranslateId($instance, $id)) {
            return $id;
        }

        return self::translateId($instance, $id);
    }

    private static function translateId(string $instance, string $id): string
    {
        return sprintf('jms_serializer.instance.%s.%s', $instance, substr($id, 15));
    }

    private static function shouldTranslateId(string $instance, string $id): bool
    {
        return !(
            strpos($id, 'jms_serializer.instance.') === 0 ||
            strpos($id, 'jms_serializer.') !== 0 ||
            $instance === 'default'
        );
    }

    private static function cloneDefinition(string $instance, string $id, Definition $parentDef, ContainerBuilder $container)
    {
        if (strpos($id, 'jms_serializer.') !== 0 || strpos($id, 'jms_serializer.instance.') === 0) {
            return;
        }

        $name = self::translateId($instance, $id);

        // add jms_serializer.instance.%s.%s for any jms service
        $container->setAlias($name, new Alias((string)$id, false));

        if ($parentDef->hasTag('jms_serializer.instance_global')) {
            return;
        }

        if ($instance === 'default') {
            if (!$parentDef->hasTag('jms_serializer.instance')) {
                $parentDef->addTag('jms_serializer.instance', ['name' => $instance]);
            }
            return;
        }

        $newDef = new Definition($parentDef->getClass());
        $container->setDefinition($name, $newDef);

        $tags = $parentDef->getTags();
        unset($tags['jms_serializer.instance']);
        $newDef->setTags($tags);
        $newDef->addTag('jms_serializer.instance', ['name' => $instance]);

        $newDef->setArguments(self::handleArgs($parentDef->getArguments(), $container, $instance));

        $calls = [];
        foreach ($parentDef->getMethodCalls() as $call) {
            $calls[] = [
                $call[0],
                self::handleArgs($call[1], $container, $instance)
            ];
        }
        $newDef->setMethodCalls($calls);
    }

    private static function handleArgs(array $args, ContainerBuilder $container, string $instance): array
    {
        foreach ($args as $n => $arg) {
            if (is_array($arg)) {
                $args[$n] = self::handleArgs($arg, $container, $instance);
            } elseif ($arg instanceof Reference) {
                $args[$n] = self::handleRef($arg, $instance);
            }
        }
        return $args;
    }
}
