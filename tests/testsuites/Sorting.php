<?php

use PHPUnit\Framework\TestCase;
use Mistralys\VersionParser\VersionParser;

final class SortingTest extends TestCase
{
    public function test_parse()
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

        foreach ($versions as $string)
        {
            $parsed[] = VersionParser::create($string);
        }

        usort($parsed, function (VersionParser $a, VersionParser $b)
        {
            return $a->getBuildNumberInt() - $b->getBuildNumberInt();
        });

        $result = array();
        foreach($parsed as $version)
        {
            $result[] = $version->getTagVersion();
        }

        $this->assertEquals($expected, $result);
    }
}
