<?php

declare(strict_types=1);

namespace Mistralys\VersionParserTests\TestClasses;

use Mistralys\VersionParser\VersionParser;
use PHPUnit\Framework\TestCase;

abstract class VersionParserTestCase extends TestCase
{
    protected function setUp(): void
    {
        VersionParser::resetTagTypes();
    }
}
