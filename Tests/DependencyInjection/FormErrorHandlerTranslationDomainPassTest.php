<?php

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use JMS\SerializerBundle\DependencyInjection\Compiler\FormErrorHandlerTranslationDomainPass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class FormErrorHandlerTranslationDomainPassTest extends TestCase
{
    /**
     * @param array $configs
     *
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

    public function testExistentParameter()
    {
        $container = $this->getContainer();
        $container->setParameter('validator.translation_domain', 'custom_domain');

        $pass = new FormErrorHandlerTranslationDomainPass();
        $pass->process($container);

        $args = $container->findDefinition('jms_serializer.form_error_handler')->getArguments();

        $this->assertArrayHasKey(1, $args);
        $this->assertContains('%validator.translation_domain%', $args[1]);
    }

    public function testNonExistentParameter()
    {
        $container = $this->getContainer();

        $pass = new FormErrorHandlerTranslationDomainPass();
        $pass->process($container);

        $args = $container->findDefinition('jms_serializer.form_error_handler')->getArguments();

        $this->assertArrayNotHasKey(1, $args);
    }
}
