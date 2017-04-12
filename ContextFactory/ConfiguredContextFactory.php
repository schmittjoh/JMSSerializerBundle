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
     * Flag if we should enable the max depth exclusion strategy
     *
     * @var bool
     */
    private $enableMaxDepthChecks = false;

    /**
     * Key-value pairs with custom attributes
     *
     * @var array
     */
    private $attributes = array();

    /**
     * Serialization groups
     *
     * @var string[]
     */
    private $groups = array();

    /**
     * @param null|string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @param bool $serializeNulls
     */
    public function setSerializeNulls($serializeNulls)
    {
        $this->serializeNulls = (bool)$serializeNulls;
    }

    public function enableMaxDepthChecks()
    {
        $this->enableMaxDepthChecks = true;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param string[] $groups
     */
    public function setGroups(array $groups)
    {
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
        if (!empty($this->groups)) {
            $context->setGroups($this->groups);
        }
        if ($this->serializeNulls !== null) {
            $context->setSerializeNull($this->serializeNulls);
        }

        if ($this->enableMaxDepthChecks === true) {
            $context->enableMaxDepthChecks();
        }

        if ($this->version !== null) {
            $context->setVersion($this->version);
        }

        return $context;
    }
}
