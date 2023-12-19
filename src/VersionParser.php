<?php
/**
 * File containing the {@see VersionParser} class.
 *
 * @package VersionParser
 * @see VersionParser
 */

declare(strict_types=1);

namespace Mistralys\VersionParser;

use Mistralys\VersionParser\Parser\BuildNumberGenerator;
use Mistralys\VersionParser\Parser\ComponentDetector;
use Mistralys\VersionParser\Parser\NumberDetector;
use Mistralys\VersionParser\Parser\ShortTags;
use Mistralys\VersionParser\Parser\TagWeights;
use Stringable;

/**
 * Version number parsing utility: parses version numbers,
 * and allows retrieving information on the version.
 *
 * Supports beta, alpha and release candidate tags, as well
 * as custom tags and combinations thereof.
 *
 * Expects version to use the following structure:
 *
 * <code>Major.Minor.Patch-(Branch or release name)-(Release type: alpha, beta...)</code>
 *
 * Examples:
 *
 * - 1
 * - 1.0
 * - 1.1.0
 * - 1.145.147
 * - 1.1.5-beta
 * - 1.1.5-beta2
 * - 1.1.5-BranchName
 * - 1.1.5-BranchName-alpha2
 *
 * Usage:
 *
 * <code>
 * $version = VersionParser::create('1.0');
 * </code>
 *
 * @package VersionParser
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class VersionParser implements Stringable
{
    public const TAG_TYPE_NONE = 'none';
    public const TAG_TYPE_BETA = 'beta';
    public const TAG_TYPE_BETA_SHORT = 'b';
    public const TAG_TYPE_ALPHA = 'alpha';
    public const TAG_TYPE_ALPHA_SHORT = 'a';
    public const TAG_TYPE_RELEASE_CANDIDATE = 'rc';
    public const TAG_TYPE_SNAPSHOT = 'snapshot';
    public const TAG_TYPE_SNAPSHOT_SHORT = 's';
    public const TAG_TYPE_DEV = 'dev';
    public const TAG_TYPE_DEV_SHORT = 'd';
    public const TAG_TYPE_PATCH = 'patch';
    public const TAG_TYPE_PATCH_SHORT = 'p';
    public const TAG_TYPE_STABLE = 'stable';

    private string $version;

    private float $buildNumber = -1.0;

    private ?VersionTag $tag = null;

   /**
    * @var array<int,int>
    */
    private array $parts = array();
    private string $separator = '-';
    private bool $lowercase = true;

    private static ?array $tagWeights = null;

    private static ?array $shortTags = null;

    private string $original;

    private string $tagParts = '';

    private function __construct(string $version)
    {
        $this->original = $version;
        $this->version = $version;

        $this->parseNumber();
        $this->parseTag();
    }

    /**
     * @param bool $uppercase
     * @return $this
     * @deprecated Use {@see self::setTagUppercase()} instead.
     */
    public function setUppercase(bool $uppercase=true) : self
    {
        $this->lowercase = !$uppercase;
        return $this;
    }

    /**
     * Sets the tag type to be rendered in uppercase
     * when normalizing the version string, e.g. "1.0-BETA".
     *
     * @param bool $uppercase
     * @return $this
     */
    public function setTagUppercase(bool $uppercase=true) : self
    {
        $this->lowercase = !$uppercase;
        return $this;
    }

    /**
     * Sets the separator character to use between version
     * components when normalizing the version string.
     * Components are the number, tag type and branch name.
     *
     * @param string $char
     * @return $this
     */
    public function setSeparatorChar(string $char) : self
    {
        $this->separator = $char;
        return $this;
    }

   /**
    * Creates a new instance for the specified version string.
    *
    * @param string $version
    * @return VersionParser
    */
    public static function create(string $version) : VersionParser
    {
        return new VersionParser($version);
    }

   /**
    * Retrieves the version's build number as a float.
    *
    * @return float
    */
    public function getBuildNumber() : float
    {
        if($this->buildNumber === -1.0)
        {
            $this->calculateBuildNumber();
        }

        return $this->buildNumber;
    }

   /**
    * Retrieves the version's build number as an integer.
    *
    * @return int
    */
    public function getBuildNumberInt() : int
    {
        return (int)($this->getBuildNumber() * 1000000);
    }

   /**
    * Retrieves the major version number.
    *
    * @return int
    */
    public function getMajorVersion() : int
    {
        return $this->parts[0];
    }

   /**
    * Retrieves the minor version number.
    *
    * @return int
    */
    public function getMinorVersion() : int
    {
        return $this->parts[1];
    }

   /**
    * Retrieves the patch version number.
    *
    * @return int
    */
    public function getPatchVersion() : int
    {
        return $this->parts[2];
    }

    /**
     * Retrieves the full version with tag appended, if any.
     *
     * @return string
     */
    public function getTagVersion() : string
    {
        $version = $this->getVersion();

        if(!$this->hasTag())
        {
            return $version;
        }

        return $version.$this->separator.$this->getTag();
    }

   /**
    * Retrieves only the numeric version, omitting dots
    * as far as possible (e.g. `1.0.0` => `1`).
    *
    * @return string
    */
    public function getShortVersion() : string
    {
        if($this->parts[2] > 0)
        {
            $keep = $this->parts;
        }
        else if($this->parts[1] > 0)
        {
            $keep = array($this->parts[0], $this->parts[1]);
        }
        else
        {
            $keep = array($this->parts[0]);
        }

        return implode('.', $keep);
    }

    /**
     * Returns the original version string specified in the constructor.
     * @return string
     */
    public function getOriginalString(): string
    {
        return $this->original;
    }

    /**
    * Retrieves the normalized version tag.
    *
    * @return string
    * @see self::getTagInfo()
    */
    public function getTag() : string
    {
        if(isset($this->tag)) {
            return $this->tag->getNormalized();
        }

        return '';
    }

    public function getTagInfo() : VersionTag
    {
        return $this->tag;
    }

   /**
    * Whether the version has a release tag appended.
    *
    * @return bool
    */
    public function hasTag() : bool
    {
        return isset($this->tag);
    }

   /**
    * Retrieves the type of the release tag.
    *
    * @return string
    *
    * @see VersionParser::TAG_TYPE_NONE
    * @see VersionParser::TAG_TYPE_ALPHA
    * @see VersionParser::TAG_TYPE_BETA
    * @see VersionParser::TAG_TYPE_RELEASE_CANDIDATE
    * @see VersionParser::TAG_TYPE_DEV
    * @see VersionParser::TAG_TYPE_SNAPSHOT
    * @see VersionParser::TAG_TYPE_PATCH
    */
    public function getTagType() : string
    {
        if(isset($this->tag)) {
            return $this->tag->getTagType();
        }

        return self::TAG_TYPE_NONE;
    }

   /**
    * Retrieves the number of the release tag.
    *
    * @return int The tag number if present, `1` if no number has been specified, and `0` if the version has no tag.
    */
    public function getTagNumber() : int
    {
        if(isset($this->tag)) {
            return $this->tag->getNumber();
        }

        return 0;
    }

    public function isBeta() : bool
    {
        if(isset($this->tag)) {
            return $this->tag->isBeta();
        }

        return false;
    }

    public function isAlpha() : bool
    {
        return $this->getTagType() === self::TAG_TYPE_ALPHA;
    }

    public function isReleaseCandidate() : bool
    {
        if(isset($this->tag)) {
            return $this->tag->isReleaseCandidate();
        }
        return false;
    }

    public function isSnapshot() : bool
    {
        if(isset($this->tag)) {
            return $this->tag->isSnapshot();
        }

        return false;
    }

    public function isDev() : bool
    {
        if(isset($this->tag)) {
            return $this->tag->isDev();
        }

        return false;
    }

    public function isPatch() : bool
    {
        if(isset($this->tag)) {
            return $this->tag->isPatch();
        }

        return false;
    }

    public function isStable() : bool
    {
        if(isset($this->tag)) {
            return $this->tag->isStable();
        }

        // To tag = stable
        return true;
    }

   /**
    * Whether a branch name is present in the version.
    *
    * @return bool
    */
    public function hasBranch() : bool
    {
        return !empty($this->getBranchName());
    }

   /**
    * Retrieves the branch name, if any.
    *
    * @return string The branch name, or an empty string if none.
    */
    public function getBranchName() : string
    {
        if(isset($this->tag)) {
            return $this->tag->getBranchName();
        }

        return '';
    }

   /**
    * Retrieves the version without tag, normalized to
    * use all three levels, even if less have been
    * specified (e.g. `1` => `1.0.0`).
    *
    * @return string
    */
    public function getVersion() : string
    {
        return implode('.', $this->parts);
    }

    private function parseTag() : void
    {
        if (empty($this->tagParts)) {
            return;
        }

        $this->tag = (new ComponentDetector($this, $this->tagParts))->detectTag();
    }

    public function toArray() : array
    {
        $tag = null;
        if(isset($this->tag)) {
            $tag = $this->tag->toArray();
        }

        return array(
            'originalVersion' => $this->getOriginalString(),
            'normalized' => $this->getVersion(),
            'majorVersion' => $this->getMajorVersion(),
            'minorVersion' => $this->getMinorVersion(),
            'patchVersion' => $this->getPatchVersion(),
            'shortVersion' => $this->getShortVersion(),
            'buildNumber' => $this->getBuildNumber(),
            'buildNumberInt' => $this->getBuildNumberInt(),
            'tag' => $tag
        );
    }

    private function parseNumber() : void
    {
        $number = new NumberDetector($this->version);

        $this->parts = $number->getNumbers();
        $this->tagParts = $number->getTagString();
    }

    private function calculateBuildNumber() : void
    {
        $this->buildNumber = (new BuildNumberGenerator(
            $this->getMajorVersion(),
            $this->getMinorVersion(),
            $this->getPatchVersion(),
            $this->tag
        ))
            ->getBuildNumber();
    }

    /**
     * Whether this version is higher than the specified version.
     *
     * @param VersionParser $version
     * @return bool
     */
    public function isHigherThan(VersionParser $version) : bool
    {
        return $this->getBuildNumberInt() > $version->getBuildNumberInt();
    }

    /**
     * Whether this version is lower than the specified version.
     *
     * @param VersionParser $version
     * @return bool
     */
    public function isLowerThan(VersionParser $version) : bool
    {
        return $this->getBuildNumberInt() < $version->getBuildNumberInt();
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
     * @return void
     */
    public static function registerTagType(string $name, int $weight, string $shortName='') : void
    {
        TagWeights::registerTagType($name, $weight, $shortName);
    }

    /**
     * @return array<string,int>
     * @deprecated Use {@see TagWeights::getWeights()} instead.
     */
    public static function getTagWeights() : array
    {
        return TagWeights::getWeights();
    }

    public static function resetTagTypes() : void
    {
        TagWeights::reset();
        ShortTags::reset();
    }

    public function isTagUppercase() : bool
    {
        return !$this->lowercase;
    }

    public function getSeparatorChar() : string
    {
        return $this->separator;
    }

    public function __toString() : string
    {
        return $this->getTagVersion();
    }
}
