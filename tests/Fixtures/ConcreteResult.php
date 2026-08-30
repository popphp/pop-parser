<?php

namespace Pop\Parser\Test\Fixtures;

use Pop\Parser\AbstractResult;

class ConcreteResult extends AbstractResult
{

    protected mixed $value;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function toArray(): array
    {
        return ['value' => $this->value];
    }

}
