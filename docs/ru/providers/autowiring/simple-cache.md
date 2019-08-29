### AutowiringSimpleCache

Этот провайдер является декоратором, который перед использование Reflection пытается найти значение в
[psr/simple-cache](https://www.php-fig.org/psr/psr-16/) на продакшене каждое обращение может браться из кэша.

1. Выберите реализацию
[psr/simple-cache-implementation](https://packagist.org/providers/psr/simple-cache-implementation)
или создайте свою
2. Установите реализацию например [cache/array-adapter](https://packagist.org/packages/cache/array-adapter)
этот вариант прост для демонстрации, но для production хорошо чтобы он кэшировал в постоянное хранилище (redis,
memcached, file system и тд).
    ```
    composer require cache/array-adapter
    ```
3. Пример

```php
<?php
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cekta\DI\Container;
use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\AutowiringSimpleCache;

$cache = new ArrayCachePool();
$providers[] = new AutowiringSimpleCache($cache, new Autowiring());
$container = new Container(... $providers);

$start = microtime(true);
$container->get(stdClass::class);
$result = number_format(microtime(true) - $start, 17);
echo "$result используя Reflection и помещает в кэш" . PHP_EOL;

$start = microtime(true);
$container->get(stdClass::class);
$result = number_format(microtime(true) - $start, 17);
echo "$result последующие вызовы идут минуя Provider и Reflection" . PHP_EOL;

$container = new Container(...$providers);

$start = microtime(true);
$container->get(stdClass::class);
$result = number_format(microtime(true) - $start, 17);
echo "$result минуя Reflection используя Cache" . PHP_EOL;

$start = microtime(true);
$container->get(stdClass::class);
$result = number_format(microtime(true) - $start, 17);
echo "$result последующие вызовы идут минуя Provider и Reflection" . PHP_EOL;
```

Output:
```
0.00098490715026856 используя Reflection и помещает в кэш
0.00000500679016113 последующие вызовы идут минуя Provider и Reflection
0.00007414817810059 минуя Reflection используя Cache
0.00000405311584473 последующие вызовы идут минуя Provider и Reflection
```

Вывод времени и microtime не совсем корректный bencmark показывающий разницу, но для примера сойдет.

---
* [Autowiring](autowiring.md):
    * [Autowiring и interface](interface.md) 
    * [Autowiring и RuleInterface](rule-interface.md) 
    * [Autowiring и производительность](perfomance.md) 
    * [AutowiringCache](cache.md) 
---
[Вернуться на главную](../../readme.md)
