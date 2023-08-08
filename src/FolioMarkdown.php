<?php

namespace Snelling\FolioMarkdown;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Folio\Folio;
use Laravel\Folio\MountPath;

class FolioMarkdown
{
    /**
     * Create a new Folio manager instance.
     */
    public function __construct(protected Application $app)
    {

    }

    public function scopeFromFolio()
    {
        $this->mountPaths = Folio::mountPaths();
        Route::fallback($this->handler());
    }

    protected function handler(): Closure
    {
        return function (Request $request) {
            $mountPaths = collect($this->mountPaths)->filter(
                fn (MountPath $mountPath) => str_starts_with(mb_strtolower('/'.$request->path()), $mountPath->baseUri)
            )->all();

            return (new RequestHandler(
                $this,
                $mountPaths,
            ))($request);
        };
    }
}
