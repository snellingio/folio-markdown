<?php

namespace Snelling\FolioMarkdown\Pipeline;

use Closure;
use Illuminate\Support\Str;
use Laravel\Folio\Pipeline\MatchedView;
use Laravel\Folio\Pipeline\State;

class MatchWildcardViews
{
    use FindsWildcardViews;

    /**
     * Invoke the routing pipeline handler.
     */
    public function __invoke(State $state, Closure $next): mixed
    {
        if ($state->onLastUriSegment() &&
            $path = $this->findWildcardView($state->currentDirectory())) {
            return new MatchedView($state->currentDirectory().'/'.$path, $state->withData(
                Str::of($path)
                    ->before('.md')
                    ->match('/\[(.*)\]/')->value(),
                $state->currentUriSegment(),
            )->data);
        }

        return $next($state);
    }
}
