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
namespace Pop\Parser\Address;

use Pop\Parser\AbstractResult;

/**
 * Address result class
 *
 * @category   Pop
 * @package    Pop\Parser
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class AddressResult extends AbstractResult
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
     * Constructor
     *
     * @param  array $address
     */
    public function __construct(array $address)
    {
        $this->streetNumber      = $address['streetNumber'] ?? null;
        $this->streetName        = $address['streetName'] ?? null;
        $this->routeType         = $address['routeType'] ?? null;
        $this->direction         = $address['direction'] ?? null;
        $this->directionPosition = $address['directionPosition'] ?? null;
        $this->unit              = $address['unit'] ?? null;
        $this->isPoBox           = $address['isPoBox'] ?? null;
        $this->city              = $address['city'] ?? null;
        $this->postalCode        = $address['postalCode'] ?? null;
        $this->zip4              = $address['zip4'] ?? null;
        $this->stateName         = $address['stateName'] ?? null;
        $this->stateCode         = $address['stateCode'] ?? null;
        $this->country           = $address['country'] ?? null;
        $this->confidence        = $address['confidence'] ?? 1.0;
    }

    /**
     * Method to get street name
     *
     * @param  bool $withRouteDirection
     * @return ?string
     */
    public function getStreetName(bool $withRouteDirection = true): ?string
    {
        return $withRouteDirection ? $this->applyDirection($this->streetName) : $this->streetName;
    }

    /**
     * Prepend/append the direction to a street name, per its recorded position
     *
     * @param  ?string $streetName
     * @return ?string
     */
    private function applyDirection(?string $streetName): ?string
    {
        if (empty($this->direction)) {
            return $streetName;
        }

        return ($this->directionPosition == 1)
            ? $streetName . ' ' . $this->direction
            : $this->direction . ' ' . $streetName;
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
            $streetName = $this->applyDirection($this->streetName);

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
            'confidence'   => $this->confidence,
        ];
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
