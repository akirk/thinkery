<?php

class syncTest extends Thinkery_TestCase {
	public function testArchived() {
		$user = User::newAnonUser()->setCurrent();
		$user->synclog->grouping = false;

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();
		$this->assertNotNull($session);

		$ret = $api->api('/sync', "POST", array("from" => 0));
		$this->assertArrayNotHasKey("thingCount", $ret);

		$sync_reply = $api->api('/sync', "POST", array(
			"from" => 0,
			"new_thing" => array(
			array(
				"local_id" => "my-1",
				"title" => "api-test 1",
				"archived" => 0
			), array(
				"local_id" => "my-2",
				"title" => "api-test 2",
				"archived" => false
			), array(
				"local_id" => "my-3",
				"title" => "api-test 3",
				"archived" => true
			), array(
				"local_id" => "my-4",
				"title" => "api-test 4",
				"archived" => "1"
			), array(
				"local_id" => "my-5",
				"title" => "api-test 5",
				"archived" => "true"
			), array(
				"local_id" => "my-6",
				"title" => "api-test 6",
				"archived" => "false"
			), array(
				"local_id" => "my-7",
				"title" => "api-test 7",
				"archived" => "fAlse"
			))
		));

		$ret = $api->api('/things/get');
		$this->assertCount(4, $ret);

		$ret = $api->api('/things/get', array('archived' => "include"));
		$this->assertCount(7, $ret);

		$ret = $api->api('/things/get', array('archived' => true));
		$this->assertCount(3, $ret);

		$ret = $api->api('/sync', "POST", array(
			"from" => $sync_reply["pos"],
			"changed_thing" => "[{\"title\":\"Ghjghj\",\"archived\":\"1\",\"_id\":\"" . $sync_reply["things"]["new"]["my-7"] . "\",\"local_id\":\"my-7\",\"tags\":\"\",\"date\":\"1362140732\"}]"
		));

		$ret = $api->api('/things/get');
		$this->assertCount(3, $ret);
	}

	public function testBadConnection() {
		$user = User::newAnonUser()->setCurrent();
		$user->synclog->grouping = false;

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();
		$this->assertNotNull($session);

		$thing = array(
			"local_id" => "my-1",
			"title" => "api-test",
			"html" => "new-content",
		);

		$ret = $api->api('/sync', 'POST', array(
			"new_thing" => json_encode($thing),
			"from" => 0,
		));
		$things = $user->things->all();
		$this->assertCount(1, $things);
		$this->assertEquals("new-content", $things[0]->html);
		$this->assertEmpty($ret["things"]["changed"]);

		$thing["html"] = "edited-content";

		$ret = $api->api('/sync', 'POST', array(
			"new_thing" => json_encode($thing),
			"from" => 0,
			));

		$things = $user->things->all();
		$this->assertCount(1, $things);
		$this->assertEquals("edited-content", $things[0]->html);
		$this->assertEmpty($ret["things"]["changed"]);

	}

	public function testInitialSync() {
		$user = User::newAnonUser()->setCurrent();
		$user->synclog->grouping = false;
		$firstThingTitle = "initial thing #tag1 #tag2";
		$user->things->add($firstThingTitle);
		$user->tags->get("tag2")->makeFavorite();

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();

		$this->assertNotNull($session);

		$thing = array(
			"local_id" => "my-1",
			"title" => "api-test",
		);

		$ret = $api->api('/sync', 'POST', array(
			"from" => 0,
			"new_thing" => json_encode($thing),
		));

		$this->assertArrayHasKey("changed", $ret["things"]);
		$this->assertEquals(2, $ret["thingCount"]);
	}

	public function testNewThenDelete() {
		$user = User::newAnonUser()->setCurrent();
		$user->synclog->grouping = false;
		$thing = $user->things->add("test");

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();
		$this->assertNotNull($session);

		$ret = $api->api('/sync', array("from" => 0));
		$this->assertArrayHasKey("changed", $ret["things"]);

		$this->assertEquals($thing->title, $ret["things"]["changed"][0]["title"]);

		$thing->title = "new";

		$ret = $api->api('/sync', 'POST', array(
			"deleted_thing" => array($thing->_id),
			"from" => $ret["pos"],
		));

		$this->assertArrayNotHasKey("things", $ret);
	}

