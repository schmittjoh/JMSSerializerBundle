Expression Language
-------------------

You can add custom expression functions using the `jms.expression.function_provider` tag.

.. configuration-block::

    .. code-block:: xml

        <service id="my_function_provider" class="MyFunctionProvider">
            <tag name="jms.expression.function_provider"/>
        </service>

    .. code-block:: yaml

        my_function_provider:
            class: MyFunctionProvider
            tags:
                - jms.expression.function_provider


A functions provider for the Symfony Expression Language might look something as this:

.. code-block:: php

    use Symfony\Component\ExpressionLanguage\ExpressionFunction;
    use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

    class MyFunctionProvider implements ExpressionFunctionProviderInterface
    {
        public function getFunctions()
        {
            return [
                new ExpressionFunction('str_rot13', function ($arg) {
                    return sprintf('str_rot13(%s)', $arg);
                }, function (array $variables, $value) {
                    return str_rot13($value);
                })
            ];
        }
    }


You can read more about it on the official `expression function providers`_ documentation.

.. _expression function providers: https://symfony.com/doc/current/components/expression_language/extending.html#using-expression-providers