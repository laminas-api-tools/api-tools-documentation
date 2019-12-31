# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.1.1 - 2016-07-13

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zfcampus/zf-apigility-documentation#39](https://github.com/zfcampus/zf-apigility-documentation/pull/39) updates
  the component to properly display information about laminas-inputfilter
  Collections when displaying operation validation information.
- [zfcampus/zf-apigility-documentation#40](https://github.com/zfcampus/zf-apigility-documentation/pull/40) updates
  the Operations view script to:
  - display HTTP method-specific fields first, if present.
  - display general fields only if they exist (the fix prevents an empty row
    displaying).
  - insert a closing `</span>` tag within the table data cell containing the
    required flag.
- [zfcampus/zf-apigility-documentation#41](https://github.com/zfcampus/zf-apigility-documentation/pull/41) updates
  the `ApiFactory` to ensure that if an entity has no collection associated with
  it, documentation will not attempt to retrieve the fields.
