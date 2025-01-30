<?php

class RegisterTest extends PHPUnit_Framework_TestCase {
	private $username = "u_123";
	private $email = "e@mail.com";
	private $reason = "test";
	protected $backupGlobalsBlacklist = array("mongo");

	protected function setUp() {
		Http::deleteAllCookies();

		global $mongo;
		$mongo->user->remove(array("email" => $this->email));
		$mongo->user->remove(array("username" => $this->username));

		User::init();
	}

	public function testRegisterLoggedIn() {
		$user = TestUser::getCurrentUserOrCreateNewOne($this->reason);

		$this->assertEquals(false, $user->isLoggedIn());
		$this->assertEquals(true, $user->hasCookie());

		$user->register($this->username, "password", $this->email);
		$user = TestUser::getCurrentUser();

		$this->assertEquals(true, $user->isLoggedIn());
		$this->assertEquals(true, $user->hasCookie());
	}

	public function testRegisterBannedUsername() {
		$user = TestUser::getCurrentUserOrCreateNewOne($this->reason);

		Config::set("usernames.reserved", "");
		Config::set("usernames.banned", "thinkery");

		$this->setExpectedException("UsernameBannedException");
		$user->register("thinkery", "", $this->email);
	}

	public function testRegisterBannedUsernamePart() {
		$user = TestUser::getCurrentUserOrCreateNewOne($this->reason);

		Config::set("usernames.reserved", "");
		Config::set("usernames.banned", "think");

		$this->setExpectedException("UsernameBannedException");
		$user->register("thinkery", "", $this->email);
	}

	public function testRegisterReservedUsername() {
		$user = TestUser::getCurrentUserOrCreateNewOne($this->reason);

		Config::set("usernames.reserved", "thinkery");
		Config::set("usernames.banned", "");

		$this->setExpectedException("UsernameReservedException");
		$user->register("thinkery", "", $this->email);
	}

	public function testRegisterInvalidUsername() {
		$user = TestUser::getCurrentUserOrCreateNewOne($this->reason);

		$this->setExpectedException("UsernameSyntaxException");
		$user->register("alex!", "password", $this->email);
	}

	public function testRegisterTooShortUsername() {
		$user = TestUser::getCurrentUserOrCreateNewOne($this->reason);

		$this->setExpectedException("UsernameSyntaxException");
		$user->register("ab", "password", $this->email);
	}

	public function testRegisterUsernameAlreadyTaken() {
		$user = TestUser::getCurrentUserOrCreateNewOne($this->reason);

		$user->register($this->username, "password", $this->email);

		Http::deleteAllCookies();
		TestUser::init();
		$user = TestUser::getCurrentUserOrCreateNewOne($this->reason);

		$this->setExpectedException("UsernameTakenException");
		$user->register($this->username, "password", $this->email . "2");
	}

	public function testRegisterEmptyPassword() {
		$user = TestUser::getCurrentUserOrCreateNewOne($this->reason);

		$this->setExpectedException("InvalidPasswordException");
		$user->register($this->username, "", $this->email);
	}

	public function testRegisterWrongEmail() {
		$user = TestUser::getCurrentUserOrCreateNewOne($this->reason);

		$this->setExpectedException("EmailSyntaxException");
		$user->register($this->username, "password", "a@b");
	}

	public function testRegisterEmptyEmail() {
		$user = TestUser::getCurrentUserOrCreateNewOne($this->reason);

		$this->setExpectedException("EmailSyntaxException");
		$user->register($this->username, "password", "");
	}
}
