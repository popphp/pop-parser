<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Parser\Address;

use Pop\Parser\AbstractParser;
use Pop\Parser\Exception;

/**
 * Address parser class
 *
 * @category   Pop
 * @package    Pop\Parser
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class AddressParser extends AbstractParser
{

    /**
     * Street number
     * @var ?string
     * */
    protected ?string $streetNumber = null;

    /**
     * Street name
     * @var ?string
     * */
    protected ?string $streetName = null;

    /**
     * Route type
     * @var ?string
     * */
    protected ?string $routeType = null;

    /**
     * Direction
     * @var ?string
     * */
    protected ?string $direction = null;

    /**
     * Direction position
     * @var ?int
     * */
    protected ?int $directionPosition = null;

    /**
     * Unit
     * @var ?string
     * */
    protected ?string $unit = null;

    /**
     * City
     * @var ?string
     * */
    protected ?string $city = null;

    /**
     * Postal code
     * @var ?string
     * */
    protected ?string $postalCode = null;

    /**
     * Zip 4
     * @var ?string
     * */
    protected ?string $zip4 = null;

    /**
     * State name
     * @var ?string
     * */
    protected ?string $stateName = null;

    /**
     * State code
     * @var ?string
     * */
    protected ?string $stateCode = null;

    /**
     * Country
     * @var ?string
     * */
    protected ?string $country = null;

    /**
     * PO Box flag
     * @var bool
     * */
    protected bool $isPoBox = false;

    /**
     * Method to get street number
     *
     * @return ?string
     */
    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    /**
     * Method to get street name
     *
     * @param  bool $withRouteDirection
     * @return ?string
     */
    public function getStreetName(bool $withRouteDirection = true): ?string
    {
        $streetName = $this->streetName;
        if (!empty($this->direction) && ($withRouteDirection)) {
            if ($this->directionPosition == 1) {
                $streetName = $streetName . ' ' . $this->direction;
            } else {
                $streetName = $this->direction . ' ' . $streetName;
            }
        }

        return $streetName;
    }

    /**
     * Method to get route type
     *
     * @return ?string
     */
    public function getRouteType(): ?string
    {
        return $this->routeType;
    }

    /**
     * Method to get direction
     *
     * @return ?string
     */
    public function getDirection(): ?string
    {
        return $this->direction;
    }

    /**
     * Method to get unit
     *
     * @return ?string
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * Method to get city
     *
     * @return ?string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Method to get postal code
     *
     * @return ?string
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * Method to get zip 4
     *
     * @return ?string
     */
    public function getZip4(): ?string
    {
        return $this->zip4;
    }

    /**
     * Method to get state name
     *
     * @return ?string
     */
    public function getStateName(): ?string
    {
        return $this->stateName;
    }

    /**
     * Method to get state code
     *
     * @return ?string
     */
    public function getStateCode(): ?string
    {
        return $this->stateCode;
    }

    /**
     * Method to get country
     *
     * @return ?string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Has street number
     *
     * @return bool
     */
    public function hasStreetNumber(): bool
    {
        return !empty($this->streetNumber);
    }

    /**
     * Has street name
     *
     * @return bool
     */
    public function hasStreetName(): bool
    {
        return !empty($this->streetName);
    }

    /**
     * Has route type
     *
     * @return bool
     */
    public function hasRouteType(): bool
    {
        return !empty($this->routeType);
    }

    /**
     * Has direction
     *
     * @return bool
     */
    public function hasDirection(): bool
    {
        return !empty($this->direction);
    }

    /**
     * Has unit
     *
     * @return bool
     */
    public function hasUnit(): bool
    {
        return !empty($this->unit);
    }

    /**
     * Has city
     *
     * @return bool
     */
    public function hasCity(): bool
    {
        return !empty($this->city);
    }

    /**
     * Has postal code
     *
     * @return bool
     */
    public function hasPostalCode(): bool
    {
        return !empty($this->postalCode);
    }

    /**
     * Has zip 4
     *
     * @return bool
     */
    public function hasZip4(): bool
    {
        return !empty($this->zip4);
    }

    /**
     * Has state name
     *
     * @return bool
     */
    public function hasStateName(): bool
    {
        return !empty($this->stateName);
    }

    /**
     * Has state code
     *
     * @return bool
     */
    public function hasStateCode(): bool
    {
        return !empty($this->stateCode);
    }

    /**
     * Has country
     *
     * @return bool
     */
    public function hasCountry(): bool
    {
        return !empty($this->country);
    }

    /**
     * Is PO Box
     *
     * @return bool
     */
    public function isPoBox(): bool
    {
        return $this->isPoBox;
    }

    /**
     * Method to get full address
     *
     * @param  string $delimiter
     * @param  bool   $useStateCode
     * @param  bool   $includeCountry
     * @return string
     */
    public function getFullAddress(string $delimiter = ', ', bool $useStateCode = true, bool $includeCountry = false): string
    {
        $fullAddress  = [];
        $addressLine1 = null;

        if (!empty($this->streetNumber)) {
            $addressLine1 = $this->streetNumber;
        }

        if (!empty($this->streetName)) {
            $streetName = $this->streetName;

            if (!empty($this->direction)) {
                if ($this->directionPosition == 1) {
                    $streetName = $streetName . ' ' . $this->direction;
                } else {
                    $streetName = $this->direction . ' ' . $streetName;
                }
            }

            if (!empty($this->routeType)) {
                $streetName .= ' ' . $this->routeType;
            }

            if (!empty($addressLine1)) {
                $addressLine1 .= ' ' . $streetName;
            } else {
                $addressLine1 = $streetName;
            }
        }

        if (!empty($addressLine1)) {
            $fullAddress[] = $addressLine1;
        }

        if (!empty($this->unit)) {
            $fullAddress[] = $this->unit;
        }

        if (!empty($this->city) && (!empty($this->stateCode) || !empty($this->stateName))) {
            $cityState = $this->city;
            if (($useStateCode) && !empty($this->stateCode)) {
                $cityState .= ', ' . $this->stateCode;
            } else if (!empty($this->stateName)) {
                $cityState .= ', ' . $this->stateName;
            }

            if (!empty($this->postalCode)) {
                $cityState .= ' ' . $this->postalCode;
                if (!empty($this->zip4)) {
                    $cityState .= '-' . $this->zip4;
                }
            }

            $fullAddress[] = $cityState;
        }

        if (($includeCountry) && !empty($this->country)) {
            $fullAddress[] = $this->country;
        }

        return implode($delimiter, $fullAddress);
    }

    /**
     * Parse street address
     *
     * @param  string $streetAddress
     * @return array
     */
    public function parseStreetAddress(string $streetAddress): array
    {
        $addressValues    = new AddressValues();
        $lines            = $this->clean($streetAddress);
        $tokens           = $this->tokenize($lines);
        $locationResults  = $this->extractLocation($tokens, $addressValues);

        $this->streetNumber      = $locationResults['streetNumber'];
        $this->streetName        = $locationResults['streetName'];
        $this->routeType         = $locationResults['routeType'];
        $this->direction         = $locationResults['direction'];
        $this->directionPosition = $locationResults['directionPosition'];
        $this->unit               = $locationResults['unit'];
        $this->isPoBox            = $locationResults['isPoBox'];

        return [
            'streetNumber' => $this->streetNumber,
            'streetName'   => $this->streetName,
            'routeType'    => $this->routeType,
            'direction'    => $this->direction,
            'unit'         => $this->unit,
        ];
    }

    /**
     * Parse method
     *
     * @param  ?string $address
     * @throws Exception
     * @return static
     */
    public function parse(?string $address = null): static
    {
        if (empty($this->data) && empty($address)) {
            throw new Exception('Error: You must pass an address string to the parser object.');
        }

        if ((null === $address) && !empty($this->data)) {
            $address = $this->data;
        } else if ((null !== $address) && empty($this->data)) {
            $this->data = $address;
        }

        $addressValues = new AddressValues();
        $lines         = $this->clean($address);
        $tokens        = $this->tokenize($lines);
        $geoResults    = $this->extractGeo($tokens, $addressValues);

        $remainingLines = [];
        foreach ($tokens as $i => $lineTokens) {
            if (isset($geoResults['trimmedLines'][$i])) {
                $remainingLines[] = $geoResults['trimmedLines'][$i];
            } else if (!in_array($i, $geoResults['linesProcessed'])) {
                $remainingLines[] = $lineTokens;
            }
        }

        $locationResults = $this->extractLocation($remainingLines, $addressValues);

        $this->streetNumber      = $locationResults['streetNumber'];
        $this->streetName        = $locationResults['streetName'];
        $this->routeType         = $locationResults['routeType'];
        $this->direction         = $locationResults['direction'];
        $this->directionPosition = $locationResults['directionPosition'];
        $this->unit               = $locationResults['unit'];
        $this->isPoBox            = $locationResults['isPoBox'];
        $this->city               = $geoResults['city'];
        $this->postalCode         = $geoResults['postalCode'];
        $this->zip4               = $geoResults['zip4'];
        $this->stateName          = $geoResults['stateName'];
        $this->stateCode          = $geoResults['stateCode'];
        $this->country            = $geoResults['country'];

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
            'streetNumber' => $this->streetNumber,
            'streetName'   => $this->streetName,
            'routeType'    => $this->routeType,
            'direction'    => $this->direction,
            'unit'         => $this->unit,
            'city'         => $this->city,
            'postalCode'   => $this->postalCode,
            'zip4'         => $this->zip4,
            'stateName'    => $this->stateName,
            'stateCode'    => $this->stateCode,
            'country'      => $this->country,
        ];
    }

    /**
     * Clean method
     *
     * @param  string $address
     * @return array
     */
    public function clean(string $address): array
    {
        // Multiple spaces
        $address = preg_replace('/\s+/', ' ', $address);

        // Bad dash format
        $address = str_replace([' -', '- '], '-', $address);

        // Split into array by comma, semi-colon, newline, and/or tab delimiters
        return array_filter(array_map('trim', preg_split('/,|\t|\n|\r|;/', $address)));
    }

    /**
     * Tokenize method
     *
     * Splits each cleaned line into an array of whitespace-delimited word tokens, keyed by
     * the original line index.
     *
     * @param  array $lines
     * @return array
     */
    protected function tokenize(array $lines): array
    {
        $tokens = [];

        foreach ($lines as $i => $line) {
            $tokens[$i] = preg_split('/\s+/', trim($line));
        }

        return $tokens;
    }

    /**
     * Extract geo (country, postal code, state and city)
     *
     * Works right-to-left over the tokenized lines, using token *position* rather than
     * substring matching: the postal code anchors everything else, the state is the token
     * immediately before it, the city is whatever precedes the state within that same
     * segment (or the entirely preceding segment), and the country is only recognized as a
     * distinct segment outside the state slot. This ordering is what keeps a state code
     * like "CA" from ever being mistaken for the country code "CA".
     *
     * @param  array         $tokens
     * @param  AddressValues $addressValues
     * @return array
     */
    protected function extractGeo(array $tokens, AddressValues $addressValues): array
    {
        $city       = null;
        $state      = null;
        $stateName  = null;
        $stateCode  = null;
        $postalCode = null;
        $zip4       = null;
        $country    = null;

        $linesProcessed = [];
        $trimmedLines   = [];

        $usStates = $addressValues->getStates('US');
        $caStates = $addressValues->getStates('CA');

        $usZipRegex    = '/^\d{5}(-\d{4})?$/';
        $usZip9Regex   = '/^\d{9}$/';
        $caPostalRegex = '/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/i';
        $poBoxRegex    = '/^(P\.?O\.?\s*Box|POB|Box)$/i';

        $looksLikePoBoxLine = function(array $line) use ($poBoxRegex): bool {
            return (preg_match($poBoxRegex, str_replace(' ', '', $line[0])) === 1)
                || ((count($line) >= 2) && (strcasecmp(str_replace('.', '', $line[0]), 'PO') === 0) && (strcasecmp($line[1], 'Box') === 0));
        };

        $lineKeys = array_keys($tokens);
        rsort($lineKeys);

        $postalLine  = null;
        $postalIndex = null;

        // Find the postal code, scanning lines and tokens from the end backward
        foreach ($lineKeys as $i) {
            $lineTokens = $tokens[$i];
            $count      = count($lineTokens);

            for ($t = $count - 1; $t >= 0; $t--) {
                $word = $lineTokens[$t];

                if ((preg_match($usZipRegex, $word) === 1) || (preg_match($usZip9Regex, $word) === 1)) {
                    $postalCode  = $word;
                    $postalLine  = $i;
                    $postalIndex = $t;
                    $country     = 'US';
                    break 2;
                }

                if (preg_match($caPostalRegex, $word) === 1) {
                    $postalCode  = $word;
                    $postalLine  = $i;
                    $postalIndex = $t;
                    $country     = 'CA';
                    break 2;
                }

                // Canadian postal codes are sometimes written with an internal space, e.g. "M4B 1B3"
                if ($t > 0) {
                    $joined = $lineTokens[$t - 1] . ' ' . $word;
                    if (preg_match($caPostalRegex, $joined) === 1) {
                        $postalCode  = str_replace(' ', '', $joined);
                        $postalLine  = $i;
                        $postalIndex = $t - 1;
                        $country     = 'CA';
                        break 2;
                    }
                }
            }
        }

        if ($postalCode !== null) {
            $linesProcessed[] = $postalLine;

            if ($country === 'US') {
                if (strpos($postalCode, '-') !== false) {
                    [$postalCode, $zip4] = explode('-', $postalCode);
                } else if (strlen($postalCode) === 9) {
                    $zip4       = substr($postalCode, -4);
                    $postalCode = substr($postalCode, 0, 5);
                }
            }

            // The state slot is the token immediately before the postal code, on the same
            // line. If the postal code is the first token of its line, fall back to the
            // last token of the nearest preceding unconsumed line.
            $stateLine  = null;
            $stateIndex = null;

            if ($postalIndex > 0) {
                $stateLine  = $postalLine;
                $stateIndex = $postalIndex - 1;
            } else {
                foreach ($lineKeys as $i) {
                    if (($i < $postalLine) && !in_array($i, $linesProcessed)) {
                        $stateLine  = $i;
                        $stateIndex = count($tokens[$i]) - 1;
                        break;
                    }
                }
            }

            if ($stateLine !== null) {
                // Try progressively longer token spans ending at $stateIndex (longest first),
                // so multi-word state/province names ("New York", "District of Columbia",
                // "Prince Edward Island") resolve, not just the single trailing token.
                $stateSpanStart = $stateIndex;

                for ($span = min(3, $stateIndex + 1); $span >= 1; $span--) {
                    $spanStart      = $stateIndex - $span + 1;
                    $candidate      = implode(' ', array_slice($tokens[$stateLine], $spanStart, $span));
                    $candidateUpper = strtoupper($candidate);

                    if (($country === 'US') && ($span === 1) && (strlen($candidate) === 2) && isset($usStates[$candidateUpper])) {
                        $state          = $candidateUpper;
                        $stateCode      = $candidateUpper;
                        $stateName      = $usStates[$candidateUpper];
                        $stateSpanStart = $spanStart;
                        break;
                    } else if (($country === 'CA') && ($span === 1) && (strlen($candidate) === 2) && isset($caStates[$candidateUpper])) {
                        $state          = $candidateUpper;
                        $stateCode      = $candidateUpper;
                        $stateName      = $caStates[$candidateUpper];
                        $stateSpanStart = $spanStart;
                        break;
                    } else if (($fullMatch = array_search($candidate, $usStates)) !== false) {
                        $state          = $candidate;
                        $stateCode      = $fullMatch;
                        $stateName      = $candidate;
                        $stateSpanStart = $spanStart;
                        break;
                    } else if (($fullMatch = array_search($candidate, $caStates)) !== false) {
                        $state          = $candidate;
                        $stateCode      = $fullMatch;
                        $stateName      = $candidate;
                        $stateSpanStart = $spanStart;
                        break;
                    }
                }

                if ($state !== null) {
                    if (!in_array($stateLine, $linesProcessed)) {
                        $linesProcessed[] = $stateLine;
                    }

                    // A comma already separated an earlier segment from this one if any lower
                    // line index exists at all - in that case, whatever precedes the state
                    // in THIS line's own tokens (or the nearest preceding line, if this line's
                    // "before" is empty) is unambiguously city, because the street already got
                    // its own segment earlier. Only when there's no preceding segment - a truly
                    // comma-less single-line address - can this line's leading tokens be a
                    // street/city hybrid that needs the route-type-boundary split below.
                    $hasPrecedingLine = false;
                    foreach ($lineKeys as $i) {
                        if ($i < $stateLine) {
                            $hasPrecedingLine = true;
                            break;
                        }
                    }

                    $routeTypes = array_merge(
                        array_map('strtolower', $addressValues->getRouteTypes(true)),
                        $addressValues->getCommonRouteTypes()
                    );

                    // Prefer the RIGHTMOST route-type match that still leaves at least
                    // one token after it (for a city). Neither "first" nor "last" alone
                    // works: taking the last match breaks when a city name itself ends in
                    // a route-type word ("Beverly Hills" - "Hills" is a valid suffix), and
                    // taking the first match breaks when the street name itself starts
                    // with a route-type word ("Park Ave ...", "Circle Dr ..."). A match
                    // with nothing after it is far more likely to be the tail of the city
                    // name than the street's actual route suffix, since a route suffix is
                    // normally followed by a city.
                    $findRouteBoundary = function(array $span) use ($routeTypes, $looksLikePoBoxLine): ?int {
                        // A span with no digit/PO-Box evidence at its head can only plausibly be
                        // a place name (e.g. "Lake Forest"), not a street/city hybrid - don't let
                        // a route-type word that's also a legitimate city-name word ("Lake",
                        // "Park", "Hills", ...) be mistaken for a street's route-type suffix here.
                        if ((preg_match('/^\d/', $span[0]) !== 1) && !$looksLikePoBoxLine($span)) {
                            return null;
                        }

                        $routeEndIndex   = null;
                        $lastSpanIndex   = count($span) - 1;
                        foreach ($span as $idx => $word) {
                            $routeCandidate = strtolower(rtrim($word, '.'));
                            if (in_array($routeCandidate, $routeTypes, true) && ($idx < $lastSpanIndex)) {
                                $routeEndIndex = $idx;
                            }
                        }
                        return $routeEndIndex;
                    };

                    // City: remaining tokens before the state, in the same line
                    $before = array_slice($tokens[$stateLine], 0, $stateSpanStart);
                    if (!empty($before)) {
                        if ($hasPrecedingLine) {
                            // A comma already separates this from the street - it's just city.
                            $city = implode(' ', $before);
                        } else {
                            // This line carries city AND (with no comma to separate them)
                            // possibly the street portion too. Find where the street portion
                            // ends (its route-type suffix, if any) so city only takes what's
                            // left over, and hand the leading portion back for street parsing
                            // rather than losing it. If no route-type boundary can be found,
                            // city is left unguessed (null) rather than swallowing words that
                            // might be street, not city.
                            $routeEndIndex = $findRouteBoundary($before);

                            if ($routeEndIndex !== null) {
                                $city = implode(' ', array_slice($before, $routeEndIndex + 1));
                                if ($city === '') {
                                    $city = null;
                                }
                                $trimmedLines[$stateLine] = array_slice($before, 0, $routeEndIndex + 1);
                            } else {
                                $trimmedLines[$stateLine] = $before;
                            }
                        }
                    } else {
                        // Fall back to the nearest preceding unconsumed line. If that line
                        // still looks like it carries the street (a route-type boundary can be
                        // found in it, it starts with a number, or it's a PO Box line), don't
                        // swallow it whole as city - split it the same way, or hand it back
                        // unsplit for street parsing, rather than silently discarding the street.
                        foreach ($lineKeys as $i) {
                            if (($i < $stateLine) && !in_array($i, $linesProcessed)) {
                                $candidateLine = $tokens[$i];
                                $routeEndIndex = $findRouteBoundary($candidateLine);

                                if ($routeEndIndex !== null) {
                                    $city = implode(' ', array_slice($candidateLine, $routeEndIndex + 1));
                                    if ($city === '') {
                                        $city = null;
                                    }
                                    $trimmedLines[$i] = array_slice($candidateLine, 0, $routeEndIndex + 1);
                                } else if ((preg_match('/^\d/', $candidateLine[0]) === 1) || $looksLikePoBoxLine($candidateLine)) {
                                    $trimmedLines[$i] = $candidateLine;
                                } else {
                                    $city = implode(' ', $candidateLine);
                                }

                                $linesProcessed[] = $i;
                                break;
                            }
                        }
                    }
                }
            }
        }

        // Country is only recognized as a distinct, unconsumed segment (never the state slot).
        // Bare two-letter codes ("US"/"CA") are deliberately excluded here - only unambiguous
        // full forms are accepted - because a bare "CA" can only safely be trusted as a state
        // when the state-slot mechanism above resolves it; without a postal code to anchor
        // that slot, a bare "CA" segment must not silently become "Canada" instead.
        $countryLineValues = [
            'US' => ['USA', 'U S A', 'UNITED STATES'],
            'CA' => ['CAN', 'CANADA'],
        ];

        foreach ($lineKeys as $i) {
            if (in_array($i, $linesProcessed)) {
                continue;
            }

            $normalizedLine = strtoupper(str_replace('.', '', implode(' ', $tokens[$i])));

            if (in_array($normalizedLine, $countryLineValues['CA'], true)) {
                $country          = 'CA';
                $linesProcessed[] = $i;
                break;
            }

            if (in_array($normalizedLine, $countryLineValues['US'], true)) {
                $country          = 'US';
                $linesProcessed[] = $i;
                break;
            }
        }

        return [
            'city'           => $city,
            'stateName'      => $stateName,
            'stateCode'      => $stateCode,
            'postalCode'     => $postalCode,
            'zip4'           => $zip4,
            'country'        => $country,
            'linesProcessed' => $linesProcessed,
            'trimmedLines'   => $trimmedLines,
        ];
    }

    /**
     * Extract street/location details (PO Box, unit, direction, route type, street number/name)
     *
     * Operates on whatever lines extractGeo() didn't consume. The primary (first) line is
     * where the street number, name, route type and direction are extracted from; a
     * secondary line is only pulled in if it looks like a unit (e.g. a comma-separated
     * "Apt 3B" segment) so that an unrecognized trailing line (e.g. a city extractGeo()
     * couldn't place) is never merged into the street name. Each step removes the tokens it
     * claims before the next step runs, so nothing can be claimed twice.
     *
     * @param  array         $lines
     * @param  AddressValues $addressValues
     * @return array
     */
    protected function extractLocation(array $lines, AddressValues $addressValues): array
    {
        $lines = array_values($lines);

        $streetNumber      = null;
        $streetName        = null;
        $routeType         = null;
        $unit              = null;
        $direction         = null;
        $directionPosition = null;
        $isPoBox           = false;

        if (empty($lines)) {
            return compact('streetNumber', 'streetName', 'routeType', 'direction', 'directionPosition', 'unit', 'isPoBox');
        }

        $unitTypes = array_map('strtoupper', $addressValues->getUnitTypes());
        $routeTypes = array_merge(
            array_map('strtolower', $addressValues->getRouteTypes(true)),
            $addressValues->getCommonRouteTypes()
        );
        $poBoxRegex = '/^(P\.?O\.?\s*Box|POB|Box)$/i';

        // Pick the primary (street) line: the first remaining line with STRONG evidence of
        // being the street - a leading number AND a trailing route-type word together, or a
        // match for the PO Box pattern. This matters when a non-street line sorts ahead of the
        // real street line (e.g. a recipient name: "John Smith, 123 Main St, ..."); without
        // it, the recipient name would be mistaken for the street name and the real street
        // silently dropped. Requiring BOTH signals (not just one) matters just as much: a line
        // that only weakly matches one signal - e.g. "4th Floor" starts with a digit but isn't
        // a street - must not be promoted over the true street line just because that line
        // (e.g. "Broadway") has no recognizable route-type suffix of its own. Falls back to
        // the first line when nothing qualifies.
        $primaryIndex = 0;
        foreach ($lines as $idx => $line) {
            $lastLineIndex = count($line) - 1;
            $looksLikePoBox = (preg_match($poBoxRegex, str_replace(' ', '', $line[0])) === 1)
                || ((count($line) >= 2) && (strcasecmp(str_replace('.', '', $line[0]), 'PO') === 0) && (strcasecmp($line[1], 'Box') === 0));
            $looksLikeStreet = $looksLikePoBox
                || ((preg_match('/^\d/', $line[0]) === 1) && (in_array(strtolower(rtrim($line[$lastLineIndex], '.')), $routeTypes, true)));
            if ($looksLikeStreet) {
                $primaryIndex = $idx;
                break;
            }
        }

        $secondaryLines = $lines;
        unset($secondaryLines[$primaryIndex]);

        // A trailing (non-primary) line is only pulled in as a unit if it looks like one: a
        // recognized designator word co-occurring with a digit (e.g. "Apt 3B"), or a bare
        // "#..." token. A designator word alone isn't enough - several unit-type words
        // ("Front", "Rear", "Lobby", "Pier", "Side", "Fl", ...) are also ordinary English
        // words that appear in real street names, so requiring a digit too is what keeps an
        // unrecognized line like "FL" (a state, with no zip to anchor it) from being
        // mistaken for a unit. Anything that doesn't qualify is left alone rather than
        // merged into the street name.
        foreach ($secondaryLines as $line) {
            $hasDesignator = false;
            $hasDigit      = false;
            foreach ($line as $word) {
                if (in_array(strtoupper(rtrim($word, '.')), $unitTypes, true)) {
                    $hasDesignator = true;
                }
                if (preg_match('/\d/', $word) === 1) {
                    $hasDigit = true;
                }
            }
            if (($hasDesignator && $hasDigit) || str_starts_with($line[0], '#')) {
                $unit = implode(' ', $line);
                break;
            }
        }

        $tokens = $lines[$primaryIndex];

        // PO Box, e.g. "PO Box 1234", "P.O. Box 1234", "POB 1234", "Box 1234"
        if ((count($tokens) >= 2) && (preg_match($poBoxRegex, str_replace(' ', '', $tokens[0])) === 1)
            && (preg_match('/^\d+[A-Za-z]?$/', $tokens[1]) === 1)) {
            return [
                'streetNumber'      => null,
                'streetName'        => 'PO Box ' . $tokens[1],
                'routeType'         => null,
                'direction'         => null,
                'directionPosition' => null,
                'unit'              => $unit,
                'isPoBox'           => true,
            ];
        }
        // "PO" "Box" "1234" as three separate tokens (e.g. from "P.O. Box 1234")
        if ((count($tokens) >= 3) && (strcasecmp(str_replace('.', '', $tokens[0]), 'PO') === 0)
            && (strcasecmp($tokens[1], 'Box') === 0) && (preg_match('/^\d+[A-Za-z]?$/', $tokens[2]) === 1)) {
            return [
                'streetNumber'      => null,
                'streetName'        => 'PO Box ' . $tokens[2],
                'routeType'         => null,
                'direction'         => null,
                'directionPosition' => null,
                'unit'              => $unit,
                'isPoBox'           => true,
            ];
        }

        // Unit designator within the primary line: anchored to the tail (a bare "#..." last
        // token, or a recognized designator word immediately followed by a value token that
        // contains a digit). Anchoring here - rather than scanning the whole line - is what
        // keeps a street name like "123 Front St" or "500 Pier Rd" from having its second
        // word mistaken for a unit designator; "Front"/"Pier" are unit-type words too, but
        // "St"/"Rd" right after them don't look like a unit value.
        if ($unit === null) {
            $lastIndex = count($tokens) - 1;
            if (($lastIndex >= 0) && str_starts_with($tokens[$lastIndex], '#')) {
                $unit = $tokens[$lastIndex];
                array_splice($tokens, $lastIndex, 1);
            } else if ($lastIndex >= 1) {
                $designatorCandidate = strtoupper(rtrim($tokens[$lastIndex - 1], '.'));
                if (in_array($designatorCandidate, $unitTypes, true) && (preg_match('/\d/', $tokens[$lastIndex]) === 1)) {
                    $unit = $tokens[$lastIndex - 1] . ' ' . $tokens[$lastIndex];
                    array_splice($tokens, $lastIndex - 1, 2);
                }
            }
        }

        // Direction: recognized only as a prefix (immediately after the street number) or a
        // suffix (the very last remaining token) - never in the middle of the street name.
        $directionSet = [];
        foreach ($addressValues->getDirections() as $value) {
            $directionSet[strtoupper(trim($value))] = true;
        }

        if (count($tokens) > 1) {
            $prefixCandidate = strtoupper(rtrim($tokens[1], '.'));
            if (isset($directionSet[$prefixCandidate])) {
                $direction         = $tokens[1];
                $directionPosition = 0;
                array_splice($tokens, 1, 1);
            }
        }
        if (($direction === null) && (count($tokens) > 1)) {
            $lastIndex       = count($tokens) - 1;
            $suffixCandidate = strtoupper(rtrim($tokens[$lastIndex], '.'));
            if (isset($directionSet[$suffixCandidate])) {
                $direction         = $tokens[$lastIndex];
                $directionPosition = 1;
                array_splice($tokens, $lastIndex, 1);
            }
        }

        // Route type: only recognized as the last remaining token, not merely present
        // anywhere in the street name (this is what fixes e.g. "Park" in "Park Granada"
        // being mistaken for a route-type suffix).
        if (!empty($tokens)) {
            $lastIndex = count($tokens) - 1;
            $candidate = strtolower(rtrim($tokens[$lastIndex], '.'));
            if (in_array($candidate, $routeTypes, true)) {
                $routeType = $tokens[$lastIndex];
                array_splice($tokens, $lastIndex, 1);
            }
        }

        // Street number / name
        if (!empty($tokens)) {
            if (preg_match('/^\d/', $tokens[0]) === 1) {
                $streetNumber = $tokens[0];
                $streetName   = implode(' ', array_slice($tokens, 1));
            } else {
                $streetName = implode(' ', $tokens);
            }
            if ($streetName === '') {
                $streetName = null;
            }
        }

        return compact('streetNumber', 'streetName', 'routeType', 'direction', 'directionPosition', 'unit', 'isPoBox');
    }

    /**
     * To string method
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFullAddress();
    }

}
