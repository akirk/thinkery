<?php

class deviceTest extends Thinkery_TestCase {

	public function testBasicFunctionality() {
		$user = User::newAnonUser()->setCurrent();
		$myDeviceId = "test-id";

		$api = new ThinkeryApi($user->getApiAuthCode(ExternalApps::TEST_CLIENT_ID), Config::get("test-api"), "ThinkeryUnitTest");
		$session = $api->getSession();
		$this->assertNotNull($session);

		$ret = $api->api("device/register", "POST", array("type" => "gcm", "id" => $myDeviceId));

		$this->assertArrayHasKey("registered", $ret);
		$this->assertTrue($ret["registered"]);

		$userdata = UserData::fromUserId($user->userId);
		$this->assertArrayHasKey($myDeviceId, $userdata["deviceIds"][ExternalApps::TEST_CLIENT_ID]["gcm"]);

		$ret = $api->api("device/unregister", "POST", array("type" => "gcm", "id" => $myDeviceId));

		$this->assertArrayHasKey("unregistered", $ret);
		$this->assertTrue($ret["unregistered"]);

		$userdata = UserData::fromUserId($user->userId);
		$this->assertArrayNotHasKey("deviceIds", $userdata);

	}
}
