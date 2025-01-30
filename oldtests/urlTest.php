<?php

class urlTest extends Thinkery_TestCase {
	public function testAtaWiki() {
		preg_match(URL_REGEX, "https://ata.wiki.kernel.org/index.php/ATA_Secure_Erase", $match);
		$this->assertEquals("https://ata.wiki.kernel.org/index.php/ATA_Secure_Erase", $match[0]);
	}

	public function testBBC() {
		preg_match(URL_REGEX, "bbc.co.uk", $match);
		$this->assertEquals("bbc.co.uk", $match[0]);
	}

	public function testORF() {
		preg_match(URL_REGEX, "orf.at", $match);
		$this->assertEquals("orf.at", $match[0]);
	}

	public function testRevision3() {
		preg_match(URL_REGEX, "revision3.com/tekzilla/tekzilla-158", $match);
		$this->assertEquals("revision3.com/tekzilla/tekzilla-158", $match[0]);
	}

	public function testNewsYCombinator() {
		preg_match(URL_REGEX, "news.ycombinator.com", $match);
		$this->assertEquals("news.ycombinator.com", $match[0]);
	}

	public function testDaringFireball() {
		preg_match_all(URL_REGEX, "http://foo.com/blah_blah
http://foo.com/blah_blah/
(Something like http://foo.com/blah_blah)
http://foo.com/blah_blah_(wikipedia)
http://foo.com/more_(than)_one_(parens)
(Something like http://foo.com/blah_blah_(wikipedia))
http://foo.com/blah_(wikipedia)#cite-1
http://foo.com/blah_(wikipedia)_blah#cite-1
http://foo.com/unicode_(✪)_in_parens
http://foo.com/(something)?after=parens
http://foo.com/blah_blah.
http://foo.com/blah_blah/.
<http://foo.com/blah_blah>
<http://foo.com/blah_blah/>
http://foo.com/blah_blah,
http://www.extinguishedscholar.com/wpglob/?p=364.
http://✪df.ws/1234
http://➡.ws/䨹
www.c.ws/䨹
<tag>http://example.com</tag>
Just a www.example.com link.
http://example.com/something?with,commas,in,url, but not at end
What about <mailto:gruber@daringfireball.net?subject=TEST> (including brokets).
bit.ly/foo
“is.gd/foo/”
WWW.EXAMPLE.COM
http://www.asianewsphoto.com/(S(neugxif4twuizg551ywh3f55))/Web_ENG/View_DetailPhoto.aspx?PicId=752
http://www.asianewsphoto.com/(S(neugxif4twuizg551ywh3f55))
http://lcweb2.loc.gov/cgi-bin/query/h?pp/horyd:@field(NUMBER+@band(thc+5a46634))
", $match);
		$this->assertEquals(array(
			"http://foo.com/blah_blah",
			"http://foo.com/blah_blah/",
			"http://foo.com/blah_blah",
			"http://foo.com/blah_blah_(wikipedia)",
			"http://foo.com/more_(than)_one_(parens)",
			"http://foo.com/blah_blah_(wikipedia)",
			"http://foo.com/blah_(wikipedia)#cite-1",
			"http://foo.com/blah_(wikipedia)_blah#cite-1",
			"http://foo.com/unicode_(✪)_in_parens",
			"http://foo.com/(something)?after=parens",
			"http://foo.com/blah_blah",
			"http://foo.com/blah_blah/",
			"http://foo.com/blah_blah",
			"http://foo.com/blah_blah/",
			"http://foo.com/blah_blah",
			"http://www.extinguishedscholar.com/wpglob/?p=364",
			"http://✪df.ws/1234",
			"http://➡.ws/䨹",
			"www.c.ws/䨹",
			"http://example.com",
			"www.example.com",
			"http://example.com/something?with,commas,in,url",
			"bit.ly/foo",
			"is.gd/foo/",
			"WWW.EXAMPLE.COM",
			"http://www.asianewsphoto.com/(S(neugxif4twuizg551ywh3f55))/Web_ENG/View_DetailPhoto.aspx?PicId=752",
			"http://www.asianewsphoto.com/(S(neugxif4twuizg551ywh3f55))",
			"http://lcweb2.loc.gov/cgi-bin/query/h?pp/horyd:@field(NUMBER+@band(thc+5a46634))",
		), $match[0]);
	}
}
