<?php

declare(strict_types=1);

return [
    Iterator::class => 'file not exist', // must be ignored
    stdClass::class => __DIR__ . '/with_deleted_files.php', // exist files must be loaded
];
