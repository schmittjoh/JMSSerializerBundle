Handlers
--------

You can register any service as a handler by adding either the ``jms_serializer.handler``,
or the ``jms_serializer.subscribing_handler``.

.. configuration-block::

    .. code-block:: xml

        <service id="my_handler" class="MyHandler">
            <tag name="jms_serializer.handler" type="DateTime" direction="serialization" format="json"
                        method="serializeDateTimeToJson" />
        </service>

    .. code-block:: yaml

        my_handler:
            class: MyHandler
            tags:
                - name: jms_serializer.handler
                  type: DateTime
                  direction: serialization
                  format: json
                  method: serializeDateTimeToJson

.. tip::

    The ``direction`` attribute is not required if you want to support both directions. Likewise can the
    ``method`` attribute be omitted, then a default using the scheme ``serializeTypeToFormat``,
    or ``deserializeTypeFromFormat`` will be used for serialization or deserialization
    respectively.

You can read more about it on the  `standalone library documentation`_.

.. _standalone library documentation: https://jmsyst.com/libs/serializer/master/handlers