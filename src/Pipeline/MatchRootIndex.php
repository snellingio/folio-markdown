<?php

namespace Snelling\FolioMarkdown\Pipeline;

use Closure;
use Laravel\Folio\Pipeline\MatchedView;
use Laravel\Folio\Pipeline\State;
use Laravel\Folio\Pipeline\StopIterating;

class MatchRootIndex
{
    /**
     * Invoke the routing pipeline handler.
     */
    public function __invoke(State $state, Closure $next): mixed
    {
        if (trim($state->uri) === '/') {
            return file_exists($path = $state->mountPath.'/index.md')
                ? new MatchedView($path, $state->data)
                : new StopIterating;
        }

        return $next($state);
    }
}
