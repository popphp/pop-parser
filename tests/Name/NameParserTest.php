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

use Pop\Parser\Name\NameParser;
use Pop\Parser\Exception;
use PHPUnit\Framework\TestCase;

class NameParserTest extends TestCase
{

    public function testParseWithNoDataThrowsException(): void
    {
        $this->expectException(Exception::class);

        $parser = new NameParser();
        $parser->parse();
    }

    public function testParseWithWhitespaceOnlyThrowsException(): void
    {
        $this->expectException(Exception::class);

        $parser = new NameParser();
        $parser->parse('   ');
    }

    public function testConstructorSetsData(): void
    {
        $parser = new NameParser('James Norrington');
        $this->assertEquals('James Norrington', $parser->getData());
    }

    public function testParseWithNoArgumentUsesConstructorData(): void
    {
        $parser = new NameParser('James Norrington');
        $parser->parse();

        $this->assertEquals('James', $parser->getFirstname());
    }

    public function testParseSimpleFirstLast(): void
    {
        $parser = new NameParser();
        $parser->parse('James Norrington');

        $this->assertEquals('James', $parser->getFirstname());
        $this->assertEquals('Norrington', $parser->getLastname());
        $this->assertTrue($parser->hasFirstname());
        $this->assertTrue($parser->hasLastname());
    }

    public function testParseFirstMiddleLast(): void
    {
        $parser = new NameParser();
        $parser->parse('Hans Christian Anderssen');

        $this->assertEquals('Hans', $parser->getFirstname());
        $this->assertEquals('Christian', $parser->getMiddlename());
        $this->assertEquals('Anderssen', $parser->getLastname());
    }

    public function testParseSingleWordIsFirstnameOnly(): void
    {
        $parser = new NameParser();
        $parser->parse('Adam');

        $this->assertEquals('Adam', $parser->getFirstname());
        $this->assertNull($parser->getLastname());
    }

    public function testParseSalutationInitialPrefixedLastnameAndSuffix(): void
    {
        $parser = new NameParser();
        $parser->parse('Mr Anthony R Von Fange III');

        $this->assertEquals('Mr.', $parser->getSalutation());
        $this->assertEquals('Anthony', $parser->getFirstname());
        $this->assertEquals('R', $parser->getInitials());
        $this->assertEquals('von', $parser->getLastnamePrefix());
        $this->assertEquals('Fange', $parser->getLastname());
        $this->assertEquals('III', $parser->getSuffix());
    }

    public function testParseConsecutiveSalutationsAreBothClaimed(): void
    {
        $parser = new NameParser();
        $parser->parse('Rev. Dr John Doe');

        $this->assertEquals('Rev. Dr.', $parser->getSalutation());
        $this->assertEquals('John', $parser->getFirstname());
        $this->assertEquals('Doe', $parser->getLastname());
    }

    public function testParseDottedInitialsPromoteFirstOneToFirstname(): void
    {
        $parser = new NameParser();
        $parser->parse('J. B. Hunt');

        $this->assertEquals('J.', $parser->getFirstname());
        $this->assertEquals('B.', $parser->getInitials());
        $this->assertEquals('Hunt', $parser->getLastname());
    }

    public function testParseCombinedTwoLetterInitialsAreSplit(): void
    {
        $parser = new NameParser();
        $parser->parse('J.B. Hunt');

        $this->assertEquals('J', $parser->getFirstname());
        $this->assertEquals('B', $parser->getInitials());
        $this->assertEquals('Hunt', $parser->getLastname());
    }

    public function testParseSingleInitialWithRealFirstnamePresent(): void
    {
        $parser = new NameParser();
        $parser->parse('M Peter Williams');

        $this->assertEquals('Peter', $parser->getFirstname());
        $this->assertEquals('M', $parser->getInitials());
        $this->assertEquals('Williams', $parser->getLastname());
    }

