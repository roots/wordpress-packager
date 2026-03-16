# WordPress Packager

> Helper to generate WordPress Composer packages

[![Test](https://img.shields.io/github/actions/workflow/status/roots/wordpress-packager/test.yml?logo=github&label=CI&style=flat-square)](https://github.com/roots/wordpress-packager/actions/workflows/test.yml)
[![Follow Roots](https://img.shields.io/badge/follow%20@rootswp-1da1f2?logo=twitter&logoColor=ffffff&message=&style=flat-square)](https://twitter.com/rootswp)
[![Sponsor Roots](https://img.shields.io/badge/sponsor%20roots-525ddc?logo=github&style=flat-square&logoColor=ffffff&message=)](https://github.com/sponsors/roots)

## About

This package helps to generate Composer packages for any WordPress release.

## Support us

We're dedicated to pushing modern WordPress development forward through our open source projects, and we need your support to keep building. You can support our work by purchasing [Radicle](https://roots.io/radicle/), our recommended WordPress stack, or by [sponsoring us on GitHub](https://github.com/sponsors/roots). Every contribution directly helps us create better tools for the WordPress ecosystem.

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

## Related

- [WP Composer](https://wp-composer.com/) — All WordPress.org plugins and themes as a Composer repository

## Community

Keep track of development and community news.

- Join us on Discord by [sponsoring us on GitHub](https://github.com/sponsors/roots)
- Join us on [Roots Discourse](https://discourse.roots.io/)
- Follow [@rootswp on Twitter](https://twitter.com/rootswp)
- Follow the [Roots Blog](https://roots.io/blog/)
- Subscribe to the [Roots Newsletter](https://roots.io/subscribe/)
