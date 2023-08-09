<?php

namespace Snelling\FolioMarkdown;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Folio\Folio;
use Laravel\Folio\MountPath;
use Laravel\Folio\Pipeline\MatchedView;

class FolioMarkdown
{
    /**
     * @var string[]
     */
    protected array $supportedExtensions = ['.blade.md', '.md'];

    /**
     * The mount paths.
     *
     * @var array<int, MountPath>
     */
    protected array $mountPaths = [];

    /**
     * The callback that should be used to render matched views.
     */
    protected ?Closure $renderUsing = null;

    /**
     * The callback that should be used when terminating the manager.
     */
    protected ?Closure $terminateUsing = null;

    /**
     * The view that was last matched by Folio.
     */
    protected ?MatchedView $lastMatchedView = null;

    /**
     * Create a new Folio manager instance.
     */
    public function __construct(protected Application $app)
    {

    }

    /**
     * @param  array  $extensions
     * @return FolioMarkdown
     */
    public function extensions($extensions = []): static
    {
        $this->supportedExtensions = $extensions;

        return $this;
    }

    public function getSupportedExtensions(): array
    {
        return $this->supportedExtensions;
    }

    public function register(): void
    {
        $this->mountPaths = Folio::mountPaths();

        $placeholder = 'markdownFallbackPlaceholder';

        Route::addRoute(
            'GET', "{{$placeholder}}", $this->handler()
        )->where($placeholder, '.*');
    }

    /**
     * Specify the callback that should be used to render matched views.
     */
    public function renderUsing(Closure $callback = null): static
    {
        $this->renderUsing = $callback;

        return $this;
    }

    /**
     * Execute the pending termination callback.
     */
    public function terminate(): void
    {
        if ($this->terminateUsing) {
            try {
                ($this->terminateUsing)();
            } finally {
                $this->terminateUsing = null;
            }
        }
    }

    /**
     * Specify the callback that should be used when terminating the application.
     */
    public function terminateUsing(Closure $callback = null): static
    {
        $this->terminateUsing = $callback;

        return $this;
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
