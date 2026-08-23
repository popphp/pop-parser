<?php

namespace Pop\Parser\Test\Fixtures;

use Pop\Parser\AbstractParser;

class ConcreteParser extends AbstractParser
{

    public function parse(): static
    {
        $this->result = $this->data;
        return $this;
    }

    public function triggerError(string $message): static
    {
        $this->error        = true;
        $this->errorMessage = $message;
        return $this;
    }

}
