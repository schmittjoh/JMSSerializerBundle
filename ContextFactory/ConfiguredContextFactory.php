<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\ContextFactory;

use JMS\Serializer\Context;
use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;

class ConfiguredContextFactory implements SerializationContextFactoryInterface, DeserializationContextFactoryInterface
{
    /**
     * Application version
     *
     * @var string|null
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
    private $attributes = [];

    /**
     * Serialization groups
     *
     * @var string[]
     */
    private $groups = [];

    /**
     * @param string|null $version
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
        $this->serializeNulls = (bool) $serializeNulls;
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

    public function createDeserializationContext(): DeserializationContext
    {
        return $this->configureContext(new DeserializationContext());
    }

    public function createSerializationContext(): SerializationContext
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

        if (($context instanceof SerializationContext) && null !== $this->serializeNulls) {
            $context->setSerializeNull($this->serializeNulls);
        }

        if (true === $this->enableMaxDepthChecks) {
            $context->enableMaxDepthChecks();
        }

        if (null !== $this->version) {
            $context->setVersion($this->version);
        }

        return $context;
    }
}
