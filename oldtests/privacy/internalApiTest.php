<?php

class internalApiTest extends Thinkery_TestCase {
	public function testThingVisibility() {
		$this->setupTestUsers();

		$anon_user = User::newAnonUser();
		$cookieless_user = User::getCookieless();

		$user = $this->testusers[0]->setCurrent();
		$thing = $user->things->add("test");

		$display_user = $this->testusers[0]->getDisplayUser();

		try {
			$cookieless_user->setCurrent();
			$display_user->things->get($thing->_id);
			$this->assertFalse(true, "thing should not have been found by cookieless user.");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by cookieless user as expected.");
		}

		try {
			$anon_user->setCurrent();
			$display_user->things->get($thing->_id);
			$this->assertFalse(true, "thing should not have been found by anonymous user.");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by anonymous user as expected.");
		}

		try {
			$this->testusers[1]->setCurrent();
			$display_user->things->get($thing->_id);
			$this->assertFalse(true, "thing should not have been found by user b.");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by user b as expected.");
		}

		$user = $this->testusers[0]->setCurrent();
		$thing->makePublic();

		try {
			$cookieless_user->setCurrent();
			$display_user->things->get($thing->_id);
			$this->assertTrue(true, "thing found by cookieless user as expected.");
		} catch (ThingNotFoundException $e) {
			$this->assertFalse(true, "thing should have been found by cookieless user.");
		}

		try {
			$anon_user->setCurrent();
			$display_user->things->get($thing->_id);
			$this->assertTrue(true, "thing found by anonymous user as expected.");
		} catch (ThingNotFoundException $e) {
			$this->assertFalse(true, "thing should have been found by anonymous user.");
		}

		try {
			$this->testusers[1]->setCurrent();
			$display_user->things->get($thing->_id);
			$this->assertTrue(true, "thing found by user b as expected.");
		} catch (ThingNotFoundException $e) {
			$this->assertFalse(true, "thing should have been found by user b.");
		}

		$user = $this->testusers[0]->setCurrent();
		$thing->makePrivate();
		try {
			$cookieless_user->setCurrent();
			$display_user->things->get($thing->_id);
			$this->assertFalse(true, "thing should not have been found by cookieless user.");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by cookieless user as expected.");
		}

		try {
			$anon_user->setCurrent();
			$display_user->things->get($thing->_id);
			$this->assertFalse(true, "thing should not have been found by anonymous user.");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by anonymous user as expected.");
		}

		try {
			$this->testusers[1]->setCurrent();
			$display_user->things->get($thing->_id);
			$this->assertFalse(true, "thing should not have been found by user b.");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by user b as expected.");
		}

	}
}
