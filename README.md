# Version string parser for PHP

PHP utility used to parse application version strings, and retrieve information on the version, as well as convert it to a numeric build number (float or integer). Supports release tags like alpha, beta and reelease candidate, as well as custom branch names.

## Supported version strings

The parser expects versions to be in the following format:

`MajorVersion.MinorVersion.PatchVersion-BranchName-ReleaseTagNumber`

This allows the use of a wide range of version strings. Some examples:

- `1`
- `1.1`
- `1.1.5`
- `1.145.147`
- `1.1.5-rc1`
- `1.1.5-beta`
- `1.1.5-beta2`
- `1.1.5-beta.42`
- `1.1.5-BranchName`
- `1.1.5-BranchName-alpha2`
- `1.1.5 BranchName A2` _Spaces are allowed_
- `1.1.5 "Branch name"` _Quotes are stripped_
- `1.1.5 (BranchName) / Alpha 2` _Special characters are filtered out_
- `1.1.5-DEV Branch Name` _Branch names after the tag type_

Most special characters are filtered out, which means that it is 
very lenient in what is passed to it. After the version number,
anything that is not a tag qualifier (`beta`, `alpha`, etc.) is
considered the branch name.

## Installation

Simply require the package with composer.

Via command line:

```shell
composer require mistralys/version-parser
```

Via composer.json:

```json
{
    "require": {
      "mistralys/version-parser": "dev-master"
    }
}
```

## Usage

### Getting individual version numbers

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1.5.2');

$major = $version->getMajorVersion(); // 1
$minor = $version->getMinorVersion(); // 5
$patch = $version->getPatchVersion(); // 2
```

### Getting a version without tag

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1.5.2-RC3');

$number = $version->getVersion(); // 1.5.2
```

The version is normalized to show all three levels, even if they were not specified.

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1');

$number = $version->getVersion(); // 1.0.0
```

### Getting a short version

The method `getShortVersion()` retrieves a version string with the minimum possible levels.

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1.0.0');

$number = $version->getVersion(); // 1
```

### Getting the full version, normalized

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1-BETA');

$normalized = $version->getTagVersion(); // 1.0.0-beta
```

### Checking the tag type

To check the release type, the shorthand methods `isBeta()`, `isAlpha()`, 
etc. can be used. See "Supported release tags" for details.

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1.5.2-beta');

$isBeta = $version->isBeta(); // true
```

Alternatively, it is possible to check the tag type manually.

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1.5.2-beta5');

if($version->getTagType() === VersionParser::TAG_TYPE_BETA)
{
	// is a beta version
}
```

The tag info object that can be accessed with the `getTagInfo()`
method goes more in depth. 

For example, the method `getTagName()` will return the tag type 
exactly as used in the version string, whereas `getTagType()` will 
only return the long type variant (e.g. `beta` instead of `b`):

```php
use Mistralys\VersionParser\VersionParser;

$tag = VersionParser::create('1.5.2-B2')->getTagInfo();

if($tag !== null)
{
    echo $tag->getTagName(); // b
    echo $tag->getTagType(); // beta
}
```

### Getting the tag number

When no number is added to the tag, it is assumed that it is the tag #1.

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1.5.2-beta');

$betaVersion = $version->getTagNumber(); // 1 (implicit)
```

With a number added:

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1.5.2-beta5');

$betaVersion = $version->getTagNumber(); // 5
```

### Getting the branch name

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1.5.2-Foobar');

$hasBranch = $version->hasBranch(); // true
$branchName = $version->getBranchName(); // Foobar
```

This also works in combination with a release tag:

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1.5.2-Foobar-RC1');

$hasBranch = $version->hasBranch(); // true
$branchName = $version->getBranchName(); // Foobar
```

Branch names may contain special characters. Quotes are filtered out:

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1.5.2 "Foobar/42"');

$hasBranch = $version->hasBranch(); // true
$branchName = $version->getBranchName(); // Foobar/42
```

