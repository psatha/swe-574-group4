<?php

class APP_Updater {
	const WP_URL = 'http://api.wordpress.org/themes/update-check/';
	const APP_URL = 'http://api.appthemes.com/themes/update-check/1.0/';

	private static $themes;

	function init() {
		add_filter( 'http_request_args', array( __CLASS__, 'exclude_themes' ), 10, 2 );
		add_filter( 'http_response', array( __CLASS__, 'check_updates' ), 10, 3 );
		add_action( 'all_admin_notices', array( __CLASS__, 'display_warning' ) );
	}

	function exclude_themes( $r, $url ) {
		if ( 0 === strpos( $url, self::WP_URL ) ) {
			$themes = unserialize( $r['body']['themes'] );

			self::$themes['current_theme'] = $themes['current_theme'];

			foreach ( array( 'qualitycontrol', 'classipress', 'clipper', 'jobroller' ) as $name ) {
				if ( !isset( $themes[ $name ] ) )
					continue;

				self::$themes[ $name ] = $themes[ $name ];
				unset( $themes[ $name ] );
			}

			$r['body']['themes'] = serialize( $themes );
		}

		return $r;
	}

	function check_updates( $response, $args, $url ) {
		if ( 0 === strpos( $url, self::WP_URL ) ) {
			$args['body'] = array( 'themes' => serialize( self::$themes ) );
			$raw_response = wp_remote_post( self::APP_URL, $args );

			if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) )
				return $response;

			$response['body'] = serialize( array_merge(
				unserialize( $response['body'] ),
				unserialize( wp_remote_retrieve_body( $raw_response ) )
			) );
		}

		return $response;
	}

	function display_warning() {
		static $themes_update;

		if ( !current_user_can( 'update_themes' ) )
			return;

		if ( !isset( $themes_update ) )
			$themes_update = get_site_transient( 'update_themes' );

		$stylesheet = get_stylesheet();
		if ( isset( $themes_update->response[ $stylesheet ] ) ) {
			global $pagenow;

			if ( in_array( $pagenow, array( 'themes.php', 'update-core.php' ) ) ) : ?>
				<div id="message" class="error">
					<p><?php echo sprintf( __( '<strong>IMPORTANT</strong>: If you have made any modifications to the AppThemes files, they will be overwritten if you proceed with the automatic update. Those with modified theme files should do a manual update instead. Visit your <a href="%1$s" target="_blank">customer dashboard</a> to download the latest version.', 'appthemes' ), 'http://www.appthemes.com/cp/member.php' ); ?></p>
				</div>
<?php endif;
		}
	}
}

APP_Updater::init();
