# WordPress theme (developer notes)

- `south-cairns-dental/`: the theme. See its README for installing and editing.
- `south-cairns-dental.zip`: the installable theme (upload in *Appearance › Themes › Add New*).
- `tools/build-content.php`: regenerates the theme's starter content from the static site.
- `tools/package.php`: builds the ZIP.

This folder is excluded from the static site's Vercel deployment (`.vercelignore`).

## Regenerating the starter content

The importer's content (`south-cairns-dental/inc/import/content/site.json`) is generated from the built static pages in the repository root and `_build/*.json`. After changing the static site's content and rebuilding it, run these from the repository root:

```
php wordpress-theme/tools/build-content.php
php wordpress-theme/tools/package.php
```

The generator converts each section of each page into its South Cairns Dental Elementor widget, with that section's text, images and links as widget settings. It reports any section it can't convert. Images become `scd-asset:` references and internal links become `scd-page:` references, which the importer resolves on the WordPress site.

Content already imported into a WordPress site is not affected. Edit it in Elementor.
