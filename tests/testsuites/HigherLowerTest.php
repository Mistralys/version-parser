<?php

declare(strict_types=1);

namespace Mistralys\VersionParserTests;

use Mistralys\VersionParser\VersionParser;
use Mistralys\VersionParserTests\TestClasses\VersionParserTestCase;

final class HigherLowerTest extends VersionParserTestCase
{
    public function test_higher(): void
    {
        $tests = array(
            array(
                'label' => '',
                'version' => '1.0-beta2',
                'compareWith' => '1.0-beta',
                'higher' => true
            ),
            array(
                'label' => 'Regular version higher than release candidate',
                'version' => '1.0',
                'compareWith' => '1.0-rc',
                'higher' => true
            ),
            array(
                'label' => 'Regular version higher than alpha',
                'version' => '1.0',
                'compareWith' => '1.0-alpha',
                'higher' => true
            ),
            array(
                'label' => 'Release candidate higher than beta',
                'version' => '1.0-rc',
                'compareWith' => '1.0-beta11',
                'higher' => true
            ),
            array(
                'label' => 'Beta 5 higher than beta 2',
                'version' => '1.0-beta5',
                'compareWith' => '1.0-beta2',
                'higher' => true
            ),
            array(
                'label' => 'Beta not higher than beta 2',
                'version' => '1.0-beta',
                'compareWith' => '1.0-beta2',
                'higher' => false
            )
        );

        foreach ($tests as $test) {
            $version = VersionParser::create($test['version']);
            $compare = VersionParser::create($test['compareWith']);

            $label = $test['label'] . PHP_EOL .
                'Version......: ' . $version->getBuildNumberInt() . ' (' . $test['version'] . ')' . PHP_EOL .
                'Higher than..: ' . $compare->getBuildNumberInt() . ' (' . $test['compareWith'] . ')';

            $this->assertSame($test['higher'] === true, $version->isHigherThan($compare), $label);
            $this->assertSame($test['higher'] !== true, $version->isLowerThan($compare), $label);
        }
    }
}
