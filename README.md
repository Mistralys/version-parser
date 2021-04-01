# Version string parser for PHP

PHP utility used to parse application version strings, and retrieve information on the version, as well as convert it to a numeric build number (float or integer). Supports release tags like alpha, beta and reelease candidate, as well as custom branch names.

## Supported version strings

The parser expects versions to be in the following format:

`MajorVersion.MinorVersion.PatchVersion-BranchName-ReleaseTagNumber`

This allows the use of a wide range of version strings. Some examples:

  - 1
  - 1.1
  - 1.1.5
  - 1.145.147
  - 1.1.5-rc1
  - 1.1.5-beta
  - 1.1.5-beta2
  - 1.1.5-BranchName
  - 1.1.5-BranchName-alpha2

  > NOTE: Hyphens are typically used as separators, but underscores
    are acceptable as well. Spaces are not supported.

## Installation

Simply require the package with composer.

Via command line:

```
composer require mistralys/version-parser
```

Via composer.json:

```json
"require": {
   "mistralys/version-parser": "dev-master"
}
```

## Usage

### Getting individual version numbers

```
$version = VersionParser::create('1.5.2');

$major = $version->getMajorVersion(); // 1
$minor = $version->getMinorVersion(); // 5
$patch = $version->getPatchVersion(); // 2
```

### Getting a version without tag

```
$version = VersionParser::create('1.5.2-RC3');

$number = $version->getVersion(); // 1.5.2
```

The version is normalized to show all three levels, even if they were not specified.

```
$version = VersionParser::create('1');

$number = $version->getVersion(); // 1.0.0
```

### Getting a short version

The method `getShortVersion()` retrieves a version string with the minimum possible levels.

```
$version = VersionParser::create('1.0.0');

$number = $version->getVersion(); // 1
```

### Getting the full version, normalized

```
$version = VersionParser::create('1-BETA');

$normalized = $version->getTagVersion(); // 1.0.0-beta
```

### Checking the tag type

To check the release type, the shorthand methods `isBeta()`, `isAlpha()` and `isReleaseCandidate()` can be used.
See "Supported release tags" for details.

```
$version = VersionParser::create('1.5.2-beta');

$isBeta = $version->isBeta(); // true
```

Alternatively, it is possible to check the tag type manually.

```
$version = VersionParser::create('1.5.2-beta5');

if($version->getTagType() === VersionParser::TAG_TYPE_BETA)
{
	// is a beta version
}
```

### Getting the tag number

When no number is added to the tag, it is assumed that it is the tag #1.

```
$version = VersionParser::create('1.5.2-beta');

$betaVersion = $version->getTagNumber(); // 1 (implicit)
```

With a number added:

```
$version = VersionParser::create('1.5.2-beta5');

$betaVersion = $version->getTagNumber(); // 5
```

### Getting the branch name

```
$version = VersionParser::create('1.5.2-Foobar');

$hasBranch = $version->hasBranch(); // true
$branchName = $version->getBranchName(); // Foobar
```

This also works in combination with a release tag:

```
$version = VersionParser::create('1.5.2-Foobar-RC1');

$hasBranch = $version->hasBranch(); // true
$branchName = $version->getBranchName(); // Foobar
```

Branch names may contain numbers, but no hyphens.

```
$version = VersionParser::create('1.5.2-Foobar45');

$hasBranch = $version->hasBranch(); // true
$branchName = $version->getBranchName(); // Foobar45
```

## Supported release tags

The parser will handle the following tags automatically, and assign
them a build number value:

- `alpha` - Alpha release
- `beta` - Beta release
- `rc` - Release candidate

This means that comparing the same version numbers with different 
release tags will work. For example, `1.4-beta` is considered a higher
version than `1.4-alpha`.

### Numbering tags

Also supported is numbering tagged versions:

- `1.0-alpha` - Implied `alpha1`
- `1.0-alpha2` - Alpha `2`

## Build numbers

The version strings are intelligently converted to numbers, to allow
comparisons and sorting. This includes the release tags like `alpha`
or `beta`, which are converted as well. The result is a build number 
which can be either a floating point number, or an integer.

  > NOTE: These numbers are not meant to be human readable. Their sole
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

usort($versions, function (VersionParser $a, VersionParser $b) {
    return $a->getBuildNumberInt() - $b->getBuildNumberInt();
});
```

This will sort the list the following way:

1. `1.1.0`
2. `1.5.9-beta`
3. `1.5.9`
4. `2.0.0-alpha`
5. `2.0.0`