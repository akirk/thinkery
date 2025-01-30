<?php include __DIR__ . "/common.php";

$username_a = "user-a";
$data = UserData::fromUsername($username_a);
if ($data) {
	$user_a = new TestUser($data);
	$user_a->delete();
}
$username_b = "user-b";
$data = UserData::fromUsername($username_b);
if ($data) {
	$user_b = new TestUser($data);
	$user_b->delete();
}
