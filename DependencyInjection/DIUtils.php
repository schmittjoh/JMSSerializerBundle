<?php

namespace JMS\SerializerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DIUtils
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


                if (!self::shouldHanlde($instance, (string)$alias, $container)) {
                    continue;
                }

                if (!self::shouldHanlde($instance, (string)$aliasDef, $container)) {
                    $newAliasDef = $aliasDef;
                }else{
                    $newAliasDef = new Alias(self::getDefinitionRealIdOnly($instance, (string)$aliasDef), $aliasDef->isPublic());
                }
                $container->setAlias(self::getDefinitionRealIdOnly($instance, $alias), $newAliasDef);
            }
        }
    }

    private static function handleRef(ContainerBuilder $container, Reference $argument, string $instance): Reference
    {
        if (!self::shouldHanlde($instance, (string)$argument, $container)) {
            return $argument;
        }

        if ($container->hasAlias((string)self::getDefinitionRealId($instance, (string)$argument, $container)) &&
            !self::shouldHanlde($instance, (string)$container->getAlias((string)$argument), $container)) {
                return $argument;
        } else {
            $target = self::getDefinitionRealId($instance, (string)$argument, $container);
        }


        return new Reference($target, $argument->getInvalidBehavior());
    }

    private static function shouldHanlde(string $instance, string $id, ContainerBuilder $container):bool
    {
        if (strpos($id, 'jms_serializer.instance.') === 0 || strpos($id, 'jms_serializer.') !== 0 || $instance === 'default') {
            return false;
        }

        if ($container->hasDefinition($id) && $container->getDefinition($id)->hasTag('jms_serializer.instance_global')) {
            return false;
        }

        return true;
    }
    public static function getDefinitionRealId(string $instance, string $id, ContainerBuilder $container):string
    {
        if (!self::shouldHanlde($instance, $id, $container)) {
            return $id;
        }

        return sprintf('jms_serializer.instance.%s.%s', $instance, substr($id, 15));
    }


    private static function getDefinitionRealIdOnly(string $instance, string $id):string
    {
        return sprintf('jms_serializer.instance.%s.%s', $instance, substr($id, 15));
    }

    private static function cloneDefinition(string $instance, string $id, Definition $parentDef, ContainerBuilder $container)
    {
        if ($parentDef->hasTag('jms_serializer.instance_global') || strpos($id, 'jms_serializer.')!==0 || strpos($id, 'jms_serializer.instance.')===0) {
            return;
        } elseif ($instance === 'default') {
            if (!$parentDef->hasTag('jms_serializer.instance')) {
                $parentDef->addTag('jms_serializer.instance', ['name' => $instance]);
            }
            return;
        }

        $name = self::getDefinitionRealIdOnly($instance, $id);
        if (!$container->hasDefinition($name)) {
            $newDef = new Definition($parentDef->getClass());
            foreach ($parentDef->getTags() as $tagName => $tags){
                if ($tagName === 'jms_serializer.instance') {
                    continue;
                }
                foreach ($tags as $tag) {
                    $newDef->addTag($tagName, $tag);
                }
            }

            $newDef->addTag('jms_serializer.instance', ['name' => $instance]);

            $newDef->setArguments(self::handleArgs($parentDef->getArguments(), $container, $instance));

            $calls = [];
            foreach ($parentDef->getMethodCalls() as $call) {
                $calls[] =  [
                    $call[0],
                    self::handleArgs($call[1], $container, $instance)
                ];
            }
            $newDef->setMethodCalls($calls);

            $container->setDefinition($name, $newDef);
        }

    }

    public static function getSerializers(ContainerBuilder $container): array
    {
        $serializers = [];

        foreach ($container->findTaggedServiceIds('jms_serializer.serializer') as $serializerId => $serializerAttributes) {
            foreach ($serializerAttributes as $serializerAttribute) {
                $serializers[$serializerAttribute['name']] = $serializerId;
            }
        }
        return $serializers;
    }

    private static function handleArgs(array $args, ContainerBuilder $container, string $instance): array
    {
        foreach ($args as $n => $arg) {
            if (is_array($arg)) {
                $args[$n] = self::handleArgs($arg, $container, $instance);
            } elseif ($arg instanceof Reference) {
                $args[$n] = self::handleRef($container, $arg, $instance);
            }
        }
        return $args;
    }
}
