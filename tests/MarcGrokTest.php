<?php

namespace RudolfByker\PhpMarcCsl\Tests;

use PHPUnit\Framework\TestCase;
use RudolfByker\PhpMarcCsl\MarcGrok;
use Scriptotek\Marc\Record;

/**
 * Test the MarcGrok class.
 *
 * @group php-marc-csl
 * @covers \RudolfByker\PhpMarcCsl\MarcGrok
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

  /**
   * Helper for testing getContainerInfo.
   *
   * @param string $xml
   *   The MARC XML source.
   * @param array $result
   *   The expected result.
   * @param string $message
   *   The assertion message.
   */
  private function assertContainerInfo(string $xml, array $result, string $message = "") {
    $grok = new MarcGrok(Record::fromString($xml));
    $this->assertEquals($result, $grok->getContainerInfo(), $message);
  }

  /**
   * Test method getContainerInfo.
   */
  public function testGetContainerInfo() {
    $this->assertContainerInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="773">
    <subfield code="t">The Journal of Theological Studies</subfield>
    <subfield code="p">JTS</subfield>
    <subfield code="g">Vol. 49, no. 2 (October 1998), p. 553-581</subfield>
    <subfield code="q">49:2&lt;553</subfield>
  </datafield>
</record>', [
      'title' => "The Journal of Theological Studies",
      'title_short' => "JTS",
      'volume' => "49",
      'number' => "2",
      'pages' => "553-581",
      'first_page' => "553",
      'date' => "October 1998",
    ], "Journal container with title, volume, number, pages and date.");

    $this->assertContainerInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="773">
    <subfield code="g">Vol. 49, no. 2 (October 1998)</subfield>
    <subfield code="q">49:2&lt;553</subfield>
  </datafield>
</record>', [
      'volume' => "49",
      'number' => "2",
      'pages' => "553",
      'first_page' => "553",
      'date' => "October 1998",
    ], 'Use first page from $q if pages are not specified in $g.');

    $this->assertContainerInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="773">
    <subfield code="g">Vol. 49, no. 2 (October 1998)</subfield>
    <subfield code="q">&lt;553</subfield>
  </datafield>
</record>', [
      'volume' => "49",
      'number' => "2",
      'pages' => "553",
      'first_page' => "553",
      'date' => "October 1998",
    ], 'Use volume and issue number from $g if not specified in $q.');

    $this->assertContainerInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="773">
    <subfield code="g">Vol. 49, pt. C, no. 2 (October 1998)</subfield>
  </datafield>
</record>', [
      'volume' => "49",
      'part' => "C",
      'number' => "2",
      'date' => "October 1998",
    ], 'Volume, part and issue in $g');

    $this->assertContainerInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="773">
    <subfield code="q">49:C:2</subfield>
  </datafield>
