<?php

namespace Pop\Parser\Test;

use Pop\Parser\ParserInterface;
use Pop\Parser\Test\Fixtures\ConcreteParser;
use PHPUnit\Framework\TestCase;

class AbstractParserTest extends TestCase
{

    public function testImplementsParserInterface(): void
    {
        $parser = new ConcreteParser();
        $this->assertInstanceOf(ParserInterface::class, $parser);
    }

    public function testConstructorWithoutDataLeavesDataNull(): void
    {
        $parser = new ConcreteParser();
        $this->assertNull($parser->getData());
    }

    public function testConstructorWithDataSetsData(): void
    {
        $parser = new ConcreteParser('foo');
        $this->assertEquals('foo', $parser->getData());
    }

    public function testSetDataIsFluentAndRetrievable(): void
    {
        $parser = new ConcreteParser();
        $result = $parser->setData('bar');

        $this->assertSame($parser, $result);
        $this->assertEquals('bar', $parser->getData());
    }

    public function testGetResultDefaultsToNull(): void
    {
        $parser = new ConcreteParser();
        $this->assertNull($parser->getResult());
    }

    public function testParseSetsResultAndReturnsStatic(): void
    {
        $parser  = new ConcreteParser('baz');
        $result  = $parser->parse();

        $this->assertSame($parser, $result);
        $this->assertEquals('baz', $parser->getResult());
    }

    public function testHasErrorDefaultsToFalse(): void
    {
        $parser = new ConcreteParser();
        $this->assertFalse($parser->hasError());
    }

    public function testGetErrorMessageDefaultsToNull(): void
    {
        $parser = new ConcreteParser();
        $this->assertNull($parser->getErrorMessage());
    }

    public function testErrorStateIsExposedThroughGetters(): void
    {
        $parser = new ConcreteParser();
        $parser->triggerError('Something went wrong.');

        $this->assertTrue($parser->hasError());
        $this->assertEquals('Something went wrong.', $parser->getErrorMessage());
    }

}
