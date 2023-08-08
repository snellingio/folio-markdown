<?php

namespace Snelling\FolioMarkdown\Pipeline;

use Closure;
use Laravel\Folio\Pipeline\MatchedView;
use Laravel\Folio\Pipeline\State;

class MatchDirectoryIndexViews
{
    /**
     * Invoke the routing pipeline handler.
     */
    public function __invoke(State $state, Closure $next): mixed
    {
        return $state->onLastUriSegment() &&
            $state->currentUriSegmentIsDirectory() &&
            file_exists($path = $state->currentUriSegmentDirectory().'/index.md')
                ? new MatchedView($path, $state->data)
                : $next($state);
    }
}
