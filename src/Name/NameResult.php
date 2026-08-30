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

use Pop\Parser\AbstractResult;

/**
 * Name result class
 *
 * @category   Pop
 * @package    Pop\Parser
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class NameResult extends AbstractResult
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
     * Credentials
     * @var ?string
     * */
    protected ?string $credentials = null;

    /**
     * Constructor
     *
     * @param  array $name
     */
    public function __construct(array $name)
    {
        $this->salutation     = $name['salutation'] ?? null;
        $this->firstname      = $name['firstname'] ?? null;
        $this->middlename     = $name['middlename'] ?? null;
        $this->nickname       = $name['nickname'] ?? null;
        $this->initials       = $name['initials'] ?? null;
        $this->lastnamePrefix = $name['lastnamePrefix'] ?? null;
        $this->lastname       = $name['lastname'] ?? null;
        $this->suffix         = $name['suffix'] ?? null;
        $this->credentials    = $name['credentials'] ?? null;
        $this->confidence     = $name['confidence'] ?? 1.0;
    }

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
     * Method to get credentials
     *
     * @return ?string
     */
    public function getCredentials(): ?string
    {
        return $this->credentials;
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
     * Has credentials
     *
     * @return bool
     */
    public function hasCredentials(): bool
    {
        return !empty($this->credentials);
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
            'credentials'    => $this->credentials,
            'confidence'     => $this->confidence,
        ];
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
            $this->credentials,
        ]);

        return implode(' ', $parts);
    }

}