	public function testJsonArray() {
		$user = User::newAnonUser()->setCurrent();
		$user->synclog->grouping = false;

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();
		$this->assertNotNull($session);

		$ret = $api->api('/sync', "POST", array("from" => 0));
		$this->assertArrayNotHasKey("thingCount", $ret);

		$new_things = array();
		$post = array();
		$j = 0;
		for ($i = 0; $i < 3; $i++) {
			$thing = array(
				"local_id" => "my-$i",
				"title" => "api-test $i",
				);
			$post["new_thing[$i]"] = $thing;

			$thing["local_id"] .= "x";
			$new_things[] = $thing;

			$thing["local_id"] .= "x";
			$new_things2[] = $thing;

			$thing["local_id"] .= "x";
			$new_things3[] = $thing;
		}

		$ret = $api->api('/sync', "POST", array("from" => $ret["pos"], "new_thing" => json_encode($new_things)));
		$this->assertEquals(3, $ret["thingCount"]);

		$ret = $api->api('/sync', "POST", array("from" => $ret["pos"], "new_thing[]" => json_encode($new_things2)));
		$this->assertEquals(3, $ret["thingCount"]);

		$post["from"] = $ret["pos"];

		$p = array();
		foreach ($post as $k => $v) {
			$p[$k] = json_encode($v);
		}
		$ret = $api->api('/sync', "POST", $p);
		$this->assertEquals(3, $ret["thingCount"]);

		$ret = $api->api('/sync', "POST", array("from" => $ret["pos"], "new_thing[]" => json_encode($new_things3[0])));
		$this->assertEquals(1, $ret["thingCount"]);

		$ret = $api->api('/sync', "POST", array("from" => $ret["pos"], "new_thing" => json_encode($new_things3[1])));
		$this->assertEquals(1, $ret["thingCount"]);

		$ret = $api->api('/things/get');
		$this->assertCount(11, $ret);

	}

	public function testSkip() {
		$user = User::newAnonUser()->setCurrent();
		$user->synclog->grouping = false;

		$time = time();
		for ($i = 20; $i > 0; $i--) {
			$user->things->add("thing $i", array("date" => $time--));
		}

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();
		$this->assertNotNull($session);

		$this->assertEquals(20, $this->getSyncThingCount($api, array("from" => 0)));
		$this->assertEquals(15, $this->getSyncThingCount($api, array("from" => 0, "skip" => 5)));
		$this->assertEquals(5, $this->getSyncThingCount($api, array("from" => 0, "skip" => 5, "limit" => 5)));
		$this->assertEquals(5, $this->getSyncThingCount($api, array("from" => 0, "limit" => 5)));

		$ret = $api->api("/sync", array("from" => 0, "skip" => 5, "limit" => 1));
		$this->assertEquals("thing 15", $ret["things"]["changed"][0]["title"]);
		$this->assertEquals(20, $ret["thingCount"]);

		$ret = $api->api("/sync", array("from" => 0, "skip" => 15, "limit" => 1));
		$this->assertEquals("thing 5", $ret["things"]["changed"][0]["title"]);

		$ret = $api->api("/sync", array("from" => 0, "skip" => 15));
		$this->assertEquals("thing 1", $ret["things"]["changed"][4]["title"]);
		$this->assertEquals(20, $ret["thingCount"]);

		$ret = $api->api("/sync", array("from" => 0, "skip" => 20));
		$this->assertEmpty($ret["things"]["changed"]);
	}

	function getSyncThingCount($api, $req) {
		$ret = $api->api("/sync", $req);
		$c = 0;
		if (!empty($ret["things"]["changed"])) $c += count($ret["things"]["changed"]);
		if (!empty($ret["things"]["deleted"])) $c += count($ret["things"]["deleted"]);
		return $c;
	}

	public function testTags() {
		$user = User::newAnonUser()->setCurrent();
		$user->synclog->grouping = false;

		$user->things->add("first thing #tag1 #tag2");
		$user->things->add("thing no 2 #tag2 #tag3");
		$user->tags->get("tag2")->makeFavorite();

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();
		$this->assertNotNull($session);

		$ret = $api->api('/tags/get');

		$this->assertArrayHasKey("tag2", $ret["favorite"]);
		$this->assertArrayHasKey("tag1", $ret["other"]);
		$this->assertEquals(1, $ret["other"]["tag3"]);
		$this->assertEquals(2, $ret["favorite"]["tag2"]);
	}

