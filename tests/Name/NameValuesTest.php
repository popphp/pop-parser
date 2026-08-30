<?php

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
        $this->assertEquals('III', $suffixes['iii']);
        $this->assertArrayNotHasKey('phd', $suffixes);
    }

    public function testGetCredentialsContainsExpectedEntries(): void
    {
        $credentials = (new NameValues())->getCredentials();

        $this->assertEquals('PhD', $credentials['phd']);
        $this->assertEquals('MD', $credentials['md']);
        $this->assertArrayNotHasKey('jr', $credentials);
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
