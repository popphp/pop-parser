pop-parser
==========

[![Build Status](https://github.com/popphp/pop-parser/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-parser/actions)
[![Coverage Status](https://cc.popphp.org/coverage.php?comp=pop-parser)](https://cc.popphp.org/pop-parser/)

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
construct with or without data, call `parse()`, which returns an immutable result object
(`AddressResult`/`NameResult`) - read the parsed fields off *that* via `get*()`/`has*()` methods,
`toArray()`, or by casting it to a string. The parser itself holds no parsed fields.

Both results also carry a `getConfidence()` score (0.0-1.0, `isConfident()` for a quick threshold
check) reflecting how much of the input was confidently matched vs. guessed, and both parsers
title-case an all-uppercase or all-lowercase field ("MAIN ST" -> "Main St") while leaving any
existing mixed case exactly as typed.

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
$result = $parser->parse('123 Main St Apt 4B, Springfield, IL 62704');

$result->getStreetNumber(); // '123'
$result->getStreetName();   // 'Main'
$result->getRouteType();    // 'St'
$result->getUnit();         // 'Apt 4B'
$result->getCity();         // 'Springfield'
$result->getStateCode();    // 'IL'
$result->getStateName();    // 'Illinois'
$result->getPostalCode();   // '62704'
$result->getCountry();      // 'US'
```

The address string can also be passed to the constructor, or set separately with `setData()`
before calling `parse()` with no argument:

```php
$parser = new AddressParser('123 Main St, Springfield, IL 62704');
$result = $parser->parse();
```

### Directions and route types

A leading or trailing directional (`N`, `S`, `E`, `W`, `NE`, `SW`, ...) is recognized and kept
separate from the street name. `getStreetName()` includes it by default; pass `false` to get the
bare street name without it:

```php
$parser = new AddressParser();
$result = $parser->parse('456 N Elm Street, Chicago, IL 60601');

$result->getDirection();          // 'N'
$result->getStreetName();         // 'N Elm'
$result->getStreetName(false);    // 'Elm'
$result->getRouteType();          // 'Street'
```

### PO Boxes

PO Box addresses ("PO Box 1234", "P.O. Box 1234", "POB 1234", "Box 1234") are recognized
directly - the box number is returned via `getStreetName()`, `getStreetNumber()` stays `null`, and
`isPoBox()` reports the match:

```php
$parser = new AddressParser();
$result = $parser->parse('PO Box 1234, Springfield, IL 62704');

$result->isPoBox();       // true
$result->getStreetName(); // 'PO Box 1234'
$result->getCity();       // 'Springfield'
```

### Full address, array output, and string casting

```php
$result->getFullAddress();
// '123 Main St, Apt 4B, Springfield, IL 62704'

// Options: delimiter, whether to use the state code vs. full state name, whether to include the country
$result->getFullAddress(', ', false, true);
// '123 Main St, Apt 4B, Springfield, Illinois 62704, US'

$result->toArray();
// [
//     'streetNumber' => '123', 'streetName' => 'Main', 'routeType' => 'St',
//     'direction' => null, 'unit' => 'Apt 4B', 'city' => 'Springfield',
//     'postalCode' => '62704', 'zip4' => null, 'stateName' => 'Illinois',
//     'stateCode' => 'IL', 'country' => 'US', 'confidence' => 1.0,
// ]

(string) $result; // same as getFullAddress()
```

Every component getter on `AddressResult` (`getStreetNumber()`, `getStreetName()`, `getRouteType()`,
`getDirection()`, `getUnit()`, `getCity()`, `getPostalCode()`, `getZip4()`, `getStateName()`,
`getStateCode()`, `getCountry()`) has a matching `has*()` method (e.g. `hasUnit()`, `hasZip4()`) that
returns `true` only when that part was actually found.

### Confidence

```php
$result->getConfidence();     // 1.0
$result->isConfident();       // true (default threshold: 0.7)
$result->isConfident(0.9);    // pass a stricter threshold if you need one
```

Confidence starts at `1.0` and drops for specific, individually-identifiable situations where the
parser had to guess rather than resolve something from solid evidence - e.g. no postal code was
found at all, or a comma-less address needed a heuristic to split the street from the city. An
address that genuinely has no city (`"123 Main St, IL 62704"`) still parses with full confidence -
a confident, correct `null` isn't the same as a failed guess.

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
$result = $parser->parse('Dr. John Michael Smith Jr.');

$result->getSalutation(); // 'Dr.'
$result->getFirstname();  // 'John'
$result->getMiddlename(); // 'Michael'
$result->getLastname();   // 'Smith'
$result->getSuffix();     // 'Jr'
$result->getFullName();   // 'John Michael Smith'
(string) $result;         // 'Dr. John Michael Smith Jr'
```

As with `AddressParser`, the name string can be passed to the constructor instead of `parse()`:

```php
$parser = new NameParser('John Smith');
$result = $parser->parse();
```

### Comma-separated format

A comma anywhere in the input switches to "Last, First Middle[, Suffix]" parsing automatically:

```php
$parser = new NameParser();
$result = $parser->parse('Smith, John Michael, Jr');

$result->getFirstname();  // 'John'
$result->getMiddlename(); // 'Michael'
$result->getLastname();   // 'Smith'
$result->getSuffix();     // 'Jr'
```

### Lastname prefixes

Recognized lastname-prefix words ("van", "von", "de", "della", "st", ...) are split out into
their own field rather than left attached to the lastname:

```php
$parser = new NameParser();
$result = $parser->parse('Ludwig van Beethoven');

$result->getLastnamePrefix(); // 'van'
$result->getLastname();       // 'Beethoven'
$result->getFullName();       // 'Ludwig van Beethoven'
```

### Initials and nicknames

A single letter (with or without a trailing period) is treated as an initial rather than a first
or middle name, and a parenthetical or quoted segment anywhere in the name is pulled out as a
nickname:

```php
$parser = new NameParser();
$result = $parser->parse('J.R. "Bob" Smith');

$result->getFirstname();    // 'J'
$result->getInitials();     // 'R'
$result->getNickname();     // 'Bob'
$result->getNickname(true); // '(Bob)'
$result->getLastname();     // 'Smith'
```

### Credentials

Professional credentials/degrees (PhD, MD, Esq, JD, MBA, RN, DDS, DVM, CPA, CFA, PE, RPh, DNP,
PsyD, EdD, DO) are recognized separately from generational suffixes (Jr, Sr, III, ...), even when
both trail the same name:

```php
$parser = new NameParser();
$result = $parser->parse('Anthony Von Fange III, PhD');

$result->getSuffix();      // 'III'
$result->getCredentials(); // 'PhD'
```

### Full name, given name, array output, and string casting

```php
$result->getGivenName(); // firstname + initials + middlename, whichever are present
$result->getFullName();  // given name + lastname prefix + lastname

$result->toArray();
// [
//     'salutation' => null, 'firstname' => 'John', 'initials' => null,
//     'middlename' => 'Michael', 'nickname' => null, 'lastnamePrefix' => null,
//     'lastname' => 'Smith', 'suffix' => 'Jr', 'credentials' => null, 'confidence' => 1.0,
// ]

(string) $result; // salutation + given name + nickname + lastname prefix + lastname + suffix + credentials
```

Every component getter on `NameResult` (`getSalutation()`, `getFirstname()`, `getMiddlename()`,
`getNickname()`, `getInitials()`, `getLastnamePrefix()`, `getLastname()`, `getSuffix()`,
`getCredentials()`) has a matching `has*()` method (e.g. `hasSuffix()`, `hasNickname()`) that
returns `true` only when that part was actually found.

### Confidence

```php
$result->getConfidence();  // 1.0
$result->isConfident();    // true (default threshold: 0.7)
```

Confidence starts at `1.0` and drops for specific situations where the parser had to guess rather
than resolve something directly - e.g. a queued initial had to be promoted to `firstname` because
the name never actually supplied one (`"J. B. Hunt"`), or comma-mode leftover content didn't fit
any recognized category and had to be defaulted into `middlename`.

### Reference data

`Pop\Parser\Name\NameValues` exposes the underlying lookup data the parser uses - salutations
(`getSalutations()`), generational suffixes (`getSuffixes()`), professional credentials
(`getCredentials()`), lastname prefixes (`getLastnamePrefixes()`), and recognized
nickname-wrapping delimiter pairs (`getNicknameDelimiters()`).

### Errors

Calling `parse()` with no data set (neither passed to the constructor, `setData()`, nor `parse()`
itself), or with data that normalizes to an empty string, throws a `Pop\Parser\Exception`.

[Top](#pop-parser)


