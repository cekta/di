# Начало

## Установка используя [composer](https://getcomposer.org/){:target="_blank"}

```
composer require cekta/di
```

## Использование

Рассмотрим ситуацию:
 * Необходимо сделать AuthHandler для входа на сайт по username и password.
 * AuthHandler для работы с БД будет использовать PDO для примера.
 * Параметры для подключения к бд будут лежать в config.json для примера.
 
/public/index.php - основная точка входа для демонстрации работы
```php 
<?php

declare(strict_types=1);

use Cekta\DI\AuthHandler;
use Cekta\DI\MyContainer;

require __DIR__ . '/../vendor/autoload.php';

$container = new MyContainer();
/** @var AuthHandler $auth */
$auth = $container->get(AuthHandler::class);
var_dump($auth->handle('test', '12345'));
```

/src/MyContainer.php - здесь лежит основная настройка иньекции зависимостей
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Container;
use Cekta\DI\Provider;
use Cekta\DI\Reflection;

class MyContainer extends Container
{
    public function __construct()
    {
        $reflection = new Reflection();
        $providers[] = new Provider\FileJSON(__DIR__ . '/../config.json');
        $providers[] = new Provider\Autowiring($reflection);
        parent::__construct(...$providers);
    }
}
```

/src/AuthHandler.php - пример кода куда надо внедрить зависимости
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

use PDO;

class AuthHandler
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function handle(string $username, string $password): bool
    {
        // просто пример
        $sth = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $sth->execute([$username]);
        $user = $sth->fetch(PDO::FETCH_ASSOC);
        return !empty($user) && password_verify($password, $user['password']);
    }
}
```

/config.json - параметры для подключения к бд
```json
{
  "dsn": "mysql:dbname=testdb;host=127.0.0.1",
  "username": "root",
  "passwd": "1234",
  "options": {}
}
```

Если поднять БД, создать таблицу users и вписать туда пользователя test и пароль password_hash('12345', PASSWORD_DEFAULT).

```
$ php -f public/index.php 
/public/index.php:13:
bool(true)
```

## Полезное

 * MyContainer может содержать любое число провайдеров.
 * MyContainer хранит основные настройки cekta/di.
 * Расширение функционала и возможностей осуществляются с помощью провайдеров
 * Порядок провайдеров важен, если два провайдера предоставляют контейнер с одинаковыми именами, 
    используется тот что добавлен раньше.

Смотрите другие разделы для знакомства с другими возможностями.
