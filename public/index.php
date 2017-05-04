<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../vendor/autoload.php';

// Self-called anonymous function that creates its own scope and keep the global namespace clean
(function () {
    $container = require __DIR__ . '/../config/container.php';
})();