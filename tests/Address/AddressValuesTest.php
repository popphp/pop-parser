<?php

namespace Pop\Parser\Test\Address;

use Pop\Parser\Address\AddressValues;
use PHPUnit\Framework\TestCase;

class AddressValuesTest extends TestCase
{

    public function testGetCommonRouteTypesContainsExpectedEntriesSortedByLengthDescending(): void
    {
        $routes = (new AddressValues())->getCommonRouteTypes();

        $this->assertContains('street', $routes);
        $this->assertContains('ave', $routes);

        $lengths = array_map('strlen', $routes);
        $sorted  = $lengths;
        rsort($sorted);
        $this->assertEquals($sorted, $lengths);
    }

    public function testGetRouteTypesReturnsAbbreviationMapByDefault(): void
    {
        $routes = (new AddressValues())->getRouteTypes();

        $this->assertEquals('st', $routes['street']);
        $this->assertEquals('ct', $routes['court']);
    }

    public function testGetRouteTypesMergedContainsBothKeysAndValues(): void
    {
        $routes = (new AddressValues())->getRouteTypes(true);

        $this->assertContains('street', $routes);
        $this->assertContains('st', $routes);
        $this->assertEquals(array_unique($routes), $routes);
    }

    public function testGetDirectionsContainsAbbreviationsAndFullNames(): void
    {
        $directions = (new AddressValues())->getDirections();

        $this->assertContains('North', $directions);
        $this->assertContains('N.', $directions);
        $this->assertContains('Southwest', $directions);
    }

    public function testGetStatesReturnsCodeToNameMapForUs(): void
    {
        $states = (new AddressValues())->getStates('US');

        $this->assertEquals('Illinois', $states['IL']);
    }

    public function testGetStateCodesAndGetStateNamesForUs(): void
    {
        $addressValues = new AddressValues();

        $this->assertContains('IL', $addressValues->getStateCodes('US'));
        $this->assertContains('Illinois', $addressValues->getStateNames('US'));
    }

    public function testGetUnitTypesContainsAbbreviationsAndFullNames(): void
    {
        $unitTypes = (new AddressValues())->getUnitTypes();

        $this->assertContains('APT', $unitTypes);
        $this->assertContains('APARTMENT', $unitTypes);
    }

}
