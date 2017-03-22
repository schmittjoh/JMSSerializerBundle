<?php

namespace JMS\SerializerBundle\ContextFactory;

use JMS\Serializer\Context;
use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;

/**
 * Class ConfiguredContextFactory
 */
class ConfiguredContextFactory implements SerializationContextFactoryInterface, DeserializationContextFactoryInterface
{
    /**
     * Application version
     *
     * @var null|string
     */
    private $version;

    /**
     * Flag if we should serialize null values
     *
     * @var bool
     */
    private $serializeNulls;

    /**
     * Key-value pairs with custom attributes
     *
     * @var array
     */
    private $attributes;

    /**
     * Serialization groups
     *
     * @var string[]
     */
    private $groups;

    /**
     * ConfiguredContextFactory constructor.
     *
     * @param string|null $version        Application version
     * @param bool        $serializeNulls Flag if we should serialize null values
     * @param array       $attributes     Key-value pairs with custom attributes
     * @param string[]    $groups         Serialization groups
     */
    public function __construct($version, $serializeNulls, array $attributes, array $groups)
    {
        $this->version = $version;
        $this->serializeNulls = $serializeNulls;
        $this->attributes = $attributes;
        $this->groups = $groups;
    }

    /**
     * @inheritDoc
     */
    public function createDeserializationContext()
    {
        return $this->configureContext(new DeserializationContext());
    }

    /**
     * @inheritDoc
     */
    public function createSerializationContext()
    {
        return $this->configureContext(new SerializationContext());
    }

    /**
     * Configures context according to configuration
     *
     * @param Context $context The context
     *
     * @return Context Given object
     */
    private function configureContext(Context $context)
    {
        foreach ($this->attributes as $key => $value) {
            $context->setAttribute($key, $value);
        }

        $context->setGroups($this->groups);
        $context->setSerializeNull($this->serializeNulls);
        if ($this->version !== null) {
            $context->setVersion($this->version);
        }

        return $context;
    }
}
