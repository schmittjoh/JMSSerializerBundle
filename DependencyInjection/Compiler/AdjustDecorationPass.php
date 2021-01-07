<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\SerializerBundle\DependencyInjection\DIUtils;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
final class AdjustDecorationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        DIUtils::adjustDecorators($container);
    }
}
