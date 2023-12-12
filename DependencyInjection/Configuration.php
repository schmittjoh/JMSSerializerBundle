<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\DependencyInjection;

use JMS\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

/**
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    private $debug;

    /**
     * @param bool $debug
     */
    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tb = new TreeBuilder('jms_serializer');

        if (method_exists($tb, 'getRootNode')) {
            $root = $tb->getRootNode()->children();
        } else {
            $root = $tb->root('jms_serializer')->children();
        }

        $root->scalarNode('twig_enabled')->defaultValue('default')->end();
        $this->addProfilerSection($root);
        $this->addConfigNodes($root);

        $instanceRoot = $root->arrayNode('instances')
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children();

        $instanceRoot->booleanNode('inherit')->defaultFalse()->end();

        $this->addConfigNodes($instanceRoot);

        return $tb;
    }

    private function addConfigNodes($root): void
    {
        $root->scalarNode('enum_support')->defaultValue(false)->end();
        $root->scalarNode('default_value_property_reader_support')->defaultValue(false)->end();
        $this->addHandlersSection($root);
        $this->addSubscribersSection($root);
        $this->addObjectConstructorsSection($root);
        $this->addSerializersSection($root);
        $this->addMetadataSection($root);
        $this->addVisitorsSection($root);
        $this->addContextSection($root);
    }

    private function addProfilerSection(NodeBuilder $builder): void
    {
        $builder->scalarNode('profiler')
            ->defaultValue($this->debug)
            ->validate()
            ->always(static function ($v): ?bool {
                if (!is_bool($v) && null !== $v) {
                    throw new InvalidArgumentException('The profiler setting must be null or a boolean');
                }

                return $v;
            })
            ->end()
            ->end();
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
                            ->scalarNode('default_format')->defaultValue(\DateTime::RFC3339)->end()
                            ->scalarNode('default_timezone')->defaultValue(date_default_timezone_get())->end()
                            ->scalarNode('cdata')->defaultTrue()->end()
                        ->end()
                    ->end()
                    ->arrayNode('array_collection')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('initialize_excluded')->defaultFalse()->end()
                        ->end()
                    ->end()
                    ->arrayNode('symfony_uid')
                        ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('default_format')->defaultValue('canonical')->end() // Same as JMS\Serializer\Handler\SymfonyUidHandler::FORMAT_CANONICAL
                                ->scalarNode('cdata')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
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
                            ->booleanNode('initialize_excluded')->defaultFalse()->end()
                            ->booleanNode('initialize_virtual_types')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addObjectConstructorsSection(NodeBuilder $builder)
    {
        $builder
            ->arrayNode('object_constructors')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('doctrine')
                        ->canBeDisabled()
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->enumNode('fallback_strategy')
                                ->defaultValue('null')
                                ->values(['null', 'exception', 'fallback'])
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addSerializersSection(NodeBuilder $builder)
    {
        $builder
            ->arrayNode('property_naming')
                ->addDefaultsIfNotSet()
                ->beforeNormalization()
                    ->ifString()
                    ->then(static function ($id) {
                        return ['id' => $id];
                    })
                ->end()
                ->children()
                    ->scalarNode('id')->cannotBeEmpty()->end()
                    ->scalarNode('separator')->defaultValue('_')->end()
                    ->booleanNode('lower_case')->defaultTrue()->end()
                ->end()
            ->end()
            ->arrayNode('expression_evaluator')
                ->addDefaultsIfNotSet()
                ->beforeNormalization()
                    ->ifString()
                    ->then(static function ($id) {
                        return ['id' => $id];
                    })
                ->end()
                ->children()
                    ->scalarNode('id')
                        ->defaultValue(static function () {
                            if (interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
                                return 'jms_serializer.expression_evaluator';
                            }

                            return null;
                        })
                        ->validate()
                            ->always(static function ($v) {
                                if (!empty($v) && !interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
                                    throw new InvalidArgumentException('You need at least symfony/expression-language v2.6 or v3.0 to use the expression evaluator features');
                                }

                                return $v;
                            })
                        ->end()
                ->end()
            ->end();
    }

    private function addMetadataSection(NodeBuilder $builder)
    {
        $builder
            ->arrayNode('metadata')
                ->addDefaultsIfNotSet()
                ->fixXmlConfig('directory', 'directories')
                ->children()

                    ->arrayNode('warmup')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('paths')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->arrayNode('included')
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('excluded')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()

                    ->scalarNode('cache')->defaultValue('file')->end()
                    ->booleanNode('debug')->defaultValue($this->debug)->end()
                    ->arrayNode('file_cache')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('dir')->defaultValue(null)->end()
                        ->end()
                    ->end()
                    ->booleanNode('include_interfaces')->defaultFalse()->end()
                    ->booleanNode('auto_detection')->defaultTrue()->end()
                    ->booleanNode('infer_types_from_doc_block')->defaultFalse()->end()
                    ->booleanNode('infer_types_from_doctrine_metadata')
                        ->info('Infers type information from Doctrine metadata if no explicit type has been defined for a property.')
                        ->defaultTrue()
                    ->end()
                    ->arrayNode('directories')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('path')->isRequired()->end()
                                ->scalarNode('namespace_prefix')->defaultValue('')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addVisitorsSection(NodeBuilder $builder)
    {
        $arrayNormalization = static function ($v) {
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
        };
        $stringNormalization = static function ($v) {
            if (is_numeric($v)) {
                $value = (int) $v;
            } elseif (defined($v)) {
                $value = constant($v);
            } else {
                throw new InvalidArgumentException('Expected either an integer representing one of the JSON_ constants, or a string of the constant itself.');
            }

            return $value;
        };
        $arrayNormalizationXML = static function ($v) {
            $options = 0;
            foreach ($v as $option) {
                if (is_numeric($option)) {
                    $options |= (int) $option;
                } elseif (defined($option)) {
                    $options |= constant($option);
                } else {
                    throw new InvalidArgumentException('Expected either an integer representing one of the LIBXML_ constants, or a string of the constant itself.');
                }
            }

            return $options;
        };
        $stringNormalizationXML = static function ($v) {
            if (is_numeric($v)) {
                $value = (int) $v;
            } elseif (defined($v)) {
                $value = constant($v);
            } else {
                throw new InvalidArgumentException('Expected either an integer representing one of the LIBXML_ constants, or a string of the constant itself.');
            }

            return $value;
        };

        $jsonValidation = static function ($v) {
            if (!is_int($v)) {
                throw new InvalidArgumentException('Expected either integer value or a array of the JSON_ constants.');
            }

            return $v;
        };
        $xmlValidation = static function ($v) {
            if (!is_int($v)) {
                throw new InvalidArgumentException('Expected either integer value or a array of the LIBXML_ constants.');
            }

            return $v;
        };

        $builder
            ->arrayNode('visitors')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('json_serialization')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('depth')->end()
                            ->scalarNode('options')
                                ->defaultValue(1024 /*JSON_PRESERVE_ZERO_FRACTION*/)
                                ->beforeNormalization()
                                    ->ifArray()->then($arrayNormalization)
                                ->end()
                                ->beforeNormalization()
                                    ->ifString()->then($stringNormalization)
                                ->end()
                                ->validate()
                                    ->always($jsonValidation)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('json_deserialization')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('options')
                                ->defaultValue(0)
                                ->beforeNormalization()
                                    ->ifArray()->then($arrayNormalization)
                                ->end()
                                ->beforeNormalization()
                                    ->ifString()->then($stringNormalization)
                                ->end()
                                ->validate()
                                    ->always($jsonValidation)
                                ->end()
                            ->end()
                            ->booleanNode('strict')
                                ->defaultValue(false)
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('xml_serialization')
                        ->fixXmlConfig('whitelisted-doctype', 'doctype_whitelist')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('version')
                            ->end()
                            ->scalarNode('encoding')
                            ->end()
                            ->booleanNode('format_output')
                                ->defaultFalse()
                            ->end()
                            ->scalarNode('default_root_name')
                            ->end()
                            ->scalarNode('default_root_ns')
                                ->defaultValue('')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('xml_deserialization')
                        ->fixXmlConfig('whitelisted-doctype', 'doctype_whitelist')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('doctype_whitelist')
                                ->prototype('scalar')->end()
                            ->end()
                            ->booleanNode('external_entities')
                                ->defaultFalse()
                            ->end()
                            ->scalarNode('options')
                                ->defaultValue(0)
                                ->beforeNormalization()
                                    ->ifArray()->then($arrayNormalizationXML)
                                ->end()
                                ->beforeNormalization()
                                    ->ifString()->then($stringNormalizationXML)
                                ->end()
                                ->validate()
                                    ->always($xmlValidation)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
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
                    ->then(static function ($id) {
                        return ['id' => $id];
                    })
                ->end()
                ->validate()->always(static function ($v) {
                    if (!empty($v['id'])) {
                        return ['id' => $v['id']];
                    }

                    return $v;
                })->end()
                ->children()
                    ->scalarNode('id')->cannotBeEmpty()->end()
                    ->scalarNode('serialize_null')
                        ->validate()->always(static function ($v) {
                            if (!in_array($v, [true, false, null], true)) {
                                throw new InvalidTypeException('Expected boolean or NULL for the serialize_null option');
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
