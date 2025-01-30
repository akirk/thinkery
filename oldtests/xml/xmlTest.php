<?php

class xmlTest extends Thinkery_TestCase {
	public function testShortTags() {
		$xml = new LightXmlReader("<xml>test<img src='test' /><img src='test2' />hello</xml>");
		$this->assertEquals("<img src='test' />", (string) $xml->nextTag("img"));
		$this->assertEquals("<img src='test2' />", (string) $xml->nextTag("img"));
	}
	public function testShortTagsExternal() {
		$xml = new LightXmlReader;
		$xml->loadFile(__DIR__ . "/shorttag.xml");
		$this->assertEquals("<img src='test' />", (string) $xml->nextTag("img"));
	}

	public function testInnerXML() {
		$xml = new LightXmlReader("<xml>test<b>hello</b>yep</xml>");
		$this->assertEquals("test<b>hello</b>yep", $xml->nextTag("xml")->getInnerXML());
		$xml->setPos(0);
		$this->assertEquals("hello", $xml->nextTag("b")->getInnerXML());
		$xml->setPos(0);
		$this->assertEquals("hello", $xml->nextTag("b")->getText());
	}

	public function testAttrs() {
		$xml = new LightXmlReader("<xml>test<img src='test' width=12 height=\"13\" alt=\"this is a test\" />hello</xml>");
		$img = $xml->nextTag("img");
		$this->assertEquals("test", $img->getAttr("src"));
		$this->assertEquals("12", $img->getAttr("width"));
		$this->assertEquals("13", $img->getAttr("height"));
		$this->assertEquals("this is a test", $img->getAttr("alt"));
		$this->assertFalse($img->getAttr("doesnotexist"));
	}

