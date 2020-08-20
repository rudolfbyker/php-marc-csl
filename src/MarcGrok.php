<?php

namespace RudolfByker\PhpMarcCsl;

use Scriptotek\Marc\Record;

/**
 * Class MarcGrok.
 *
 * Wraps a MARC Record and provide methods to extract and interpret the data.
 *
 * @see http://marcspec.github.io/MARCspec/marc-spec.html
 */
class MarcGrok {

  /**
   * The MARC record being wrapped.
   *
   * @var \Scriptotek\Marc\Record
   */
  protected $record;

  /**
   * All names, excluding meetings, series names.
   *
   * @var array
   */
  private $names = NULL;

  /**
   * All series names.
   *
   * @var array
   */
  private $seriesNames = NULL;

  /**
   * All meetings.
   *
   * @var array
   */
  private $meetings = NULL;

  /**
   * The container (parent record) info.
   *
   * @var array
   */
  private $container = NULL;

  /**
   * The publication info (publisher, date, etc.)
   *
   * @var array
   */
  private $publication = NULL;

  /**
   * MarcGrok constructor.
   *
   * @param \Scriptotek\Marc\Record $record
   *   The MARC record to wrap.
   */
  public function __construct(Record $record) {
    $this->record = $record;
  }

  /**
   * Get the wrapped MARC record.
   *
   * @return \Scriptotek\Marc\Record
   *   The MARC record.
   */
  public function getRecord(): Record {
    return $this->record;
  }

  /**
   * Extract names from an X00 or X10 field.
   *
   * MARC: Any tag containing personal names (X00) or corporate names (X10).
   * - X00 - Personal Names-General Information
   *     - $a - Personal name (NR)
   *     - $c - Titles and words associated with a name (R)
   *     - $e - Relator term (R)
   * - X10 - Corporate Names-General Information
   *     - $a - Corporate name or jurisdiction name as entry element (NR)
   *     - $c - Location of meeting (R)
   *     - $e - Relator term (R)
   *
   * @param string $tag
   *   The tag of the field from which to extract the names.
   *
   * @see https://www.loc.gov/marc/bibliographic/bdx00.html
   * @see https://www.loc.gov/marc/bibliographic/bdx10.html
   * @see https://www.loc.gov/marc/relators/relaterm.html
   *
   * @return array
   *   An array of arrays, each serializable to CSL-JSON name variables.
   */
  protected function extractNames(string $tag): array {
    $names = [];
    /** @var \Scriptotek\Marc\Fields\Field $field */
    foreach ($this->record->query($tag) as $field) {

      // This is one of those rare cases where CSL is more detailed than
      // MARC. We could try to parse the name, but it's probably not worth
      // the effort, so we just stick everything in "family".
      // See https://citeproc-js.readthedocs.io/en/latest/csl-json/markup.html#name-fields
      // TODO: Take first indicator into account.
      $personal_name = $field->getSubfield('a');
      if (!$personal_name) {
        // We don't have a name, so skip this field.
        continue;
      }

      $name = ['family' => $personal_name->getData()];

      // Use "Titles and words associated with a name" or "Location of meeting"
      // as the suffix in CSL, if available.
      $associated_words = $field->getSubfield('c');
      if ($associated_words) {
        $name['suffix'] = $associated_words->getData();
      }

      // Now look at the relator code subfield to see what the relationship of
      // this name is with the work.
      $relator_terms = $field->getSubfieldValues('e');

      // If there are no relator terms, assume it's an author.
      if (!count($relator_terms)) {
        $relator_terms[] = RelatorTerm::AUTHOR;
      }

      foreach ($relator_terms as $relator_term) {
        $names[$relator_term][] = $name;
      }
    }

    return $names;
  }

  /**
   * Get all personal, corporate and uncontrolled names.
   *
   * Memoized. See getAllNamesNotMemoized for details.
   *
   * @see \RudolfByker\PhpMarcCsl\MarcGrok::getAllNamesNotMemoized()
   *
   * @return array
   *   An array of arrays containing CSL name variables, keyed by relator code.
   */
  public function getAllNames(): array {
    if (!$this->names) {
      $this->names = $this->getAllNamesNotMemoized();
    }
    return $this->names;
  }

