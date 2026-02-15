# Params (Параметры)

Параметры позволяют задавать конкретные значения для аргументов зависимостей:

- Встроенные типы (`string`, `int`, `array` и т.д.)
- Переопределять значение по умолчанию
- Конкретные экземпляры объектов (например если вы создали `$logger` вы можете использовать его и дальше)

Некоторые параметры могут разрешаться в момент использования, а не во время build Container, такие параметры называются
Lazy (ленивые). С помощью таких параметров можно реализовать свою callback функцию, которая будет
генерировать зависимость в особо сложных случаях. [Подробней о Lazy (ленивых) параметрах](lazy.md).

## Пример {#example-primitive}

**src/Example.php**
```php
<?php

namespace App;

class Example
{
    public function __construct(public string $username, public string $password){}
}
```

**bin/build.php**
```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$fqcn = 'App\Container';
$filename = __DIR__ . '/../src/Container.php';

file_put_contents(
    $filename,
    (new \Cekta\DI\ContainerBuilder(
        fqcn: $fqcn,
        entries: [\App\Example::class],
        params: [
            'username' => 'some username', // глобальное имя, всем кому потребуется username, будет использовано это значение.
            \App\Example::class . '$password' => 'some password', // локальное имя, только для Example, аргумент с именем password будет иметь значение ...
        ]
    ))->build()
);
```

Параметры можно задавать [глобально и локально](dependency-naming.md#global_vs_local).  
Рекомендую задавать параметры для конкретной зависимости(локально).

**Генерируем Container** - build
```
php bin/build.php
```

**index.php** - usage
```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$params = []; // you current params
$container = new \App\Container([
    'username' => 'actual username',
    \App\Example::class . '$password' => 'actual password',
]);
$example = $container->get(\App\Example::class);

assert($example->username === 'actual username');
assert($example->password === 'actual password');
```

Обратите внимание что используется актуальное значение параметра которое передается при создании контейнера, а не то 
что было на этапе build.

Параметры, что **использовались** на этапе build считаются обязательными для создания Container, 
если вы их не передадите, получите соответствующее исключение.

Использовались != были объявлены. Использовались это значит они применялись для разрешения ``entries``.