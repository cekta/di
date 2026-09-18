<?php

declare(strict_types=1);

namespace Cekta\DI\Test\LifeCycleTest;

use Cekta\DI\Lazy\Closure;
use stdClass;
use Cekta\DI\Test\LifeCycleTest\SingletonSubContainer\Dependency;

class Project extends \Cekta\DI\Test\Fixture\Project
{
    public const SCOPED_ALIAS = 'scoped_alias';
    public const SCOPED_DEFINITION = 'scoped_definition';
    public const SINGLETON_ALIAS = 'singleton_alias';
    public const SINGLETON_DEFINITION = 'singleton_definition';
    public const FACTORY_ALIAS = 'factory_alias';
    public const FACTORY_DEFINITION = 'factory_definition';
    public const FQCN = 'Cekta\DI\Test\LifeCycleContainer';

    public function __construct(
        string $filename,
    ) {
        parent::__construct(
            filename: $filename,
            fqcn: self::FQCN,
            params: [
                self::SCOPED_DEFINITION => new Closure(function () {
                    return new stdClass();
                }),
                self::SINGLETON_DEFINITION => new Closure(function () {
                    return new stdClass();
                }),
                self::FACTORY_DEFINITION => new Closure(function () {
                    static $index = 0;
                    return $index++;
                }),
            ],
            entries: [
                stdClass::class,
                SingletonSubContainer::class,
                FactorySubContainer::class,
                Singleton::class,
                Factory::class,
                self::SCOPED_ALIAS,
                self::SINGLETON_ALIAS,
                self::FACTORY_ALIAS,
            ],
            alias: [
                self::SCOPED_ALIAS => stdClass::class,
                self::SINGLETON_ALIAS => stdClass::class,
                self::FACTORY_ALIAS => self::FACTORY_DEFINITION,
            ],
            singletons: [
                Dependency::class,
                self::SINGLETON_ALIAS,
                self::SINGLETON_DEFINITION,
                Singleton::class,
            ],
            factories: [
                FactorySubContainer\Dependency::class,
                FactorySubContainer::class,
                Factory::class,
                self::FACTORY_ALIAS,
                self::FACTORY_DEFINITION,
            ],
        );
    }
}
