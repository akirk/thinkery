<?php

class shareThingTest extends Thinkery_TestCase {
	public function setUp() {
		if (!SHARING_ENABLED) {
			$this->markTestSkipped("Sharing not enabled.");
		}
	}

	public function testUsernameToId() {
		$this->setupTestUsers();
		$this->assertEquals(User::UsernameToId($this->testusers[0]->username), $this->testusers[0]->userId);
		$this->assertEquals(User::UsernameToId($this->testusers[0]->userId), $this->testusers[0]->userId);
	}

	public function testTitle() {
		$this->setupTestUsers();
		$user = $this->testusers[0]->setCurrent();
		$thing = $user->things->add("test @" . $this->testusers[1]->username);
		$this->checkSharedThing($thing, "test");
	}

	public function testTitleRW() {
		$this->setupTestUsers();
		$user = $this->testusers[0]->setCurrent();
		$thing = $user->things->add("test @" . $this->testusers[1]->username . "+");
		$this->checkSharedThing($thing, "test", true);

		$user = $this->testusers[1]->setCurrent();
		$this->assertTrue($user->things->get($thing->_id)->canEdit);

		$user = $this->testusers[0]->setCurrent();
		$thing->refresh();
		$thing->share($this->testusers[1]->username, false);

		$user = $this->testusers[1]->setCurrent();
		$this->assertFalse($user->things->get($thing->_id)->canEdit);
	}

	public function testTitleChangeRW() {
		$this->setupTestUsers();
		$user = $this->testusers[0]->setCurrent();
		$thing = $user->things->add("test @" . $this->testusers[1]->username);
		$this->checkSharedThing($thing, "test");

		$user = $this->testusers[1]->setCurrent();
		$this->assertFalse($user->things->get($thing->_id)->canEdit);

		$user = $this->testusers[0]->setCurrent();
		$thing->refresh();
		$thing->saveChanges(array("title" => "test", "tags" => "@" . $this->testusers[1]->username . "+"));

		$user = $this->testusers[1]->setCurrent();
		$this->assertTrue($user->things->get($thing->_id)->canEdit);
	}

	public function testTitleLater() {
		$this->setupTestUsers();
		$user = $this->testusers[0]->setCurrent();
		$thing = $user->things->add("test");
		$thing->saveChanges(array("title" => "test @" . $this->testusers[1]->username));
		$this->checkSharedThing($thing, "test");
	}

	public function testTagLater() {
		$this->setupTestUsers();
		$user = $this->testusers[0]->setCurrent();
		$thing = $user->things->add("test");
		$thing->saveChanges(array("title" => "test", "tags" => "@" . $this->testusers[1]->username));
		$this->checkSharedThing($thing, "test");
	}

	public function testAddTag() {
		$this->setupTestUsers();
		$user = $this->testusers[0]->setCurrent();
		$thing = $user->things->add("test");
		$thing->addTag("@" . $this->testusers[1]->username);
		$this->checkSharedThing($thing, "test");
	}

	public function testUnshare() {
		$this->setupTestUsers();

		$user = $this->testusers[0]->setCurrent();
		$thing = $user->things->add("test");

		$thing->share($this->testusers[1]->username);
		$this->checkSharedThing($thing, "test");

		$user = $this->testusers[0]->setCurrent();
		$thing->unshare($this->testusers[1]->username);

		$this->assertEquals(0, count($this->testusers[1]->things->all()));
	}

	public function testModify() {
		$this->setupTestUsers();

		$user = $this->testusers[0]->setCurrent();
		$thing = $user->things->add("test");

		$thing->share($this->testusers[1]->username, true);
		$this->checkSharedThing($thing, "test", true);

		$user = $this->testusers[1]->setCurrent();
		$thing_b = $user->things->get($thing->_id);
		$thing_b->saveChanges(array("title" => "hello"));

		$user = $this->testusers[0]->setCurrent();
		$thing_a = $user->things->get($thing->_id);

		$this->assertEquals("hello", $thing_a->title);
	}

	public function testDeleteThingSharedWithMe() {
		$this->setupTestUsers();

		$user = $this->testusers[0]->setCurrent();
		$thing = $user->things->add("test");

		$thing->share($this->testusers[1]->username, true);
		$this->checkSharedThing($thing, "test", true);

		$user = $this->testusers[1]->setCurrent();
		$thing_b = $user->things->get($thing->_id);
		$thing_b->delete();

		$user = $this->testusers[0]->setCurrent();
		$thing_a = $user->things->get($thing->_id);

		$this->assertEquals("test", $thing_a->title);
	}

	public function testDeleteMySharedThing() {
		$this->setupTestUsers();

		$user = $this->testusers[0]->setCurrent();
		$thing = $user->things->add("test");

		$thing->share($this->testusers[1]->username, true);
		$this->checkSharedThing($thing, "test", true);

		$user = $this->testusers[0]->setCurrent();
		$thing_1 = $user->things->get($thing->_id);
		$thing_1->delete();

		$user = $this->testusers[0]->setCurrent();
		try {
			$thing_a = $user->things->get($thing->_id);
			$this->assertFalse(true, "thing should not be found by user a");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by user a as expected");
		}

		$user = $this->testusers[1]->setCurrent();
		try {
			$thing_b = $user->things->get($thing->_id);
			$this->assertFalse(true, "thing should not be found by user b");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by user b as expected");
		}
	}

	protected function checkSharedThing($thing, $title, $rw = false) {
		$rw = $rw ? "+" : "";

		$thing->refresh();
		$this->assertContains($this->testusers[1]->username, $thing->offered, "thing offered to user_b");

		$this->testusers[1]->setCurrent();
		$this->assertEquals("test", $thing->title);
		$this->assertEquals(0, count($this->testusers[1]->things->all()));

		$notifications = $this->testusers[1]->notifications->getAll();
		$this->assertEquals(1, count($notifications));

		$n = reset($notifications);
		$this->assertEquals($n->type, MESSAGE_SHARED_THING);
		$this->assertEquals($n->thing->_id, $thing->_id);

		$this->testusers[1]->sharing->acceptThing($n->thing->_id, DisplayUser::fromUserId($n->from));

		$this->assertEquals(1, count($this->testusers[1]->things->all()));
		$this->assertEquals(0, count($this->testusers[1]->notifications->getAll()));

		$this->testusers[0]->setCurrent();
		$this->assertEquals("@" . $this->testusers[1]->username . $rw, $this->testusers[0]->things->get($thing->_id)->tags);
		$thing->refresh();
		$this->assertNotContains($this->testusers[1]->username, $thing->offered, "thing offered to user_b");

		$this->testusers[1]->setCurrent();
		$this->assertEquals("@" . $this->testusers[0]->username, $this->testusers[1]->things->get($thing->_id)->tags);
	}

}
