<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Container;
use Cekta\DI\ContainerFactory;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\NotInstantiable;
use Cekta\DI\Test\Fixture\A;
use Cekta\DI\Test\Fixture\Example\AutowiringInConstructor;
use Cekta\DI\Test\Fixture\Example\Shared;
use Cekta\DI\Test\Fixture\R1;
use Cekta\DI\Test\Fixture\S;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class ReadmeTest extends TestCase
{
    private const FILENAME = __DIR__ . '/ReadmeContainer.php';
    private const FQCN = "Cekta\DI\Test\ReadmeContainer";

    /**
     * @throws ContainerExceptionInterface
     * @throws IOExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotInstantiable
     * @throws InfiniteRecursion
     * @throws InvalidContainerForCompile
     */
    public function testExample(): void
    {
        $container = (new ContainerFactory())->make(
            filename: self::FILENAME,
            fqcn: self::FQCN,
            force_compile: true, // only for develop, always recompile file, in prod compile run once
            containers: [
                AutowiringInConstructor::class,
                Shared::class
            ],
            params: [
                // this params usually read from config files or environment
                'username' => 'username value',
                'password' => 'password value',
                S::class . '|string' => 'some value for union type', // union type and other new types in php 8.X
                '...variadic_int' => [1, 2, 3],
                'db_type' => 'sqlite', // use in definition
                'sqlite_file' => '/path/to/db.sqlite', // use in definition
            ],
            alias: [
                // register inteface or abstract class implementation here
                Fixture\I::class => Fixture\R1::class
            ],
            definitions: [
                // configuration of exclusive dependencies
                'dsn' => function (ContainerInterface $c) {
                    // get any dependencies from ContainerInterface, call any methods, and return any type or object
                    /** @var string $db_type */
                    $db_type = $c->get('db_type');
                    /** @var string $sqlite_file */
                    $sqlite_file = $c->get('sqlite_file');
                    return "$db_type:$sqlite_file";
                }
            ]
        );
        $autowiring = $container->get(AutowiringInConstructor::class);
        $this->assertInstanceOf(
            AutowiringInConstructor::class,
            $autowiring,
            'all containers must be available'
        );
        $this->assertInstanceOf(A::class, $autowiring->a, 'example autowiring in constructor');
        $this->assertInstanceOf(
            R1::class,
            $autowiring->i,
            'must be concrete implementation, defined in alias'
        );
        $this->assertInstanceOf(Shared::class, $autowiring->exampleShared);
        $this->assertInstanceOf(S::class, $autowiring->exampleShared->s);
        $this->assertSame('username value', $autowiring->username, 'value must be from params');
        $this->assertSame($autowiring->username, $autowiring->exampleShared->username);
        $this->assertSame(
            'sqlite:/path/to/db.sqlite',
            $autowiring->dsn,
            'value defined in definitions'
        );
        $this->assertSame($autowiring->dsn, $autowiring->exampleShared->dsn);
        $this->assertSame('some value for union type', $autowiring->union_type);
        $this->assertSame([1, 2, 3], $autowiring->variadic_int);

        $shared = $container->get(Shared::class);
        $this->assertInstanceOf(Shared::class, $shared, 'container must be available');
        $this->assertSame(
            $autowiring->s,
            $shared->s,
            'must reuse object, without recrating (singleton all dependency)'
        );
    }

    public static function tearDownAfterClass(): void
    {
        if (file_exists(self::FILENAME)) {
            unlink(self::FILENAME);
        }
    }
}
