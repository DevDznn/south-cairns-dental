<?php
/**
 * Build wordpress-theme/south-cairns-dental.zip with the theme folder at the root of the archive,
 * as WordPress expects when uploading a theme.
 *
 * Usage (from the repository root): php wordpress-theme/tools/package.php
 */

// phpcs:disable -- developer CLI tool, not part of the theme.

if ( PHP_SAPI !== 'cli' ) {
	exit;
}

$base = dirname( __DIR__ );
$dir  = $base . '/south-cairns-dental';
$zip  = $base . '/south-cairns-dental.zip';
$skip = '#(^|/)(\.git|\.DS_Store|Thumbs\.db|node_modules|\.idea|\.vscode)(/|$)|\.log$|~$#';

if ( file_exists( $zip ) ) {
	unlink( $zip );
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
