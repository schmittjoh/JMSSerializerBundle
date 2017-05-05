<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('twig.runtime_loader')
            || !class_exists($container->getParameter('jms_serializer.twig_runtime_extension.class'))
            || !interface_exists('Twig_RuntimeLoaderInterface')
            || !class_exists($container->getParameter('jms_serializer.twig_runtime_extension_helper.class'))
        ) {
            $container->removeDefinition('jms_serializer.twig_extension.serializer_runtime_helper');
            return;
        }

        $def = $container->getDefinition('jms_serializer.twig_extension.serializer');
        $def->setClass('%jms_serializer.twig_runtime_extension.class%');
        $def->setArguments(array());
    }
}
