#### Использование файла

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
---
* [Создание объекта Container](container-creation.md)
* [Использования класса](class.md)
* [Регистрирование реализации интерфейсов в одном месте](reg-in-one.md)
* [Использование autocomplete](autocomplete.md)
---
[Вернуться на главную](../readme.md)
