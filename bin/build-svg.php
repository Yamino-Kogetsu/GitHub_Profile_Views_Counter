<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Odoru\GitHubProfileViewsCounter\BadgeImageRendererService;
use Odoru\GitHubProfileViewsCounter\Count;
use Odoru\GitHubProfileViewsCounter\GitHubTrafficClient;
use Odoru\GitHubProfileViewsCounter\TrafficService;

$appBasePath = realpath(__DIR__ . '/..');
require $appBasePath . '/vendor/autoload.php';

if (is_file($appBasePath . '/.env')) {
    Dotenv::createImmutable($appBasePath)->safeLoad();
}

$repository = getenv('INPUT_REPOSITORY') ?: getenv('GITHUB_REPOSITORY') ?: '';
$countType = strtolower(getenv('INPUT_COUNT_TYPE') ?: 'count');
$badgeLabel = getenv('INPUT_BADGE_LABEL') ?: 'Profile views';
$badgeColor = getenv('INPUT_BADGE_COLOR') ?: 'blue';
$badgeStyle = getenv('INPUT_BADGE_STYLE') ?: 'flat';
$baseCount = getenv('INPUT_BASE_COUNT') ?: '0';
$abbreviated = filter_var(getenv('INPUT_ABBREVIATED') ?: 'false', FILTER_VALIDATE_BOOL);
$outputPath = getenv('OUTPUT_PATH') ?: ($appBasePath . '/public/views.svg');
$token = getenv('TRAFFIC_TOKEN') ?: (getenv('GITHUB_TOKEN') ?: null);

if (!in_array($badgeStyle, ['flat', 'flat-square', 'plastic', 'for-the-badge'], true)) {
    $badgeStyle = 'flat';
}

$renderer = new BadgeImageRendererService();
$outputDir = dirname($outputPath);
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

try {
    if ($repository === '') {
        throw new RuntimeException('GITHUB_REPOSITORY is required');
    }

    $traffic = (new TrafficService(new GitHubTrafficClient()))->syncTraffic($repository, $countType, $token);

    if ($baseCount !== '0') {
        $traffic = $traffic->plus(Count::ofString($baseCount));
    }

    $svg = $renderer->renderBadgeWithCount(
        $badgeLabel,
        $traffic,
        $badgeColor,
        $badgeStyle,
        $abbreviated,
    );

    file_put_contents($outputPath, $svg);
    echo $outputPath . PHP_EOL;
} catch (Throwable $exception) {
    $svg = $renderer->renderBadgeWithError(
        $badgeLabel,
        $exception->getMessage(),
        $badgeStyle,
    );

    file_put_contents($outputPath, $svg);
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    echo $outputPath . PHP_EOL;
}
