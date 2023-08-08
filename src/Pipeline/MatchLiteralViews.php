<?php

namespace Snelling\FolioMarkdown\Pipeline;

use Closure;
use Laravel\Folio\Pipeline\MatchedView;
use Laravel\Folio\Pipeline\State;

class MatchLiteralViews
{
    /**
     * Invoke the routing pipeline handler.
     */
    public function __invoke(State $state, Closure $next): mixed
    {
        return $state->onLastUriSegment() &&
        file_exists($path = $state->currentDirectory().'/'.$state->currentUriSegment().'.md')
            ? new MatchedView($path, $state->data)
            : $next($state);
    }
}
