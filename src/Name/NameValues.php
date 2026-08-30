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

/**
 * Name values class
 *
 * @category   Pop
 * @package    Pop\Parser
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
class NameValues
{

    /**
     * A list of salutations
     * @var array $salutations
     * */
    protected static array $salutations = [
        'mr'         => 'Mr.',
        'mrs'        => 'Mrs.',
        'ms'         => 'Ms.',
        'mx'         => 'Mx.',
        'miss'       => 'Miss',
        'master'     => 'Mr.',
        'mister'     => 'Mr.',
        'dr'         => 'Dr.',
        'prof'       => 'Prof.',
        'rev'        => 'Rev.',
        'fr'         => 'Fr.',
        'sir'        => 'Sir',
        'madam'      => 'Madam',
        'his honour' => 'His Honour',
        'her honour' => 'Her Honour',
    ];

    /**
     * A list of generational name suffixes
     * @var array $suffixes
     * */
    protected static array $suffixes = [
        'jr'     => 'Jr',
        'junior' => 'Junior',
        'sr'     => 'Sr',
        'senior' => 'Senior',
        'i'      => 'I',
        'ii'     => 'II',
        'iii'    => 'III',
        'iv'     => 'IV',
        'v'      => 'V',
        '1st'    => '1st',
        '2nd'    => '2nd',
        '3rd'    => '3rd',
    ];

    /**
     * A list of professional credentials/degrees, kept separate from generational suffixes
     * so e.g. "Anthony Von Fange III, PhD" can expose "III" and "PhD" independently
     * @var array $credentials
     * */
    protected static array $credentials = [
        'phd'  => 'PhD',
        'md'   => 'MD',
        'do'   => 'DO',
        'esq'  => 'Esq',
        'jd'   => 'JD',
        'mba'  => 'MBA',
        'rn'   => 'RN',
        'dds'  => 'DDS',
        'dvm'  => 'DVM',
        'cpa'  => 'CPA',
        'cfa'  => 'CFA',
        'pe'   => 'PE',
        'rph'  => 'RPh',
        'dnp'  => 'DNP',
        'psyd' => 'PsyD',
        'edd'  => 'EdD',
    ];

    /**
     * A list of lastname prefixes
     * @var array $lastnamePrefixes
     * */
    protected static array $lastnamePrefixes = [
        'van'    => 'van',
        'von'    => 'von',
        'de'     => 'de',
        'del'    => 'del',
        'della'  => 'della',
        'der'    => 'der',
        'di'     => 'di',
        'da'     => 'da',
        'du'     => 'du',
        'la'     => 'la',
        'le'     => 'le',
        'st'     => 'St.',
        'ter'    => 'ter',
        'vanden' => 'vanden',
        'vere'   => 'vere',
    ];

    /**
     * A list of nickname-wrapping delimiter pairs (opening => closing)
     * @var array $nicknameDelimiters
     * */
    protected static array $nicknameDelimiters = [
        '(' => ')',
        '[' => ']',
        '{' => '}',
        '<' => '>',
        '"' => '"',
        "'" => "'",
    ];

    /**
     * Getter method for accessing the salutations list
     *
     * @return array
     */
    public function getSalutations(): array
    {
        return self::$salutations;
    }

    /**
     * Getter method for accessing the suffixes list
     *
     * @return array
     */
    public function getSuffixes(): array
    {
        return self::$suffixes;
    }

    /**
     * Getter method for accessing the credentials list
     *
     * @return array
     */
    public function getCredentials(): array
    {
        return self::$credentials;
    }

    /**
     * Getter method for accessing the lastname prefixes list
     *
     * @return array
     */
    public function getLastnamePrefixes(): array
    {
        return self::$lastnamePrefixes;
    }

    /**
     * Getter method for accessing the nickname delimiters list
     *
     * @return array
     */
    public function getNicknameDelimiters(): array
    {
        return self::$nicknameDelimiters;
    }

}
