<?php

namespace RudolfByker\PhpMarcCsl\Tests;

use RudolfByker\PhpMarcCsl\Util;
use PHPUnit\Framework\TestCase;

/**
 * Test the Util class.
 *
 * @group php-marc-csl
 */
class UtilTest extends TestCase {

  /**
   * Test function trimNonWordCharacters.
   */
  public function testTrimNonWordCharacters() {
    $this->assertEquals("Congress on Machinability", Util::trimNonWordCharacters("Congress on Machinability"));
    $this->assertEquals("1965", Util::trimNonWordCharacters("(1965 :"));
    $this->assertEquals("Royal Commonwealth Society", Util::trimNonWordCharacters("Royal Commonwealth Society)"));
    $this->assertEquals("", Util::trimNonWordCharacters(""));
  }

  /**
   * Test function parseRelatedParts.
   */
  public function testParseRelatedParts() {
    $this->assertEquals([
      "date" => "1972-1974",
    ], Util::parseRelatedParts("1972-1974"));

    $this->assertEquals([
      "date" => "Feb. 1948",
      "volume" => "17",
      "number" => "98",
      "pages" => "78-159",
    ], Util::parseRelatedParts("Vol. 17, no. 98 (Feb. 1948), p. 78-159"));

    $this->assertEquals([
      "date" => "Sept. 1993",
      "volume" => "24",
      "part" => "B",
      "number" => "9",
      "pages" => "235-48",
    ], Util::parseRelatedParts("Vol. 24, pt. B no. 9 (Sept. 1993), p. 235-48"));

    $this->assertEquals([
      "date" => "May 2000",
      "volume" => "96",
      "number" => "4",
      "pages" => "23-24, 27",
    ], Util::parseRelatedParts("Vol. 96, no. 4 (May 2000), p. 23-24, 27"));
  }

  /**
   * Test function parseEnumerationAndFirstPage.
   */
  public function testParseEnumerationAndFirstPage() {
    $this->assertEquals([
      "volume" => "123",
    ], Util::parseEnumerationAndFirstPage("123"));

    $this->assertEquals([
      "volume" => "279",
      "page" => "GM5",
    ], Util::parseEnumerationAndFirstPage("279<GM5"));

    $this->assertEquals([
      "volume" => "90",
      "number" => "23",
    ], Util::parseEnumerationAndFirstPage("90:23"));

    $this->assertEquals([
      "volume" => "96",
      "number" => "4",
      "page" => "23",
    ], Util::parseEnumerationAndFirstPage("96:4<23"));

    $this->assertEquals([
      "volume" => "24",
      "part" => "B",
      "number" => "9",
      "page" => "235",
    ], Util::parseEnumerationAndFirstPage("24:B:9<235"));

    $this->assertEquals([
      "volume" => "24",
      "part" => "C",
      "number" => "10",
    ], Util::parseEnumerationAndFirstPage("24:C:10"));

    $this->assertEquals([], Util::parseEnumerationAndFirstPage(""));
  }

  /**
   * Test function getLastArrayElement.
   */
  public function testGetLastArrayElement() {
    $this->assertEquals("Llama", Util::getLastArrayElement([], "Llama"));
    $this->assertEquals("Llama", Util::getLastArrayElement(["Llama"]));
    $this->assertEquals("Llama", Util::getLastArrayElement(["Alpaca", "Llama"]));
    $this->assertEquals(2, Util::getLastArrayElement([
      'one' => 1,
      'two' => 2,
    ]));
  }

}
