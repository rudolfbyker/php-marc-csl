<?php

namespace RudolfByker\PhpMarcCsl;

use JsonSerializable;

/**
 * Class MarcCslVariables.
 *
 * Wraps a MARC Record to provide Citation Style Language variables.
 *
 * Objects of this class can be serialized to CSL JSON, a.k.a. CiteProc JSON:
 *   $this->jsonSerialize();
 * OR:
 *   json_encode($this);
 *
 * Built from the CSL 1.0.1 Specification docs, accessed 2020-07-30.
 *
 * @see https://docs.citationstyles.org/en/stable/specification.html#appendix-iv-variables
 * @see https://citeproc-js.readthedocs.io/en/latest/csl-json/markup.html
 * @see http://marcspec.github.io/MARCspec/marc-spec.html
 */
class MarcCslVariables extends MarcGrok implements JsonSerializable {

  /*
   * STANDARD VARIABLES
   * https://docs.citationstyles.org/en/stable/specification.html#standard-variables
   */

  /**
   * Get the abstract of the item (e.g. the abstract of a journal article).
   */
  public function getAbstract(): string {
    // TODO: Use 520[0]$a$b, but only if first indicator is 3.
    return "";
  }

  /**
   * Get the reader’s notes about the item content.
   */
  public function getAnnote(): string {
    // TODO: Maybe use 500[0]$a ?
    return "";
  }

  /**
   * Get the archive storing the item.
   */
  public function getArchive(): string {
    // TODO:
    // - 535[0]$a ?
    // - 850[0]$a ?
    // - 852[0]$a$b ?
    return "";
  }

  /**
   * Get the storage location within an archive (e.g. a box and folder number).
   */
  public function getArchiveLocation(): string {
    // TODO:
    // - 852[0]$c ?
    return "";
  }

  /**
   * Get the geographic location of the archive.
   */
  public function getArchivePlace(): string {
    // TODO:
    // - 852[0]$e ?
    return "";
  }

  /**
   * Get the issuing or judicial authority.
   *
   * (e.g. “USPTO” for a patent, “Fairfax Circuit Court” for a legal case).
   */
  public function getAuthority(): string {
    // TODO:
    return "";
  }

  /**
   * Get the call number (to locate the item in a library)
   */
  public function getCallNumber(): string {
    // TODO: Support other types of call numbers.
    // Dewey:
    return $this->record->query('082[0]$a')->text() ?? "";
  }

  /**
   * Get the title of the collection holding the item.
   *
   * (e.g. the series title for a book).
   */
  public function getCollectionTitle(): string {
    // The series name is normally in:
    // 490 - Series Statement (R)
    // $a - Series statement (R)
    $seriesStatement = $this->record->query('490[0]$a')->text();

    // Sometimes there is more info in:
    // 830 - Series Added Entry-Uniform Title (R)
    // $a - Uniform title (NR)
    $seriesAddedEntryUniformTitle = $this->record->query('830[0]$a')->text();

    // If this is a sub-series, the title of the main series may be in 760$t.
    $mainSeriesTitle = $this->record->query('760[0]$t')->text();

    return Util::trimNonWordCharacters($seriesStatement ?: $seriesAddedEntryUniformTitle ?: $mainSeriesTitle ?: "");
  }

  /**
   * Get the title of the container holding the item.
   *
   * (e.g. the book title for a book chapter, the journal title for a journal
   * article).
   */
  public function getContainerTitle(): string {
    return $this->getContainerInfo()['title'] ?? "";
  }

  /**
   * Get the short/abbreviated form of “container-title”.
   *
   * (also accessible through the “short” form of the “container-title”
   * variable).
   */
  public function getContainerTitleShort(): string {
    return $this->getContainerInfo()['title_short'] ?? "";
  }

  /**
   * Get the physical (e.g. size) or temporal (e.g. running time) dimensions.
   */
  public function getDimensions(): string {
    // TODO:
    return "";
  }

