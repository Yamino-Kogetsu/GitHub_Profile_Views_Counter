<?php

declare(strict_types=1);

namespace Odoru\GitHubProfileViewsCounter;

final class GitHubTrafficClient
{
    public function fetchRepoViews(string $repository, ?string $token): array
    {
        [$owner, $repo] = explode('/', $repository, 2);

        $url = sprintf(
            'https://api.github.com/repos/%s/%s/traffic/views',
            rawurlencode($owner),
            rawurlencode($repo)
        );

        $headers = [
            'Accept: application/vnd.github+json',
            'X-GitHub-Api-Version: 2022-11-28',
            'User-Agent: Odoru-GitHub-Profile-Views-Counter',
        ];

        if ($token !== null && $token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
                'timeout' => 20,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        $statusCode = $this->extractStatusCode($http_response_header ?? []);

        if ($body === false) {
            throw new \RuntimeException('Unable to contact GitHub API.');
        }

        $payload = json_decode($body, true);

        if (!is_array($payload)) {
            throw new \RuntimeException('GitHub API returned invalid JSON.');
        }

		if ($statusCode < 200 || $statusCode >= 300) {
			$message = $payload['message'] ?? 'Unexpected GitHub API error';

			throw new \RuntimeException(sprintf(
				"GitHub API returned HTTP code %d.\nURL: %s\nMessage: %s\nResponse: %s",
			$statusCode,
			$url,
			$message,
			json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
			));
		}

        return [
            'count' => (int) ($payload['count'] ?? 0),
            'uniques' => (int) ($payload['uniques'] ?? 0),
        ];
    }

    private function extractStatusCode(array $headers): int
    {
        foreach ($headers as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', (string) $line, $matches) === 1) {
                return (int) $matches[1];
            }
        }

        return 0;
    }
}