  /**
   * Get all personal, corporate and uncontrolled names.
   *
   * Excludes meeting names and series names.
   * Not memoized.
   *
   * @see https://www.loc.gov/marc/bibliographic/bdx00.html
   * @see https://www.loc.gov/marc/bibliographic/bdx10.html
   * @see https://www.loc.gov/marc/bibliographic/bd720.html
   * @see https://www.loc.gov/marc/relators/relaterm.html
   *
   * @return array
   *   An array of arrays containing CSL name variables, keyed by relator code.
   */
  private function getAllNamesNotMemoized(): array {
    $tags = [
      // Main Entry - Personal Name.
      '100',
      // Main Entry - Corporate Name.
      '110',
      // Subject Added Entry - Personal Name.
      '600',
      // 610 - Subject Added Entry - Corporate Name
      '610',
      // Added Entry - Personal Name.
      '700',
      // Added Entry - Corporate Name.
      '710',
      // Added Entry - Uncontrolled Name.
      '720',
    ];

    $names = [];
    foreach ($tags as $tag) {
      $names = array_merge($names, $this->extractNames($tag));
    }
    return $names;
  }

  /**
   * Get all series names.
   *
   * Memoized. See getAllSeriesNamesNotMemoized for details.
   *
   * @see \RudolfByker\PhpMarcCsl\MarcGrok::getAllSeriesNamesNotMemoized()
   *
   * @return array
   *   An array of arrays containing CSL name variables, keyed by relator code.
   */
  public function getAllSeriesNames(): array {
    if (!$this->seriesNames) {
      $this->seriesNames = $this->getAllSeriesNamesNotMemoized();
    }
    return $this->seriesNames;
  }

  /**
   * Get all series names.
   *
   * Excludes meeting names.
   * Not memoized.
   *
   * @see https://www.loc.gov/marc/bibliographic/bdx00.html
   * @see https://www.loc.gov/marc/bibliographic/bdx10.html
   * @see https://www.loc.gov/marc/relators/relaterm.html
   *
   * @return array
   *   An array of arrays containing CSL name variables, keyed by relator code.
   */
  private function getAllSeriesNamesNotMemoized(): array {
    $tags = [
      // Series Added Entry-Personal Name (R)
      '800',
      // Series Added Entry-Corporate Name (R)
      '810',
    ];

    $names = [];
    foreach ($tags as $tag) {
      $names = array_merge($names, $this->extractNames($tag));
    }
    return $names;
  }

  /**
   * Get all meetings.
   *
   * Memoized. See getAllMeetingsNotMemoized for details.
   *
   * @see \RudolfByker\PhpMarcCsl\MarcGrok::getAllMeetingsNotMemoized()
   *
   * @return array
   *   An array of arrays containing CSL name variables, keyed by relator code.
   */
  public function getAllMeetings(): array {
    if (!$this->meetings) {
      $this->meetings = $this->getAllMeetingsNotMemoized();
    }
    return $this->meetings;
  }

  /**
   * Get all meetings.
   *
   * Not memoized.
   *
   * @see https://www.loc.gov/marc/bibliographic/bdx11.html
   *
   * @return array
   *   An array of meetings. Each meeting can have the following keys:
   *   - `name`: string
   *   - `locations`: array of strings
   *   - `dates`: array of strings
   */
  private function getAllMeetingsNotMemoized(): array {
    $tags = [
      // Main Entry - Meeting Name.
      '111',
      // Subject Added Entry - Meeting Name
      '611',
      // Added Entry - Meeting Name.
      '711',
      // Series Added Entry - Meeting Name
      '811',
    ];

    $meetings = [];
    foreach ($tags as $tag) {
      /** @var \Scriptotek\Marc\Fields\Field $field */
      foreach ($this->record->query($tag) as $field) {

        /*
         * Here we collect data for multiple CSL variables:
         * - event (The event name)
         * - event-place
         * - event-date
         */

        // $a - Meeting name or jurisdiction name as entry element (NR).
        $name = $field->getSubfield('a');
        if (!$name) {
          // We don't have a name, so skip this field.
          continue;
        }

        $meeting = ['name' => $name->getData()];

        // $c - Location of meeting (R).
        $locations = $field->getSubfieldValues('c');
        if (count($locations)) {
          $meeting['locations'] = $locations;
        }

        // $d - Date of meeting or treaty signing (R).
        $dates = $field->getSubfieldValues('d');
        if (count($dates)) {
          $meeting['dates'] = $dates;
        }

        $meetings[] = $meeting;
      }
    }
    return $meetings;
  }

  /**
   * Get all container (parent record) info.
   *
   * Memoized. See getContainerInfoNotMemoized for more info.
   *
   * @see \RudolfByker\PhpMarcCsl\MarcGrok::getContainerInfoNotMemoized()
   *
   * @return array
   *   Associative array of container info.
   */
  public function getContainerInfo(): array {
    if (!$this->container) {
      $this->container = $this->getContainerInfoNotMemoized();
    }
    return $this->container;
  }

