<?php include __DIR__ . "/common.php";
Config::set("memcache", false);
Config::set("usernames.reserved", "");
Config::set("usernames.banned", "");

$username_a = "user-a";
$data = UserData::fromUsername($username_a);
if ($data) {
	$user_a = new TestUser($data);
	$user_a->delete();
}
$user_a = TestUser::newAnonUser();
$user_a = $user_a->register($username_a, "password", $username_a . "@thinkery.me");
$user_a_auth = Http::getCookie("a");

$username_b = "user-b";
$data = UserData::fromUsername($username_b);
if ($data) {
	$user_b = new TestUser($data);
	$user_b->delete();
}
$user_b = TestUser::newAnonUser();
$user_b = $user_b->register($username_b, "password", $username_b . "@thinkery.me");
$user_b_auth = Http::getCookie("a");

function switchToUser(User $_user) {
	global $user, $display_user;
	$user = $_user;
	$display_user = $user->getDisplayUser();
}

switchToUser($user_a);
