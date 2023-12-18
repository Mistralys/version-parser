<?php

declare(strict_types=1);

namespace Mistralys\VersionParser;

use Mistralys\VersionParser\Parser\ShortTags;
use Mistralys\VersionParser\Parser\TagWeights;

class VersionTag
{
    private string $tagName;
    private int $number;
    private string $branch;
    private VersionParser $version;

    public function __construct(VersionParser $version, string $tagName, int $number, string $branchName='')
    {
        $this->version = $version;
        $this->tagName = strtolower($tagName);
        $this->number = $number;
        $this->branch = $branchName;

        if($this->number === 0 && !empty($this->tagName)) {
            $this->number = 1;
        }
    }

    public function getWeight() : int
    {
        return TagWeights::getWeights()[$this->tagName] ?? 0;
    }

    public function getBranchName() : string
    {
        return $this->branch;
    }

    public function getNormalized() : string
    {
        $tagName = $this->getTagName();

        if(empty($tagName)) {
            return $this->getBranchName();
        }

        if($this->number > 1) {
            $name = $tagName.$this->number;
        } else {
            $name = $tagName;
        }

        if(!empty($this->branch)) {
            $name = $this->branch.$this->version->getSeparatorChar().$name;
        }

        return $name;
    }

    /**
     * Retrieves the tag name used in the version.
     *
     * NOTE: This can be either the long or short variant
     * of the tag, if it has one.
     *
     * @return string Lowercase by default, or uppercase if enabled - see {@see VersionParser::setTagUppercase()}.
     */
    public function getTagName() : string
    {
        if($this->version->isTagUppercase()) {
            return strtoupper($this->tagName);
        }

        return $this->tagName;
    }

    /**
     * Retrieves the tag type, which excludes the short tag
     * variants. For example, if the tag is <code>a</code>,
     * this will return <code>alpha</code>.
     *
     * @return string The lowercase tag type.
     */
    public function getTagType() : string
    {
        if(empty($this->tagName)) {
            return VersionParser::TAG_TYPE_NONE;
        }

        $short = ShortTags::getTagNames();
        $tagName = array_search($this->tagName, $short);
        if($tagName !== false) {
            return $tagName;
        }

        return $this->tagName;
    }

    public function getNumber() : int
    {
        return $this->number;
    }

    public function isReleaseCandidate() : bool
    {
        return $this->isTagType(VersionParser::TAG_TYPE_RELEASE_CANDIDATE);
    }

    public function isSnapshot() : bool
    {
        return $this->isTagType(VersionParser::TAG_TYPE_SNAPSHOT);
    }

    public function isBeta() : bool
    {
        return $this->isTagType(VersionParser::TAG_TYPE_BETA);
    }

    public function isAlpha() : bool
    {
        return $this->isTagType(VersionParser::TAG_TYPE_ALPHA);
    }

    public function isPatch() : bool
    {
        return $this->isTagType(VersionParser::TAG_TYPE_PATCH);
    }

    public function isDev() : bool
    {
        return $this->isTagType(VersionParser::TAG_TYPE_DEV);
    }

    public function isStable() : bool
    {
        return
            $this->isTagType(VersionParser::TAG_TYPE_NONE)
            ||
            $this->isTagType(VersionParser::TAG_TYPE_STABLE);
    }

    public function isTagType(string $type) : bool
    {
        return $this->getTagType() === $type || $this->getTagName() === $type;
    }

    public function toArray() : array
    {
        return array(
            'tagName' => $this->getTagName(),
            'tagType' => $this->getTagType(),
            'number' => $this->getTagName(),
            'branch' => $this->getBranchName(),
            'weight' => $this->getWeight(),
            'normalized' => $this->getNormalized()
        );
    }
}