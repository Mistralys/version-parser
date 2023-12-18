<?php

declare(strict_types=1);

namespace Mistralys\VersionParser;

use Mistralys\VersionParserTests\TestClasses\VersionParserTestCase;

final class QualifierTests extends VersionParserTestCase
{
    public function test_no_tag(): void
    {
        $version = VersionParser::create('1');

        $this->assertTrue($version->isStable());
        $this->assertFalse($version->isAlpha());
        $this->assertFalse($version->isBeta());
        $this->assertFalse($version->isReleaseCandidate());
        $this->assertFalse($version->isSnapshot());
        $this->assertFalse($version->isDev());
        $this->assertFalse($version->isPatch());
    }

    public function test_stable(): void
    {
        $version = VersionParser::create('1-stable');

        $this->assertTrue($version->isStable());
        $this->assertFalse($version->isAlpha());
        $this->assertFalse($version->isBeta());
        $this->assertFalse($version->isReleaseCandidate());
        $this->assertFalse($version->isSnapshot());
        $this->assertFalse($version->isDev());
        $this->assertFalse($version->isPatch());
    }

    public function test_tag_beta(): void
    {
        $version = VersionParser::create('1-beta');

        $this->assertFalse($version->isStable());
        $this->assertTrue($version->isBeta());
        $this->assertFalse($version->isAlpha());
        $this->assertFalse($version->isReleaseCandidate());
        $this->assertFalse($version->isSnapshot());
        $this->assertFalse($version->isDev());
        $this->assertFalse($version->isPatch());
    }

    public function test_tag_alpha(): void
    {
        $version = VersionParser::create('1-alpha');

        $this->assertFalse($version->isStable());
        $this->assertFalse($version->isBeta());
        $this->assertTrue($version->isAlpha());
        $this->assertFalse($version->isReleaseCandidate());
        $this->assertFalse($version->isSnapshot());
        $this->assertFalse($version->isDev());
        $this->assertFalse($version->isPatch());
    }

    public function test_tag_releaseCandidate(): void
    {
        $version = VersionParser::create('1-rc');

        $this->assertFalse($version->isStable());
        $this->assertFalse($version->isBeta());
        $this->assertFalse($version->isAlpha());
        $this->assertTrue($version->isReleaseCandidate());
        $this->assertFalse($version->isSnapshot());
        $this->assertFalse($version->isDev());
        $this->assertFalse($version->isPatch());
    }

    public function test_tag_snapshot(): void
    {
        $version = VersionParser::create('1-snapshot');

        $this->assertFalse($version->isStable());
        $this->assertFalse($version->isBeta());
        $this->assertFalse($version->isAlpha());
        $this->assertFalse($version->isReleaseCandidate());
        $this->assertTrue($version->isSnapshot());
        $this->assertFalse($version->isDev());
        $this->assertFalse($version->isPatch());
    }

    public function test_tag_dev(): void
    {
        $version = VersionParser::create('1-dev');

        $this->assertFalse($version->isStable());
        $this->assertFalse($version->isBeta());
        $this->assertFalse($version->isAlpha());
        $this->assertFalse($version->isReleaseCandidate());
        $this->assertFalse($version->isSnapshot());
        $this->assertTrue($version->isDev());
        $this->assertFalse($version->isPatch());
    }

    public function test_tag_patch(): void
    {
        $version = VersionParser::create('1-patch');

        $this->assertFalse($version->isStable());
        $this->assertFalse($version->isBeta());
        $this->assertFalse($version->isAlpha());
        $this->assertFalse($version->isReleaseCandidate());
        $this->assertFalse($version->isSnapshot());
        $this->assertFalse($version->isDev());
        $this->assertTrue($version->isPatch());
    }
}
