--TEST--
Autowire проверка на вызов с аргументами разных типов и без типов
--FILE--
<?php

use Cekta\DI\Container;
use Cekta\DI\Provider\Autowire;
use Cekta\DI\Provider\KeyValue;

require __DIR__ . '/../../vendor/autoload.php';

class Demo
{
    /**
     * @var stdClass
     */
    public $class;
    /**
     * @var string
     */
    public $str;

    public function __construct(stdClass $class, string $str)
    {
        $this->class = $class;
        $this->str = $str;
    }
}

$providers[] = new Autowire();
$providers[] = new KeyValue(['str' => 'string value']);
$container = new Container(... $providers);
$demo = $container->get(Demo::class);
echo get_class($demo) . PHP_EOL;
echo get_class($demo->class) . PHP_EOL;
echo $demo->str;
?>
--EXPECT--
Demo
stdClass
string value
