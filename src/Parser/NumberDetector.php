<?php

declare(strict_types=1);

namespace Mistralys\VersionParser\Parser;

class NumberDetector
{
    private string $version;
    /**
     * @var int[]
     */
    private array $parts = array();
    private string $tagString = '';

    public function __construct(string $version)
    {
        $this->version = $version;

        $this->parse();
    }

    /**
     * @return int[]
     */
    public function getNumbers() : array
    {
        return $this->parts;
    }

    public function getTagString() : string
    {
        return $this->tagString;
    }

    private function parse() : void
    {
        $parts = $this->splitVersionString($this->version);

        foreach($parts as $idx => $part)
        {
            // Continue only as long as we're dealing with numbers
            if(is_numeric($part)) {
                $this->parts[] = intval($part);
                unset($parts[$idx]);
            } else {
                break;
            }
        }

        while(count($this->parts) < 3) {
            $this->parts[] = 0;
        }

        if(count($this->parts) > 3) {
            $this->parts = array_slice($this->parts, 0, 3);
        }

        $this->tagString = trim(implode('-', $parts), '-');
    }

    /**
     * Splits the version string into an indexed array of parts,
     * delimited by dots, dashes, underscores, spaces, tabs and
     * newlines.
     *
     * @param string $version
     * @return string[]
     */
    public function splitVersionString(string $version) : array
    {
        $version = str_replace(SeparatorChars::CHARS, '.', $version);

        while(strpos($version, '..') !== false) {
            $version = str_replace('..', '.', $version);
        }

        return explode('.', $version);
    }
}
