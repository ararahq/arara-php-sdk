<?php

declare(strict_types=1);

namespace Arara;

final readonly class Config
{
    public const DEFAULT_BASE_URL = 'https://api.ararahq.com';
    public const DEFAULT_TIMEOUT = 30;
    public const DEFAULT_RETRY_TIMES = 3;
    public const DEFAULT_RETRY_DELAY_MS = 100;
    public const API_VERSION = 'v1';

    public function __construct(
        public string $apiKey,
        public string $baseUrl = self::DEFAULT_BASE_URL,
        public int $timeout = self::DEFAULT_TIMEOUT,
        public int $retryTimes = self::DEFAULT_RETRY_TIMES,
        public int $retryDelayMs = self::DEFAULT_RETRY_DELAY_MS,
        public string $apiVersion = self::API_VERSION,
    ) {}
}
