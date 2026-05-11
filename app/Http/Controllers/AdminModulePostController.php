<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

abstract class AdminModulePostController extends Controller
{
    /**
     * Resource routes may use {id} or the default singular key (e.g. {movie}); resolve the primary key.
     */
    protected function routeContentId(Request $request): string
    {
        $params = $request->route()->parameters();
        foreach (['id', 'movie', 'series', 'web_tv', 'wallpaper', 'game', 'software', 'application'] as $key) {
            if (isset($params[$key]) && $params[$key] !== '') {
                return (string) $params[$key];
            }
        }

        abort(404);
    }

    /**
     * @return array{
     *     route_prefix: string,
     *     model: class-string<Model>,
     *     link_model: class-string<Model>,
     *     foreign_key: string,
     *     slug_fallback: string,
     *     label: string,
     *     entity_singular: string,
     *     route_param: string
     * }
     */
    abstract protected static function definition(): array;

    /**
     * Override in a child module when extra fields are needed.
     *
     * @return array<string, mixed>
     */
    protected function extraValidationRules(): array
    {
        return [];
    }

    /**
     * Override in a child module to persist extra module-specific values on create.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function extraCreatePayload(array $validated): array
    {
        return [];
    }

    /**
     * Override in a child module to persist extra module-specific values on update.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function extraUpdatePayload(array $validated): array
    {
        return [];
    }

    public function index(Request $request): View
    {
        $def = static::definition();
        $adminId = (int) $request->session()->get('admin_id');
        /** @var class-string<Model> $modelClass */
        $modelClass = $def['model'];

        $records = $modelClass::query()
            ->where('created_by', $adminId)
            ->withCount('downloadLinks')
            ->latest()
            ->paginate(15);

