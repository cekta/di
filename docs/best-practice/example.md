---
parent: Практические советы
nav_order: 5
---

# Пример использования

В процессе использования этой библиотеки скопился практический опыт как ее использовать.

```php
<?php
/** @noinspection ALL */

declare(strict_types=1);

namespace App;

use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\KeyValue;
use Dotenv\Dotenv;

class Container extends \Cekta\DI\Container
{
    public function __construct()
    {
        $dotenv = Dotenv::create(__DIR__ . '/../');
        $dotenv->load();
        $providers[] = new KeyValue(array_map(function ($value) {
            if (is_string($value)) {
                switch (strtolower($value)) {
                    case 'true':
                    case '(true)':
                        return true;
                    case 'false':
                    case '(false)':
                        return false;
                    case 'empty':
                    case '(empty)':
                        return '';
                    case 'null':
                    case '(null)':
                        return null;
                }
                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                    return $matches[2];
                }
            }
            return $value;
        },$_SERVER + $_ENV + getenv()));
        $providers[] = KeyValue::stringToAlias(require __DIR__ . '/../implementation.php');
        $providers[] = new KeyValue(require __DIR__ . '/../service.php');
        $providers[] = new Autowiring();
        parent::__construct(...$providers);
    }
}
```

Незабудьте создать .env и implementation.php и service.php.  
Файлы *.php должны возвращать массив, хотя бы пустой.

```php
<?php
# service.php
return [];
```

```php
<?php
# implementation.php
return [];
```

```
# .env
```

Используется [dotenv](https://packagist.org/packages/vlucas/phpdotenv) 
для чтения .env файла с параметрами по умолчанию, который можно добавить в репозиторий.

Уставнока **dotenv**

``` 
composer require vlucas/phpdotenv
```

Параметры .env файла могут быть переопределенны переменными окружения с которыми запущен процесс.

Благодаря $_SERVER + $_ENV + getenv() я объединяю все параметры и могу к ним обращаться из приложения 
по ключам.

Значение указанные в dotenv или переменных окружения являются строками 
когда мы пишем 'true' мы обычно имеем ввиду bool(true) 
для этого я использую функцию которая трансформирует такие строки, подобная функция есть в большинстве фреймворков.

В implementation.php я могу указать название интерфейса и его реализацию которая используется по умолчанию.

В service.php я могу создавать зависимости вручную, которые немогут создаваться автоматически.

Во всех остальных случаях я пытаюсь создать зависимость используя Autowiring, на основе конструктора класса.

Этот Container можно переиспользовать в различных частях приложениях для обработки http или cli запросов.

Настройки хранящиеся внутри этого контейнера могут быть расширены по желанию необходимыми провайдерами, например 
можно кэшировать Autowiring. 
