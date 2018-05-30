<?php

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use JMS\SerializerBundle\DependencyInjection\Compiler\TwigExtensionPass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigExtensionPassTest extends TestCase
{
    /**
     * @return ContainerBuilder
     */
    private function getContainer()
    {
        $loader = new JMSSerializerExtension();
        $container = new ContainerBuilder();

        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/serializer');
        $container->setParameter('kernel.bundles', array());

        $loader->load([[]], $container);
        return $container;
    }

    public function testStandardExtension()
    {
        $container = $this->getContainer();

        $pass = new TwigExtensionPass();
        $pass->process($container);

        $extension = $container->getDefinition('jms_serializer.twig_extension.serializer');
        $this->assertCount(1, $extension->getArguments());

        $this->assertFalse($container->hasDefinition('jms_serializer.twig_extension.serializer_runtime_helper'));
    }

    public function testLazyExtension()
    {
        if (!interface_exists('Twig_RuntimeLoaderInterface')) {
            $this->markTestSkipped("Lazy extensions are supported only by serializer 1.7.0");
        }
        $container = $this->getContainer();

        $container->register('twig.runtime_loader');

        $pass = new TwigExtensionPass();
        $pass->process($container);

        $extension = $container->getDefinition('jms_serializer.twig_extension.serializer');
        $this->assertCount(0, $extension->getArguments());

        $this->assertTrue($container->hasDefinition('jms_serializer.twig_extension.serializer_runtime_helper'));
    }
}

