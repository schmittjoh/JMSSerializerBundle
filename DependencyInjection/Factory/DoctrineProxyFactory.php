<?php

namespace JMS\SerializerBundle\DependencyInjection\Factory;

use JMS\SerializerBundle\DependencyInjection\HandlerFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class DoctrineProxyFactory implements HandlerFactoryInterface
{
    public function getConfigKey()
    {
        return 'doctrine_proxy';
    }

    public function getType(array $config)
    {
        return self::TYPE_ALL;
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->canBeUnset()
            ->children()
                ->scalarNode('orm')->defaultValue('doctrine.orm.default_entity_manager')->end()
            ->end()
        ;
    }

    public function getHandlerId(ContainerBuilder $container, array $config)
    {
        $container
            ->getDefinition('jms_serializer.doctrine_handler')
            ->addArgument(new Reference($config['orm']));

        return 'jms_serializer.doctrine_handler';
    }
}
