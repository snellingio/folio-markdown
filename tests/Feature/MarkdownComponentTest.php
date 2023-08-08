
<?php

use Laravel\Folio\Folio;
use Snelling\FolioMarkdown\Facades\FolioMarkdown;

it('may use component props', function () {
    Folio::route(__DIR__.'/resources/views/pages');
    FolioMarkdown::register();

    $this->withoutExceptionHandling();

    $response = $this->get('/posts/stuff');

    $response
        ->assertSee('My Page Title Is: Stuff.')
        ->assertSee('This is a post with about stuff.');

    expect($response->getContent())
        ->toStartWith('<!DOCTYPE html>');
});

it('may use parameters as component props', function () {
    Folio::route(__DIR__.'/resources/views/pages');
    FolioMarkdown::register();

    $this->withoutExceptionHandling();

    $response = $this->get('/posts/1');

    $response
        ->assertSee('My Page Title Is: Post.')
        ->assertSee('This is a post with an id.')
        ->assertSee('My Page ID Is: 1.');

    expect($response->getContent())
        ->toStartWith('<!DOCTYPE html>');
});
