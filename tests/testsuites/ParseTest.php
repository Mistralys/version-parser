<?php

declare(strict_types=1);

namespace Mistralys\VersionParserTests;

use Mistralys\VersionParser\VersionParser;
use Mistralys\VersionParserTests\TestClasses\VersionParserTestCase;

final class ParseTest extends VersionParserTestCase
{
    public function test_parse(): void
    {
        $tests = array(
            array(
                'label' => 'Single digit version',
                'version' => '1',
                'expected' => 1000000,
                'int' => 1000000000000,
                'normalized' => '1.0.0'
            ),
            array(
                'label' => 'Double digit version',
                'version' => '1.1',
                'expected' => 1001000,
                'int' => 1001000000000,
                'normalized' => '1.1.0'
            ),
            array(
                'label' => 'Triple digit version',
                'version' => '1.1.1',
                'expected' => 1001001,
                'int' => 1001001000000,
                'normalized' => '1.1.1'
            ),
            array(
                'label' => 'Triple digit version',
                'version' => '1.12.348',
                'expected' => 1012348,
                'int' => 1012348000000,
                'normalized' => '1.12.348'
            ),
            array(
                'label' => 'Triple digit version, higher numbers',
                'version' => '5.12.134',
                'expected' => 5012134,
                'int' => 5012134000000,
                'normalized' => '5.12.134'
            ),
            array(
                'label' => 'With beta tag',
                'version' => '1.0.0-beta',
                'expected' => 999999.000101,
                'int' => 999999000101,
                'normalized' => '1.0.0'
            ),
            array(
                'label' => 'With beta tag, numbered 2',
                'version' => '1.0.0-beta2',
                'expected' => 999999.000201,
                'int' => 999999000201,
                'normalized' => '1.0.0'
            ),
            array(
                'label' => 'With beta tag, numbered 2, with dot separator',
                'version' => '1.0.0-beta.2',
                'expected' => 999999.000201,
                'int' => 999999000201,
                'normalized' => '1.0.0'
            ),
            array(
                'label' => 'With beta tag, numbered 2, with hyphen separator',
                'version' => '1.0.0-beta-2',
                'expected' => 999999.000201,
                'int' => 999999000201,
                'normalized' => '1.0.0'
            ),
            array(
                'label' => 'With beta tag, numbered 2, with underscore separator',
                'version' => '1.0.0-beta_2',
                'expected' => 999999.000201,
                'int' => 999999000201,
                'normalized' => '1.0.0'
            ),
            array(
                'label' => 'With alpha tag',
                'version' => '1.0.0-alpha',
                'expected' => 999999.000002,
                'int' => 999999000002,
                'normalized' => '1.0.0'
            ),
            array(
                'label' => 'With release candidate tag',
                'version' => '1.0.0-rc',
                'expected' => 999999.010001,
                'int' => 999999010001,
                'normalized' => '1.0.0'
            ),
            array(
                'label' => 'With branch name',
                'version' => '1.0.0-BranchName',
                'expected' => 1000000,
                'int' => 1000000000000,
                'normalized' => '1.0.0'
            ),
            array(
                'label' => 'With branch name and release candidate tag',
                'version' => '1.0.0-BranchName-rc',
                'expected' => 999999.010001,
                'int' => 999999010001,
                'normalized' => '1.0.0'
            ),
            array(
                'label' => 'With underscores as separators',
                'version' => '1.0.0_BranchName_rc',
                'expected' => 999999.010001,
                'int' => 999999010001,
                'normalized' => '1.0.0'
            )
        );

        foreach ($tests as $test) {
            $version = VersionParser::create($test['version']);

            $this->assertEquals($test['expected'], $version->getBuildNumber(), $test['label'].print_r($version->toArray(), true));
            $this->assertEquals($test['int'], $version->getBuildNumberInt(), $test['label']);
            $this->assertEquals($test['normalized'], $version->getVersion(), $test['label']);
        }
    }

