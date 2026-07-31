<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Parser\Name;

use Pop\Parser\AbstractParser;
use Pop\Parser\Exception;

/**
 * Name parser class
 *
 * @category   Pop
 * @package    Pop\Parser
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class NameParser extends AbstractParser
{

    /**
     * Salutation
     * @var ?string
     * */
    protected ?string $salutation = null;

    /**
     * First name
     * @var ?string
     * */
    protected ?string $firstname = null;

    /**
     * Middle name
     * @var ?string
     * */
    protected ?string $middlename = null;

    /**
     * Nickname
     * @var ?string
     * */
    protected ?string $nickname = null;

    /**
     * Initials
     * @var ?string
     * */
    protected ?string $initials = null;

    /**
     * Lastname prefix
     * @var ?string
     * */
    protected ?string $lastnamePrefix = null;

    /**
     * Last name
     * @var ?string
     * */
    protected ?string $lastname = null;

    /**
     * Suffix
     * @var ?string
     * */
    protected ?string $suffix = null;

    /**
     * Queue of raw tokens claimed as initials during extraction, before the first one is
     * (potentially) promoted back to firstname by finalizeInitials()
     * @var array
     * */
    protected array $initialsQueue = [];

    /**
     * Method to get salutation
     *
     * @return ?string
     */
    public function getSalutation(): ?string
    {
        return $this->salutation;
    }

    /**
     * Method to get first name
     *
     * @return ?string
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * Method to get middle name
     *
     * @return ?string
     */
    public function getMiddlename(): ?string
    {
        return $this->middlename;
    }

    /**
     * Method to get nickname
     *
     * @param  bool $wrap
     * @return ?string
     */
    public function getNickname(bool $wrap = false): ?string
    {
        if (($this->nickname !== null) && $wrap) {
            return '(' . $this->nickname . ')';
        }

        return $this->nickname;
    }

    /**
     * Method to get initials
     *
     * @return ?string
     */
    public function getInitials(): ?string
    {
        return $this->initials;
    }

    /**
     * Method to get lastname prefix
     *
     * @return ?string
     */
    public function getLastnamePrefix(): ?string
    {
        return $this->lastnamePrefix;
    }

    /**
     * Method to get last name
     *
     * @return ?string
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * Method to get suffix
     *
     * @return ?string
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * Method to get the given name (first name, initials and middle name, in that order)
     *
     * @return ?string
     */
    public function getGivenName(): ?string
    {
        $parts = array_filter([$this->firstname, $this->initials, $this->middlename]);
        return !empty($parts) ? implode(' ', $parts) : null;
    }

    /**
     * Method to get the full name (given name plus lastname prefix and lastname)
     *
     * @return ?string
     */
    public function getFullName(): ?string
    {
        $parts = array_filter([$this->getGivenName(), $this->lastnamePrefix, $this->lastname]);
        return !empty($parts) ? implode(' ', $parts) : null;
    }

    /**
     * Has salutation
     *
     * @return bool
     */
    public function hasSalutation(): bool
    {
        return !empty($this->salutation);
    }

    /**
     * Has first name
     *
     * @return bool
     */
    public function hasFirstname(): bool
    {
        return !empty($this->firstname);
    }

    /**
     * Has middle name
     *
     * @return bool
     */
    public function hasMiddlename(): bool
    {
        return !empty($this->middlename);
    }

    /**
     * Has nickname
     *
     * @return bool
     */
    public function hasNickname(): bool
    {
        return !empty($this->nickname);
    }

    /**
     * Has initials
     *
     * @return bool
     */
    public function hasInitials(): bool
    {
        return !empty($this->initials);
    }

    /**
     * Has lastname prefix
     *
     * @return bool
     */
    public function hasLastnamePrefix(): bool
    {
        return !empty($this->lastnamePrefix);
    }

    /**
     * Has last name
     *
     * @return bool
     */
    public function hasLastname(): bool
    {
        return !empty($this->lastname);
    }

    /**
     * Has suffix
     *
     * @return bool
     */
    public function hasSuffix(): bool
    {
        return !empty($this->suffix);
    }

    /**
     * Parse method
     *
     * @param  ?string $name
     * @throws Exception
     * @return static
     */
    public function parse(?string $name = null): static
    {
        if (empty($this->data) && empty($name)) {
            throw new Exception('Error: You must pass a name string to the parser object.');
        }

        if ((null === $name) && !empty($this->data)) {
            $name = $this->data;
        } else if ((null !== $name) && empty($this->data)) {
            $this->data = $name;
        }

        $name = $this->clean($name);

        if ($name === '') {
            throw new Exception('Error: You must pass a name string to the parser object.');
        }

        // Several fields below are built by concatenation as extraction proceeds (e.g.
        // consecutive salutations, multiple trailing suffixes). Reset them here so a second
        // parse() call on the same instance starts clean rather than appending onto results
        // from a previous call.
        $this->salutation     = null;
        $this->firstname      = null;
        $this->middlename     = null;
        $this->nickname       = null;
        $this->initials       = null;
        $this->lastnamePrefix = null;
        $this->lastname       = null;
        $this->suffix         = null;
        $this->initialsQueue  = [];

        $nameValues = new NameValues();

        if (str_contains($name, ',')) {
            $this->parseCommaMode($name, $nameValues);
        } else {
            $tokens        = $this->tokenize($name);
            $originalCount = count($tokens);
            $tokens        = $this->extractNickname($tokens, $nameValues);
            $tokens        = $this->extractSalutation($tokens, $nameValues);
            $tokens        = $this->extractSuffix($tokens, $nameValues, 2);
            $tokens        = $this->extractInitials($tokens, false);
            $tokens        = $this->extractLastname($tokens, $nameValues, false, $originalCount);
            $tokens        = $this->extractFirstname($tokens);
            $tokens        = $this->extractMiddlename($tokens);
            $this->absorbLeftovers($tokens);
        }

        $this->finalizeInitials();

        return $this;
    }

    /**
     * To array method
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'salutation'     => $this->salutation,
            'firstname'      => $this->firstname,
            'initials'       => $this->initials,
            'middlename'     => $this->middlename,
            'nickname'       => $this->nickname,
            'lastnamePrefix' => $this->lastnamePrefix,
            'lastname'       => $this->lastname,
            'suffix'         => $this->suffix,
        ];
    }

    /**
     * Clean method
     *
     * @param  string $name
     * @return string
     */
    public function clean(string $name): string
    {
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    /**
     * Tokenize method
     *
     * @param  string $name
     * @return array
     */
    protected function tokenize(string $name): array
    {
        return preg_split('/\s+/', trim($name));
    }

    /**
     * Normalize the case of a single word: an all-uppercase or all-lowercase word gets
     * title-cased ("MACDONALD" / "macdonald" -> "Macdonald"); a word with any existing
     * mixed case (e.g. "MacDonald", "McDonald", "O'Brien") is left exactly as typed, since
     * that mixed case is almost always deliberate.
     *
     * @param  string $word
     * @return string
     */
    protected function normalizeCase(string $word): string
    {
        $stripped = str_replace('.', '', $word);

        if (($stripped === mb_strtoupper($stripped)) || ($stripped === mb_strtolower($stripped))) {
            return preg_replace_callback('/\p{L}+/u', function ($matches) {
                return mb_convert_case($matches[0], MB_CASE_TITLE);
            }, $word);
        }

        return $word;
    }

    /**
     * Normalize the case of each word in an array and join them with a space
     *
     * @param  array $words
     * @return string
     */
    protected function normalizeWords(array $words): string
    {
        return implode(' ', array_map([$this, 'normalizeCase'], $words));
    }

    /**
     * Parse comma-separated "Last, First Middle[, Suffix]" format
     *
     * @param  string      $name
     * @param  NameValues  $nameValues
     * @return void
     */
    protected function parseCommaMode(string $name, NameValues $nameValues): void
    {
        $segments = array_map('trim', explode(',', $name));

        // Segment 1 (before the first comma): the lastname segment. Salutation, suffix and
        // lastname(-with-prefix) extraction run here. Whatever's left over (e.g. "Garcia" in
        // "Garcia Marquez, Gabriel", or a lastname-prefix word extractLastname's ordinary
        // guards wouldn't fold at position 0) is deliberately NOT assigned to firstname here
        // - it's carried forward and absorbed only after segment 2 runs, so it can never
        // silently overwrite or lose to segment 2's own firstname; absorbLeftovers() merges
        // it into middlename once firstname is already set instead.
        $segment1       = $this->tokenize($segments[0]);
        $originalCount1 = count($segment1);
        $segment1       = $this->extractSalutation($segment1, $nameValues);
        $segment1       = $this->extractSuffix($segment1, $nameValues, 0, true);
        $segment1       = $this->extractLastname($segment1, $nameValues, true, $originalCount1);

        // Segment 2 (between commas, or everything after the first comma): the given-name segment
        if (isset($segments[1]) && ($segments[1] !== '')) {
            $segment2 = $this->tokenize($segments[1]);
            $segment2 = $this->extractSalutation($segment2, $nameValues);
            $segment2 = $this->extractSuffix($segment2, $nameValues, 0, true, true);
            $segment2 = $this->extractNickname($segment2, $nameValues);
            $segment2 = $this->extractInitials($segment2, true);
            $segment2 = $this->extractFirstname($segment2);
            $segment2 = $this->extractMiddlename($segment2);
            $this->absorbLeftovers($segment2);
        }

        // Now that segment 2 has had its chance to claim the firstname slot: if it didn't
        // (firstname is still null), segment 1's leftover IS the given name, so split it the
        // normal way (first word -> firstname, the rest -> middlename) rather than joining it
        // into one string. If segment 2 already provided a firstname, segment 1's leftover is
        // secondary content - absorbLeftovers() merges the whole thing into middlename.
        if ($this->firstname === null) {
            $segment1 = $this->extractFirstname($segment1);
            $segment1 = $this->extractMiddlename($segment1);
        }
        $this->absorbLeftovers($segment1);

        // Segment 3 (after a second comma, if present): suffix only, with any non-suffix
        // leftover absorbed rather than discarded (e.g. "Smith, John, Michael" must not lose
        // "Michael" just because it isn't a recognized suffix).
        if (isset($segments[2]) && ($segments[2] !== '')) {
            $segment3 = $this->tokenize($segments[2]);
            $segment3 = $this->extractSuffix($segment3, $nameValues, 0, true);
            $this->absorbLeftovers($segment3);
        }
    }

    /**
     * Absorb any tokens no extraction step claimed: the first time this is called with a
     * non-empty leftover, it becomes firstname (since every name needs one); after that,
     * leftovers are appended to middlename. This is a deliberate difference from
     * theiconic/name-parser, which can silently drop an unrecognized leading word (e.g. "The"
     * in "The Rev. Mark Williams") - here nothing is ever discarded.
     *
     * @param  array $tokens
     * @return void
     */
    protected function absorbLeftovers(array $tokens): void
    {
        if (empty($tokens)) {
            return;
        }

        $text = $this->normalizeWords($tokens);

        if ($this->firstname === null) {
            $this->firstname = $text;
        } else {
            $this->middlename = trim(($this->middlename ?? '') . ' ' . $text);
        }
    }

    /**
     * If no raw token ever became firstname but one or more initials were set aside, promote
     * the FIRST claimed initial back to firstname - handles "J. B. Hunt" (firstname="J.",
     * initials="B.", lastname="Hunt"), since a name consisting only of initials plus a
     * lastname still needs a firstname.
     *
     * @return void
     */
    protected function finalizeInitials(): void
    {
        if (empty($this->initialsQueue)) {
            return;
        }

        if ($this->firstname === null) {
            $promoted        = array_shift($this->initialsQueue);
            $this->firstname = $this->normalizeCase($promoted);
        }

        if (!empty($this->initialsQueue)) {
            $this->initials = trim(($this->initials ?? '') . ' ' . $this->normalizeWords($this->initialsQueue));
        }

        $this->initialsQueue = [];
    }

    /**
     * Extract nickname
     *
     * Scans for a token starting with an opening delimiter and collects tokens until one
     * ends with the matching closing delimiter (supports multi-word nicknames).
     *
     * @param  array      $tokens
     * @param  NameValues $nameValues
     * @return array
     */
    protected function extractNickname(array $tokens, NameValues $nameValues): array
    {
        $delimiters = $nameValues->getNicknameDelimiters();
        $openChars  = array_keys($delimiters);

        foreach ($tokens as $start => $token) {
            $firstChar = mb_substr($token, 0, 1);
            if (!in_array($firstChar, $openChars, true)) {
                continue;
            }

            $closeChar = $delimiters[$firstChar];
            $end       = null;

            for ($i = $start; $i < count($tokens); $i++) {
                if (str_ends_with($tokens[$i], $closeChar) && (($i > $start) || (strlen($tokens[$i]) > 1))) {
                    $end = $i;
                    break;
                }
            }

            if ($end === null) {
                continue;
            }

            $span             = array_slice($tokens, $start, $end - $start + 1);
            $span[0]          = ltrim($span[0], $firstChar);
            $lastIndex        = count($span) - 1;
            $span[$lastIndex] = rtrim($span[$lastIndex], $closeChar);
            $span             = array_map(fn($word) => trim($word, '\'"'), $span);
            $span             = array_filter($span, fn($word) => $word !== '');

            $this->nickname = $this->normalizeWords($span);
            array_splice($tokens, $start, $end - $start + 1);
            break;
        }

        return array_values($tokens);
    }

    /**
     * Extract salutation
     *
     * Scans from the start of the tokens (bounded to roughly the first half) for matches
     * against the salutation list, checked as both single tokens and multi-word phrases.
     * Multiple consecutive salutations (e.g. "Rev. Dr John Doe") are all claimed.
     *
     * @param  array      $tokens
     * @param  NameValues $nameValues
     * @return array
     */
    protected function extractSalutation(array $tokens, NameValues $nameValues): array
    {
        $salutations = $nameValues->getSalutations();
        $claimed     = [];
        $index       = 0;

        while (true) {
            $max = !empty($tokens) ? max(1, (int) floor(count($tokens) / 2)) : 0;
            if (($index >= $max) || ($index >= count($tokens))) {
                break;
            }

            $matchedLength = null;
            $matchedValue  = null;

            foreach ($salutations as $key => $display) {
                $keyWords = explode(' ', $key);
                $length   = count($keyWords);
                if (($index + $length) > count($tokens)) {
                    continue;
                }
                $subset     = array_slice($tokens, $index, $length);
                $subsetKeys = array_map(fn($word) => strtolower(str_replace('.', '', $word)), $subset);
                if ($subsetKeys === $keyWords) {
                    $matchedLength = $length;
                    $matchedValue  = $display;
                    break;
                }
            }

            if ($matchedLength === null) {
                $index++;
                continue;
            }

            $claimed[] = $matchedValue;
            array_splice($tokens, $index, $matchedLength);
        }

        if (!empty($claimed)) {
            $this->salutation = trim(($this->salutation ?? '') . ' ' . implode(' ', $claimed));
        }

        return array_values($tokens);
    }

    /**
     * Extract suffix
     *
     * Scans from the end backward while trailing tokens keep matching the suffix list,
     * stopping before it would eat into the reserved leading tokens (or, in single-part
     * mode, matches only when exactly one token remains).
     *
     * @param  array      $tokens
     * @param  NameValues $nameValues
     * @param  int        $reservedParts
     * @param  bool       $matchSinglePart
     * @param  bool       $reserveLastToken
     * @return array
     */
    protected function extractSuffix(
        array $tokens,
        NameValues $nameValues,
        int $reservedParts = 2,
        bool $matchSinglePart = false,
        bool $reserveLastToken = false
    ): array
    {
        $suffixes = $nameValues->getSuffixes();

        if ($matchSinglePart && (count($tokens) === 1)) {
            $key = strtolower(str_replace('.', '', $tokens[0]));
            if (isset($suffixes[$key])) {
                $this->suffix = trim(($this->suffix ?? '') . ' ' . $suffixes[$key]);
                return [];
            }
            return $tokens;
        }

        $claimed = [];
        $stop    = $reserveLastToken ? 1 : $reservedParts;
        $index   = count($tokens) - 1;

        while ($index >= $stop) {
            $key = strtolower(str_replace('.', '', $tokens[$index]));
            if (!isset($suffixes[$key])) {
                break;
            }
            array_unshift($claimed, $suffixes[$key]);
            $index--;
        }

        if (!empty($claimed)) {
            $count = count($claimed);
            array_splice($tokens, count($tokens) - $count, $count);
            $this->suffix = trim(($this->suffix ?? '') . ' ' . implode(' ', $claimed));
        }

        return array_values($tokens);
    }

    /**
     * Extract initials
     *
     * A remaining single letter (optionally with a trailing period) is an initial. An
     * all-caps 2-letter run (e.g. "JR") is split into two separate initials first. The very
     * last remaining token is never treated as an initial unless $matchLastPart is true.
     *
     * @param  array $tokens
     * @param  bool  $matchLastPart
     * @return array
     */
    protected function extractInitials(array $tokens, bool $matchLastPart): array
    {
        $last = count($tokens) - 1;
        for ($i = 0; $i < count($tokens); $i++) {
            if (!$matchLastPart && ($i === $last)) {
                continue;
            }
            $stripped = str_replace('.', '', $tokens[$i]);
            if ((strlen($stripped) === 2) && ($stripped === strtoupper($stripped)) && ctype_alpha($stripped)) {
                array_splice($tokens, $i, 1, [$stripped[0], $stripped[1]]);
                $last = count($tokens) - 1;
                $i++;
            }
        }

        $last           = count($tokens) - 1;
        $claimedIndexes = [];

        foreach ($tokens as $i => $token) {
            if (!$matchLastPart && ($i === $last)) {
                continue;
            }
            $stripped = str_replace('.', '', $token);
            if (strlen($stripped) === 1) {
                $claimedIndexes[] = $i;
            }
        }

        foreach ($claimedIndexes as $i) {
            $this->initialsQueue[] = $tokens[$i];
        }
        foreach (array_reverse($claimedIndexes) as $i) {
            array_splice($tokens, $i, 1);
        }

        return array_values($tokens);
    }

    /**
     * Extract lastname (with prefix folding)
     *
     * Scans remaining tokens from the end backward, claiming them as lastname. A claimed run
     * immediately preceded by a recognized lastname-prefix word, with at least one unclaimed
     * token still before it, folds the prefix into lastnamePrefix. Stops once it hits a word
     * long enough to look like a complete lastname on its own with more still unclaimed
     * before it - what keeps a middle name from being swallowed into the lastname.
     *
     * $originalCount is the token count BEFORE any earlier extraction step ran; it (not the
     * current, shrunk token count) determines whether there was ever more than one word in
     * this name to begin with, since a name reduced to a single remaining token by earlier
     * steps (e.g. "J. B. Hunt" -> "Hunt" once both initials are claimed) should still have
     * that token claimed as lastname.
     *
     * @param  array      $tokens
     * @param  NameValues $nameValues
     * @param  bool       $singlePartOk
     * @param  ?int       $originalCount
     * @return array
     */
    protected function extractLastname(
        array $tokens,
        NameValues $nameValues,
        bool $singlePartOk = false,
        ?int $originalCount = null
    ): array
    {
        $originalCount ??= count($tokens);

        if ((!$singlePartOk && ($originalCount < 2)) || empty($tokens)) {
            return $tokens;
        }

        $prefixes        = $nameValues->getLastnamePrefixes();
        $lastnameWords   = [];
        $prefixWords     = [];
        $index           = count($tokens) - 1;
        $claimedAny      = false;
        $lastClaimedWord = null;

        while ($index >= 0) {
            $word = $tokens[$index];
            $key  = strtolower(str_replace('.', '', $word));

            // The "must be at index > 0" guard exists to always leave at least one token
            // unclaimed for a firstname - but in singlePartOk mode (comma-mode segment 1),
            // firstname comes from segment 2, not this segment, so there's nothing to
            // reserve: allow folding and continued lastname-claiming all the way to index 0.
            if ($claimedAny && isset($prefixes[$key]) && (($index > 0) || $singlePartOk)) {
                array_unshift($prefixWords, $prefixes[$key]);
                array_splice($tokens, $index, 1);
                $index--;
                continue;
            }

            if ($claimedAny) {
                if (!$singlePartOk && ($index < 1)) {
                    break;
                }
                if (strlen($lastClaimedWord) >= 3) {
                    break;
                }
            }

            array_unshift($lastnameWords, $word);
            array_splice($tokens, $index, 1);
            $claimedAny      = true;
            $lastClaimedWord = $word;
            $index--;

            if (!$singlePartOk && ($index < 0)) {
                break;
            }
        }

        if (!empty($lastnameWords)) {
            $this->lastname = $this->normalizeWords($lastnameWords);
        }
        if (!empty($prefixWords)) {
            $this->lastnamePrefix = implode(' ', $prefixWords);
        }

        return array_values($tokens);
    }

    /**
     * Extract firstname
     *
     * If exactly one raw token remains, it's the firstname outright; otherwise the first
     * remaining token becomes firstname.
     *
     * @param  array $tokens
     * @return array
     */
    protected function extractFirstname(array $tokens): array
    {
        if (empty($tokens)) {
            return $tokens;
        }

        $this->firstname = $this->normalizeCase($tokens[0]);
        array_splice($tokens, 0, 1);

        return array_values($tokens);
    }

    /**
     * Extract middlename
     *
     * Whatever raw tokens remain after firstname extraction join as middlename.
     *
     * @param  array $tokens
     * @return array
     */
    protected function extractMiddlename(array $tokens): array
    {
        if (empty($tokens)) {
            return $tokens;
        }

        $this->middlename = trim(($this->middlename ?? '') . ' ' . $this->normalizeWords($tokens));

        return [];
    }

    /**
     * To string method
     *
     * @return string
     */
    public function __toString(): string
    {
        $parts = array_filter([
            $this->salutation,
            $this->getGivenName(),
            $this->getNickname(true),
            $this->lastnamePrefix,
            $this->lastname,
            $this->suffix,
        ]);

        return implode(' ', $parts);
    }

}
