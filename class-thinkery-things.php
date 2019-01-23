<?php
/**
 * Thinkery s
 *
 * @package Thinkery
 */

/**
 * This is the class for s in Thinkery.
 *
 * @package Thinkery
 * @author Alex Kirk
 */
class Thinkery_Things {
	const CPT = 'thinkery_thing';
	const TAG = 'thinkery_tag';

	/**
	 * Contains a reference to the Thinkery class.
	 *
	 * @var Thinkery
	 */
	private $thinkery;

	/**
	 * Constructor
	 *
	 * @param thinkery $thinkery A reference to the thinkery object.
	 */
	public function __construct( Thinkery $thinkery ) {
		$this->thinkery = $thinkery;
		$this->register_hooks();
	}

	/**
	 * Register the WordPress hooks
	 */
	private function register_hooks() {
		add_filter( 'init', array( $this, 'register_custom_post_type' ) );
		add_action( 'edit_form_before_permalink', array( $this, 'edit_form_before_permalink' ), 10, 3 );
		add_action( 'post_row_actions', array( $this, 'post_row_actions' ), 10, 2 );
		add_action( 'wp_ajax_thinkery_save_url', array( $this, 'ajax_save_url' ) );
	}

	/**
	 * Registers the custom post type
	 */
	public function register_custom_post_type() {
		$labels = array(
			'name'               => _x( 'Things', 'taxonomy plural name', 'thinkery' ),
			'singular_name'      => _x( 'Thing', 'taxonomy singular name', 'thinkery' ),
			'add_new'            => _x( 'Add New', 'thing', 'thinkery' ),
			'add_new_item'       => __( 'Add New Thing', 'thinkery' ),
			'edit_item'          => __( 'Edit Thing', 'thinkery' ),
			'new_item'           => __( 'New Thing', 'thinkery' ),
			'all_items'          => __( 'All Things', 'thinkery' ),
			'view_item'          => __( 'View Thing', 'thinkery' ),
			'search_items'       => __( 'Search Things', 'thinkery' ),
			'not_found'          => __( 'No Things found', 'thinkery' ),
			'not_found_in_trash' => __( 'No Things found in the Trash', 'thinkery' ),
			'parent_item_colon'  => '',
			'menu_name'          => 'Thinkery',
		);

		$args = array(
			'labels'              => $labels,
			'description'         => _x( 'Thing', 'taxonomy singular name', 'thinkery' ),
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'show_in_rest'        => false,
			'exclude_from_search' => false,
			'public'              => false,
			'menu_position'       => 4,
			'menu_icon'           => null,
			'supports'            => array( 'title', 'editor', 'author' ),
			'taxonomies'          => array( self::TAG ),
			'has_archive'         => true,
		);

		register_post_type( self::CPT, $args );

		$tag_labels = array(
			'name'                       => _x( 'Tags', 'taxonomy general name' ),
			'singular_name'              => _x( 'Tag', 'taxonomy singular name' ),
			'search_items'               => __( 'Search Tags' ),
			'popular_items'              => __( 'Popular Tags' ),
			'all_items'                  => __( 'All Tags' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Tag' ),
			'update_item'                => __( 'Update Tag' ),
			'add_new_item'               => __( 'Add New Tag' ),
			'new_item_name'              => __( 'New Tag Name' ),
			'separate_items_with_commas' => __( 'Separate tags with commas' ),
			'add_or_remove_items'        => __( 'Add or remove tags' ),
			'choose_from_most_used'      => __( 'Choose from the most used tags' ),
			'menu_name'                  => __( 'Tags' ),
		);

		register_taxonomy(
			self::TAG,
			self::CPT,
			array(
				'hierarchical'          => true,
				'labels'                => $tag_labels,
				'show_ui'               => true,
				'update_count_callback' => array( $this, '_update_post_term_count' ),
				'query_var'             => true,
				'rewrite'               => array( 'slug' => 'tag' ),
			)
		);
	}

	/**
	 * Almost verbatim copy of Core's _update_post_term_count() except for the ignored post_status
	 *
	 * @param array  $terms    List of Term taxonomy IDs.
	 * @param object $taxonomy Current taxonomy object of terms.
	 */
	public function _update_post_term_count( $terms, $taxonomy ) {
		global $wpdb;

		$object_types = (array) $taxonomy->object_type;

		foreach ( $object_types as &$object_type ) {
			list( $object_type ) = explode( ':', $object_type );
		}

		$object_types = array_unique( $object_types );

		if ( false !== ( $check_attachments = array_search( 'attachment', $object_types ) ) ) {
			unset( $object_types[ $check_attachments ] );
			$check_attachments = true;
		}

		if ( $object_types ) {
			$object_types = esc_sql( array_filter( $object_types, 'post_type_exists' ) );
		}

		foreach ( (array) $terms as $term ) {
			$count = 0;

			// Attachments can be 'inherit' status, we need to base count off the parent's status if so.
			if ( $check_attachments ) {
				$count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts p1 WHERE p1.ID = $wpdb->term_relationships.object_id AND ( post_status = 'publish' OR ( post_status = 'inherit' AND post_parent > 0 AND ( SELECT post_status FROM $wpdb->posts WHERE ID = p1.post_parent ) = 'publish' ) ) AND post_type = 'attachment' AND term_taxonomy_id = %d", $term ) );
			}

			if ( $object_types ) {
				$count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND post_type IN ('" . implode( "', '", $object_types ) . "') AND term_taxonomy_id = %d", $term ) );
			}

			/** This action is documented in wp-includes/taxonomy.php */
			do_action( 'edit_term_taxonomy', $term, $taxonomy->name );
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );

			/** This action is documented in wp-includes/taxonomy.php */
			do_action( 'edited_term_taxonomy', $term, $taxonomy->name );
		}
	}


