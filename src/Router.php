<?php

namespace Snelling\FolioMarkdown;

use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Laravel\Folio\MountPath;
use Laravel\Folio\Pipeline\ContinueIterating;
use Laravel\Folio\Pipeline\EnsureMatchesDomain;
use Laravel\Folio\Pipeline\EnsureNoDirectoryTraversal;
use Laravel\Folio\Pipeline\MatchedView;
use Laravel\Folio\Pipeline\MatchLiteralDirectories;
use Laravel\Folio\Pipeline\MatchWildcardDirectories;
use Laravel\Folio\Pipeline\SetMountPathOnMatchedView;
use Laravel\Folio\Pipeline\State;
use Laravel\Folio\Pipeline\StopIterating;
use Laravel\Folio\Pipeline\TransformModelBindings;
use Snelling\FolioMarkdown\Pipeline\MatchDirectoryIndexViews;
use Snelling\FolioMarkdown\Pipeline\MatchLiteralViews;
use Snelling\FolioMarkdown\Pipeline\MatchRootIndex;
use Snelling\FolioMarkdown\Pipeline\MatchWildcardViews;
use Snelling\FolioMarkdown\Pipeline\MatchWildcardViewsThatCaptureMultipleSegments;

class Router
{
    /**
     * Create a new router instance.
     */
    public function __construct(protected MountPath $mountPath)
    {
    }

    /**
     * Match the given URI to a view via page based routing.
     */
    public function match(Request $request, string $uri): ?MatchedView
    {
        $uri = strlen($uri) > 1 ? trim($uri, '/') : $uri;

        if ($view = $this->matchAtPath($request, $uri)) {
            return $view;
        }

        return null;
    }

    /**
     * Resolve the given URI via page based routing at the given mount path.
     */
    protected function matchAtPath(Request $request, string $uri): ?MatchedView
    {
        $state = new State(
            uri: $uri,
            mountPath: $this->mountPath->path,
            segments: explode('/', $uri)
        );

        for ($i = 0; $i < $state->uriSegmentCount(); $i++) {
            $value = (new Pipeline)
                ->send($state->forIteration($i))
                ->through([
                    new EnsureMatchesDomain($request, $this->mountPath),
                    new EnsureNoDirectoryTraversal,
                    new TransformModelBindings($request),
                    new SetMountPathOnMatchedView,
                    new MatchRootIndex,
                    new MatchDirectoryIndexViews,
                    new MatchWildcardViewsThatCaptureMultipleSegments,
                    new MatchLiteralDirectories,
                    new MatchWildcardDirectories,
                    new MatchLiteralViews,
                    new MatchWildcardViews,
                ])->then(fn () => new StopIterating);

            if ($value instanceof MatchedView) {
                return $value;
            }

            if ($value instanceof ContinueIterating) {
                $state = $value->state;

                continue;
            }

            if ($value instanceof StopIterating) {
                break;
            }
        }

        return null;
    }
}
