# Начало работы

## Пример проекта {#example-autowiring}

Давайте рассмотрим простейший проект у которого все исходники в **src** и namespace **App**
с классами **Example** и **A** (зависимости, чтобы продемонстрировать autowiring в конструктор).

**src/Example.php**
```php
<?php

declare(strict_types=1);

namespace App;

class Example {
    public function __construct(
        private A $a,
    ) {
    }
}
```

**src/A.php**
```php
<?php

declare(strict_types=1);

namespace App;

class A {
}
```

**index.php** - Usage (Использование)
```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$example = new \App\Example(new \App\A());
var_dump($example);
```

Естественно будет настроенна автозагрузка классов с помощью composer и psr4.

**composer.json**
```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

## Установка
```
composer require cekta/di
```

## Минимальная настройка проекта. {#minimal-install}

### 1. Создаем скрипт

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
        // you configuration here, like entries, params, alias, etc.
    ))->build()
);
```

В этом скрипте вы можете осуществлять [основную конфигурацию](./configuration.md).

### 2. Генерируем Container (build)
```
php bin/build.php
```

Эту команду мы будем запускать каждый раз когда изменится наши зависимости и мы захотим актуализировать наш Container.

### 3. Используем Container

В вашей основной точке входа теперь все зависимости создаем через наш созданный контейнер

**index.php**
```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$params = []; // you current params
$container = new \App\Container($params);
var_dump($container->get(\App\Example::class));
```

⚠️ Если какие-то параметры **ИСПОЛЬЗОВАЛИСЬ** во время build, то эти параметры необходимо передать при создании 
Container.

Использовались это не значит что были объявлены, а значит что реально были использованы для разрешения entries, такие 
параметры запоминаются.

Container гарантирует все что было указано в entries на этапе build будет доступно при использовании.

## Полезные рекомендации.

### Получение параметров из одного места.

Так как параметры нужны как на этапе build так и во время использования, лучше чтобы они генерировались в одном месте и 
их можно было получать в разных местах.

**src/Config.php**
```php
<?php

declare(strict_types=1);

namespace App;

class Config
{
    public function __construct(private readonly array $env = []) 
    {
    }
    
    public function load(): array 
    {
        $json = [];
        $config = __DIR__ '/../config.json';
        if (file_exists($config)) {
            $json = json_decode(file_get_contents($config), true);
        }
        return [
            'db_username' => $this->env['DB_USERNAME'] ?? $json['db']['username'] ?? 'default username',
            // etc
        ];
    }
}
```

Наличие такого конфига решает 2 основные проблемы:
1. Позволяет получать параметры на этапе build и usage.
2. Позволяет управлять конфигурацией проекта.

Для примера реализованная простейшая конфигурация, параметр `db_username` либо берется из env `DB_USERNAME` 
если там задан, в противном случае он читается из конфигурационного файла в формате json, в остальных случаях используется 
значение по умолчанию.

Естественно в каждом проекте своя конфигурация, свое расположение конфигурационных файлов, свои форматы конфигурации и 
приоритет их определения, но задавать в одном месте очень удобно.

### Сгенерированные файлы в отдельной папке

Лучше не мешать файлы что пишутся людьми с файлами что были сгенерированными скриптами, например для сгенерированных 
файлов можно создать папку **runtime** в корне с проектом.

```
mkdir runtime
```

Внутри этой папки можно разместить readme.md что это для сгенерированных файлов, чтобы папка с этим файлом была в git.

```
echo "# For generated files" > runtime/readme.md
git add runtime/readme.md
```

Можно выделить отдельный namespace, например **App\Runtime\\** для сгенерированных файлов.

**composer.json**:
```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/",
      "App\\Runtime\\": "runtime/"
    }
  }
}
```

### Сгенерированные файлы в .gitignore

Нет смысла добавлять сгенерированные файлы в систему контроля версий (git), лучше их внести в .gitignore чтобы они 
случайно не добавились

**.gitignore**
```
runtime # в случае если сгенерированные файлы в отдельной папке (предварительно добавленный readme.md останется)
src/Container.php # в случае минимальной конфигурации
```
### Skeleton для проектов.

Имеется проект cekta/skeleton в котором сложенны лучшие практик, в том числе по cekta/di.

Вы можете использовать данный проект для ваших новых проектов или посмотреть лучшие применения в нем.