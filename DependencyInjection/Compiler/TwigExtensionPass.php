<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\Serializer\Twig\SerializerRuntimeExtension;
use JMS\Serializer\Twig\SerializerRuntimeHelper;
use JMS\SerializerBundle\DependencyInjection\ScopedContainer;

class TwigExtensionPass extends PerInstancePass
{
    protected function processInstance(ScopedContainer $container): void
    {
        // todo allow multiple twig extensions
        if ($container->getInstanceName() !== 'default') {
            return;
        }

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
