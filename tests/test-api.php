<?php
/**
 * Class Friends_NoticiationTest
 *
 * @package Friends
 */

/**
 * Test the Notifications
 */
class Thinkery_API_Test extends WP_UnitTestCase {
	/**
	 * Current User ID
	 *
	 * @var int
	 */
	private $user_a_id;

	/**
	 * User ID of a friend at friend.local
	 *
	 * @var int
	 */
	private $friend_id;

	/**
	 * Token for the friend at friend.local
	 *
	 * @var int
	 */
	private $friends_in_token;

	/**
	 * Setup the unit tests.
	 */
	public function setUp() {
		parent::setUp();

		$this->user_a_id = $this->factory->user->create(
			array(
				'user_login' => 'user-a',
				'user_email' => 'user-a@example.org',
				'role'       => 'administrator',
			)
		);
	}

}
