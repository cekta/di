<?php

declare(strict_types=1);

return [
    stdClass::class => __DIR__ . '/example.php',
    'some_invalid_value' => __DIR__ . '/example.php', // must be ignored because some_invalid_value class not exist
    Iterator::class => __DIR__ . '/example.php',
];
