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

Overriding Third-Party Metadata
-------------------------------
Sometimes you want to serialize objects which are shipped by a third-party bundle.
Such a third-party bundle might not ship with metadata that suits your needs, or
possibly none, at all. In such a case, you can override the default location that
is searched for metadata with a path that is under your control.

.. configuration-block ::

    .. code-block :: yaml

        jms_serializer:
            metadata:
                directories:
                    FOSUB:
                        namespace_prefix: "FOS\\UserBundle"
                        path: "%kernel.root_dir%/serializer/FOSUB"

    .. code-block :: xml

        <jms-serializer>
            <metadata>
                <directory namespace_prefix="FOS\UserBundle"
                           path="%kernel.root_dir%/serializer/FOSUB" />
            </metadata>
        </jms-serializer>

Changing the Object Constructor
----------------------------------
A Constructor class is used to construct new objects during deserialization. The 
default constructor uses the `unserialize` function to construct objects. Other
constructors are configured as services. You can set the constructor by changing 
the service alias:

.. code-block :: yaml

    services:
        jms_serializer.object_constructor:
            alias: jms_serializer.doctrine_object_constructor
            public: false

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
                    default_format: "c" # ISO8601
                    default_timezone: "UTC" # defaults to whatever timezone set in php.ini or via date_default_timezone_set

            property_naming:
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

            visitors:
                json:
                    options: 0 # json_encode options bitmask
                xml:
                    doctype_whitelist:
                        - '<!DOCTYPE authorized SYSTEM "http://some_url">' # an authorized document type for xml deserialization

    .. code-block :: xml

        <!-- config.xml -->
        <jms-serializer>
            <handlers>
                <object-based />
                <datetime
                    format="Y-mdTH:i:s"
                    default-timezone="UTC" />
                <array-collection />
                <form-error />
                <constraint-violation />
            </handlers>

            <property-naming
                seperator="_"
                lower-case="true" />

            <metadata
                cache="file"
                debug="%kernel.debug%"
                auto-detection="true">

                <file-cache dir="%kernel.cache_dir%/serializer" />

                <!-- If auto-detection is enabled, mapping files for each bundle will
                     be expected in the Resources/config/serializer directory.

                     Example:
                     class: My\FooBundle\Entity\User
                     expected path: @MyFooBundle/Resources/config/serializer/Entity.User.(yml|xml|php)
                -->
                <directory
                    namespace-prefix="My\FooBundle"
                    path="@MyFooBundle/Resources/config/serializer" />
            </metadata>

            <visitors>
                <xml>
                    <whitelisted-doctype><![CDATA[<!DOCTYPE...>]]></whitelisted-doctype>
                    <whitelisted-doctype><![CDATA[<!DOCTYPE...>]]></whitelisted-doctype>
                </xml>
            </visitors>
        </jms-serializer>