	public function testEvernote() {
		$xml = new LightXmlReader;
		$xml->loadFile(__DIR__ . "/evernote.enex");

		$this->assertEquals($xml->nextTag("note")->getInnerXML(), <<<EVERNOTE
<title>Welcome to Evernote</title><content><![CDATA[<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE en-note SYSTEM "http://xml.evernote.com/pub/enml2.dtd">
<en-note><table border="0" cellpadding="4"><tr><td align="left" colspan="3" rowspan="1" bgcolor="#6FB536" height="60">
<h1><strong><span style="color: white; font-family: Arial, Helvetica, sans-serif; font-size: x-large;">Welcome to Evernote</span></strong></h1>
</td></tr><tr><td align="left" colspan="3" rowspan="1"><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Use Evernote to save your ideas, things you see, and things you like. Then find them all on any computer or device you use.</span></td></tr><tr><td align="left" colspan="1" rowspan="1" valign="top" width="320"><span><span style="font-size: small;"><span style="font-size: small;"><strong><span style="color: #252525;">A few simple ideas to get you started</span></strong></span></span></span>
<ul><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Click New Note and take down an idea or task.</span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Clip and save a webpage using a <a shape="rect" href="http://www.evernote.com/about/download/web_clipper.php" target="_blank">Web Clipper.</a></span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Use Evernote on your phone to snap a photo of a whiteboard, business card, or wine label. <em>Evernote automatically makes text in your snapshots searchable!</em></span></li></ul>
<span><span style="color: #000000; font-size: small;"><span style="font-size: small;"><strong>Lots of useful features</strong></span></span></span>
<ul><li><span style="font-family: Arial, Helvetica, sans-serif;"><span style="font-size: x-small;">Take notes, save images, create to-dos, view PDFs, and more <br clear="none"/></span></span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Access your Evernote notes from any computer or phone you use</span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Search and find everything, even printed or handwritten text in images</span></li></ul>
<div><span><span style="color: #000000; font-size: small;"><span style="font-size: small;"><strong>Install and use Evernote everywhere</strong></span></span></span>
<ul><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Download and install Evernote on all of your computers and phones</span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Install a <a shape="rect" href="http://www.evernote.com/about/download/web_clipper.php" target="_blank">Web Clipper</a> into your web browser</span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Email notes to your Evernote email address</span></li><li><span style="font-family: Arial, Helvetica, sans-serif;"><span style="font-size: x-small;"><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Save Twitter messages by following <a shape="rect" href="http://s.evernote.com/myen" target="_blank">@myEN</a></span><br clear="none"/></span></span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Import photos from your digital camera</span></li></ul>
</div>
<div><span style="font-family: Arial, Helvetica, sans-serif;"><span style="font-size: x-small;"><br clear="none"/></span></span></div>
<div><span style="font-family: Arial, Helvetica, sans-serif;"><span style="font-size: x-small;"><br clear="none"/></span></span></div>
<div><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Interested in getting even more out of Evernote? Check out <a shape="rect" href="http://s.evernote.com/premium" target="_blank">Evernote Premium »</a></span></div>
<div><br clear="none"/></div>
</td><td colspan="1" rowspan="1" width="30"> </td><td colspan="1" rowspan="1" valign="top" width="180"><span><span style="color: #000000; font-size: small;"><span style="font-size: small;"><strong>Click the link to install Evernote to your computer:</strong></span></span></span>
<ul><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;"><a shape="rect" href="http://www.evernote.com/about/download/#Windows" target="_blank">Windows</a></span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;"><a shape="rect" href="http://www.evernote.com/about/download/#Mac" target="_blank">Mac</a></span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;"><a shape="rect" href="http://www.evernote.com/about/download/#Webclipper" target="_blank">Web browser</a></span></li></ul>
<span><span style="color: #000000; font-size: small;"><span style="font-size: small;"><strong>Click the link to install Evernote onto your mobile device:</strong></span></span></span>
<ul><li><span style="font-family: Arial, Helvetica, sans-serif;"><span style="font-size: x-small;"><a shape="rect" href="http://www.evernote.com/about/download/#iPhone" target="_blank">iPhone / iPod</a><br clear="none"/></span></span></li><li><span style="font-family: Arial, Helvetica, sans-serif;"><span style="font-size: x-small;"><a shape="rect" href="http://www.evernote.com/about/download/ipad.php" target="_blank">iPad</a><br clear="none"/></span></span></li><li><span style="font-family: Arial, Helvetica, sans-serif;"><span style="font-size: x-small;"><a shape="rect" href="http://www.evernote.com/about/download/android.php" target="_blank">Android</a><br clear="none"/></span></span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;"><a shape="rect" href="http://www.evernote.com/about/download/#BlackBerry" target="_blank">BlackBerry</a></span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;"><a shape="rect" href="http://www.evernote.com/about/download/#PalmPre" target="_blank">Palm Pre</a></span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;"><a shape="rect" href="http://www.evernote.com/about/download/#WinMo" target="_blank">Windows Mobile</a></span></li></ul>
<p><span><span style="color: #000000; font-size: small;"><span style="font-size: small;"><strong>Get the latest news</strong></span></span></span></p>
<ul><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Read our <a shape="rect" href="http://s.evernote.com/blog" target="_blank">blog</a></span></li><li><span style="font-family: Arial, Helvetica, sans-serif;"><span style="font-size: x-small;"><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Follow us on <a shape="rect" href="http://s.evernote.com/tweet" target="_blank">Twitter</a></span><br clear="none"/></span></span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Be a <a shape="rect" href="http://s.evernote.com/fbook" target="_blank">Facebook fan</a></span></li><li><span style="font-family: Arial, Helvetica, sans-serif; font-size: x-small;">Check out our <a shape="rect" href="http://s.evernote.com/tips" target="_blank">tips blog</a></span></li></ul>
</td></tr></table></en-note>]]></content><created>20091019T224837Z</created><updated>20121208T065001Z</updated><tag>php test</tag><tag>fdh sfhds</tag><note-attributes><latitude>37.321429</latitude><longitude>-122.015791</longitude><altitude>0</altitude><author>alexkirk</author><source>web.clip</source><source-url>http://www.evernote.com</source-url></note-attributes>
EVERNOTE
		);
	}
}
