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

use JMS\SerializerBundle\DependencyInjection\Compiler\TwigExtensionPass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use Symfony\Component\DependencyInjection\Compiler\RemoveUnusedDefinitionsPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveParameterPlaceHoldersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigExtensionPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return ContainerBuilder
     */
    private function getContainer()
    {
        $loader = new JMSSerializerExtension();
        $container = new ContainerBuilder();

        $container->getCompilerPassConfig()->setOptimizationPasses(array(
            new ResolveParameterPlaceHoldersPass(),
            new ResolveDefinitionTemplatesPass(),
        ));
        $container->getCompilerPassConfig()->setRemovingPasses(array(new RemoveUnusedDefinitionsPass()));

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
        $this->assertEquals('%jms_serializer.twig_extension.class%', (string)$extension->getClass());
        $this->assertCount(1, $extension->getArguments());

        $this->assertFalse($container->hasDefinition('jms_serializer.twig_extension.serializer_runtime_helper'));
    }

    public function testLazyExtension()
    {
        if (
            !class_exists('JMS\Serializer\Twig\SerializerRuntimeExtension')
            || !interface_exists('Twig_RuntimeLoaderInterface')
        ) {
            $this->markTestSkipped("Lazy extensions are supported only by serializer 1.7.0");
        }
        $container = $this->getContainer();

        $container->register('twig.runtime_loader');

        $pass = new TwigExtensionPass();
        $pass->process($container);

        $extension = $container->getDefinition('jms_serializer.twig_extension.serializer');
        $this->assertEquals('%jms_serializer.twig_runtime_extension.class%', (string)$extension->getClass());
        $this->assertCount(0, $extension->getArguments());

        $this->assertTrue($container->hasDefinition('jms_serializer.twig_extension.serializer_runtime_helper'));
    }

    public function testLazyExtensionNotLoadedWhenOldSerializer()
    {
        $container = $this->getContainer();

        $container->getParameterBag()->add(array(
            'jms_serializer.twig_runtime_extension.class' => 'foo',
            'jms_serializer.twig_runtime_extension_helper.class' => 'bar',
        ));

        $container->register('twig.runtime_loader');

        $pass = new TwigExtensionPass();
        $pass->process($container);

        $extension = $container->getDefinition('jms_serializer.twig_extension.serializer');
        $this->assertEquals('%jms_serializer.twig_extension.class%', (string)$extension->getClass());
        $this->assertCount(1, $extension->getArguments());

        $this->assertFalse($container->hasDefinition('jms_serializer.twig_extension.serializer_runtime_helper'));
    }
}

