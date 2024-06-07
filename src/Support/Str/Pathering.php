<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Support\Str;

use UnexpectedValueException;

/**
 *
 */
class Pathering
{
    /**
     *
     */
    public const Divider = '/';
    /**
     * @var bool
     */
    private bool $keepFirst = true;

    /**
     * @var bool
     */
    private bool $keepEnd = false;

    /**
     * @var bool
     */
    private bool $end = false;

    /**
     * @var array
     */
    private array $parts;

    /**
     * @param ...$paths
     */
    public function __construct(...$paths)
    {
        $this->parts = $paths;
    }

    /**
     * @param ...$paths
     *
     * @return static
     */
    public static function handle(...$paths): static
    {
        return new self(...$paths);
    }

    /**
     * @param ...$paths
     *
     * @return string
     */
    public static function concat(...$paths): string
    {
        return (string)self::handle(...$paths);
    }

    /**
     * Compare segments by segment
     *
     * @param $strip
     * @param $fromPath
     *
     * @return static
     */
    public static function stripPathFromStart($strip, $fromPath): static
    {
        // makes sure both parts are semi valid
        $strip    = self::trim($strip);
        $fromPath = self::trim($fromPath);

        $fromPathParts = explode(Pathering::Divider, $fromPath);
        foreach (explode(Pathering::Divider, $strip) as $idx => $value) {
            // same idx and text matching
            if (($fromPathParts[$idx] ?? null) && $fromPathParts[$idx] === $value) {
                unset($fromPathParts[$idx]);
            }
            else {
                // back the original, different parts
                return self::handle($fromPath);
            }
        }

        return self::handle(...$fromPathParts);
    }

    /**
     * @param ...$paths
     *
     * @return string
     */
    public static function keepSlashes(...$paths): string
    {
        return (string)self::handle(...$paths)
                           ->keep()
        ;
    }

    /**
     * @param ...$paths
     *
     * @return string
     */
    public static function withEnd(...$paths): string
    {
        return (string)self::handle(...$paths)
                           ->end()
        ;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $usable       = [];
        $prependSlash = $this->keepFirst && str_starts_with($this->parts[0], self::Divider) ? self::Divider : '';

        $ee     = end($this->parts);
        $hasEnd = str_ends_with($ee, self::Divider) && $this->keepEnd;

        foreach ($this->parts as $part) {
            if ($part && $part = self::trim($part)) {
                $usable[] = $part;
            }
            else {
                throw new UnexpectedValueException('wrong path notation. Segment between is empty');
            }
        }
        $clearedString = $prependSlash . implode(self::Divider, $usable);

        return $this->end || ($this->keepEnd && $hasEnd) ? $clearedString . self::Divider : $clearedString;
    }


    /**
     * Forces trailing slash
     *
     * @return $this
     */
    public function end(): static
    {
        $this->end = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function keepEnd(): static
    {
        $this->keepEnd = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function keepFirst(): static
    {
        $this->keepFirst = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function keep(): static
    {
        return $this->keepEnd()
                    ->keepFirst()
        ;
    }

    /**
     * @param string $s
     *
     * @return string
     */
    public static function trim(string $s): string
    {
        return trim($s, self::Divider);
    }

    /**
     * @param string $s
     *
     * @return string
     */
    public static function ltrim(string $s): string
    {
        return ltrim($s, self::Divider);
    }

    /**
     * @param string $s
     *
     * @return string
     */
    public static function rtrim(string $s): string
    {
        return rtrim($s, self::Divider);
    }
}