  /**
   * Get the Digital Object Identifier (e.g. “10.1128/AEM.02591-07”).
   */
  public function getDoi(): string {
    // TODO:
    return "";
  }

  /**
   * Get the name of the related event.
   *
   * (e.g. the conference name when citing a conference paper).
   */
  public function getEvent(): string {
    $meetings = $this->getAllMeetings();
    return $meetings[0]->name ?? "";
  }

  /**
   * Get the geographic location of the related event.
   *
   * (e.g. “Amsterdam, the Netherlands”).
   */
  public function getEventPlace(): string {
    $meetings = $this->getAllMeetings();
    $firstLocation = $meetings[0]->locations[0] ?? "";
    return Util::trimNonWordCharacters($firstLocation) ?: "";
  }

  /**
   * Get the class, type or genre of the item.
   *
   * (e.g. “adventure” for an adventure movie, “PhD dissertation” for a PhD
   * thesis).
   */
  public function getGenre(): string {
    // TODO:
    return "";
  }

  /**
   * Get the International Standard Book Number.
   */
  public function getIsbn(): string {
    return $this->record->query('020[0]$a')->text() ?? "";
  }

  /**
   * Get the International Standard Serial Number.
   */
  public function getIssn(): string {
    return $this->record->query('022[0]$a')->text() ?? "";
  }

  /**
   * Get the geographic scope of relevance (e.g. “US” for a US patent).
   */
  public function getJurisdiction(): string {
    // TODO:
    return "";
  }

  /**
   * Get the keyword(s) or tag(s) attached to the item.
   */
  public function getKeyword(): string {
    // TODO:
    return "";
  }

  /**
   * Get the medium description (e.g. “CD”, “DVD”, etc.).
   */
  public function getMedium(): string {
    // TODO:
    return "";
  }

  /**
   * Get the (short) inline note giving additional item details.
   *
   * (e.g. a concise summary or commentary).
   */
  public function getNote(): string {
    // TODO:
    return "";
  }

  /**
   * Get the original publisher.
   *
   * For items that have been republished by a different publisher.
   */
  public function getOriginalPublisher(): string {
    $info = $this->getAllPublicationInfo();
    $original_publisher = Util::getLastArrayElement($info['publisher'] ?? [], []);
    return $original_publisher['name'] ?? "";
  }

  /**
   * Get the geographic location of the original publisher (e.g. “London, UK”).
   */
  public function getOriginalPublisherPlace(): string {
    $info = $this->getAllPublicationInfo();
    $original_publisher = Util::getLastArrayElement($info['publisher'] ?? [], []);
    return $original_publisher['place'] ?? "";
  }

  /**
   * Get the title of the original version.
   *
   * (e.g. “Война и мир”, the untranslated Russian title of “War and Peace”).
   */
  public function getOriginalTitle(): string {
    // 247 - Former Title (R)
    // $a - Title (NR)
    // $b - Remainder of title (NR)
    return $this->record->query('247[0]$a$b')->text() ?? "";
  }

  /**
   * Get the range of pages the item covers in a container.
   *
   * E.g. range of pages covered by journal article in journal issue.
   */
  public function getPage(): string {
    return $this->getContainerInfo()['pages'] ?? "";
  }

  /**
   * Get the first page of the range of pages the item covers in a container.
   *
   * E.g. first page of journal article in journal issue.
   */
  public function getPageFirst(): string {
    return $this->getContainerInfo()['first_page'] ?? "";
  }

  /**
   * Get the PubMed Central reference number.
   */
  public function getPmcid(): string {
    // TODO:
    return "";
  }

  /**
   * Get the PubMed reference number.
   */
  public function getPmid(): string {
    // TODO:
    return "";
  }

  /**
   * Get the publisher.
   */
  public function getPublisher(): string {
    $info = $this->getAllPublicationInfo();
    return $info['publisher'][0]['name'] ?? "";
  }

  /**
   * Get the geographic location of the publisher.
   */
  public function getPublisherPlace(): string {
    $info = $this->getAllPublicationInfo();
    return $info['publisher'][0]['place'] ?? "";
  }