    public function test_empty(): void
    {
        $version = VersionParser::create('');

        $this->assertEquals('0.0.0', $version->getVersion());
        $this->assertEquals(0, $version->getBuildNumber());
        $this->assertEquals(0, $version->getBuildNumberInt());
    }

    public function test_invalid(): void
    {
        $version = VersionParser::create('eop op.kweofkjw.lpkpl-fcsfk');

        $this->assertEquals('0.0.0', $version->getVersion());
        $this->assertEquals(0, $version->getBuildNumber());
        $this->assertEquals(0, $version->getBuildNumberInt());
    }

    public function test_getTag(): void
    {
        $tests = array(
            array(
                'label' => 'No tag',
                'version' => '1.0',
                'expected' => '',
                'type' => VersionParser::TAG_TYPE_NONE,
                'tagNumber' => 0
            ),
            array(
                'label' => 'No tag, but with hyphen',
                'version' => '1.0-',
                'expected' => '',
                'type' => VersionParser::TAG_TYPE_NONE,
                'tagNumber' => 0
            ),
            array(
                'label' => 'Implicit tag number 1',
                'version' => '1.0-beta',
                'expected' => 'beta',
                'type' => VersionParser::TAG_TYPE_BETA,
                'tagNumber' => 1
            ),
            array(
                'label' => 'Setting implicit number 1',
                'version' => '1.0-beta1',
                'expected' => 'beta',
                'type' => VersionParser::TAG_TYPE_BETA,
                'tagNumber' => 1
            ),
            array(
                'label' => 'Lowercasing the tag',
                'version' => '1.0-Beta2',
                'expected' => 'beta2',
                'type' => VersionParser::TAG_TYPE_BETA,
                'tagNumber' => 2
            ),
            array(
                'label' => 'Alpha tag',
                'version' => '1.0-alpha3',
                'expected' => 'alpha3',
                'type' => VersionParser::TAG_TYPE_ALPHA,
                'tagNumber' => 3
            ),
            array(
                'label' => 'Release candidate tag',
                'version' => '1.0-rc2',
                'expected' => 'rc2',
                'type' => VersionParser::TAG_TYPE_RELEASE_CANDIDATE,
                'tagNumber' => 2
            ),
            array(
                'label' => 'Custom tag name, without number (beta tag number)',
                'version' => '1.0-custom-beta5',
                'expected' => 'Custom-beta5',
                'type' => VersionParser::TAG_TYPE_BETA,
                'tagNumber' => 5
            ),
            array(
                'label' => 'Custom tag name, with number (beta tag number)',
                'version' => '1.0-custom3-beta5',
                'expected' => 'Custom3-beta5',
                'type' => VersionParser::TAG_TYPE_BETA,
                'tagNumber' => 5
            ),
            array(
                'label' => 'Custom tag name, without known tag (no tag number)',
                'version' => '1.0-custom3',
                'expected' => 'Custom3',
                'type' => VersionParser::TAG_TYPE_NONE,
                'tagNumber' => 0
            )
        );

        foreach ($tests as $test) {
            $version = VersionParser::create($test['version']);

            $label = $test['label'].' ('.$test['version'].')'.PHP_EOL.print_r($version->toArray(), true);

            $this->assertEquals($test['expected'], $version->getTag(), $label);
            $this->assertEquals($test['type'], $version->getTagType(), $label);
            $this->assertEquals($test['tagNumber'], $version->getTagNumber(), $label);
        }
    }

    public function test_no_tag(): void
    {
        $version = VersionParser::create('1.0');

        $this->assertFalse($version->hasTag());
    }

    public function test_tag_info() : void
    {
        $version = VersionParser::create('1.0-beta11');

        $tag = $version->getTagInfo();
        $this->assertNotNull($tag);

        $testLabel = print_r($version->toArray(), true);

        $this->assertTrue($tag->isBeta(), $testLabel);
        $this->assertFalse($tag->isAlpha(), $testLabel);
        $this->assertFalse($tag->isReleaseCandidate(), $testLabel);
        $this->assertSame(11, $tag->getNumber(), $testLabel);
    }

