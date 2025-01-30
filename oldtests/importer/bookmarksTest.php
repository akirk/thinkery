<?php

class bookmarksTest extends Thinkery_TestCase {
	public function testFile() {
		$user = User::newAnonUser()->setCurrent();

		include_once IMPORTER . "bookmarks.php";

		$importer = new BookmarksImporter($user);
		$importer->loadFile(__DIR__ . "/bookmarks-file.html");
		$this->assertTrue($importer->canHandle());

		$importer->process();

		$things = $user->things->all();
		$this->assertEquals("Bookmark successfully imported", $things[0]->title);
		$this->assertEquals("http://www.thinkery.me/", $things[0]->url);
	}

	public function testBookmarks() {
		$user = User::newAnonUser()->setCurrent();

		include_once IMPORTER . "bookmarks.php";

		$importer = new BookmarksImporter($user);
		$importer->loadFile(__DIR__ . "/bookmarks.html");
		$this->assertTrue($importer->canHandle());

		$importer->process();

		$things = $user->things->all();
		$this->assertEquals("Bookmark successfully imported",  $things[0]->title);
		$this->assertEquals("http://thinkery.me/", $things[0]->url);
		$this->assertEquals("tag1 tag2", (string) $things[0]->tags);
		$this->assertEquals("tag1 tag2", (string) $things[1]->tags);

	}

	public function testHeadline() {
		$user = User::newAnonUser()->setCurrent();

		include_once IMPORTER . "bookmarks.php";

		$importer = new BookmarksImporter($user);
		$importer->loadString(file_get_contents(__DIR__ . "/bookmarks-headline.html"));
		$this->assertTrue($importer->canHandle());

		$importer->process();

		$things = $user->things->all();
		$this->assertEquals("The Hype Machine",  $things[0]->title);
		$this->assertEquals("http://hypem.com/", $things[0]->url);
		$this->assertEquals("music", (string) $things[0]->tags);
	}
}
