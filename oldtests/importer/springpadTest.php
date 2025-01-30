// <?php

class springpadTest extends Thinkery_TestCase {
	public function testSpringpad() {
		$user = User::newAnonUser()->setCurrent();

		include_once IMPORTER . "springpad.php";

		$importer = new SpringpadImporter($user);
		$importer->loadFile(__DIR__ . "/cocooo-export.zip");
		$this->assertTrue($importer->canHandle());

		$importer->process();

		$things = $user->things->all();

		$this->assertEquals("http://stackoverflow.com/questions/6553758/in-vim-why-is-j-used-for-down-and-k-for-up", $things[0]->url);

		$this->assertEquals("hello test. test", $things[1]->title);
		$this->assertEquals("fasfl_lasd_alsd_asd asasd asdasd test", (string) $things[4]->tags);

	}

	public function testSpringpadExampleZIP() {
		$this->springpadExample(__DIR__ . "/springpad-export-master.zip");
	}

	public function testSpringpadExampleJSON() {
		$this->springpadExample(__DIR__ . "/springpad.json");
	}

	public function springpadExample($filename) {
		$user = User::newAnonUser()->setCurrent();

		include_once IMPORTER . "springpad.php";

		$importer = new SpringpadImporter($user);
		$importer->loadFile($filename);
		$this->assertTrue($importer->canHandle());

		$importer->process();

		$things = $user->things->all();

		$this->assertEquals("Uncompleted task", $things[7]->title);
		$this->assertFalse($things[7]->getTodoStatus());

		$this->assertEquals("Completed task", $things[8]->title);
		$this->assertTrue($things[8]->getTodoStatus());

		$this->assertEquals("Shopping list", $things[9]->title);
		$this->assertContains("packages active", $things[9]->html);

		$this->assertEquals("Raspberry Beer Cocktail", $things[11]->title);
		$this->assertEquals("http://www.myrecipes.com/recipe/raspberry-beer-cocktail-10000001975699/", $things[11]->url);

		$this->assertEquals("Fix the fence outside", $things[13]->title);
		$this->assertEquals("tasks Home_Improvement todo", (string) $things[13]->tags);
		$this->assertContains("Task: Fix the fence outside where it was blown over by the storm", $things[13]->html);
		// $this->assertEquals("fasfl_lasd_alsd_asd asasd asdasd", (string) $things[4]->tags);

	}

	public function testSpacesInTag() {
		$user = User::newAnonUser()->setCurrent();

		include_once IMPORTER . "springpad.php";

		$importer = new SpringpadImporter($user);
		$importer->loadFile(__DIR__ . "/spacesintag.json");
		$this->assertTrue($importer->canHandle());

		$importer->process();

		$things = $user->things->all();

		$this->assertEquals("RoboMind.net - Welcome to RoboMind.net, the new way to learn programming", $things[0]->title);
		$this->assertEquals("Robots Virtual_Robots Joseph Gift_Ideas", (string) $things[0]->tags);

	}
}
