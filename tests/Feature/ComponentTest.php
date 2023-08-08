<?php

use Laravel\Folio\Folio;
use Snelling\FolioMarkdown\Facades\FolioMarkdown;

it('may use component props', function () {
    Folio::route(__DIR__.'/resources/views/pages');
    FolioMarkdown::register();

    $this->withoutExceptionHandling();

    $response = $this->get('/dashboard');

    $response
        ->assertSee('My Page Title Is: Videos.')
        ->assertSee('Hello From Main Content');

    expect($response->getContent())
        ->toStartWith('<!DOCTYPE html>');
});
