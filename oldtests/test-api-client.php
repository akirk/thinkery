<?php

class ThinkeryApi extends OAuth2Client {
	public $debug = false;
	public function __construct( $code, 'https://api.thinkery.me/v1/', 'ThinkeryApiExplorer' ) {
		self::$CURL_OPTS[ CURLOPT_USERAGENT ] = $userAgent;
		parent::__construct( array(
			'base_uri'         => $base_uri,
			'client_id'        => ExternalApps::TEST_CLIENT_ID,
			'client_secret'    => ExternalApps::TEST_CLIENT_SECRET,
			'code'             => $code,
			'access_token_uri' => 'token',
			'authorize_uri'    => 'authorize',
			'cookie_support'   => false,
		) );
	}
}
