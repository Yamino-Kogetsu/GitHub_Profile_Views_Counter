<?php

declare(strict_types=1);

namespace Odoru\GitHubProfileViewsCounter;

use PUGX\Poser\Badge;
use PUGX\Poser\Calculator\SvgTextSizeCalculator;
use PUGX\Poser\Poser;
use PUGX\Poser\Render\SvgFlatRender;
use PUGX\Poser\Render\SvgFlatSquareRender;
use PUGX\Poser\Render\SvgForTheBadgeRenderer;
use PUGX\Poser\Render\SvgPlasticRender;

final class BadgeImageRendererService
{
    private const ABBREVIATIONS = ['', 'K', 'M', 'B', 'T', 'Qa', 'Qi'];

    private Poser $poser;

    public function __construct()
    {
        $calculator = new SvgTextSizeCalculator();

        $this->poser = new Poser([
            new SvgPlasticRender(textSizeCalculator: $calculator),
            new SvgFlatRender(textSizeCalculator: $calculator),
            new SvgFlatSquareRender(textSizeCalculator: $calculator),
            new SvgForTheBadgeRenderer(textSizeCalculator: $calculator),
        ]);
    }

    public function renderBadgeWithCount(
        string $label,
        Count $count,
        string $messageBackgroundFill,
        string $badgeStyle,
        bool $isCountAbbreviated,
    ): string {
        $message = $this->formatNumber($count->toInt(), $isCountAbbreviated);

        return $this->renderBadge($label, $message, $messageBackgroundFill, $badgeStyle);
    }

    public function renderBadgeWithError(string $label, string $message, string $badgeStyle): string
    {
        return $this->renderBadge($label, $message, 'red', $badgeStyle);
    }

    private function renderBadge(string $label, string $message, string $fill, string $style): string
    {
        return (string) $this->poser->generate(
            $label,
            $message,
            $fill,
            $style,
            Badge::DEFAULT_FORMAT,
        );
    }

    private function formatNumber(int $number, bool $abbreviated): string
    {
        if ($abbreviated) {
            $index = 0;
            $value = (float) $number;
            while ($value >= 1000 && $index < count(self::ABBREVIATIONS) - 1) {
                $value /= 1000;
                $index++;
            }

            $text = rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
            return $text . self::ABBREVIATIONS[$index];
        }

        return number_format($number, 0, '.', ',');
    }
}
