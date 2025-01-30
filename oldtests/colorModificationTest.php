<?php

class colorModificationTest extends Thinkery_TestCase {
	public function testColors() {
		$this->assertEquals("#fe0000", CssCrush_Color::darken("#ff0000", .1));
		$this->assertEquals("#fe0000", CssCrush_Color::darken("red", .1));
		$this->assertEquals("#ff0101", CssCrush_Color::lighten("#ff0000", .1));
		$this->assertEquals("#ff0101", CssCrush_Color::lighten("red", .1));
	}
}

