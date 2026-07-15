<?php

declare(strict_types=1);

namespace Arara\Tests\Unit;

use Arara\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function test_config_sets_defaults(): void
    {
        $config = new Config(apiKey: 'test-key');

        $this->assertSame('test-key', $config->apiKey);
        $this->assertSame('https://api.ararahq.com', $config->baseUrl);
        $this->assertSame(30, $config->timeout);
        $this->assertSame(3, $config->retryTimes);
        $this->assertSame(100, $config->retryDelayMs);
        $this->assertSame('v1', $config->apiVersion);
    }

    public function test_config_accepts_custom_values(): void
    {
        $config = new Config(
            apiKey: 'custom-key',
            baseUrl: 'https://custom.api.com',
            timeout: 60,
            retryTimes: 5,
            retryDelayMs: 200,
            apiVersion: 'v2',
        );

        $this->assertSame('custom-key', $config->apiKey);
        $this->assertSame('https://custom.api.com', $config->baseUrl);
        $this->assertSame(60, $config->timeout);
        $this->assertSame(5, $config->retryTimes);
        $this->assertSame(200, $config->retryDelayMs);
        $this->assertSame('v2', $config->apiVersion);
    }
}
