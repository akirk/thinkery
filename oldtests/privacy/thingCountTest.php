<?php

class thingCountTest extends Thinkery_TestCase {
	public function testNoPublicThings() {
		$this->setupTestUsers();
		$user_a = $this->testusers[0]->setCurrent();

		$user_a->things->add("test");
		$user_a->things->add("test2");

		$user = User::getCookieless()->setCurrent();
		$this->assertCount(0, $user_a->things->all());
		$this->assertEquals(array("count" => 0, "shared" => 0), $user_a->things->count());
	}

	public function testPublicThings() {
		$this->setupTestUsers();
		$user_a = $this->testusers[0]->setCurrent();

		$user_a->things->add("test");
		$user_a->things->add("test2")->makePublic();

		$user = User::getCookieless()->setCurrent();
		$this->assertCount(1, $user_a->things->all());
		$this->assertEquals(array("count" => 1, "shared" => 0), $user_a->things->count());
	}
}
