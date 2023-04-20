<?php

declare(strict_types=1);

namespace Mistralys\VersionParserTests;

use Mistralys\VersionParser\VersionParser;
use Mistralys\VersionParserTests\TestClasses\VersionParserTestCase;

final class SortingTest extends VersionParserTestCase
{
    public function test_parse() : void
    {
        $versions = array(
            '1.1',
            '2',
            '1.5.9',
            '1.5.9-beta',
            '2.0.0-alpha'
        );

        $expected = array(
            '1.1.0',
            '1.5.9-beta',
            '1.5.9',
            '2.0.0-alpha',
            '2.0.0',
        );

        $parsed = array();

        foreach ($versions as $string) {
            $parsed[] = VersionParser::create($string);
        }

        usort($parsed, static function (VersionParser $a, VersionParser $b) : int {
            return $a->getBuildNumberInt() - $b->getBuildNumberInt();
        });

        $result = array();
        foreach ($parsed as $version) {
            $result[] = $version->getTagVersion();
        }

        $this->assertEquals($expected, $result);
    }
}
