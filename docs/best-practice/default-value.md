---
parent: Практические советы
nav_order: 2
---

# Значения по умолчанию

В приложение хочется хранить значения параметры по умолчанию и в различных окружениях переопределять их.

Если два провайдера предоставляют одинаковую зависимость то используется тот что передан раньше.  
Исходя из этого чем раньше задан провайдер тем выше его приоритет, это можно использовать.

```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;

$providers[] = new KeyValue(getenv());
$providers[] = new KeyValue([
    'default' => 'default value'
]);
$container = new Container(...$providers);
```

В случае если в переменной окружение передать параметр то он переопределит тот что хранится в репозитории.

Можно проверять существование файла, если он существует то читать его и переопределять значения по умолчанию например:

```php
<?php
/** @noinspection PhpComposerExtensionStubsInspection */
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;

if (is_readable('overwrite.json')) {
    $providers[] = new KeyValue(json_decode(file_get_contents('overwrite.json'), true));
}
$providers[] = new KeyValue([
    'default' => 'default value'
]);
$container = new Container(...$providers);
```

Это можно применять и комбинировать по разному.
