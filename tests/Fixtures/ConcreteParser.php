<?php

namespace Pop\Parser\Test\Fixtures;

use Pop\Parser\AbstractParser;
use Pop\Parser\AbstractResult;

class ConcreteParser extends AbstractParser
{

    public function parse(): AbstractResult
    {
        $this->result = new ConcreteResult($this->data);
        return $this->result;
    }

    public function triggerError(string $message): static
    {
        $this->error        = true;
        $this->errorMessage = $message;
        return $this;
    }

}
