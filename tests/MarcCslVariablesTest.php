<?php

namespace RudolfByker\PhpMarcCsl\Tests;

use PHPUnit\Framework\TestCase;
use RudolfByker\PhpMarcCsl\MarcCslVariables;
use Scriptotek\Marc\Record;

/**
 * Test the MarcCslVariables class.
 *
 * @group php-marc-csl
 * @covers \RudolfByker\PhpMarcCsl\MarcCslVariables
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
   * Test the getOriginalPublisher method.
   */
  public function testGetOriginalPublisher() {
    $marcCsl = new MarcCslVariables(Record::fromString(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="260">
    <subfield code="b">Canon Press</subfield>
    <subfield code="c">2000</subfield>
  </datafield>
  <datafield tag="260">
    <subfield code="b">Oxford University Press</subfield>
    <subfield code="c">1950</subfield>
  </datafield>
</record>
XML
    ));
    $this->assertEquals(
      "Oxford University Press",
      $marcCsl->getOriginalPublisher(),
      "Original publisher is the one with the oldest date."
    );

    $marcCsl = new MarcCslVariables(Record::fromString(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="260">
    <subfield code="b">Canon Press</subfield>
    <subfield code="c">2000</subfield>
  </datafield>
</record>
XML
    ));
    $this->assertEquals(
      "",
      $marcCsl->getOriginalPublisher(),
      "Original publisher is empty if there is only one publisher."
    );

    $marcCsl = new MarcCslVariables(Record::fromString(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="100">
    <subfield code="a">Author</subfield>
  </datafield>
</record>
XML
    ));
    $this->assertEquals(
      "",
      $marcCsl->getOriginalPublisher(),
      "Handle NO publisher gracefully."
    );
  }

  /**
   * Test the getPublisher method.
   */
  public function testGetPublisher() {
    $marcCsl = new MarcCslVariables(Record::fromString(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="260">
    <subfield code="b">Canon Press</subfield>
    <subfield code="c">2000</subfield>
  </datafield>
  <datafield tag="260">
    <subfield code="b">Oxford University Press</subfield>
    <subfield code="c">1950</subfield>
  </datafield>
</record>
XML
    ));
    $this->assertEquals(
      "Canon Press",
      $marcCsl->getPublisher(),
      "Publisher is the one with the latest date."
    );
  }

  /**
   * Test the getAuthor method.
   */
  public function testGetAuthor() {
    $marcCsl = new MarcCslVariables(Record::fromString(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="100">
    <subfield code="a">An author</subfield>
  </datafield>
  <datafield tag="100">
    <subfield code="a">Another author</subfield>
  </datafield>
  <datafield tag="100">
    <subfield code="a">Not really the author</subfield>
    <subfield code="e">dub</subfield>
  </datafield>
</record>
XML
    ));
    $this->assertEquals(
      [
        ['family' => "An author"],
        ['family' => "Another author"],
      ],
      $marcCsl->getAuthor(),
      "Don't use dubious author if any other author is available."
    );

    $marcCsl = new MarcCslVariables(Record::fromString(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="100">
    <subfield code="a">Not really the author</subfield>
    <subfield code="e">dub</subfield>
  </datafield>
</record>
XML
    ));
    $this->assertEquals(
      [
        ['family' => "Not really the author"],
      ],
      $marcCsl->getAuthor(),
      "Use dubious author when no other author is available."
    );
  }

  /**
   * Test the getAll method.
   */
  public function testGetAll() {
    $this->assertGetAll(
      '<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="245">
    <subfield code="a">\'Let the Dead Bury Their Dead\' (Matt. 8:22/Luke 9:60) :</subfield>
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
</record>',
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

  public function testJsonSerialize() {
    $marcCsl = new MarcCslVariables(Record::fromString('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="245">
    <subfield code="a">Title :</subfield>
    <subfield code="b">Subtitle</subfield>
  </datafield>
  <datafield tag="100">
    <subfield code="a">Author</subfield>
  </datafield>
</record>'));
    $this->assertEquals(
      '{"title":"Title : Subtitle","author":[{"family":"Author"}]}',
      json_encode($marcCsl),
      "Object is JSON-serializable."
    );
  }

  /**
   * Test the type property.
   */
  public function testTypeProperty() {
    $csl = new MarcCslVariables(Record::fromString('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="245">
    <subfield code="a">Title :</subfield>
    <subfield code="b">Subtitle</subfield>
  </datafield>
</record>'));
    $csl->type = "article-journal";
    $data = json_decode(json_encode($csl));
    $this->assertEquals($data->type, "article-journal");
  }

}
