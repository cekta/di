#### Autowiring и RuleInterface

В некоторых случаях, может существовать два класса которые зависят от username, но одному надо username от mysql,
другому от redis.

[RuleInterface](/Provider/Autowiring/RuleInterface.php) позволяет задавать правила для загружаемой зависимости,
чтобы загружать зависимость с другим именем, есть простая реализация в виде [Rule](/Provider/Autowiring/Rule.php).

```php
<?php
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Container;

class DriverMysql
{
    public $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }
}
class DriverRedis
{
    public $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }
}

$providers[] = new KeyValue([
    'username' => 'mysql username',
    'redis.username' => 'redis username'
]);
$providers[] = new Autowiring(new Autowiring\Rule(DriverRedis::class, ['username' => 'redis.username']));
$container = new Container(...$providers);

$mysql = $container->get(DriverMysql::class);
assert($mysql instanceof DriverMysql);
assert($mysql->username === 'mysql username');
$redis = $container->get(DriverRedis::class);
assert($redis instanceof DriverRedis);
assert($redis->username === 'redis username');
```
---
* [Autowiring](autowiring.md):
    * [Autowiring и interface](interface.md) 
    * [Autowiring и производительность](perfomance.md) 
    * [AutowiringSimpleCache](simple-cache.md) 
    * [AutowiringCache](cache.md) 
---
[Вернуться на главную](../../readme.md)
