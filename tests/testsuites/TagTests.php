<?php

declare(strict_types=1);

use Mistralys\VersionParser\VersionParser;
use Mistralys\VersionParser\VersionTag;
use Mistralys\VersionParserTests\TestClasses\VersionParserTestCase;

final class TagTests extends VersionParserTestCase
{
    // region: _Tests

    public function test_alpha() : void
    {
        $tag = $this->getTag('1.0.0-alpha');

        $this->assertTrue($tag->isAlpha());
        $this->assertSame('alpha', $tag->getTagName());
        $this->assertSame(1, $tag->getNumber());
    }

    public function test_alpha_short() : void
    {
        $tag = $this->getTag('1.0.0-A2');

        $this->assertTrue($tag->isAlpha());
        $this->assertSame('a', $tag->getTagName());
        $this->assertSame('alpha', $tag->getTagType());
        $this->assertSame(2, $tag->getNumber());
    }

    public function test_beta() : void
    {
        $tag = $this->getTag('1.0.0-beta');

        $this->assertTrue($tag->isBeta());
        $this->assertSame('beta', $tag->getTagName());
        $this->assertSame(1, $tag->getNumber());
    }

    public function test_beta_short() : void
    {
        $tag = $this->getTag('1.0.0-B2');

        $this->assertTrue($tag->isBeta());
        $this->assertSame('b', $tag->getTagName());
        $this->assertSame('beta', $tag->getTagType());
        $this->assertSame(2, $tag->getNumber());
    }

    public function test_snapshot() : void
    {
        $tag = $this->getTag('1.0.0-snapshot');

        $this->assertTrue($tag->isSnapshot());
        $this->assertSame('snapshot', $tag->getTagName());
        $this->assertSame(1, $tag->getNumber());
    }

    public function test_snapshot_short() : void
    {
        $tag = $this->getTag('1.0.0-S2');

        $this->assertTrue($tag->isSnapshot());
        $this->assertSame('s', $tag->getTagName());
        $this->assertSame('snapshot', $tag->getTagType());
        $this->assertSame(2, $tag->getNumber());
    }

    public function test_dev() : void
    {
        $tag = $this->getTag('1.0.0-dev');

        $this->assertTrue($tag->isDev());
        $this->assertSame('dev', $tag->getTagName());
        $this->assertSame(1, $tag->getNumber());
    }

    public function test_dev_short() : void
    {
        $tag = $this->getTag('1.0.0-D2');

        $testLabel = print_r($tag->toArray(), true);

        $this->assertTrue($tag->isDev(), $testLabel);
        $this->assertSame('d', $tag->getTagName(), $testLabel);
        $this->assertSame('dev', $tag->getTagType(), $testLabel);
        $this->assertSame(2, $tag->getNumber(), $testLabel);
    }

    public function test_patch() : void
    {
        $tag = $this->getTag('1.0.0-patch');

        $this->assertTrue($tag->isPatch());
        $this->assertSame('patch', $tag->getTagName());
        $this->assertSame(1, $tag->getNumber());
    }

    public function test_patch_short() : void
    {
        $tag = $this->getTag('1.0.0-P2');

        $testLabel = print_r($tag->toArray(), true);

        $this->assertTrue($tag->isPatch(), $testLabel);
        $this->assertSame('p', $tag->getTagName(), $testLabel);
        $this->assertSame('patch', $tag->getTagType(), $testLabel);
        $this->assertSame(2, $tag->getNumber(), $testLabel);
    }

    public function test_releaseCandidate() : void
    {
        $tag = $this->getTag('1.0.0-rc');

        $this->assertTrue($tag->isReleaseCandidate());
        $this->assertSame('rc', $tag->getTagName());
        $this->assertSame(1, $tag->getNumber());
    }

    public function test_customTag() : void
    {
        VersionParser::registerTagType('foobar', 14, 'f');

        $tag = $this->getTag('1.0.0-foobar');

        $this->assertTrue($tag->isTagType('foobar'));
        $this->assertSame('foobar', $tag->getTagName());
        $this->assertSame(1, $tag->getNumber());
    }

    public function test_custom_tag_short() : void
    {
        VersionParser::registerTagType('foobar', 14, 'f');

        $tag = $this->getTag('1.0.0-F2');

        $testLabel = print_r($tag->toArray(), true);

        $this->assertTrue($tag->isTagType('foobar'), $testLabel);
        $this->assertSame('f', $tag->getTagName(), $testLabel);
        $this->assertSame('foobar', $tag->getTagType(), $testLabel);
        $this->assertSame(2, $tag->getNumber(), $testLabel);
    }

    // endregion

    // region: Support methods

    public function getTag(string $versionString) : VersionTag
    {
        $tag = VersionParser::create($versionString)->getTagInfo();

        $this->assertNotNull($tag);

        return $tag;
    }

    // endregion
}
