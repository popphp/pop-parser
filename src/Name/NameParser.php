<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
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
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class NameParser extends AbstractParser
{

    /**
     * Parse method
     *
     * Builds up the parsed fields in a local $fields array (and a local $initialsQueue),
     * threaded by reference through each extraction step, rather than on $this - the
     * parser holds no parsed-field state of its own. The very last step wraps that array
     * into an immutable NameResult, which is what's actually returned.
     *
     * @param  ?string $name
     * @throws Exception
     * @return NameResult
     */
    public function parse(?string $name = null): NameResult
    {
        if (empty($this->data) && empty($name)) {
            throw new Exception('Error: You must pass a name string to the parser object.');
        }

        if ((null === $name) && !empty($this->data)) {
            $name = $this->data;
        } else if (null !== $name) {
            $this->data = $name;
        }

        $name = $this->clean($name);

        if ($name === '') {
            throw new Exception('Error: You must pass a name string to the parser object.');
        }

        $fields = [
            'salutation'     => null,
            'firstname'      => null,
            'middlename'     => null,
            'nickname'       => null,
            'initials'       => null,
            'lastnamePrefix' => null,
            'lastname'       => null,
            'suffix'         => null,
        ];
        $initialsQueue = [];

        $nameValues = new NameValues();

        if (str_contains($name, ',')) {
            $this->parseCommaMode($name, $nameValues, $fields, $initialsQueue);
        } else {
            $tokens        = $this->tokenize($name);
            $originalCount = count($tokens);
            $tokens        = $this->extractNickname($tokens, $nameValues, $fields);
            $tokens        = $this->extractSalutation($tokens, $nameValues, $fields);
            $tokens        = $this->extractSuffix($tokens, $nameValues, $fields, 2);
            $tokens        = $this->extractInitials($tokens, false, $nameValues, $initialsQueue);
            $tokens        = $this->extractLastname($tokens, $nameValues, $fields, false, $originalCount);
            $tokens        = $this->extractFirstname($tokens, $fields);
            $tokens        = $this->extractMiddlename($tokens, $fields);
            $this->absorbLeftovers($tokens, $fields);
        }

        $this->finalizeInitials($fields, $initialsQueue);

        $this->result = new NameResult($fields);

        return $this->result;
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
     * @param  string     $name
     * @param  NameValues $nameValues
     * @param  array      $fields
     * @param  array      $initialsQueue
     * @return void
     */
    protected function parseCommaMode(string $name, NameValues $nameValues, array &$fields, array &$initialsQueue): void
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
        $segment1       = $this->extractSalutation($segment1, $nameValues, $fields);
        $segment1       = $this->extractSuffix($segment1, $nameValues, $fields, 0, true);
        $segment1       = $this->extractLastname($segment1, $nameValues, $fields, true, $originalCount1);

        // Segment 2 (between commas, or everything after the first comma): the given-name segment
        if (isset($segments[1]) && ($segments[1] !== '')) {
            $segment2 = $this->tokenize($segments[1]);
            $segment2 = $this->extractSalutation($segment2, $nameValues, $fields);
            $segment2 = $this->extractSuffix($segment2, $nameValues, $fields, 0, true, true);
            $segment2 = $this->extractNickname($segment2, $nameValues, $fields);
            $segment2 = $this->extractInitials($segment2, true, $nameValues, $initialsQueue);
            $segment2 = $this->extractFirstname($segment2, $fields);
            $segment2 = $this->extractMiddlename($segment2, $fields);
            $this->absorbLeftovers($segment2, $fields);
        }

        // Now that segment 2 has had its chance to claim the firstname slot: if it didn't
        // (firstname is still null), segment 1's leftover IS the given name, so split it the
        // normal way (first word -> firstname, the rest -> middlename) rather than joining it
        // into one string. If segment 2 already provided a firstname, segment 1's leftover is
        // secondary content - absorbLeftovers() merges the whole thing into middlename.
        if ($fields['firstname'] === null) {
            $segment1 = $this->extractFirstname($segment1, $fields);
            $segment1 = $this->extractMiddlename($segment1, $fields);
        }
        $this->absorbLeftovers($segment1, $fields);

        // Segment 3 onward (after a second comma, if present): suffix only per segment, with
        // any non-suffix leftover absorbed rather than discarded (e.g. "Smith, John, Michael"
        // must not lose "Michael" just because it isn't a recognized suffix). Looping over
        // every remaining segment - not just $segments[2] - means a name with more than 3
        // comma-separated parts (e.g. "Smith, John, PhD, Esq") doesn't silently drop
        // anything past the third.
        foreach (array_slice($segments, 2) as $segment) {
            if ($segment === '') {
                continue;
            }
            $extraSegment = $this->tokenize($segment);
            $extraSegment = $this->extractSuffix($extraSegment, $nameValues, $fields, 0, true);
            $this->absorbLeftovers($extraSegment, $fields);
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
     * @param  array $fields
     * @return void
     */
    protected function absorbLeftovers(array $tokens, array &$fields): void
    {
        if (empty($tokens)) {
            return;
        }

        $text = $this->normalizeWords($tokens);

        if ($fields['firstname'] === null) {
            $fields['firstname'] = $text;
        } else {
            $fields['middlename'] = trim(($fields['middlename'] ?? '') . ' ' . $text);
        }
    }

    /**
     * If no raw token ever became firstname but one or more initials were set aside, promote
     * the FIRST claimed initial back to firstname - handles "J. B. Hunt" (firstname="J.",
     * initials="B.", lastname="Hunt"), since a name consisting only of initials plus a
     * lastname still needs a firstname.
     *
     * @param  array $fields
     * @param  array $initialsQueue
     * @return void
     */
    protected function finalizeInitials(array &$fields, array &$initialsQueue): void
    {
        if (empty($initialsQueue)) {
            return;
        }

        if ($fields['firstname'] === null) {
            $promoted            = array_shift($initialsQueue);
            $fields['firstname'] = $this->normalizeCase($promoted);
        }

        if (!empty($initialsQueue)) {
            $fields['initials'] = trim(($fields['initials'] ?? '') . ' ' . $this->normalizeWords($initialsQueue));
        }

        $initialsQueue = [];
    }

    /**
     * Extract nickname
     *
     * Scans for a token starting with an opening delimiter and collects tokens until one
     * ends with the matching closing delimiter (supports multi-word nicknames).
     *
     * @param  array      $tokens
     * @param  NameValues $nameValues
     * @param  array      $fields
     * @return array
     */
    protected function extractNickname(array $tokens, NameValues $nameValues, array &$fields): array
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

            $fields['nickname'] = $this->normalizeWords($span);
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
     * @param  array      $fields
     * @return array
     */
    protected function extractSalutation(array $tokens, NameValues $nameValues, array &$fields): array
    {
        $salutations = [];
        foreach ($nameValues->getSalutations() as $key => $display) {
            $keyWords      = explode(' ', $key);
            $salutations[] = ['keyWords' => $keyWords, 'length' => count($keyWords), 'display' => $display];
        }

        $claimed = [];
        $index   = 0;

        while (true) {
            $max = !empty($tokens) ? max(1, (int) floor(count($tokens) / 2)) : 0;
            if (($index >= $max) || ($index >= count($tokens))) {
                break;
            }

            $matchedLength = null;
            $matchedValue  = null;

            foreach ($salutations as ['keyWords' => $keyWords, 'length' => $length, 'display' => $display]) {
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
            $fields['salutation'] = trim(($fields['salutation'] ?? '') . ' ' . implode(' ', $claimed));
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
     * @param  array      $fields
     * @param  int        $reservedParts
     * @param  bool       $matchSinglePart
     * @param  bool       $reserveLastToken
     * @return array
     */
    protected function extractSuffix(
        array $tokens,
        NameValues $nameValues,
        array &$fields,
        int $reservedParts = 2,
        bool $matchSinglePart = false,
        bool $reserveLastToken = false
    ): array
    {
        $suffixes = $nameValues->getSuffixes();

        if ($matchSinglePart && (count($tokens) === 1)) {
            $key = strtolower(str_replace('.', '', $tokens[0]));
            if (isset($suffixes[$key])) {
                $fields['suffix'] = trim(($fields['suffix'] ?? '') . ' ' . $suffixes[$key]);
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
            $fields['suffix'] = trim(($fields['suffix'] ?? '') . ' ' . implode(' ', $claimed));
        }

        return array_values($tokens);
    }

    /**
     * Extract initials
     *
     * A remaining single letter (optionally with a trailing period) is an initial. An
     * all-caps 2-letter run (e.g. "JR") is split into two separate initials first - unless
     * that run is also a recognized lastname prefix ("DE", "LA", "ST", ...), in which case
     * it's left alone so extractLastname() can fold it as a prefix; without this guard,
     * all-caps input like "JAMES DE LUCA" would have "DE" shredded into two fake initials
     * before extractLastname() ever saw it. The very last remaining token is never treated
     * as an initial unless $matchLastPart is true.
     *
     * @param  array      $tokens
     * @param  bool       $matchLastPart
     * @param  NameValues $nameValues
     * @param  array      $initialsQueue
     * @return array
     */
    protected function extractInitials(array $tokens, bool $matchLastPart, NameValues $nameValues, array &$initialsQueue): array
    {
        $prefixes = $nameValues->getLastnamePrefixes();

        $last = count($tokens) - 1;
        for ($i = 0; $i < count($tokens); $i++) {
            if (!$matchLastPart && ($i === $last)) {
                continue;
            }
            $stripped = str_replace('.', '', $tokens[$i]);
            if ((strlen($stripped) === 2) && ($stripped === strtoupper($stripped)) && ctype_alpha($stripped)
                && !isset($prefixes[strtolower($stripped)])) {
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
            $initialsQueue[] = $tokens[$i];
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
     * @param  array      $fields
     * @param  bool       $singlePartOk
     * @param  ?int       $originalCount
     * @return array
     */
    protected function extractLastname(
        array $tokens,
        NameValues $nameValues,
        array &$fields,
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

        $fields['lastname'] = $this->normalizeWords($lastnameWords);
        if (!empty($prefixWords)) {
            $fields['lastnamePrefix'] = implode(' ', $prefixWords);
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
     * @param  array $fields
     * @return array
     */
    protected function extractFirstname(array $tokens, array &$fields): array
    {
        if (empty($tokens)) {
            return $tokens;
        }

        $fields['firstname'] = $this->normalizeCase($tokens[0]);
        array_splice($tokens, 0, 1);

        return array_values($tokens);
    }

    /**
     * Extract middlename
     *
     * Whatever raw tokens remain after firstname extraction join as middlename.
     *
     * @param  array $tokens
     * @param  array $fields
     * @return array
     */
    protected function extractMiddlename(array $tokens, array &$fields): array
    {
        if (empty($tokens)) {
            return $tokens;
        }

        $fields['middlename'] = trim(($fields['middlename'] ?? '') . ' ' . $this->normalizeWords($tokens));

        return [];
    }

}
