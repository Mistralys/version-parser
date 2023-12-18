### v2.1.0 - Parser improvements
- Allowed more separator characters for the tag number ([#2](https://github.com/Mistralys/version-parser/issues/2))
- Improved the branch and tag detection.
- More flexible and lenient parsing.
- Added the `VersionTag` class to hold tag and branch information.
- Added `getTagInfo()` to retrieve the `VersionTag` object.
- Added `isStable()`, `isDev()` and `isPatch()`.
- The `stable` tag type is now recognized as well if present.
- Added short tag name parameter to `registerTagType()`.
- Spaces are now supported as separator characters.
- More special characters are now filtered out.
- Added `toArray()`, mostly for debug reasons.
- Split the parser into several specialized subclasses.
- Deprecated `setUppercase()` - use `setTagUppercase()` instead.
- Deprecated `getTagWeights()` - use the `TagWeights` class instead.

### v2.0.0 - PHP 7.4 update
- Increased minimum requirement to PHP 7.4.
- Added the possibility to register custom tag types.
- Added `setSeparatorChar()` to change the separator character.
- Added `setUppercase()` to convert version tags to uppercase.
- Added support for the `snapshot` version tag type.
- Added `isSnapshot()`.
- Using strict types in all classes.
- Set up PHPStan analysis, clean up to level 9.

### v1.0.1 - Minor improvements
- Improved build number generation.
- Added comparison methods `isHigherThan()`, `isLowerThan()`.
- Added the `changelog.md` file.

### v1.0.0 - Initial feature-set release
- Initial features.
