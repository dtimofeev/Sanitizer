
[![Build Status](https://travis-ci.org/dtimofeev/Sanitizer.svg?branch=master)](https://travis-ci.org/dtimofeev/Sanitizer)

# Overview
Sanitizer is a simple PHP library for data sanitation done via user predefined sanitize schema.

# Usage
```php
use sanitizer\Sanitizer;
use sanitizer\SanitizerSchema as SS;

// Single element
$processed = Sanitezer::process(true, SS::boolean());

// Complex schema
$processed = Sanitizer::process([
    // Input data
], SS::arr()->schema([
    'id'        => SS::int()->min(1),
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


