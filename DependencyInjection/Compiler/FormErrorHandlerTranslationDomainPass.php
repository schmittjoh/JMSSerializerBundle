<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection\Compiler;

use JMS\SerializerBundle\DependencyInjection\ScopedContainer;

/**
 * @internal
 */
final class FormErrorHandlerTranslationDomainPass extends PerInstancePass
{
    protected function processInstance(ScopedContainer $container): void
    {
        if (!$container->hasParameter('validator.translation_domain')) {
            return;
        }

        $container->findDefinition('jms_serializer.form_error_handler')
            ->setArgument(1, '%validator.translation_domain%');
    }
}
