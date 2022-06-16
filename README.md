# WordPress Packager

> Helper to generate WordPress Composer packages

[![Test](https://github.com/roots/wordpress-packager/actions/workflows/test.yml/badge.svg)](https://github.com/roots/wordpress-packager/actions/workflows/test.yml)

## About

This package helps to generate Composer packages for any WordPress release.

## Usage

```bash
$ vendor/bin/wordpress-packager [--source SOURCE] [-t|--type TYPE] [-u|--unstable] [--] <remote> <package>
```

### Repository remote `<remote>`

Required.

A valid git repository remote.  
Eg. `https://github.com/org/project.git`

### Package name `<package>`

Required.

A valid Composer package name.  
Eg. `wordpress-package`

### Release source `--source SOURCE`

Optional, default `WPDotOrgAPI`.

Must be a PHP class implementing [`Roots\WordPressPackager\ReleaseSources\SourceInterface`](https://github.com/roots/wordpress-packager/blob/main/src/ReleaseSources/SourceInterface.php).  
Provides the implementation of data generation for packages.

### Release type `-t|--type TYPE`

Optional, default `full`.

Different release types are available as WordPress core deliveries.  
The list of themes and plugins bundled differs between release types.

Type|Official|Themes|Plugins|Beta & RC
--|:--:|--|--|:--:
`full`|✅|[3 latest official](https://wordpress.org/themes/author/wordpressdotorg/)|[Akismet](https://wordpress.org/plugins/akismet/), [Hello Dolly](https://wordpress.org/plugins/hello-dolly/)|✔️
`new-bundled`|✅*|[3 latest official](https://wordpress.org/themes/author/wordpressdotorg/)|none|❌
`no-content`|✅*|none|none|❌

\* Although they are not extensively documented, these builds are made available by WordPress.org as regular builds.

### Unstable releases `--unstable`

Optional.

If set, the available unstable releases (beta & release candidates) will be added as well.
