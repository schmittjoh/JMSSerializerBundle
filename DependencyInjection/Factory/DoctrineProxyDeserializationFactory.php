<?php

namespace JMS\SerializerBundle\DependencyInjection\Factory;

use JMS\SerializerBundle\DependencyInjection\HandlerFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class DoctrineProxyDeserializationFactory implements HandlerFactoryInterface
{
    public function getConfigKey()
    {
        return 'doctrine_proxy_deserialization';
    }

    public function getType(array $config)
    {
        return self::TYPE_DESERIALIZATION;
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->canBeUnset()
            ->children()
                ->scalarNode('registry')->defaultValue('doctrine')->end()
            ->end()
        ;
    }

    public function getHandlerId(ContainerBuilder $container, array $config)
    {
        $container
            ->getDefinition('jms_serializer.doctrine_deserialization_handler')
            ->addArgument(new Reference($config['registry']));

        return 'jms_serializer.doctrine_deserialization_handler';
    }
}
