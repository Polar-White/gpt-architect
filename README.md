
# LaravelGptArchitect

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
