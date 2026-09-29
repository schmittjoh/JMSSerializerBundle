<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use JMS\SerializerBundle\DependencyInjection\Compiler\AdjustDecorationPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\DoctrinePass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use Metadata\Driver\DriverChain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DoctrinePassTest extends TestCase
{
    /**
     * @param array $configs
     *
     * @return ContainerBuilder
     */
    private function getContainer(array $configs = [])
    {
        $loader = new JMSSerializerExtension();
        $container = new ContainerBuilder();

        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/serializer');
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.bundles_metadata', []);

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
        $container = $this->getContainer([
            'object_constructors' => ['doctrine' => false],
            'metadata' => ['infer_types_from_doctrine_metadata' => false],
        ]);
        $container->register('doctrine.orm.entity_manager', 'stdClass');

        $container->compile();

        $constructor = $container->findDefinition('jms_serializer.object_constructor');
        $driver = $container->findDefinition('jms_serializer.metadata_driver');

        $this->assertFalse(self::assertDefinitionIsOfType($container, $constructor, 'JMS\Serializer\Construction\DoctrineObjectConstructor'));
        $this->assertFalse(self::assertDefinitionIsOfType($container, $driver, 'JMS\Serializer\Metadata\Driver\DoctrineTypeDriver'));

        $this->assertFalse(self::assertDefinitionIsOfType($container, $constructor, 'JMS\Serializer\Construction\DoctrineObjectConstructor'));
        $this->assertFalse(self::assertDefinitionIsOfType($container, $driver, 'JMS\Serializer\Metadata\Driver\DoctrinePHPCRTypeDriver'));
    }

    public function testDoctrineDisabledOnlyForInstance()
    {
        $container = $this->getContainer([
            'instances' => [
                'my_instance' => [
                    'inherit' => true,
                    'metadata' => ['infer_types_from_doctrine_metadata' => false],
                ],
            ],
        ]);
        $container->register('doctrine.orm.entity_manager', 'stdClass');
        $container->register('doctrine', 'stdClass');
        $container->register('doctrine_phpcr.odm.document_manager', 'stdClass');
        $container->register('doctrine_phpcr', 'stdClass');

        $container->compile();

        $driver = $container->findDefinition('jms_serializer.instance.my_instance.metadata_driver');

        $this->assertFalse(self::assertDefinitionIsOfType($container, $driver, 'JMS\Serializer\Metadata\Driver\DoctrineTypeDriver'));

        $this->assertFalse(self::assertDefinitionIsOfType($container, $driver, 'JMS\Serializer\Metadata\Driver\DoctrinePHPCRTypeDriver'));
    }

    public function testDecoratorsOfAnInstanceWrapItsOwnServices()
    {
        $container = $this->getContainer([
            'instances' => [
                'my_instance' => [],
            ],
        ]);
        $container->register('doctrine.orm.entity_manager', 'stdClass');
        $container->register('doctrine', 'stdClass');
        // as in the bundle, so that the decorators target the services of their instance
        $container->addCompilerPass(new AdjustDecorationPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -100);
        // keep the references, the removing passes would inline them
        $container->getCompilerPassConfig()->setRemovingPasses([]);

        $container->compile();

        $driverId = 'jms_serializer.instance.my_instance.metadata.doctrine_type_driver';
        $this->assertEquals(
            new Reference($driverId . '.inner'),
            $container->getDefinition($driverId)->getArgument(0),
        );
        $this->assertSame(DriverChain::class, $container->getDefinition($driverId . '.inner')->getClass());

        $constructorId = 'jms_serializer.instance.my_instance.doctrine_object_constructor';
        $this->assertEquals(
            new Reference($constructorId . '.inner'),
            $container->getDefinition($constructorId)->getArgument(1),
        );
    }

    public function testOrm()
    {
        $container = $this->getContainer();
        $container->register('doctrine.orm.entity_manager', 'stdClass');
        $container->register('doctrine', 'stdClass');

        $container->compile();

        $constructor = $container->findDefinition('jms_serializer.object_constructor');
        $driver = $container->findDefinition('jms_serializer.metadata_driver');

        $this->assertTrue(self::assertDefinitionIsOfType($container, $constructor, 'JMS\Serializer\Construction\DoctrineObjectConstructor'));
        $this->assertTrue(self::assertDefinitionIsOfType($container, $driver, 'JMS\Serializer\Metadata\Driver\DoctrineTypeDriver'));
    }

    public function testOdm()
    {
        $container = $this->getContainer();
        $container->register('doctrine_phpcr.odm.document_manager', 'stdClass');
        $container->register('doctrine_phpcr', 'stdClass');

        $container->compile();

        $constructor = $container->findDefinition('jms_serializer.object_constructor');
        $driver = $container->findDefinition('jms_serializer.metadata_driver');

        $this->assertTrue(self::assertDefinitionIsOfType($container, $constructor, 'JMS\Serializer\Construction\DoctrineObjectConstructor'));
        $this->assertTrue(self::assertDefinitionIsOfType($container, $driver, 'JMS\Serializer\Metadata\Driver\DoctrinePHPCRTypeDriver'));
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

        $this->assertTrue(self::assertDefinitionIsOfType($container, $constructor, 'JMS\Serializer\Construction\DoctrineObjectConstructor'));
        $this->assertTrue(self::assertDefinitionIsOfType($container, $driver, 'JMS\Serializer\Metadata\Driver\DoctrineTypeDriver'));

        $this->assertTrue(self::assertDefinitionIsOfType($container, $constructor, 'JMS\Serializer\Construction\DoctrineObjectConstructor'));
        $this->assertTrue(self::assertDefinitionIsOfType($container, $driver, 'JMS\Serializer\Metadata\Driver\DoctrinePHPCRTypeDriver'));
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
