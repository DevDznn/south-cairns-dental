# WordPress theme (developer notes)

- `south-cairns-dental/`: the theme. See its README for installing and editing.
- `south-cairns-dental-v<version>.zip`: the installable theme (upload in *Appearance › Themes › Add New*). The highest version is the latest; earlier ZIPs are kept as backups. `south-cairns-dental.zip` is the last build before versioned names (1.0.0).
- `tools/build-content.php`: regenerates the theme's starter content from the static site.
- `tools/package.php`: builds the ZIP for the Version in `south-cairns-dental/style.css`.

## Releasing a new theme version

1. Raise `Version` in `south-cairns-dental/style.css` and `SCD_THEME_VERSION` in `south-cairns-dental/functions.php` to the same number.
2. Run `php wordpress-theme/tools/package.php`. It writes `south-cairns-dental-v<version>.zip` and won't overwrite an existing version.

Stylesheet and script URLs carry the theme version and each file's modification time, so a site picks up the new CSS and JS after the ZIP is installed, even behind a page cache or CDN (purge any cached pages once, as their HTML holds the old URLs).

This folder is excluded from the static site's Vercel deployment (`.vercelignore`).

## Regenerating the starter content

The importer's content (`south-cairns-dental/inc/import/content/site.json`) is generated from the built static pages in the repository root and `_build/*.json`. After changing the static site's content and rebuilding it, run these from the repository root:

```
php wordpress-theme/tools/build-content.php
php wordpress-theme/tools/package.php
```

The generator converts each section of each page into its South Cairns Dental Elementor widget, with that section's text, images and links as widget settings. It reports any section it can't convert. Images become `scd-asset:` references and internal links become `scd-page:` references, which the importer resolves on the WordPress site.

Content already imported into a WordPress site is not affected. Edit it in Elementor.
