<?php

class htmlTest extends Thinkery_TestCase {
	public function testHausruck() {
		$html = trim(file_get_contents(__DIR__ . "/hausruck.txt"));

		$html = Html::truncate($html, 51200);
		$this->assertEquals("</div></div>", substr($html, -12));
		$this->assertFalse("null" == json_encode($html));
	}

	public function testTruncate1() {
		$html = trim(file_get_contents(__DIR__ . "/truncate-1.txt"));

		$this->assertEquals(Html::truncate($html, 10), "We will ne");
		$this->assertEquals(Html::truncate($html, 35), "We will need a lot more text here. ");
		$this->assertEquals(Html::truncate($html, 36), "We will need a lot more text here. <div><div>I</div></div>");
		$this->assertEquals(Html::truncate($html, 37), "We will need a lot more text here. <div><div>I </div></div>");
		$this->assertEquals(Html::truncate($html, 38), "We will need a lot more text here. <div><div>I l</div></div>");
		$this->assertEquals(Html::truncate($html, 39), "We will need a lot more text here. <div><div>I li</div></div>");
		$this->assertEquals(Html::truncate($html, 40), "We will need a lot more text here. <div><div>I lik</div></div>");
		$this->assertEquals(Html::truncate($html, 41), "We will need a lot more text here. <div><div>I like</div></div>");
		$this->assertEquals(Html::truncate($html, 42), "We will need a lot more text here. <div><div>I like </div></div>");
		$this->assertEquals(Html::truncate($html, 43), "We will need a lot more text here. <div><div>I like i</div></div>");
		$this->assertEquals(Html::truncate($html, 44), "We will need a lot more text here. <div><div>I like it</div></div>");
		$this->assertEquals(Html::truncate($html, 45), "We will need a lot more text here. <div><div>I like it </div></div>");
		$this->assertEquals(Html::truncate($html, 46), "We will need a lot more text here. <div><div>I like it r</div></div>");
		$this->assertEquals(Html::truncate($html, 47), "We will need a lot more text here. <div><div>I like it ra</div></div>");
		$this->assertEquals(Html::truncate($html, 48), "We will need a lot more text here. <div><div>I like it raw</div></div>");
		$this->assertEquals(Html::truncate($html, 49), "We will need a lot more text here. <div><div>I like it raw.</div></div>");
		$this->assertEquals(Html::truncate($html, 350), "We will need a lot more text here. <div><div>I like it raw.</div><div><br></div><div>Can the web app display proper mobile code?<span style=\"font-size: 10pt;\">I like it raw.</span></div><div><span style=\"font-size: 10pt;\">Can the web app display proper mobile code?</span><span style=\"font-size: 10pt;\">I like it raw.</span></div><div><span style=\"font-size: 10pt;\">Can the web app display proper mobile code?</span><span style=\"font-size: 10pt;\">I like it raw.</span></div><div><br></div><div>Can the web app display proper mobile code?<span style=\"font-size: 10pt;\">I like it raw.</span></div><div><br></div><div>Can the web app display proper mobile code?</div></div>");
		$this->assertEquals(Html::truncate($html, 1000), $html);

	}

	public function testTruncate2() {
		$html = trim(file_get_contents(__DIR__ . "/truncate-2.txt"));

		$this->assertEquals(Html::truncate($html, 500), <<<TRUNC2
<div id="extract"><h6 class="grey italic normal">Extracted Page: <a href="http://www.appbank.net/2011/03/26/iphone-application/236984.php" class="grey external">http://www.appbank.net/2011/03/26/iphone-application/236984.php</a></h6> Evernote の使い方: 「お気に入りの店」「得意先」などの『場所』も管理しよう。880 - iPhoneアプリのAppBank<div class="expandable"><div id="entrya" readability="107.51755661749">
<p><a href="http://www.appbank.net/iphoneappimages/wp-content/uploads/2011/03/Evernote-LocationBookmark-01.jpg" rel="nofollow"><img src="http://imgc.appbank.net/c/wp-content/uploads/2011/03/Evernote-LocationBookmark-01.jpg" alt="Evernote: 「お気に入りの店」「得意先」などの『場所』も Evernote で管理しよう！" width="480" class="alignnone size-full wp-image-236969" /></a></p>

<p>仕事系アプリを使う人で知らない人はいない（はず）の <a href="http://click.linksynergy.com/fs-bin/stat?id=dtqxn7cT2As&amp;offerid=94348&amp;type=3&amp;subid=0&amp;tmpid=2192&amp;RD_PARM1=http%253A%252F%252Fitunes.apple.com%252Fjp%252Fapp%252Fevernote%252Fid281796108%253Fmt%253D8%2526uo%253D4%2526partnerId%253D30" target="itunes_store">Evernote</a> 。単なるメモツールと思われる方が多く、その魅力に気付かないまま使わなくなってしまう事も多いようです。</p>
<p>そこで AppBank では Evernote の具体的な活用方法を何回かに渡ってご紹介します。</p>
<p>今回は「Evernote でお気に入りの店や得意先も管理する！」と題しまして、店や得意先などの「場所」を Evernote でブックマークして、活用する方法をご紹介します。</p>


<h3>PC 版 Evernote ですること</h3>
<p>まずはその場所に関する情報を集めます。例えば「お気に</p></div></div></div>
TRUNC2
		);

	}

	public function testTruncatePRE() {
		$html = "Das ist <pre>
test    xyz
</pre><b>ein langer        test</b>";

		$html2 = str_replace("        ", " ", $html);
		$this->assertEquals(Html::truncate($html, 21), "Das ist <pre>
test    xyz
</pre>");
		$this->assertEquals(Html::truncate($html, 15), "Das ist <pre>
test  </pre>");
		$this->assertEquals(Html::truncate($html, 26), "Das ist <pre>
test    xyz
</pre><b>ein l</b>");
		$this->assertTrue($html == Html::truncate($html, 194));
		$this->assertTrue($html2 == Html::truncate($html, 48));
		$this->assertTrue($html2 == Html::truncate($html, 47));
		$this->assertTrue($html2 == Html::truncate($html, 46));
		$this->assertTrue($html2 == Html::truncate($html, 45));
		$this->assertTrue($html2 == Html::truncate($html, 44));
		$this->assertEquals(Html::truncate($html, 35), "Das ist <pre>
test    xyz
</pre><b>ein langer tes</b>");

	}

	public function testFixRelativeUrls() {
		$html = Html::fixRelativeUrls('<img alt="A MongoDB document." src="../../_images/crud-annotated-document.png">', "http://docs.mongodb.org/manual/core/introduction/");
		$this->assertContains('http://docs.mongodb.org/manual/core/introduction/../../_images/crud-annotated-document.png', $html);

	}

}
