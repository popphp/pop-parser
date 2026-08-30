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
 * Abstract result class
 *
 * @category   Pop
 * @package    Pop\Parser
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    1.0.0
 */
abstract class AbstractResult implements ResultInterface
{

    /**
     * Confidence score (0.0-1.0) reflecting how much of the input was confidently matched
     * against a recognized pattern vs. guessed/absorbed as leftover content. Defaults to 1.0
     * (full confidence) - a concrete result's constructor sets it from whatever confidence
     * value its parser computed, if any.
     * @var float
     */
    protected float $confidence = 1.0;

    /**
     * To array method
     *
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * Get confidence score
     *
     * @return float
     */
    public function getConfidence(): float
    {
        return $this->confidence;
    }

    /**
     * Whether the confidence score meets or exceeds the given threshold
     *
     * @param  float $threshold
     * @return bool
     */
    public function isConfident(float $threshold = 0.7): bool
    {
        return $this->confidence >= $threshold;
    }

}
