<?php

class thinkeryTest extends Thinkery_TestCase {
	public function testImport() {
		$user = User::newAnonUser()->setCurrent();

		include_once IMPORTER . "thinkery.php";

		$importer = new ThinkeryImporter($user);
		$importer->loadFile(__DIR__ . "/thinkery.xml");
		$this->assertTrue($importer->canHandle());

		$importer->process();

		$things = $user->things->all();

		$this->assertEquals("Rucksäcke",  $things[0]->title);
		$this->assertFalse($things[0]->url);
		$this->assertEquals('<p>dankine division 27l<br>
Jansport <u>beamer</u></p>
', $things[0]->html);

		$this->assertEquals("Bookmark successfully imported",  $things[1]->title);
		$this->assertEquals("http://www.thinkery.me/", $things[1]->url);
		$this->assertEquals("Contents", $things[1]->html);

		$this->assertEquals("Second bookmark ok",  $things[2]->title);
		$this->assertEquals("http://alexander.kirk.at/", $things[2]->url);
		$this->assertEquals("Contents", $things[2]->html);
	}

	public function testExportImport() {
		$user = User::newAnonUser()->setCurrent();

		$things = array(
			"thing with html" => array(
				"tags" => "tag1 tag2",
				"html" => "<h1>Content</h1> with an <a href=\"\">a href</a> and something &gt; entity encoded",
			),
			"thing without html" => array(
				"tags" => "tag1 tag2",
				"html" => "Content of &gt;thing",
			)
		);

		$thing_count = 0;
		foreach ($things as $title => $data) {
			$thing = $user->things->add($title, $data);
			$things[$title]["date"] = $thing->date;
			$thing_count += 1;
		}

		$c = $user->things->count();
		$this->assertEquals($c["count"], $thing_count);

		$exportData = $user->things->allInklArchived(array())->getExportData();
		ob_start();
		$exportData->outputXml();
		$xml = ob_get_contents();
		ob_end_clean();

		foreach ($user->things->all() as $thing) {
			$thing->delete();
		}

		$c = $user->things->count();
		$this->assertEquals($c["count"], 0);

		include_once IMPORTER . "thinkery.php";

		$importer = new ThinkeryImporter($user);
		$importer->loadString($xml);
		$importer->process();

		$c = $user->things->count();
		$this->assertEquals($c["count"], $thing_count);

		foreach ($user->things->all() as $thing) {
			$this->assertArrayHasKey($thing->title, $things);
			if (!isset($things[$thing->title])) continue;
			$this->assertEquals($things[$thing->title]["html"], $thing->html);
			$this->assertEquals($things[$thing->title]["tags"], $thing->tags);
			$this->assertEquals($things[$thing->title]["date"], $thing->date);
		}

	}
}
