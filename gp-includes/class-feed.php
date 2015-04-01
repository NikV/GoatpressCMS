<?php

if ( !class_exists('SimplePie') )
	require_once( ABSPATH . gpINC . '/class-simplepie.php' );

class gp_Feed_Cache extends SimplePie_Cache {
	/**
	 * Create a new SimplePie_Cache object
	 *
	 * @static
	 * @access public
	 */
	public function create($location, $filename, $extension) {
		return new gp_Feed_Cache_Transient($location, $filename, $extension);
	}
}

class gp_Feed_Cache_Transient {
	public $name;
	public $mod_name;
	public $lifetime = 43200; //Default lifetime in cache of 12 hours

	public function __construct($location, $filename, $extension) {
		$this->name = 'feed_' . $filename;
		$this->mod_name = 'feed_mod_' . $filename;

		$lifetime = $this->lifetime;
		/**
		 * Filter the transient lifetime of the feed cache.
		 *
		 * @since 2.8.0
		 *
		 * @param int    $lifetime Cache duration in seconds. Default is 43200 seconds (12 hours).
		 * @param string $filename Unique identifier for the cache object.
		 */
		$this->lifetime = apply_filters( 'gp_feed_cache_transient_lifetime', $lifetime, $filename);
	}

	public function save($data) {
		if ( $data instanceof SimplePie ) {
			$data = $data->data;
		}

		set_transient($this->name, $data, $this->lifetime);
		set_transient($this->mod_name, time(), $this->lifetime);
		return true;
	}

	public function load() {
		return get_transient($this->name);
	}

	public function mtime() {
		return get_transient($this->mod_name);
	}

	public function touch() {
		return set_transient($this->mod_name, time(), $this->lifetime);
	}

	public function unlink() {
		delete_transient($this->name);
		delete_transient($this->mod_name);
		return true;
	}
}

class gp_SimplePie_File extends SimplePie_File {

	public function __construct($url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false) {
		$this->url = $url;
		$this->timeout = $timeout;
		$this->redirects = $redirects;
		$this->headers = $headers;
		$this->useragent = $useragent;

		$this->method = SIMPLEPIE_FILE_SOURCE_REMOTE;

		if ( preg_match('/^http(s)?:\/\//i', $url) ) {
			$args = array(
				'timeout' => $this->timeout,
				'redirection' => $this->redirects,
			);

			if ( !empty($this->headers) )
				$args['headers'] = $this->headers;

			if ( SIMPLEPIE_USERAGENT != $this->useragent ) //Use default gp user agent unless custom has been specified
				$args['user-agent'] = $this->useragent;

			$res = gp_safe_remote_request($url, $args);

			if ( is_gp_error($res) ) {
				$this->error = 'gp HTTP Error: ' . $res->get_error_message();
				$this->success = false;
			} else {
				$this->headers = gp_remote_retrieve_headers( $res );
				$this->body = gp_remote_retrieve_body( $res );
				$this->status_code = gp_remote_retrieve_response_code( $res );
			}
		} else {
			$this->error = '';
			$this->success = false;
		}
	}
}

/**
 * Goatpress SimplePie Sanitization Class
 *
 * Extension of the SimplePie_Sanitize class to use KSES, because
 * we cannot universally count on DOMDocument being available
 *
 * @package Goatpress
 * @since 3.5.0
 */
class gp_SimplePie_Sanitize_KSES extends SimplePie_Sanitize {
	public function sanitize( $data, $type, $base = '' ) {
		$data = trim( $data );
		if ( $type & SIMPLEPIE_CONSTRUCT_MAYBE_HTML ) {
			if (preg_match('/(&(#(x[0-9a-fA-F]+|[0-9]+)|[a-zA-Z0-9]+)|<\/[A-Za-z][^\x09\x0A\x0B\x0C\x0D\x20\x2F\x3E]*' . SIMPLEPIE_PCRE_HTML_ATTRIBUTE . '>)/', $data)) {
				$type |= SIMPLEPIE_CONSTRUCT_HTML;
			}
			else {
				$type |= SIMPLEPIE_CONSTRUCT_TEXT;
			}
		}
		if ( $type & SIMPLEPIE_CONSTRUCT_BASE64 ) {
			$data = base64_decode( $data );
		}
		if ( $type & ( SIMPLEPIE_CONSTRUCT_HTML | SIMPLEPIE_CONSTRUCT_XHTML ) ) {
			$data = gp_kses_post( $data );
			if ( $this->output_encoding !== 'UTF-8' ) {
				$data = $this->registry->call( 'Misc', 'change_encoding', array( $data, 'UTF-8', $this->output_encoding ) );
			}
			return $data;
		} else {
			return parent::sanitize( $data, $type, $base );
		}
	}
}
