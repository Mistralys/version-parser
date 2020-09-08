<?php

use PHPUnit\Framework\TestCase;
use Mistralys\VersionParser\VersionParser;

final class ParseTest extends TestCase
{
    public function test_parse()
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
                'label' => 'Triple digit version, higher numbers',
                'version' => '5.12.134',
                'expected' => 5012134,
                'int' => 5012134000000,
                'normalized' => '5.12.134'
            ),
            array(
                'label' => 'With beta tag',
                'version' => '1.0.0-beta',
                'expected' => 1000000.0001,
                'int' => 1000000000100,
                'normalized' => '1.0.0'
            ),
            array(
                'label' => 'With alpha tag',
                'version' => '1.0.0-alpha',
                'expected' => 1000000.000001,
                'int' => 1000000000001,
                'normalized' => '1.0.0'
            ),
            array(
                'label' => 'With release candidate tag',
                'version' => '1.0.0-rc',
                'expected' => 1000000.01,
                'int' => 1000000010000,
                'normalized' => '1.0.0'
            )
        );
        
        foreach($tests as $test)
        {
            $version = VersionParser::create($test['version']);
            
            $this->assertEquals($test['expected'], $version->getBuildNumber(), $test['label']);
            $this->assertEquals($test['int'], $version->getBuildNumberInt(), $test['label']);
            $this->assertEquals($test['normalized'], $version->getVersion(), $test['label']);
        }
    }
    
    public function test_empty() : void
    {
        $version = VersionParser::create('');
        
        $this->assertEquals('0.0.0', $version->getVersion());
        $this->assertEquals(0, $version->getBuildNumber());
        $this->assertEquals(0, $version->getBuildNumberInt());
    }
    
    public function test_invalid() : void
    {
        $version = VersionParser::create('eop op.kweofkjw.lpkpl-fcsfk');
        
        $this->assertEquals('0.0.0', $version->getVersion());
        $this->assertEquals(0, $version->getBuildNumber());
        $this->assertEquals(0, $version->getBuildNumberInt());
    }
    
    public function test_getTag() : void
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
                'expected' => 'custom-beta5',
                'type' => VersionParser::TAG_TYPE_BETA,
                'tagNumber' => 5
            ),
            array(
                'label' => 'Custom tag name, with number (beta tag number)',
                'version' => '1.0-custom3-beta5',
                'expected' => 'custom3-beta5',
                'type' => VersionParser::TAG_TYPE_BETA,
                'tagNumber' => 5
            ),
            array(
                'label' => 'Custom tag name, without known tag (no tag number)',
                'version' => '1.0-custom3',
                'expected' => 'custom3',
                'type' => VersionParser::TAG_TYPE_NONE,
                'tagNumber' => 0
            )
        );
        
        foreach($tests as $test)
        {
            $version = VersionParser::create($test['version']);
            
            $label = $test['label'].' ('.$test['version'].')';
            
            $this->assertEquals($test['expected'], $version->getTag(), $label);
            $this->assertEquals($test['type'], $version->getTagType(), $label);
            $this->assertEquals($test['tagNumber'], $version->getTagNumber(), $label);
        }
    }
    
    public function test_no_tag() : void
    {
        $version = VersionParser::create('1.0');
        
        $this->assertFalse($version->isAlpha());
        $this->assertFalse($version->isBeta());
        $this->assertFalse($version->isReleaseCandidate());
        $this->assertFalse($version->hasTag());
    }

    public function test_tag_beta() : void
    {
        $version = VersionParser::create('1.0-beta2');
        
        $this->assertTrue($version->isBeta());
        $this->assertFalse($version->isAlpha());
        $this->assertFalse($version->isReleaseCandidate());
    }

    public function test_tag_alpha() : void
    {
        $version = VersionParser::create('1.0-alpha2');
        
        $this->assertFalse($version->isBeta());
        $this->assertTrue($version->isAlpha());
        $this->assertFalse($version->isReleaseCandidate());
    }
    
    public function test_tag_releaseCandidate() : void
    {
        $version = VersionParser::create('1.0-rc2');
        
        $this->assertFalse($version->isBeta());
        $this->assertFalse($version->isAlpha());
        $this->assertTrue($version->isReleaseCandidate());
    }
    
    public function test_tag_hyphen() : void
    {
        $version = VersionParser::create('1.0-rc-2');
        
        $this->assertTrue($version->isReleaseCandidate());
        $this->assertEquals('rc2', $version->getTag());
    }
    
    public function test_tooManyDots() : void
    {
        $version = VersionParser::create('1.2.3.4');
        
        $this->assertEquals('1.2.3', $version->getVersion());
    }
    
    public function test_stripSpaces() : void
    {
        $version = VersionParser::create('1 . 2 .
   3');
        
        $this->assertEquals('1.2.3', $version->getVersion());
    }
    
    public function test_getBranchName() : void
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
        
        foreach($tests as $test)
        {
            $version = VersionParser::create($test['version']);

            $label = $test['label'].' ('.$test['version'].')';
            
            $this->assertEquals($test['name'], $version->getBranchName(), $label);
            $this->assertEquals($test['hasBranch'], $version->hasBranch(), $label);
        }
    }
    
    public function test_shortVersion() : void
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
        
        foreach($tests as $test)
        {
            $version = VersionParser::create($test['version']);
            
            $label = $test['label'].' ('.$test['version'].')';
            
            $this->assertEquals($test['expected'], $version->getShortVersion(), $label);
        }
    }
    
    public function test_normalize() : void
    {
        $version = VersionParser::create('1-BETA');
        
        $this->assertEquals('1.0.0-beta', $version->getTagVersion());
    }
}
