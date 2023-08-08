<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\View;
use Laravel\Folio\FolioServiceProvider;
use Laravel\Folio\MountPath;
use Orchestra\Testbench\TestCase as Orchestra;
use Snelling\FolioMarkdown\FolioMarkdownServiceProvider;
use Snelling\FolioMarkdown\Router;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__.'/Feature/resources/views');
    }

    protected function getPackageProviders($app)
    {
        return [
            FolioMarkdownServiceProvider::class,
            FolioServiceProvider::class,
        ];
    }

    /**
     * Create the given views.
     *
     * @param  array<string, array<string, string>|string>  $views
     */
    protected function views(array $views, $directory = null): void
    {
        $directory ??= __DIR__.'/tmp/views';

        foreach ($views as $key => $value) {
            if (is_array($value)) {
                (new Filesystem)->ensureDirectoryExists(
                    $directory.$key,
                );

                $this->views($value, $directory.$key);
            } else {
                touch($directory.$value);
            }
        }
    }

    /**
     * Create a new router instance.
     */
    protected function router(): Router
    {
        return new Router(
            new MountPath(__DIR__.'/tmp/views', '/', [], null),
        );
    }

    /**
     * Create a new router instance.
     */
    protected function folio(): \Laravel\Folio\Router
    {
        return new \Laravel\Folio\Router(
            new MountPath(__DIR__.'/tmp/views', '/', [], null),
        );
    }
}
