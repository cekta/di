---
nav_order: 3
---

# Container provider

В проекте уже может существовать и использоваться реализация [psr/container](https://www.php-fig.org/psr/psr-11/), 
любую такую реализацию можно рассмотреть как провайдер для моего контейнера.

Можно делать вложенными и мои контейнеры.

Все это позволяет сделать **ContainerAdapter**

/src/MyContainer.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Provider\ContainerAdapter;
use Cekta\DI\Provider\KeyValue;

class MyContainer extends Container
{
    public function __construct()
    {
        $container = new Container(new KeyValue(['key' => 'value']));
        $providers[] = new ContainerAdapter($container);
        parent::__construct(...$providers);
    }
}
```

/public/index.php
```php 
<?php

declare(strict_types=1);

use Cekta\DI\MyContainer;

require __DIR__ . '/../vendor/autoload.php';

$container = new MyContainer();
var_dump($container->get('key'));

```

Проверим
``` 
$ php public/index.php 
/public/index.php:10:
string(5) "value"
```

Мы можем вкладывать таким образом любые реализации **psr/container** друг в друга.