    /**
     * Regression test: suffix scanning never touches the first two tokens of a name (it
     * reserves them for firstname/lastname), so a bare two-token name never has its second
     * word mistaken for a suffix even when that word ("Senior") is also a valid suffix.
     */
    public function testParseTwoTokenNameNeverTreatsSecondWordAsSuffix(): void
    {
        $parser = new NameParser();
        $parser->parse('Jason Senior');

        $this->assertEquals('Jason', $parser->getFirstname());
        $this->assertEquals('Senior', $parser->getLastname());
        $this->assertNull($parser->getSuffix());
    }

    public function testParseMultipleTrailingSuffixesAreAllClaimed(): void
    {
        $parser = new NameParser();
        $parser->parse('Edward Dale Senior II');

        $this->assertEquals('Edward', $parser->getFirstname());
        $this->assertEquals('Dale', $parser->getLastname());
        $this->assertEquals('Senior II', $parser->getSuffix());
    }

    /**
     * Regression test: an all-uppercase or all-lowercase word gets title-cased, but a word
     * with existing mixed case (like "MacDonald") is left exactly as typed, since that
     * capitalization is almost always deliberate.
     */
    public function testParseNormalizesMonotoneCaseButPreservesMixedCase(): void
    {
        $allCaps = new NameParser();
        $allCaps->parse('OLD MACDONALD');
        $this->assertEquals('Old', $allCaps->getFirstname());
        $this->assertEquals('Macdonald', $allCaps->getLastname());

        $mixedCase = new NameParser();
        $mixedCase->parse('Old MacDonald');
        $this->assertEquals('MacDonald', $mixedCase->getLastname());
    }

    public function testParseLastnamePrefix(): void
    {
        $parser = new NameParser();
        $parser->parse('James van Allen');

        $this->assertEquals('James', $parser->getFirstname());
        $this->assertEquals('van', $parser->getLastnamePrefix());
        $this->assertEquals('Allen', $parser->getLastname());
        $this->assertTrue($parser->hasLastnamePrefix());
    }

    public function testParseChainedLastnamePrefixes(): void
    {
        $parser = new NameParser();
        $parser->parse('Ludwig van der Berg');

        $this->assertEquals('Ludwig', $parser->getFirstname());
        $this->assertEquals('van der', $parser->getLastnamePrefix());
        $this->assertEquals('Berg', $parser->getLastname());
    }

    /**
     * Regression test: a prefix word with nothing before it in the name (nothing left to
     * become a firstname) is not folded as a prefix - it's treated as the firstname itself.
     */
    public function testParsePrefixWordAsFirstnameWhenNothingPrecedesIt(): void
    {
        $parser = new NameParser();
        $parser->parse('Mr. Van Truong');

        $this->assertEquals('Mr.', $parser->getSalutation());
        $this->assertEquals('Van', $parser->getFirstname());
        $this->assertNull($parser->getLastnamePrefix());
        $this->assertEquals('Truong', $parser->getLastname());
    }

    public function testParseNicknameInParentheses(): void
    {
        $parser = new NameParser();
        $parser->parse('Jimmy (Bubba) Smith');

        $this->assertEquals('Jimmy', $parser->getFirstname());
        $this->assertEquals('Bubba', $parser->getNickname());
        $this->assertEquals('Smith', $parser->getLastname());
        $this->assertEquals('(Bubba)', $parser->getNickname(true));
    }

    public function testParseMultiWordNicknameInQuotes(): void
    {
        $parser = new NameParser();
        $parser->parse('Jimmy "Bubba Junior" Smith');

        $this->assertEquals('Bubba Junior', $parser->getNickname());
    }

    public function testParseCommaModeLastFirst(): void
    {
        $parser = new NameParser();
        $parser->parse('Fraser, Joshua');

        $this->assertEquals('Joshua', $parser->getFirstname());
        $this->assertEquals('Fraser', $parser->getLastname());
    }

    public function testParseCommaModeWithSalutation(): void
    {
        $parser = new NameParser();
        $parser->parse('Mrs. Brown, Amanda');

        $this->assertEquals('Mrs.', $parser->getSalutation());
        $this->assertEquals('Amanda', $parser->getFirstname());
        $this->assertEquals('Brown', $parser->getLastname());
    }

