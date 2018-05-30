<?php

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use JMS\SerializerBundle\DependencyInjection\Compiler\DoctrinePass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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

        $loader->load(['jms_serializer' => $configs], $container);
        return $container;
    }

    public function testDoctrineDisabled()
    {
        $container = $this->getContainer(array(
            'metadata' => array('infer_types_from_doctrine_metadata' => false)
        ));
        $container->register('doctrine.orm.entity_manager');

        $pass = new DoctrinePass();
        $pass->process($container);

        $alias = $container->getAlias('jms_serializer.object_constructor');
        $this->assertFalse($alias->isPublic());

        $this->assertEquals('jms_serializer.unserialize_object_constructor', (string)$alias);
    }

    public function testOrm()
    {
        $container = $this->getContainer();
        $container->register('doctrine.orm.entity_manager');

        $pass = new DoctrinePass();
        $pass->process($container);

        $alias = $container->getAlias('jms_serializer.object_constructor');
        $this->assertTrue($alias->isPublic());

        $this->assertEquals('jms_serializer.doctrine_object_constructor', (string)$alias);
    }

    public function testOdm()
    {
        $container = $this->getContainer();
        $container->register('doctrine_phpcr.odm.document_manager');

        $pass = new DoctrinePass();
        $pass->process($container);

        $alias = $container->getAlias('jms_serializer.object_constructor');
        $this->assertTrue($alias->isPublic());

        $this->assertEquals('jms_serializer.doctrine_phpcr_object_constructor', (string)$alias);

        $def = $container->getDefinition('jms_serializer.doctrine_phpcr_object_constructor');
        $this->assertEquals('jms_serializer.unserialize_object_constructor', (string)$def->getArgument(1));
    }

    public function testOrmAndOdm()
    {
        $container = $this->getContainer();

        $container->register('doctrine_phpcr.odm.document_manager');
        $container->register('doctrine.orm.entity_manager');

        $pass = new DoctrinePass();
        $pass->process($container);


        $alias = $container->getAlias('jms_serializer.object_constructor');
        $this->assertTrue($alias->isPublic());

        $this->assertEquals('jms_serializer.doctrine_object_constructor', (string)$alias);

        $def = $container->getDefinition('jms_serializer.doctrine_object_constructor');
        $this->assertEquals('jms_serializer.doctrine_phpcr_object_constructor', (string)$def->getArgument(1));

        $def = $container->getDefinition('jms_serializer.doctrine_phpcr_object_constructor');
        $this->assertEquals('jms_serializer.unserialize_object_constructor', (string)$def->getArgument(1));
    }
}

