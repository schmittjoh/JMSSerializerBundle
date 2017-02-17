<?php

namespace JMS\SerializerBundle\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class BasicSerializerFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            new ExpressionFunction('service', function ($arg) {
                return sprintf('$this->get(%s)', $arg);
            }, function (array $variables, $value) {
                return $variables['container']->get($value);
            }),
            new ExpressionFunction('parameter', function ($arg) {
                return sprintf('$this->getParameter(%s)', $arg);
            }, function (array $variables, $value) {
                return $variables['container']->getParameter($value);
            }),
            new ExpressionFunction('is_granted', function ($attribute, $object = null) {
                return sprintf('call_user_func_array(array($this->get(security.authorization_checker), isGranted), array(%s, %s))', $attribute, $object);
            }, function (array $variables, $attribute, $object = null) {
                return call_user_func_array(
                    array($variables['container']->get('security.authorization_checker'), 'isGranted'),
                    [$attribute, $object]
                );
            }),
        ];
    }
}
