<?php

namespace Snelling\FolioMarkdown\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Snelling\FolioMarkdown\FolioMarkdown
 */
class FolioMarkdown extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Snelling\FolioMarkdown\FolioMarkdown::class;
    }
}
