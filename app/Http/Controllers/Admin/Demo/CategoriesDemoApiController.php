<?php

namespace App\Http\Controllers\Admin\Demo;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoriesDemoApiController extends Controller
{
    public function pageData(): JsonResponse
    {
        try {
            $payload = $this->readStore();

            return $this->successResponse('Category page data loaded.', $payload['data']);
        } catch (\Throwable $exception) {
            return $this->errorResponse(500, 'Unable to load category JSON store.');
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:Active,Draft'],
            'parent_id' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        try {
            $payload = $this->readStore();
            $data = $payload['data'];
            $categories = $data['categories'];

            $name = trim((string) $request->input('name'));
            $slugInput = trim((string) $request->input('slug', ''));
            $status = $request->input('status') === 'Active' ? 'Active' : 'Draft';
            $description = trim((string) $request->input('description', ''));
            $parentId = $this->toPositiveInt($request->input('parent_id'));

            if ($parentId !== null && !$this->categoryExists($categories, $parentId)) {
                return $this->validationErrorResponse([
                    'parent_id' => ['Selected parent category does not exist.'],
                ]);
            }

            $slug = $slugInput !== '' ? Str::slug($slugInput) : Str::slug($name);
            if ($slug === '') {
                return $this->validationErrorResponse([
                    'slug' => ['Slug could not be generated from this category name.'],
                ]);
            }

            if ($this->slugExists($categories, $slug)) {
                return $this->validationErrorResponse([
                    'slug' => ['Slug already exists. Please choose a different slug.'],
                ]);
            }

            $newId = $this->nextCategoryId($categories);

            array_unshift($categories, [
                'id' => $newId,
                'name' => $name,
                'slug' => $slug,
                'parent_id' => $parentId,
                'parent_name' => null,
                'description' => $description,
                'products' => 0,
                'share' => 0,
                'status' => $status,
                'updated_at' => 'Just now',
            ]);

            $data['categories'] = $this->syncParentNames($categories);
            $data = $this->syncMetrics($data);

            $payload['data'] = $data;
            $payload['meta']['version'] = (int) ($payload['meta']['version'] ?? 1) + 1;
            $this->writeStore($payload);

            return $this->successResponse(sprintf('Category "%s" created.', $name), $data);
        } catch (\Throwable $exception) {
            return $this->errorResponse(500, 'Unable to create category in JSON store.');
        }
    }

    public function update(Request $request, int $categoryId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:Active,Draft'],
            'parent_id' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        try {
            $payload = $this->readStore();
            $data = $payload['data'];
            $categories = $data['categories'];
            $index = $this->findCategoryIndex($categories, $categoryId);

            if ($index === null) {
                return $this->errorResponse(404, 'Category not found.');
            }

            $name = trim((string) $request->input('name'));
            $slugInput = trim((string) $request->input('slug', ''));
            $status = $request->input('status') === 'Active' ? 'Active' : 'Draft';
            $description = trim((string) $request->input('description', ''));
            $parentId = $this->toPositiveInt($request->input('parent_id'));

            if ($parentId !== null && !$this->categoryExists($categories, $parentId)) {
                return $this->validationErrorResponse([
                    'parent_id' => ['Selected parent category does not exist.'],
                ]);
            }

            if ($parentId !== null && $parentId === $categoryId) {
                return $this->validationErrorResponse([
                    'parent_id' => ['Parent category would create a loop.'],
                ]);
            }

            if ($this->createsCycle($categories, $categoryId, $parentId)) {
                return $this->validationErrorResponse([
                    'parent_id' => ['Parent category would create a loop.'],
                ]);
            }

            $slug = $slugInput !== '' ? Str::slug($slugInput) : Str::slug($name);
            if ($slug === '') {
                return $this->validationErrorResponse([
                    'slug' => ['Slug could not be generated from this category name.'],
                ]);
            }

            if ($this->slugExists($categories, $slug, $categoryId)) {
                return $this->validationErrorResponse([
                    'slug' => ['Slug already exists. Please choose a different slug.'],
                ]);
            }

            $current = $categories[$index];
            $categories[$index] = [
                'id' => (int) $current['id'],
                'name' => $name,
                'slug' => $slug,
                'parent_id' => $parentId,
                'parent_name' => null,
                'description' => $description,
                'products' => max(0, (int) ($current['products'] ?? 0)),
                'share' => $this->clampPercent((int) ($current['share'] ?? 0)),
                'status' => $status,
                'updated_at' => 'Just now',
            ];

            $data['categories'] = $this->syncParentNames($categories);
            $data = $this->syncMetrics($data);

            $payload['data'] = $data;
            $payload['meta']['version'] = (int) ($payload['meta']['version'] ?? 1) + 1;
            $this->writeStore($payload);

            return $this->successResponse(sprintf('Category "%s" updated.', $name), $data);
        } catch (\Throwable $exception) {
            return $this->errorResponse(500, 'Unable to update category in JSON store.');
        }
    }

    public function destroy(int $categoryId): JsonResponse
    {
        try {
            $payload = $this->readStore();
            $data = $payload['data'];
            $categories = $data['categories'];
            $index = $this->findCategoryIndex($categories, $categoryId);

            if ($index === null) {
                return $this->errorResponse(404, 'Category not found.');
            }

            $category = $categories[$index];
            $name = (string) ($category['name'] ?? 'Category');
            $productsCount = max(0, (int) ($category['products'] ?? 0));
            $childCount = count(array_filter($categories, function ($item) use ($categoryId) {
                return isset($item['parent_id']) && (int) $item['parent_id'] === $categoryId;
            }));

            if ($childCount > 0) {
                return $this->errorResponse(
                    403,
                    sprintf('Cannot delete "%s". It has %d child categories. Reassign child categories first.', $name, $childCount)
                );
            }

            if ($productsCount > 0) {
                return $this->errorResponse(
                    403,
                    sprintf('Cannot delete "%s". It has %d products. Move products to another category first.', $name, $productsCount)
                );
            }

            array_splice($categories, $index, 1);

            $data['categories'] = $this->syncParentNames($categories);
            $data = $this->syncMetrics($data);

            $payload['data'] = $data;
            $payload['meta']['version'] = (int) ($payload['meta']['version'] ?? 1) + 1;
            $this->writeStore($payload);

            return $this->successResponse(sprintf('"%s" removed.', $name), $data);
        } catch (\Throwable $exception) {
            return $this->errorResponse(500, 'Unable to delete category from JSON store.');
        }
    }

    public function generateDescription(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_name' => ['required', 'string', 'max:120'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $categoryName = trim((string) $request->input('category_name'));
        $description = sprintf(
            '%s includes curated, high-demand items with clear quality standards, reliable pricing, and quick fulfillment for repeat buyers.',
            $categoryName
        );

        return response()->json([
            'success' => true,
            'message' => sprintf('Description generated for "%s".', $categoryName),
            'data' => [
                'category_name' => $categoryName,
                'description' => $description,
            ],
            'errors' => null,
            'meta' => $this->meta(),
        ]);
    }

    public function commitSetup(Request $request): JsonResponse
    {
        $stagedChanges = max(0, (int) $request->input('staged_changes', 0));

        return response()->json([
            'success' => true,
            'message' => 'Category setup saved (json demo).',
            'data' => [
                'staged_changes' => $stagedChanges,
                'saved_at' => 'Just now',
            ],
            'errors' => null,
            'meta' => $this->meta(),
        ]);
    }

    private function readStore(): array
    {
        $path = $this->storePath();
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (!file_exists($path)) {
            $this->writeStore($this->defaultPayload());
        }

        $raw = file_get_contents($path);
        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        if (!is_array($decoded)) {
            $decoded = $this->defaultPayload();
            $this->writeStore($decoded);
        }

        $normalized = $this->normalizePayload($decoded);
        return $normalized;
    }

    private function writeStore(array $payload): void
    {
        $path = $this->storePath();
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (!is_string($encoded)) {
            throw new \RuntimeException('Unable to encode categories payload.');
        }

        file_put_contents($path, $encoded . PHP_EOL);
    }

    private function normalizePayload(array $payload): array
    {
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        $categories = [];
        foreach (($data['categories'] ?? []) as $rawCategory) {
            if (!is_array($rawCategory)) {
                continue;
            }

            $id = $this->toPositiveInt($rawCategory['id'] ?? null);
            if ($id === null) {
                continue;
            }

            $categories[] = [
                'id' => $id,
                'name' => trim((string) ($rawCategory['name'] ?? '')),
                'slug' => trim((string) ($rawCategory['slug'] ?? '')),
                'parent_id' => $this->toPositiveInt($rawCategory['parent_id'] ?? null),
                'parent_name' => null,
                'description' => trim((string) ($rawCategory['description'] ?? '')),
                'products' => max(0, (int) ($rawCategory['products'] ?? 0)),
                'share' => $this->clampPercent((int) ($rawCategory['share'] ?? 0)),
                'status' => ($rawCategory['status'] ?? 'Draft') === 'Active' ? 'Active' : 'Draft',
                'updated_at' => trim((string) ($rawCategory['updated_at'] ?? 'Just now')),
            ];
        }

        $normalizedData = [
            'metrics' => is_array($data['metrics'] ?? null) ? array_values($data['metrics']) : $this->defaultMetrics(),
            'categories' => $this->syncParentNames($categories),
            'suggestionSchedule' => is_array($data['suggestionSchedule'] ?? null)
                ? $data['suggestionSchedule']
                : ['next_reset_in' => '-', 'next_reset_at' => '-'],
            'suggestions' => is_array($data['suggestions'] ?? null) ? array_values($data['suggestions']) : [],
        ];

        $normalizedData = $this->syncMetrics($normalizedData);

        return [
            'success' => true,
            'message' => (string) ($payload['message'] ?? 'Category payload loaded.'),
            'data' => $normalizedData,
            'errors' => null,
            'meta' => [
                'source' => 'database/json',
                'version' => (int) ($payload['meta']['version'] ?? 1),
            ],
        ];
    }

    private function defaultPayload(): array
    {
        return [
            'success' => true,
            'message' => 'Category payload initialized.',
            'data' => [
                'metrics' => $this->defaultMetrics(),
                'categories' => [],
                'suggestionSchedule' => [
                    'next_reset_in' => '-',
                    'next_reset_at' => '-',
                ],
                'suggestions' => [],
            ],
            'errors' => null,
            'meta' => [
                'source' => 'database/json',
                'version' => 1,
            ],
        ];
    }

    private function defaultMetrics(): array
    {
        return [
            ['label' => 'Total Categories', 'value' => '0', 'meta' => '0 drafts pending approval'],
            ['label' => 'Catalog Coverage', 'value' => '96%', 'meta' => '4% products uncategorized'],
            ['label' => 'Top Category', 'value' => '-', 'meta' => '0% share of catalog'],
            ['label' => 'Updated Today', 'value' => '0', 'meta' => 'Last sync now'],
        ];
    }

    private function syncParentNames(array $categories): array
    {
        $namesById = [];
        foreach ($categories as $category) {
            $namesById[(int) $category['id']] = (string) $category['name'];
        }

        foreach ($categories as $index => $category) {
            $parentId = $this->toPositiveInt($category['parent_id'] ?? null);
            $categories[$index]['parent_name'] = $parentId !== null ? ($namesById[$parentId] ?? null) : null;
        }

        return array_values($categories);
    }

    private function syncMetrics(array $data): array
    {
        $metrics = is_array($data['metrics'] ?? null) ? array_values($data['metrics']) : $this->defaultMetrics();
        $categories = is_array($data['categories'] ?? null) ? $data['categories'] : [];

        foreach ($metrics as $index => $metric) {
            if (!is_array($metric)) {
                continue;
            }

            $label = strtolower(trim((string) ($metric['label'] ?? '')));

            if ($label === 'total categories') {
                $metrics[$index]['value'] = (string) count($categories);
            }

            if ($label === 'top category') {
                $top = null;
                foreach ($categories as $category) {
                    if ($top === null || (int) ($category['share'] ?? 0) > (int) ($top['share'] ?? 0)) {
                        $top = $category;
                    }
                }

                $topName = $top['name'] ?? '-';
                $topShare = isset($top['share']) ? (int) $top['share'] : 0;
                $metrics[$index]['value'] = (string) $topName;
                $metrics[$index]['meta'] = sprintf('%d%% share of catalog', $topShare);
            }
        }

        $data['metrics'] = $metrics;

        return $data;
    }

    private function categoryExists(array $categories, int $categoryId): bool
    {
        foreach ($categories as $category) {
            if ((int) ($category['id'] ?? 0) === $categoryId) {
                return true;
            }
        }

        return false;
    }

    private function findCategoryIndex(array $categories, int $categoryId): ?int
    {
        foreach ($categories as $index => $category) {
            if ((int) ($category['id'] ?? 0) === $categoryId) {
                return $index;
            }
        }

        return null;
    }

    private function nextCategoryId(array $categories): int
    {
        $max = 1000;
        foreach ($categories as $category) {
            $id = (int) ($category['id'] ?? 0);
            if ($id > $max) {
                $max = $id;
            }
        }

        return $max + 1;
    }

    private function slugExists(array $categories, string $slug, ?int $excludeId = null): bool
    {
        $target = Str::lower(trim($slug));

        foreach ($categories as $category) {
            $currentId = (int) ($category['id'] ?? 0);
            if ($excludeId !== null && $currentId === $excludeId) {
                continue;
            }

            if (Str::lower((string) ($category['slug'] ?? '')) === $target) {
                return true;
            }
        }

        return false;
    }

    private function createsCycle(array $categories, int $categoryId, ?int $parentId): bool
    {
        if ($parentId === null) {
            return false;
        }

        $parentById = [];
        foreach ($categories as $category) {
            $id = (int) ($category['id'] ?? 0);
            $parentById[$id] = $this->toPositiveInt($category['parent_id'] ?? null);
        }

        $cursor = $parentId;
        while ($cursor !== null) {
            if ($cursor === $categoryId) {
                return true;
            }

            $cursor = $parentById[$cursor] ?? null;
        }

        return false;
    }

    private function toPositiveInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $parsed = (int) $value;
        return $parsed > 0 ? $parsed : null;
    }

    private function clampPercent(int $value): int
    {
        if ($value < 0) {
            return 0;
        }

        if ($value > 100) {
            return 100;
        }

        return $value;
    }

    private function successResponse(string $message, array $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => $this->meta(),
        ], $status);
    }

    private function validationErrorResponse(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'data' => null,
            'errors' => $errors,
            'meta' => $this->meta(),
        ], 422);
    }

    private function errorResponse(int $status, string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => null,
            'meta' => $this->meta(),
        ], $status);
    }

    private function meta(): array
    {
        return [
            'source' => 'database/json',
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function storePath(): string
    {
        return database_path('json/categories.json');
    }
}
