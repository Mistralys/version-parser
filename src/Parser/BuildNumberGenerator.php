<?php

declare(strict_types=1);

namespace Mistralys\VersionParser\Parser;

use Mistralys\VersionParser\VersionTag;

class BuildNumberGenerator
{
    private float $buildNumber;

    public function __construct(int $major, int $minor, int $patch, ?VersionTag $tag)
    {
        $parts = array(
            $major,
            sprintf('%03d', $minor),
            sprintf('%03d', $patch)
        );

        $number = (float)implode('', $parts);

        if($tag !== null) {
            $number -= (float)$this->formatTagNumber($tag->getWeight(), $tag->getNumber());
        }

        $this->buildNumber = $number;
    }

    public function getBuildNumber() : float
    {
        return $this->buildNumber;
    }

    private function formatTagNumber(int $weight, int $number) : string
    {
        if($weight === 0) {
            return '';
        }

        $positions = 2 * 3;

        $number = sprintf('%0'.$weight.'d', $number);
        $number = str_pad($number, $positions, '0', STR_PAD_RIGHT);

        $number = (int)str_repeat('9', $positions) - (int)$number;
        return '.'.$number;
    }
}
