# EU Vat

[![PHP Version](https://img.shields.io/packagist/php-v/eventix/eu-vat)](https://php.net/)
[Laravel Badge TODO](https://laravel.com/docs/5.7)
[![License](https://img.shields.io/github/license/eventix/eu-vat)](LICENSE)

This Laravel package enables the formatting and validation of (EU) VAT numbers.

For validation it uses the endpoints of the European Commission's VAT Information Exchange Service (VIES)

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Testing](#Testing)
- [Contributing](#contributing)

## Installation

Via Composer

``` bash
$ composer require eventix/eu-vat
```


## Usage

When the package is included in a Laravel project, composer autoload functionality has automatically discovered a Service Provider.
This will extend the Laravel Validator with a validation rule (vat_number).

Also an alias for a Facade is registered. This facade (EuVat) will enable manual formatting and validation for vat numbers.

### Supported countries
``` php
EuVat::codes(): array; // List of supported country codes 
EuVat::supports(string $countryCode): bool; // Determines if a given country code is supported
EuVat::name(string $countryCode): ?string; // Returns the (english) name associated with a country code if it is supported)
EuVat::inferCountry(string $vatNumber): ?string; // (Try to) guess the country of a vat number.
```

### Vat Numbers
``` php
EuVat::format(string $vatNumber, ?string $countryCode = null): ?string // (Try to) format a vat number by the formatting rules of a given country, or a guessed country
EuVat::validate(string $vatNumber, ?string $countryCode = null): ?string // (Try to) validate a vat number by the formatting rules of a given country, or a guessed country
```

### Validator

> Validates the vat number by inferring its country
``` php
$data = [
    'vat_nr' => 'NL123456789B01',
];

$validator = Validator::make($data, [
    'vat_nr' => 'required|vat_number',
]);
```

> Validates the vat number for a given country
``` php
$data = [
    'vat_nr' => 'NL123456789B01',
];

$validator = Validator::make($data, [
    'vat_nr' => 'required|vat_number:NL',
]);
```

> Validates the vat number for a country determined by another field
``` php
$data = [
    'country' => 'NL',
    'vat_nr' => 'NL123456789B01',
];

$validator = Validator::make($data, [
    'vat_nr' => 'required|vat_number:country',
]);
```

**Validation ONLY when the vat number changes**

*If the value does not change... it should already be valid. This will reduce the calls to VIES. Note: The validation rule needs the original value for this to work.*

> Validates a changed vat number for an inferred country
``` php
$data = [
    'vat_nr' => 'NL123456789B01',
];

$validator = Validator::make($data, [
    'vat_nr' => 'required|vat_number:NULL,NL123456789B01',
]);
```

> Validates a changed vat number for a country determined by another field
``` php
$data = [
    'country' => 'NL',
    'vat_nr' => 'NL123456789B01',
];

$validator = Validator::make($data, [
    'vat_nr' => 'required|vat_number:country,NL123456789B01',
]);
```

> Validates a changed vat number for a given country
``` php
$data = [
    'vat_nr' => 'NL123456789B01',
];

$validator = Validator::make($data, [
    'vat_nr' => 'required|vat_number:NL,NL123456789B01',
]);
```

## Testing

Note, for testing the project needs to be cloned and all dependencies installed first.

``` bash
$ cd /packages/directory
$ git clone TODO
$ composer install
```

``` bash
$ composer test
```

## License

Please see [License File](LICENSE).

No liability, implementor is responsible for reviewing code!

## Issues

Please [open an issue](https://github.com/eventix/eu-vat/issues/new).

## Contributing

Please contribute using [Github Flow](https://guides.github.com/introduction/flow/). 
Fork the project, create a branch, add commits, and [open a pull request](https://github.com/eventix/eu-vat/compare/).

## Opportunities

- Test coverage is not a 100% yet.

**Potentials**
- Opportunities for localized country names
- Opportunities for 3 char country codes
- Opportunities for country/ies outside the EU
