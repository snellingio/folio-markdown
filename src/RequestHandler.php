<?php

namespace Snelling\FolioMarkdown;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Laravel\Folio\Events;
use Laravel\Folio\FolioManager;
use Laravel\Folio\MountPath;
use Laravel\Folio\Pipeline\MatchedView;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Str;

class RequestHandler
{
    /**
     * Create a new request handler instance.
     *
     * @param  array<int, MountPath>  $mountPaths
     */
    public function __construct(
        protected FolioMarkdown $manager,
        protected array $mountPaths,
        protected ?Closure $renderUsing = null,
        protected ?Closure $onViewMatch = null,
    ) {
    }

    /**
     * Handle the incoming request using Folio.
     */
    public function __invoke(Request $request): mixed
    {
        foreach ($this->mountPaths as $mountPath) {
            $requestPath = '/'.ltrim($request->path(), '/');

            $uri = '/'.ltrim(substr($requestPath, strlen($mountPath->baseUri)), '/');

            if ($matchedView = (new Router($mountPath))->match($request, $uri)) {
                break;
            }
        }

        if (! $matchedView) {
            return invade(app(FolioManager::class))->handler()($request);
        }

        app(Dispatcher::class)->dispatch(new Events\ViewMatched($matchedView, $mountPath));

        $middleware = collect($this->middleware($mountPath, $matchedView));

        return (new Pipeline(app()))
            ->send($request)
            ->through($middleware->all())
            ->then(function (Request $request) use ($matchedView) {
                if ($this->onViewMatch) {
                    ($this->onViewMatch)($matchedView);
                }

                $response = $this->renderUsing
                    ? ($this->renderUsing)($request, $matchedView)
                    : $this->toResponse($matchedView);

                /*
                $this->manager->terminateUsing(
                    fn (Application $app) => $middleware->filter(fn ($middleware) => is_string($middleware) && class_exists($middleware) && method_exists($middleware, 'terminate'))
                        ->map(fn (string $middleware) => $app->make($middleware))
                        ->each(fn (object $middleware) => $app->call([$middleware, 'terminate'], ['request' => $request, 'response' => $response]))
                );
                */

                return $response;
            });
    }

    /**
     * Get the middleware that should be applied to the matched view.
     */
    protected function middleware(MountPath $mountPath, MatchedView $matchedView): array
    {
        return Route::resolveMiddleware(
            $mountPath
                ->middleware
                ->match($matchedView)
                ->prepend('web')
                ->merge($matchedView->inlineMiddleware())
                ->unique()
                ->values()
                ->all()
        );
    }

    /**
     * Create a response instance for the given matched view.
     */
    protected function toResponse(MatchedView $matchedView): Response
    {
        $parser = YamlFrontMatter::parse(file_get_contents($matchedView->path));
        $data = $parser->matter() + $matchedView->data;
        $body = new HtmlString(Str::markdown($parser->body()));

        if (isset($data['view'])) {
            $data['slot'] = $body;

            return new Response(
                View::make($data['view'], $data),
                200,
                ['Content-Type' => 'text/html'],
            );
        }

        return new Response(
            $body,
            200,
            ['Content-Type' => 'text/html'],
        );
    }
}
