<?php

class emailTest extends Thinkery_TestCase {
	public function setUp() {
		if (!extension_loaded("mailparse")) {
			$this->markTestSkipped("Mailparse extension missing.");
		}
	}
	public function testForwardedPDF() {
		$mail = new ReceivedMail(file_get_contents(__DIR__ . "/forwarded-pdf.txt"));

		$this->assertEquals("Ihre Buchungsbestätigung 3VAXVV", $mail->getSubject());
		$html = Html::unify(Pdf::GetHtml($mail->getPDF()));
		$this->assertTrue(strpos($html, "Rechnungsnummer") !== false);
		$this->assertTrue(strpos($mail->getExtractedHtml(), "Rechnungsnummer") !== false);

	}

	public function testForwarded() {
		$mail = new ReceivedMail(file_get_contents(__DIR__ . "/forwarded.txt"));

		$this->assertEquals("www.gebrauchtwagen.at - Suchagenten - Verständigung", $mail->getSubject());
		$this->assertTrue(strpos($mail->getTextBody(), "übermitteln") !== false);

		$html = $mail->getExtractedHTML();
		$this->assertFalse(strpos($html, "font.fahrzeuglink"));
		$this->assertTrue(strpos($html, "übermitteln") !== false);

		$this->assertEquals("1 neues Fahrzeug", substr($mail->getTextBody(), 0, 16));

	}

	public function testReceiveMail() {
		$mail = new ReceivedMail(file_get_contents(__DIR__ . "/receive-mail.txt"));

		$this->assertEquals(array("alex", "nader"), $mail->getAddressedUsernames());
		$this->assertEquals("test", $mail->getSubject());
		$this->assertEquals("lg, alex", trim($mail->getHTML()));
	}

	public function testTextAndHTML() {
		$mail = new ReceivedMail(file_get_contents(__DIR__ . "/text-and-html.txt"));

		$this->assertEquals("www.gebrauchtwagen.at - Suchagenten - Verständigung", $mail->getSubject());
		$this->assertTrue(strpos($mail->getTextBody(), "übermitteln") !== false);

		$html = $mail->getExtractedHTML();
		$this->assertFalse(strpos($html, "font.fahrzeuglink"));
		$this->assertTrue(strpos($html, "übermitteln") !== false);

		$this->assertEquals("1 neues Fahrzeug", substr($mail->getTextBody(), 0, 16));
	}
}