	/**
	 * Save the saved_url via the saved_urllet
	 */
	function ajax_save_url() {
		if ( empty( $_GET['url'] ) ) {
			return new WP_Error( 'invalid-url', __( 'You entered an invalid URL.', 'thinkery' ) );
		}

		$error = $this->save_url( $_GET['url'] );
		wp_safe_redirect( add_query_arg( 'error', $error->get_error_code(), self_admin_url( 'admin.php?page=thinkery-save-url&url=' . esc_url( $_GET['url'] ) ) ) );
	}

	/**
	 * Download and save the saved_url as a CPT
	 *
	 * @param  string $url The URL to save.
	 * @return WP_Error    Potentially an error message
	 */
	function save_url( $url ) {
		if ( ! is_string( $url ) || ! wp_http_validate_url( $url ) ) {
			return new WP_Error( 'invalid-url', __( 'You entered an invalid URL.', 'thinkery' ) );
		}

		$post_id = $this->url_to_postid( $url, get_current_user_id() );
		if ( is_null( $post_id ) ) {
			$item = $this->download( $url );
			if ( is_wp_error( $item ) ) {
				return $item;
			}

			if ( ! $item->content && ! $item->title ) {
				return new WP_Error( 'invalid-content', __( 'No content was extracted.', 'thinkery' ) );
			}

			$title   = strip_tags( trim( $item->title ) );
			$content = wp_kses_post( trim( $item->content ) );

			$post_data = array(
				'post_title'    => $title,
				'post_content'  => $content,
				'post_date_gmt' => date( 'Y-m-d H:i:s' ),
				'post_status'   => 'private',
				'guid'          => $item->url,
				'post_type'     => self::CPT,
			);

			$post_id = wp_insert_post( $post_data, true );
		}
		wp_untrash_post( $post_id );
		wp_safe_redirect( self_admin_url( 'post.php?post=' . $post_id . '&action=edit' ) );
		exit;
	}

