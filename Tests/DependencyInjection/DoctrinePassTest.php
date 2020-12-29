<?php

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use JMS\SerializerBundle\DependencyInjection\Compiler\DoctrinePass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class DoctrinePassTest extends TestCase
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

        $pass = new DoctrinePass();
        $container->addCompilerPass($pass);

        $loader->load(['jms_serializer' => $configs], $container);

        $container->register('jms_serializer.object_constructor', 'stdClass')->setPublic(true);
        $container->register('jms_serializer.metadata_driver', 'stdClass')->setPublic(true);
        $container->register('annotation_reader', 'stdClass');

        return $container;
    }

    public function testDoctrineDisabled()
    {
        $container = $this->getContainer(array(
            'metadata' => array('infer_types_from_doctrine_metadata' => false)
        ));
        $container->register('doctrine.orm.entity_manager', 'stdClass');

        $container->compile();

        $constructor = $container->findDefinition('jms_serializer.object_constructor');
        $driver = $container->findDefinition('jms_serializer.metadata_driver');

        $this->assertFalse(self::assertDefinitionIsOfType(
            $container,
            $constructor,
            'JMS\Serializer\Construction\DoctrineObjectConstructor'
        ));

        $this->assertFalse(self::assertDefinitionIsOfType(
            $container,
            $driver,
            'JMS\Serializer\Metadata\Driver\DoctrineTypeDriver'
        ));

        $this->assertFalse(self::assertDefinitionIsOfType(
            $container,
            $constructor,
            'JMS\Serializer\Construction\DoctrineObjectConstructor'
        ));

        $this->assertFalse(self::assertDefinitionIsOfType(
            $container,
            $driver,
            'JMS\Serializer\Metadata\Driver\DoctrinePHPCRTypeDriver'
        ));
    }

    public function testOrm()
    {
        $container = $this->getContainer();
        $container->register('doctrine.orm.entity_manager', 'stdClass');
        $container->register('doctrine', 'stdClass');

        $container->compile();

        $constructor = $container->findDefinition('jms_serializer.object_constructor');
        $driver = $container->findDefinition('jms_serializer.metadata_driver');

        $this->assertTrue(self::assertDefinitionIsOfType(
            $container,
            $constructor,
            'JMS\Serializer\Construction\DoctrineObjectConstructor'
        ));
        $this->assertTrue(self::assertDefinitionIsOfType(
            $container,
            $driver,
            'JMS\Serializer\Metadata\Driver\DoctrineTypeDriver'
        ));
    }

    public function testOdm()
    {
        $container = $this->getContainer();
        $container->register('doctrine_phpcr.odm.document_manager', 'stdClass');
        $container->register('doctrine_phpcr', 'stdClass');

        $container->compile();

        $constructor = $container->findDefinition('jms_serializer.object_constructor');
        $driver = $container->findDefinition('jms_serializer.metadata_driver');

        $this->assertTrue(self::assertDefinitionIsOfType(
            $container,
            $constructor,
            'JMS\Serializer\Construction\DoctrineObjectConstructor'
        ));
        $this->assertTrue(self::assertDefinitionIsOfType(
            $container,
            $driver,
            'JMS\Serializer\Metadata\Driver\DoctrinePHPCRTypeDriver'
        ));
    }

    public function testOrmAndOdm()
    {
        $container = $this->getContainer();
        $container->register('doctrine.orm.entity_manager', 'stdClass');
        $container->register('doctrine', 'stdClass');
        $container->register('doctrine_phpcr.odm.document_manager', 'stdClass');
        $container->register('doctrine_phpcr', 'stdClass');

        $container->compile();

        $constructor = $container->findDefinition('jms_serializer.object_constructor');
        $driver = $container->findDefinition('jms_serializer.metadata_driver');

        $this->assertTrue(self::assertDefinitionIsOfType(
            $container,
            $constructor,
            'JMS\Serializer\Construction\DoctrineObjectConstructor'
        ));
        $this->assertTrue(self::assertDefinitionIsOfType(
            $container,
            $driver,
            'JMS\Serializer\Metadata\Driver\DoctrineTypeDriver'
        ));

        $this->assertTrue(self::assertDefinitionIsOfType(
            $container,
            $constructor,
            'JMS\Serializer\Construction\DoctrineObjectConstructor'
        ));
        $this->assertTrue(self::assertDefinitionIsOfType(
            $container,
            $driver,
            'JMS\Serializer\Metadata\Driver\DoctrinePHPCRTypeDriver'
        ));
    }

    private static function assertDefinitionIsOfType(ContainerBuilder $builder, Definition $definition, string $class)
    {
        if ($definition->getClass() === $class) {
            return true;
        }

        foreach ($definition->getArguments() as $argument) {
            if ($argument instanceof Definition && self::assertDefinitionIsOfType($builder, $argument, $class)) {
                return true;
            }
        }

        return false;
    }
}
