Defining Metadata
-----------------
To define the metadata using YAML or XML, you need to specify their location and to which PHP namespace prefix they refer.

.. configuration-block::

    .. code-block:: yaml

        jms_serializer:
            metadata:
                directories:
                    App:
                        namespace_prefix: "App\\Entity"
                        path: "%kernel.project_dir%/serializer/app"
                    FOSUB:
                        namespace_prefix: "FOS\\UserBundle"
                        path: "%kernel.project_dir%/serializer/FOSUB"

    .. code-block:: xml

        <jms-serializer>
            <metadata>
                <directory namespace_prefix="App\Entity"
                           path="%kernel.project_dir%/serializer/app" />
                <directory namespace_prefix="FOS\UserBundle"
                           path="%kernel.project_dir%/serializer/FOSUB" />
            </metadata>
        </jms-serializer>

.. note::

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