<?php

declare(strict_types=1);

namespace Mistralys\VersionParser\Parser;

use Mistralys\VersionParser\VersionParser;

class ShortTags
{
    public const DEFAULT_SHORT_TAGS = array(
        VersionParser::TAG_TYPE_ALPHA => VersionParser::TAG_TYPE_ALPHA_SHORT,
        VersionParser::TAG_TYPE_BETA => VersionParser::TAG_TYPE_BETA_SHORT,
        VersionParser::TAG_TYPE_PATCH => VersionParser::TAG_TYPE_PATCH_SHORT,
        VersionParser::TAG_TYPE_SNAPSHOT => VersionParser::TAG_TYPE_SNAPSHOT_SHORT,
        VersionParser::TAG_TYPE_DEV => VersionParser::TAG_TYPE_DEV_SHORT
    );

    /**
     * @var array<string,string>|null
     */
    private static ?array $shortTags = null;

    /**
     * @return array<string,string>
     */
    public static function getTagNames() : array
    {
        if(!isset(self::$shortTags)) {
            self::$shortTags = ShortTags::DEFAULT_SHORT_TAGS;
        }

        return self::$shortTags;
    }

    public static function reset() : void
    {
        self::$shortTags = null;
    }

    public static function registerTagType(string $name, string $shortName) : void
    {
        $tags = self::getTagNames();
        $tags[$name] = $shortName;

        self::$shortTags = $tags;
    }
}
