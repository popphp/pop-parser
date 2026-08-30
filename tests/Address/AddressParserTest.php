<?php

namespace Pop\Parser\Test\Address;

use Pop\Parser\Address\AddressParser;
use Pop\Parser\Address\AddressResult;
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
        $result = $parser->parse();

        $this->assertEquals('123', $result->getStreetNumber());
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
        $result = $parser->parse('222 Second St, Springfield, IL 62704');

        $this->assertEquals('111 First St, Springfield, IL 62704', $parser->getData());
        $this->assertEquals('222', $result->getStreetNumber());
    }

    public function testParseFullUsAddress(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertEquals('123', $result->getStreetNumber());
        $this->assertEquals('Main', $result->getStreetName(false));
        $this->assertEquals('Springfield', $result->getCity());
        $this->assertEquals('IL', $result->getStateCode());
        $this->assertEquals('Illinois', $result->getStateName());
        $this->assertEquals('62704', $result->getPostalCode());
        $this->assertEquals('US', $result->getCountry());
        $this->assertNull($result->getZip4());
        $this->assertNull($result->getDirection());
    }

    public function testParsePicksUpDirectionAndUnit(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('456 N Oak Avenue Apt 3B, Chicago, IL 60614');

        $this->assertEquals('456', $result->getStreetNumber());
        $this->assertEquals('Oak', $result->getStreetName(false));
        $this->assertEquals('N', $result->getDirection());
        $this->assertEquals('Apt 3B', $result->getUnit());
        $this->assertEquals('Chicago', $result->getCity());
        $this->assertTrue($result->hasDirection());
        $this->assertTrue($result->hasUnit());
    }

    public function testParseMultiLineAddressWithUnitOnOwnLine(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse("123 Main St\nApt 4B\nSpringfield, IL 62704");

        $this->assertEquals('123', $result->getStreetNumber());
        $this->assertEquals('Main', $result->getStreetName(false));
        $this->assertEquals('Apt 4B', $result->getUnit());
        $this->assertEquals('Springfield', $result->getCity());
        $this->assertEquals('IL', $result->getStateCode());
        $this->assertEquals('62704', $result->getPostalCode());
    }

    public function testGetStreetNamePrefixesDirectionWhenItPrecedesStreetName(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('456 N Oak Avenue, Chicago, IL 60614');

        $this->assertEquals('N Oak', $result->getStreetName());
        $this->assertEquals('Oak', $result->getStreetName(false));
    }

    public function testGetStreetNameSuffixesDirectionWhenItFollowsStreetName(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St S, Springfield, IL 62704');

        $this->assertEquals('Main S', $result->getStreetName());
        $this->assertEquals('Main', $result->getStreetName(false));
    }

    public function testParseSplitsZipPlus4FromDashedPostalCode(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('100 Main St, Anytown, IL 90210-1234');

        $this->assertEquals('90210', $result->getPostalCode());
        $this->assertEquals('1234', $result->getZip4());
        $this->assertTrue($result->hasZip4());
    }

    public function testParseCanadianAddress(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('789 Elm St, Toronto, ON M4B1B3, Canada');

        $this->assertEquals('789', $result->getStreetNumber());
        $this->assertEquals('Toronto', $result->getCity());
        $this->assertEquals('M4B1B3', $result->getPostalCode());
    }

    /**
     * City extraction is positional (whatever precedes the state token in its segment),
     * not a hardcoded-dataset lookup, so a city that isn't in AddressValues's city list
     * still parses correctly.
     */
    public function testParseRecognizesCityNotInDataset(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('4500 Park Granada, Calabasas, CA 91302');

        $this->assertEquals('Calabasas', $result->getCity());
        $this->assertTrue($result->hasCity());
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
        $result = $parser->parse('2 Rodeo Dr, Beverly Hills, CA 90210');

        $this->assertEquals('US', $result->getCountry());
        $this->assertEquals('CA', $result->getStateCode());
        $this->assertEquals('California', $result->getStateName());
        $this->assertEquals('Beverly Hills', $result->getCity());
    }

    /**
     * Regression test: a suffix direction (here "NW") must not also be captured into
     * unit - each token can only be claimed by one field.
     */
    public function testSuffixDirectionIsNotDuplicatedIntoUnit(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('1600 Pennsylvania Avenue NW, Washington, DC 20500');

        $this->assertEquals('NW', $result->getDirection());
        $this->assertNull($result->getUnit());
        $this->assertFalse($result->hasUnit());
    }

    /**
     * Regression test: a route-type word (here "Park") that is merely the first word of
     * a street name, not a trailing route-type suffix, must not be treated as one.
     */
    public function testRouteTypeWordInsideStreetNameIsNotMistakenForARouteType(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('4500 Park Granada, Calabasas, CA 91302');

        $this->assertEquals('Park Granada', $result->getStreetName(false));
        $this->assertNull($result->getRouteType());
    }

    public function testParsePoBoxAddress(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('PO Box 1234, Austin, TX 73301');

        $this->assertTrue($result->isPoBox());
        $this->assertEquals('PO Box 1234', $result->getStreetName(false));
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Austin', $result->getCity());
        $this->assertEquals('TX', $result->getStateCode());
        $this->assertEquals('73301', $result->getPostalCode());
    }

    public function testIsPoBoxDefaultsToFalse(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertFalse($result->isPoBox());
    }

    /**
     * Regression test: a comma-less address whose CITY ends in a word that's also a
     * valid route-type suffix ("Beverly Hills" - "Hills" is a real route-type word) must
     * not have that word mistaken for the street's route type.
     */
    public function testParseCommaLessAddressWithCityEndingInRouteTypeWord(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('2 Rodeo Dr Beverly Hills CA 90210');

        $this->assertEquals('2', $result->getStreetNumber());
        $this->assertEquals('Rodeo', $result->getStreetName(false));
        $this->assertEquals('Dr', $result->getRouteType());
        $this->assertEquals('Beverly Hills', $result->getCity());
    }

    /**
     * Regression test: a comma-less address whose STREET NAME starts with a word that's
     * also a valid route-type word ("Park Ave" - "Park" is a real route-type word) must
     * not have that word mistaken for the street's route type.
     */
    public function testParseCommaLessAddressWithStreetNameStartingInRouteTypeWord(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Park Ave Springfield IL 62701');

        $this->assertEquals('Park', $result->getStreetName(false));
        $this->assertEquals('Ave', $result->getRouteType());
        $this->assertEquals('Springfield', $result->getCity());
    }

    /**
     * Regression test: a city name whose FIRST word is also a valid route-type suffix
     * ("Lake Forest" - "Lake" is a real route-type word, e.g. "123 Lake Dr") must not have
     * that word mistaken for a street's route-type boundary and silently dropped. This
     * exercises the fallback path (no comma directly before the state) that pulls the city
     * from the nearest preceding, still-unconsumed line - that line must only ever be
     * treated as a street/city hybrid worth splitting when it actually looks like a street
     * (leads with a digit or is a PO Box line), never a plain, comma-delimited city-only
     * line.
     */
    public function testParseCityStartingWithRouteTypeWordIsNotSplitAndDropped(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('55 Winding Way, Lake Forest, IL 60045');

        $this->assertEquals('55', $result->getStreetNumber());
        $this->assertEquals('Winding', $result->getStreetName(false));
        $this->assertEquals('Way', $result->getRouteType());
        $this->assertEquals('Lake Forest', $result->getCity());
        $this->assertEquals('IL', $result->getStateCode());
        $this->assertEquals('60045', $result->getPostalCode());
    }

    /**
     * Regression test: the same bug in the degenerate two-line case (no separate street
     * line at all, so the postal-code line's "before" segment is empty and the fallback
     * lands directly on the city-only line) - the city must not be misread as a
     * street/city hybrid and split, leaving a bogus route type and a null street name.
     */
    public function testParseCityOnlyAddressStartingWithRouteTypeWordIsNotSplit(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('Lake Forest, IL 60045');

        $this->assertEquals('Lake Forest', $result->getCity());
        $this->assertNull($result->getRouteType());
        $this->assertNull($result->getStreetName(false));
    }

    /**
     * Regression test: a leading non-street line (e.g. a recipient name) must not be
     * mistaken for the street name, and the real street line further down must not be
     * silently dropped.
     */
    public function testParseDoesNotMistakeALeadingRecipientLineForTheStreet(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('John Smith, 123 Main St, Springfield, IL 62704');

        $this->assertEquals('123', $result->getStreetNumber());
        $this->assertEquals('Main', $result->getStreetName(false));
        $this->assertEquals('St', $result->getRouteType());
        $this->assertEquals('Springfield', $result->getCity());
    }

    /**
     * Regression test: when no city is given at all (state immediately follows the
     * street, with nothing between them), the street must still parse correctly and
     * city must be left null rather than swallowing the street as a "city".
     */
    public function testParseWithNoCityLeavesCityNullWithoutLosingTheStreet(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St, IL 62704');

        $this->assertEquals('123', $result->getStreetNumber());
        $this->assertEquals('Main', $result->getStreetName(false));
        $this->assertEquals('St', $result->getRouteType());
        $this->assertNull($result->getCity());
    }

    /**
     * Regression test: a single comma separating "street" from "city state zip" (no
     * comma between city and state) must still recognize the city rather than silently
     * dropping it.
     */
    public function testParseWithSingleCommaBeforeCityStateZipStillFindsCity(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St, Springfield IL 62704');

        $this->assertEquals('123', $result->getStreetNumber());
        $this->assertEquals('Main', $result->getStreetName(false));
        $this->assertEquals('St', $result->getRouteType());
        $this->assertEquals('Springfield', $result->getCity());
    }

    /**
     * Regression test: extractLocation() only promotes a non-first line to "primary" when
     * it has strong evidence of being the street (a leading number AND a trailing
     * route-type word). A line that only weakly matches one signal - e.g. "4th Floor"
     * starts with a digit but isn't a street - must not be promoted over the real street
     * line ("Broadway") just because the real street line has no recognizable route-type
     * suffix of its own.
     */
    public function testParseDoesNotPromoteAWeaklyMatchingLaterLineOverAStreetWithNoRouteType(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('Broadway, 4th Floor, New York, NY 10001');

        $this->assertEquals('Broadway', $result->getStreetName(false));
        $this->assertEquals('New York', $result->getCity());
    }

    /**
     * Regression test: a PO Box address with no city given must still be recognized as a
     * PO Box, not swallowed whole as a garbage city value.
     */
    public function testParsePoBoxWithNoCityGivenIsNotSwallowedAsCity(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('PO Box 1234, IL 62704');

        $this->assertTrue($result->isPoBox());
        $this->assertEquals('PO Box 1234', $result->getStreetName(false));
        $this->assertNull($result->getCity());
        $this->assertEquals('IL', $result->getStateCode());
        $this->assertEquals('62704', $result->getPostalCode());
    }

    /**
     * Regression test: an all-uppercase or all-lowercase street name and city get
     * title-cased, the same treatment NameParser already applies to name fields.
     */
    public function testParseNormalizesMonotoneCaseStreetNameAndCity(): void
    {
        $allCaps       = new AddressParser();
        $allCapsResult = $allCaps->parse('123 MAIN ST, SPRINGFIELD, IL 62704');
        $this->assertEquals('Main', $allCapsResult->getStreetName(false));
        $this->assertEquals('Springfield', $allCapsResult->getCity());

        $lowercase       = new AddressParser();
        $lowercaseResult = $lowercase->parse('123 main st, springfield, il 62704');
        $this->assertEquals('Main', $lowercaseResult->getStreetName(false));
        $this->assertEquals('Springfield', $lowercaseResult->getCity());
    }

    /**
     * Regression test: "Mc" is a reliable surname/place-prefix marker in a monotone-case
     * city, so the letter right after it gets capitalized too.
     */
    public function testParseCapitalizesLetterAfterMcPrefixInCity(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St, MCKEESPORT, PA 15132');

        $this->assertEquals('McKeesport', $result->getCity());
    }

    /**
     * Regression test: casing normalization must never touch postal code, state code,
     * country code, route type, or direction - only street name and city.
     */
    public function testParseCasingNormalizationDoesNotTouchCanonicalFields(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('1600 PENNSYLVANIA AVENUE NW, WASHINGTON, DC 20500, US');

        $this->assertEquals('NW', $result->getDirection());
        $this->assertEquals('AVENUE', $result->getRouteType());
        $this->assertEquals('DC', $result->getStateCode());
        $this->assertEquals('20500', $result->getPostalCode());
        $this->assertEquals('US', $result->getCountry());
    }

    /**
     * Regression test: a street name/city already in mixed case is left exactly as typed.
     */
    public function testParsePreservesMixedCaseStreetNameAndCity(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('789 MacArthur Blvd, DeSoto, TX 75115');

        $this->assertEquals('MacArthur', $result->getStreetName(false));
        $this->assertEquals('DeSoto', $result->getCity());
    }

    public function testToArrayReturnsAllParsedFields(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St, Springfield, IL 62704');

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
            'confidence'   => 1.0,
        ], $result->toArray());
    }

    public function testConfidenceDefaultsToFullConfidenceForAClearAddress(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertEquals(1.0, $result->getConfidence());
        $this->assertTrue($result->isConfident());
    }

    /**
     * Regression test: the comma-less street/city-hybrid route-boundary heuristic is a
     * guess relative to a comma-anchored split, so using it lowers confidence.
     */
    public function testConfidenceIsLoweredWhenRouteBoundaryHeuristicIsUsed(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('2 Rodeo Dr Beverly Hills CA 90210');

        $this->assertEquals(0.75, $result->getConfidence());
    }

    /**
     * Regression test: falling back to the first line as the street line (no line had
     * strong street evidence) is a default, not a resolved match, so it lowers confidence.
     */
    public function testConfidenceIsLoweredWhenNoLineHasStrongStreetEvidence(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('Broadway, 4th Floor, New York, NY 10001');

        $this->assertEquals(0.75, $result->getConfidence());
    }

    public function testConfidenceIsLoweredWhenNoPostalCodeIsFound(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St, Springfield, IL');

        $this->assertEquals(0.75, $result->getConfidence());
    }

    public function testConfidenceIsLoweredWhenPostalCodeFoundButStateIsNot(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St, Springfield, ZZ 62704');

        $this->assertNull($result->getStateCode());
        $this->assertEquals(0.75, $result->getConfidence());
    }

    public function testGetFullAddressWithStateCode(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertEquals('123 Main St, Springfield, IL 62704', $result->getFullAddress());
    }

    public function testGetFullAddressWithStateNameInsteadOfCode(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertEquals('123 Main St, Springfield, Illinois 62704', $result->getFullAddress(', ', false));
    }

    public function testGetFullAddressCanIncludeCountry(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertEquals('123 Main St, Springfield, IL 62704, US', $result->getFullAddress(', ', true, true));
    }

    public function testToStringMatchesGetFullAddress(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse('123 Main St, Springfield, IL 62704');

        $this->assertEquals($result->getFullAddress(), (string) $result);
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

    /**
     * Dirty-input corpus: doubled delimiters, stray leading/trailing commas, and irregular
     * whitespace must never crash the parser, whatever they end up parsing to.
     */
    public function testParseDoesNotCrashOnDirtyInput(): void
    {
        $inputs = [
            '123 Main St,, Springfield,,  IL 62704',
            ' , 123 Main St, Springfield, IL 62704 , ',
            '123   Main    St,   Springfield,   IL   62704',
            '123 Main St,,,',
            ',,',
            '???',
            "123 Main St\t\tSpringfield,  IL  62704",
            '123 Main St, Springfield, IL 62704,,',
            ',123 Main St, Springfield, IL 62704',
        ];

        foreach ($inputs as $input) {
            $parser = new AddressParser();
            $result = $parser->parse($input);
            $this->assertInstanceOf(AddressResult::class, $result);
        }
    }

    /**
     * Regression test: doubled/trailing commas around an otherwise well-formed address must
     * not prevent it from parsing correctly - the extra empty segments are simply dropped.
     */
    public function testParseWithDoubledOrLeadingCommasStillParsesCorrectly(): void
    {
        $parser = new AddressParser();
        $result = $parser->parse(' , 123 Main St, Springfield, IL 62704 , ');

        $this->assertEquals('123', $result->getStreetNumber());
        $this->assertEquals('Main', $result->getStreetName(false));
        $this->assertEquals('Springfield', $result->getCity());
        $this->assertEquals('IL', $result->getStateCode());
        $this->assertEquals('62704', $result->getPostalCode());
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

    public function testCleanSplitsOnNewlinesAndTabs(): void
    {
        $parser = new AddressParser();
        $result = $parser->clean("123 Main St\nApt 4B\tSpringfield, IL 62704");

        $this->assertEquals([
            '123 Main St',
            'Apt 4B',
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
