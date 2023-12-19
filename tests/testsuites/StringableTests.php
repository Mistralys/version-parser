<?php

declare(strict_types=1);

namespace Mistralys\VersionParserTests;

use Mistralys\VersionParser\VersionParser;
use Mistralys\VersionParserTests\TestClasses\VersionParserTestCase;

final class StringableTests extends VersionParserTestCase
{
    public function test_toString() : void
    {
        $version = VersionParser::create('1.2.3-BranchName-rc5');

        $this->assertSame('1.2.3-BranchName-rc5', (string)$version);
        $this->assertSame('BranchName-rc5', (string)$version->getTagInfo());
    }
}
