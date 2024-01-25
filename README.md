
# LaravelGptArchitect

![image](https://github.com/Polar-White/laravel-gpt-architect/assets/1485635/acc05971-8d87-43d0-b121-983766aaaf04)

![Build Status](https://img.shields.io/badge/build-passing-brightgreen)
![License](https://img.shields.io/badge/license-MIT-blue)
![Version](https://img.shields.io/badge/version-1.0.0-blue)
![Downloads](https://img.shields.io/badge/downloads-1-brightgreen)

LaravelGptArchitect is a Laravel package designed to generate GPT-friendly project blueprints...


LaravelGptArchitect is a Laravel package designed to generate GPT-friendly project blueprints. It creates a comprehensive overview of your Laravel application's database schema, models, services, and dependencies, in a format that can be easily consumed by custom GPT models.

## Features

- Generates a detailed description of database tables.
- Includes information about models, relationships, and methods.
- Captures services with their properties and methods.
- Extracts dependencies from `composer.json` and `package.json`.

## Installation

To install the package, run the following command in your Laravel project:

```bash
composer require polar-white/laravel-gpt-architect
```

## Configuration

Publish the configuration file to customize the output:

```bash
php artisan vendor:publish --provider="PolarWhite\LaravelGptArchitect\LaravelGptArchitectServiceProvider"
```

You can then edit the published `config/gpt-architect.php` file according to your needs.

## Usage

To generate the GPT resource file, run:

```bash
php artisan make:gpt-plan
```

The default output will be located at `resources/gpt-plan.txt`.

## Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) for details.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
