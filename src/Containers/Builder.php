<?php

namespace Mateodioev\TgHandler\Containers;

use Closure;

/**
 * @internal
 */
final class Builder
{
    public string $class;
    public ?Closure $builder;
    public array $atributtes = [];
    public bool $singleton = false;

    public function with(array $params): self
    {
        $this->atributtes = $params;
        return $this;
    }

    /**
     * @param class-string $class
     */
    public function isMatch(string $class): bool
    {
        return $class === $this->class;
    }

    public function build(): object
    {
        $class = $this->class;
        $fn = $this->builder;

        if ($fn === null)
            return new $class(...$this->atributtes);

        return $fn(...$this->atributtes);
    }
}

