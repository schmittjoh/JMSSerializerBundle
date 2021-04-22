Event Listener/Subscriber
-------------------------

You can use the tags ``jms_serializer.event_listener``, or ``jms_serializer.event_subscriber``
in order to register a listener.

The semantics are mainly the same as registering a regular Symfony2 event listener
except that you can specify some additional attributes:

- *format*: The format that you want to listen to; defaults to all formats.
- *class*: The type name that you want to listen to; defaults to all types.
- *direction*: The direction (serialization, or deserialization); defaults to both.

.. configuration-block::

    .. code-block:: yaml

        Acme\MyEventSubscriber:
            tags:
                - name: jms_serializer.event_listener
                  event: serializer.pre_deserialize
                  class: 'Acme\BarClass'
                  format: json
                  method: doStuff

.. note::

    Events are not dispatched by Symfony2's event dispatcher as such
    you cannot register listeners with the ``kernel.event_listener`` tag,
    or the ``@DI\Observe`` annotation. Please see above.

You can read more about it on the  `standalone library documentation`_.

.. _standalone library documentation: https://jmsyst.com/libs/serializer/master/event_system


Autowiring
^^^^^^^^^^
To register event subscriber automatically the class should implement `JMS\Serializer\EventDispatcher\EventSubscriberInterface`.

.. code-block:: php

    namespace Acme;

    use JMS\Serializer\EventDispatcher\EventSubscriberInterface;

    class MyEventSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            return [
                [
                    'event' => 'serializer.pre_deserialize',
                    'class' => 'Acme\BarClass',
                    'format' => 'json',
                    'method' => 'doStuff',
                ],
            ];
        }
    }

Learn more in the `autowiring documentation <https://symfony.com/doc/current/service_container/autowiring.html>`_.