    public function test_tag_alpha() : void
    {
        $version = VersionParser::create('1.0-alpha2');

        $this->assertFalse($version->isBeta());
        $this->assertTrue($version->isAlpha());
        $this->assertFalse($version->isReleaseCandidate());
    }

    public function test_tag_releaseCandidate(): void
    {
        $version = VersionParser::create('1.0-rc2');

        $this->assertFalse($version->isBeta());
        $this->assertFalse($version->isAlpha());
        $this->assertTrue($version->isReleaseCandidate());
    }

    public function test_tag_snapshot(): void
    {
        $version = VersionParser::create('1.0-snapshot');

        $this->assertTrue($version->isSnapshot());
    }

    public function test_tag_hyphen(): void
    {
        $version = VersionParser::create('1.0-rc-2');

        $this->assertTrue($version->isReleaseCandidate());
        $this->assertEquals('rc2', $version->getTag());
    }

    public function test_tag_dot() : void
    {
        $version = VersionParser::create('1.0-rc.2');

        $this->assertTrue($version->isReleaseCandidate());
        $this->assertEquals('rc2', $version->getTag());
    }

    public function test_tag_underscore() : void
    {
        $version = VersionParser::create('1.0-rc_2');

        $this->assertTrue($version->isReleaseCandidate());
        $this->assertEquals('rc2', $version->getTag());
    }

    public function test_tag_and_branch() : void
    {
        $version = VersionParser::create('1.0-BranchName-RC');

        $this->assertTrue($version->isReleaseCandidate());
        $this->assertEquals('BranchName-rc', $version->getTag());
        $this->assertEquals('BranchName', $version->getBranchName(), print_r($version->toArray(), true));
    }

    public function test_branchCanS0tartWithNumber() : void
    {
        $version = VersionParser::create('1.0-42BranchName-RC');

        $testLabel = print_r($version->toArray(), true);

        $this->assertEquals('1.0.0', $version->getVersion(), $testLabel);
        $this->assertTrue($version->isReleaseCandidate(), $testLabel);
        $this->assertEquals('42BranchName-rc', $version->getTag(), $testLabel);
        $this->assertEquals('42BranchName', $version->getBranchName(), $testLabel);
    }

    public function test_branchCanContainSpecialChars() : void
    {
        $version = VersionParser::create('1.5.2 "Foobar/42"');

        $testLabel = print_r($version->toArray(), true);

        $this->assertEquals('1.5.2', $version->getVersion(), $testLabel);
        $this->assertEquals('Foobar/42', $version->getBranchName(), $testLabel);
    }

    public function test_tooManyDots() : void
    {
        $version = VersionParser::create('1.2.3.4');

        $this->assertEquals('1.2.3', $version->getVersion());
    }