  /**
   * Get all container (parent record) info.
   *
   * Not memoized.
   *
   * MARC: 773 - Host Item Entry (R)
   * - $t - Title (NR) -> title
   * - $p - Abbreviated title (NR) -> title_short
   * - $g - Related parts (R) -> pages, number, volume, part, date
   * - $q - Enumeration and first page (NR) -> first page, number, volume, part
   * - $b - Edition (NR) -> edition
   * - $a - Main entry heading (NR) -> authors
   *
   * Subfield $q is considered before subfield $g for number, volume and part,
   * because it uses a fixed format which can be reliably parsed. However, if
   * a range of pages needs to be specified, we have to get it is from $g, since
   * $q only contains the first page in the range.
   *
   * @see https://www.loc.gov/marc/bibliographic/bd773.html
   *
   * @return array
   *   Associative array of container info.
   */
  private function getContainerInfoNotMemoized(): array {
    $result = [];

    // $t - Title (NR)
    $t = $this->record->query('773[0]$t')->text();
    if ($t) {
      $result['title'] = $t;
    }

    // $p - Abbreviated title (NR)
    $p = $this->record->query('773[0]$p')->text();
    if ($p) {
      $result['title_short'] = $p;
    }

    // $q and $g contain almost the same info, but $q is less error prone,
    // because $g is an ill-defined human readable format.
    // $g - Related parts (R)
    $g = Util::parseRelatedParts($this->record->query('773[0]$g')->text() ?? "");
    // $q - Enumeration and first page (NR)
    $q = Util::parseEnumerationAndFirstPage($this->record->query('773[0]$q')->text() ?? "");

    // Prefer $g over $q for pages, since $q only has the starting page.
    if (!empty($g['pages'])) {
      $result['pages'] = $g['pages'];
    }
    elseif (!empty($q['page'])) {
      $result['pages'] = $q['page'];
    }

    if (!empty($q['page'])) {
      $result['first_page'] = $q['page'];
    }

    if (!empty($g['date'])) {
      $result['date'] = $g['date'];
    }

    if (!empty($q['number'])) {
      $result['number'] = $q['number'];
    }
    elseif (!empty($g['number'])) {
      $result['number'] = $g['number'];
    }

    if (!empty($q['volume'])) {
      $result['volume'] = $q['volume'];
    }
    elseif (!empty($g['volume'])) {
      $result['volume'] = $g['volume'];
    }

    if (!empty($q['part'])) {
      $result['part'] = $q['part'];
    }
    elseif (!empty($g['part'])) {
      $result['part'] = $g['part'];
    }

    // $b - Edition (NR)
    $b = $this->record->query('773[0]$b')->text();
    if ($b) {
      $result['edition'] = $b;
    }

    // $a - Main entry heading (NR)
    // TODO: Is this field really supposed to contain the author?
    // When creating a child record in Koha, the host's '100$a$b' value is
    // copied to the child's '773$a' value. This happens in
    // 'prepare_host_field' in 'Biblio.pm'.
    $a = $this->record->query('773$a')->text();
    if ($a) {
      $result['author'] = ['family' => $a];
    }

    // TODO: "section" ? Not sure where to find this in MARC data other than
    // $g or $q, but there are no examples of this in the MARC spec.

    return $result;
  }

  /**
   * Get all info about publishers, etc.
   *
   * Memoized. See getAllPublicationInfoNotMemoized for details.
   *
   * @see \RudolfByker\PhpMarcCsl\MarcGrok::getAllPublicationInfoNotMemoized
   *
   * @return array
   *   Associative array of publication info.
   */
  public function getAllPublicationInfo(): array {
    if (!$this->publication) {
      $this->publication = $this->getAllPublicationInfoNotMemoized();
    }
    return $this->publication;
  }

