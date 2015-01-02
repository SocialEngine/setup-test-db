<?php

// bootstrap/testing.php
$testEnv = (getenv('APP_ENV')) ? : 'testing';

passthru("php " . __DIR__ . "/../artisan test:setup-db --env={$testEnv}");

require __DIR__ . '/autoload.php';