	/**
	 * Download site config for a URL if it exists
	 *
	 * @param  string $filename The filename to download.
	 * @return string|false The site config.
	 */
	public function download_site_config( $filename ) {
		$response = wp_safe_remote_get(
			'https://raw.githubusercontent.com/fivefilters/ftr-site-config/master/' . $filename,
			array(
				'timeout'     => 20,
				'redirection' => 5,
			)
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Get the parsed site config for a URL
	 *
	 * @param  string $url The URL for which to retrieve the site config.
	 * @return array|false The site config.
	 */
	public function get_site_config( $url ) {
		foreach ( $this->get_site_config_filenames( $url ) as $filename ) {
			$text = $this->download_site_config( $filename );
			if ( ! $text ) {
				continue;
			}

			return $this->parse_site_config( $text );
		}
		return false;
	}

	/**
	 * Prase the site config
	 *
	 * @param  string $text The site config text.
	 * @return array The parsed site config.
	 */
	public function parse_site_config( $text ) {
		$site_config = array();
		$search      = false;
		foreach ( explode( PHP_EOL, $text ) as $line ) {
			if ( false === strpos( $line, ':' ) || '#' === substr( ltrim( $line ), 0, 1 ) ) {
				continue;
			}

			list( $key, $value ) = explode( ':', $line, 2 );
			$key                 = strtolower( trim( $key ) );
			$value               = trim( $value );

			if ( 'find_string' === $key ) {
				$search = $value;
				continue;
			}

			if ( in_array( $key, array( 'title', 'date', 'body', 'author' ) ) ) {
				$site_config[ $key ] = $value;
				continue;
			}

			if ( 'replace_string' === $key ) {
				if ( false === $search ) {
					continue;
				}

				if ( ! isset( $site_config['replace'] ) ) {
					$site_config['replace'] = array();
				}

				$site_config['replace'][ $search ] = $value;
				$search                            = false;
				continue;

			}

			if ( 'http_header(' === substr( $key, 0, 12 ) ) {
				if ( ! isset( $site_config['http_header'] ) ) {
					$site_config['http_header'] = array();
				}

				$site_config['http_header'][ substr( $key, 12, -1 ) ] = $value;
				continue;
			}

			if ( in_array( $key, array( 'strip', 'strip_id_or_class' ) ) ) {
				if ( ! isset( $site_config[ $key ] ) ) {
					$site_config[ $key ] = array();
				}

				$site_config[ $key ][] = $value;
				continue;
			}
		}

		return $site_config;
	}

	/**
	 * Get possible site config filenames
	 *
	 * @param  string $url The URL for which to get possible site config filenames.
	 * @return array An array of potential filenames.
	 */
	public function get_site_config_filenames( $url ) {
		$host = parse_url( $url, PHP_URL_HOST );
		if ( 'www.' === substr( $host, 0, 4 ) ) {
			$host = substr( $host, 4 );
		}

		$filenames = array( $host . '.txt' );
		if ( substr_count( $host, '.' ) > 1 ) {
			$filenames[] = substr( $host, strpos( $host, '.' ) ) . '.txt';
		}

		return $filenames;
	}

	/**
	 * Download the url from the URL
	 *
	 * @param  string $url The URL to download.
	 * @return object An item object.
	 */
	public function download( $url ) {
		$args = array(
			'timeout'     => 20,
			'redirection' => 5,
			'headers'     => array(
				'user-agent' => 'thinkery Plugin',
			),
		);

		$site_config = $this->get_site_config( $url );
		if ( isset( $site_config['http_header'] ) ) {
			$args['headers'] = array_merge( $args['headers'], $site_config['http_header'] );
		}

		$response = wp_safe_remote_get( $url, $args );
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( 'could-not-download', __( 'Could not download the URL.', 'thinkery' ) );
		}

		$item      = $this->extract_content( wp_remote_retrieve_body( $response ), $site_config );
		$item->url = $url;
		return $item;

	}

	/**
	 * Extract the content of a URL
	 *
	 * @param  string $html        The HTML from which to extract the content.
	 * @param  array  $site_config The site config.
	 * @return object The parsed content.
	 */
	public function extract_content( $html, $site_config = array() ) {
		if ( ! $site_config ) {
			$site_config = array();
		}

		if ( isset( $site_config['replace'] ) ) {
			foreach ( $site_config['replace'] as $search => $replace ) {
				$html = str_replace( $search, $replace, $html );
			}
		}

		$item = (object) array(
			'title'   => false,
			'content' => false,
		);

		if ( ! class_exists( 'Readability', false ) ) {
			require_once __DIR__ . '/lib/PressForward-Readability/Readability.php';
		}

		set_error_handler( '__return_null' );
		$readability = new Readability( '<?xml encoding="utf-8" ?>' . $html );
		restore_error_handler();
		$xpath = new DOMXpath( $readability->dom );

		if ( isset( $site_config['strip_id_or_class'] ) ) {
			foreach ( $site_config['strip_id_or_class'] as $id_or_class ) {
				$strip = $xpath->query( '//*[contains(@class, "' . esc_attr( $id_or_class ) . '")]|//*[@id="' . esc_attr( $id_or_class ) . '"]' );
				$this->remove_node( $strip );
			}
		}

		if ( isset( $site_config['strip'] ) ) {
			foreach ( $site_config['strip'] as $xp ) {
				$this->remove_node( $xpath->query( $xp ) );
			}
		}

		if ( isset( $site_config['title'] ) ) {
			$item->title = $xpath->query( $site_config['title'] );
			if ( $item->title ) {
				$item->title = $this->get_inner_html( $item->title );
			}
		}

		if ( isset( $site_config['body'] ) ) {
			$item->content = $xpath->query( $site_config['body'] );
			if ( $item->content ) {
				$item->content = $this->get_inner_html( $item->content );
			}
		}

		if ( ! $item->title || ! $item->content ) {
			$copied_dom = clone $readability->dom;
			$result     = $readability->init();
			if ( $result ) {
				if ( ! $item->title ) {
					$item->title = $readability->getTitle()->textContent;
				}
				if ( ! $item->content ) {
					$item->content = $readability->getContent()->innerHTML;
				}
			} else {
				$xpath = new DOMXpath( $copied_dom );

				if ( ! $item->title ) {
					$item->title = $xpath->query( '(//h1)[1]' );
					if ( $item->title ) {
						$item->title = $this->get_inner_html( $item->title );
					} else {
						$item->title = $xpath->query( '//title' );
						if ( $item->title ) {
							$item->title = $this->get_inner_html( $item->title );
						}
					}
				}
				if ( ! $item->content ) {
					$urls      = array( 'url', 'blog', 'body', 'content', 'entry', 'hentry', 'main', 'page', 'post', 'text', 'story' );
					$item->content = $xpath->query( '(//*[contains(@class, "' . implode( '")]|//*[contains(@class, "', $urls ) . '")]|*[contains(@id, "' . implode( '")]|//*[contains(@id, "', $urls ) . '")])[1]' );
					if ( $item->content ) {
						$item->content = $this->get_inner_html( $item->content );
					} else {
						$item->title = $xpath->query( '//body' );
						if ( $item->title ) {
							$item->title = $this->get_inner_html( $item->title );
						}
					}
				}
			}
		}

		return $item;
	}

	/**
	 * Extract the innerHTML of a node
	 *
	 * @param  object $node The DOM node or a DOMNodeList.
	 * @return string The innerHTML.
	 */
	private function get_inner_html( $node ) {
		$html = '';
		if ( $node instanceof DOMNodeList ) {
			$nodelist = $node;
		} elseif ( isset( $node->childNodes ) ) { // @codingStandardsIgnoreLine
			$nodelist = $node->childNodes; // @codingStandardsIgnoreLine
		} else {
			return false;
		}

		foreach ( $nodelist as $child ) {
			$html .= $child->innerHTML; // @codingStandardsIgnoreLine
		}

		return $this->clean_html( $html );
	}

	/**
	 * Remove the node from the DOM.
	 *
	 * @param  object $node The DOM node or a DOMNodeList to remove.
	 */
	private function remove_node( $node ) {
		if ( $node instanceof DOMNodeList ) {
			$nodelist = $node;
		} elseif ( isset( $node->childNodes ) ) { // @codingStandardsIgnoreLine
			$nodelist = $node->childNodes; // @codingStandardsIgnoreLine
		} else {
			return false;
		}

		foreach ( $nodelist as $child ) {
			$child->parentNode->removeChild( $child ); // @codingStandardsIgnoreLine
		}
	}

	/**
	 * Clean the HTML
	 *
	 * @param  string $html The HTML to be cleaned.
	 * @return string       The cleaned HTML.
	 */
	private function clean_html( $html ) {
		$html = preg_replace( '#\n\s*\n\s*#', PHP_EOL . PHP_EOL, trim( $html ) );

		return $html;
	}

	/**
	 * Add actions to the post rows
	 *
	 * @param  array   $actions The existing actions.
	 * @param  WP_Post $post    The post in question.
	 * @return array The extended actions.
	 */
	public function post_row_actions( array $actions, WP_Post $post ) {
		if ( self::CPT !== $post->post_type ) {
			return $actions;
		}
		$actions['visit'] = '<a href="' . esc_url( $post->guid ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $post->guid ) . '</a>';

		return $actions;
	}

	/**
	 * Show the URL on the saved_urls custom post type
	 *
	 * @param  WP_Post $post The post to be shown.
	 */
	public function edit_form_before_permalink( WP_Post $post ) {
		if ( self::CPT !== $post->post_type ) {
			return;
		}

		?>
		<p>
			<label><?php _e( 'URL' ); ?> <input type="text" name="guid" value="<?php echo esc_url( $post->guid ); ?>" class="regular-text"/></label>
			(Saving URL changes doesn't work yet)
			<a href="<?php echo esc_url( site_url( '/thinkery/' . $post->ID . '/' ) ); ?>">View on frontend</a>
		</p>
		<?php
	}

	/**
	 * More generic version of the native url_to_postid()
	 *
	 * @param string $url       Permalink to check.
	 * @param int    $author_id The id of the author.
	 * @return int Post ID, or 0 on failure.
	 */
	function url_to_postid( $url, $author_id = false ) {
		global $wpdb;
		if ( $author_id ) {
			$post_id = $wpdb->get_var( $wpdb->prepare( 'SELECT ID from ' . $wpdb->posts . ' WHERE guid IN (%s, %s) AND post_author = %d LIMIT 1', $url, esc_attr( $url ), $author_id ) );
		} else {
			$post_id = $wpdb->get_var( $wpdb->prepare( 'SELECT ID from ' . $wpdb->posts . ' WHERE guid IN (%s, %s) LIMIT 1', $url, esc_attr( $url ) ) );
		}
		return $post_id;
	}
}
