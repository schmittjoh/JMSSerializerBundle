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

namespace JMS\SerializerBundle\DependencyInjection;

use JMS\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

class Configuration implements ConfigurationInterface
{
    private $debug;

    /**
     * @param boolean $debug
     */
    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }

    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();

        $root = $tb
            ->root('jms_serializer', 'array')
                ->children()
                    ->booleanNode('enable_short_alias')->defaultTrue()->end()
        ;

        $this->addHandlersSection($root);
        $this->addSubscribersSection($root);
        $this->addObjectConstructorsSection($root);
        $this->addSerializersSection($root);
        $this->addMetadataSection($root);
        $this->addVisitorsSection($root);
        $this->addContextSection($root);

        return $tb;
    }

    private function addHandlersSection(NodeBuilder $builder)
    {
        $builder
            ->arrayNode('handlers')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('datetime')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('default_format')->defaultValue(\DateTime::ISO8601)->end()
                            ->scalarNode('default_timezone')->defaultValue(date_default_timezone_get())->end()
                            ->scalarNode('cdata')->defaultTrue()->end()
                        ->end()
                    ->end()
                    ->arrayNode('array_collection')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('initialize_excluded')->defaultTrue()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addSubscribersSection(NodeBuilder $builder)
    {
        $builder
            ->arrayNode('subscribers')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('doctrine_proxy')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('initialize_excluded')->defaultTrue()->end()
                            ->booleanNode('initialize_virtual_types')->defaultTrue()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addObjectConstructorsSection(NodeBuilder $builder)
    {
        $builder
            ->arrayNode('object_constructors')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('doctrine')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->enumNode('fallback_strategy')
                                ->defaultValue("null")
                                ->values(["null", "exception", "fallback"])
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addSerializersSection(NodeBuilder $builder)
    {
        $builder
            ->arrayNode('property_naming')
                ->addDefaultsIfNotSet()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($id) {
                        return array('id' => $id);
                    })
                ->end()
                ->children()
                    ->scalarNode('id')->cannotBeEmpty()->end()
                    ->scalarNode('separator')->defaultValue('_')->end()
                    ->booleanNode('lower_case')->defaultTrue()->end()
                    ->booleanNode('enable_cache')->defaultTrue()->end()
                ->end()
            ->end()
            ->arrayNode('expression_evaluator')
                ->addDefaultsIfNotSet()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($id) {
                        return array('id' => $id);
                    })
                ->end()
                ->children()
                    ->scalarNode('id')
                        ->defaultValue(function () {
                            if (interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
                                return 'jms_serializer.expression_evaluator';
                            }
                            return null;
                        })
                        ->validate()
                            ->always(function($v) {
                                if (!empty($v) && !interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
                                    throw new InvalidArgumentException('You need at least symfony/expression language v2.6 or v3.0 to use the expression evaluator features');
                                }
                                return $v;
                            })
                        ->end()
                ->end()
            ->end()
        ;
    }

    private function addMetadataSection(NodeBuilder $builder)
    {
        $builder
            ->arrayNode('metadata')
                ->addDefaultsIfNotSet()
                ->fixXmlConfig('directory', 'directories')
                ->children()
                    ->scalarNode('cache')->defaultValue('file')->end()
                    ->booleanNode('debug')->defaultValue($this->debug)->end()
                    ->arrayNode('file_cache')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('dir')->defaultValue('%kernel.cache_dir%/jms_serializer')->end()
                        ->end()
                    ->end()
                    ->booleanNode('auto_detection')->defaultTrue()->end()
                    ->booleanNode('infer_types_from_doctrine_metadata')
                        ->info('Infers type information from Doctrine metadata if no explicit type has been defined for a property.')
                        ->defaultTrue()
                    ->end()
                    ->arrayNode('directories')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('path')->isRequired()->end()
                                ->scalarNode('namespace_prefix')->defaultValue('')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addVisitorsSection(NodeBuilder $builder)
    {
        $builder
            ->arrayNode('visitors')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('json')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('options')
                                ->defaultValue(0)
                                ->beforeNormalization()
                                    ->ifArray()->then(function($v) {
                                        $options = 0;
                                        foreach ($v as $option) {
                                            if (is_numeric($option)) {
                                                $options |= (int) $option;
                                            } elseif (defined($option)) {
                                                $options |= constant($option);
                                            } else {
                                                throw new InvalidArgumentException('Expected either an integer representing one of the JSON_ constants, or a string of the constant itself.');
                                            }
                                        }

                                        return $options;
                                    })
                                ->end()
                                ->beforeNormalization()
                                    ->ifString()->then(function($v) {
                                        if (is_numeric($v)) {
                                            $value = (int) $v;
                                        } elseif (defined($v)) {
                                            $value = constant($v);
                                        } else {
                                            throw new InvalidArgumentException('Expected either an integer representing one of the JSON_ constants, or a string of the constant itself.');
                                        }

                                        return $value;
                                    })
                                ->end()
                                ->validate()
                                    ->always(function($v) {
                                        if (!is_int($v)) {
                                            throw new InvalidArgumentException('Expected either integer value or a array of the JSON_ constants.');
                                        }

                                        return $v;
                                    })
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('xml')
                        ->fixXmlConfig('whitelisted-doctype', 'doctype_whitelist')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('doctype_whitelist')
                                ->prototype('scalar')->end()
                            ->end()
                            ->booleanNode('format_output')
                                ->defaultTrue()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addContextSection(NodeBuilder $builder)
    {
        $root = $builder
                    ->arrayNode('default_context')
                    ->addDefaultsIfNotSet();

        $this->createContextNode($root->children(), 'serialization');
        $this->createContextNode($root->children(), 'deserialization');
    }

    private function createContextNode(NodeBuilder $builder, $name)
    {
        $builder
            ->arrayNode($name)
                ->addDefaultsIfNotSet()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($id) {
                        return array('id' => $id);
                    })
                ->end()
                ->validate()->always(function ($v) {
                    if (!empty($v['id'])) {
                        return array('id' => $v['id']);
                    }
                    return $v;
                })->end()
                ->children()
                    ->scalarNode('id')->cannotBeEmpty()->end()
                    ->scalarNode('serialize_null')
                        ->validate()->always(function ($v) {
                            if (!in_array($v, array(true, false, NULL), true)){
                                throw new InvalidTypeException("Expected boolean or NULL for the serialize_null option");
                            }
                            return $v;
                        })
                        ->ifNull()->thenUnset()
                        ->end()
                        ->info('Flag if null values should be serialized')
                    ->end()
                    ->scalarNode('enable_max_depth_checks')
                        ->info('Flag to enable the max-depth exclusion strategy')
                    ->end()
                    ->arrayNode('attributes')
                        ->fixXmlConfig('attribute')
                        ->useAttributeAsKey('key')
                        ->prototype('scalar')->end()
                        ->info('Arbitrary key-value data for context')
                    ->end()
                    ->arrayNode('groups')
                        ->fixXmlConfig('group')
                        ->prototype('scalar')->end()
                        ->info('Default serialization groups')
                    ->end()
                    ->scalarNode('version')
                        ->validate()->ifNull()->thenUnset()->end()
                        ->info('Application version to use in exclusion strategies')
                    ->end()
                ->end()
            ->end();
    }
}