  /**
   * Get the resources related to the procedural history of a legal case.
   */
  public function getReferences(): string {
    // TODO:
    return "";
  }

  /**
   * Get the title of the item reviewed by the current item.
   */
  public function getReviewedTitle(): string {
    // TODO:
    return "";
  }

  /**
   * Get the scale of e.g. a map.
   */
  public function getScale(): string {
    // TODO:
    return "";
  }

  /**
   * Get the container section holding the item.
   *
   * (e.g. “politics” for a newspaper article).
   */
  public function getSection(): string {
    return $this->getContainerInfo()['section'] ?? "";
  }

  /**
   * Get the source from whence the item originates.
   *
   * (e.g. a library catalog or database).
   */
  public function getSource(): string {
    // TODO:
    return "";
  }

  /**
   * Get the (publication) status of the item (e.g. “forthcoming”).
   */
  public function getStatus(): string {
    return $this->record->query('542[0]$m')->text ?? "";
  }

  /**
   * Get the primary title of the item.
   */
  public function getTitle(): string {
    // 245 - Title Statement (NR)
    // $a - Title (NR)
    // $b - Remainder of title (NR)
    return $this->record->query('245[0]$a$b')->text() ?? "";
  }

  /**
   * Get the short/abbreviated form of “title”.
   *
   * (also accessible through the “short” form of the “title” variable).
   */
  public function getTitleShort(): string {
    // 210 - Abbreviated Title (R)
    // $a - Abbreviated title (NR)
    return $this->record->query('210[0]$a')->text() ?? "";
  }

  /**
   * Get the Uniform Resource Locator.
   *
   * (e.g. “http://aem.asm.org/cgi/content/full/74/9/2766”).
   */
  public function getUrl(): string {
    // TODO: There are probably many other fields that may contain URLs.
    // 856 - Electronic Location and Access (R)
    // $u - Uniform Resource Identifier (R)
    return $this->record->query('856[0]$u')->text() ?? "";
  }

  /**
   * Get the version of the item (e.g. “2.0.9” for a software program).
   */
  public function getVersion(): string {
    // TODO:
    return "";
  }

  /*
   * NUMBER VARIABLES
   * https://docs.citationstyles.org/en/stable/specification.html#number-variables
   */

  /**
   * Get the chapter number.
   */
  public function getChapterNumber(): string {
    // TODO: How is this different from "issue"?
    // TODO: How is this different from "collection-number" (490$v, 830$v)?
    return $this->getContainerInfo()['number'] ?? "";
  }

  /**
   * Get the number identifying the collection holding the item.
   *
   * (e.g. the series number for a book).
   */
  public function getCollectionNumber(): string {
    // The series number is normally in:
    // 490 - Series Statement (R)
    // $v - Volume/sequential designation (R)
    $seriesSeq = $this->record->query('490[0]$v')->text();

    // Sometimes there is more info in:
    // 830 - Series Added Entry-Uniform Title (R)
    // $v - Volume/sequential designation (NR)
    $seriesAddedEntrySeq = $this->record->query('830[0]$v')->text();

    return Util::trimNonWordCharacters($seriesSeq ?: $seriesAddedEntrySeq ?: "");
  }

  /**
   * Get the (container) edition holding the item.
   *
   * (e.g. “3” when citing a chapter in the third edition of a book).
   */
  public function getEdition(): string {
    // 773 - Host Item Entry (R)
    // $b - Edition (NR)
    $container_edition = $this->getContainerInfo()['edition'] ?? "";
    // 250 - Edition Statement (R)
    // $a - Edition statement
    $edition_statement = $this->record->query('250[0]$a')->text();
    // TODO: How will we parse these edition statements? They are normally human
    // readable strings like "2nd ed." or "Canadian edition.".
    return $container_edition ?? $edition_statement ?? "";
  }

