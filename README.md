# Add markdown routing to Folio

[![Latest Version on Packagist](https://img.shields.io/packagist/v/snellingio/folio-markdown.svg?style=flat-square)](https://packagist.org/packages/snellingio/folio-markdown)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/snellingio/folio-markdown/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/snellingio/folio-markdown/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/snellingio/folio-markdown/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/snellingio/folio-markdown/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/snellingio/folio-markdown.svg?style=flat-square)](https://packagist.org/packages/snellingio/folio-markdown)

# Folio Markdown for Laravel Folio

Folio Markdown is an extension for the Laravel Folio page-based router that enables the creation of routes using `.md`
and `.blade.md` files. This simplifies the process of generating routes in Laravel applications.

## Overview

Laravel Folio allows you to generate routes by creating Blade templates in your application's `resources/views/pages`
directory. Folio Markdown enhances this functionality by enabling you to create routes using Markdown files in the same
directory.

For example, to create a page accessible at the `/greeting` URL, you can create a `greeting.md` file in
the `resources/views/pages` directory:

```md
---
view: layouts.app
title: Greetings From Space!
---

# Greetings earthlings!

...
```

The YAML front matter is converted into variables for the view template, and the Markdown content is passed to the view
template as a `$slot` variable.

## Installation Steps

Follow these steps to install and setup Folio Markdown:

1. **Install Laravel Folio:** Use Composer to install Laravel Folio into your Laravel application:

```bash
composer require laravel/folio:^1.0@beta
```

2. **Execute `folio:install`:** Run the `folio:install` Artisan command. This installs Folio's service provider and
   registers the directory where Folio will look for routes or pages:

```bash
php artisan folio:install
```

3. **Install the Folio Markdown package:** Install the Folio Markdown package using Composer:

(Note: The package needs to be published to Composer first.)

4. **Register Folio Markdown:** In your `App\Providers\FolioServiceProvider` file, call the `register` method using the
   FolioMarkdown Facade:

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
        Folio::path(resource_path('views/pages'))->middleware([
            '*' => [
                //
            ],
        ]);
        
        // Register Folio Markdown at the bottom of the boot method
        FolioMarkdown::register();
    }
}
```

With these steps completed, you can now create routes using Markdown files in your Laravel application.

## Quick Highlights

Folio Markdown supports a variety of features, including:

- **File Extensions**: Files with a `.md` extension are processed as Markdown files. Files with a `.blade.md` extension
  are processed as a Blade template and then as Markdown, enabling the use of specific Blade directives within your
  Markdown files. However, some Blade directives, like `@push` and `@section`, that span across components may not work
  because each file is rendered separately.

- **View Templates**: To specify a view template, use the `view` key in the front matter of the page. The content of the
  page is passed as the `$slot` variable to the view template.

- **Handling Soft Deleted Models**: By default, soft deleted models are not retrieved when resolving implicit model
  bindings. If you want Folio Markdown to also retrieve these models, add the `withTrashed` key to the page's front
  matter.

- **Middleware**: To apply middleware to a specific page, add the `middleware` key to the page's front matter.
  Alternatively, to apply middleware to a group of pages, chain the `middleware` method after calling the `Folio::path`
  method.

- **Additional Front Matter**: You can add any additional front matter to your page. For instance, you might add
  a `title` key to the page's front matter. This will be passed as the `$title` variable to the view template. This is
  applicable to all front matter keys.

- **Nested Routes & Index Routes**: You can create a nested route by creating one or more directories within one of
  Folio's directories. Similarly, by placing an `index.md` template within a Folio directory, any requests to the root
  of that directory will be routed to that page.

- **Route Parameters**: You can have segments of the incoming request's URL injected into your page so that you can
  interact with them. For example, you may need to access the "ID" of the user whose profile is being displayed.

- **Route Model Binding**: If a wildcard segment of your page template's filename corresponds one of your application's
  Eloquent models, Folio will automatically take advantage of Laravel's route model binding capabilities and attempt to
  inject the resolved model instance into your page.

- **PHP Blocks**: If you want to write PHP code within your file, you must end it in a `.blade.md`. After which, you can
  use the `@php` Blade directive.

Please refer to the detailed guide below for a comprehensive understanding of each feature.

### Understanding File Extensions

Files that end with `.md` are automatically recognized and processed as Markdown files. If a file ends with `.blade.md`,
it will first be processed as a Blade template, then as a Markdown file. This allows you to use specific Blade
directives within your Markdown files. However, keep in mind that some Blade directives, like `@push` and `@section`,
that are used across components might not work because each file is processed individually.

### Markdown Extensions

Under the hood, Folio Markdown uses the [Spatie Laravel Markdown](https://spatie.be/docs/laravel-markdown/v1/introduction) package.

While Folio Markdown respects the config of the Spatie Laravel Markdown package, currently code highlighting via Shiki is disabled (@TODO: need to create an issue!).

### How to Use View Templates

To use a view template, you need to include the `view` key in the front matter of the page, like this:

```md
---
view: app.layouts
---

