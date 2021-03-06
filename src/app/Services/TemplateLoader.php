<?php

namespace LaravelEnso\Tables\app\Services;

use Illuminate\Support\Str;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use LaravelEnso\Tables\app\Contracts\Table;

class TemplateLoader
{
    private $table;
    private $template;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function handle()
    {
        $this->load();

        return $this->template;
    }

    private function load()
    {
        if ($this->cache()->has($this->cacheKey())) {
            $cache = $this->cache()->get($this->cacheKey());

            $this->template = (new Template())
                ->load($cache['template'], $cache['meta']);

            return;
        }

        $this->template = (new Template())->build($this->table);

        if ($this->shouldCache()) {
            $this->cache()->put($this->cacheKey(), $this->template->toArray());
        }
    }

    private function shouldCache()
    {
        $type = $this->template->get('templateCache',
            config('enso.tables.cache.template'));

        switch ($type) {
            case 'never':
                return false;
            case 'always':
                return true;
            default:
                return app()->environment($type);
        }
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
