<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Support\Str;

use Illuminate\Support\Str;
use Stringable;
use UnexpectedValueException;

/**
 *
 */
class Namespacering implements Stringable
{
    /**
     *
     */
    public const Divider = '\\';
    /**
     * @var array
     */
    private array $parts;

    /**
     * @var bool
     */
    private bool $end = false;

    /**
     * @param ...$namespaces
     */
    public function __construct(...$namespaces)
    {
        $this->parts = $namespaces;
    }

    /**
     * @param ...$namespaces
     *
     * @return string
     */
    public static function concat(...$namespaces): string
    {
        return (string)self::work(...$namespaces);
    }

    /**
     * @param ...$namespaces
     *
     * @return self
     */
    public static function work(...$namespaces): self
    {
        return new self(...$namespaces);
    }

    /**
     * Clean add last Namespace Separator \\
     *
     * @param ...$namespaces
     *
     * @return string
     */
    public static function withEnd(...$namespaces): string
    {
        return (string)self::work(...$namespaces)
                           ->end()
        ;
    }

    /**
     * Make sure the topNamespace is prepended to relative namespace
     *
     * @param $topNamespace
     * @param $relativeNamespace
     *
     * @return string
     */
    public static function prefixNamespaceIfNeed($topNamespace, $relativeNamespace): string
    {
        return !Str::startsWith($relativeNamespace, $topNamespace) ?
            Namespacering::concat($topNamespace, $relativeNamespace) :
            // already prefixed with topNamespace
            $relativeNamespace;
    }

    /**
     * @return $this
     */
    public function end(): static
    {
        $this->end = true;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $usable = [];
        foreach ($this->parts as $part) {
            if ($part && $part = self::trim($part)) {
                $usable[] = $part;
            }
            else {
                throw new UnexpectedValueException('wrong namespace notation. Segment between is empty');
            }
        }
        $clearedString = implode(self::Divider, $usable);

        return $this->end ? $clearedString . self::Divider : $clearedString;
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
