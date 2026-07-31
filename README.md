pop-parser
==========

[![Build Status](https://github.com/popphp/pop-parser/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-parser/actions)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Address Parsing](#address-parsing)
* [Name Parsing](#name-parsing)

Overview
--------
Pop Parser is a simple set of parsers for names and addresses. It ships two independent, native
parsers - no third-party parsing dependencies required:

- `Pop\Parser\Address\AddressParser` - parses free-form US/CA street addresses into their
  component parts (street number, street name, route type, direction, unit, city, state,
  postal code, country, PO Box detection).
- `Pop\Parser\Name\NameParser` - parses personal names into their component parts (salutation,
  first/middle/last name, lastname prefix, initials, nickname, suffix), including comma-separated
  "Last, First" format.

Both parsers extend the same `Pop\Parser\AbstractParser` base class and share a consistent shape:
construct with or without data, call `parse()`, then read the results off via `get*()`/`has*()`
methods, `toArray()`, or by casting to a string.

[Top](#pop-parser)

Install
-------

Install `pop-parser` using Composer.

    composer require popphp/pop-parser

Or, require it in your composer.json file

    "require": {
        "popphp/pop-parser" : "^1.0.0"
    }

[Top](#pop-parser)

Address Parsing
----------------

`Pop\Parser\Address\AddressParser` parses a free-form US or Canadian street address string into
its component parts. Addresses can be passed as a single comma/semicolon/newline-delimited
string, or built up over multiple lines - both a "123 Main St, Springfield, IL 62704" one-liner
and a multi-line mailing-label style address parse the same way.

### Basic usage

```php
use Pop\Parser\Address\AddressParser;

$parser = new AddressParser();
$parser->parse('123 Main St Apt 4B, Springfield, IL 62704');

$parser->getStreetNumber(); // '123'
$parser->getStreetName();   // 'Main'
$parser->getRouteType();    // 'St'
$parser->getUnit();         // 'Apt 4B'
$parser->getCity();         // 'Springfield'
$parser->getStateCode();    // 'IL'
$parser->getStateName();    // 'Illinois'
$parser->getPostalCode();   // '62704'
$parser->getCountry();      // 'US'
```

The address string can also be passed to the constructor, or set separately with `setData()`
before calling `parse()` with no argument:

```php
$parser = new AddressParser('123 Main St, Springfield, IL 62704');
$parser->parse();
```

### Directions and route types

A leading or trailing directional (`N`, `S`, `E`, `W`, `NE`, `SW`, ...) is recognized and kept
separate from the street name. `getStreetName()` includes it by default; pass `false` to get the
bare street name without it:

```php
$parser = new AddressParser();
$parser->parse('456 N Elm Street, Chicago, IL 60601');

$parser->getDirection();          // 'N'
$parser->getStreetName();         // 'N Elm'
$parser->getStreetName(false);    // 'Elm'
$parser->getRouteType();          // 'Street'
```

### PO Boxes

PO Box addresses ("PO Box 1234", "P.O. Box 1234", "POB 1234", "Box 1234") are recognized
directly - the box number is returned via `getStreetName()`, `getStreetNumber()` stays `null`, and
`isPoBox()` reports the match:

```php
$parser = new AddressParser();
$parser->parse('PO Box 1234, Springfield, IL 62704');

$parser->isPoBox();       // true
$parser->getStreetName(); // 'PO Box 1234'
$parser->getCity();       // 'Springfield'
```

### Full address, array output, and string casting

```php
$parser->getFullAddress();
// '123 Main St, Apt 4B, Springfield, IL 62704'

// Options: delimiter, whether to use the state code vs. full state name, whether to include the country
$parser->getFullAddress(', ', false, true);
// '123 Main St, Apt 4B, Springfield, Illinois 62704, US'

$parser->toArray();
// [
//     'streetNumber' => '123', 'streetName' => 'Main', 'routeType' => 'St',
//     'direction' => null, 'unit' => 'Apt 4B', 'city' => 'Springfield',
//     'postalCode' => '62704', 'zip4' => null, 'stateName' => 'Illinois',
//     'stateCode' => 'IL', 'country' => 'US',
// ]

(string) $parser; // same as getFullAddress()
```

Every component getter (`getStreetNumber()`, `getStreetName()`, `getRouteType()`, `getDirection()`,
`getUnit()`, `getCity()`, `getPostalCode()`, `getZip4()`, `getStateName()`, `getStateCode()`,
`getCountry()`) has a matching `has*()` method (e.g. `hasUnit()`, `hasZip4()`) that returns `true`
only when that part was actually found.

### Reference data

`Pop\Parser\Address\AddressValues` exposes the underlying lookup/validation data the parser uses
- route type abbreviations (`getRouteTypes()`, `getCommonRouteTypes()`), directions
  (`getDirections()`), unit types (`getUnitTypes()`), and US/Canadian states and provinces
  (`getStates()`, `getStateCodes()`, `getStateNames()`) - useful if you want to validate or build
  your own address-related form fields against the same data the parser relies on.

### Errors

Calling `parse()` with no data set (neither passed to the constructor, `setData()`, nor `parse()`
itself) throws a `Pop\Parser\Exception`.

[Top](#pop-parser)

Name Parsing
-------------

`Pop\Parser\Name\NameParser` parses a free-form personal name string into its component parts. It
supports plain space-separated names ("John Smith") as well as comma-separated "Last, First
Middle[, Suffix]" format, and recognizes salutations, suffixes, initials, lastname prefixes
("van", "de", "von", ...), and parenthetical/quoted nicknames along the way.

### Basic usage

```php
use Pop\Parser\Name\NameParser;

$parser = new NameParser();
$parser->parse('Dr. John Michael Smith Jr.');

$parser->getSalutation(); // 'Dr.'
$parser->getFirstname();  // 'John'
$parser->getMiddlename(); // 'Michael'
$parser->getLastname();   // 'Smith'
$parser->getSuffix();     // 'Jr'
$parser->getFullName();   // 'John Michael Smith'
(string) $parser;         // 'Dr. John Michael Smith Jr'
```

As with `AddressParser`, the name string can be passed to the constructor instead of `parse()`:

```php
$parser = new NameParser('John Smith');
$parser->parse();
```

### Comma-separated format

A comma anywhere in the input switches to "Last, First Middle[, Suffix]" parsing automatically:

```php
$parser = new NameParser();
$parser->parse('Smith, John Michael, Jr');

$parser->getFirstname();  // 'John'
$parser->getMiddlename(); // 'Michael'
$parser->getLastname();   // 'Smith'
$parser->getSuffix();     // 'Jr'
```

### Lastname prefixes

Recognized lastname-prefix words ("van", "von", "de", "della", "st", ...) are split out into
their own field rather than left attached to the lastname:

```php
$parser = new NameParser();
$parser->parse('Ludwig van Beethoven');

$parser->getLastnamePrefix(); // 'van'
$parser->getLastname();       // 'Beethoven'
$parser->getFullName();       // 'Ludwig van Beethoven'
```

### Initials and nicknames

A single letter (with or without a trailing period) is treated as an initial rather than a first
or middle name, and a parenthetical or quoted segment anywhere in the name is pulled out as a
nickname:

```php
$parser = new NameParser();
$parser->parse('J.R. "Bob" Smith');

$parser->getFirstname();    // 'J'
$parser->getInitials();     // 'R'
$parser->getNickname();     // 'Bob'
$parser->getNickname(true); // '(Bob)'
$parser->getLastname();     // 'Smith'
```

### Full name, given name, array output, and string casting

```php
$parser->getGivenName(); // firstname + initials + middlename, whichever are present
$parser->getFullName();  // given name + lastname prefix + lastname

$parser->toArray();
// [
//     'salutation' => null, 'firstname' => 'John', 'initials' => null,
//     'middlename' => 'Michael', 'nickname' => null, 'lastnamePrefix' => null,
//     'lastname' => 'Smith', 'suffix' => 'Jr',
// ]

(string) $parser; // salutation + given name + nickname + lastname prefix + lastname + suffix
```

Every component getter (`getSalutation()`, `getFirstname()`, `getMiddlename()`, `getNickname()`,
`getInitials()`, `getLastnamePrefix()`, `getLastname()`, `getSuffix()`) has a matching `has*()`
method (e.g. `hasSuffix()`, `hasNickname()`) that returns `true` only when that part was actually
found.

### Reference data

`Pop\Parser\Name\NameValues` exposes the underlying lookup data the parser uses - salutations
(`getSalutations()`), suffixes (`getSuffixes()`), lastname prefixes (`getLastnamePrefixes()`), and
recognized nickname-wrapping delimiter pairs (`getNicknameDelimiters()`).

### Errors

Calling `parse()` with no data set (neither passed to the constructor, `setData()`, nor `parse()`
itself), or with data that normalizes to an empty string, throws a `Pop\Parser\Exception`.

[Top](#pop-parser)
