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
     * Context config
     *
     * @var array
     */
    private $config;

    /**
     * ConfiguredContextFactory constructor.
     *
     * @param array $config Context configuration
     */
    public function __construct(array $config)
    {
        $this->config = $config;
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
        foreach ($this->config['attributes'] as $key => $value) {
            $context->setAttribute($key, $value);
        }

        $context->setGroups($this->config['groups']);
        $context->setSerializeNull($this->config['serialize_null']);
        if ($this->config['version'] !== null) {
            $context->setVersion($this->config['version']);
        }

        return $context;
    }
}
