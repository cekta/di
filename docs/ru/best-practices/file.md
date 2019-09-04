## Использование файла
---
Другой вариант это вынести создание Container в отдельный и возвращать объект, подключая его по необходимости.

/app/container.php
```php
<?php
use Cekta\DI\Container;

$providers = [];
// Тут создаем провайдеры
return new Container(...$providers)
```

/public/index.php
```php
<?php
/** @noinspection PhpIncludeInspection */

use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$container = require '../app/container.php';
```

/cli.php
```php
<?php
/** @noinspection PhpIncludeInspection */

use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$container = require 'app/container.php';
```
