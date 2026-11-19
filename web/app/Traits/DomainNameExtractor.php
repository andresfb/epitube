<?php

declare(strict_types=1);

namespace App\Traits;

trait DomainNameExtractor
{
    private function getDomainRoot(string $url): string
    {
        // Parse the URL and get the host part
        $host = parse_url($url, PHP_URL_HOST);
        if ($host === null) {
            return '';
        }

        // Remove possible leading "www."
        $host = preg_replace('/^www\./i', '', $host);

        // Split the host into its labels
        $parts = explode('.', $host);

        // If there are at least two parts (e.g., example.com), return the first one
        // This works for typical domains; for multi‑level TLDs (co.uk, .gov.au) you may need a list.
        return $parts[0] ?? 'some-domain';
    }
}
