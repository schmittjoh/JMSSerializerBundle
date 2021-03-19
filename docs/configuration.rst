Configuration
=============

Handlers
--------
You can register any service as a handler by adding either the ``jms_serializer.handler``,
or the ``jms_serializer.subscribing_handler``.

.. code-block :: xml

    <service id="my_handler" class="MyHandler">
        <tag name="jms_serializer.handler" type="DateTime" direction="serialization" format="json"
                    method="serializeDateTimeToJson" />
    </service>

.. tip ::

    The ``direction`` attribute is not required if you want to support both directions. Likewise can the
    ``method`` attribute be omitted, then a default using the scheme ``serializeTypeToFormat``,
    or ``deserializeTypeFromFormat`` will be used for serialization or deserialization
    respectively.

Event Dispatcher
----------------
You can use the tags ``jms_serializer.event_listener``, or ``jms_serializer.event_subscriber``
in order to register a listener.

The semantics are mainly the same as registering a regular Symfony2 event listener
except that you can specify some additional attributes:

- *format*: The format that you want to listen to; defaults to all formats.
- *class*: The type name that you want to listen to; defaults to all types.
- *direction*: The direction (serialization, or deserialization); defaults to both.

.. note ::

    Events are not dispatched by Symfony2's event dispatcher as such
    you cannot register listeners with the ``kernel.event_listener`` tag,
    or the ``@DI\Observe`` annotation. Please see above.

Expression Language
-------------------

You can add custom expression functions using the `jms.expression.function_provider` tag.

.. configuration-block ::

    .. code-block :: xml

        <service id="my_function_provider" class="MyFunctionProvider">
            <tag name="jms.expression.function_provider"/>
        </service>

    .. code-block :: yaml

        my_function_provider:
            class: MyFunctionProvider
            tags:
                - jms.expression.function_provider


A functions provider for the Symfony Expression Language might look something as this:

.. code-block :: php

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

Defining Metadata
-----------------
To define the metadata using YAML or XML, you need to specify their location and to which PHP namespace prefix they refer.

.. configuration-block ::

    .. code-block :: yaml

        jms_serializer:
            metadata:
                directories:
                    App:
                        namespace_prefix: "App\\Entity"
                        path: "%kernel.project_dir%/serializer/app"
                    FOSUB:
                        namespace_prefix: "FOS\\UserBundle"
                        path: "%kernel.project_dir%/serializer/FOSUB"

    .. code-block :: xml

        <jms-serializer>
            <metadata>
                <directory namespace_prefix="App\Entity"
                           path="%kernel.project_dir%/serializer/app" />
                <directory namespace_prefix="FOS\UserBundle"
                           path="%kernel.project_dir%/serializer/FOSUB" />
            </metadata>
        </jms-serializer>

.. note ::

    - ``path`` must not contain trailing slashes
    - If you are using YAML files as metadata format, the file extension to use is ``.yml``


Suppose you want to define the metadata using YAML for the classes in the ``App\\Entity`` namespace prefix
and the configured path is ``%kernel.project_dir%/serializer/app``, then your metadata file should be named:
``%kernel.project_dir%/serializer/app/Product.yml``.


This feature is also useful for **Overriding Third-Party Metadata**.
Sometimes you want to serialize objects which are shipped by a third-party bundle.
Such a third-party bundle might not ship with metadata that suits your needs, or
possibly none, at all. In such a case, you can override the default location that
is searched for metadata with a path that is under your control.


Changing the Object Constructor
----------------------------------
A Constructor class is used to construct new objects during deserialization. The
default constructor uses the `unserialize` function to construct objects. Other
constructors are configured as services. You can set the constructor by changing
the service alias:

.. configuration-block ::

    .. code-block :: yaml

        services:
            jms_serializer.object_constructor:
                alias: jms_serializer.doctrine_object_constructor
                public: false

    .. code-block :: xml

        <services>
            <service id="jms_serializer.object_constructor" alias="jms_serializer.doctrine_object_constructor" public="false">
            </service>
        </services>

Extension Reference
-------------------

Below you find a reference of all configuration options with their default
values:

.. configuration-block ::

    .. code-block :: yaml

        # config.yml
        jms_serializer:
            handlers:
                datetime:
                    default_format: "Y-m-d\\TH:i:sP" # ATOM
                    default_timezone: "UTC" # defaults to whatever timezone set in php.ini or via date_default_timezone_set
                array_collection:
                    initialize_excluded: false

            subscribers:
                doctrine_proxy:
                    initialize_virtual_types: false
                    initialize_excluded: false

            object_constructors:
                doctrine:
                    fallback_strategy: "null" # possible values ("null" | "exception" | "fallback")

            property_naming:
                id: ~
                separator:  _
                lower_case: true

            metadata:
                cache: file
                debug: "%kernel.debug%"
                file_cache:
                    dir: "%kernel.cache_dir%/serializer"

                # Using auto-detection, the mapping files for each bundle will be
                # expected in the Resources/config/serializer directory.
                #
                # Example:
                # class: My\FooBundle\Entity\User
                # expected path: @MyFooBundle/Resources/config/serializer/Entity.User.(yml|xml|php)
                auto_detection: true

                # if you don't want to use auto-detection, you can also define the
                # namespace prefix and the corresponding directory explicitly
                directories:
                    any-name:
                        namespace_prefix: "My\\FooBundle"
                        path: "@MyFooBundle/Resources/config/serializer"
                    another-name:
                        namespace_prefix: "My\\BarBundle"
                        path: "@MyBarBundle/Resources/config/serializer"
                warmup:
                    # list of directories to scan searching for php classes to use when warming up the cache
                    paths:
                        included: []
                        excluded: []

            expression_evaluator:
                id: jms_serializer.expression_evaluator # auto detected

            default_context:
                serialization:
                    serialize_null: false
                    version: ~
                    attributes: {}
                    groups: ['Default']
                    enable_max_depth_checks: false
                deserialization:
                    serialize_null: false
                    version: ~
                    attributes: {}
                    groups: ['Default']
                    enable_max_depth_checks: false

            visitors:
                json_serialization:
                    options: 0 # json_encode options bitmask, suggested JSON_PRETTY_PRINT in development
                    depth: 512
                json_deserialization:
                    options: 0 # json_decode options bitmask
                xml_serialization:
                    format_output: false
                    version: "1.0"
                    encoding: "UTF-8"
                    default_root_name: "result"
                    default_root_ns: null
                xml_deserialization:
                    options: 0 # simplexml_load_string options bitmask
                    external_entities: false
                    doctype_whitelist:
                        - '<!DOCTYPE authorized SYSTEM "http://some_url">' # an authorized document type for xml deserialization


.. _expression function providers: https://symfony.com/doc/current/components/expression_language/extending.html#using-expression-providers
