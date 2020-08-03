<?php

namespace RudolfByker\PhpMarcCsl\Tests;

use RudolfByker\PhpMarcCsl\MarcGrok;
use PHPUnit\Framework\TestCase;
use Scriptotek\Marc\Record;

/**
 * Test the MarcGrok class.
 *
 * @group php-marc-csl
 */
class MarcGrokTest extends TestCase {

  /**
   * Helper for testing getAllPublicationInfo.
   *
   * @param string $xml
   *   The MARC XML source.
   * @param array $result
   *   The expected result.
   * @param string $message
   *   The assertion message.
   */
  private function assertPublicationInfo(string $xml, array $result, string $message = "") {
    $grok = new MarcGrok(Record::fromString($xml));
    $this->assertEquals($result, $grok->getAllPublicationInfo(), $message);
  }

  /**
   * Test method getAllPublicationInfo.
   */
  public function testGetAllPublicationInfo() {
    $this->assertPublicationInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="260" ind1=" " ind2=" ">
    <subfield code="a">Los Angeles :</subfield>
    <subfield code="b">SAGE,</subfield>
    <subfield code="c">c2009.</subfield>
  </datafield>
</record>', [
      'publisher' => [
        [
          'place' => "Los Angeles",
          'name' => "SAGE",
          'date' => "c2009",
        ],
      ],
    ], "Single publisher in 260.");


    $this->assertPublicationInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="260" ind1=" " ind2=" ">
    <subfield code="e">(Oak Ridge, Tenn. :</subfield>
    <subfield code="f">Oak Ridge National Laboratory,</subfield>
    <subfield code="g">1988</subfield>
  </datafield>
</record>', [
      'manufacturer' => [
        [
          'place' => "Oak Ridge, Tenn",
          'name' => "Oak Ridge National Laboratory",
          'date' => "1988",
        ],
      ],
    ], "Single manufacturer in 260.");

    $this->assertPublicationInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="260" ind1=" " ind2=" ">
    <subfield code="a">Los Angeles :</subfield>
    <subfield code="b">SAGE,</subfield>
    <subfield code="c">c2009.</subfield>
  </datafield>
  <datafield tag="260" ind1="3" ind2=" ">
    <subfield code="a">Los Angeles :</subfield>
    <subfield code="b">Latest publisher,</subfield>
    <subfield code="c">2020.</subfield>
  </datafield>
</record>', [
      'publisher' => [
        [
          'place' => "Los Angeles",
          'name' => "Latest publisher",
          'date' => "2020",
        ],
        [
          'place' => "Los Angeles",
          'name' => "SAGE",
          'date' => "c2009",
        ],
      ],
    ], "Two publishers in 260. Using first indicator.");

    $this->assertPublicationInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="260" ind1=" " ind2=" ">
    <subfield code="c">1941</subfield>
  </datafield>
  <datafield tag="260" ind1="2" ind2=" ">
    <subfield code="c">1920</subfield>
  </datafield>
  <datafield tag="260" ind1="2" ind2=" ">
    <subfield code="c">1951</subfield>
  </datafield>
  <datafield tag="260" ind1=" " ind2=" ">
    <subfield code="c">2001</subfield>
  </datafield>
  <datafield tag="260" ind1="2" ind2=" ">
    <subfield code="c">1968</subfield>
  </datafield>
</record>', [
      'publisher' => [
        ['date' => "1968"],
        ['date' => "1951"],
        ['date' => "1920"],
        ['date' => "2001"],
        ['date' => "1941"],
      ],
    ], "Sort by indicator DESC, then date DESC.");

    $this->assertPublicationInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="264" ind1=" " ind2=" ">
    <subfield code="a">Los Angeles :</subfield>
    <subfield code="b">SAGE,</subfield>
    <subfield code="c">c2009.</subfield>
  </datafield>
</record>', [
      'publisher' => [
        [
          'place' => "Los Angeles",
          'name' => "SAGE",
          'date' => "c2009",
        ],
      ],
    ], "A publisher in 264. Missing second indicator.");

    $this->assertPublicationInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="264" ind1=" " ind2="1">
    <subfield code="a">Los Angeles :</subfield>
    <subfield code="b">SAGE,</subfield>
    <subfield code="c">c2009.</subfield>
  </datafield>
</record>', [
      'publisher' => [
        [
          'place' => "Los Angeles",
          'name' => "SAGE",
          'date' => "c2009",
        ],
      ],
    ], "A publisher in 264. Using second indicator.");

    $this->assertPublicationInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="264" ind1=" " ind2="3">
    <subfield code="a">Los Angeles :</subfield>
    <subfield code="b">SAGE,</subfield>
    <subfield code="c">c2009.</subfield>
  </datafield>
</record>', [
      'manufacturer' => [
        [
          'place' => "Los Angeles",
          'name' => "SAGE",
          'date' => "c2009",
        ],
      ],
    ], "A manufacturer in 264. Using second indicator.");

    $this->assertPublicationInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="264" ind1="3" ind2="3">
    <subfield code="a">Kaapstad</subfield>
    <subfield code="b">Cape Steel</subfield>
    <subfield code="c">1961</subfield>
  </datafield>
  <datafield tag="260" ind1=" " ind2=" ">
    <subfield code="a">Los Angeles :</subfield>
    <subfield code="b">SAGE,</subfield>
    <subfield code="c">c2009.</subfield>
  </datafield>
  <datafield tag="260" ind1="3" ind2=" ">
    <subfield code="a">Los Angeles :</subfield>
    <subfield code="b">Latest publisher,</subfield>
    <subfield code="c">2020.</subfield>
  </datafield>
  <datafield tag="260" ind1=" " ind2=" ">
    <subfield code="e">(Oak Ridge, Tenn. :</subfield>
    <subfield code="f">Oak Ridge National Laboratory,</subfield>
    <subfield code="g">1988</subfield>
  </datafield>
</record>', [
      'manufacturer' => [
        [
          'place' => "Kaapstad",
          'name' => "Cape Steel",
          'date' => "1961",
        ],
        [
          'place' => "Oak Ridge, Tenn",
          'name' => "Oak Ridge National Laboratory",
          'date' => "1988",
        ],
      ],
      'publisher' => [
        [
          'place' => "Los Angeles",
          'name' => "Latest publisher",
          'date' => "2020",
        ],
        [
          'place' => "Los Angeles",
          'name' => "SAGE",
          'date' => "c2009",
        ],
      ],
    ], "Multiple values in 260 and 264.");
  }

}
