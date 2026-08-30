<?php

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
        $result = $parser->parse();

        $this->assertEquals('James', $result->getFirstname());
    }

    public function testParseArgumentUpdatesDataWhenConstructorDataWasAlreadySet(): void
    {
        $parser = new NameParser('James Norrington');
        $result = $parser->parse('Elizabeth Swann');

        $this->assertEquals('Elizabeth Swann', $parser->getData());
        $this->assertEquals('Elizabeth', $result->getFirstname());
    }

    public function testParseSimpleFirstLast(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('James Norrington');

        $this->assertEquals('James', $result->getFirstname());
        $this->assertEquals('Norrington', $result->getLastname());
        $this->assertTrue($result->hasFirstname());
        $this->assertTrue($result->hasLastname());
    }

    public function testParseFirstMiddleLast(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Hans Christian Anderssen');

        $this->assertEquals('Hans', $result->getFirstname());
        $this->assertEquals('Christian', $result->getMiddlename());
        $this->assertEquals('Anderssen', $result->getLastname());
    }

    public function testParseSingleWordIsFirstnameOnly(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Adam');

        $this->assertEquals('Adam', $result->getFirstname());
        $this->assertNull($result->getLastname());
    }

    public function testParseSalutationInitialPrefixedLastnameAndSuffix(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Mr Anthony R Von Fange III');

        $this->assertEquals('Mr.', $result->getSalutation());
        $this->assertEquals('Anthony', $result->getFirstname());
        $this->assertEquals('R', $result->getInitials());
        $this->assertEquals('von', $result->getLastnamePrefix());
        $this->assertEquals('Fange', $result->getLastname());
        $this->assertEquals('III', $result->getSuffix());
    }

    public function testParseConsecutiveSalutationsAreBothClaimed(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Rev. Dr John Doe');

        $this->assertEquals('Rev. Dr.', $result->getSalutation());
        $this->assertEquals('John', $result->getFirstname());
        $this->assertEquals('Doe', $result->getLastname());
    }

    public function testParseDottedInitialsPromoteFirstOneToFirstname(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('J. B. Hunt');

        $this->assertEquals('J.', $result->getFirstname());
        $this->assertEquals('B.', $result->getInitials());
        $this->assertEquals('Hunt', $result->getLastname());
    }

    public function testParseCombinedTwoLetterInitialsAreSplit(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('J.B. Hunt');

        $this->assertEquals('J', $result->getFirstname());
        $this->assertEquals('B', $result->getInitials());
        $this->assertEquals('Hunt', $result->getLastname());
    }

    public function testParseSingleInitialWithRealFirstnamePresent(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('M Peter Williams');

        $this->assertEquals('Peter', $result->getFirstname());
        $this->assertEquals('M', $result->getInitials());
        $this->assertEquals('Williams', $result->getLastname());
    }

    /**
     * Regression test: suffix scanning never touches the first two tokens of a name (it
     * reserves them for firstname/lastname), so a bare two-token name never has its second
     * word mistaken for a suffix even when that word ("Senior") is also a valid suffix.
     */
    public function testParseTwoTokenNameNeverTreatsSecondWordAsSuffix(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Jason Senior');

        $this->assertEquals('Jason', $result->getFirstname());
        $this->assertEquals('Senior', $result->getLastname());
        $this->assertNull($result->getSuffix());
    }

    public function testParseMultipleTrailingSuffixesAreAllClaimed(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Edward Dale Senior II');

        $this->assertEquals('Edward', $result->getFirstname());
        $this->assertEquals('Dale', $result->getLastname());
        $this->assertEquals('Senior II', $result->getSuffix());
    }

    /**
     * Regression test: an all-uppercase or all-lowercase word gets title-cased, but a word
     * with existing mixed case (like "MacDonald") is left exactly as typed, since that
     * capitalization is almost always deliberate.
     */
    public function testParseNormalizesMonotoneCaseButPreservesMixedCase(): void
    {
        $allCaps       = new NameParser();
        $allCapsResult = $allCaps->parse('OLD MACDONALD');
        $this->assertEquals('Old', $allCapsResult->getFirstname());
        $this->assertEquals('Macdonald', $allCapsResult->getLastname());

        $mixedCase       = new NameParser();
        $mixedCaseResult = $mixedCase->parse('Old MacDonald');
        $this->assertEquals('MacDonald', $mixedCaseResult->getLastname());
    }

    /**
     * Regression test: "Mc" is a reliable surname-prefix marker, so a monotone-case "Mc"
     * name gets the letter right after it capitalized too, not just the leading "M".
     */
    public function testParseCapitalizesLetterAfterMcPrefix(): void
    {
        $allCaps       = new NameParser();
        $allCapsResult = $allCaps->parse('JAMES MCDONALD');
        $this->assertEquals('McDonald', $allCapsResult->getLastname());

        $lowercase       = new NameParser();
        $lowercaseResult = $lowercase->parse('james mcdonald');
        $this->assertEquals('McDonald', $lowercaseResult->getLastname());
    }

    /**
     * "Mac" is deliberately NOT given the same treatment as "Mc" - it's also the start of
     * many ordinary names ("Macy", "Mack") where blindly capitalizing the next letter would
     * misfire more often than it would help.
     */
    public function testParseDoesNotCapitalizeLetterAfterMacPrefix(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('OLD MACY');

        $this->assertEquals('Macy', $result->getLastname());
    }

    /**
     * Regression test: an apostrophe-prefixed monotone-case surname title-cases correctly on
     * both sides of the apostrophe without any extra prefix-specific logic.
     */
    public function testParseTitleCasesApostrophePrefixedSurname(): void
    {
        $allCaps       = new NameParser();
        $allCapsResult = $allCaps->parse('PATRICK OBRIEN');
        $this->assertEquals('Obrien', $allCapsResult->getLastname());

        $withApostrophe       = new NameParser();
        $withApostropheResult = $withApostrophe->parse("PATRICK O'BRIEN");
        $this->assertEquals("O'Brien", $withApostropheResult->getLastname());
    }

    public function testParseLastnamePrefix(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('James van Allen');

        $this->assertEquals('James', $result->getFirstname());
        $this->assertEquals('van', $result->getLastnamePrefix());
        $this->assertEquals('Allen', $result->getLastname());
        $this->assertTrue($result->hasLastnamePrefix());
    }

    public function testParseChainedLastnamePrefixes(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Ludwig van der Berg');

        $this->assertEquals('Ludwig', $result->getFirstname());
        $this->assertEquals('van der', $result->getLastnamePrefix());
        $this->assertEquals('Berg', $result->getLastname());
    }

    /**
     * Regression test: a prefix word with nothing before it in the name (nothing left to
     * become a firstname) is not folded as a prefix - it's treated as the firstname itself.
     */
    public function testParsePrefixWordAsFirstnameWhenNothingPrecedesIt(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Mr. Van Truong');

        $this->assertEquals('Mr.', $result->getSalutation());
        $this->assertEquals('Van', $result->getFirstname());
        $this->assertNull($result->getLastnamePrefix());
        $this->assertEquals('Truong', $result->getLastname());
    }

    public function testParseNicknameInParentheses(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Jimmy (Bubba) Smith');

        $this->assertEquals('Jimmy', $result->getFirstname());
        $this->assertEquals('Bubba', $result->getNickname());
        $this->assertEquals('Smith', $result->getLastname());
        $this->assertEquals('(Bubba)', $result->getNickname(true));
    }

    public function testParseMultiWordNicknameInQuotes(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Jimmy "Bubba Junior" Smith');

        $this->assertEquals('Bubba Junior', $result->getNickname());
    }

    public function testParseCommaModeLastFirst(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Fraser, Joshua');

        $this->assertEquals('Joshua', $result->getFirstname());
        $this->assertEquals('Fraser', $result->getLastname());
    }

    public function testParseCommaModeWithSalutation(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Mrs. Brown, Amanda');

        $this->assertEquals('Mrs.', $result->getSalutation());
        $this->assertEquals('Amanda', $result->getFirstname());
        $this->assertEquals('Brown', $result->getLastname());
    }

    public function testParseCommaModeLastFirstMiddle(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Smith, John Eric');

        $this->assertEquals('Smith', $result->getLastname());
        $this->assertEquals('John', $result->getFirstname());
        $this->assertEquals('Eric', $result->getMiddlename());
    }

    public function testParseCommaModeThreeSegmentsWithSuffix(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Williams, Hank, Jr.');

        $this->assertEquals('Hank', $result->getFirstname());
        $this->assertEquals('Williams', $result->getLastname());
        $this->assertEquals('Jr', $result->getSuffix());
    }

    /**
     * Regression test: segment 1 (before the first comma) can itself contain a full
     * "Firstname Lastname [Suffix]" - firstname extraction must run unconditionally there,
     * not only when nothing else matched, otherwise the firstname is lost.
     */
    public function testParseCommaModeFirstSegmentContainsFullNamePlusSuffix(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Anthony Von Fange III, PHD');

        $this->assertEquals('Anthony', $result->getFirstname());
        $this->assertEquals('von', $result->getLastnamePrefix());
        $this->assertEquals('Fange', $result->getLastname());
        $this->assertEquals('III', $result->getSuffix());
        $this->assertEquals('PhD', $result->getCredentials());
    }

    /**
     * Regression test: when segment 1's leftover is what ends up becoming the given name
     * (segment 2 didn't supply one) AND that leftover has more than one word, it must still
     * split into firstname (first word) and middlename (the rest) - not collapse into a
     * single firstname string.
     */
    public function testParseCommaModeFirstSegmentMultiWordLeftoverSplitsIntoFirstnameAndMiddlename(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('John Michael Smith, MD');

        $this->assertEquals('John', $result->getFirstname());
        $this->assertEquals('Michael', $result->getMiddlename());
        $this->assertEquals('Smith', $result->getLastname());
        $this->assertNull($result->getSuffix());
        $this->assertEquals('MD', $result->getCredentials());
    }

    public function testParseCommaModeWithInitial(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Kirk, James T.');

        $this->assertEquals('James', $result->getFirstname());
        $this->assertEquals('T.', $result->getInitials());
        $this->assertEquals('Kirk', $result->getLastname());
    }

    public function testGetGivenNameCombinesFirstnameInitialsAndMiddlename(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Mr Anthony R Von Fange III');

        $this->assertEquals('Anthony R', $result->getGivenName());
    }

    public function testGetFullNameCombinesGivenNamePrefixAndLastname(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Mr Anthony R Von Fange III');

        $this->assertEquals('Anthony R von Fange', $result->getFullName());
    }

    public function testToStringComposesAllParts(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Jimmy (Bubba) Smith');

        $this->assertEquals('Jimmy (Bubba) Smith', (string) $result);
    }

    public function testToArrayReturnsAllParsedFields(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Mr Anthony R Von Fange III');

        $this->assertEquals([
            'salutation'     => 'Mr.',
            'firstname'      => 'Anthony',
            'initials'       => 'R',
            'middlename'     => null,
            'nickname'       => null,
            'lastnamePrefix' => 'von',
            'lastname'       => 'Fange',
            'suffix'         => 'III',
            'credentials'    => null,
            'confidence'     => 1.0,
        ], $result->toArray());
    }

    public function testConfidenceDefaultsToFullConfidenceForAClearName(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('James Norrington');

        $this->assertEquals(1.0, $result->getConfidence());
        $this->assertTrue($result->isConfident());
    }

    /**
     * Regression test: promoting a queued initial to firstname (no real firstname token
     * was ever given) lowers confidence, since it's a stand-in rather than an actual match.
     */
    public function testConfidenceIsLoweredWhenAnInitialIsPromotedToFirstname(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('J. B. Hunt');

        $this->assertEquals(0.75, $result->getConfidence());
        $this->assertTrue($result->isConfident());
        $this->assertFalse($result->isConfident(0.8));
    }

    /**
     * Regression test: a comma-mode leftover absorbed into middlename (content that didn't
     * fit any recognized category) lowers confidence.
     */
    public function testConfidenceIsLoweredWhenCommaModeLeftoverIsAbsorbedIntoMiddlename(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Garcia Marquez, Gabriel');

        $this->assertEquals(0.75, $result->getConfidence());
    }

    public function testParseSpaceSeparatedCredentialSuffixGoesToCredentialsNotSuffix(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('John Smith MD');

        $this->assertEquals('John', $result->getFirstname());
        $this->assertEquals('Smith', $result->getLastname());
        $this->assertNull($result->getSuffix());
        $this->assertEquals('MD', $result->getCredentials());
        $this->assertTrue($result->hasCredentials());
    }

    /**
     * Regression test: a generational suffix and a credential trailing together must each
     * land in their own field, in the order they appeared, not merged into one string.
     */
    public function testParseMixedGenerationalSuffixAndCredentialsAreSplitButOrderPreserved(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Edward Dale Smith III MD');

        $this->assertEquals('III', $result->getSuffix());
        $this->assertEquals('MD', $result->getCredentials());
    }

    public function testHasCredentialsDefaultsToFalseWhenNoneFound(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('James Norrington');

        $this->assertFalse($result->hasCredentials());
        $this->assertNull($result->getCredentials());
    }

    /**
     * Characterization test: unlike theiconic/name-parser (which can silently drop an
     * unrecognized leading word), this parser never discards tokens - a stray word that
     * doesn't match any category becomes part of the name content instead of vanishing.
     */
    public function testParseNeverSilentlyDropsAnUnrecognizedLeadingWord(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('The Rev. Mark Williams');

        $this->assertEquals('Rev.', $result->getSalutation());
        $this->assertEquals('The', $result->getFirstname());
        $this->assertEquals('Mark', $result->getMiddlename());
        $this->assertEquals('Williams', $result->getLastname());
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
        $result = $parser->parse('Dr Jr');

        $this->assertEquals('Dr.', $result->getSalutation());
        $this->assertNull($result->getFirstname());
        $this->assertEquals('Jr', $result->getLastname());
    }

    /**
     * Regression test: a lastname prefix in the comma-mode lastname segment (segment 1)
     * must still be recognized and must not swallow or lose the firstname that comes from
     * segment 2.
     */
    public function testParseCommaModeLastnamePrefixInFirstSegment(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('van Allen, James');

        $this->assertEquals('James', $result->getFirstname());
        $this->assertEquals('van', $result->getLastnamePrefix());
        $this->assertEquals('Allen', $result->getLastname());
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
        $result = $parser->parse('Garcia Marquez, Gabriel');

        $this->assertEquals('Gabriel', $result->getFirstname());
        $this->assertEquals('Garcia', $result->getMiddlename());
        $this->assertEquals('Marquez', $result->getLastname());
    }

    /**
     * Regression test: a third comma segment that isn't a recognized suffix must not be
     * silently discarded.
     */
    public function testParseCommaModeThirdSegmentNonSuffixIsNotLost(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Smith, John, Michael');

        $this->assertEquals('John', $result->getFirstname());
        $this->assertEquals('Michael', $result->getMiddlename());
        $this->assertEquals('Smith', $result->getLastname());
    }

    /**
     * Regression test: a name with more than 3 comma-separated segments must not silently
     * drop anything past the third segment.
     */
    public function testParseCommaModeFourthSegmentIsNotLost(): void
    {
        $parser = new NameParser();
        $result = $parser->parse('Smith, John, PhD, Esq');

        $this->assertEquals('John', $result->getFirstname());
        $this->assertEquals('Smith', $result->getLastname());
        $this->assertNull($result->getSuffix());
        $this->assertEquals('PhD Esq', $result->getCredentials());
    }

    /**
     * Regression test: an all-caps 2-letter lastname-prefix word ("DE", "LA", "ST", "DU")
     * must not be mistaken for two combined initials just because the input is all-caps -
     * that heuristic exists for cases like "JR Smith", not for prefix words.
     */
    public function testParseAllCapsDoesNotShredLastnamePrefixIntoInitials(): void
    {
        $deLuca       = new NameParser();
        $deLucaResult = $deLuca->parse('JAMES DE LUCA');
        $this->assertNull($deLucaResult->getInitials());
        $this->assertEquals('de', $deLucaResult->getLastnamePrefix());
        $this->assertEquals('Luca', $deLucaResult->getLastname());

        $stJohn       = new NameParser();
        $stJohnResult = $stJohn->parse('MARY ST JOHN');
        $this->assertNull($stJohnResult->getInitials());
        $this->assertEquals('St.', $stJohnResult->getLastnamePrefix());
        $this->assertEquals('John', $stJohnResult->getLastname());

        $deLaCruz       = new NameParser();
        $deLaCruzResult = $deLaCruz->parse('JOSE M DE LA CRUZ JR');
        $this->assertEquals('M', $deLaCruzResult->getInitials());
        $this->assertEquals('de la', $deLaCruzResult->getLastnamePrefix());
        $this->assertEquals('Cruz', $deLaCruzResult->getLastname());
        $this->assertEquals('Jr', $deLaCruzResult->getSuffix());
    }

    /**
     * Regression test: an all-uppercase or all-lowercase word with accented/multibyte
     * characters must still be title-cased correctly (not corrupted by splitting the word
     * at the non-ASCII character).
     */
    public function testParseNormalizesAccentedMonotoneCaseWords(): void
    {
        $lowercase       = new NameParser();
        $lowercaseResult = $lowercase->parse('etna übel');
        $this->assertEquals('Übel', $lowercaseResult->getLastname());

        $uppercase       = new NameParser();
        $uppercaseResult = $uppercase->parse('JOSÉ GARCÍA');
        $this->assertEquals('José', $uppercaseResult->getFirstname());
        $this->assertEquals('García', $uppercaseResult->getLastname());
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
        $result = $parser->parse('Mrs. Jane Doe Sr.');

        $this->assertEquals('Mrs.', $result->getSalutation());
        $this->assertEquals('Jane', $result->getFirstname());
        $this->assertEquals('Doe', $result->getLastname());
        $this->assertEquals('Sr', $result->getSuffix());
    }

    /**
     * Regression test: a leading/trailing/doubled comma leaves an empty comma-mode segment
     * ("" between two commas, or before a leading comma). That must be treated as no tokens
     * at all - not a single blank word claimed as lastname - so a field that was never
     * actually given content stays null rather than becoming an empty string.
     */
    public function testParseWithLeadingOrDoubledCommaLeavesUnaffectedFieldsNull(): void
    {
        $leading       = new NameParser();
        $leadingResult = $leading->parse(',John Smith,');
        $this->assertEquals('John', $leadingResult->getFirstname());
        $this->assertNull($leadingResult->getLastname());

        $onlyCommas       = new NameParser();
        $onlyCommasResult = $onlyCommas->parse(',,');
        $this->assertNull($onlyCommasResult->getFirstname());
        $this->assertNull($onlyCommasResult->getLastname());
    }

    /**
     * Dirty-input corpus: doubled delimiters, irregular whitespace, and trailing punctuation
     * must never crash the parser, whatever they end up parsing to.
     */
    public function testParseDoesNotCrashOnDirtyInput(): void
    {
        $inputs = [
            'John,, Smith',
            'John   Smith',
            'John Smith,,',
            'John Smith .',
            "John\t\tSmith",
            '!!!',
            'John Smith  ,  Jr  ,,  ',
        ];

        foreach ($inputs as $input) {
            $parser = new NameParser();
            $result = $parser->parse($input);
            $this->assertInstanceOf(\Pop\Parser\Name\NameResult::class, $result);
        }
    }

    public function testCleanNormalizesWhitespace(): void
    {
        $parser = new NameParser();
        $result = $parser->clean("Mr.\r\nPaul\rJoseph\nMaria\tWinters");

        $this->assertEquals('Mr. Paul Joseph Maria Winters', $result);
    }

}
