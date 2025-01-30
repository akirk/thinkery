<?php

class specialTagsTest extends Thinkery_TestCase {
	public function testIsFavorite() {
		$user = User::newAnonUser()->setCurrent();
		$user->things->add("thing #tag");
		$tag = $user->tags->get("tag");

		$this->assertFalse($tag->isFavorite());
		$tag->makeFavorite();
		$this->assertTrue($tag->isFavorite());
		$tag->makeNonFavorite();
		$this->assertFalse($tag->isFavorite());
	}

	public function testTagInfo() {
		$user = User::newAnonUser()->setCurrent();
		$user->things->add("thing #tag");
		$tag = $user->tags->get("tag");

		$this->assertEquals(array("color" => false, "favorite" => false, "todo" => false), $tag->getInfo(true));

		$tag->makeFavorite();
		$this->assertEquals(array("color" => false, "favorite" => true, "todo" => false), $tag->getInfo(true));

		$specialTags = array(
		  "tag" => array(
		    "type" => "color",
		    "options" => array(
		      "hex" => "#ff0000",
		    ),
		  ),
		);

		$user->setPref(PREF_SPECIAL_TAGS, $specialTags);
		$this->assertEquals(array("color" => "#ff0000", "favorite" => true, "todo" => false), $tag->getInfo(true));

		$specialTags = array(
		  "tag" => array(
		    "type" => "todo",
		    "options" => array(
		      "strike" => "false",
		    ),
		  ),
		);

		$user->setPref(PREF_SPECIAL_TAGS, $specialTags);
		$this->assertEquals(array("color" => false, "favorite" => true, "todo" => "strike"), $tag->getInfo(true));

		$user->setPref(PREF_SPECIAL_TAGS, array());
		$this->assertEquals(array("color" => false, "favorite" => true, "todo" => false), $tag->getInfo(true));

		$tag->makeNonFavorite();
		$this->assertEquals(array("color" => false, "favorite" => false, "todo" => false), $tag->getInfo(true));

	}
}