  /**
   * Get the (container) issue holding the item.
   *
   * (e.g. “5” when citing a journal article from journal volume 2, issue 5).
   */
  public function getIssue(): string {
    // TODO: How is this different from "number"?
    // TODO: How is this different from "collection-number" (490$v, 830$v)?
    return $this->getContainerInfo()['number'] ?? "";
  }

  /**
   * Get the number identifying the item (e.g. a report number).
   */
  public function getNumber(): string {
    // TODO: How is this different from "issue"?
    // TODO: How is this different from "collection-number" (490$v, 830$v)?
    return $this->getContainerInfo()['number'] ?? "";
  }

  /**
   * Get the total number of pages of the cited item.
   */
  public function getNumberOfPages(): string {
    // TODO:
    return "";
  }

  /**
   * Get the total number of volumes.
   *
   * Usable for citing multi-volume books and such.
   */
  public function getNumberOfVolumes(): string {
    // TODO:
    return "";
  }

  /**
   * Get the (container) volume holding the item.
   *
   * (e.g. “2” when citing a chapter from book volume 2).
   */
  public function getVolume(): string {
    // TODO: How is this different from "collection-number" (490$v, 830$v)?
    return $this->getContainerInfo()['volume'] ?? "";
  }

  /*
   * DATE VARIABLES
   * https://docs.citationstyles.org/en/stable/specification.html#date-variables
   * https://citeproc-js.readthedocs.io/en/latest/csl-json/markup.html#date-fields
   */

  /**
   * Get the date the item has been accessed.
   */
  public function getAccessed(): string {
    // TODO:
    return "";
  }

  /**
   * Get the ?.
   *
   * TODO: This CSL variable has no documentation.
   * Even the CSL spec lists this as "?".
   */
  public function getContainer(): string {
    return "";
  }

  /**
   * Get the date the related event took place.
   */
  public function getEventDate(): string {
    $meetings = $this->getAllMeetings();
    $firstDate = $meetings[0]->dates[0] ?? "";
    return Util::trimNonWordCharacters($firstDate) ?: "";
  }

  /**
   * Get the date the item was issued/published.
   *
   * Interpreting this as the most recent publication date.
   */
  public function getIssued(): string {
    $info = $this->getAllPublicationInfo();
    return $info['publisher'][0]['date'] ?? "";
  }

  /**
   * Get the (issue) date of the original version.
   *
   * Interpreting this as this first publication date.
   */
  public function getOriginalDate(): string {
    $info = $this->getAllPublicationInfo();
    $original_publisher = Util::getLastArrayElement($info['publisher'] ?? [], []);
    return $original_publisher['date'] ?? "";
  }

  /**
   * Get the date the item has been submitted for publication.
   */
  public function getSubmitted(): string {
    // TODO:
    return "";
  }

  /*
   * NAME VARIABLES
   * https://docs.citationstyles.org/en/stable/specification.html#name-variables
   * https://citeproc-js.readthedocs.io/en/latest/csl-json/markup.html#name-fields
   */

  /**
   * Get the authors.
   */
  public function getAuthor(): array {
    $names = $this->getAllNames();
    $legit_authors = array_merge(
      $names[RelatorTerm::AUTHOR] ?? [],
      $names[RelatorTerm::AUTHOR_IN_QUOTATIONS_OR_TEXT_ABSTRACTS] ?? [],
      $names[RelatorTerm::AUTHOR_OF_AFTERWORD_COLOPHON_ETC] ?? [],
      $names[RelatorTerm::AUTHOR_OF_DIALOG] ?? [],
      $names[RelatorTerm::AUTHOR_OF_INTRODUCTION_ETC] ?? []
    );
    // Only use dubious author if there are no other authors specified.
    if (count($legit_authors)) {
      return $legit_authors;
    }
    return $names[RelatorTerm::DUBIOUS_AUTHOR] ?? [];
  }