    public function test_stripSpaces(): void
    {
        $version = VersionParser::create('1 . 2 .
   3');

        $this->assertEquals('1.2.3', $version->getVersion());
    }

    public function test_preserveSpacesInTag(): void
    {
        $version = VersionParser::create('1 . 2 . 3 BranchName RC');

        $testLabel = print_r($version->toArray(), true);

        $this->assertEquals('1.2.3', $version->getVersion(), $testLabel);
        $this->assertEquals('BranchName', $version->getBranchName(), $testLabel);
        $this->assertEquals('rc', $version->getTagType(), $testLabel);
    }

    public function test_stripSpecialChars() : void
    {
        $version = VersionParser::create('1.2 (BranchName) / Alpha2');

        $testLabel = print_r($version->toArray(), true);

        $this->assertEquals('1.2.0', $version->getVersion(), $testLabel);
        $this->assertEquals('BranchName', $version->getBranchName(), $testLabel);
        $this->assertEquals('alpha', $version->getTagType(), $testLabel);
    }

    public function test_preserveOriginalCharsInBranch() : void
    {
        $version = VersionParser::create('1.2 "Super:branch"');

        $testLabel = print_r($version->toArray(), true);

        $this->assertEquals('1.2.0', $version->getVersion(), $testLabel);
        $this->assertEquals('Super:branch', $version->getBranchName(), $testLabel);
    }

    public function test_normalizeBranchName() : void
    {
        $version = VersionParser::create('1.2 "Super branch/epic*new"');

        $testLabel = print_r($version->toArray(), true);

        $tag = $version->getTagInfo();
        $this->assertNotNull($tag);
        $this->assertEquals('Super branch/epic*new', $tag->getBranchName(), $testLabel);
        $this->assertEquals('SuperBranch/Epic*New', $tag->getBranchNameNormalized(), $testLabel);
    }

    public function test_branchNameAfterTag() : void
    {
        $version = VersionParser::create('1.2-beta2 "Super branch"');

        $testLabel = print_r($version->toArray(), true);

        $this->assertEquals('1.2.0', $version->getVersion(), $testLabel);
        $this->assertEquals('beta', $version->getTagType(), $testLabel);
        $this->assertEquals('Super branch', $version->getBranchName(), $testLabel);
        $this->assertEquals('1.2.0-SuperBranch-beta2', $version->getTagVersion(), $testLabel);
    }

    public function test_branchNameBeforeAndAfterTag() : void
    {
        $version = VersionParser::create('1.2 Super beta branch');

        $testLabel = print_r($version->toArray(), true);

        $this->assertEquals('1.2.0', $version->getVersion(), $testLabel);
        $this->assertEquals('beta', $version->getTagType(), $testLabel);

        // This cannot be resolved back to the original branch name,
        // because the tag qualifier is in the middle of it.
        $this->assertEquals('Super-branch', $version->getBranchName(), $testLabel);
    }

    public function test_getBranchName(): void
    {
        $tests = array(
            array(
                'label' => 'No branch, no tag',
                'version' => '1',
                'name' => '',
                'hasBranch' => false
            ),
            array(
                'label' => 'With branch, no tag',
                'version' => '1-Foobar',
                'name' => 'Foobar',
                'hasBranch' => true
            ),
            array(
                'label' => 'With branch and tag',
                'version' => '1-Foobar-beta',
                'name' => 'Foobar',
                'hasBranch' => true
            )
        );

        foreach ($tests as $test) {
            $version = VersionParser::create($test['version']);

            $label = $test['label'].' ('.$test['version'].')'.PHP_EOL.print_r($version->toArray(), true);

            $this->assertEquals($test['name'], $version->getBranchName(), $label);
            $this->assertEquals($test['hasBranch'], $version->hasBranch(), $label);
        }
    }

    public function test_shortVersion(): void
    {
        $tests = array(
            array(
                'label' => 'Single digit',
                'version' => '1',
                'expected' => '1'
            ),
            array(
                'label' => 'Triple digit, only major',
                'version' => '1.0.0',
                'expected' => '1'
            ),
            array(
                'label' => 'Triple digit, last',
                'version' => '1.0.1',
                'expected' => '1.0.1'
            ),
            array(
                'label' => 'Triple digit, only minor',
                'version' => '1.1.0',
                'expected' => '1.1'
            )
        );

        foreach ($tests as $test) {
            $version = VersionParser::create($test['version']);

            $label = $test['label'] . ' (' . $test['version'] . ')';

            $this->assertEquals($test['expected'], $version->getShortVersion(), $label);
        }
    }

    public function test_normalize(): void
    {
        $version = VersionParser::create('1-BETA');

        $this->assertEquals('1.0.0-beta', $version->getTagVersion());
    }

    public function test_normalizeUppercase(): void
    {
        $version = VersionParser::create('1-BranchName-beta');

        $this->assertEquals('1.0.0-BranchName-BETA', $version->setTagUppercase()->getTagVersion(), print_r($version->toArray(), true));
    }

    public function test_setSeparator(): void
    {
        $version = VersionParser::create('1-BranchName-beta2');

        $this->assertEquals('1.0.0_BranchName_beta2', $version->setSeparatorChar('_')->getTagVersion());
    }

    public function test_registerTagType(): void
    {
        VersionParser::registerTagType('foobar', 5);

        $version = VersionParser::create('1-foobar2');

        $this->assertEquals('foobar', $version->getTagType());
    }
}
