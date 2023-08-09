<?php

namespace Snelling\FolioMarkdown;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Laravel\Folio\Events;
use Laravel\Folio\FolioManager;
use Laravel\Folio\MountPath;
use Laravel\Folio\Pipeline\MatchedView;
use Spatie\LaravelMarkdown\MarkdownRenderer;
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
        app()->extend(\Laravel\Folio\InlineMetadataInterceptor::class, fn ($app) => new \Snelling\FolioMarkdown\InlineMetadataInterceptor());
        foreach ($this->mountPaths as $mountPath) {
            $requestPath = '/'.ltrim($request->path(), '/');

            $uri = '/'.ltrim(substr($requestPath, strlen($mountPath->baseUri)), '/');

            if ($matchedView = (new Router($mountPath, $this->manager->getSupportedExtensions()))->match($request, $uri)) {
                break;
            }
        }

        // Pass the request to the next handler if no view was matched.
        if (! isset($matchedView)) {
            app()->extend(\Laravel\Folio\InlineMetadataInterceptor::class, fn ($app) => new \Laravel\Folio\InlineMetadataInterceptor());
            /** @phpstan-ignore-next-line */
            return invade(app(FolioManager::class))->handler()($request);
        }

        app(Dispatcher::class)->dispatch(new Events\ViewMatched($matchedView, $mountPath));

        $middleware = collect($this->middleware($mountPath, $matchedView));

        return (new Pipeline(app()))
            ->send($request)
            ->through($middleware->all())
            ->then(function (Request $request) use ($matchedView, $middleware) {
                if ($this->onViewMatch) {
                    ($this->onViewMatch)($matchedView);
                }

                $response = $this->renderUsing
                    ? ($this->renderUsing)($request, $matchedView)
                    : $this->toResponse($matchedView);

                $this->manager->terminateUsing(
                    fn (Application $app) => $middleware->filter(fn ($middleware) => is_string($middleware) && class_exists($middleware) && method_exists($middleware, 'terminate'))
                        ->map(fn (string $middleware) => $app->make($middleware))
                        ->each(fn (object $middleware) => $app->call([$middleware, 'terminate'], ['request' => $request, 'response' => $response]))
                );

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
     * Create a response instance for the given-matched view.
     */
    protected function toResponse(MatchedView $matchedView): Response
    {
        $parser = YamlFrontMatter::parseFile($matchedView->path);
        $data = $parser->matter() + $matchedView->data;
        $slot = $parser->body();
        if (Str::of($matchedView->path)->endsWith('.blade.md')) {
            $slot = \Blade::render($slot, $data);
        }

        // @todo make this configurable
        $slot = app(MarkdownRenderer::class)
            ->disableHighlighting()
            ->disableAnchors()
            ->toHtml($slot);

        if (isset($data['view'])) {
            $data['slot'] = $slot;

            return new Response(
                View::make($data['view'], $data),
                200,
                ['Content-Type' => 'text/html'],
            );
        }

        return new Response(
            $slot,
            200,
            ['Content-Type' => 'text/html'],
        );
    }
}
