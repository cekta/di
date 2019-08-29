## Практические советы

В этом разделе я решил собрать советы которые сам использую и рекомендовал бы вам, чтобы было проще работать с библиотекой.

### Создание объекта Container

Самое первое с чем сталкиваешься это как создавать объект контейнера чтобы его можно было переиспользовать, например 
есть обработчик HTTP запросов и загрузчик CLI команд, в обоих случаях нужен один и тот же Container.

Два основных способа опишу ниже.

#### Использования класса

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

### Регистрируйте реализации интерфейсов в одном месте.

Обычно в любом проекте есть интерфейсы, где нужно указывать реализации используемые вами, я рекомендую такое место 
сделать в одном месте.

```php
<?php
/** @noinspection PhpIncludeInspection */

use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\KeyValue;

interface Logger;
class FileLogger implements Logger;
class StdoutLogger implements Logger;

$providers[] = new KeyValue(require '../app/implementation.php');
$providers[] = new Autowiring();
```

/app/implementation.php
```php
<?php
/** @noinspection PhpUndefinedClassInspection */

return [
    Logger::class => new Alias(StdoutLogger::class)
];
```

В файле implementation.php можно таким образом указывать все существующие интерфейсы и их реализации, в случае если 
потребуется что то изменить вы всегда можете открыть этот файл и поменять не трогая остальных мест.

### Используй autocomplete

Для autocomplete в PHPSTORM я использую [php di plugin](https://plugins.jetbrains.com/plugin/7694-php-di-plugin/)
который помогает делать автокомплит если я запрашиваю классы или интерфейсы у container.

## Как связать

Процесс разработки этой библиотеки полностью транслировался на youtube, 
[ссылка на плейлист](https://www.youtube.com/playlist?list=PL7Nh93imVuXyePa8PjJ1qZzkjkGFWyDZ0)

Есть чат для youtube канала и где можно задать вопрос по библиотеке [telegram](https://t.me/dev_ru)

[Мой телеграмм](https://t.me/KuvshinovEE)

Я буду очень рад вашим Pull Request, а также оставленными сообщениями об ошибках или пожеланиям по улучшениям.

---
[Вернуться на главную.](README.md)
