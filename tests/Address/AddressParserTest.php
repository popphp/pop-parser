<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

namespace Pop\Parser\Test\Address;

use Pop\Parser\Address\AddressParser;
use Pop\Parser\Exception;
use PHPUnit\Framework\TestCase;

class AddressParserTest extends TestCase
{

    public function testParseWithNoDataThrowsException(): void
    {
        $this->expectException(Exception::class);

        $parser = new AddressParser();
        $parser->parse();
    }

    public function testConstructorSetsData(): void
    {
        $parser = new AddressParser('123 Main St, Springfield, IL 62704');
        $this->assertEquals('123 Main St, Springfield, IL 62704', $parser->getData());
    }

    public function testSetDataGetData(): void
    {
        $parser = new AddressParser();
        $parser->setData('123 Main St, Springfield, IL 62704');
        $this->assertEquals('123 Main St, Springfield, IL 62704', $parser->getData());
    }

    public function testParseWithNoArgumentUsesConstructorData(): void
    {
        $parser = new AddressParser('123 Main St, Springfield, IL 62704');
        $parser->parse();

        $this->assertEquals('123', $parser->getStreetNumber());
    }

    public function testParseWithExplicitAddressWhenNoDataSetAlsoSetsData(): void
    {
        $parser = new AddressParser();
        $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertEquals('123 Main St, Springfield, IL 62704', $parser->getData());
    }

    /**
     * Characterization test: when both constructor data and an explicit parse()
     * argument are provided, the address argument is what actually gets parsed,
     * but getData() still reflects the original constructor value rather than
     * the argument that was parsed.
     */
    public function testParseArgumentTakesPrecedenceOverConstructorDataWithoutUpdatingData(): void
    {
        $parser = new AddressParser('111 First St, Springfield, IL 62704');
        $parser->parse('222 Second St, Springfield, IL 62704');

        $this->assertEquals('111 First St, Springfield, IL 62704', $parser->getData());
        $this->assertEquals('222', $parser->getStreetNumber());
    }

    public function testParseFullUsAddress(): void
    {
        $parser = new AddressParser();
        $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertEquals('123', $parser->getStreetNumber());
        $this->assertEquals('Main', $parser->getStreetName(false));
        $this->assertEquals('Springfield', $parser->getCity());
        $this->assertEquals('IL', $parser->getStateCode());
        $this->assertEquals('Illinois', $parser->getStateName());
        $this->assertEquals('62704', $parser->getPostalCode());
        $this->assertEquals('US', $parser->getCountry());
        $this->assertNull($parser->getZip4());
        $this->assertNull($parser->getDirection());
    }

    public function testParsePicksUpDirectionAndUnit(): void
    {
        $parser = new AddressParser();
        $parser->parse('456 N Oak Avenue Apt 3B, Chicago, IL 60614');

        $this->assertEquals('456', $parser->getStreetNumber());
        $this->assertEquals('Oak', $parser->getStreetName(false));
        $this->assertEquals('N', $parser->getDirection());
        $this->assertEquals('Apt 3B', $parser->getUnit());
        $this->assertEquals('Chicago', $parser->getCity());
        $this->assertTrue($parser->hasDirection());
        $this->assertTrue($parser->hasUnit());
    }

    public function testGetStreetNamePrefixesDirectionWhenItPrecedesStreetName(): void
    {
        $parser = new AddressParser();
        $parser->parse('456 N Oak Avenue, Chicago, IL 60614');

        $this->assertEquals('N Oak', $parser->getStreetName());
        $this->assertEquals('Oak', $parser->getStreetName(false));
    }

    public function testGetStreetNameSuffixesDirectionWhenItFollowsStreetName(): void
    {
        $parser = new AddressParser();
        $parser->parse('123 Main St S, Springfield, IL 62704');

        $this->assertEquals('Main S', $parser->getStreetName());
        $this->assertEquals('Main', $parser->getStreetName(false));
    }

    public function testParseSplitsZipPlus4FromDashedPostalCode(): void
    {
        $parser = new AddressParser();
        $parser->parse('100 Main St, Anytown, IL 90210-1234');

        $this->assertEquals('90210', $parser->getPostalCode());
        $this->assertEquals('1234', $parser->getZip4());
        $this->assertTrue($parser->hasZip4());
    }

    public function testParseCanadianAddress(): void
    {
        $parser = new AddressParser();
        $parser->parse('789 Elm St, Toronto, ON M4B1B3, Canada');

        $this->assertEquals('789', $parser->getStreetNumber());
        $this->assertEquals('Toronto', $parser->getCity());
        $this->assertEquals('M4B1B3', $parser->getPostalCode());
    }

