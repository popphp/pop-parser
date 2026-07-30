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
namespace Pop\Parser\Address;

use Pop\Parser\AbstractParser;
use Pop\Parser\Exception;

/**
 * Address parser class
 *
 * @category   Pop
 * @package    Pop\Parser
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
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
            if (!in_array($i, $geoResults['linesProcessed'])) {
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
     * segment (or the entire preceding segment), and the country is only recognized as a
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

        $usStates = $addressValues->getStates('US');
        $caStates = $addressValues->getStates('CA');

        $usZipRegex    = '/^\d{5}(-\d{4})?$/';
        $usZip9Regex   = '/^\d{9}$/';
        $caPostalRegex = '/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/i';

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
                $candidate      = $tokens[$stateLine][$stateIndex];
                $candidateUpper = strtoupper($candidate);

                if (($country === 'US') && (strlen($candidate) === 2) && isset($usStates[$candidateUpper])) {
                    $state     = $candidateUpper;
                    $stateCode = $candidateUpper;
                    $stateName = $usStates[$candidateUpper];
                } else if (($country === 'CA') && (strlen($candidate) === 2) && isset($caStates[$candidateUpper])) {
                    $state     = $candidateUpper;
                    $stateCode = $candidateUpper;
                    $stateName = $caStates[$candidateUpper];
                } else if (($fullMatch = array_search($candidate, $usStates)) !== false) {
                    $state     = $candidate;
                    $stateCode = $fullMatch;
                    $stateName = $candidate;
                } else if (($fullMatch = array_search($candidate, $caStates)) !== false) {
                    $state     = $candidate;
                    $stateCode = $fullMatch;
                    $stateName = $candidate;
                }

                if ($state !== null) {
                    if (!in_array($stateLine, $linesProcessed)) {
                        $linesProcessed[] = $stateLine;
                    }

                    // City: remaining tokens before the state, in the same line
                    $before = array_slice($tokens[$stateLine], 0, $stateIndex);
                    if (!empty($before)) {
                        $city = implode(' ', $before);
                    } else {
                        // Fall back to the entirety of the nearest preceding unconsumed line
                        foreach ($lineKeys as $i) {
                            if (($i < $stateLine) && !in_array($i, $linesProcessed)) {
                                $city             = implode(' ', $tokens[$i]);
                                $linesProcessed[] = $i;
                                break;
                            }
                        }
                    }
                }
            }
        }

        // Country is only recognized as a distinct, unconsumed segment (never the state slot)
        $countryLineValues = [
            'US' => ['US', 'USA', 'U S A', 'UNITED STATES'],
            'CA' => ['CA', 'CAN', 'CANADA'],
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

        $isUnitToken = function(string $word) use ($unitTypes): bool {
            return in_array(strtoupper(rtrim($word, '.')), $unitTypes, true) || str_starts_with($word, '#');
        };

        // A trailing (non-primary) line is only pulled in as a unit. Anything else trailing
        // is left alone rather than merged into the street name.
        foreach (array_slice($lines, 1) as $line) {
            foreach ($line as $word) {
                if ($isUnitToken($word)) {
                    $unit = implode(' ', $line);
                    break 2;
                }
            }
        }

        $tokens = $lines[0];

        // PO Box, e.g. "PO Box 1234", "P.O. Box 1234", "POB 1234", "Box 1234"
        $poBoxRegex = '/^(P\.?O\.?\s*Box|POB|Box)$/i';
        if ((count($tokens) >= 2) && (preg_match($poBoxRegex, str_replace(' ', '', $tokens[0])) === 1)
            && (preg_match('/^\d+[A-Za-z]?$/', $tokens[1]) === 1)) {
            return [
                'streetNumber'      => null,
                'streetName'        => 'PO Box ' . $tokens[1],
                'routeType'         => null,
                'direction'         => null,
                'directionPosition' => null,
                'unit'              => null,
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
                'unit'              => null,
                'isPoBox'           => true,
            ];
        }

        // Unit designator within the primary line: whole-token match
        if ($unit === null) {
            foreach ($tokens as $i => $word) {
                $normalized = strtoupper(rtrim($word, '.'));
                if (in_array($normalized, $unitTypes, true) && isset($tokens[$i + 1])) {
                    $unit = $tokens[$i] . ' ' . $tokens[$i + 1];
                    array_splice($tokens, $i, 2);
                    break;
                }
                if (str_starts_with($word, '#')) {
                    $unit = $word;
                    array_splice($tokens, $i, 1);
                    break;
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
        $routeTypes = array_merge(
            array_map('strtolower', $addressValues->getRouteTypes(true)),
            $addressValues->getCommonRouteTypes()
        );
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
