<?php
// Expected failures (SMTP down, DB down) are logged by the handler; keep them out of test output.
ini_set('error_log', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'atomservices-tests.log');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/site.php';
require_once __DIR__ . '/../includes/contact_handler.php';
require_once __DIR__ . '/../includes/search_index.php';
require_once __DIR__ . '/Integration/HttpTestCase.php';