    /**
     * City extraction is positional (whatever precedes the state token in its segment),
     * not a hardcoded-dataset lookup, so a city that isn't in AddressValues's city list
     * still parses correctly.
     */
    public function testParseRecognizesCityNotInDataset(): void
    {
        $parser = new AddressParser();
        $parser->parse('4500 Park Granada, Calabasas, CA 91302');

        $this->assertEquals('Calabasas', $parser->getCity());
        $this->assertTrue($parser->hasCity());
    }

    /**
     * Regression test: a California address must not be misdetected as Canada just
     * because the state code "CA" also happens to be a country code. The state slot is
     * defined by position (the token immediately before the postal code), so "CA" there
     * is unambiguously the state.
     */
    public function testParseCaliforniaAddressIsNotMisdetectedAsCanada(): void
    {
        $parser = new AddressParser();
        $parser->parse('2 Rodeo Dr, Beverly Hills, CA 90210');

        $this->assertEquals('US', $parser->getCountry());
        $this->assertEquals('CA', $parser->getStateCode());
        $this->assertEquals('California', $parser->getStateName());
        $this->assertEquals('Beverly Hills', $parser->getCity());
    }

    /**
     * Regression test: a suffix direction (here "NW") must not also be captured into
     * unit - each token can only be claimed by one field.
     */
    public function testSuffixDirectionIsNotDuplicatedIntoUnit(): void
    {
        $parser = new AddressParser();
        $parser->parse('1600 Pennsylvania Avenue NW, Washington, DC 20500');

        $this->assertEquals('NW', $parser->getDirection());
        $this->assertNull($parser->getUnit());
        $this->assertFalse($parser->hasUnit());
    }

    /**
     * Regression test: a route-type word (here "Park") that is merely the first word of
     * a street name, not a trailing route-type suffix, must not be treated as one.
     */
    public function testRouteTypeWordInsideStreetNameIsNotMistakenForARouteType(): void
    {
        $parser = new AddressParser();
        $parser->parse('4500 Park Granada, Calabasas, CA 91302');

        $this->assertEquals('Park Granada', $parser->getStreetName(false));
        $this->assertNull($parser->getRouteType());
    }

    public function testParsePoBoxAddress(): void
    {
        $parser = new AddressParser();
        $parser->parse('PO Box 1234, Austin, TX 73301');

        $this->assertTrue($parser->isPoBox());
        $this->assertEquals('PO Box 1234', $parser->getStreetName(false));
        $this->assertNull($parser->getStreetNumber());
        $this->assertEquals('Austin', $parser->getCity());
        $this->assertEquals('TX', $parser->getStateCode());
        $this->assertEquals('73301', $parser->getPostalCode());
    }

    public function testIsPoBoxDefaultsToFalse(): void
    {
        $parser = new AddressParser();
        $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertFalse($parser->isPoBox());
    }

    /**
     * Regression test: a comma-less address whose CITY ends in a word that's also a
     * valid route-type suffix ("Beverly Hills" - "Hills" is a real route-type word) must
     * not have that word mistaken for the street's route type.
     */
    public function testParseCommaLessAddressWithCityEndingInRouteTypeWord(): void
    {
        $parser = new AddressParser();
        $parser->parse('2 Rodeo Dr Beverly Hills CA 90210');

        $this->assertEquals('2', $parser->getStreetNumber());
        $this->assertEquals('Rodeo', $parser->getStreetName(false));
        $this->assertEquals('Dr', $parser->getRouteType());
        $this->assertEquals('Beverly Hills', $parser->getCity());
    }

    /**
     * Regression test: a comma-less address whose STREET NAME starts with a word that's
     * also a valid route-type word ("Park Ave" - "Park" is a real route-type word) must
     * not have that word mistaken for the street's route type.
     */
    public function testParseCommaLessAddressWithStreetNameStartingInRouteTypeWord(): void
    {
        $parser = new AddressParser();
        $parser->parse('123 Park Ave Springfield IL 62701');

        $this->assertEquals('Park', $parser->getStreetName(false));
        $this->assertEquals('Ave', $parser->getRouteType());
        $this->assertEquals('Springfield', $parser->getCity());
    }

    /**
     * Regression test: a leading non-street line (e.g. a recipient name) must not be
     * mistaken for the street name, and the real street line further down must not be
     * silently dropped.
     */
    public function testParseDoesNotMistakeALeadingRecipientLineForTheStreet(): void
    {
        $parser = new AddressParser();
        $parser->parse('John Smith, 123 Main St, Springfield, IL 62704');

        $this->assertEquals('123', $parser->getStreetNumber());
        $this->assertEquals('Main', $parser->getStreetName(false));
        $this->assertEquals('St', $parser->getRouteType());
        $this->assertEquals('Springfield', $parser->getCity());
    }

