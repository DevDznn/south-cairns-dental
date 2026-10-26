<?php
/**
 * Appearance > South Cairns setup: imports the website's pages (as Elementor layouts), the blog
 * post, images (into the Media Library), menus, logo and front page from inc/import/content/site.json,
 * which is generated from the static site by wordpress-theme/tools/build-content.php.
 *
 * Safe to run more than once: pages and images are matched by slug and source file, existing pages
 * that the importer didn't create are left alone unless "replace" is ticked, and nothing is deleted.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin page.
 */
function scd_setup_menu() {
	add_theme_page( __( 'South Cairns setup', 'south-cairns-dental' ), __( 'South Cairns setup', 'south-cairns-dental' ), 'manage_options', 'scd-setup', 'scd_setup_page' );
}
add_action( 'admin_menu', 'scd_setup_menu' );

/**
 * Point to the setup page after activating the theme.
 */
function scd_setup_activation_notice() {
	global $pagenow;
	if ( 'themes.php' !== $pagenow || ! isset( $_GET['activated'] ) || ! current_user_can( 'manage_options' ) || get_option( 'scd_imported' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
		return;
	}
	printf(
		'<div class="notice notice-success"><p>%s <a href="%s">%s</a></p></div>',
		esc_html__( 'South Cairns Dental theme activated.', 'south-cairns-dental' ),
		esc_url( admin_url( 'themes.php?page=scd-setup' ) ),
		esc_html__( 'Import the website’s pages, menus and images', 'south-cairns-dental' )
	);
}
add_action( 'admin_notices', 'scd_setup_activation_notice' );

/**
 * Render the setup page and handle the import.
 */
function scd_setup_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do this.', 'south-cairns-dental' ) );
	}
	$log = array();
	if ( isset( $_POST['scd_import'] ) ) {
		check_admin_referer( 'scd_import' );
		$opts = array(
			'replace'    => ! empty( $_POST['scd_replace'] ),
			'menus'      => ! empty( $_POST['scd_menus'] ),
			'front'      => ! empty( $_POST['scd_front'] ),
			'permalinks' => ! empty( $_POST['scd_permalinks'] ),
			'kit'        => ! empty( $_POST['scd_kit'] ),
		);
		$log  = scd_run_import( $opts );
	}
	$elementor = did_action( 'elementor/loaded' );
	$thinkrank = defined( 'THINKRANK_VERSION' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'South Cairns Dental setup', 'south-cairns-dental' ); ?></h1>
		<?php if ( $log ) : ?>
			<div class="notice notice-info"><ul style="list-style:disc;padding-left:1.5em">
				<?php foreach ( $log as $line ) : ?>
					<li><?php echo esc_html( $line ); ?></li>
				<?php endforeach; ?>
			</ul></div>
		<?php endif; ?>
		<h2><?php esc_html_e( 'Plugins', 'south-cairns-dental' ); ?></h2>
		<ul>
			<li><?php echo $elementor ? '&#10003; ' : '&#10007; '; ?><strong>Elementor</strong> — <?php $elementor ? esc_html_e( 'active. Pages are edited with Edit with Elementor.', 'south-cairns-dental' ) : esc_html_e( 'required. Install and activate Elementor (free) before importing, so the pages can be displayed and edited.', 'south-cairns-dental' ); ?></li>
			<li><?php echo $thinkrank ? '&#10003; ' : '&#10007; '; ?><strong>ThinkRank</strong> — <?php $thinkrank ? esc_html_e( 'active. SEO titles and descriptions from the old site are copied into each page’s ThinkRank fields (only where empty).', 'south-cairns-dental' ) : esc_html_e( 'recommended for SEO. If it is active when you import, each page’s SEO title and description are filled in for you.', 'south-cairns-dental' ); ?></li>
		</ul>
		<h2><?php esc_html_e( 'Import the website', 'south-cairns-dental' ); ?></h2>
		<p><?php esc_html_e( 'Creates the homepage, 8 information pages, 14 treatment pages, the blog page and article, images in the Media Library, the main and footer menus and the logo. Pages are built with the South Cairns Dental Elementor widgets, so every heading, paragraph, image, button and card can be edited in Elementor.', 'south-cairns-dental' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'scd_import' ); ?>
			<p><label><input type="checkbox" name="scd_menus" value="1" checked> <?php esc_html_e( 'Create the main and footer menus and assign them', 'south-cairns-dental' ); ?></label></p>
			<p><label><input type="checkbox" name="scd_front" value="1" checked> <?php esc_html_e( 'Use the imported Home page as the homepage and Blog as the posts page (Settings > Reading)', 'south-cairns-dental' ); ?></label></p>
			<p><label><input type="checkbox" name="scd_permalinks" value="1" checked> <?php esc_html_e( 'Use page-name links, e.g. /fillings/ (Settings > Permalinks)', 'south-cairns-dental' ); ?></label></p>
			<p><label><input type="checkbox" name="scd_kit" value="1" checked> <?php esc_html_e( 'Set Elementor’s global colours and fonts to the South Cairns Dental brand, and stop Elementor adding its default fonts and colours', 'south-cairns-dental' ); ?></label></p>
			<p><label><input type="checkbox" name="scd_replace" value="1"> <?php esc_html_e( 'Replace the content of pages that already exist with these addresses (normally left alone)', 'south-cairns-dental' ); ?></label></p>
			<?php submit_button( __( 'Import website content', 'south-cairns-dental' ), 'primary', 'scd_import', false, $elementor ? array() : array( 'disabled' => 'disabled' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Run the import.
 *
 * @param array $opts Options.
 * @return string[] Log lines.
 */
function scd_run_import( $opts ) {
	$file = SCD_THEME_DIR . '/inc/import/content/site.json';
	$site = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	if ( ! $site ) {
		return array( __( 'The content file is missing or unreadable.', 'south-cairns-dental' ) );
	}
	if ( ! did_action( 'elementor/loaded' ) ) {
		return array( __( 'Elementor is not active. Activate Elementor and run the import again.', 'south-cairns-dental' ) );
	}
	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 300 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- one-off import of ~30 images.
	}
	$log      = array();
	$importer = new SCD_Importer( $site['alts'] ?? array() );

	if ( $opts['permalinks'] && '' === get_option( 'permalink_structure' ) ) {
		// Through $wp_rewrite so permalinks generated later in this request (links in the
		// imported pages and menus) already use the new structure.
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		$log[] = __( 'Permalinks set to page names.', 'south-cairns-dental' );
	}

	// 1. Pages (created first so every link and related card can be resolved).
	$ids     = array();
	$created = 0;
	$skipped = array();
	foreach ( $site['pages'] as $page ) {
		$res = $importer->ensure_page( $page['slug'], $page['title'], $page['menu_order'], $opts['replace'] );
		if ( $res['id'] ) {
			$ids[ $page['slug'] ] = $res['id'];
			$created             += $res['new'] ? 1 : 0;
			if ( $res['skip'] ) {
				$skipped[] = $page['slug'];
			}
		}
	}
	$blog_res            = $importer->ensure_page( $site['blog']['slug'], $site['blog']['title'], 0, $opts['replace'] );
	$ids['blog']         = $blog_res['id'];
	$importer->page_ids  = $ids;

	// 2. Blog posts.
	foreach ( $site['posts'] as $post ) {
		$pid = $importer->ensure_post( $post );
		if ( $pid ) {
			$importer->post_ids[ $post['slug'] ] = $pid;
		}
	}

	// 3. Page content, meta and SEO fields.
	foreach ( $site['pages'] as $page ) {
		$id = $ids[ $page['slug'] ] ?? 0;
		if ( ! $id || in_array( $page['slug'], $skipped, true ) ) {
			continue;
		}
		$importer->fill_page( $id, $page );
	}
	if ( $ids['blog'] && ! $blog_res['skip'] ) {
		wp_update_post(
			array(
				'ID'           => $ids['blog'],
				'post_excerpt' => $site['blog']['excerpt'],
				'post_content' => $importer->resolve_html( $site['blog']['content'] ),
			)
		);
		$importer->seo( $ids['blog'], $site['blog']['seo_title'], $site['blog']['seo_description'] );
		update_post_meta( $ids['blog'], '_scd_imported', SCD_THEME_VERSION );
	}
	foreach ( $site['posts'] as $post ) {
		if ( isset( $importer->post_ids[ $post['slug'] ] ) ) {
			$importer->fill_post( $importer->post_ids[ $post['slug'] ], $post );
		}
	}
	/* translators: 1: pages created, 2: total pages */
	$log[] = sprintf( __( 'Pages: %1$d created, %2$d in total.', 'south-cairns-dental' ), $created, count( $ids ) );
	if ( $skipped ) {
		/* translators: %s: page slugs */
		$log[] = sprintf( __( 'Left unchanged because they already existed: %s. Tick "replace" to overwrite them.', 'south-cairns-dental' ), implode( ', ', $skipped ) );
	}
	/* translators: %d: number of images */
	$log[] = sprintf( __( 'Images in the Media Library: %d.', 'south-cairns-dental' ), count( $importer->attachments ) );

	// 4. Reading settings.
	if ( $opts['front'] && ! empty( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
		if ( ! empty( $ids['blog'] ) ) {
			update_option( 'page_for_posts', $ids['blog'] );
		}
		$log[] = __( 'Homepage and posts page set.', 'south-cairns-dental' );
	}

	// 5. Menus.
	if ( $opts['menus'] ) {
		$importer->menus();
		$log[] = __( 'Main and footer menus created and assigned.', 'south-cairns-dental' );
	}

	// 6. Elementor brand settings. Saving the kit also saves its Site Identity (logo), so this
	// runs before the logo is set.
	if ( $opts['kit'] ) {
		$importer->elementor_brand();
		$log[] = __( 'Elementor global colours and fonts set to the brand.', 'south-cairns-dental' );
	}

	// 7. Logo (only when none is set).
	if ( ! get_theme_mod( 'custom_logo' ) ) {
		$logo = $importer->attachment( 'brand/scd-logo.png', 'South Cairns Dental' );
		if ( $logo ) {
			set_theme_mod( 'custom_logo', $logo );
		}
	}
	if ( ! get_theme_mod( 'scd_footer_logo' ) ) {
		$rev = $importer->attachment( 'brand/scd-logo-reversed.png', 'South Cairns Dental' );
		if ( $rev ) {
			set_theme_mod( 'scd_footer_logo', (string) $rev );
		}
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	flush_rewrite_rules();
	update_option( 'scd_imported', SCD_THEME_VERSION );
	$log[] = __( 'Done. Visit the site, then open any page and choose Edit with Elementor.', 'south-cairns-dental' );
	return $log;
}

/**
 * Import helpers.
 */
class SCD_Importer {

	/** @var int[] Imported page IDs by slug. */
	public $page_ids = array();

	/** @var int[] Imported post IDs by slug. */
	public $post_ids = array();

	/** @var int[] Attachment IDs by source path. */
	public $attachments = array();

	/** @var string[] Alt text by source path. */
	protected $alts;

	/**
	 * Constructor.
	 *
	 * @param string[] $alts Alt text by source path.
	 */
	public function __construct( $alts ) {
		$this->alts = $alts;
	}

	/**
	 * Find or create a page.
	 *
	 * @param string $slug    Slug.
	 * @param string $title   Title.
	 * @param int    $order   Menu order.
	 * @param bool   $replace Replace existing pages' content.
	 * @return array{id:int,new:bool,skip:bool}
	 */
	public function ensure_page( $slug, $title, $order, $replace ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing ) {
			$ours = (bool) get_post_meta( $existing->ID, '_scd_imported', true );
			return array( 'id' => $existing->ID, 'new' => false, 'skip' => ! $ours && ! $replace );
		}
		$id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => $title,
				'post_name'   => $slug,
				'menu_order'  => (int) $order,
			),
			true
		);
		return array( 'id' => is_wp_error( $id ) ? 0 : $id, 'new' => ! is_wp_error( $id ), 'skip' => false );
	}

	/**
	 * Fill a page: Elementor layout, excerpt, featured image, treatment details and SEO fields.
	 *
	 * @param int   $id   Page ID.
	 * @param array $page Page data.
	 */
	public function fill_page( $id, $page ) {
		wp_update_post(
			array(
				'ID'           => $id,
				'post_title'   => $page['title'],
				'post_excerpt' => $page['excerpt'],
				'menu_order'   => (int) $page['menu_order'],
			)
		);
		update_post_meta( $id, '_wp_page_template', 'default' );
		update_post_meta( $id, '_scd_imported', SCD_THEME_VERSION );
		if ( $page['thumb'] ) {
			$thumb = $this->attachment( substr( $page['thumb'], strlen( 'scd-asset:' ) ) );
			if ( $thumb ) {
				set_post_thumbnail( $id, $thumb );
			}
		}
		if ( $page['service'] ) {
			update_post_meta( $id, '_scd_service', '1' );
			update_post_meta( $id, '_scd_service_group', $page['service']['group'] );
			update_post_meta( $id, '_scd_service_category', $page['service']['category'] );
			update_post_meta( $id, '_scd_service_short', $page['service']['short'] );
			update_post_meta( $id, '_scd_service_card', $page['service']['card'] );
			update_post_meta( $id, '_scd_service_blurb', $page['service']['blurb'] );
			if ( $page['service']['tone'] ) {
				update_post_meta( $id, '_scd_service_tone', $page['service']['tone'] );
			} else {
				delete_post_meta( $id, '_scd_service_tone' );
			}
		}
		$this->seo( $id, $page['seo_title'], $page['seo_description'] );

		$elements = $this->resolve( $page['elementor'] );
		$document = \Elementor\Plugin::$instance->documents->get( $id, false );
		if ( $document ) {
			$document->set_is_built_with_elementor( true );
			$document->save(
				array(
					'elements' => $elements,
					'settings' => array( 'post_status' => 'publish' ),
				)
			);
		}
	}

	/**
	 * Find or create a blog post.
	 *
	 * @param array $post Post data.
	 * @return int
	 */
	public function ensure_post( $post ) {
		$found = get_posts(
			array(
				'name'        => $post['slug'],
				'post_type'   => 'post',
				'post_status' => 'any',
				'numberposts' => 1,
			)
		);
		if ( $found ) {
			return $found[0]->ID;
		}
		$id = wp_insert_post(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
				'post_title'  => $post['title'],
				'post_name'   => $post['slug'],
				'post_date'   => $post['date'] . ' 09:00:00',
			),
			true
		);
		return is_wp_error( $id ) ? 0 : $id;
	}

	/**
	 * Fill a post.
	 *
	 * @param int   $id   Post ID.
	 * @param array $post Post data.
	 */
	public function fill_post( $id, $post ) {
		if ( get_post_meta( $id, '_scd_imported', true ) || '' === get_post_field( 'post_content', $id ) ) {
			wp_update_post(
				array(
					'ID'           => $id,
					'post_excerpt' => $post['excerpt'],
					'post_content' => $this->resolve_html( $post['content'] ),
				)
			);
			$term = term_exists( $post['category'], 'category' );
			if ( ! $term ) {
				$term = wp_insert_term( $post['category'], 'category' );
			}
			if ( ! is_wp_error( $term ) ) {
				wp_set_post_categories( $id, array( (int) $term['term_id'] ) );
			}
			$thumb = $this->attachment( substr( $post['thumb'], strlen( 'scd-asset:' ) ) );
			if ( $thumb ) {
				set_post_thumbnail( $id, $thumb );
			}
			$related = array();
			foreach ( $post['related'] as $slug ) {
				if ( isset( $this->page_ids[ $slug ] ) ) {
					$related[] = $this->page_ids[ $slug ];
				}
			}
			update_post_meta( $id, '_scd_related', $related );
			update_post_meta( $id, '_scd_author', $post['author'] );
			if ( $post['note'] ) {
				update_post_meta( $id, '_scd_note', $post['note'] );
			}
			update_post_meta( $id, '_scd_imported', SCD_THEME_VERSION );
			$this->seo( $id, $post['seo_title'], $post['seo_description'] );
		}
	}

	/**
	 * Copy the old site's SEO title and description into ThinkRank's fields (only where empty).
	 * ThinkRank stores these as post meta; without ThinkRank they are harmless and unused.
	 *
	 * @param int    $id    Post ID.
	 * @param string $title SEO title.
	 * @param string $desc  Meta description.
	 */
	public function seo( $id, $title, $desc ) {
		if ( $title && '' === (string) get_post_meta( $id, '_thinkrank_seo_title', true ) ) {
			update_post_meta( $id, '_thinkrank_seo_title', $title );
		}
		if ( $desc && '' === (string) get_post_meta( $id, '_thinkrank_meta_description', true ) ) {
			update_post_meta( $id, '_thinkrank_meta_description', $desc );
		}
	}

	/**
	 * Import a theme image into the Media Library (once) and return its ID.
	 *
	 * @param string $rel   Path under assets/images/.
	 * @param string $title Title (defaults to the file name).
	 * @return int
	 */
	public function attachment( $rel, $title = '' ) {
		$rel = ltrim( $rel, '/' );
		if ( isset( $this->attachments[ $rel ] ) ) {
			return $this->attachments[ $rel ];
		}
		$found = get_posts(
			array(
				'post_type'   => 'attachment',
				'post_status' => 'inherit',
				'numberposts' => 1,
				'fields'      => 'ids',
				'meta_key'    => '_scd_source', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one-off import.
				'meta_value'  => $rel, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- one-off import.
			)
		);
		if ( $found ) {
			$this->attachments[ $rel ] = (int) $found[0];
			return $this->attachments[ $rel ];
		}
		$src = SCD_THEME_DIR . '/assets/images/' . $rel;
		if ( ! is_readable( $src ) ) {
			return 0;
		}
		$upload = wp_upload_bits( wp_basename( $rel ), null, (string) file_get_contents( $src ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
		if ( ! empty( $upload['error'] ) ) {
			return 0;
		}
		$type = wp_check_filetype( $upload['file'] );
		$id   = wp_insert_attachment(
			array(
				'post_mime_type' => $type['type'] ? $type['type'] : 'image/svg+xml',
				'post_title'     => $title ? $title : ucfirst( str_replace( '-', ' ', pathinfo( $rel, PATHINFO_FILENAME ) ) ),
				'post_status'    => 'inherit',
			),
			$upload['file']
		);
		if ( is_wp_error( $id ) || ! $id ) {
			return 0;
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
		update_post_meta( $id, '_scd_source', $rel );
		if ( isset( $this->alts[ $rel ] ) ) {
			update_post_meta( $id, '_wp_attachment_image_alt', $this->alts[ $rel ] );
		}
		$this->attachments[ $rel ] = (int) $id;
		return (int) $id;
	}

	/**
	 * Permalink for a slug (page, then post, then a best guess).
	 *
	 * @param string $slug Slug ('' for the homepage).
	 * @return string
	 */
	public function url_for( $slug ) {
		if ( '' === $slug || 'index' === $slug || 'home' === $slug ) {
			return home_url( '/' );
		}
		if ( isset( $this->page_ids[ $slug ] ) ) {
			return get_permalink( $this->page_ids[ $slug ] );
		}
		if ( isset( $this->post_ids[ $slug ] ) ) {
			return get_permalink( $this->post_ids[ $slug ] );
		}
		return home_url( '/' . $slug . '/' );
	}

	/**
	 * Resolve placeholders in HTML strings.
	 *
	 * @param string $html HTML.
	 * @return string
	 */
	public function resolve_html( $html ) {
		$self = $this;
		$html = preg_replace_callback(
			'#href="(?:([a-z0-9-]+)\.html|scd-page:([a-z0-9-]*))(\?[^"\#]*)?(\#[^"]*)?"#',
			function ( $m ) use ( $self ) {
				$slug = '' !== $m[1] ? $m[1] : $m[2];
				if ( 0 === strpos( $slug, 'blog-' ) ) {
					$slug = substr( $slug, 5 );
				}
				return 'href="' . esc_url( $self->url_for( 'index' === $slug ? '' : $slug ) . ( $m[3] ?? '' ) . ( $m[4] ?? '' ) ) . '"';
			},
			$html
		);
		return preg_replace_callback(
			'#src="assets/images/([^"]+)"#',
			function ( $m ) use ( $self ) {
				$id = $self->attachment( $m[1] );
				return 'src="' . esc_url( $id ? wp_get_attachment_url( $id ) : scd_theme_image( $m[1] ) ) . '"';
			},
			$html
		);
	}

	/**
	 * Resolve placeholders anywhere in Elementor data.
	 *
	 * @param mixed $data Data.
	 * @return mixed
	 */
	public function resolve( $data ) {
		if ( is_array( $data ) ) {
			if ( isset( $data['url'] ) && is_string( $data['url'] ) && 0 === strpos( $data['url'], 'scd-asset:' ) ) {
				$rel = substr( $data['url'], strlen( 'scd-asset:' ) );
				$id  = $this->attachment( $rel );
				return array_merge(
					$data,
					array(
						'url'    => $id ? wp_get_attachment_url( $id ) : scd_theme_image( $rel ),
						'id'     => $id ? $id : '',
						'source' => 'library',
						'alt'    => '',
					)
				);
			}
			foreach ( $data as $k => $v ) {
				$data[ $k ] = $this->resolve( $v );
			}
			return $data;
		}
		if ( ! is_string( $data ) ) {
			return $data;
		}
		if ( 0 === strpos( $data, 'scd-pageid:' ) ) {
			$slug = substr( $data, strlen( 'scd-pageid:' ) );
			return isset( $this->page_ids[ $slug ] ) ? (string) $this->page_ids[ $slug ] : '';
		}
		if ( 0 === strpos( $data, 'scd-page:' ) && preg_match( '#^scd-page:([a-z0-9-]*)(\?[^\#]*)?(\#.*)?$#', $data, $m ) ) {
			return $this->url_for( $m[1] ) . ( $m[2] ?? '' ) . ( $m[3] ?? '' );
		}
		if ( false !== strpos( $data, '.html' ) || false !== strpos( $data, 'scd-page:' ) || false !== strpos( $data, 'assets/images/' ) ) {
			return $this->resolve_html( $data );
		}
		return $data;
	}

	/**
	 * Main and footer menus.
	 */
	public function menus() {
		$p   = $this->page_ids;
		$url = function ( $slug ) {
			return $this->url_for( $slug );
		};
		$groups = array();
		foreach ( scd_service_groups() as $key => $label ) {
			if ( 'urgent' === $key ) {
				continue;
			}
			$groups[ $key ] = array( $label, scd_service_pages( $key ) );
		}

		// Main navigation.
		$main = $this->fresh_menu( __( 'Main navigation', 'south-cairns-dental' ) );
		$this->item( $main, __( 'Home', 'south-cairns-dental' ), home_url( '/' ) );
		$about = $this->item( $main, __( 'About us', 'south-cairns-dental' ), '#' );
		$this->page_item( $main, $p['about'] ?? 0, __( 'About South Cairns Dental', 'south-cairns-dental' ), $about );
		$this->page_item( $main, $p['team'] ?? 0, __( 'Meet our dentists', 'south-cairns-dental' ), $about );
		$this->page_item( $main, $p['new-patients'] ?? 0, __( 'New patients', 'south-cairns-dental' ), $about );
		$svc = $this->item( $main, __( 'Services', 'south-cairns-dental' ), '#', 0, array( 'description' => __( 'General, restorative and cosmetic dentistry for children and adults, all under one roof in Edmonton.', 'south-cairns-dental' ), 'attr_title' => __( 'Our services', 'south-cairns-dental' ) ) );
		$this->page_item( $main, $p['services'] ?? 0, __( 'Services overview', 'south-cairns-dental' ), $svc );
		$this->page_item( $main, $p['emergency-dentist'] ?? 0, __( 'Emergency dental care', 'south-cairns-dental' ), $svc, 'is-urgent' );
		foreach ( $groups as $g ) {
			$col = $this->item( $main, $g[0], '#', $svc );
			foreach ( $g[1] as $page ) {
				$this->page_item( $main, $page->ID, get_the_title( $page ), $col );
			}
		}
		$pat = $this->item( $main, __( 'Patient information', 'south-cairns-dental' ), '#' );
		$this->page_item( $main, $p['faq'] ?? 0, __( 'Frequently asked questions', 'south-cairns-dental' ), $pat );
		$this->page_item( $main, $p['special-offers'] ?? 0, __( 'Special offers', 'south-cairns-dental' ), $pat );
		$this->page_item( $main, $p['emergency-dentist'] ?? 0, __( 'Emergency dental care', 'south-cairns-dental' ), $pat );
		$this->page_item( $main, $p['blog'] ?? 0, __( 'Blog', 'south-cairns-dental' ) );
		$this->page_item( $main, $p['contact'] ?? 0, __( 'Contact us', 'south-cairns-dental' ) );

		// Footer: Services.
		$fs = $this->fresh_menu( __( 'Services', 'south-cairns-dental' ) );
		$this->page_item( $fs, $p['services'] ?? 0, __( 'Services overview', 'south-cairns-dental' ) );
		foreach ( $groups as $g ) {
			foreach ( $g[1] as $page ) {
				$this->page_item( $fs, $page->ID, get_the_title( $page ) );
			}
		}
		$this->page_item( $fs, $p['emergency-dentist'] ?? 0, __( 'Emergency dental care', 'south-cairns-dental' ) );

		// Footer: About us.
		$fa = $this->fresh_menu( __( 'About us', 'south-cairns-dental' ) );
		$this->page_item( $fa, $p['about'] ?? 0, __( 'About South Cairns Dental', 'south-cairns-dental' ) );
		$this->page_item( $fa, $p['team'] ?? 0, __( 'Meet our dentists', 'south-cairns-dental' ) );
		$this->page_item( $fa, $p['new-patients'] ?? 0, __( 'New patients', 'south-cairns-dental' ) );

		// Footer: Patients.
		$fp = $this->fresh_menu( __( 'Patients', 'south-cairns-dental' ) );
		$this->page_item( $fp, $p['faq'] ?? 0, __( 'Frequently asked questions', 'south-cairns-dental' ) );
		$this->page_item( $fp, $p['special-offers'] ?? 0, __( 'Special offers', 'south-cairns-dental' ) );
		$this->page_item( $fp, $p['blog'] ?? 0, __( 'Blog', 'south-cairns-dental' ) );
		$this->page_item( $fp, $p['contact'] ?? 0, __( 'Contact us', 'south-cairns-dental' ) );
		$this->page_item( $fp, $p['privacy'] ?? 0, __( 'Privacy policy', 'south-cairns-dental' ) );

		// Footer: bottom links.
		$fl = $this->fresh_menu( __( 'Footer links', 'south-cairns-dental' ) );
		$this->page_item( $fl, $p['privacy'] ?? 0, __( 'Privacy policy', 'south-cairns-dental' ) );

		$locations                    = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary']         = $main;
		$locations['footer_services'] = $fs;
		$locations['footer_about']    = $fa;
		$locations['footer_patients'] = $fp;
		$locations['footer_legal']    = $fl;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Create a menu, or empty the existing one with that name.
	 *
	 * @param string $name Menu name.
	 * @return int
	 */
	protected function fresh_menu( $name ) {
		$menu = wp_get_nav_menu_object( $name );
		if ( $menu ) {
			foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $item ) {
				wp_delete_post( $item->ID, true );
			}
			return (int) $menu->term_id;
		}
		return (int) wp_create_nav_menu( $name );
	}

	/**
	 * Add a custom-link menu item.
	 *
	 * @param int    $menu   Menu ID.
	 * @param string $title  Label.
	 * @param string $url    URL.
	 * @param int    $parent Parent item ID.
	 * @param array  $extra  description, attr_title.
	 * @return int
	 */
	protected function item( $menu, $title, $url, $parent = 0, $extra = array() ) {
		return (int) wp_update_nav_menu_item(
			$menu,
			0,
			array(
				'menu-item-title'       => $title,
				'menu-item-url'         => $url,
				'menu-item-type'        => 'custom',
				'menu-item-status'      => 'publish',
				'menu-item-parent-id'   => $parent,
				'menu-item-description' => $extra['description'] ?? '',
				'menu-item-attr-title'  => $extra['attr_title'] ?? '',
			)
		);
	}

	/**
	 * Add a page menu item.
	 *
	 * @param int    $menu    Menu ID.
	 * @param int    $page_id Page ID.
	 * @param string $title   Label.
	 * @param int    $parent  Parent item ID.
	 * @param string $classes CSS classes.
	 * @return int
	 */
	protected function page_item( $menu, $page_id, $title, $parent = 0, $classes = '' ) {
		if ( ! $page_id ) {
			return 0;
		}
		return (int) wp_update_nav_menu_item(
			$menu,
			0,
			array(
				'menu-item-title'     => $title,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent,
				'menu-item-classes'   => $classes,
			)
		);
	}

	/**
	 * Elementor: brand colours and fonts as the kit's globals, and no Elementor default fonts/colours.
	 */
	public function elementor_brand() {
		update_option( 'elementor_disable_color_schemes', 'yes' );
		update_option( 'elementor_disable_typography_schemes', 'yes' );
		$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
		if ( ! $kit || ! $kit->get_id() ) {
			return;
		}
		$settings                      = (array) $kit->get_settings();
		$settings['system_colors']     = array(
			array( '_id' => 'primary', 'title' => 'Forest', 'color' => '#1E4A3F' ),
			array( '_id' => 'secondary', 'title' => 'Fern', 'color' => '#2E6B5A' ),
			array( '_id' => 'text', 'title' => 'Ink', 'color' => '#1B2622' ),
			array( '_id' => 'accent', 'title' => 'SCD Lime', 'color' => '#AEC953' ),
		);
		$settings['custom_colors']     = array(
			array( '_id' => 'scdcanopy', 'title' => 'Canopy', 'color' => '#12302A' ),
			array( '_id' => 'scdmist', 'title' => 'Mist', 'color' => '#EEF3EC' ),
			array( '_id' => 'scdstone', 'title' => 'Stone', 'color' => '#535A57' ),
			array( '_id' => 'scdalert', 'title' => 'Alert', 'color' => '#A3302A' ),
		);
		$display                       = array( 'typography_typography' => 'custom', 'typography_font_family' => 'Archivo', 'typography_font_weight' => '600' );
		$body                          = array( 'typography_typography' => 'custom', 'typography_font_family' => 'Figtree', 'typography_font_weight' => '400' );
		$settings['system_typography'] = array(
			array_merge( array( '_id' => 'primary', 'title' => 'Display' ), $display ),
			array_merge( array( '_id' => 'secondary', 'title' => 'Display (light)' ), $display, array( 'typography_font_weight' => '500' ) ),
			array_merge( array( '_id' => 'text', 'title' => 'Body' ), $body ),
			array_merge( array( '_id' => 'accent', 'title' => 'Body (bold)' ), $body, array( 'typography_font_weight' => '650' ) ),
		);
		$settings['container_width']   = array( 'unit' => 'px', 'size' => 1260, 'sizes' => array() );
		// Keep the current logo in the kit's Site Identity, which Elementor writes back to the theme logo.
		$logo = (int) get_theme_mod( 'custom_logo' );
		if ( $logo ) {
			$settings['site_logo'] = array( 'id' => $logo, 'url' => wp_get_attachment_url( $logo ) );
		}
		$kit->save( array( 'settings' => $settings ) );
	}
}
