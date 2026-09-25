<?php

declare(strict_types=1);

namespace Arara\Tests\Unit;

use Arara\Arara;
use PHPUnit\Framework\TestCase;

final class ReleaseVersionScriptTest extends TestCase
{
    private const SCRIPT = __DIR__ . '/../../scripts/release-version.sh';

    private string $repo;

    protected function setUp(): void
    {
        $this->repo = sys_get_temp_dir() . '/arara-release-' . bin2hex(random_bytes(6));
        mkdir($this->repo);
        $this->git('init -q');
        $this->git('commit -q --allow-empty -m init');
        file_put_contents($this->repo . '/CHANGELOG.md', "# Changelog\n\n## [2.0.0] - 2026-09-24\n\n## [1.8.1]\n");
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . escapeshellarg($this->repo));
    }

    public function test_publishes_new_version_greater_than_latest_tag(): void
    {
        $this->git('tag v1.8.1');
        $this->git('tag v1.10.0');
        file_put_contents($this->repo . '/CHANGELOG.md', "## [2.0.0]\n");

        [$code, $out] = $this->runScript('2.0.0');

        $this->assertSame(0, $code);
        $this->assertSame(['tag=v2.0.0', 'publish=true'], $out);
    }

    public function test_skips_when_tag_already_exists(): void
    {
        $this->git('tag v2.0.0');

        [$code, $out] = $this->runScript('2.0.0');

        $this->assertSame(0, $code);
        $this->assertSame(['tag=v2.0.0', 'publish=false'], $out);
    }

    public function test_fails_when_version_is_lower_than_latest_tag(): void
    {
        $this->git('tag v2.1.0');

        [$code] = $this->runScript('2.0.0');

        $this->assertSame(1, $code);
    }

    public function test_compares_versions_semantically_not_lexically(): void
    {
        $this->git('tag v1.9.0');
        file_put_contents($this->repo . '/CHANGELOG.md', "## [1.10.0]\n");

        [$code, $out] = $this->runScript('1.10.0');

        $this->assertSame(0, $code);
        $this->assertSame('publish=true', $out[1]);
    }

    public function test_fails_without_changelog_section(): void
    {
        [$code] = $this->runScript('2.0.1');

        $this->assertSame(1, $code);
    }

    public function test_fails_on_invalid_version(): void
    {
        [$code] = $this->runScript('2.0');

        $this->assertSame(1, $code);
    }

    public function test_current_sdk_version_has_changelog_section(): void
    {
        $changelog = (string) file_get_contents(__DIR__ . '/../../CHANGELOG.md');

        $this->assertStringContainsString('## [' . Arara::VERSION . ']', $changelog);
    }

    /**
     * @return array{int, list<string>}
     */
    private function runScript(string $version): array
    {
        $command = 'cd ' . escapeshellarg($this->repo) . ' && ' . escapeshellarg((string) realpath(self::SCRIPT)) . ' ' . escapeshellarg($version) . ' 2>/dev/null';
        $output = [];
        exec($command, $output, $code);

        return [$code, array_values($output)];
    }

    private function git(string $args): void
    {
        exec('git -c tag.gpgSign=false -c commit.gpgSign=false -c user.email=t@t -c user.name=t -C ' . escapeshellarg($this->repo) . ' ' . $args, $unused, $code);
        $this->assertSame(0, $code, "git {$args}");
    }
}
