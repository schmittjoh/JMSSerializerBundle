<?php

namespace JMS\SerializerBundle\Templating;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\Templating\Helper\Helper;

/**
 * Serializer PHP helper
 *
 * Basically provides access to JMSSerializer from PHP templates
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 */
class SerializerHelper extends Helper
{
    protected $serializer;

    public function getName()
    {
        return 'jms_serializer';
    }

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param mixed $object
     * @param string $type
     * @return string Serialized data
     */
    public function serialize($object, $type = 'json')
    {
        return $this->serializer->serialize($object, $type);
    }
}
