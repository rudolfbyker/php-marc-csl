<?php

namespace RudolfByker\PhpMarcCsl;

/**
 * Utilities for parsing MARC data.
 */
class Util {

  /**
   * Trim any non-word characters from the start or end of a string.
   *
   * This is useful for getting data from MARC subfields, which often look
   * something like this:
   * $aCongress on Machinability$d(1965 :$cRoyal Commonwealth Society)
   * E.g. we would like to get "1965" for $d, not "(1965 :".
   *
   * @param string $message
   *   The string to parse.
   *
   * @return string
   *   A new string, with leading and trailing non-word characters removed.
   */
  public static function trimNonWordCharacters(string $message): string {
    $matches = [];
    preg_match('/\w.*\w/', $message, $matches);
    return $matches[0] ?? "";
  }

  /**
   * Parse subfield $g (related parts) from 76X-78X fields.
   *
   * @param string $message
   *   The string to parse.
   *
   * @return array
   *   An array containing the separated info extracted from the subfield.
   *   Possible keys:
   *   - date
   *   - volume
   *   - part
   *   - number
   *   - pages
   *
   * @see https://www.loc.gov/marc/bibliographic/bd76x78x.html
   */
  public static function parseRelatedParts(string $message): array {
    if (preg_match('/^\d+(-\d+)?$/', $message)) {
      // Looks like a year or range of years.
      return ['date' => $message];
    }

    $result = [];
    $matches = [];

    // Find the volume.
    if (preg_match('/(vol|volume)[\s.]*([\w\-]+)/i', $message, $matches)) {
      $result['volume'] = $matches[2];
    }

    // Find the part.
    if (preg_match('/(pt|part)[\s.]*([\w\-]+)/i', $message, $matches)) {
      $result['part'] = $matches[2];
    }

    // Find the number.
    if (preg_match('/(no|number)[\s.]*([\w\-]+)/i', $message, $matches)) {
      $result['number'] = $matches[2];
    }

    // Find the pages.
    if (preg_match('/(p|pp|page|pages)[\s.]*([\d\-ivxlcdm, ]+)/i', $message, $matches)) {
      $result['pages'] = $matches[2];
    }

    // Find the date.
    if (preg_match('/\(([\w. ]+)\)/i', $message, $matches)) {
      $result['date'] = $matches[1];
    }

    return $result;
  }

  /**
   * Parse subfield $q (enumeration and first page) from field 773.
   *
   * @param string $message
   *   The string to parse.
   *
   * @return array
   *   An array containing the separated info extracted from the subfield.
   *   Possible keys:
   *   - volume
   *   - part
   *   - number
   *   - page
   *
   * @see https://www.loc.gov/marc/bibliographic/bd76x78x.html
   * @see https://www.loc.gov/marc/bibliographic/bd773.html
   * @see https://en.wikipedia.org/wiki/Serial_Item_and_Contribution_Identifier
   */
  public static function parseEnumerationAndFirstPage(string $message): array {
    $parts = explode("<", $message);
    if (count($parts) === 0) {
      return [];
    }

    $results = [];
    if (count($parts) > 1) {
      $results['page'] = $parts[1];
    }

    $parts = explode(":", $parts[0]);
    switch (count($parts)) {
      case 3:
        $results['volume'] = $parts[0];
        $results['part'] = $parts[1];
        $results['number'] = $parts[2];
        break;

      case 2:
        $results['volume'] = $parts[0];
        $results['number'] = $parts[1];
        break;

      case 1:
        if (strlen($parts[0])) {
          $results['volume'] = $parts[0];
        }
        break;
    }

    return $results;
  }

  /**
   * Get the last array element. If not available, return the fallback value.
   *
   * @param array $arr
   *   The array from which to get the last element.
   * @param mixed $fallback
   *   The value to return if the array has no last element.
   *
   * @return mixed
   */
  public static function getLastArrayElement(array $arr, $fallback = NULL) {
    $n = count($arr);
    if ($n < 1) {
      return $fallback;
    }
    // No need to call reset, as $arr was passed by value.
    return end($arr);
  }

}
