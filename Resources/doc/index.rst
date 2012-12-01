JMSSerializerBundle
===================

Introduction
------------
JMSSerializerBundle allows you to serialize your data into a requested
output format such as JSON, XML, or YAML, and vice versa.

You can learn more in the `documentation <http://jmsyst.com/libs/serializer>`_ for the standalone library.

Installation
------------
You can install this bundle using composer

.. code-block :: bash

    composer require jms/serializer-bundle

or add the package to your ``composer.json`` file directly.

After you have installed the package, you just need to add the bundle to your ``AppKernel.php`` file::

    // in AppKernel::registerBundles()
    $bundles = array(
        // ...
        new JMS\SerializerBundle\JMSSerializerBundle(),
        // ...
    );

Configuration
-------------
JMSSerializerBundle requires no initial configuration to get you started.

For all available configuration options, please see the :doc:`configuration reference <configuration>`.

Usage
-----
The configured serializer is available as ``jms_serializer`` service::

    $serializer = $container->get('jms_serializer');
    $serializer->serialize($data, $format);
    $data = $serializer->deserialize($inputStr, $typeName, $format);

In templates, you may also use the ``serialize`` filter:

.. code-block :: html+jinja

    {{ data | serialize }} {# serializes to JSON #}
    {{ data | serialize('json') }}
    {{ data | serialize('xml') }}

Learn more in the `documentation for the dedicated library <http://jmsyst.com/libs/serializer/master/usage>`_.

License
-------

The code is released under the business-friendly `Apache2 license`_. 

Documentation is subject to the `Attribution-NonCommercial-NoDerivs 3.0 Unported
license`_.

.. _Apache2 license: http://www.apache.org/licenses/LICENSE-2.0.html
.. _Attribution-NonCommercial-NoDerivs 3.0 Unported license: http://creativecommons.org/licenses/by-nc-nd/3.0/

