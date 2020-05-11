<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\Serializer\Twig\SerializerRuntimeExtension;
use JMS\Serializer\Twig\SerializerRuntimeHelper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('jms_serializer.twig_extension.serializer_runtime_helper')) {
            return;
        }

        if (!$container->hasDefinition('twig.runtime_loader')
            || !class_exists(SerializerRuntimeExtension::class)
            || !(interface_exists('Twig\RuntimeLoader\RuntimeLoaderInterface') || interface_exists('Twig_RuntimeLoaderInterface'))
            || !class_exists(SerializerRuntimeHelper::class)
        ) {
            $container->removeDefinition('jms_serializer.twig_extension.serializer_runtime_helper');
            return;
        }

        $def = $container->findDefinition('jms_serializer.twig_extension.serializer');
        $def->setClass(SerializerRuntimeExtension::class);
        $def->setArguments(array());
    }
}
