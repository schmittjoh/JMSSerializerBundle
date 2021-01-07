<?php

declare(strict_types=1);

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
    /**
     * @see \JMS\SerializerBundle\DependencyInjection\Compiler\AdjustDecorationPass
     */
    public static function adjustDecorators(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            $tagData = $definition->getTag('jms_serializer.instance');
            if (empty($tagData) || 'default' === $tagData[0]['name']) {
                continue;
            }

            $decorationServiceDefinition = $definition->getDecoratedService();
            if ($decorationServiceDefinition) {
                // if we are referring to a jms service, but that service is not per-instance
                if (
                    false !== strpos($decorationServiceDefinition[0], 'jms_serializer.')
                    &&
                    false === strpos($decorationServiceDefinition[0], 'jms_serializer.instance.')
                ) {
                    if ($container->hasDefinition($decorationServiceDefinition[0])) {
                        $decorationDefinition = $container->getDefinition($decorationServiceDefinition[0]);
                        if ($decorationDefinition->hasTag('jms_serializer.instance_global')) {
                            throw new \LogicException('It is not possible to decorate global JMS services');
                        }
                    }

                    $decorationServiceDefinition[0] = self::getRealId($tagData[0]['name'], $decorationServiceDefinition[0]);
                    call_user_func_array([$definition, 'setDecoratedService'], $decorationServiceDefinition);
                }
            }
        }
    }

    public static function cloneDefinitions(ContainerBuilder $container, array $instances)
    {
        $definitions = $container->getDefinitions();
        $aliases = $container->getAliases();

        foreach ($instances as $instance) {
            foreach ($definitions as $id => $definition) {
                self::cloneDefinition($instance, $id, $definition, $container);
            }

            foreach ($aliases as $alias => $aliasDef) {
                if (!self::shouldTranslateId($instance, (string) $alias)) {
                    continue;
                }

                if (!self::shouldTranslateId($instance, (string) $aliasDef)) {
                    $newAliasDef = $aliasDef;
                } else {
                    $newAliasDef = new Alias(self::translateId($instance, (string) $aliasDef), $aliasDef->isPublic());
                }

                $container->setAlias(self::translateId($instance, $alias), $newAliasDef);
            }
        }
    }

    private static function handleRef(Reference $argument, string $instance): Reference
    {
        if (!self::shouldTranslateId($instance, (string) $argument)) {
            return $argument;
        }

        $target = self::getRealId($instance, (string) $argument);

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
            0 === strpos($id, 'jms_serializer.instance.') ||
            0 !== strpos($id, 'jms_serializer.') ||
            'default' === $instance
        );
    }

    private static function cloneDefinition(string $instance, string $id, Definition $parentDef, ContainerBuilder $container)
    {
        if (0 !== strpos($id, 'jms_serializer.') || 0 === strpos($id, 'jms_serializer.instance.')) {
            return;
        }

        $name = self::translateId($instance, $id);

        // add jms_serializer.instance.%s.%s for any jms service
        $container->setAlias($name, new Alias((string) $id, false));

        if ($parentDef->hasTag('jms_serializer.instance_global')) {
            return;
        }

        if ($parentDef->hasTag('jms_serializer.instance') && $parentDef->getTag('jms_serializer.instance')[0]['name'] === $instance) {
            return;
        }

        if ('default' === $instance) {
            if (!$parentDef->hasTag('jms_serializer.instance')) {
                $parentDef->addTag('jms_serializer.instance', ['name' => $instance]);
            }

            return;
        }

        $newDef = new Definition($parentDef->getClass());
        $container->setDefinition($name, $newDef);

        $decoration = $parentDef->getDecoratedService();
        if ($decoration) {
            $decoration[0] = self::getRealId($instance, $decoration[0]);

            call_user_func_array([$newDef, 'setDecoratedService'], $decoration);
        }

        $tags = $parentDef->getTags();
        unset($tags['jms_serializer.instance']);

        // we have one data collector for each serializer instance
        if (!empty($tags['data_collector'])) {
            $tags['data_collector'][0]['id'] = 'jms_serializer_' . $instance;
        }

        $newDef->setTags($tags);
        $newDef->addTag('jms_serializer.instance', ['name' => $instance]);

        $newDef->setArguments(self::handleArgs($parentDef->getArguments(), $container, $instance));

        $calls = [];
        foreach ($parentDef->getMethodCalls() as $call) {
            $calls[] = [
                $call[0],
                self::handleArgs($call[1], $container, $instance),
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
