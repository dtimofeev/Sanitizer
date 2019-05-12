
[![Build Status](https://travis-ci.org/dtimofeev/Sanitizer.svg?branch=master)](https://travis-ci.org/dtimofeev/Sanitizer)
[![codecov](https://codecov.io/gh/dtimofeev/Sanitizer/branch/master/graph/badge.svg)](https://codecov.io/gh/dtimofeev/Sanitizer)

# Overview
Sanitizer is a simple PHP library for data sanitation done via user predefined sanitize schema.

# Usage
```php
use sanitizer\Sanitizer;
use sanitizer\SanitizerSchema as SS;

// Single element
$processed = Sanitezer::process(true, SS::boolean());

// Complex schema
$processed = Sanitizer::process($input, SS::arr()->schema([
    'id'        => SS::integer()->min(1),
    'nickname'  => SS::string()->alphaNum(),
    'email'     => SS::string()->email(),
    'ip'        => SS::string()->ip(),
    'sex'       => SS::string()->optional('na')->oneOf(['male', 'female', 'na']),
    'favMovies' => SS::arr()->each(
        SS::arr()->schema([
            'title'   => SS::string()->trim()->max(200),
            'release' => SS::date()->format('Y-m-d H:i:s'),
            'tags'    => SS::arr()->unique()->each(
                SS::string()->alphaNum()
            ),
        ])
    ),
]));
```

# Aliases

Aliases allow for a single definition of commonly used sanitation rules. They can be defined via `SS::createAlias(string $name, SanitizerSchema  $schema)` and used as normal schema rules `SS::alias($name)` everywhere, no matter the depth. Because of the schemas nature these are also more memory efficient than defining a separate schemas.  

##### Example:
```php
SS::createAlias('alphaNum', SS::string()->alphaNum());

$processed = Sanitizer::process($input, SS::arr()->schema([
    'nickname'  => SS::alias('alphaNum'),
    'favMovies' => SS::arr()->each(
        SS::arr()->schema([
            'tags' => SS::arr()->unique()->each(SS::alias('alphaNum')),
        ])
    ),
]));
```

# Detailed supported methods

### SS::boolean()
| method | params | description |
| ---    | :---   | :---        |
| `optional` | `?bool $default` | Sets a default boolean in case a value is not provided for validation.

### SS::integer()
| method | params | description |
| ---    | :---   | :---        |
| `optional` | `?int $default` | Sets a default integer in case a value is not provided for validation.
| `min`      | `int $value` | Checks if the value is above or equal to the one provided in parameter.
| `max`      | `int $value` | Checks if the value is below or equal to the one provided in parameter.
| `between`  | `int $min`, `int $max` | Checks if the value is between minimum and maximum provided in params.
| `equal`    | `int $expected` | Checks if the value is equal to the one provided in parameter.
| `not`      | `int $unexpected` | Checks if the value is not equal to the one provided in parameter.
| `oneOf`    | `int[] $values` | Checks if the value is one of the array provided in parameter.
| `notOneOf` | `int[] $values` | Checks if the value is not one of the array provided in parameter.

### SS::string()
| method | params | description |
| ---    | :---   | :---        |
| `optional` | `?string $default` | Sets a default string in case a value is not provided for validation.
| `trim`     | `bool $left = true`, `bool $right = true` | Trims spaces from the start(in case $left = true) and from the end(in case $right = true). Note: at least one of both parameters have to be set to true.
| `length`   | `bool $length`, `string $charset = 'UTF-8'` | Checks if the value length is exactly the same as the one provided in $length parameter.
| `min`      | `bool $length`, `string $charset = 'UTF-8'` | Checks if the value length is greater or equal to the one provided in $length parameter.
| `max`      | `bool $length`, `string $charset = 'UTF-8'` | Checks if the value length is smaller or equal to the one provided in $length parameter.
| `oneOf`    | `string[] $values`, `bool $strict = true` | Checks if the value is one of the array provided in parameter.
| `notOneOf` | `string[] $values`, `bool $strict = true` | Checks if the value is not one of the array provided in parameter.
| `email`    | - | Checks if the value is a valid email address.
| `ip`       | `bool $v4 = true`, `bool $v6 = false` | Checks if the value is a valid IP address. Flags in parameters indicate if v4/v6 should be considered valid.
| `url`      | `bool $httpsOnly = false` | Checks if the value is a valid URL. $httpsOnly flags indicates in only https:// URLs should be considered valid.
| `regex`    | `string $pattern`, `string $name = null` | Checks if the value matches a specific regular expression pattern. Parameter $name is used for naming the pattern and will be displayed in error message instead of the whole pattern.
| `alpha`    | `bool $dash = false`, `bool $space = false` | Checks if the value contains only characters from A to Z, lower or uppercase. Parameter $dash indicates if `-` and `_` characters should also be allowed. Parameter `$space` indicates if spaces should also be allowed.
| `alphaNum` | `bool $dash = false`, `bool $space = false` | Checks if the value contains only characters from A to Z, lower or uppercase + numbers. Parameter $dash indicates if `-` and `_` characters should also be allowed. Parameter `$space` indicates if spaces should also be allowed.

### SS::date()
| method | params | description |
| ---    | :---   | :---        |
| `optional` | `?string $default` | Sets a default date in case a value is not provided for validation. Value should be in the same format as instantiated date.
| `before`   | `string $date`     | Checks if validated date is before the one provided in parameter.
| `after`    | `string $date`     | Checks if validated date is after the one provided in parameter.

### SS::arr()
| method | params | description |
| ---    | :---   | :---        |
| `optional` | `?array $default` | Sets a default array in case a value is not provided for validation.
| `scalar`   | - | Checks if all values of the validated array are scalar.
| `unique`   | - | Checks if all values of the validated array are unique.
| `each`     | `SanitizerSchema $schema` | Checks if each of the values of the validated array satisfy the specified `$schema`
| `schema`   | `array $schema` | Validates every key => value based on provided schema.
