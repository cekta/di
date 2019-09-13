---
parent: Практические советы
nav_order: 4
---

# Реализации интерфейсов.

Обычно в любом проекте есть интерфейсы, нужно указывать реализации используемые вами, я рекомендую это вынести в 
отдельный фаил (implementation.php).

```php
<?php
/** @noinspection PhpIncludeInspection */

use Cekta\DI\Container;use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\KeyValue;

interface Logger;
class FileLogger implements Logger;
class StdoutLogger implements Logger;

$providers[] = KeyValue::stringToAlias(require '../app/implementation.php');
$providers[] = new Autowiring();
$container = new Container(...$providers);
assert($container->get(Logger::class) instanceof StdoutLogger);
```

/app/implementation.php
```php
<?php
/** @noinspection PhpUndefinedClassInspection */

return [
    Logger::class => StdoutLogger::class
];
```

В файле implementation.php будет хранится информация о всех интерфейсах и их реализациях используемых в вашем 
приложении.  
В случае необходимости вы можете его легко редактировать и менять поведение программы, например логировать сообщения в 
фаил.
