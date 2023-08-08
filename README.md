# Add markdown routing to Folio

[![Latest Version on Packagist](https://img.shields.io/packagist/v/snellingio/folio-markdown.svg?style=flat-square)](https://packagist.org/packages/snellingio/folio-markdown)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/snellingio/folio-markdown/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/snellingio/folio-markdown/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/snellingio/folio-markdown/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/snellingio/folio-markdown/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/snellingio/folio-markdown.svg?style=flat-square)](https://packagist.org/packages/snellingio/folio-markdown)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

<a name="introduction"></a>

## Introduction

Folio Markdown is a powerful extension to the Laravel Folio page based router.

With Laravel Folio, generating a route becomes as effortless as creating a Blade template within your
application's `resources/views/pages` directory.
With Folio Markdown, you can create a route by simply creating a Markdown file within the same directory.

For example, to create a page that is accessible at the `/greeting` URL, just create a `greeting.md` file in your
application's `resources/views/pages` directory:

```md
---
view: layouts.app
title: Greetings From Space!
---

# Greetings earthlings!

...
```

All the YAML front matter will be passed to the view template as variables, and the Markdown content will be passed to
the view template as a `$slot` variable.

## Installation

You will first need to install Laravel Folio into your Laravel application using the Composer package manager:

```bash
composer require laravel/folio:^1.0@beta
```

After installing Folio, you may execute the `folio:install` Artisan command, which will install Folio's service provider
into your application. This service provider registers the directory where Folio will search for routes / pages:

```bash
php artisan folio:install
```

After installing Folio, you may install the Folio Markdown package using Composer:

```bash
composer require snellingio/folio-markdown
```

Finally, within your `App\Providers\FolioServiceProvider` file,
call the `register` method using the FolioMarkdown Facade:

```php
use Snelling\FolioMarkdown\Facades\FolioMarkdown;

class FolioServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Place your Folio calls before the register method
        
        // Register Folio Markdown at the bottom of the boot method
        FolioMarkdown::register();
    }
}
```

<a name="page-paths-uris"></a>

### Page Paths / URIs

Folio Markdown uses the same paths and URIs as Folio, and applies the same rules as Folio does.

// @TODO: write more docs explaining middleware, paths, domains, etc all apply the same.

<a name="nested-routes"></a>

### Nested Routes

```bash
# pages/user/profile.md → /user/profile
```

<a name="index-routes"></a>

### Index Routes

```bash
# pages/users/index.md → /users
```

<a name="route-parameters"></a>

## Route Parameters

```bash 
# pages/users/[id].md → /user/1
```

Captured segments cannot be accessed within the markdown portion, but will be passed to the view template:

```md
---
view: layouts.app
---

# User {{ $id }} <-- This does not work
```

```html
<!-- layouts.app -->
<h1>User {{ $id }}</h1> <-- This works
{{ $slot }}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Sam Snelling](https://github.com/snellingio)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
