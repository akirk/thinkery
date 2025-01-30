<?php

class extractTest extends Thinkery_TestCase {
	public function testTag() {
		$this->assertEquals(array("test2", "test"), Tags::extract("hallo #test juhu #test2"));
	}

	public function testUser() {
		if (!SHARING_ENABLED) {
			$this->markTestIncomplete("Sharing not enabled.");
		}
		$this->assertEquals(array("user-a", "alex+", "nader"), User::extract("hallo @nader @alex+ @user-a #test"));
	}
}
