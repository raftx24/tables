<?php

namespace LaravelEnso\Tables\app\Services;

use Illuminate\Support\Str;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use LaravelEnso\Tables\app\Contracts\Table;

class TemplateLoader
{
    private $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function get()
    {
        if ($this->cache()->has($this->cacheKey())) {
            return $this->cache()->get($this->cacheKey());
        }

        $template = new Template($this->table);

        if ($template->shouldCache()) {
            $this->cache()->put($this->cacheKey(), $template->get());
        }

        return $template->get();
    }

    private function cacheKey(): string
    {
        return config('enso.tables.cache.prefix')
            .':'.Str::slug(str_replace(
                ['/', '.'], [' ', ' '], $this->table->templatePath()
            ));
    }

    private function cache()
    {
        return Cache::getStore() instanceof TaggableStore
            ? Cache::tags(config('enso.tables.cache.tag'))
            : Cache::store();
    }
}