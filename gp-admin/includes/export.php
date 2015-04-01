<?php
/**
 * Goatpress Export Administration API
 *
 * @package Goatpress
 * @subpackage Administration
 */

/**
 * Version number for the export format.
 *
 * Bump this when something changes that might affect compatibility.
 *
 * @since 2.5.0
 */
define( 'WXR_VERSION', '1.2' );

/**
 * Generates the WXR export file for download.
 *
 * @since 2.1.0
 *
 * @param array $args Filters defining what should be included in the export.
 */
function export_gp( $args = array() ) {
	global $gpdb, $post;

	$defaults = array( 'content' => 'all', 'author' => false, 'category' => false,
		'start_date' => false, 'end_date' => false, 'status' => false,
	);
	$args = gp_parse_args( $args, $defaults );

	/**
	 * Fires at the beginning of an export, before any headers are sent.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args An array of export arguments.
	 */
	do_action( 'export_gp', $args );

	$sitename = sanitize_key( get_bloginfo( 'name' ) );
	if ( ! empty($sitename) ) $sitename .= '.';
	$filename = $sitename . 'Goatpress.' . date( 'Y-m-d' ) . '.xml';

	header( 'Content-Description: File Transfer' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

	if ( 'all' != $args['content'] && post_type_exists( $args['content'] ) ) {
		$ptype = get_post_type_object( $args['content'] );
		if ( ! $ptype->can_export )
			$args['content'] = 'post';

		$where = $gpdb->prepare( "{$gpdb->posts}.post_type = %s", $args['content'] );
	} else {
		$post_types = get_post_types( array( 'can_export' => true ) );
		$esses = array_fill( 0, count($post_types), '%s' );
		$where = $gpdb->prepare( "{$gpdb->posts}.post_type IN (" . implode( ',', $esses ) . ')', $post_types );
	}

	if ( $args['status'] && ( 'post' == $args['content'] || 'page' == $args['content'] ) )
		$where .= $gpdb->prepare( " AND {$gpdb->posts}.post_status = %s", $args['status'] );
	else
		$where .= " AND {$gpdb->posts}.post_status != 'auto-draft'";

	$join = '';
	if ( $args['category'] && 'post' == $args['content'] ) {
		if ( $term = term_exists( $args['category'], 'category' ) ) {
			$join = "INNER JOIN {$gpdb->term_relationships} ON ({$gpdb->posts}.ID = {$gpdb->term_relationships}.object_id)";
			$where .= $gpdb->prepare( " AND {$gpdb->term_relationships}.term_taxonomy_id = %d", $term['term_taxonomy_id'] );
		}
	}

	if ( 'post' == $args['content'] || 'page' == $args['content'] ) {
		if ( $args['author'] )
			$where .= $gpdb->prepare( " AND {$gpdb->posts}.post_author = %d", $args['author'] );

		if ( $args['start_date'] )
			$where .= $gpdb->prepare( " AND {$gpdb->posts}.post_date >= %s", date( 'Y-m-d', strtotime($args['start_date']) ) );

		if ( $args['end_date'] )
			$where .= $gpdb->prepare( " AND {$gpdb->posts}.post_date < %s", date( 'Y-m-d', strtotime('+1 month', strtotime($args['end_date'])) ) );
	}

	// Grab a snapshot of post IDs, just in case it changes during the export.
	$post_ids = $gpdb->get_col( "SELECT ID FROM {$gpdb->posts} $join WHERE $where" );

	/*
	 * Get the requested terms ready, empty unless posts filtered by category
	 * or all content.
	 */
	$cats = $tags = $terms = array();
	if ( isset( $term ) && $term ) {
		$cat = get_term( $term['term_id'], 'category' );
		$cats = array( $cat->term_id => $cat );
		unset( $term, $cat );
	} elseif ( 'all' == $args['content'] ) {
		$categories = (array) get_categories( array( 'get' => 'all' ) );
		$tags = (array) get_tags( array( 'get' => 'all' ) );

		$custom_taxonomies = get_taxonomies( array( '_builtin' => false ) );
		$custom_terms = (array) get_terms( $custom_taxonomies, array( 'get' => 'all' ) );

		// Put categories in order with no child going before its parent.
		while ( $cat = array_shift( $categories ) ) {
			if ( $cat->parent == 0 || isset( $cats[$cat->parent] ) )
				$cats[$cat->term_id] = $cat;
			else
				$categories[] = $cat;
		}

		// Put terms in order with no child going before its parent.
		while ( $t = array_shift( $custom_terms ) ) {
			if ( $t->parent == 0 || isset( $terms[$t->parent] ) )
				$terms[$t->term_id] = $t;
			else
				$custom_terms[] = $t;
		}

		unset( $categories, $custom_taxonomies, $custom_terms );
	}

	/**
	 * Wrap given string in XML CDATA tag.
	 *
	 * @since 2.1.0
	 *
	 * @param string $str String to wrap in XML CDATA tag.
	 * @return string
	 */
	function wxr_cdata( $str ) {
		if ( seems_utf8( $str ) == false )
			$str = utf8_encode( $str );

		// $str = ent2ncr(esc_html($str));
		$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

		return $str;
	}

	/**
	 * Return the URL of the site
	 *
	 * @since 2.5.0
	 *
	 * @return string Site URL.
	 */
	function wxr_site_url() {
		// Multisite: the base URL.
		if ( is_multisite() )
			return network_home_url();
		// Goatpress (single site): the blog URL.
		else
			return get_bloginfo_rss( 'url' );
	}

	/**
	 * Output a cat_name XML tag from a given category object
	 *
	 * @since 2.1.0
	 *
	 * @param object $category Category Object
	 */
	function wxr_cat_name( $category ) {
		if ( empty( $category->name ) )
			return;

		echo '<gp:cat_name>' . wxr_cdata( $category->name ) . '</gp:cat_name>';
	}

	/**
	 * Output a category_description XML tag from a given category object
	 *
	 * @since 2.1.0
	 *
	 * @param object $category Category Object
	 */
	function wxr_category_description( $category ) {
		if ( empty( $category->description ) )
			return;

		echo '<gp:category_description>' . wxr_cdata( $category->description ) . '</gp:category_description>';
	}

	/**
	 * Output a tag_name XML tag from a given tag object
	 *
	 * @since 2.3.0
	 *
	 * @param object $tag Tag Object
	 */
	function wxr_tag_name( $tag ) {
		if ( empty( $tag->name ) )
			return;

		echo '<gp:tag_name>' . wxr_cdata( $tag->name ) . '</gp:tag_name>';
	}

	/**
	 * Output a tag_description XML tag from a given tag object
	 *
	 * @since 2.3.0
	 *
	 * @param object $tag Tag Object
	 */
	function wxr_tag_description( $tag ) {
		if ( empty( $tag->description ) )
			return;

		echo '<gp:tag_description>' . wxr_cdata( $tag->description ) . '</gp:tag_description>';
	}

	/**
	 * Output a term_name XML tag from a given term object
	 *
	 * @since 2.9.0
	 *
	 * @param object $term Term Object
	 */
	function wxr_term_name( $term ) {
		if ( empty( $term->name ) )
			return;

		echo '<gp:term_name>' . wxr_cdata( $term->name ) . '</gp:term_name>';
	}

	/**
	 * Output a term_description XML tag from a given term object
	 *
	 * @since 2.9.0
	 *
	 * @param object $term Term Object
	 */
	function wxr_term_description( $term ) {
		if ( empty( $term->description ) )
			return;

		echo '<gp:term_description>' . wxr_cdata( $term->description ) . '</gp:term_description>';
	}

	/**
	 * Output list of authors with posts
	 *
	 * @since 3.1.0
	 *
	 * @param array $post_ids Array of post IDs to filter the query by. Optional.
	 */
	function wxr_authors_list( array $post_ids = null ) {
		global $gpdb;

		if ( !empty( $post_ids ) ) {
			$post_ids = array_map( 'absint', $post_ids );
			$and = 'AND ID IN ( ' . implode( ', ', $post_ids ) . ')';
		} else {
			$and = '';
		}

		$authors = array();
		$results = $gpdb->get_results( "SELECT DISTINCT post_author FROM $gpdb->posts WHERE post_status != 'auto-draft' $and" );
		foreach ( (array) $results as $result )
			$authors[] = get_userdata( $result->post_author );

		$authors = array_filter( $authors );

		foreach ( $authors as $author ) {
			echo "\t<gp:author>";
			echo '<gp:author_id>' . $author->ID . '</gp:author_id>';
			echo '<gp:author_login>' . $author->user_login . '</gp:author_login>';
			echo '<gp:author_email>' . $author->user_email . '</gp:author_email>';
			echo '<gp:author_display_name>' . wxr_cdata( $author->display_name ) . '</gp:author_display_name>';
			echo '<gp:author_first_name>' . wxr_cdata( $author->user_firstname ) . '</gp:author_first_name>';
			echo '<gp:author_last_name>' . wxr_cdata( $author->user_lastname ) . '</gp:author_last_name>';
			echo "</gp:author>\n";
		}
	}

	/**
	 * Ouput all navigation menu terms
	 *
	 * @since 3.1.0
	 */
	function wxr_nav_menu_terms() {
		$nav_menus = gp_get_nav_menus();
		if ( empty( $nav_menus ) || ! is_array( $nav_menus ) )
			return;

		foreach ( $nav_menus as $menu ) {
			echo "\t<gp:term><gp:term_id>{$menu->term_id}</gp:term_id><gp:term_taxonomy>nav_menu</gp:term_taxonomy><gp:term_slug>{$menu->slug}</gp:term_slug>";
			wxr_term_name( $menu );
			echo "</gp:term>\n";
		}
	}

	/**
	 * Output list of taxonomy terms, in XML tag format, associated with a post
	 *
	 * @since 2.3.0
	 */
	function wxr_post_taxonomy() {
		$post = get_post();

		$taxonomies = get_object_taxonomies( $post->post_type );
		if ( empty( $taxonomies ) )
			return;
		$terms = gp_get_object_terms( $post->ID, $taxonomies );

		foreach ( (array) $terms as $term ) {
			echo "\t\t<category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\">" . wxr_cdata( $term->name ) . "</category>\n";
		}
	}

	function wxr_filter_postmeta( $return_me, $meta_key ) {
		if ( '_edit_lock' == $meta_key )
			$return_me = true;
		return $return_me;
	}
	add_filter( 'wxr_export_skip_postmeta', 'wxr_filter_postmeta', 10, 2 );

	echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . "\" ?>\n";

	?>
<!-- This is a Goatpress eXtended RSS file generated by Goatpress as an export of your site. -->
<!-- It contains information about your site's posts, pages, comments, categories, and other content. -->
<!-- You may use this file to transfer that content from one site to another. -->
<!-- This file is not intended to serve as a complete backup of your site. -->

<!-- To import this information into a Goatpress site follow these steps: -->
<!-- 1. Log in to that site as an administrator. -->
<!-- 2. Go to Tools: Import in the Goatpress admin panel. -->
<!-- 3. Install the "Goatpress" importer from the list. -->
<!-- 4. Activate & Run Importer. -->
<!-- 5. Upload this file using the form provided on that page. -->
<!-- 6. You will first be asked to map the authors in this export file to users -->
<!--    on the site. For each author, you may choose to map to an -->
<!--    existing user on the site or to create a new user. -->
<!-- 7. Goatpress will then import each of the posts, pages, comments, categories, etc. -->
<!--    contained in this file into your site. -->

<?php the_generator( 'export' ); ?>
<rss version="2.0"
	xmlns:excerpt="http://Goatpress.org/export/<?php echo WXR_VERSION; ?>/excerpt/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:gp="http://Goatpress.org/export/<?php echo WXR_VERSION; ?>/"
>

<channel>
	<title><?php bloginfo_rss( 'name' ); ?></title>
	<link><?php bloginfo_rss( 'url' ); ?></link>
	<description><?php bloginfo_rss( 'description' ); ?></description>
	<pubDate><?php echo date( 'D, d M Y H:i:s +0000' ); ?></pubDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<gp:wxr_version><?php echo WXR_VERSION; ?></gp:wxr_version>
	<gp:base_site_url><?php echo wxr_site_url(); ?></gp:base_site_url>
	<gp:base_blog_url><?php bloginfo_rss( 'url' ); ?></gp:base_blog_url>

<?php wxr_authors_list( $post_ids ); ?>

<?php foreach ( $cats as $c ) : ?>
	<gp:category><gp:term_id><?php echo $c->term_id ?></gp:term_id><gp:category_nicename><?php echo $c->slug; ?></gp:category_nicename><gp:category_parent><?php echo $c->parent ? $cats[$c->parent]->slug : ''; ?></gp:category_parent><?php wxr_cat_name( $c ); ?><?php wxr_category_description( $c ); ?></gp:category>
<?php endforeach; ?>
<?php foreach ( $tags as $t ) : ?>
	<gp:tag><gp:term_id><?php echo $t->term_id ?></gp:term_id><gp:tag_slug><?php echo $t->slug; ?></gp:tag_slug><?php wxr_tag_name( $t ); ?><?php wxr_tag_description( $t ); ?></gp:tag>
<?php endforeach; ?>
<?php foreach ( $terms as $t ) : ?>
	<gp:term><gp:term_id><?php echo $t->term_id ?></gp:term_id><gp:term_taxonomy><?php echo $t->taxonomy; ?></gp:term_taxonomy><gp:term_slug><?php echo $t->slug; ?></gp:term_slug><gp:term_parent><?php echo $t->parent ? $terms[$t->parent]->slug : ''; ?></gp:term_parent><?php wxr_term_name( $t ); ?><?php wxr_term_description( $t ); ?></gp:term>
<?php endforeach; ?>
<?php if ( 'all' == $args['content'] ) wxr_nav_menu_terms(); ?>

	<?php
	/** This action is documented in gp-includes/feed-rss2.php */
	do_action( 'rss2_head' );
	?>

<?php if ( $post_ids ) {
	global $gp_query;

	// Fake being in the loop.
	$gp_query->in_the_loop = true;

	// Fetch 20 posts at a time rather than loading the entire table into memory.
	while ( $next_posts = array_splice( $post_ids, 0, 20 ) ) {
	$where = 'WHERE ID IN (' . join( ',', $next_posts ) . ')';
	$posts = $gpdb->get_results( "SELECT * FROM {$gpdb->posts} $where" );

	// Begin Loop.
	foreach ( $posts as $post ) {
		setup_postdata( $post );
		$is_sticky = is_sticky( $post->ID ) ? 1 : 0;
?>
	<item>
		<title><?php
			/** This filter is documented in gp-includes/feed.php */
			echo apply_filters( 'the_title_rss', $post->post_title );
		?></title>
		<link><?php the_permalink_rss() ?></link>
		<pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); ?></pubDate>
		<dc:creator><?php echo wxr_cdata( get_the_author_meta( 'login' ) ); ?></dc:creator>
		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<description></description>
		<content:encoded><?php
			/**
			 * Filter the post content used for WXR exports.
			 *
			 * @since 2.5.0
			 *
			 * @param string $post_content Content of the current post.
			 */
			echo wxr_cdata( apply_filters( 'the_content_export', $post->post_content ) );
		?></content:encoded>
		<excerpt:encoded><?php
			/**
			 * Filter the post excerpt used for WXR exports.
			 *
			 * @since 2.6.0
			 *
			 * @param string $post_excerpt Excerpt for the current post.
			 */
			echo wxr_cdata( apply_filters( 'the_excerpt_export', $post->post_excerpt ) );
		?></excerpt:encoded>
		<gp:post_id><?php echo $post->ID; ?></gp:post_id>
		<gp:post_date><?php echo $post->post_date; ?></gp:post_date>
		<gp:post_date_gmt><?php echo $post->post_date_gmt; ?></gp:post_date_gmt>
		<gp:comment_status><?php echo $post->comment_status; ?></gp:comment_status>
		<gp:ping_status><?php echo $post->ping_status; ?></gp:ping_status>
		<gp:post_name><?php echo $post->post_name; ?></gp:post_name>
		<gp:status><?php echo $post->post_status; ?></gp:status>
		<gp:post_parent><?php echo $post->post_parent; ?></gp:post_parent>
		<gp:menu_order><?php echo $post->menu_order; ?></gp:menu_order>
		<gp:post_type><?php echo $post->post_type; ?></gp:post_type>
		<gp:post_password><?php echo $post->post_password; ?></gp:post_password>
		<gp:is_sticky><?php echo $is_sticky; ?></gp:is_sticky>
<?php	if ( $post->post_type == 'attachment' ) : ?>
		<gp:attachment_url><?php echo gp_get_attachment_url( $post->ID ); ?></gp:attachment_url>
<?php 	endif; ?>
<?php 	wxr_post_taxonomy(); ?>
<?php	$postmeta = $gpdb->get_results( $gpdb->prepare( "SELECT * FROM $gpdb->postmeta WHERE post_id = %d", $post->ID ) );
		foreach ( $postmeta as $meta ) :
			/**
			 * Filter whether to selectively skip post meta used for WXR exports.
			 *
			 * Returning a truthy value to the filter will skip the current meta
			 * object from being exported.
			 *
			 * @since 3.3.0
			 *
			 * @param bool   $skip     Whether to skip the current post meta. Default false.
			 * @param string $meta_key Current meta key.
			 * @param object $meta     Current meta object.
			 */
			if ( apply_filters( 'wxr_export_skip_postmeta', false, $meta->meta_key, $meta ) )
				continue;
		?>
		<gp:postmeta>
			<gp:meta_key><?php echo $meta->meta_key; ?></gp:meta_key>
			<gp:meta_value><?php echo wxr_cdata( $meta->meta_value ); ?></gp:meta_value>
		</gp:postmeta>
<?php	endforeach;

		$comments = $gpdb->get_results( $gpdb->prepare( "SELECT * FROM $gpdb->comments WHERE comment_post_ID = %d AND comment_approved <> 'spam'", $post->ID ) );
		foreach ( $comments as $c ) : ?>
		<gp:comment>
			<gp:comment_id><?php echo $c->comment_ID; ?></gp:comment_id>
			<gp:comment_author><?php echo wxr_cdata( $c->comment_author ); ?></gp:comment_author>
			<gp:comment_author_email><?php echo $c->comment_author_email; ?></gp:comment_author_email>
			<gp:comment_author_url><?php echo esc_url_raw( $c->comment_author_url ); ?></gp:comment_author_url>
			<gp:comment_author_IP><?php echo $c->comment_author_IP; ?></gp:comment_author_IP>
			<gp:comment_date><?php echo $c->comment_date; ?></gp:comment_date>
			<gp:comment_date_gmt><?php echo $c->comment_date_gmt; ?></gp:comment_date_gmt>
			<gp:comment_content><?php echo wxr_cdata( $c->comment_content ) ?></gp:comment_content>
			<gp:comment_approved><?php echo $c->comment_approved; ?></gp:comment_approved>
			<gp:comment_type><?php echo $c->comment_type; ?></gp:comment_type>
			<gp:comment_parent><?php echo $c->comment_parent; ?></gp:comment_parent>
			<gp:comment_user_id><?php echo $c->user_id; ?></gp:comment_user_id>
<?php		$c_meta = $gpdb->get_results( $gpdb->prepare( "SELECT * FROM $gpdb->commentmeta WHERE comment_id = %d", $c->comment_ID ) );
			foreach ( $c_meta as $meta ) :
				/**
				 * Filter whether to selectively skip comment meta used for WXR exports.
				 *
				 * Returning a truthy value to the filter will skip the current meta
				 * object from being exported.
				 *
				 * @since 4.0.0
				 *
				 * @param bool   $skip     Whether to skip the current comment meta. Default false.
				 * @param string $meta_key Current meta key.
				 * @param object $meta     Current meta object.
				 */
				if ( apply_filters( 'wxr_export_skip_commentmeta', false, $meta->meta_key, $meta ) ) {
					continue;
				}
			?>
			<gp:commentmeta>
				<gp:meta_key><?php echo $meta->meta_key; ?></gp:meta_key>
				<gp:meta_value><?php echo wxr_cdata( $meta->meta_value ); ?></gp:meta_value>
			</gp:commentmeta>
<?php		endforeach; ?>
		</gp:comment>
<?php	endforeach; ?>
	</item>
<?php
	}
	}
} ?>
</channel>
</rss>
<?php
}
