<?php

declare(strict_types=1);

namespace Mistralys\VersionParser\Parser;

use Mistralys\VersionParser\VersionParser;

class TagWeights
{
    public const DEFAULT_TAG_WEIGHTS = array(
        VersionParser::TAG_TYPE_DEV => 8,
        VersionParser::TAG_TYPE_SNAPSHOT => 8,
        VersionParser::TAG_TYPE_ALPHA => 6,
        VersionParser::TAG_TYPE_BETA => 4,
        VersionParser::TAG_TYPE_RELEASE_CANDIDATE => 2,
        VersionParser::TAG_TYPE_PATCH => 1,
        VersionParser::TAG_TYPE_STABLE => 0,
        VersionParser::TAG_TYPE_NONE => 0,

        // Short versions after the full ones
        VersionParser::TAG_TYPE_ALPHA_SHORT => 6,
        VersionParser::TAG_TYPE_BETA_SHORT => 4,
        VersionParser::TAG_TYPE_PATCH_SHORT => 1,
        VersionParser::TAG_TYPE_SNAPSHOT_SHORT => 8,
        VersionParser::TAG_TYPE_DEV_SHORT => 8
    );

    /**
     * @var array<string,int>|null
     */
    private static ?array $tagWeights = null;

    /**
     * @return array<string,int>
     */
    public static function getWeights() : array
    {
        if(!isset(self::$tagWeights)) {
            self::$tagWeights = self::DEFAULT_TAG_WEIGHTS;
        }

        return self::$tagWeights;
    }

    public static function reset() : void
    {
        self::$tagWeights = null;
    }

    /**
     * Registers a tag name to look for in version strings,
     * in addition to the bundled tags like "beta" or "alpha".
     *
     * NOTE: Can also be used to change the weight of the
     * default tags.
     *
     * @param string $name
     * @param int $weight Used for version comparisons, like beta > alpha.
     *                  The higher the weight, the further it "sinks down", e.g.
     *              "alpha" has a default weight of "6" and beta a weight of "4".
     * @param string $shortName Optional short variant of the tag name.
     * @return void
     */
    public static function registerTagType(string $name, int $weight, string $shortName='') : void
    {
        $tagWeights = self::getWeights();
        $tagWeights[$name] = $weight;

        if(!empty($shortName)) {
            ShortTags::registerTagType($name, $shortName);
            $tagWeights[$shortName] = $weight;
        }

        self::$tagWeights = $tagWeights;
    }
}
