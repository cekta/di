---
parent: Практические советы
title: Создание Container
nav_order: 1
---

# Навигация по странице
{: .no_toc }

1. TOC
{:toc}

# Создание Container
{: .no_toc }

Самое первое с чем сталкиваешься это как создавать объект Container чтобы его можно было переиспользовать, например 
есть обработчик HTTP запросов и загрузчик CLI команд, в обоих случаях нужен один и тот же Container.

Два основных способа:

## Использования класса

Мы наследуемся от Container и переопределяем метод конструктора создавая нужные провайдеры 
и передавая их в конструктор родителя.

/src/MyContainer.php
```php
<?php
namespace Vendor\Package;

use Cekta\DI\Container;

class MyContainer extends Container
{
    public function __construct() 
    {
        $providers = [];
        // Тут создаем провайдеры
        parent::__construct(...$providers);
    }
};
```

/public/index.php
```php
<?php
/** @noinspection PhpUndefinedClassInspection */

$container = new Vendor\Package\MyContainer();
```

/cli.php
```php
<?php
/** @noinspection PhpUndefinedClassInspection */

$container = new Vendor\Package\MyContainer();
```

## Использование файла

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