	public function testTagChanges() {
		$user = User::newAnonUser()->setCurrent();
		$user->synclog->grouping = false;

		$a_thing = $user->things->add("first thing #tag1 #tag2");
		$user->things->add("thing no 2 #tag2 #tag3");
		$user->tags->get("tag2")->makeFavorite();

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();
		$this->assertNotNull($session);

		$ret = $api->api('/tags/get');
		$this->assertArrayHasKey("pos", $ret);

		$pos = $ret["pos"];

		$user->things->add("thing no 2 #tag2 #tag3");

		$ret = $api->api('/tags/get', array("from" => $ret["pos"]));

		$this->assertArrayHasKey("tag2", $ret["favorite"]);
		$this->assertEquals(2, $ret["other"]["tag3"]);
		$this->assertEquals(3, $ret["favorite"]["tag2"]);

		$a_thing->saveChanges(array("title" => "first thing #tag1", "tags" => "tag1 tag2"));

		$ret = $api->api('/tags/get', array("from" => $ret["pos"]));
		$this->assertEquals(2, $ret["favorite"]["tag2"]);

		$user->tags->get("tag2")->makeNonFavorite();
		$ret = $api->api('/tags/get', array("from" => $ret["pos"]));
		$this->assertEquals(2, $ret["other"]["tag2"]);

	}

	public function testWrongTagSync() {
		$user = User::newAnonUser()->setCurrent();
		$user->synclog->grouping = false;

		$a_thing = $user->things->add("first thing #tag1 #tag2");
		$user->things->add("thing no 2 #tag2 #tag3");
		$user->tags->get("tag2")->makeFavorite();

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();
		$this->assertNotNull($session);

		$ret = $api->api('/sync', array("from" => 0));
		$this->assertArrayHasKey("changed", $ret["things"]);
		$this->assertEquals(2, $ret["thingCount"]);

		$this->assertArrayHasKey("tags", $ret);
		$this->assertArrayHasKey("tag2", $ret["tags"]);
		$this->assertArrayHasKey("favorite", $ret["tags"]["tag2"]);
		$this->assertTrue($ret["tags"]["tag2"]["favorite"]);

		$this->setExpectedException("OAuth2Exception", "parameter_invalid"); // parameter tag must be an array
		$api->api('/sync', 'POST', array(
			"tag" => "test",
		));
	}

	private function checkTagChange($api, $tag, $change, $favorite, $color, $todo) {
	    $ret = $api->api('/sync', 'POST', array(
	        "tag" => array(
	            $tag => $change,
	        ),
	    ));
		$this->assertArrayHasKey("tags", $ret);
		$this->assertArrayHasKey($tag, $ret["tags"]);

		if ($favorite) {
			$this->assertTrue($ret["tags"][$tag]["favorite"]);
		} else {
			$this->assertFalse($ret["tags"][$tag]["favorite"]);
		}

		if ($todo) {
			$this->assertEquals("strike", $ret["tags"][$tag]["todo"]);
		} else {
			$this->assertFalse($ret["tags"][$tag]["todo"]);
		}
		if ($color) {
			$this->assertEquals($color, $ret["tags"][$tag]["color"]);
		} else {
			$this->assertFalse($ret["tags"][$tag]["color"]);
		}
	}

	public function testSpecialTags() {
			$user = User::newAnonUser()->setCurrent();
			$user->synclog->grouping = false;

			$a_thing = $user->things->add("first thing #tag1 #tag2");
			$user->things->add("thing no 2 #tag2 #tag3");
			$user->tags->get("tag2")->makeFavorite();

			$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
			$session = $api->getSession();
			$this->assertNotNull($session);

			$ret = $api->api('/sync', array("from" => 0));
			$this->assertArrayHasKey("changed", $ret["things"]);
			$this->assertEquals(2, $ret["thingCount"]);

			$this->assertArrayHasKey("tags", $ret);
			$this->assertArrayHasKey("tag2", $ret["tags"]);
			$this->assertArrayHasKey("favorite", $ret["tags"]["tag2"]);
			$this->assertTrue($ret["tags"]["tag2"]["favorite"]);

			$this->checkTagChange($api, "tag2", array(
				"favorite" => "false"
			), false, false, false);

			$this->checkTagChange($api, "tag2", array(
				"favorite" => "true"
			), true, false, false);

			$color = "#ff0000";
			$this->checkTagChange($api, "tag2", array(
				"color" => $color
			), true, $color, false);

			$this->checkTagChange($api, "tag2", array(
				"color" => "false"
			), true, false, false);

			$this->checkTagChange($api, "tag2", array(
				"todo" => "true"
			), true, false, true);

			$this->checkTagChange($api, "tag2", array(
				"favorite" => "false"
			), false, false, true);

			$this->checkTagChange($api, "tag2", array(
				"todo" => "false"
			), false, false, false);

			$this->checkTagChange($api, "tag2", json_encode(array(
				"favorite" => false,
				"color" => "#8AFAFF",
				"todo" => false,
			)), false, "#8afaff", false);

			$this->checkTagChange($api, "tag2", json_encode(array(
				"favorite" => false,
				"color" => "#bbbbbb",
				"todo" => false,
			)), false, "#bbbbbb", false);

			$this->checkTagChange($api, "tag2", '{"favorite":false,"color":false,"todo":true}', false, false, true);
			$this->checkTagChange($api, "tag2", '{"debug":true,"favorite":false,"color":"#00FF2A","todo":true}', false, "#00ff2a", true);
		}

