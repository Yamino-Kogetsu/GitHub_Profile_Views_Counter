<?php

declare(strict_types=1);

namespace Odoru\GitHubProfileViewsCounter;

final class TrafficService
{
    public function __construct(private readonly GitHubTrafficClient $client)
    {
    }

    public function syncTraffic(string $repository, string $countType, ?string $token): Count
    {
        [$owner, $name] = $this->splitRepository($repository);
        $data = $this->client->fetchRepoViews($owner . '/' . $name, $token);

        $count = strtolower($countType) === 'uniques'
            ? ($data['uniques'] ?? 0)
            : ($data['count'] ?? 0);

        return Count::ofInt((int) $count);
    }

    private function splitRepository(string $repository): array
    {
        $parts = explode('/', trim($repository), 2);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new \InvalidArgumentException('GITHUB_REPOSITORY must be in the format owner/repository');
        }

        return $parts;
    }
}
