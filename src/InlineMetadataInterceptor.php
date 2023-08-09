<?php

namespace Snelling\FolioMarkdown;

use Laravel\Folio\Metadata;
use Laravel\Folio\Pipeline\MatchedView;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class InlineMetadataInterceptor
{
    /**
     * The cached path to metadata mappings.
     */
    protected array $cache = [];

    /**
     * Intercept the inline metadata for the given matched view.
     */
    public function intercept(MatchedView $matchedView): Metadata
    {
        if (array_key_exists($matchedView->path, $this->cache)) {
            return $this->cache[$matchedView->path];
        }

        $metadata = Metadata::instance();
        $matter = YamlFrontMatter::parseFile($matchedView->path)->matter();
        $metadata->withTrashed = ($matter['withTrashed'] ?? false);
        $metadata->middleware = collect();
        if (isset($matter['middleware'])) {
            $middleware = explode(',', $matter['middleware']);
            foreach ($middleware as $m) {
                $metadata->middleware->push(trim($m));
            }
        }

        return $this->cache[$matchedView->path] = $metadata;
    }
}
