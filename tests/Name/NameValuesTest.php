<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

namespace Pop\Parser\Test\Name;

use Pop\Parser\Name\NameValues;
use PHPUnit\Framework\TestCase;

class NameValuesTest extends TestCase
{

    public function testGetSalutationsContainsExpectedEntries(): void
    {
        $salutations = (new NameValues())->getSalutations();

        $this->assertEquals('Mr.', $salutations['mr']);
        $this->assertEquals('Dr.', $salutations['dr']);
    }

    public function testGetSuffixesContainsExpectedEntries(): void
    {
        $suffixes = (new NameValues())->getSuffixes();

        $this->assertEquals('Jr', $suffixes['jr']);
        $this->assertEquals('PhD', $suffixes['phd']);
    }

    public function testGetLastnamePrefixesContainsExpectedEntries(): void
    {
        $prefixes = (new NameValues())->getLastnamePrefixes();

        $this->assertEquals('van', $prefixes['van']);
        $this->assertEquals('von', $prefixes['von']);
    }

    public function testGetNicknameDelimitersContainsExpectedPairs(): void
    {
        $delimiters = (new NameValues())->getNicknameDelimiters();

        $this->assertEquals(')', $delimiters['(']);
        $this->assertEquals('"', $delimiters['"']);
    }

}