    /**
     * Regression test: when no city is given at all (state immediately follows the
     * street, with nothing between them), the street must still parse correctly and
     * city must be left null rather than swallowing the street as a "city".
     */
    public function testParseWithNoCityLeavesCityNullWithoutLosingTheStreet(): void
    {
        $parser = new AddressParser();
        $parser->parse('123 Main St, IL 62704');

        $this->assertEquals('123', $parser->getStreetNumber());
        $this->assertEquals('Main', $parser->getStreetName(false));
        $this->assertEquals('St', $parser->getRouteType());
        $this->assertNull($parser->getCity());
    }

    /**
     * Regression test: a single comma separating "street" from "city state zip" (no
     * comma between city and state) must still recognize the city rather than silently
     * dropping it.
     */
    public function testParseWithSingleCommaBeforeCityStateZipStillFindsCity(): void
    {
        $parser = new AddressParser();
        $parser->parse('123 Main St, Springfield IL 62704');

        $this->assertEquals('123', $parser->getStreetNumber());
        $this->assertEquals('Main', $parser->getStreetName(false));
        $this->assertEquals('St', $parser->getRouteType());
        $this->assertEquals('Springfield', $parser->getCity());
    }

    public function testHasMethodsDefaultToFalseBeforeParsing(): void
    {
        $parser = new AddressParser();

        $this->assertFalse($parser->hasStreetNumber());
        $this->assertFalse($parser->hasStreetName());
        $this->assertFalse($parser->hasRouteType());
        $this->assertFalse($parser->hasDirection());
        $this->assertFalse($parser->hasUnit());
        $this->assertFalse($parser->hasCity());
        $this->assertFalse($parser->hasPostalCode());
        $this->assertFalse($parser->hasZip4());
        $this->assertFalse($parser->hasStateName());
        $this->assertFalse($parser->hasStateCode());
        $this->assertFalse($parser->hasCountry());
    }

    public function testToArrayReturnsAllParsedFields(): void
    {
        $parser = new AddressParser();
        $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertEquals([
            'streetNumber' => '123',
            'streetName'   => 'Main',
            'routeType'    => 'St',
            'direction'    => null,
            'unit'         => null,
            'city'         => 'Springfield',
            'postalCode'   => '62704',
            'zip4'         => null,
            'stateName'    => 'Illinois',
            'stateCode'    => 'IL',
            'country'      => 'US',
        ], $parser->toArray());
    }

    public function testGetFullAddressWithStateCode(): void
    {
        $parser = new AddressParser();
        $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertEquals('123 Main St, Springfield, IL 62704', $parser->getFullAddress());
    }

    public function testGetFullAddressWithStateNameInsteadOfCode(): void
    {
        $parser = new AddressParser();
        $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertEquals('123 Main St, Springfield, Illinois 62704', $parser->getFullAddress(', ', false));
    }

    public function testGetFullAddressCanIncludeCountry(): void
    {
        $parser = new AddressParser();
        $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertEquals('123 Main St, Springfield, IL 62704, US', $parser->getFullAddress(', ', true, true));
    }

    public function testToStringMatchesGetFullAddress(): void
    {
        $parser = new AddressParser();
        $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertEquals($parser->getFullAddress(), (string) $parser);
    }

    public function testParseStreetAddressOnlyParsesLocationPortion(): void
    {
        $parser = new AddressParser();
        $result = $parser->parseStreetAddress('456 N Oak Avenue Apt 3B');

        $this->assertEquals([
            'streetNumber' => '456',
            'streetName'   => 'Oak',
            'routeType'    => 'Avenue',
            'direction'    => 'N',
            'unit'         => 'Apt 3B',
        ], $result);
    }

    public function testCleanNormalizesWhitespaceAndSplitsOnDelimiters(): void
    {
        $parser = new AddressParser();
        $result = $parser->clean('123   Main St ,  Springfield ,  IL   62704');

        $this->assertEquals([
            '123 Main St',
            'Springfield',
            'IL 62704',
        ], array_values($result));
    }

    /**
     * Characterization test: array_filter() in clean() drops empty segments
     * but does not reindex, so a doubled delimiter leaves a gap in the keys.
     */
    public function testCleanDropsEmptySegmentsWithoutReindexing(): void
    {
        $parser = new AddressParser();
        $result = $parser->clean('123 Main St,, Springfield');

        $this->assertArrayNotHasKey(1, $result);
        $this->assertEquals('123 Main St', $result[0]);
        $this->assertEquals('Springfield', $result[2]);
    }

    public function testCleanNormalizesBadDashSpacing(): void
    {
        $parser = new AddressParser();
        $result = $parser->clean('123 Main St - Suite 4, Springfield');

        $this->assertEquals('123 Main St-Suite 4', $result[0]);
    }

}
