<?php

namespace Snelling\FolioMarkdown\Pipeline;

use Closure;
use Illuminate\Support\Str;
use Laravel\Folio\Pipeline\MatchedView;
use Laravel\Folio\Pipeline\State;

class MatchWildcardViewsThatCaptureMultipleSegments
{
    use FindsWildcardViews;

    /**
     * Create a new pipeline handler instance.
     *
     * @param  string[]  $extensions
     */
    public function __construct(protected array $extensions)
    {
    }

    /**
     * Invoke the routing pipeline handler.
     */
    public function __invoke(State $state, Closure $next): mixed
    {
        if ($path = $this->findWildcardMultiSegmentView($state->currentDirectory())) {
            $str = Str::of($path);
            foreach ($this->extensions as $extension) {
                $str->before($extension);
            }

            return new MatchedView($state->currentDirectory().'/'.$path, $state->withData(
                $str->match('/\[\.\.\.(.*)\]/')->value(),
                array_slice(
                    $state->segments,
                    $state->currentIndex,
                    $state->uriSegmentCount()
                )
            )->data);
        }

        return $next($state);
    }
}
