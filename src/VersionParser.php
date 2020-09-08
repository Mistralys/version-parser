<?php
/**
 * File containing the {@see VersionParser} class.
 * 
 * @package VersionParser
 * @see VersionParser
 */

declare(strict_types=1);

namespace Mistralys\VersionParser;

/**
 * Version number parsing utility: parses version numbers,
 * and allows retrieving information on the version. 
 * 
 * Supports beta, alpha and release candidate tags, as well
 * as custom tags and combinations thereof. 
 * 
 * Expects version to use the following structure:
 * 
 * <code>Major.Minor.Patch-ReleaseType</code>
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
class VersionParser
{
    const TAG_TYPE_NONE = 'none';
    const TAG_TYPE_BETA = 'beta';
    const TAG_TYPE_ALPHA = 'alpha';
    const TAG_TYPE_RELEASE_CANDIDATE = 'rc';
    
   /**
    * @var string
    */
    private $version;
    
   /**
    * @var float
    */
    private $buildNumber = -1;
    
   /**
    * @var string
    */
    private $tag = '';
    
   /**
    * @var array<int,int>
    */
    private $parts = array();
    
   /**
    * @var string
    */
    private $tagType = self::TAG_TYPE_NONE;
    
   /**
    * @var integer
    */
    private $tagNumber = 0;
    
   /**
    * @var string
    */
    private $branchName = '';
    
   /**
    * @var array<string,int>
    */
    private $tagWeights = array(
        self::TAG_TYPE_ALPHA => 6,
        self::TAG_TYPE_BETA => 4,
        self::TAG_TYPE_RELEASE_CANDIDATE => 2,
        self::TAG_TYPE_NONE => 0
    );
    
    private function __construct(string $version)
    {
        $this->version = $version;
        
        $this->parse();
        $this->postParse();
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
        if($this->buildNumber === -1)
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
        return intval($this->buildNumber * 1000000);
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
        if(!$this->hasTag())
        {
            return $this->getVersion();
        }
        
        return $this->getVersion().'-'.$this->getTag();
    }
    
   /**
    * Retrieves only the numeric version, omitting dots 
    * as far as possible (e.g. `1.0.0` => `1`).
    * 
    * @return string
    */
    public function getShortVersion() : string
    {
        $keep = array();
        
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
    * Retrieves the normalized version tag.
    * 
    * @return string
    */
    public function getTag() : string
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
        return !empty($this->tag);
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
    */
    public function getTagType() : string
    {
        return $this->tagType;
    }
    
   /**
    * Retrieves the number of the release tag.
    * 
    * @return int The tag number if present, `1` if no number has been specified, and `0` if the version has no tag.
    */
    public function getTagNumber() : int
    {
        return $this->tagNumber;
    }
    
    public function isBeta() : bool
    {
        return $this->getTagType() === self::TAG_TYPE_BETA;
    }

    public function isAlpha() : bool
    {
        return $this->getTagType() === self::TAG_TYPE_ALPHA;
    }
    
    public function isReleaseCandidate() : bool
    {
        return $this->getTagType() === self::TAG_TYPE_RELEASE_CANDIDATE;
    }
    
   /**
    * Whether a branch name is present in the version.
    * 
    * @return bool
    */
    public function hasBranch() : bool
    {
        return !empty($this->branchName);
    }
    
   /**
    * Retrieves the branch name, if any.
    * 
    * @return string The branch name, or an empty string if none.
    */
    public function getBranchName() : string
    {
        return $this->branchName;
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
    
    private function parse() : void
    {
        $parts = explode('.', $this->extractTag());
        $parts = array_map('trim', $parts);
        
        while(count($parts) < 3)
        {
            $parts[] = 0;
        }
        
        for($i=0; $i < 3; $i++)
        {
            $this->parts[] = intval($parts[$i]);
        }
    }
    
    private function extractTag() : string
    {
        $version = $this->version;
        
        $hyphen = strpos($version, '-');
        
        if($hyphen !== false)
        {
            $tag = substr($version, $hyphen+1);
            $version = substr($version, 0, $hyphen);
            $this->parseTag($tag);
        }

        return $version;
    }
    
    private function postParse() : void
    {
        $this->tag = $this->normalizeTag();
    }
    
    private function normalizeTag() : string
    {
        if($this->tagType === self::TAG_TYPE_NONE)
        {
            return $this->getBranchName();
        }
        
        $tag = $this->tagType;
        
        if($this->tagNumber > 1)
        {
            $tag .= $this->tagNumber;
        }
        
        if($this->hasBranch())
        {
            $tag = $this->getBranchName().'-'.$tag;
        }
        
        return $tag;
    }
    
    private function formatTagNumber() : string
    {
        $weight = $this->tagWeights[$this->getTagType()];
        
        if($weight > 0)
        {
            return '.'.sprintf('%0'.$weight.'d', $this->tagNumber);
        }
        
        return '';
    }
    
    private function parseTag(string $tag) : void
    {
        $parts = explode('-', $tag);
        
        foreach($parts as $part)
        {
            $this->parseTagPart($part);
        }
        
        if($this->tagNumber === 0)
        {
            $this->tagNumber = 1;
        }
        
        if($this->tagType === self::TAG_TYPE_NONE)
        {
            $this->tagNumber = 0;
        }
    }
    
    private function parseTagPart(string $part) : void
    {
        if(is_numeric($part))
        {
            $this->tagNumber = intval($part);
            return;
        }
        
        $types = array_keys($this->tagWeights);
        $type = '';
        $lower = strtolower($part);
        
        foreach($types as $tagType)
        {
            if(strstr($lower, $tagType))
            {
                $type = $tagType;
                $part = str_replace($tagType, '', $lower);
            }
        }
        
        if(empty($type))
        {
            if(!empty($part))
            {
                $this->branchName = $part;
            }
            
            return;
        }
        
        $this->tagType = $type;
        
        if(is_numeric($part))
        {
            $this->tagNumber = intval($part);
        }
    }
    
    private function calculateBuildNumber() : void
    {
        $parts = array(
            $this->getMajorVersion(),
            sprintf('%03d', $this->getMinorVersion()),
            sprintf('%03d', $this->getPatchVersion())
        );
        
        $number = implode('', $parts);
        
        if($this->tagNumber > 0)
        {
            $number .= $this->formatTagNumber();
        }
        
        $this->buildNumber = floatval($number);
    }
}