    public function testParseCommaModeLastFirstMiddle(): void
    {
        $parser = new NameParser();
        $parser->parse('Smith, John Eric');

        $this->assertEquals('Smith', $parser->getLastname());
        $this->assertEquals('John', $parser->getFirstname());
        $this->assertEquals('Eric', $parser->getMiddlename());
    }

    public function testParseCommaModeThreeSegmentsWithSuffix(): void
    {
        $parser = new NameParser();
        $parser->parse('Williams, Hank, Jr.');

        $this->assertEquals('Hank', $parser->getFirstname());
        $this->assertEquals('Williams', $parser->getLastname());
        $this->assertEquals('Jr', $parser->getSuffix());
    }

    /**
     * Regression test: segment 1 (before the first comma) can itself contain a full
     * "Firstname Lastname [Suffix]" - firstname extraction must run unconditionally there,
     * not only when nothing else matched, otherwise the firstname is lost.
     */
    public function testParseCommaModeFirstSegmentContainsFullNamePlusSuffix(): void
    {
        $parser = new NameParser();
        $parser->parse('Anthony Von Fange III, PHD');

        $this->assertEquals('Anthony', $parser->getFirstname());
        $this->assertEquals('von', $parser->getLastnamePrefix());
        $this->assertEquals('Fange', $parser->getLastname());
        $this->assertEquals('III PhD', $parser->getSuffix());
    }

    public function testParseCommaModeWithInitial(): void
    {
        $parser = new NameParser();
        $parser->parse('Kirk, James T.');

        $this->assertEquals('James', $parser->getFirstname());
        $this->assertEquals('T.', $parser->getInitials());
        $this->assertEquals('Kirk', $parser->getLastname());
    }

    public function testGetGivenNameCombinesFirstnameInitialsAndMiddlename(): void
    {
        $parser = new NameParser();
        $parser->parse('Mr Anthony R Von Fange III');

        $this->assertEquals('Anthony R', $parser->getGivenName());
    }

    public function testGetFullNameCombinesGivenNamePrefixAndLastname(): void
    {
        $parser = new NameParser();
        $parser->parse('Mr Anthony R Von Fange III');

        $this->assertEquals('Anthony R von Fange', $parser->getFullName());
    }

    public function testToStringComposesAllParts(): void
    {
        $parser = new NameParser();
        $parser->parse('Jimmy (Bubba) Smith');

        $this->assertEquals('Jimmy (Bubba) Smith', (string) $parser);
    }

    public function testToArrayReturnsAllParsedFields(): void
    {
        $parser = new NameParser();
        $parser->parse('Mr Anthony R Von Fange III');

        $this->assertEquals([
            'salutation'     => 'Mr.',
            'firstname'      => 'Anthony',
            'initials'       => 'R',
            'middlename'     => null,
            'nickname'       => null,
            'lastnamePrefix' => 'von',
            'lastname'       => 'Fange',
            'suffix'         => 'III',
        ], $parser->toArray());
    }

    public function testHasMethodsDefaultToFalseBeforeParsing(): void
    {
        $parser = new NameParser();

        $this->assertFalse($parser->hasSalutation());
        $this->assertFalse($parser->hasFirstname());
        $this->assertFalse($parser->hasMiddlename());
        $this->assertFalse($parser->hasNickname());
        $this->assertFalse($parser->hasInitials());
        $this->assertFalse($parser->hasLastnamePrefix());
        $this->assertFalse($parser->hasLastname());
        $this->assertFalse($parser->hasSuffix());
    }

    /**
     * Characterization test: unlike theiconic/name-parser (which can silently drop an
     * unrecognized leading word), this parser never discards tokens - a stray word that
     * doesn't match any category becomes part of the name content instead of vanishing.
     */
    public function testParseNeverSilentlyDropsAnUnrecognizedLeadingWord(): void
    {
        $parser = new NameParser();
        $parser->parse('The Rev. Mark Williams');

        $this->assertEquals('Rev.', $parser->getSalutation());
        $this->assertEquals('The', $parser->getFirstname());
        $this->assertEquals('Mark', $parser->getMiddlename());
        $this->assertEquals('Williams', $parser->getLastname());
    }

