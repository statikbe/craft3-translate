# Translate Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).


## 1.2.2 - 2021-05-28
### Fixed
- Fixed an issue where the siteId would not be set when navigating straight to the Translate page after login


## 1.2.1 - 2021-03-22
### Fixed
- Fixed an issue with the regex used to match translations in Twig files


## 1.2.0 - 2021-03-02
### Added
- Basic unit tests to validate certain string parsing scenario's

### Fixed
- Fixed an issue where the wrong site ID was used on initial load of the plugin page

## 1.1.6 - 2020-10-27
### Added
- Fixed PSR-4 autoloading issue.


## 1.1.5 - 2019-03-21
### Added
- Added translatable strings from modules.

## 1.1.4 - 2019-01-25
### Fixed
- Improved loading new translations after safe to account for slower fetch from files. This should fix the "save & see old data" problem.

## 1.1.3 - 2018-12-03
### Fixed
- Sleep for two seconds in the get function

## 1.1.2 - 2018-11-20
### Fixed
- Fixed previous bug that wasn't interely fixed

## 1.1.1 - 2018-11-20
### Fixed
- Fixed bug where new translations didn't immediately show in the translations view

## 1.1.0 - 2018-10-25
### Added
- Added the option to see translations of any status in the same view. So we now have All/Pending/Live.

## 1.0.0 - 2018-08-20
### Added
- Initial release
