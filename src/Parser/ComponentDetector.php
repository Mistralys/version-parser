<?php
/**
 * @package VersionParser
 * @see \Mistralys\VersionParser\Parser\ComponentDetector
 */

declare(strict_types=1);

namespace Mistralys\VersionParser\Parser;

use Mistralys\VersionParser\VersionParser;
use Mistralys\VersionParser\VersionTag;

/**
 * Used by the version parser to detect the components
 * of the tag part of a version string, which may contain
 * a tag type (alpha, beta, etc.) and a branch name.
 *
 * @package VersionParser
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ComponentDetector
{
    private string $tagList = '';
    private ?VersionTag $tag = null;
    private VersionParser $version;
    private string $tagParts;

    public function __construct(VersionParser $version, string $tagParts)
    {
        $this->tagParts = $tagParts;
        $this->version = $version;
    }

    public function detectTag() : ?VersionTag
    {
        $this->tagList = implode('|', array_keys(TagWeights::getWeights()));

        if ($this->parseTagSimple()) {
            return $this->tag;
        }

        if ($this->parseBranchAppend()) {
            return $this->tag;
        }

        if ($this->parseBranchPrepend()) {
            return $this->tag;
        }

        if($this->parseTagAnywhere()) {
            return $this->tag;
        }

        $this->parseBranchOnly();

        return $this->tag;
    }

    private function parseBranchOnly() : void
    {
        preg_match(
            '/\A([a-z0-9-]+)\Z/i',
            $this->tagParts,
            $matches
        );

        if(empty($matches[1])) {
            return;
        }

        $this->tag = new VersionTag(
            $this->version,
            '',
            0,
            $this->detectOriginalBranch($matches[1])
        );
    }

    private function parseBranchPrepend() : bool
    {
        preg_match(
            '/\A([a-z0-9-]+)-('.$this->tagList.')-?([0-9]+)\Z|\A([a-z0-9-]+)-('.$this->tagList.')\Z/i',
            $this->tagParts,
            $matches
        );

        if(empty($matches[0])) {
            return false;
        }

        $matches = $this->nullifyEmpty($matches);
        $tag = $matches[2] ?? $matches[5] ?? '';
        $number = $matches[3] ?? 0;
        $branch = $this->detectOriginalBranch($matches[1] ?? $matches[4] ?? '');

        $this->tag = new VersionTag(
            $this->version,
            $tag,
            (int)$number,
            $branch
        );

        return true;
    }

    private function parseTagSimple() : bool
    {
        return $this->parseTagSolo();
    }

    private function parseTagAnywhere() : bool
    {
        return $this->parseTagSolo(true);
    }

    private function parseTagSolo(bool $anywhere=false) : bool
    {
        if(!$anywhere)
        {
            preg_match(
                '/\A(' . $this->tagList . ')-?([0-9]+)\Z|\A(' . $this->tagList . ')\Z/i',
                $this->tagParts,
                $matches
            );
        }
        else
        {
            preg_match(
                '/-('.$this->tagList.')-?([0-9]+)-|-('.$this->tagList.')-/i',
                $this->tagParts,
                $matches
            );
        }

        if(empty($matches[0])) {
            return false;
        }

        $matches = $this->nullifyEmpty($matches);
        $tag = $matches[1] ?? $matches[3] ?? '';
        $number = $matches[2] ?? 0;

        $branch = str_replace((string)$matches[0], '-', $this->tagParts);

        while(strpos($branch, '--') !== false) {
            $branch = str_replace('--', '-', $branch);
        }

        $branch = trim($branch, '-');

        $this->tag = new VersionTag(
            $this->version,
            $tag,
            (int)$number,
            $branch
        );

        return true;
    }

    private function parseBranchAppend() : bool
    {
        preg_match(
            '/\A('.$this->tagList.')-?([0-9]+)-([a-z0-9-]+)\Z|\A('.$this->tagList.')-([a-z0-9-]+)\Z/i',
            $this->tagParts,
            $matches
        );

        if(empty($matches[0])) {
            return false;
        }

        $matches = $this->nullifyEmpty($matches);
        $tag = $matches[1] ?? $matches[4] ?? '';
        $number = $matches[2] ?? 0;
        $branch = $matches[3] ?? $matches[5] ?? '';

        $this->tag = new VersionTag(
            $this->version,
            $tag,
            (int)$number,
            $this->detectOriginalBranch($branch)
        );

        return true;
    }

    /**
     * @param array<int,string> $values
     * @return array<int,string|null>
     */
    private function nullifyEmpty(array $values) : array
    {
        foreach($values as $idx => $value) {
            if(empty($value)) {
                $values[$idx] = null;
            }
        }

        return $values;
    }

    /**
     * Because of the way the version string is split, the
     * information about how the original branch name was
     * formatted is lost. This uses the normalized name to
     * detect the original branch name.
     *
     * @param string $branch
     * @return string
     */
    private function detectOriginalBranch(string $branch) : string
    {
        if(empty($branch)) {
            return '';
        }

        $branch = str_replace('-', '_SEP_', $branch);

        preg_match('/'.str_replace('_SEP_', '.+', preg_quote($branch)).'/', $this->version->getOriginalString(), $matches);

        if(!empty($matches[0])) {
            return $matches[0];
        }

        return $branch;
    }
}
