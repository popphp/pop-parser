<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

namespace Pop\Parser\Test;

use Pop\Parser\Exception;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{

    public function testIsInstanceOfBaseException(): void
    {
        $exception = new Exception('Test error');
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testMessageIsPreserved(): void
    {
        $exception = new Exception('Test error');
        $this->assertEquals('Test error', $exception->getMessage());
    }

    public function testIsThrowableAndCatchable(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test error');

        throw new Exception('Test error');
    }

}
