<?php

namespace JMS\SerializerBundle;

use JMS\SerializerBundle\DependencyInjection\Compiler\AdjustDecorationPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\CustomHandlersPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\DoctrinePass;
use JMS\SerializerBundle\DependencyInjection\Compiler\AssignVisitorsPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\ExpressionFunctionProviderPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\FormErrorHandlerTranslationDomainPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\RegisterEventListenersAndSubscribersPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\TwigExtensionPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JMSSerializerBundle extends Bundle
{
    public function build(ContainerBuilder $builder): void
    {
        $builder->addCompilerPass(new AssignVisitorsPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);

        // Should run before Symfony\Bundle\TwigBundle\DependencyInjection\Compiler\TwigEnvironmentPass
        $builder->addCompilerPass(new TwigExtensionPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);

        $builder->addCompilerPass(new FormErrorHandlerTranslationDomainPass());
        $builder->addCompilerPass(new ExpressionFunctionProviderPass());
        $builder->addCompilerPass(new DoctrinePass());

        $builder->addCompilerPass(new RegisterEventListenersAndSubscribersPass(), PassConfig::TYPE_OPTIMIZE);
        $builder->addCompilerPass(new CustomHandlersPass(), PassConfig::TYPE_OPTIMIZE);

        $builder->addCompilerPass(new AdjustDecorationPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -100);
    }
}
