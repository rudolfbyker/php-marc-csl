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
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield ind1=" " ind2=" " tag="">
    <subfield code=""></subfield>
  </datafield>
</record>';
    $marcCsl = new MarcCslVariables(Record::fromString($xml));
    $all = $marcCsl->getAll();
    // TODO: assert something.
    echo '';
    $this->markTestIncomplete("TODO: Implement test for MarcCslVariables.");
  }

}
