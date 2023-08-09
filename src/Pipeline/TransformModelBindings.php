<?php

namespace Snelling\FolioMarkdown\Pipeline;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Folio\Pipeline\MatchedView;
use Laravel\Folio\Pipeline\PotentiallyBindablePathSegment;
use Laravel\Folio\Pipeline\State;

class TransformModelBindings
{
    /**
     * Create a new pipeline step instance.
     */
    public function __construct(protected Request $request, protected array $extensions)
    {
    }

    /**
     * Invoke the routing pipeline handler.
     */
    public function __invoke(State $state, Closure $next): mixed
    {
        if (! ($view = $next($state)) instanceof MatchedView) {
            return $view;
        }

        [$uriSegments, $pathSegments] = [
            explode('/', $state->uri),
            $this->bindablePathSegments($view),
        ];

        foreach ($pathSegments as $index => $segment) {
            if (! ($segment = new PotentiallyBindablePathSegment($segment))->bindable()) {
                continue;
            }

            if ($segment->capturesMultipleSegments()) {
                $view = $this->initializeVariable(
                    $view, $segment, array_slice($uriSegments, $index)
                );

                return $view->replace(
                    $segment->trimmed(),
                    $segment->variable(),
                    collect(array_slice($uriSegments, $index))
                        ->map(fn (string $value) => $segment->resolveOrFail(
                            $value, $parent ?? null, $view->allowsTrashedBindings()
                        ))
                        ->all(),
                );
            }

            $view = $this->initializeVariable($view, $segment, $uriSegments[$index]);

            $view = $view->replace(
                $segment->trimmed(),
                $segment->variable(),
                $resolved = $segment->resolveOrFail(
                    $uriSegments[$index],
                    $parent ?? null,
                    $view->allowsTrashedBindings()
                ),
            );

            $parent = $resolved;
        }

        if ($this->request->route()) {
            foreach ($view->data as $key => $value) {
                $this->request->route()->setParameter($key, $value);
            }
        }

        return $view;
    }

    /**
     * Get the bindable path segments for the matched view.
     *
     * @return string[]
     */
    protected function bindablePathSegments(MatchedView $view): array
    {
        $segments = Str::of($view->path)->replace($view->mountPath, '');

        foreach ($this->extensions as $extension) {
            $segments->beforeLast($extension);
        }

        return explode(DIRECTORY_SEPARATOR, (string) $segments->trim(DIRECTORY_SEPARATOR));
    }

    /**
     * Initialize a given variable on the matched view, so we can intercept the page metadata without errors.
     */
    protected function initializeVariable(MatchedView $view, PotentiallyBindablePathSegment $segment, mixed $value): MatchedView
    {
        return $view->replace(
            $segment->trimmed(),
            $segment->variable(),
            $value,
        );
    }
}
