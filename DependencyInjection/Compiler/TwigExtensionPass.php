<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
final class TwigExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // if there is no support for twig runtime extensions, remove the services
        if (!$container->hasDefinition('twig.runtime_loader')) {
            $container->removeDefinition('jms_serializer.twig_extension.runtime_serializer');
            $container->removeDefinition('jms_serializer.twig_extension.serializer_runtime_helper');
        }

        // if there is no twig, remove the standard extension
        // or twig is there with runtime extensions, thus the "legacy" extensions are not needed
        if (!$container->hasDefinition('twig') || $container->hasDefinition('twig.runtime_loader')) {
            $container->removeDefinition('jms_serializer.twig_extension.serializer');
        }
    }
}
