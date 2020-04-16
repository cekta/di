---
nav_order: 2
---

# Переменные окружения (ENV)

В некоторых случаях параметры(например для подключения к БД) удобней задавать из env.

/index.php
```php 
<?php

declare(strict_types=1);

use Cekta\DI\MyContainer;

require __DIR__ . '/vendor/autoload.php';

$container = new MyContainer();
$container->get(\Cekta\DI\Example::class);
```

/src/MyContainer.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\Environment;

class MyContainer extends Container
{
    public function __construct()
    {
        $reflection = new Reflection();
        $providers[] = new Environment($_ENV + getenv());
        $providers[] = new Autowiring($reflection);
        parent::__construct(...$providers);
    }
}
```

/src/Example.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

class Example
{
    public function __construct(string $KEY)
    {
        var_dump($KEY);
    }
}
```

Проверим
```
$ export KEY="its works"
$ php -f index.php 
/src/Example.php:11:
string(9) "its works"

$ export KEY="new value"
$ php -f index.php 
/src/Example.php:11:
string(9) "new value"
```

## Приведение типов

При работе с переменными окружения нужно помнить что функция getenv возвращает строку и если вам потребуется 
привести результат к нужному типу можно использовать трансформацию.

/index.php
```php 
<?php

declare(strict_types=1);

var_dump(getenv('TEST'));
```

пример отсутвия приведения типов
```
$ export TEST=value
$ php -f index.php 
/index.php:5:
string(5) "value"

$ export TEST=true
$ php -f index.php 
/home/smpl/project/di/index.php:5:
string(4) "true"
```

Провайдер Environment автоматически конвертирует:
```php 
<?php
[
    'true' => true,
    '(true)' => true,
    'false' => false,
    '(false)' => false,
    'null' => null,
    '(null)' => null,
    'empty' => '',
    '(empty)' => '',
]
```

Проверим конвертирование.

/index.php
```php 
<?php

declare(strict_types=1);

use Cekta\DI\MyContainer;

require __DIR__ . '/vendor/autoload.php';

$container = new MyContainer();
var_dump($container->get('TEST'));
```

```
$ export TEST=value
$ php -f index.php 
/index.php:10:
string(5) "value"

$ export TEST=true
$ php -f index.php
/index.php:10:
bool(true)
```

Если вам не нужно конвертировать значения, используйте KeyValue provider.
