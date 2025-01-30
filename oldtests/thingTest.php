<?php

class thingTest extends Thinkery_TestCase {
	public function testImage() {
		$_SERVER["HTTPS"] = true;
		$user = User::newAnonUser()->setCurrent();

		$img = "https://alexander.kirk.at/img/test.png";
		$original_html = "thing with <img src=\"$img\" />";
		$thing = $user->things->add("thing #tag", array(
			"html" => $original_html,
		));

		$html = Html::prepareForOutput($thing->html);
		$this->assertEquals($original_html, $html);

		$thing->saveChanges(array("html" => $html, "private" => true));
		$this->assertEquals($original_html, $thing->html);

		$html = Html::prepareForOutput($thing->html);
		$thing->saveChanges(array("html" => $html, "private" => true));
		$this->assertEquals($original_html, $thing->html);
	}

	public function testNonHTTPSImage() {
		$_SERVER["HTTPS"] = true;
		$user = User::newAnonUser()->setCurrent();

		$img = "http://alexander.kirk.at/img/test.png";
		$original_html = "thing with <img src=\"$img\" />";
		$thing = $user->things->add("thing #tag", array(
			"html" => $original_html,
		));
		$html = Html::prepareForOutput($thing->html);
		$this->assertNotEquals($original_html, $html);
		$thing->saveChanges(array("html" => $html, "private" => true));
		$this->assertEquals($original_html, $thing->html);

		$new_html = $thing->html;

		$html = Html::prepareForOutput($thing->html);
		$thing->saveChanges(array("html" => $html, "private" => true));
		$this->assertEquals($new_html, $thing->html);
	}

	public function testLink() {
		$_SERVER["HTTPS"] = true;
		$user = User::newAnonUser()->setCurrent();

		$link = "http://alexander.kirk.at/";
		$original_html = "thing with <a href=\"$link\">link</a>";
		$thing = $user->things->add("thing #tag", array(
			"html" => $original_html,
		));

		$html = Html::prepareForOutput($thing->html);
		$thing->saveChanges(array("html" => $html, "private" => true));
		$this->assertEquals($original_html, $thing->html);

		$html = Html::prepareForOutput($thing->html);
		$thing->saveChanges(array("html" => $html, "private" => true));
		$this->assertEquals($original_html, $thing->html);

		$html = Html::prepareForOutput(Html::prepareForOutput($thing->html));
		$thing->saveChanges(array("html" => $html, "private" => true));
		$this->assertEquals($original_html, $thing->html);

	}

	public function testUpdateTitle() {
		$this->assertEquals("#test hallo #neu", Thing::updateTitle("#test hallo #neu", array("test", "neu")));
	}

	public function testUrlEmpty() {
		$user = User::newAnonUser()->setCurrent();

		$specialTags = array(
		  "todo" => array(
		    "type" => "todo",
		    "options" => array(
		      "strike" => "false",
		    ),
		  ),
		);

		$user->setPref(PREF_SPECIAL_TAGS, $specialTags);


		$user->things->add("thing url empty #tag", array(
			"url" => "",
		));

		$user->things->add("thing url false #tag", array(
			"url" => false,
		));

		$user->things->add("thing url filled #tag", array(
			"url" => "https://thinkery.me",
		));

		$user->things->add("thing todo url empty #todo", array(
			"url" => "",
		));

		$user->things->add("thing todo url false #todo", array(
			"url" => false,
		));

		$user->things->add("thing todo url filled #todo", array(
			"url" => "https://thinkery.me",
		));

		$this->assertCount(2, $user->things->forGET(array("tag" => ":urls")));
		$this->assertCount(2, $user->things->forGET(array("tag" => ":todos")));
		$this->assertCount(2, $user->things->forGET(array("tag" => ":notes")));
		$this->assertCount(6, $user->things->all());

		$this->assertEquals(2, $user->tags->count(":urls"));
		$this->assertEquals(2, $user->tags->count(":todos"));
		$this->assertEquals(2, $user->tags->count(":notes"));

	}


}
