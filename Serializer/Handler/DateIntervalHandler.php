<?php

namespace JMS\SerializerBundle\Serializer\Handler;

use JMS\SerializerBundle\Serializer\Handler\SubscribingHandlerInterface;
use JMS\SerializerBundle\Serializer\GraphNavigator;
use JMS\SerializerBundle\Serializer\VisitorInterface;

class DateIntervalHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        foreach (array('json', 'xml', 'yml') as $format) {
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => $format,
                'type' => 'DateInterval',
                'method' => 'serialize',
            );
        }

        return $methods;
    }

    public function serialize(VisitorInterface $visitor, \DateInterval $date, array $type)
    {
        $iso8601DateIntervalString = $this->format($date);

        return $visitor->getNavigator()->accept($iso8601DateIntervalString, null, $visitor);
    }

    public function format(\DateInterval $dateInterval)
    {
        $format = 'P';

        if (0 < $dateInterval->y) {
            $format .= $dateInterval->y.'Y';
        }

        if (0 < $dateInterval->m) {
            $format .= $dateInterval->m.'M';
        }

        if (0 < $dateInterval->d) {
            $format .= $dateInterval->d.'D';
        }

        if (0 < $dateInterval->h || 0 < $dateInterval->i || 0 < $dateInterval->s) {
            $format .= 'T';
        }

        if (0 < $dateInterval->h) {
            $format .= $dateInterval->h.'H';
        }

        if (0 < $dateInterval->i) {
            $format .= $dateInterval->i.'M';
        }

        if (0 < $dateInterval->s) {
            $format .= $dateInterval->s.'S';
        }

        return $format;
    }
}
