<?php

class evernoteTest extends Thinkery_TestCase {
	public function testEvernote() {
		$user = User::newAnonUser()->setCurrent();

		include_once IMPORTER . "evernote.php";

		$importer = new EvernoteImporter($user);
		$importer->loadFile(__DIR__ . "/evernote.enex");
		$this->assertTrue($importer->canHandle());

		$importer->process();

		$things = $user->things->all();

		$this->assertEquals("img",  $things[0]->title);
		$this->assertFalse($things[0]->url);
		$this->assertEquals("art dream", (string) $things[0]->tags);
		$this->assertEquals('hello <img src="data:image/png;base64,imgdata" width="984" height="1603" />', $things[0]->html);

		$this->assertEquals("test",  $things[1]->title);
		$this->assertFalse($things[1]->url);
		$this->assertEmpty((string) $things[1]->tags);

		$this->assertEquals("Welcome to Evernote",  $things[2]->title);
		$this->assertEquals("http://www.evernote.com", $things[2]->url);
		$this->assertEquals("php-test fdh-sfhds", (string) $things[2]->tags);

	}
}
