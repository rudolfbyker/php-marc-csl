<?php


namespace RudolfByker\PhpMarcCsl\Tests;


use PHPUnit\Framework\TestCase;
use RudolfByker\PhpMarcCsl\MarcCslVariables;
use Scriptotek\Marc\Record;

/**
 * Test the MarcCslVariables class.
 *
 * @group php-marc-csl
 */
class MarcCslVariablesTest extends TestCase {

  /**
   * Test the getTitle method.
   */
  public function testGetTitle() {
    $marcCsl = new MarcCslVariables(Record::fromString(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield ind1="1" ind2="0" tag="245">
    <subfield code="a">Ons en ons kinders :</subfield>
    <subfield code="b">besinninge oor die verhouding ouer-kind /</subfield>
    <subfield code="c">deur C.K. Oberholzer.</subfield>
  </datafield>
</record>
XML
    ));
    $this->assertEquals(
      "Ons en ons kinders : besinninge oor die verhouding ouer-kind",
      $marcCsl->getTitle()
    );

    $marcCsl = new MarcCslVariables(Record::fromString(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield ind1="1" ind2="0" tag="245">
    <subfield code="a">'Let the Dead Bury Their Dead' (Matt. 8:22/Luke 9:60) :</subfield>
    <subfield code="b">Jesus and the Halakhah</subfield>
  </datafield>
</record>
XML
    ));
    $this->assertEquals(
      "'Let the Dead Bury Their Dead' (Matt. 8:22/Luke 9:60) : Jesus and the Halakhah",
      $marcCsl->getTitle()
    );
  }

  /**
   * Test the getAll method.
   */
  public function testGetAll() {
    $this->assertGetAll(
      <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="245">
    <subfield code="a">'Let the Dead Bury Their Dead' (Matt. 8:22/Luke 9:60) :</subfield>
    <subfield code="b">Jesus and the Halakhah</subfield>
  </datafield>
  <datafield tag="773">
    <subfield code="t">The Journal of Theological Studies</subfield>
    <subfield code="g">Vol. 49, no. 2 (October 1998), p. 553-581</subfield>
    <subfield code="q">49:2&lt;553</subfield>
  </datafield>
  <datafield tag="856">
    <subfield code="u">https://www.jstor.org/stable/23968765</subfield>
  </datafield>
  <datafield tag="100">
    <subfield code="a">Markus Bockmuehl</subfield>
    <subfield code="e">aut</subfield>
  </datafield>
  <datafield tag="260">
    <subfield code="b">Oxford University Press</subfield>
  </datafield>
</record>
XML,
      [
        'title' => "'Let the Dead Bury Their Dead' (Matt. 8:22/Luke 9:60) : Jesus and the Halakhah",
        'container-title' => 'The Journal of Theological Studies',
        'page' => '553-581',
        'page-first' => '553',
        'volume' => '49',
        'issue' => '2',
        'url' => 'https://www.jstor.org/stable/23968765',
        'author' => [['family' => 'Markus Bockmuehl']],
        'issued' => ['raw' => 'October 1998'],
        'publisher' => 'Oxford University Press',
      ],
      "TODO"
    );
  }

  /**
   * Helper function for testGetAll.
   *
   * @param string $xml
   *   The XML data to parse.
   * @param array $expected
   *   The expected result.
   * @param string $message
   *   The assertion message.
   */
  private function assertGetAll(string $xml, array $expected, string $message): void {
    $marcCsl = new MarcCslVariables(Record::fromString($xml));
    $this->assertEquals($expected, $marcCsl->getAll(), $message);
  }

}
