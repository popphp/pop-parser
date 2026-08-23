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
namespace Pop\Parser;

/**
 * Parser interface
 *
 * @category   Pop
 * @package    Pop\Parser
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
interface ParserInterface
{

    /**
     * Parse method
     *
     * @return static
     */
    public function parse(): static;

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData(): mixed;

    /**
     * Set data
     *
     * @param  mixed $data
     * @return static
     */
    public function setData(mixed $data): static;

    /**
     * Get parsed result
     *
     * @return mixed
     */
    public function getResult(): mixed;

    /**
     * Check if there is an error
     *
     * @return bool
     */
    public function hasError(): bool;

    /**
     * Get error message
     *
     * @return ?string
     */
    public function getErrorMessage(): ?string;

}
