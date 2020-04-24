---
nav_order: 2
---

# Другие форматы конфигурации

В начале мы хранили параметры конфигурации в config.json, но иногда люди предпочитают другие форматы, такие как 
yaml или env и тд.

## YAML

Рассмотрим пример с конфигурацией в yaml.

Установим yaml parser
```
composer require symfony/yaml
```

/index.php
```php 
<?php

declare(strict_types=1);

use Cekta\DI\MyContainer;

require __DIR__ . '/vendor/autoload.php';

$container = new MyContainer();
var_dump(
    $container->get('dsn'), 
    $container->get('username'), 
    $container->get('passwd'), 
    $container->get('options')
);
```

/config.yaml
```yaml
dsn: mysql:dbname=testdb;host=127.0.0.1
username: root
passwd: 12345
options: []
```

/src/MyContainer.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Provider\KeyValue;
use Symfony\Component\Yaml\Yaml;

class MyContainer extends Container
{
    public function __construct()
    {
        $providers[] = new KeyValue(Yaml::parseFile(__DIR__ . '/../config.yaml'));
        parent::__construct(...$providers);
    }
}
```

Проверим.

``` 
$ php -f index.php
/index.php:10:
string(34) "mysql:dbname=testdb;host=127.0.0.1"
/index.php:10:
string(4) "root"
/index.php:10:
int(12345)
/index.php:10:
array(0) {
}
```

Вы можете использовать любой формат для хранения конфигурации к которому у вас будет parser.