</record>', [
      'volume' => "49",
      'part' => "C",
      'number' => "2",
    ], 'Volume, part and issue in $q');

    $this->assertContainerInfo('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="773">
    <subfield code="a">John MacArthur</subfield>
    <subfield code="b">2</subfield>
    <subfield code="t">Biblical Counseling</subfield>
  </datafield>
</record>', [
      'author' => ['family' => "John MacArthur"],
      'title' => "Biblical Counseling",
      'edition' => "2",
    ], 'Book with title, authors and edition');
  }

  /**
   * Helper for testing getAllMeetings.
   *
   * @param string $xml
   *   The MARC XML source.
   * @param array $result
   *   The expected result.
   * @param string $message
   *   The assertion message.
   */
  private function assertAllMeetings(string $xml, array $result, string $message = "") {
    $grok = new MarcGrok(Record::fromString($xml));
    $this->assertEquals($result, $grok->getAllMeetings(), $message);
  }

  /**
   * Test method getAllMeetings.
   */
  public function testGetAllMeetings() {
    $this->assertAllMeetings('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="111">
    <subfield code="a">One</subfield>
  </datafield>
  <datafield tag="111">
    <subfield code="a">Two</subfield>
  </datafield>
  <datafield tag="611">
    <subfield code="a">Three</subfield>
  </datafield>
  <datafield tag="611">
    <subfield code="a">Four</subfield>
  </datafield>
  <datafield tag="711">
    <subfield code="a">Five</subfield>
  </datafield>
  <datafield tag="711">
    <subfield code="a">Six</subfield>
  </datafield>
  <datafield tag="811">
    <subfield code="a">Seven</subfield>
  </datafield>
  <datafield tag="811">
    <subfield code="a">Eight</subfield>
  </datafield>
</record>', [
      ['name' => 'One'],
      ['name' => 'Two'],
      ['name' => 'Five'],
      ['name' => 'Six'],
      ['name' => 'Seven'],
      ['name' => 'Eight'],
    ], "Meeting fields are repeatable.");

    $this->assertAllMeetings('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="111">
    <subfield code="a">DrupalCon</subfield>
    <subfield code="c">Amsterdam</subfield>
    <subfield code="d">2019</subfield>
    <subfield code="c">Barcelona</subfield>
    <subfield code="d">2020</subfield>
  </datafield>
</record>', [
      [
        'name' => 'DrupalCon',
        'locations' => [
          'Amsterdam',
          'Barcelona',
        ],
        'dates' => [
          '2019',
          '2020',
        ],
      ],
    ], "Each meeting can have multiple dates and locations.");

    $this->assertAllMeetings('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="111">
    <subfield code="a">DrupalCon</subfield>
    <subfield code="c">Amsterdam</subfield>
    <subfield code="d">2019</subfield>
  </datafield>
  <datafield tag="111">
    <subfield code="c">Barcelona</subfield>
    <subfield code="d">2020</subfield>
  </datafield>
</record>', [
      [
        'name' => 'DrupalCon',
        'locations' => [
          'Amsterdam',
        ],
        'dates' => [
          '2019',
        ],
      ],
    ], "Skip meetings that don't have names.");

  }

  /**
   * Helper for testing getAllSeriesNames.
   *
   * @param string $xml
   *   The MARC XML source.
   * @param array $result
   *   The expected result.
   * @param string $message
   *   The assertion message.
   */
  private function assertAllSeriesNames(string $xml, array $result, string $message = "") {
    $grok = new MarcGrok(Record::fromString($xml));
    $this->assertEquals($result, $grok->getAllSeriesNames(), $message);
  }

  /**
   * Test method getAllSeriesNames.
   */
  public function testGetAllSeriesNames() {
    $this->assertAllSeriesNames('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="800">
    <subfield code="a">Series author 1</subfield>
  </datafield>
  <datafield tag="800">
    <subfield code="a">Series author 2</subfield>
  </datafield>
  <datafield tag="810">
    <subfield code="a">Another series author</subfield>
  </datafield>
</record>', [
      'aut' => [
        ['family' => 'Series author 1'],
        ['family' => 'Series author 2'],
        ['family' => 'Another series author'],
      ],
    ], "Use fields 800 and 810.");
  }

  /**
   * Helper for testing getAllNames.
   *
   * @param string $xml
   *   The MARC XML source.
   * @param array $result
   *   The expected result.
   * @param string $message
   *   The assertion message.
   */
  private function assertAllNames(string $xml, array $result, string $message = "") {
    $grok = new MarcGrok(Record::fromString($xml));
    $this->assertEquals($result, $grok->getAllNames(), $message);
  }

  /**
   * Test method getAllNames.
   */
  public function testGetAllNames() {
    $this->assertAllNames('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="100">
    <subfield code="a">Person 1</subfield>
  </datafield>
  <datafield tag="100">
    <subfield code="a">Person 2</subfield>
  </datafield>
  <datafield tag="110">
    <subfield code="a">Person 3</subfield>
  </datafield>
  <datafield tag="600">
    <subfield code="a">Person 4</subfield>
  </datafield>
  <datafield tag="610">
    <subfield code="a">Person 5</subfield>
  </datafield>
  <datafield tag="700">
    <subfield code="a">Person 6</subfield>
  </datafield>
  <datafield tag="710">
    <subfield code="a">Person 7</subfield>
  </datafield>
  <datafield tag="720">
    <subfield code="a">Person 8</subfield>
  </datafield>
</record>', [
      'aut' => [
        ['family' => 'Person 1'],
        ['family' => 'Person 2'],
        ['family' => 'Person 3'],
        ['family' => 'Person 6'],
        ['family' => 'Person 7'],
        ['family' => 'Person 8'],
      ],
    ], "Use fields 100, 110, 600, 610, 700, 710 and 720.");

    $this->assertAllNames('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="100">
    <subfield code="a">Name</subfield>
    <subfield code="c">Mr</subfield>
  </datafield>
  <datafield tag="100">
    <subfield code="c">Professor</subfield>
  </datafield>
</record>', [
      'aut' => [
        [
          'family' => 'Name',
          'suffix' => "Mr",
        ],
      ],
    ], 'Skip fields that do not have $a.');

    $this->assertAllNames('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="100">
    <subfield code="a">Name</subfield>
    <subfield code="c">Mr</subfield>
    <subfield code="4">edt</subfield>
  </datafield>
</record>', [
      'edt' => [
        [
          'family' => 'Name',
          'suffix' => "Mr",
        ],
      ],
    ], 'Take relator code into account.');

    // The following are fake examples, i.e. you would not have something like
    // "Bach, Johann Sebastian." when first indicator=3. Having a comma in a
    // name is rare when it's not in surname-first format, but we have to test
    // for it.
    $this->assertAllNames('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="100">
    <subfield code="a">Adams, Henry,</subfield>
  </datafield>
  <datafield tag="100" ind1="0">
    <subfield code="a">Riaño, Juan Facundo,</subfield>
  </datafield>
  <datafield tag="100" ind1="1">
    <subfield code="a">Fowler, Thaddeus Mortimer</subfield>
  </datafield>
  <datafield tag="100" ind1="1">
    <subfield code="a">van der Linden, Jopie</subfield>
  </datafield>
  <datafield tag="100" ind1="3">
    <subfield code="a">Bach, Johann Sebastian.</subfield>
  </datafield>
  <datafield tag="110" ind1="1">
    <subfield code="a">Texas, USA</subfield>
  </datafield>
</record>', [
      'aut' => [
        [
          'family' => 'Adams, Henry',
        ],
        [
          'family' => 'Riaño, Juan Facundo',
        ],
        [
          'given' => 'Thaddeus Mortimer',
          'family' => 'Fowler',
        ],
        [
          'given' => 'Jopie',
          'family' => 'van der Linden',
        ],
        [
          'family' => 'Bach, Johann Sebastian.',
        ],
        [
          'family' => 'Texas, USA',
        ],
      ],
    ], 'For X00 fields, split on comma when first indicator is 1 (surname first).');
  }

  public function testRelators() {
    $this->assertAllNames('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="100" ind1="1" ind2=" ">
    <subfield code="a">Moule, C. F. D.</subfield>
    <subfield code="q">(Charles Francis Digby),</subfield>
    <subfield code="d">1908-2007.</subfield>
  </datafield>
</record>', [
      'aut' => [
        [
          'family' => 'Moule',
          'given' => 'C. F. D.',
        ],
      ],
    ], 'No $e, and no $4.');

    $this->assertAllNames('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="100" ind1="1" ind2=" ">
    <subfield code="a">Moule, C. F. D.</subfield>
    <subfield code="q">(Charles Francis Digby),</subfield>
    <subfield code="d">1908-2007,</subfield>
    <subfield code="e">editor.</subfield>
  </datafield>
</record>', [
      'edt' => [
        [
          'family' => 'Moule',
          'given' => 'C. F. D.',
        ],
      ],
    ], 'Human-readable $e with punctuation, and no $4.');

    $this->assertAllNames('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="100" ind1="1" ind2=" ">
    <subfield code="a">Moule, C. F. D.</subfield>
    <subfield code="q">(Charles Francis Digby),</subfield>
    <subfield code="d">1908-2007.</subfield>
    <subfield code="4">edt</subfield>
  </datafield>
</record>', [
      'edt' => [
        [
          'family' => 'Moule',
          'given' => 'C. F. D.',
        ],
      ],
    ], 'No $e, but $4 is given.');

    $this->assertAllNames('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="100">
    <subfield code="a">An author</subfield>
  </datafield>
  <datafield tag="100">
    <subfield code="a">Another author</subfield>
  </datafield>
  <datafield tag="100">
    <subfield code="a">Not really the author</subfield>
    <subfield code="4">dub</subfield>
  </datafield>
</record>', [
      'aut' => [
        ['family' => 'An author'],
        ['family' => 'Another author'],
      ],
      'dub' => [
        ['family' => 'Not really the author'],
      ],
    ], 'Dubious author should be marked as such.');
  }

  /**
   * Test method getRecord.
   */
  public function testGetRecord() {
    $record = Record::fromString('<?xml version="1.0" encoding="UTF-8"?>
<record>
  <datafield tag="100">
    <subfield code="a">Person 1</subfield>
  </datafield>
</record>');
    $grok = new MarcGrok($record);
    $this->assertSame($record, $grok->getRecord());
  }
}
