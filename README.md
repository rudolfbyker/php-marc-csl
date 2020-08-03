# Convert MARC records to CSL variables in CSL-JSON format using PHP

This package tries to map MARC21 records to CSL variables.
There is no official or industry standard mapping.
We develop our mapping on our interpretation of the [MARC21 docs](https://www.loc.gov/marc/bibliographic/) and
[CSL docs](https://docs.citationstyles.org/en/stable/specification.html).

## Example usage:

```php
// Get a MARC record (e.g. from an XML file)
$record = Record::fromSimpleXMLElement(simplexml_load_file("marc/xml/35663.xml"));

// Wrap the record in the class provided by this package.
$marcCsl = new MarcCslVariables($record);

// Get the CSL variables as a PHP data structure.
$csl_variables = $csl_variables->jsonSerialize();

// Get the CSL variables as a CSL-JSON string.
$json_string = json_encode($csl_variables);
```

From here, you can use the CSL JSON string as input for something like CiteProc-JS or CiteProc-PHP 
in order to generate bibliographies and citations.

## Installing

```shell script
composer require rudolfbyker/php-marc-csl
```

## Running unit tests

```shell script
./vendor/bin/phpunit
```

## Reporting bugs

If your data is mapped incorrectly, create an issue or PR, and provide:

- the input MARC data
- the actual output
- the expected output
- links to relevant CSL and MARC documentation
- suggestions for how to fix it
