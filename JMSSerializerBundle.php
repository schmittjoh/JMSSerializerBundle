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

namespace JMS\SerializerBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use JMS\SerializerBundle\DependencyInjection\Compiler\CustomHandlersPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\RegisterEventListenersAndSubscribersPass;
use JMS\SerializerBundle\DependencyInjection\Compiler\ServiceMapPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use JMS\DiExtraBundle\DependencyInjection\Compiler\LazyServiceMapPass;
use Symfony\Component\DependencyInjection\Definition;

class JMSSerializerBundle extends Bundle
{
    public function build(ContainerBuilder $builder)
    {
        $builder->addCompilerPass($this->getServiceMapPass('jms_serializer.serialization_visitor', 'format',
            function(ContainerBuilder $container, Definition $def) {
                $container->getDefinition('jms_serializer.serializer')->replaceArgument(3, $def);
            }
        ));
        $builder->addCompilerPass($this->getServiceMapPass('jms_serializer.deserialization_visitor', 'format',
            function(ContainerBuilder $container, Definition $def) {
                $container->getDefinition('jms_serializer.serializer')->replaceArgument(4, $def);
            }
        ));

        $builder->addCompilerPass(new RegisterEventListenersAndSubscribersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $builder->addCompilerPass(new CustomHandlersPass(), PassConfig::TYPE_BEFORE_REMOVING);
    }

    private function getServiceMapPass($tagName, $keyAttributeName, $callable)
    {
        if (class_exists('JMS\DiExtraBundle\DependencyInjection\Compiler\LazyServiceMapPass')) {
            return new LazyServiceMapPass($tagName, $keyAttributeName, $callable);
        }

        return new ServiceMapPass($tagName, $keyAttributeName, $callable);
    }
}
