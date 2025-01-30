<?php

class webInterfaceTest extends Thinkery_TestCase {
	public function testThingVisibility() {
		$this->setupTestUsers();

		$anon_user = User::newAnonUser();
		$cookieless_user = User::getCookieless();

		$user = $this->testusers[0]->setCurrent();
		$thing = $user->things->add("test");

		$display_user = $this->testusers[0]->getDisplayUser();

		Http::$canUseNetwork = true;
		$content = Http::get($user->getPublicUrl());

		try {
			$content = Http::get($user->getPublicUrl());
			if (strpos($content, $thing->_id) === false) throw new ThingNotFoundException;
			$this->assertFalse(true, "thing should not have been found by cookieless user.");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by cookieless user as expected.");
		}

		try {
			$content = Http::get($user->getPublicUrl(), array(
				"Cookie: i=" . $anon_user->userId
			));
			if (strpos($content, $thing->_id) === false) throw new ThingNotFoundException;
			$this->assertFalse(true, "thing should not have been found by anonymous user.");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by anonymous user as expected.");
		}

		try {
			$content = Http::get($user->getPublicUrl(), array(
				"Cookie: i=" . $this->testusers[1]->userId . ";a=" . $this->auths[1]
			));
			if (strpos($content, $thing->_id) === false) throw new ThingNotFoundException;
			$this->assertFalse(true, "thing should not have been found by user b.");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by user b as expected.");
		}

		$user = $this->testusers[0]->setCurrent();
		$thing->makePublic();

		try {
			$content = Http::get($user->getPublicUrl());
			if (strpos($content, $thing->_id) === false) throw new ThingNotFoundException;
			$this->assertTrue(true, "thing found by cookieless user as expected.");
		} catch (ThingNotFoundException $e) {
			$this->assertFalse(true, "thing should have been found by cookieless user.");
		}

		try {
			$content = Http::get($user->getPublicUrl(), array(
				"Cookie: i=" . $anon_user->userId
			));
			if (strpos($content, $thing->_id) === false) throw new ThingNotFoundException;
			$this->assertTrue(true, "thing found by anonymous user as expected.");
		} catch (ThingNotFoundException $e) {
			$this->assertFalse(true, "thing should have been found by anonymous user.");
		}

		try {
			$content = Http::get($user->getPublicUrl(), array(
			));
			if (strpos($content, $thing->_id) === false) throw new ThingNotFoundException;
			$this->assertTrue(true, "thing found by user b as expected.");
		} catch (ThingNotFoundException $e) {
			$this->assertFalse(true, "thing should have been found by user b.");
		}

		$user = $this->testusers[0]->setCurrent();
		$thing->makePrivate();
		try {
			$content = Http::get($user->getPublicUrl());
			if (strpos($content, $thing->_id) === false) throw new ThingNotFoundException;
			$this->assertFalse(true, "thing should not have been found by cookieless user.");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by cookieless user as expected.");
		}

		try {
			$content = Http::get($user->getPublicUrl(), array(
				"Cookie: i=" . $anon_user->userId
			));
			if (strpos($content, $thing->_id) === false) throw new ThingNotFoundException;
			$this->assertFalse(true, "thing should not have been found by anonymous user.");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by anonymous user as expected.");
		}

		try {
			$content = Http::get($user->getPublicUrl(), array(
				"Cookie: i=" . $this->testusers[1]->userId . ";a=" . $this->auths[1]
			));
			if (strpos($content, $thing->_id) === false) throw new ThingNotFoundException;
			$this->assertFalse(true, "thing should not have been found by user b.");
		} catch (ThingNotFoundException $e) {
			$this->assertTrue(true, "thing not found by user b as expected.");
		}

	}
}
