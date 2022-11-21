This document details the changes that you need to make to your code
when upgrading from one version to another.

Upgrading From 4.x to 5.0
==========================

The 5.0 release allows you to define multiple serializer instances (with different configurations and metadata). 
There are no expected breaking changes in the 5.0 release, 
but the dependency injection system has been heavily changed and because of it, 
the safest option was to release a new major version.

Upgrading From 3.x to 4.0
==========================

- The twig filter has been renamed from `serialize` to `jms_serialize`.

Before:
```jinja
  {{ data | serialize }}
```
After:
```jinja
  {{ data | jms_serialize }}
```

- The services `jms_serializer.handler_registry` and `jms_serializer.event_dispatcher` now can be decorated using the Symfony 
decoration strategy. If you were doing something particular with such services, there could be a BC break.
- If you are using `friendsofsymfony/rest-bundle`, the `jms_serializer.handler_registry` is not decorated anymore, 
it will not look anymore for handlers belonging to parent classes.

Upgrading From 2.x to 3.0
==========================

- The configuration options under `jms_serializer.visitors` previously were `json` and `xml`, 
  now the options are direction specific as `json_serialization`, `json_deserialization` ,
  `xml_serialization`, `xml_deserialization`. 
   A complete list of options is available in the [configration reference](/Resources/doc/configuration.rst#extension-reference)
- Defining the naming strategy by parameters does not work any longer.

Before:
```
parameters:
    jms_serializer.serialized_name_annotation_strategy.class: JMS\Serializer\Naming\IdenticalPropertyNamingStrategy
```

After:
```
jms_serializer:
    property_naming:
        id: 'jms_serializer.identical_property_naming_strategy' # service id of the naming strategy
```

Upgrading From 1.x to 2.0
==========================

- Removed `serializer` alias, to access the serializer use the alias `jms_serializer` [#558](https://github.com/schmittjoh/JMSSerializerBundle/issues/558)
- Removed the `enable_short_alias` configuration option
- Changed the default datetime format from `ISO8601` (`Y-m-d\TH:i:sO`) to `RFC3339` (`Y-m-d\TH:i:sP`) [#494](https://github.com/schmittjoh/JMSSerializerBundle/issues/494)
- Defining not-existing metadata directories will trigger an exception [#517](https://github.com/schmittjoh/JMSSerializerBundle/issues/517)
- The "key" (or `name` attribute) for the metadata directories definition is mandatory now [#531](https://github.com/schmittjoh/JMSSerializerBundle/pull/531)
- The options `subscribers.doctrine_proxy.initialize_virtual_types`, `subscribers.doctrine_proxy.initialize_excluded` and `handlers.array_collection.initialize_excluded` now as default are `false`


Upgrading From 0.11 to 1.0
==========================
Nothing yet.

Upgrading From 0.10 to 0.11
===========================

- Namespace Changes

    The core library has been extracted to a dedicated repository ``schmittjoh/serializer``
    to make it easier re-usable in any kind of PHP project, not only in Symfony2 projects.
    This results in several namespace changes. You can adjust your projects by performing
    these replacements (in order):

    - ``JMS\SerializerBundle\Serializer`` -> ``JMS\Serializer``
    - ``JMS\SerializerBundle`` -> ``JMS\Serializer``
    - ``JMS\Serializer\DependencyInjection`` -> ``JMS\SerializerBundle\DependencyInjection``

- Dependency Changes

    You might need to increase versions of jms/di-extra-bundle, and also jms/security-extra-bundle
    depending on your stability settings. Sometimes it is also necessary to run a composer update
    twice because of a bug in composer's solving algorithm.
    

Upgrading From 0.9 to 0.10
==========================

- Custom Handlers

    The interfaces ``SerializationHandlerInterface``, and ``DeserializationHandlerInterface``
    have been removed. Instead, you can now use either an event listener, or the new handler
    concept. As a general rule, if your handler was registered for a specific type, you
    would use the new handler system, if you instead were handling an arbitrary number of
    possibly unknown types, you would use the event system.

    Please see the documentation for how to set-up one of these.

- Objects implementing Traversable

    Objects that implement the Traversable interface are not automatically treated specially
    anymore, but are serialized just like any regular object. If you would like to restore the
    previous behavior, you can either add a custom handler, or force the serialization type 
    to ``array`` using the ``@Type`` annotation (or its equivalent in XML/YML):

    ```
    /** @Type("array") */
    private $myTraversableObject;
    ```

- Configuration

    Most of the configuration under ``jms_serializer.handlers`` is gone. The order is not
    important anymore as a handler can only be registered for one specific type.

    You can still configure the built-in ``datetime`` handler though:

    ```
    jms_serializer:
        handlers:
            datetime:
                default_format: DateTime::ISO8601
                default_timezone: UTC
    ```

    This is not necessary anymore though as you can now specify the format each time when
    you use a DateTime by using the @Type annotation:

    ```
    /** @Type("DateTime<'Y-m-d', 'UTC'>") */
    private $createdAt;
    ```
