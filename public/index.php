<?php

use App\Kernel;

$bootstrap = dirname(__DIR__).'/vendor/autoload_runtime.php';

if (file_exists('enable-maintenance.txt') || !file_exists($bootstrap)) {
    readfile('maintenance.html');
    exit;
}

include_once $bootstrap;

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
