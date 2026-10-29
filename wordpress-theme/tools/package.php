<?php
/**
 * Build wordpress-theme/south-cairns-dental-v<version>.zip with the theme folder at the root of the
 * archive, as WordPress expects when uploading a theme. The version is the theme's style.css
 * Version header, so raise it for each release. Earlier ZIPs are kept as backups: the script
 * refuses to overwrite an existing version.
 *
 * Usage (from the repository root): php wordpress-theme/tools/package.php
 */

// phpcs:disable -- developer CLI tool, not part of the theme.

if ( PHP_SAPI !== 'cli' ) {
	exit;
}

$base = dirname( __DIR__ );
$dir  = $base . '/south-cairns-dental';
$skip = '#(^|/)(\.git|\.DS_Store|Thumbs\.db|node_modules|\.idea|\.vscode)(/|$)|\.log$|~$#';

if ( ! preg_match( '/^Version:\s*(\S+)/m', file_get_contents( $dir . '/style.css' ), $m ) ) {
	fwrite( STDERR, "No Version header in style.css\n" );
	exit( 1 );
}
$zip = $base . '/south-cairns-dental-v' . $m[1] . '.zip';
if ( file_exists( $zip ) ) {
	fwrite( STDERR, "$zip already exists. Raise the Version in style.css (and SCD_THEME_VERSION in functions.php) for a new release.\n" );
	exit( 1 );
}
$z = new ZipArchive();
if ( true !== $z->open( $zip, ZipArchive::CREATE ) ) {
	fwrite( STDERR, "Cannot create $zip\n" );
	exit( 1 );
}
$it    = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
$count = 0;
$files = array();
foreach ( $it as $file ) {
	$rel = str_replace( '\\', '/', substr( $file->getPathname(), strlen( $dir ) + 1 ) );
	if ( preg_match( $skip, $rel ) ) {
		continue;
	}
	$files[] = $rel;
}
sort( $files );
foreach ( $files as $rel ) {
	$z->addFile( $dir . '/' . $rel, 'south-cairns-dental/' . $rel );
	++$count;
}
$z->close();
printf( "Wrote %s: %d files, %.1f MB\n", $zip, $count, filesize( $zip ) / 1048576 );
