<?php

class webhookTest extends Thinkery_TestCase {
	public function testBasicFunctionality() {
		$user = User::newAnonUser()->setCurrent();

		$webhook = "http://" . Config::get("server") . "/test.php";
		$webhookHash = dechex(sprintf("%u", crc32($webhook)));

		$observer = $this->getMock("Observer", array("called"));
		$observer->expects($this->once())->method("called")->with($this->equalTo("fastPostJson"), $this->equalTo($webhook));
		Http::clearObservers();
		Http::attach($observer);

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();
		$this->assertNotNull($session);

		$ret = $api->api("webhook/register", "POST", array("target_url" => $webhook, "event" => "new_thing"));

		$this->assertArrayHasKey("registered", $ret);
		$this->assertTrue($ret["registered"]);

		$user->reload();
		$this->assertNotEmpty($user->webhooks);
		$user->things->add("test");

		$userdata = UserData::fromUserId($user->userId);
		$this->assertArrayHasKey(ExternalApps::TEST_CLIENT_ID, $userdata["webhooks"]);
		$this->assertArrayHasKey($webhookHash, $userdata["webhooks"][ExternalApps::TEST_CLIENT_ID]);
		$this->assertArrayHasKey(SyncLog::NEW_THING, $userdata["webhooks"][ExternalApps::TEST_CLIENT_ID][$webhookHash]);
		$this->assertEquals($webhook, $userdata["webhooks"][ExternalApps::TEST_CLIENT_ID][$webhookHash][SyncLog::NEW_THING]);

		$ret = $api->api("webhook/unregister", "POST", array("target_url" => $webhook, "event" => "new_thing"));

		$this->assertArrayHasKey("unregistered", $ret);
		$this->assertTrue($ret["unregistered"]);

		$user->reload();
		$this->assertEmpty($user->webhooks);

		$userdata = UserData::fromUserId($user->userId);
		$this->assertArrayNotHasKey("webhooks", $userdata);

		$user->delete();
	}

	public function testJsonPost() {
		$user = User::newAnonUser()->setCurrent();

		$webhook = "http://" . Config::get("server") . "/test.php";
		$webhookHash = dechex(sprintf("%u", crc32($webhook)));

		$observer = $this->getMock("Observer", array("called"));
		$observer->expects($this->once())->method("called")->with($this->equalTo("fastPostJson"), $this->equalTo($webhook));
		Http::clearObservers();
		Http::attach($observer);

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();
		$this->assertNotNull($session);

		$ret = $api->api("webhook/register", "JSON-POST", array("target_url" => $webhook, "event" => "new_thing"));

		$this->assertArrayHasKey("registered", $ret);
		$this->assertTrue($ret["registered"]);

		$user->reload();
		$this->assertNotEmpty($user->webhooks);
		$user->things->add("test");

		$userdata = UserData::fromUserId($user->userId);
		$this->assertArrayHasKey(ExternalApps::TEST_CLIENT_ID, $userdata["webhooks"]);
		$this->assertArrayHasKey($webhookHash, $userdata["webhooks"][ExternalApps::TEST_CLIENT_ID]);
		$this->assertArrayHasKey(SyncLog::NEW_THING, $userdata["webhooks"][ExternalApps::TEST_CLIENT_ID][$webhookHash]);
		$this->assertEquals($webhook, $userdata["webhooks"][ExternalApps::TEST_CLIENT_ID][$webhookHash][SyncLog::NEW_THING]);

		$ret = $api->api("webhook/unregister", "JSON-POST", array("target_url" => $webhook, "event" => "new_thing"));

		$this->assertArrayHasKey("unregistered", $ret);
		$this->assertTrue($ret["unregistered"]);

		$user->reload();
		$this->assertEmpty($user->webhooks);

		$userdata = UserData::fromUserId($user->userId);
		$this->assertArrayNotHasKey("webhooks", $userdata);

		$user->delete();
	}
}
