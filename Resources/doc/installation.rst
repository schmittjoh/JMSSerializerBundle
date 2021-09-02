Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

.. code-block:: bash

    $ composer require jms/serializer-bundle

This command requires you to have Composer installed globally, as explained
in the `installation chapter <https://getcomposer.org/doc/00-intro.md>`_
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Symfony Flex Applications
^^^^^^^^^^^^^^^^^^^^^^^^^

For an application using Symfony Flex, the bundle should be automatically
enabled. If it is not, you will need to add it to the ``config/bundles.php``
file in your project:

.. code-block:: php

    <?php
    // config/bundles.php

    return [
        // ...
        JMS\SerializerBundle\JMSSerializerBundle::class => ['all' => true],
    ];


Symfony Standard Applications
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

For an application based on the Symfony Standard structure, you will need to
enable the bundle in your Kernel by adding the following line in the
``app/AppKernel.php`` file in your project:

.. code-block:: php

    <?php
    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...
                new JMS\SerializerBundle\JMSSerializerBundle(),
            );

            // ...
        }
    }