	public function testDuplicateDetection() {
		$user = User::newAnonUser()->setCurrent();

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();
		$this->assertNotNull($session);

		$ret = $api->api('/thing/add', 'POST', array(
			"title" => "http://thinkery.me/",
		));
		$id = $ret["_id"];

		$ret = $api->api('/thing/add', 'POST', array(
			"title" => "http://thinkery.me/",
			"prevent_duplicate" => true,
		));
		// method 1: duplicate prevented
		$this->assertEquals($id, $ret["_id"]);
		// method 1: duplicate announced
		$this->assertArrayHasKey("duplicate", $ret);
		$this->assertTrue($ret["duplicate"]);

		$ret = $api->api('/thing/add', 'POST', array(
			"title" => "http://thinkery.me/",
			"prevent_duplicate" => 1,
		));
		// method 2: duplicate prevented
		$this->assertEquals($id, $ret["_id"]);
		// method 2: duplicate announced
		$this->assertArrayHasKey("duplicate", $ret);
		$this->assertTrue($ret["duplicate"]);

		$ret = $api->api('/thing/add', 'POST', array(
			"title" => "http://thinkery.me/",
			"prevent_duplicate" => false,
		));
		// method 1: duplicate was inserted as intended
		$this->assertNotEquals($id, $ret["_id"]);

		$ret = $api->api('/thing/add', 'POST', array(
			"title" => "http://thinkery.me/",
		));
		// method 2: duplicate was inserted as intended
		$this->assertNotEquals($id, $ret["_id"]);

		$ret = $api->api('/thing/add', 'POST', array(
			"title" => "http://thinkery.me/",
			"prevent_duplicate" => 0,
		));
		// method 3: duplicate was inserted as intended
		$this->assertNotEquals($id, $ret["_id"]);

	}

	public function testThingsGetAddChangeDelete() {
		$user = User::newAnonUser()->setCurrent();
		$user->synclog->grouping = false;

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();
		$this->assertNotNull($session);

		$ret = $api->api('/things/get');
		$this->assertEmpty($ret);

		$ret = $api->api('/thing/add', 'POST', array(
			"title" => "first thing",
		));

		$this->assertArrayHasKey("_id", $ret);
		$id = $ret["_id"];

		$ret = $api->api('/things/get');
		$contained = false;
		foreach ($ret as $thing) {
			if (!isset($thing["_id"]) || $thing["_id"] != $id) continue;
			$contained = true;
			break;
		}
		// thing is contained in things/get
		$this->assertTrue($contained);

		$newtitle = "first thing changed";
		$ret = $api->api('/thing/change', 'POST', array(
			"_id" => $id,
			"title" => $newtitle,
		));

		// thing was changed
		$this->assertEquals($ret["title"], $newtitle);

		try {
			$ret = $api->api('/thing/change', 'POST', array(
				"_id" => "non-existant",
			));
			$this->assertTrue(false, "could change non-existant thing");
		} catch (OAuth2Exception $e) {
			$this->assertTrue(true, "changing non-existant thing failed as expected");
		}

		$ret = $api->api('/things/get');
		$contained = false;
		foreach ($ret as $thing) {
			if (!isset($thing["_id"]) || $thing["_id"] != $id) continue;
			if (!isset($thing["_id"]) || $thing["title"] != $newtitle) continue;
			$contained = true;
			break;
		}
		// changed thing is in things/get
		$this->assertTrue($contained);

		$ret = $api->api('/thing/delete', 'POST', array(
			"_id" => $id,
		));
		// thing was deleted
		$this->assertEquals($ret["deleted_thing"], $id);

		$ret = $api->api('/things/get');
		$this->assertEmpty($ret);

		try {
			$ret = $api->api('/thing/delete', 'POST', array(
				"_id" => $id,
			));
			$this->assertTrue(false, "could delete non-existant thing");
		} catch (OAuth2Exception $e) {
			$this->assertTrue(true, "deletion non-existant thing failed as expected");
		}

	}


}
