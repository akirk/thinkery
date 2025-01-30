<?php
define("UNIT_TEST", 1);

include_once __DIR__ . "/observer.php";
// include_once __DIR__ . "/http.php";
include_once __DIR__ . "test-api-client.php";

Config::set("memcache", false);
unset($memcache);

class Thinkery_TestCase extends PHPUnit_Framework_TestCase {
	protected $username = "u_123";
	protected $email = "e@mail.com";
	protected $password = "password";
	protected $testusers = array(), $auths = array();

	protected $backupGlobalsBlacklist = array("mongo");

	public function tearDown() {
		Http::deleteAllCookies();
		try {
			$user = User::getCurrentUser();
			$user->delete();
		} catch (Exception $e) {
		}

		foreach ($this->testusers as $user) {
			$user->delete();
		}
		$this->testusers = array();
		$this->auths = array();
	}

	protected function setupTestUsers() {
		Config::set("usernames.reserved", "");
		Config::set("usernames.banned", "");

		foreach (array("user-a", "user-b") as $username) {
			$data = UserData::fromUsername($username);
			if ($data) {
				$user = new TestUser($data);
				$user->delete();
			}
			$user = TestUser::newAnonUser();
			$user = $user->register($username, "password", $username . "@thinkery.me");
			$this->testusers[] = $user;
			$this->auths[] = Http::getCookie("a");
		}

		$this->testusers[0]->setCurrent();
	}
}
