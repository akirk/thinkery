<?php

class ThinkeryApiForRegister extends OAuth2Client {
	public $debug = false;
	public function __construct($username, $password, $email, $register_token) {
		self::$CURL_OPTS[CURLOPT_USERAGENT] = "ThinkeryUnitTest";
		parent::__construct(array(
			"base_uri" => Config::get("test-api"),
			"client_id" => ExternalApps::TEST_CLIENT_ID,
			"client_secret" => ExternalApps::TEST_CLIENT_SECRET,
			"username" => $username,
			"password" => $password,
			"email" => $email,
			"register_token" => $register_token,
			"access_token_uri" => "token",
			"authorize_uri" => "authorize",
			"cookie_support" => false,
		));

	}
}

class ApiRegisterTest extends Thinkery_TestCase {
	protected function setUp() {
		parent::setUp();
		Http::deleteAllCookies();

		global $mongo;
		$mongo->user->remove(array("email" => $this->email));
		$mongo->user->remove(array("username" => $this->username));
	}

	public function testBasicFunctionality() {
		$api = new ThinkeryApiForRegister($this->username, $this->password, $this->email, Crypto::getRegisterToken($this->username, $this->password, $this->email));

		$session = $api->getSession();
		$this->assertNotNull($session);

		$ret = $api->api("test");
		$this->assertArrayHasKey("user", $ret);
		$this->assertEquals($ret["user"], $this->username);
	}

	public function testUsernameTaken() {
		$api = new ThinkeryApiForRegister($this->username, $this->password, $this->email, Crypto::getRegisterToken($this->username, $this->password, $this->email));

		$session = $api->getSession();
		$this->assertNotNull($session);

		$api = new ThinkeryApiForRegister($this->username, $this->password, $this->email, Crypto::getRegisterToken($this->username, $this->password, $this->email));

		$this->setExpectedException("UsernameTakenException");
		$session = $api->getSession();
	}

	public function testEmailTaken() {
		$api = new ThinkeryApiForRegister($this->username, $this->password, $this->email, Crypto::getRegisterToken($this->username, $this->password, $this->email));

		$session = $api->getSession();
		$this->assertNotNull($session);

		$username = $this->username . "2";
		$api = new ThinkeryApiForRegister($username, $this->password, $this->email, Crypto::getRegisterToken($username, $this->password, $this->email));

		$this->setExpectedException("EmailTakenException");
		$session = $api->getSession();
	}

	public function testInvalidUsername() {
		$username = "alex!";
		$api = new ThinkeryApiForRegister($username, $this->password, $this->email, Crypto::getRegisterToken($username, $this->password, $this->email));

		$this->setExpectedException("UsernameSyntaxException");
		$session = $api->getSession();
	}

	public function testInvalidPassword() {
		$password = "1";
		$api = new ThinkeryApiForRegister($this->username, $password, $this->email, Crypto::getRegisterToken($this->username, $password, $this->email));

		$this->setExpectedException("InvalidPasswordException");
		$session = $api->getSession();
	}

	public function testInvalidEmail() {
		$email = "invalid";
		$api = new ThinkeryApiForRegister($this->username, $this->password, $email, Crypto::getRegisterToken($this->username, $this->password, $email));

		$this->setExpectedException("EmailSyntaxException");
		$session = $api->getSession();
	}

	public function testInvalidToken() {
		$api = new ThinkeryApiForRegister($this->username, $this->password, $this->email, "wrong");

		$this->setExpectedException("OAuth2Exception");
		$session = $api->getSession();
	}
}
