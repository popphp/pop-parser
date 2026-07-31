<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

namespace Pop\Parser\Test\Fixtures;

use Pop\Parser\AbstractParser;

/**
 * Minimal concrete parser used to exercise AbstractParser, which cannot be
 * instantiated directly.
 */
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
