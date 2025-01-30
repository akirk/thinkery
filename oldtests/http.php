<?php
class Http extends BaseHttp {
	private static $cookies = array(), $observers = array();

	public static function clearObservers() {
		static::$observers = array();
	}

	public static function attach(Observer $observer) {
		static::$observers[] = $observer;
	}

	protected static function notify($method, $argument = null) {
		foreach (static::$observers as $observer) {
			$observer->called($method, $argument);
		}
	}

	public static function setPermanentCookie($key, $value, $path = "/", $host = false) {
		self::$cookies[$key] = $value;
	}

	public static function setSessionCookie($key, $value, $path = "/", $host = false) {
		self::$cookies[$key] = $value;
	}

	public static function deleteCookie($key, $path = "/", $host = false) {
		unset(self::$cookies[$key]);
	}

	public static function deleteAllCookies() {
		self::$cookies = array();
	}

	public static function getCookie($key) {
		if (!isset(self::$cookies[$key])) throw new CookieNotSetException($key);
		return self::$cookies[$key];
	}

	public static function dumpCookies() {
		var_dump(self::$cookies);
	}

	public static function getIP() {
		return "127.0.0.1";
	}

	public static function fastPostJson($url, $postData = array(), $connect_timeout = 1, $timeout = 1) {
		static::notify("fastPostJson", $url);

		return parent::fastPostJson($url, $postData, $connect_timeout, $timeout);
	}
}
