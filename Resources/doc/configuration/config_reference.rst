Bundle Config Reference
-----------------------

Below you find a reference of all configuration options with their default values:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/jms_serializer.yaml
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

                include_interfaces: false
                infer_types_from_doc_block: false

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
