<?php

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\SerializerBundle\DependencyInjection\ScopedContainer;

final class FormErrorHandlerTranslationDomainPass extends PerInstancePass
{
    protected function processInstance(ScopedContainer $container): void
    {
        if (!$container->hasParameter('validator.translation_domain')) {
            return;
        }

        $container->findDefinition('jms_serializer.form_error_handler')
            ->addArgument('%validator.translation_domain%');
    }
}
