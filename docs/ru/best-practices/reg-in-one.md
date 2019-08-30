### Регистрирование реализации интерфейсов в одном месте.

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
---
* [Вернуться к началу раздела](../best-practices.md)
* [Вернуться на главную](../readme.md)
