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
        if ($state->onLastUriSegment() &&
            $path = $this->findWildcardView($state->currentDirectory())) {
            $str = Str::of($path);
            foreach ($this->extensions as $extension) {
                $str->before($extension);
            }

            return new MatchedView($state->currentDirectory().'/'.$path, $state->withData(
                $str->match('/\[(.*)\]/')->value(),
                $state->currentUriSegment(),
            )->data);
        }

        return $next($state);
    }
}
