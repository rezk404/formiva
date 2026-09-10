<?php

declare(strict_types=1);

namespace App\Content;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class CachedContent implements ContentRepository
{
    public function __construct(
        private readonly ContentRepository $content,
        private readonly CacheRepository $cache,
        private readonly int $ttl = 3600,
        private readonly string $prefix = 'formiva.content',
    ) {
    }

    public function site(): array { return $this->remember('site', fn (): array => $this->content->site()); }
    public function services(): array { return $this->remember('services', fn (): array => $this->content->services()); }
    public function projects(): array { return $this->remember('projects', fn (): array => $this->content->projects()); }
    public function featuredProjects(): array { return $this->remember('featured-projects', fn (): array => $this->content->featuredProjects()); }
    public function project(string $slug): ?array { return $this->remember('project.'.$slug, fn (): ?array => $this->content->project($slug)); }
    public function projectNeighbors(string $slug): array { return $this->remember('project-neighbors.'.$slug, fn (): array => $this->content->projectNeighbors($slug)); }
    public function caseStudy(): array { return $this->remember('case-study', fn (): array => $this->content->caseStudy()); }
    public function studio(): array { return $this->remember('studio', fn (): array => $this->content->studio()); }
    public function process(): array { return $this->remember('process', fn (): array => $this->content->process()); }
    public function team(): array { return $this->remember('team', fn (): array => $this->content->team()); }
    public function testimonials(): array { return $this->remember('testimonials', fn (): array => $this->content->testimonials()); }
    public function insights(): array { return $this->remember('insights', fn (): array => $this->content->insights()); }
    public function insight(string $slug): ?array { return $this->remember('insight.'.$slug, fn (): ?array => $this->content->insight($slug)); }
    public function clients(): array { return $this->remember('clients', fn (): array => $this->content->clients()); }
    public function intake(): array { return $this->remember('intake', fn (): array => $this->content->intake()); }

    /**
     * Invalidate everything by moving the version the keys are namespaced by.
     *
     * Written as a read-then-write rather than increment() because the
     * counter does not exist until the first flush: increment() on a missing
     * key lands on 1, which was also the default the keys already used, so
     * the very first save after a deploy invalidated nothing and the edit
     * stayed invisible for the rest of the TTL. Starting the default at 0
     * and writing explicitly makes the first flush count like every other.
     */
    public function flush(): bool
    {
        $this->cache->forever($this->versionKey(), $this->version() + 1);

        return true;
    }

    private function remember(string $name, callable $callback): mixed
    {
        return $this->cache->remember($this->key($name), $this->ttl, $callback);
    }

    private function version(): int
    {
        return (int) $this->cache->get($this->versionKey(), 0);
    }

    private function key(string $name): string
    {
        return $this->prefix.'.'.$this->version().'.'.$name;
    }

    private function versionKey(): string
    {
        return $this->prefix.'.version';
    }
}
