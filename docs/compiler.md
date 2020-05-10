---
nav_order: 4
---

# Компиляция production

При всем удобстве Autowiring для разработки, он использует Reflection который можно считать медленым и на production 
можно сделать значительно эффективней используя Opcache и/или preload.

Идея очень простая:
1. Собрать имена всех классов которые используются в проекте.
2. Для каждого класса прочитать аргументы, и сгенерировать файл с результатами (компиляция).
4. Использовать скомпилированный файл.

/src/Foo.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

class Foo
{
    public function __construct(int $a)
    {
        var_dump($a);
    }
}
```

## Собрать имена классов в проекте.

Это можно сделать или в ручную перечислив имена классов в файле которые используются в проекте.

В автоматическом режиме это можно сделать с помощью composer.

``` 
$ composer dumpautoload -a --no-dev
Generating optimized autoload files (authoritative)composer/package-versions-deprecated: Generating version class...
composer/package-versions-deprecated: ...done generating version class                                                                Generated optimized autoload files (authoritative) containing 24 classes
```

С помощью этого шага, пакетный менеджер собрал все имена классов используемых в проекте в одном файле

**vendor/composer/autoload_classmap.php**

Где ключи массива это имена классов, а значения путь до файла с классом.

## Компиляция

/compiler.php
```php 
<?php

declare(strict_types=1);

use Cekta\DI\Compiler;

require __DIR__ . '/vendor/autoload.php';
$classes = array_keys(require __DIR__ . '/vendor/composer/autoload_classmap.php');
$reflection = new \Cekta\DI\Reflection();
$compiler = new Compiler($reflection);
foreach ($classes as $class) {
    $compiler->autowire($class);
}
file_put_contents(__DIR__ . '/compiled.php', $compiler->compile());
```

Выполним ее
```
$ php compiler.php 
```

После выполнения появится файл **compiled.php** который можно использовать в конфигурации.

Этап компиляции можно делать перед сборкой проекта, в последствии не обращаясь к Reflection.

## Используем скомплированный результат

/src/MyContainer.php