        return view('admin.module_posts.index', [
            'records' => $records,
            'title' => $def['label'],
            'routePrefix' => $def['route_prefix'],
            'entitySingular' => $def['entity_singular'],
            'routeParam' => $def['route_param'],
        ]);
    }

    public function create(): View
    {
        $def = static::definition();

        return view('admin.module_posts.create', [
            'title' => $def['label'],
            'routePrefix' => $def['route_prefix'],
            'entitySingular' => $def['entity_singular'],
            'routeParam' => $def['route_param'],
            'downloadRows' => [['label' => '', 'file_url' => '', 'file_name' => '']],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $def = static::definition();
        $adminId = (int) $request->session()->get('admin_id');
        /** @var class-string<Model> $modelClass */
        $modelClass = $def['model'];

        $validated = $request->validate(array_merge([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
            'downloads' => ['nullable', 'array'],
            'downloads.*.label' => ['nullable', 'string', 'max:255'],
            'downloads.*.file_url' => ['nullable', 'string', 'max:2000'],
            'downloads.*.file_name' => ['nullable', 'string', 'max:255'],
        ], $this->extraValidationRules()));

        $slug = $this->uniqueSlug($modelClass, $adminId, $validated['slug'] ?? null, $validated['title'], null, $def['slug_fallback']);
        $downloads = $this->normalizeDownloads($validated['downloads'] ?? []);

        DB::transaction(function () use ($modelClass, $def, $adminId, $validated, $slug, $downloads): void {
            /** @var Model $post */
            $post = $modelClass::create(array_merge([
                'title' => $validated['title'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'created_by' => $adminId,
            ], $this->extraCreatePayload($validated)));
            $this->syncDownloadLinks($def, $post, $downloads);
        });

        return redirect()->route($def['route_prefix'].'.index')->with('status', $def['label'].' entry created successfully.');
    }

    public function edit(Request $request): View
    {
        $def = static::definition();
        /** @var class-string<Model> $modelClass */
        $modelClass = $def['model'];
        $adminId = (int) $request->session()->get('admin_id');
        $id = $this->routeContentId($request);

        /** @var Model $record */
        $record = $modelClass::query()->where('created_by', $adminId)->findOrFail($id);
        $record->load('downloadLinks');

        $downloadRows = [];
        foreach ($record->downloadLinks as $link) {
            $downloadRows[] = [
                'label' => $link->label,
                'file_url' => $link->file_url,
                'file_name' => $link->file_name,
            ];
        }

        if ($downloadRows === []) {
            $downloadRows = [['label' => '', 'file_url' => '', 'file_name' => '']];
        }

        return view('admin.module_posts.edit', [
            'record' => $record,
            'title' => $def['label'],
            'routePrefix' => $def['route_prefix'],
            'entitySingular' => $def['entity_singular'],
            'routeParam' => $def['route_param'],
            'downloadRows' => $downloadRows,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $def = static::definition();
        /** @var class-string<Model> $modelClass */
        $modelClass = $def['model'];
        $adminId = (int) $request->session()->get('admin_id');
        $id = $this->routeContentId($request);

        /** @var Model $record */
        $record = $modelClass::query()->where('created_by', $adminId)->findOrFail($id);

        $validated = $request->validate(array_merge([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
            'downloads' => ['nullable', 'array'],
            'downloads.*.label' => ['nullable', 'string', 'max:255'],
            'downloads.*.file_url' => ['nullable', 'string', 'max:2000'],
            'downloads.*.file_name' => ['nullable', 'string', 'max:255'],
        ], $this->extraValidationRules()));

        $slug = $this->uniqueSlug($modelClass, $adminId, $validated['slug'] ?? null, $validated['title'], (int) $record->getKey(), $def['slug_fallback']);
        $downloads = $this->normalizeDownloads($validated['downloads'] ?? []);

        DB::transaction(function () use ($def, $record, $validated, $slug, $downloads): void {
            $record->update(array_merge([
                'title' => $validated['title'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
            ], $this->extraUpdatePayload($validated)));
            $record->downloadLinks()->delete();
            $this->syncDownloadLinks($def, $record, $downloads);
        });

        return redirect()->route($def['route_prefix'].'.index')->with('status', $def['label'].' entry updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $def = static::definition();
        /** @var class-string<Model> $modelClass */
        $modelClass = $def['model'];
        $adminId = (int) $request->session()->get('admin_id');
        $id = $this->routeContentId($request);

        $record = $modelClass::query()->where('created_by', $adminId)->findOrFail($id);
        $record->delete();

        return redirect()->route($def['route_prefix'].'.index')->with('status', $def['label'].' entry deleted.');
    }

    /**
     * @param  array<string, mixed>  $def
     * @param  list<array{label: string|null, file_url: string, file_name: string|null}>  $rows
     */
    private function syncDownloadLinks(array $def, Model $post, array $rows): void
    {
        /** @var class-string<Model> $linkClass */
        $linkClass = $def['link_model'];
        $fk = $def['foreign_key'];

        foreach ($rows as $index => $row) {
            $linkClass::create([
                $fk => $post->getKey(),
                'label' => $row['label'] ?: null,
                'file_url' => $row['file_url'],
                'file_name' => $row['file_name'] ?: null,
                'file_size' => null,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $raw
     * @return list<array{label: string|null, file_url: string, file_name: string|null}>
     */
    private function normalizeDownloads(?array $raw): array
    {
        if ($raw === null) {
            return [];
        }

        $out = [];
        foreach ($raw as $row) {
            $url = isset($row['file_url']) ? trim((string) $row['file_url']) : '';
            if ($url === '') {
                continue;
            }
            $out[] = [
                'label' => isset($row['label']) ? trim((string) $row['label']) : null,
                'file_url' => $url,
                'file_name' => isset($row['file_name']) ? trim((string) $row['file_name']) : null,
            ];
        }

        return $out;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function uniqueSlug(string $modelClass, int $adminId, ?string $preferredSlug, string $title, ?int $ignoreId, string $fallback): string
    {
        $base = Str::slug($preferredSlug !== null && $preferredSlug !== '' ? $preferredSlug : $title);
        if ($base === '') {
            $base = $fallback;
        }

        $slug = $base;
        $n = 2;
        while ($modelClass::query()
            ->where('created_by', $adminId)
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$n;
            $n++;
        }

        return $slug;
    }
}
