<?php

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

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

        $container->setDefinition("annotation_reader", new Definition(AnnotationReader::class));
        $container->setDefinition("custom_naming_strategy", new Definition(CustomNamingStrategy::class));

        $loader->load(['jms_serializer' => $configs], $container);

        // set public only for testing
        $container->getAlias('jms_serializer.naming_strategy')->setPublic(true);

        $container->compile();
        return $container;
    }

    public function testDefaultNamingStrategy()
    {
        $container = $this->getContainer();

        $strategy = $container->get('jms_serializer.naming_strategy');
        $this->assertInstanceOf(SerializedNameAnnotationStrategy::class, $strategy);

        $property = new PropertyMetadata(\stdClass::class, 'propOne');
        self::assertSame('prop_one', $strategy->translateName($property));

        $property = new PropertyMetadata(\stdClass::class, 'propOne');
        $property->serializedName = 'abc';
        self::assertSame('abc', $strategy->translateName($property));
    }

    public function testCustomNamingStrategyIsWrappedBySerializedNameAnnotationStrategy()
    {
        $container = $this->getContainer(array(
            'property_naming' => array(
                'id' => 'custom_naming_strategy',
                'allow_custom_serialized_name' => true
            )
        ));

        $strategy = $container->get('jms_serializer.naming_strategy');
        $this->assertInstanceOf(SerializedNameAnnotationStrategy::class, $strategy);

        $property = new PropertyMetadata(\stdClass::class, 'prop1');
        self::assertSame('foo', $strategy->translateName($property));

        $property = new PropertyMetadata(\stdClass::class, 'prop1');
        $property->serializedName = 'abc';
        self::assertSame('abc', $strategy->translateName($property));
    }

    public function testCustomNamingStrategyWrapIsDisabledByDefault()
    {
        $container = $this->getContainer(array(
            'property_naming' => array(
                'id' => 'custom_naming_strategy',
            )
        ));

        $strategy = $container->get('jms_serializer.naming_strategy');
        $this->assertInstanceOf(CustomNamingStrategy::class, $strategy);

        $property = new PropertyMetadata(\stdClass::class, 'prop1');
        self::assertSame('foo', $strategy->translateName($property));

        $property = new PropertyMetadata(\stdClass::class, 'prop1');
        $property->serializedName = 'abc';
        self::assertSame('foo', $strategy->translateName($property));
    }
}

class CustomNamingStrategy implements PropertyNamingStrategyInterface
{
    public function translateName(PropertyMetadata $property):string
    {
        return 'foo';
    }
}
