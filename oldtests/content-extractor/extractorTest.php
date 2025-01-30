<?php

class extractorTest extends Thinkery_TestCase {
	public function test126() {
		$html = trim(file_get_contents(__DIR__ . "/126.html"));

		$url = "http://www.126.com/";
		$plugin = new Url($url, $url);
		$content = $plugin->extractContentFromHTML($html, $url);

		$this->assertEquals($content["title"], "126网易免费邮--你的专业电子邮局");
	}

	public function testZol() {
		$html = trim(file_get_contents(__DIR__ . "/zol-com-cn-165447.html"));

		$url = "http://news.zol.com.cn/article/165447.html";
		$plugin = new Url($url, $url);
		$content = $plugin->extractContentFromHTML($html, $url);

		$this->assertEquals($content["title"], "iOS 7为何要用扁平化？-ZOL科技频道");
	}

	public function testEislaufen() {
		$html = trim(file_get_contents(__DIR__ . "/eislaufen-fuer-kinder.html"));

		$url = "http://www.eislaufschule.de/kurse/anfaenger-kinder.htm";
		$plugin = new Url($url, $url);
		$content = $plugin->extractContentFromHTML($html, $url);

		$this->assertEquals($content["title"], "Eislauf Anfängerkurse für Kinder");
	}

	public function testInterpretativeSemantik() {
		$html = trim(file_get_contents(__DIR__ . "/Interpretative_Semantik.html"));

		$url = "http://de.wikipedia.org/wiki/Interpretative_Semantik";
		$plugin = new Url("Interpretative Semantik", $url);
		$content = $plugin->extractContentFromHTML($html, $url);
		$content = $content["content"];

		$p = strpos($content, '<h4');
		// no headline found
		$this->assertTrue(!!$p);

		$p = strpos($content, '</h', $p);
		// no /headline found
		$this->assertTrue(!!$p);

		$this->assertEquals("4", substr($content, $p + 3, 1));
	}

	public function testMongoDocs() {
		$html = trim(file_get_contents(__DIR__ . "/mongodb-introduction.html"));

		$url = "http://docs.mongodb.org/manual/core/introduction/";
		$plugin = new Url($url, $url);
		$c = $plugin->extractContentFromHTML($html, $url);
		$content = $c["content"];
		$this->assertEquals("Introduction to MongoDB¶", $c["title"]);
		$this->assertContains("A record in MongoDB is a document, which is a data structure composed", $c["content"]);
	}


	public function testNZZ() {
		$html = trim(file_get_contents(__DIR__ . "/nzz-geklonte-performance.html"));

		$url = "http://www.nzz.ch/finanzen/uebersicht/private_finanzen/geklonte-performance-1.17901327";
		$plugin = new Url($url, $url);
		$c = $plugin->extractContentFromHTML($html, $url);
		$content = $c["content"];

		$p = strpos($content, '<h4');
		// no headline found
		$this->assertTrue(!!$p);

		$p = strpos($content, '</h', $p);
		// no /headline found
		$this->assertTrue(!!$p);

		$this->assertEquals("Geklonte Performance - NZZ.ch, 24.12.2012", $c["title"]);
		$this->assertEquals("4", substr($content, $p + 3, 1));
	}

	public function testNpr() {
		$html = trim(file_get_contents(__DIR__ . "/npr-tiny-tech-puts-satellites-in-hands-of-homebrew-designers.html"));

		$url = "http://www.npr.org/blogs/alltechconsidered/2013/08/22/205822987/tiny-tech-puts-satellites-in-hands-of-homebrew-designers";
		$plugin = new Url($url, $url);
		$c = $plugin->extractContentFromHTML($html, $url);
		$content = $c["content"];
		$this->assertEquals("Tiny Tech Puts Satellites In Hands Of Homebrew Designers", $c["title"]);
		$this->assertContains("The basics of Arduino seem to come easily to the students back at the HacDC workshop", $c["content"]);
	}

	public function testStochastik() {
		$html = trim(file_get_contents(__DIR__ . "/Stochastik.html"));

		$url = "http://de.wikipedia.org/wiki/Stochastik";
		$plugin = new Url("Stochastik", $url);
		$c = $plugin->extractContentFromHTML($html, $url);
		$content = $c["content"];

		$p = strpos($content, '<h4');
		// no headline found
		$this->assertTrue(!!$p);

		$p = strpos($content, '</h', $p);
		// no /headline found
		$this->assertTrue(!!$p);

		$this->assertEquals("4", substr($content, $p + 3, 1));

		$p = strpos($content, "upload.wikimedia.org");
		// no picture found
		$this->assertTrue(!!$p);

		$http = strrpos(substr($content, 0, $p), "http:");
		$this->assertEquals("http://upload.wikimedia.org", substr($content, $http, 27));
	}

	public function testUnheap() {
		$html = trim(file_get_contents(__DIR__ . "/unheap.html"));

		$url = "http://www.unheap.com/";
		$plugin = new Url($url, $url);
		$content = $plugin->extractContentFromHTML($html, $url);

		$this->assertEquals("A tidy repository of jQuery plugins", $content["title"]);
		$this->assertEquals("test", $content["content"]);
	}

	public function testUnheapOG() {
		$html = trim(file_get_contents(__DIR__ . "/unheap-og.html"));

		$url = "http://www.unheap.com/";
		$plugin = new Url($url, $url);
		$content = $plugin->extractContentFromHTML($html, $url);

		$this->assertEquals("A tidy repository of jQuery plugins", $content["title"]);
		$this->assertEquals("longer", $content["content"]);
	}

	public function testVordenker() {
		$html = trim(file_get_contents(__DIR__ . "/vordenker.html"));

		$url = "http://www.vordenker.de/ics/lds.htm";
		$plugin = new Url("Die Logik des Subjekts und das Subjekt der Logik", $url);
		$content = $plugin->extractContentFromHTML($html, $url);
		$content = $content["content"];

		$this->assertTrue(strpos($content, "daß") !== false);
	}

	public function testSpiegel() {
		$html = trim(file_get_contents(__DIR__ . "/a-955851.html"));

		$url = "http://m.spiegel.de/reise/fernweh/a-955851.html";
		$plugin = new Url($url);
		$content = $plugin->extractContentFromHTML($html, $url);

		$this->assertTrue(strpos($content["content"], "für") !== false);
		$this->assertTrue(strpos($content["title"], "Völlig") !== false);
	}

	public function testWired() {
		$html = trim(file_get_contents(__DIR__ . "/wired-microsoft-surface-tablet.html"));

		$url = "http://www.wired.com/reviews/2012/10/microsoft-surface/all/";
		$plugin = new Url($url, $url);
		$content = $plugin->extractContentFromHTML($html, $url);
		$this->assertEquals("Microsoft Dives Deep to Surface a Hit", $content["title"]);

	}

	public function testPanther() {
		$html = trim(file_get_contents(__DIR__ . "/080027panther.html"));

		$url = "http://rainer-maria-rilke.de/080027panther.html";
		$plugin = new Url($url, $url);
		$content = $plugin->extractContentFromHTML($html, $url);
		$this->assertContains("betäubt", $content["content"]);

	}
}
