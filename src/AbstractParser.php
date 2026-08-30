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
 * Abstract parser class
 *
 * @category   Pop
 * @package    Pop\Parser
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
abstract class AbstractParser implements ParserInterface
{

    /**
     * Data to parse
     * @var mixed
     */
    protected mixed $data = null;

    /**
     * Error flag
     * @var bool
     */
    protected bool $error = false;

    /**
     * Error message
     * @var ?string
     */
    protected ?string $errorMessage = null;

    /**
     * Result object
     * @var ?AbstractResult
     */
    protected ?AbstractResult $result = null;

    /**
     * Parse method
     *
     * @return AbstractResult
     */
    abstract public function parse(): AbstractResult;

    /**
     * Constructor
     *
     * Instantiate the parse object
     *
     * @param  mixed $data
     */
    public function __construct(mixed $data = null)
    {
        if (null !== $data) {
            $this->setData($data);
        }
    }

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param  mixed $data
     * @return static
     */
    public function setData(mixed $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get parsed result
     *
     * @return ?AbstractResult
     */
    public function getResult(): ?AbstractResult
    {
        return $this->result;
    }

    /**
     * Check if there is an error
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->error;
    }

    /**
     * Get error message
     *
     * @return ?string
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Turn a count of ambiguity signals collected during parse() into a 0.0-1.0 confidence
     * score: no signals is full confidence; each signal is a fixed, equal-weight deduction,
     * floored so a heavily-ambiguous parse never reads as fully unreliable (0.0). Shared by
     * both concrete parsers rather than duplicated, since the formula itself doesn't depend
     * on what a "signal" means to a particular parser - only counting what qualifies as one
     * is parser-specific.
     *
     * @param  int $signalCount
     * @return float
     */
    protected function calculateConfidence(int $signalCount): float
    {
        if ($signalCount <= 0) {
            return 1.0;
        }

        return max(0.2, 1.0 - (0.25 * $signalCount));
    }

}