  /**
   * Get all info about publishers, etc.
   *
   * Not memoized.
   *
   * MARC:
   * - 260 - Publication, Distribution, etc. (Imprint) (R)
   *     - $a - Place of publication, distribution, etc. (R)
   *     - $b - Name of publisher, distributor, etc. (R)
   *     - $c - Date of publication, distribution, etc. (R)
   *     - $e - Place of manufacture (R)
   *     - $f - Manufacturer (R)
   *     - $g - Date of manufacture (R)
   * - 264 - Production, Publication, Distribution, Manufacture, and Copyright Notice (R)
   *     - $a - Place of production, publication, distribution, manufacture
   *     - $b - Name of producer, publisher, distributor, manufacturer (R)
   *     - $c - Date of production, publication, distribution, manufacture, or copyright notice (R)
   *
   * Results are grouped by the named entity's function (e.g. publisher,
   * producer, etc.). Each group is sorted, first by the first indicator, then
   * by date, so that the current/latest entity is listed first,
   *
   * To keep things simple, we support the repeated fields, but not repeated
   * subfields here. We simply use the first subfield if available.
   *
   * @see https://www.loc.gov/marc/bibliographic/bd260.html
   * @see https://www.loc.gov/marc/bibliographic/bd264.html
   *
   * @return array
   *   Associative array of publication info.
   */
  private function getAllPublicationInfoNotMemoized(): array {
    $info = [];

    // Map 264's second indicator value to the entity's function.
    $functions = [
      0 => 'producer',
      1 => 'publisher',
      2 => 'distributor',
      3 => 'manufacturer',
      4 => 'copyright',
    ];

    // 260 - Publication, Distribution, etc. (Imprint) (R)
    /** @var \Scriptotek\Marc\Fields\Field $field */
    foreach ($this->record->query('260') as $field) {
      // $a - Place of publication, distribution, etc. (R)
      $a = Util::trimNonWordCharacters($field->getSubfieldValues('a')[0] ?? "");
      // $b - Name of publisher, distributor, etc. (R)
      $b = Util::trimNonWordCharacters($field->getSubfieldValues('b')[0] ?? "");
      // $c - Date of publication, distribution, etc. (R)
      $c = Util::trimNonWordCharacters($field->getSubfieldValues('c')[0] ?? "");
      // $e - Place of manufacture (R)
      $e = Util::trimNonWordCharacters($field->getSubfieldValues('e')[0] ?? "");
      // $f - Manufacturer (R)
      $f = Util::trimNonWordCharacters($field->getSubfieldValues('f')[0] ?? "");
      // $g - Date of manufacture (R)
      $g = Util::trimNonWordCharacters($field->getSubfieldValues('g')[0] ?? "");

      // First Indicator: Sequence of publishing statements
      // # - Not applicable/No information provided/Earliest available publisher
      // 2 - Intervening publisher
      // 3 - Current/latest publisher
      // We want the current/latest to be first, so we can use the indicator
      // as the value by which to sort the statements.
      $ind1 = $field->getIndicator(1);

      if ($a || $b || $c) {
        // Add a publisher.
        $info['publisher'][] = array_filter([
          'place' => $a,
          'name' => $b,
          'date' => $c,
          'priority' => (int) $ind1,
        ]);
      }

      if ($e || $f || $g) {
        // Add a manufacturer.
        $info['manufacturer'][] = array_filter([
          'place' => $e,
          'name' => $f,
          'date' => $g,
          'priority' => (int) $ind1,
        ]);
      }
    }

    // 264 - Production, Publication, Distribution, Manufacture, and
    // Copyright Notice (R)
    /** @var \Scriptotek\Marc\Fields\Field $field */
    foreach ($this->record->query('264') as $field) {
      // First Indicator: Sequence of publishing statements
      // # - Not applicable/No information provided/Earliest
      // 2 - Intervening
      // 3 - Current/latest
      // We want the current/latest to be first, so we can use the indicator
      // as the value by which to sort the statements.
      $ind1 = $field->getIndicator(1);

      // Second Indicator: Function of entity
      // 0 - Production
      // 1 - Publication
      // 2 - Distribution
      // 3 - Manufacture
      // 4 - Copyright notice date.
      $ind2 = $field->getIndicator(2);
      // If the second indicator is invalid or missing, default to "publisher".
      $function = $functions[$ind2] ?? 'publisher';
      if ($function) {
        // Add a statement for the selected function.
        $info[$function][] = array_filter([
          // $a - Place of production, publication, distribution, manufacture
          'place' => Util::trimNonWordCharacters($field->getSubfieldValues('a')[0] ?? ""),
          // $b - Name of producer, publisher, distributor, manufacturer (R)
          'name' => Util::trimNonWordCharacters($field->getSubfieldValues('b')[0] ?? ""),
          // $c - Date of production, publication, distribution, manufacture,
          // or copyright notice (R)
          'date' => Util::trimNonWordCharacters($field->getSubfieldValues('c')[0] ?? ""),
          'priority' => (int) $ind1,
        ]);
      }
    }

    foreach ($info as $function => &$statements) {
      usort($statements, function ($a, $b) {
        // Sort by priority DESC.
        $priority_cmp = ($b['priority'] ?? 0) - ($a['priority'] ?? 0);
        if ($priority_cmp != 0) {
          return $priority_cmp;
        }

        // Sort by date DESC.
        return strcmp($b['date'] ?? "", $a['date'] ?? "");
      });
    }

    foreach ($info as $function => &$statements) {
      foreach ($statements as &$statement) {
        unset($statement['priority']);
      }
    }

    return $info;
  }

}