    /**
     * Edge case: a name that's only a salutation plus a word that's also a valid suffix, with
     * nothing else ("Dr Jr"). Suffix scanning reserves the first two tokens (see the
     * two-token-name test above), so with only one token left after the salutation, "Jr" is
     * never reachable as a suffix - it's claimed as lastname instead, and firstname stays
     * null rather than a guess being forced. No exception either way.
     */
    public function testParseSalutationPlusSingleWordThatCouldBeASuffix(): void
    {
        $parser = new NameParser();
        $parser->parse('Dr Jr');

        $this->assertEquals('Dr.', $parser->getSalutation());
        $this->assertNull($parser->getFirstname());
        $this->assertEquals('Jr', $parser->getLastname());
    }

    /**
     * Regression test: a lastname prefix in the comma-mode lastname segment (segment 1)
     * must still be recognized and must not swallow or lose the firstname that comes from
     * segment 2.
     */
    public function testParseCommaModeLastnamePrefixInFirstSegment(): void
    {
        $parser = new NameParser();
        $parser->parse('van Allen, James');

        $this->assertEquals('James', $parser->getFirstname());
        $this->assertEquals('van', $parser->getLastnamePrefix());
        $this->assertEquals('Allen', $parser->getLastname());
    }

    /**
     * Regression test: a comma-mode lastname segment with a leading word that ISN'T a
     * recognized prefix (a compound surname with no prefix marker) must not lose that word
     * - it merges into middlename rather than being silently dropped or overwriting
     * segment 2's firstname.
     */
    public function testParseCommaModeCompoundLastnameLeftoverIsNotLost(): void
    {
        $parser = new NameParser();
        $parser->parse('Garcia Marquez, Gabriel');

        $this->assertEquals('Gabriel', $parser->getFirstname());
        $this->assertEquals('Garcia', $parser->getMiddlename());
        $this->assertEquals('Marquez', $parser->getLastname());
    }

    /**
     * Regression test: a third comma segment that isn't a recognized suffix must not be
     * silently discarded.
     */
    public function testParseCommaModeThirdSegmentNonSuffixIsNotLost(): void
    {
        $parser = new NameParser();
        $parser->parse('Smith, John, Michael');

        $this->assertEquals('John', $parser->getFirstname());
        $this->assertEquals('Michael', $parser->getMiddlename());
        $this->assertEquals('Smith', $parser->getLastname());
    }

    /**
     * Regression test: an all-uppercase or all-lowercase word with accented/multibyte
     * characters must still be title-cased correctly (not corrupted by splitting the word
     * at the non-ASCII character).
     */
    public function testParseNormalizesAccentedMonotoneCaseWords(): void
    {
        $lowercase = new NameParser();
        $lowercase->parse('etna übel');
        $this->assertEquals('Übel', $lowercase->getLastname());

        $uppercase = new NameParser();
        $uppercase->parse('JOSÉ GARCÍA');
        $this->assertEquals('José', $uppercase->getFirstname());
        $this->assertEquals('García', $uppercase->getLastname());
    }

    /**
     * Regression test: calling parse() a second time on the same instance must not
     * accumulate state from the first call - fields that are built by concatenation
     * (salutation, suffix) must reset, not append.
     */
    public function testParseResetsStateBetweenRepeatedCallsOnSameInstance(): void
    {
        $parser = new NameParser();
        $parser->parse('Dr. John Smith Jr.');
        $parser->parse('Mrs. Jane Doe Sr.');

        $this->assertEquals('Mrs.', $parser->getSalutation());
        $this->assertEquals('Jane', $parser->getFirstname());
        $this->assertEquals('Doe', $parser->getLastname());
        $this->assertEquals('Sr', $parser->getSuffix());
    }

    public function testCleanNormalizesWhitespace(): void
    {
        $parser = new NameParser();
        $result = $parser->clean("Mr.\r\nPaul\rJoseph\nMaria\tWinters");

        $this->assertEquals('Mr. Paul Joseph Maria Winters', $result);
    }

}
