Changing the Object Constructor
----------------------------------
A Constructor class is used to construct new objects during deserialization. The
default constructor uses the `unserialize` function to construct objects. Other
constructors are configured as services. You can set the constructor by changing
the service alias:

.. configuration-block::

    .. code-block:: yaml

        services:
            jms_serializer.object_constructor:
                alias: jms_serializer.doctrine_object_constructor
                public: false

    .. code-block:: xml

        <services>
            <service id="jms_serializer.object_constructor" alias="jms_serializer.doctrine_object_constructor" public="false">
            </service>
        </services>

You can read more about it on the  `standalone library documentation`_.

.. _standalone library documentation: https://jmsyst.com/libs/serializer/master/cookbook/object_constructor