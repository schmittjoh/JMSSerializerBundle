<?php

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NamingStrategyTest extends TestCase
{
    /**
     *
     * @param array $configs
     * @return ContainerBuilder
     */
    private function getContainer(array $configs = array())
    {
        $loader = new JMSSerializerExtension();
        $container = new ContainerBuilder();

        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/serializer');
        $container->setParameter('kernel.bundles', array());

        $loader->load(['jms_serializer' => $configs], $container);
        return $container;
    }

    public function testCustomNamingStrategy()
    {
        $container = $this->getContainer(array(
            'property_naming' => array(
                'id' => 'custom_naming_strategy',
            )
        ));
        $customNamingStrategy = new CustomNamingStrategy();
        $container->set("custom_naming_strategy", $customNamingStrategy);

        $this->assertSame($customNamingStrategy, $container->get('jms_serializer.naming_strategy'));
    }
}

class CustomNamingStrategy implements PropertyNamingStrategyInterface
{
    public function translateName(PropertyMetadata $property):string
    {
        return 'foo';
    }
}
