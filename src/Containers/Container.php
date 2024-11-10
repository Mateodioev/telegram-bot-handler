<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Containers;

use Closure;

/**
 * @deprecated v5.8.0 There is no need to use this class anymore.
 */
class Container
{
    /** @var array<class-string, Builder> */
    public static array $builders = [];

    /** @var array<class-string, Builder> */
    public static array $instances = [];

    private static function makeBuilder(string $class, ?Closure $fn = null): Builder
    {
        if (isset(self::$builders[$class])) { // if already exists
            return self::$builders[$class];
        }

        $builder = Builder::default($class, $fn);

        self::$builders[$class] = $builder;

        return $builder;
    }

    /**
     * Define how a class is created.
     * @param class-string $class Target class to build
     * @param ?Closure $fn Closure to build the class
     */
    public static function bind(string $class, ?Closure $fn = null): Builder
    {
        return self::makeBuilder($class, $fn);
    }

    /**
     * Define how a class is created.
     * Once the class is created, it is not created again
     * @param class-string $class Target class to build
     * @param ?Closure $fn Closure to build the class
     */
    public static function singleton(string $class, ?Closure $fn = null): Builder
    {
        if (isset(self::$builders[$class])) { // if already exists
            return self::$builders[$class];
        }

        $builder            = self::makeBuilder($class, $fn);
        $builder->singleton = true;

        self::$builders[$class] = $builder;

        return $builder;
    }

    /**
     * @param class-string $class
     */
    public static function setInstance(string $class, object $obj)
    {
        self::$instances[$class] = $obj;
    }

    /**
     * Get class object
     *
     * @param class-string $class
     */
    public static function make(string $class): object
    {
        if (isset(self::$instances[$class])) {
            return self::$instances[$class];
        }

        if (isset(self::$builders[$class]) === false) {
            return new $class();
        }

        $builder  = self::$builders[$class] ?? null;
        $instance = $builder->build();

        if ($builder->singleton) {
            self::$instances[$class] = $instance;
        }

        return $instance;
    }
}
