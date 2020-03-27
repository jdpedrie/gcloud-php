<?php

use Google\Cloud\Core\Testing\TestHelpers;
use Google\Cloud\Datastore\Tests\System\DatastoreTestCase;

$hasVendor = false;
foreach ([
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
] as $path) {
    $hasVendor = realpath($path);
    if ($hasVendor) {
        require $hasVendor;
        break;
    }
}

if (!$hasVendor) {
    trigger_error("dependencies not installed or could not be loaded", E_USER_ERROR);
}

TestHelpers::requireKeyfile('GOOGLE_CLOUD_PHP_TESTS_KEY_PATH');
TestHelpers::generatedSystemTestBootstrap();
TestHelpers::SystemBootstrap();
TestHelpers::systemTestShutdown(function () {
    DatastoreTestCase::tearDownFixtures();
});
