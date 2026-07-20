<?php

declare(strict_types=1);

namespace Odoru\GitHubProfileViewsCounter;

use Webmozart\Assert\Assert;

final class Count
{
    private int $count;

    public function __construct(int $count)
    {
        Assert::greaterThanEq($count, 0, 'Count cannot be negative');
        $this->count = $count;
    }

    public static function ofString(string $value): self
    {
        Assert::digits($value, 'Count must contain digits only');
        return new self((int) $value);
    }

    public static function ofInt(int $value): self
    {
        return new self($value);
    }

    public function toInt(): int
    {
        return $this->count;
    }

    public function plus(self $that): self
    {
        $sum = $this->count + $that->count;
        if ($sum < 0) {
            throw new \OverflowException('Count overflowed');
        }
        return new self($sum);
    }
}
