<?php

class titleTagSyncTest extends Thinkery_TestCase {
	public function testTitleTagSync() {
		$user = User::newAnonUser()->setCurrent();

		// new thing with 2 tags
		$thing = $user->things->add("test #hello #todo");
		$this->assertEquals("hello todo", $thing->tags);

		// remove hashtag from title, leave tags alone
		$thing->saveChanges(array("title" => "test #hello", "tags" => "hello todo"));
		$this->assertEquals("hello", $thing->tags);

		// add tag again through hash tag
		$thing->saveChanges(array("title" => "test #hello #todo", "tags" => "hello todo"));
		$this->assertEquals("hello todo", $thing->tags);

		// remove tag just from tags, leave title alone
		$thing->saveChanges(array("title" => "test #hello #todo", "tags" => "hello"));
		$this->assertEquals("hello", $thing->tags);
		$this->assertEquals("test #hello", $thing->title);
	}

	public function testTitleTagSyncWithAutosave() {
		$user = User::newAnonUser()->setCurrent();

		// new thing with 2 tags
		$thing = $user->things->add("test #hello #todo");
		$this->assertEquals("hello todo", $thing->tags);

		// pretend editing starts here
		$oldTitle = $thing->title;
		$oldTags = $thing->tags;

		// remove hashtag from title, leave tags alone
		$thing->saveChanges(array("title" => "test #hello", "tags" => "hello todo"));
		$this->assertEquals("hello", $thing->tags);

		// same save but the tags on the server side have been changed because of the async
		$thing->saveChanges(array("title" => "test #hello", "tags" => "hello todo"));
		// therefore the tag todo will be added again
		$this->assertEquals("test", $thing->title); // hashtags are not added to the title, it confuses users
		$this->assertEquals("hello todo", $thing->tags);

		// same save, tag todo will persist
		$thing->saveChanges(array("title" => "test #hello", "tags" => "hello todo"));
		$this->assertEquals("test #hello", $thing->title); // hashtags remains, the tags were in not in sync
		$this->assertEquals("hello todo", $thing->tags);

		// // same save, this time we will send the oldTitle and oldTags
		$thing->saveChanges(array("title" => "test #hello", "tags" => "hello todo", "oldTitle" => $oldTitle, "oldTags" => $oldTags));
		// // therefore the tag should stay removed
		$this->assertEquals("hello", $thing->tags);
	}
}
