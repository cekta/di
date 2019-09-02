#### KeyValue из произвольного формата

В мире существует огромное число различных форматов и вы можете использовать любой к которому у вас есть парсер.

Например для загрузки из YAML

Установим yaml parser
```
composer require symfony/yaml
```

/config.yaml
```yaml
username: root
```

/index.php
```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;
use Symfony\Component\Yaml\Yaml;

$providers[] = new KeyValue(Yaml::parseFile(__DIR__ . '/config.yaml'));
$container = new Container(...$providers);
assert($container->get('username') === 'root');
```
---
* [KeyValue](key-value.md)
* [Environment](environment.md)
* [JSON](json.md)
* [PHP](PHP.md)
* [LoaderInterface](loader-interface.md)
* [ClosureToService](closureToService.md)
---
[Вернуться на главную](../../readme.md)
