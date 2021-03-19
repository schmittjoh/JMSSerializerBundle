<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\Tests\DependencyInjection;

use JMS\SerializerBundle\DependencyInjection\Compiler\FormErrorHandlerTranslationDomainPass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FormErrorHandlerTranslationDomainPassTest extends TestCase
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
        $this->assertSame('%validator.translation_domain%', $args[1]);
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
