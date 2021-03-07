<?php

declare(strict_types=1);

use Doctrine\Common\Annotations\AnnotationRegistry;

call_user_func(static function () {
    if (!is_file($autoloadFile = __DIR__ . '/../vendor/autoload.php')) {
        throw new RuntimeException('Did not find vendor/autoload.php. Did you run "composer install --dev"?');
    }

    require_once $autoloadFile;

    AnnotationRegistry::registerLoader('class_exists');
});