### Setting the separator character

By default, the branch name and tag are separated with hyphens (`-`) 
when normalizing the version string. This can be adjusted to any character:

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1.5.2-BranchName-alpha5');

echo $version
    ->setSeparatorChar('_')
    ->getTagVersion();
```

Will output:

```
1.5.2_BranchName_alpha5
```

### Converting tag types to uppercase

By default, tag types are converted to lowercase when normalizing the
version string. They can be switched to uppercase instead:

```php
use Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1.5.2-BranchName-alpha5');

echo $version
    ->setTagUppercase()
    ->getTagVersion();
```

Will output:

```
1.5.2-BranchName-ALPHA5
```

## Supported release tags

The parser will handle the following tags automatically, and assign
them a build number value:

- `dev` or `snapshot` - Development release, weight: `8`
- `alpha` - Alpha release, weight: `6`
- `beta` - Beta release, weight: `4`
- `rc` - Release candidate, weight: `2`
- `patch` - Patch/bugfix release, weight: `1`

This means that comparing the same version numbers with different 
release tags will work. For example, `1.4-beta` is considered a higher
version than `1.4-alpha`.

### Numbering tags

Also supported is numbering tagged versions:

- `1.0-alpha` - Implied `alpha1`
- `1.0-alpha2` - Alpha `2`

### Adding custom tags

If you use other tag types in your application's version strings,
they can be added so the parser recognizes them:

```php
use Mistralys\VersionParser\VersionParser;

// The third parameter is the short variant of the tag type.
VersionParser::registerTagType('foobar', 5, 'f');

$version = VersionParser::create('1.0.5-foobar2');
$short = VersionParser::create('1.0.5-F2');

echo $version->getTagType(); // foobar
echo $short->getTagType(); // foobar
```

If you mix custom tag types and the standard ones, be careful with
the sorting weight you set for them, so they will be weighted correctly.
If needed, you can change the weight of the default types to make more
room.

This for example resets the weights for some existing tag types, and 
inserts new ones:

```php
use Mistralys\VersionParser\VersionParser;

$weight = 900; // Can be any number
VersionParser::registerTagType(VersionParser::TAG_TYPE_ALPHA, $weight--);
VersionParser::registerTagType(VersionParser::TAG_TYPE_BETA, $weight--);
VersionParser::registerTagType('foobar', $weight--);
VersionParser::registerTagType(VersionParser::TAG_TYPE_RELEASE_CANDIDATE, $weight--);
VersionParser::registerTagType('prefinal', $weight--);
```

> NOTE: The weights are used in the generated build numbers. Changing the
> default tag type weights will change their build numbers as well.

## Build numbers

The version strings are intelligently converted to numbers, to allow
comparisons and sorting. This includes the release tags like `alpha`
or `beta`, which are converted as well. The result is a build number 
which can be either a floating point number, or an integer.

  > NOTE: These numbers are not meant to be human-readable. Their sole
    purpose is to recognize version numbers programmatically.
   
There are two methods related to this:

```php
use \Mistralys\VersionParser\VersionParser;

$version = VersionParser::create('1.0-alpha2');

$float = $version->getBuildNumber();
$int = $version->getBuildNumberInt();
```

## Sorting versions

The best way to sort versions is to use the build numbers, which allow
numeric comparisons. Here's an example that sorts them in ascending order:

```php
use \Mistralys\VersionParser\VersionParser;

$versions = array(
    VersionParser::create('1.1'),
    VersionParser::create('2'),
    VersionParser::create('1.5.9'),
    VersionParser::create('1.5.9-beta'),
    VersionParser::create('2.0.0-alpha')
);

usort($versions, static function (VersionParser $a, VersionParser $b) : int {
    return $a->getBuildNumberInt() - $b->getBuildNumberInt();
});
```

This will sort the list the following way:

1. `1.1.0`
2. `1.5.9-beta`
3. `1.5.9`
4. `2.0.0-alpha`
5. `2.0.0`
