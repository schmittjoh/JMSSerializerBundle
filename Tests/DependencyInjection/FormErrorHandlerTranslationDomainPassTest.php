<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
