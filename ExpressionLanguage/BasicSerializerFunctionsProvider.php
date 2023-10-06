<?php

declare(strict_types=1);

namespace JMS\SerializerBundle\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class BasicSerializerFunctionsProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('service', static function ($arg) {
                return sprintf('$this->get(%s)', $arg);
            }, static function (array $variables, $value) {
                return $variables['container']->get($value);
            }),
            new ExpressionFunction('parameter', static function ($arg) {
                return sprintf('$this->getParameter(%s)', $arg);
            }, static function (array $variables, $value) {
                return $variables['container']->getParameter($value);
            }),
            new ExpressionFunction('is_granted', static function ($attribute, $object = null) {
                return sprintf('call_user_func_array(array($this->get(\'jms_serializer.authorization_checker\'), \'isGranted\'), array(%s, %s))', $attribute, $object);
            }, static function (array $variables, $attribute, $object = null) {
                return call_user_func_array(
                    [$variables['container']->get('jms_serializer.authorization_checker'), 'isGranted'],
                    [$attribute, $object]
                );
            }),
        ];
    }
}
