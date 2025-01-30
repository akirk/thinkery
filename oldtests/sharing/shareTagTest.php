<?php

class shareTagTest extends Thinkery_TestCase {
	public function setUp() {
		if (!SHARING_ENABLED) {
			$this->markTestSkipped("Sharing not enabled.");
		}
	}

	public function testShareTag() {
		$this->setupTestUsers();

		$shared_tag = "test";

		$user = $this->testusers[0]->setCurrent();
		$thing_a = $user->things->add("test #" . $shared_tag);

		$user = $this->testusers[1]->setCurrent();
		$this->assertEquals(0, count($user->things->all()));

		$user = $this->testusers[0]->setCurrent();
		$user->sharing->shareTag($shared_tag, $this->testusers[1]->username);

		$user = $this->testusers[1]->setCurrent();
		$notifications = $this->testusers[1]->notifications->getAll();
		$this->assertEquals(1, count($notifications));

		$n = reset($notifications);
		$this->assertEquals($n->type, MESSAGE_SHARED_TAG);
		$this->assertEquals($n->tag, $shared_tag);

		$this->testusers[1]->sharing->acceptTag($n->tag, DisplayUser::fromUserId($n->from));

		$this->assertEquals(1, count($this->testusers[1]->things->all()));
		$this->assertEquals(0, count($this->testusers[1]->notifications->getAll()));

		$user = $this->testusers[0]->setCurrent();
		$thing_a = $user->things->add("test2 #" . $shared_tag);

		$user = $this->testusers[1]->setCurrent();
		$this->assertEquals(2, count($this->testusers[1]->things->all()));

	}
}