# Hello World!
```

The content of the page will then be passed to the view template as the `$slot` variable.

### Working with Soft Deleted Models

By default, soft deleted models are not included when resolving implicit model bindings. If you want Folio Markdown to
also include these models, add the `withTrashed` key to the page's front matter:

```md
---
withTrashed: true
---

# Hello Trashed!
```

### Applying Middleware

If you want to use middleware for a specific page, include the `middleware` key in the page's front matter:

```md
---
middleware: auth
---

# Hello Middleware!
```

If you want to use middleware for a group of pages, you can do so by chaining the `middleware` method after calling
the `Folio::path` method.

### Adding More Front Matter

You can add any additional front matter to your page. For example, you might want to add a `title` key to the page's
front matter:

```md
---
view: app.layouts
title: Hello World!
---
```

This will be passed as the `$title` variable to the view template. This applies to all front matter keys.

## The Same Folio You Know and Love

### Creating Subpages

To create a subpage in Folio, you need to make a new directory within an existing one. For example, if you want to
create a page that can be accessed via `/user/profile`, you would create a `profile.md` template within the `pages/user`
directory like this:

```bash
# pages/user/profile.md → /user/profile
```

### Setting a Default Page

Sometimes, you might want to set a specific page as the default for a directory. You can do this by placing
an `index.md` or `index.blade.md` template within a Folio directory. Any requests to the root of that directory will
then be directed to that page:

```bash
# pages/index.md → /
# pages/users/index.md → /users
```

### Using URL Segments in Your Page

There might be times when you need to use parts of the incoming request's URL in your page. For example, you might need
to access the "ID" of a user whose profile is being displayed. To do this, you can include a segment of the page's
filename in square brackets:

```bash
# pages/users/[id].blade.md → /users/1
```

You can then use these captured segments as variables within your `.blade.md` template, or within the parent `view`
component:

```html

<div>
    User {{ $id }}
</div>
```

To capture multiple segments, you can prefix the encapsulated segment with three dots `...`:

```bash
# pages/users/[...ids].blade.md → /users/1/2/3
```

When capturing multiple segments, the captured segments will be injected into the page as an array:

```html

<ul>
    @foreach ($ids as $id)
    <li>User {{ $id }}</li>
    @endforeach
</ul>
```

### Linking URL Segments to Models

If a wildcard segment of your page template's filename matches one of your application's Eloquent models, Folio will
automatically link it to Laravel's route model binding capabilities and try to inject the resolved model instance into
your page:

```bash
# pages/users/[User].md → /users/1
# pages/users/[User].blade.md → /users/1
```

The captured models can then be accessed as variables within your `.blade.md` template or the parent `view` component.
The model's variable name will be converted to "camel case":

```html

<div>
    User {{ $user->id }}
</div>
```

#### Customizing the Model Key

If you want to link bound Eloquent models using a column other than `id`, you can specify the column in the page's
filename. For example, a page with the filename `[Post:slug].blade.md` will try to link the bound model via the `slug`
column instead of the `id` column.

#### Specifying the Model Location

By default, Folio will look for your model within your application's `app/Models` directory. However, if needed, you can
specify the full model class name in your template's filename:

```bash
# pages/users/[.App.Models.User].blade.md → /users/1
```

### Writing PHP Code in Your File

If you need to write PHP code within your file, you must end it in a `.blade.md`.

You can then use the `@php` Blade directive:

```php
@php
    if (! Auth::user()->can('view-posts', $user)) {
        abort(403);
    }

    $posts = $user->posts;
@endphp

@foreach ($posts as $post)
    <div>
        {{ $post->title }}
    </div>
@endforeach
```

Please note that in Folio, the `<?php` and `?>` tags are only for Folio page definition functions such as `middleware`
and `withTrashed`, which are not supported by Folio Markdown at this time.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