  /**
   * Get the editors of the collection holding the item.
   *
   * (e.g. the series editor for a book).
   */
  public function getCollectionEditor(): array {
    $names = $this->getAllSeriesNames();
    $editors = array_merge(
      $names[RelatorTerm::EDITOR] ?? [],
      $names[RelatorTerm::EDITOR_OF_COMPILATION] ?? [],
      $names[RelatorTerm::EDITOR_OF_MOVING_IMAGE_WORK] ?? [],
      $names[RelatorTerm::FILM_EDITOR] ?? [],
      $names[RelatorTerm::MARKUP_EDITOR] ?? []
    );
    $authors = array_merge(
      $names[RelatorTerm::AUTHOR] ?? [],
      $names[RelatorTerm::AUTHOR_IN_QUOTATIONS_OR_TEXT_ABSTRACTS] ?? [],
      $names[RelatorTerm::AUTHOR_OF_AFTERWORD_COLOPHON_ETC] ?? [],
      $names[RelatorTerm::AUTHOR_OF_DIALOG] ?? [],
      $names[RelatorTerm::AUTHOR_OF_INTRODUCTION_ETC] ?? []
    );

    // First try editors. If there are none, use authors.
    return $editors ?: $authors;
  }

  /**
   * Get the composers (e.g. of a musical score).
   */
  public function getComposer(): array {
    return $this->getAllNames()[RelatorTerm::COMPOSER] ?? [];
  }

  /**
   * Get the authors of the container holding the item.
   *
   * (e.g. the book author for a book chapter).
   */
  public function getContainerAuthor(): array {
    return $this->getContainerInfo()['authors'] ?? [];
  }

  /**
   * Get the directors (e.g. of a film).
   */
  public function getDirector(): array {
    $names = $this->getAllNames();
    return array_merge(
      $names[RelatorTerm::DIRECTOR] ?? [],
      $names[RelatorTerm::ART_DIRECTOR] ?? [],
      $names[RelatorTerm::ARTISTIC_DIRECTOR] ?? [],
      $names[RelatorTerm::FIELD_DIRECTOR] ?? [],
      $names[RelatorTerm::FILM_DIRECTOR] ?? [],
      $names[RelatorTerm::LABORATORY_DIRECTOR] ?? [],
      $names[RelatorTerm::MUSICAL_DIRECTOR] ?? [],
      $names[RelatorTerm::PROJECT_DIRECTOR] ?? [],
      $names[RelatorTerm::RADIO_DIRECTOR] ?? [],
      $names[RelatorTerm::STAGE_DIRECTOR] ?? [],
      $names[RelatorTerm::TECHNICAL_DIRECTOR] ?? [],
      $names[RelatorTerm::TELEVISION_DIRECTOR] ?? []
    );
  }

  /**
   * Get the editors.
   */
  public function getEditor(): array {
    $names = $this->getAllNames();
    return array_merge(
      $names[RelatorTerm::EDITOR] ?? [],
      $names[RelatorTerm::EDITOR_OF_COMPILATION] ?? [],
      $names[RelatorTerm::EDITOR_OF_MOVING_IMAGE_WORK] ?? [],
      $names[RelatorTerm::FILM_EDITOR] ?? [],
      $names[RelatorTerm::MARKUP_EDITOR] ?? []
    );
  }

  /**
   * Get the managing editors (“Directeur de la Publication” in French).
   */
  public function getEditorialDirector(): array {
    // This is a wild guess, based on the French description.
    return $this->getAllNames()[RelatorTerm::PUBLISHING_DIRECTOR] ?? [];
  }

  /**
   * Get the illustrators (e.g. of a children’s book).
   */
  public function getIllustrator(): array {
    return $this->getAllNames()[RelatorTerm::ILLUSTRATOR] ?? [];
  }

  /**
   * Get the interviewers (e.g. of an interview).
   */
  public function getInterviewer(): array {
    return $this->getAllNames()[RelatorTerm::INTERVIEWER] ?? [];
  }

  /**
   * Get the original authors?
   */
  public function getOriginalAuthor(): array {
    // TODO.
    return [];
  }

  /**
   * Get the recipients (e.g. of a letter).
   */
  public function getRecipient(): array {
    return $this->getAllNames()[RelatorTerm::ADDRESSEE] ?? [];
  }

