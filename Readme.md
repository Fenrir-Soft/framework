# Fenrir Framework

## Requirements

- php 8.2+

## Installation

Create a composer.json file
```json
{
    "scripts": {
        "dev": "serve --host=0.0.0.0 --port=0"
    },
    "require": {
        "fenrir-soft/framework": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "": "src/"
        }
    }
}
```
then run the command:
```bash
$ composer install
```

## Running the dev server

To run the dev server, run the command:
```
$ composer run dev
```