  /**
   * Get the authors of the item reviewed by the current item.
   */
  public function getReviewedAuthor(): array {
    // TODO.
    return [];
  }

  /**
   * Get the translators.
   */
  public function getTranslator(): array {
    return $this->getAllNames()[RelatorTerm::TRANSLATOR] ?? [];
  }

  /**
   * Get all CSL variables in CiteProc-JSON compatible format.
   *
   * Convert to object to conform to the JsonSerializable interface.
   *
   * @return object
   *   JSON-serializable object.
   */
  public function jsonSerialize() {
    return (object) $this->getAll();
  }

  /**
   * Get all CSL variables in CiteProc-JSON compatible format.
   *
   * @return array
   *   Associative array of all available CSL variables.
   */
  public function getAll(): array {
    return array_filter([
      "abstract" => $this->getAbstract(),
      "annote" => $this->getAnnote(),
      "archive" => $this->getArchive(),
      "archive-location" => $this->getArchiveLocation(),
      "archive-place" => $this->getArchivePlace(),
      "authority" => $this->getAuthority(),
      "call-number" => $this->getCallNumber(),
      "collection-title" => $this->getCollectionTitle(),
      "container-title" => $this->getContainerTitle(),
      "container-title-short" => $this->getContainerTitleShort(),
      "dimensions" => $this->getDimensions(),
      "doi" => $this->getDoi(),
      "event" => $this->getEvent(),
      "event-place" => $this->getEventPlace(),
      "genre" => $this->getGenre(),
      "isbn" => $this->getIsbn(),
      "issn" => $this->getIssn(),
      "jurisdiction" => $this->getJurisdiction(),
      "keyword" => $this->getKeyword(),
      "medium" => $this->getMedium(),
      "note" => $this->getNote(),
      "original-publisher" => $this->getOriginalPublisher(),
      "original-publisher-place" => $this->getOriginalPublisherPlace(),
      "original-title" => $this->getOriginalTitle(),
      "page" => $this->getPage(),
      "page-first" => $this->getPageFirst(),
      "pmcid" => $this->getPmcid(),
      "pmid" => $this->getPmid(),
      "publisher" => $this->getPublisher(),
      "publisher-place" => $this->getPublisherPlace(),
      "references" => $this->getReferences(),
      "reviewed-title" => $this->getReviewedTitle(),
      "scale" => $this->getScale(),
      "section" => $this->getSection(),
      "source" => $this->getSource(),
      "status" => $this->getStatus(),
      "title" => $this->getTitle(),
      "title-short" => $this->getTitleShort(),
      "url" => $this->getUrl(),
      "version" => $this->getVersion(),
      "chapter-number" => $this->getChapterNumber(),
      "collection-number" => $this->getCollectionNumber(),
      "edition" => $this->getEdition(),
      "issue" => $this->getIssue(),
      "number" => $this->getNumber(),
      "number-of-pages" => $this->getNumberOfPages(),
      "number-of-volumes" => $this->getNumberOfVolumes(),
      "volume" => $this->getVolume(),
      "accessed" => $this->getAccessed(),
      "container" => $this->getContainer(),
      "event-date" => $this->getEventDate(),
      "issued" => $this->getIssued(),
      "original-date" => $this->getOriginalDate(),
      "submitted" => $this->getSubmitted(),
      "author" => $this->getAuthor(),
      "collection-editor" => $this->getCollectionEditor(),
      "composer" => $this->getComposer(),
      "container-author" => $this->getContainerAuthor(),
      "director" => $this->getDirector(),
      "editor" => $this->getEditor(),
      "editorial-director" => $this->getEditorialDirector(),
      "illustrator" => $this->getIllustrator(),
      "interviewer" => $this->getInterviewer(),
      "original-author" => $this->getOriginalAuthor(),
      "recipient" => $this->getRecipient(),
      "reviewed-author" => $this->getReviewedAuthor(),
      "translator" => $this->getTranslator(),
    ]);
  }

}
