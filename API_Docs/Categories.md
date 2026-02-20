# Categories API Spec
Page: `http://127.0.0.1:8000/admin/categories`

## API style and standards
- Style: REST (JSON)
- Base path: `/api/v1/admin`
- Auth for every endpoint: Bearer token in header
- Header format:
`Authorization: Bearer <ACCESS_TOKEN>`
`Accept: application/json`
`Content-Type: application/json` (except GET)

## Response envelope (consistent for all endpoints)
```json
{
  "success": true,
  "message": "Human readable message",
  "data": {},
  "errors": null,
  "meta": {
    "request_id": "req_01HXYZ...",
    "timestamp": "2026-02-20T16:00:00Z"
  }
}
```

Error envelope:
```json
{
  "success": false,
  "message": "Request failed",
  "data": null,
  "errors": [
    {
      "code": "validation_error",
      "field": "name",
      "message": "The name field is required."
    }
  ],
  "meta": {
    "request_id": "req_01HXYZ...",
    "timestamp": "2026-02-20T16:00:00Z"
  }
}
```

## Pagination / sorting / filtering conventions
- Pagination params:
`page` (default `1`), `per_page` (default `20`, max `100`)
- Sorting params:
`sort_by` in `name|status|products_count|share_percent|updated_at|created_at`
`sort_dir` in `asc|desc`
- Filtering params:
`search` (name/slug partial), `status` (`active|draft`), `parent_id`, `has_products` (`true|false`)
- Lists return:
`meta.pagination = { page, per_page, total, total_pages }`

---

## A) Dynamic components and user actions on this page
1. KPI metrics cards (Total Categories, Coverage, Top Category, Updated Today)
2. Add New Category form (name, slug, visibility, parent, description)
3. AI Description helper (`AI Write`) with processing state
4. Add Category action (`+ Add Category`)
5. Edit mode toggle per table row (`Edit`) which hides add panel and shows edit panel
6. Edit Category form prefilled with selected row data
7. Update Category action (`Update Category`)
8. Cancel Edit action (`Cancel Edit`)
9. Categories table listing (category, parent, slug, products, share, status, updated, actions)
10. Delete action per row (`Delete`) with business guards
11. Delete feedback UI (inline success/error messages)
12. Total categories badge count
13. Suggestions drawer toggle/open/close
14. Suggestions list with reset-time indicators
15. Top-level button `Save Category Setup` (supports staged commit mode)

---

## B) Complete API list required (CRUD + extras)
1. `GET /api/v1/admin/categories/bootstrap` - all page data on initial load
2. `GET /api/v1/admin/categories` - categories table list with filters/search/sort/pagination
3. `POST /api/v1/admin/categories` - create category
4. `GET /api/v1/admin/categories/{category_id}` - fetch one category for edit prefill
5. `PATCH /api/v1/admin/categories/{category_id}` - update category
6. `DELETE /api/v1/admin/categories/{category_id}` - delete category with guards
7. `POST /api/v1/admin/categories/ai/description` - AI short-description generation
8. `GET /api/v1/admin/categories/suggestions` - suggestions drawer data + reset schedule
9. `POST /api/v1/admin/categories/commit` - optional staged bulk commit for "Save Category Setup"

Note:
- Bulk endpoint included because page has a global save action suitable for staged operations.
- File upload endpoint is not included because this page has no category image upload UI.
- Reorder endpoint is not included because this page has no drag-and-drop or reorder control.

---

## C) Endpoint specs

### 1) Bootstrap
**Endpoint**: `/api/v1/admin/categories/bootstrap`
**Method**: `GET`
**Auth**: Bearer token in `Authorization` header

**Query params**:
- `page`, `per_page`, `search`, `status`, `parent_id`, `sort_by`, `sort_dir`
- `include` optional CSV: `metrics,parent_options,suggestions`

**Request body**: none

**Response data schema**:
```json
{
  "metrics": [
    { "key": "total_categories", "label": "Total Categories", "value": "5", "meta": "1 draft pending approval" }
  ],
  "categories": [
    {
      "id": 101,
      "name": "Apparel",
      "slug": "apparel",
      "status": "active",
      "description": "...",
      "parent": null,
      "products_count": 84,
      "share_percent": 46,
      "updated_at": "2026-02-20T14:00:00Z"
    }
  ],
  "parent_options": [
    { "id": 101, "name": "Apparel" }
  ],
  "suggestion_schedule": {
    "next_reset_in": "2h 40m",
    "next_reset_at": "2026-02-20T23:30:00Z"
  },
  "suggestions": [
    { "id": "sug_1", "title": "Launch New Arrival Category", "note": "...", "next_reset_in": "2h 40m", "next_reset_at": "2026-02-20T23:30:00Z" }
  ]
}
```

**Success example (200)**:
```json
{
  "success": true,
  "message": "Categories page bootstrap loaded.",
  "data": {
    "metrics": [
      { "key": "total_categories", "label": "Total Categories", "value": "5", "meta": "1 draft pending approval" },
      { "key": "coverage", "label": "Catalog Coverage", "value": "96%", "meta": "4% products uncategorized" }
    ],
    "categories": [
      {
        "id": 101,
        "name": "Apparel",
        "slug": "apparel",
        "status": "active",
        "description": "Everyday fashion essentials and seasonal wear.",
        "parent": null,
        "products_count": 84,
        "share_percent": 46,
        "updated_at": "2026-02-20T14:00:00Z"
      }
    ],
    "parent_options": [
      { "id": 101, "name": "Apparel" },
      { "id": 102, "name": "Electronics" }
    ],
    "suggestion_schedule": {
      "next_reset_in": "2h 40m",
      "next_reset_at": "2026-02-20T23:30:00Z"
    },
    "suggestions": [
      { "id": "sug_1", "title": "Launch New Arrival Category", "note": "Create a dedicated New Arrival category.", "next_reset_in": "2h 40m", "next_reset_at": "2026-02-20T23:30:00Z" }
    ]
  },
  "errors": null,
  "meta": {
    "request_id": "req_bootstrap_001",
    "timestamp": "2026-02-20T16:30:00Z",
    "pagination": { "page": 1, "per_page": 20, "total": 5, "total_pages": 1 }
  }
}
```

**Validation error example (422)**:
```json
{
  "success": false,
  "message": "Validation failed.",
  "data": null,
  "errors": [
    { "code": "validation_error", "field": "sort_by", "message": "sort_by must be one of: name,status,products_count,share_percent,updated_at,created_at" }
  ],
  "meta": { "request_id": "req_bootstrap_422", "timestamp": "2026-02-20T16:30:00Z" }
}
```

**Forbidden example (403)**:
```json
{
  "success": false,
  "message": "You are not allowed to view categories.",
  "data": null,
  "errors": [
    { "code": "forbidden", "field": null, "message": "Missing permission: categories:read" }
  ],
  "meta": { "request_id": "req_bootstrap_403", "timestamp": "2026-02-20T16:30:00Z" }
}
```

**Not found example (404)**:
```json
{
  "success": false,
  "message": "Requested parent category not found.",
  "data": null,
  "errors": [
    { "code": "not_found", "field": "parent_id", "message": "Category 99999 was not found." }
  ],
  "meta": { "request_id": "req_bootstrap_404", "timestamp": "2026-02-20T16:30:00Z" }
}
```

---

### 2) List categories
**Endpoint**: `/api/v1/admin/categories`
**Method**: `GET`
**Auth**: Bearer token in `Authorization` header

**Query params**:
- `page`, `per_page`, `search`, `status`, `parent_id`, `has_products`, `sort_by`, `sort_dir`

**Request body**: none

**Response data schema**:
```json
{
  "items": [
    {
      "id": 101,
      "name": "Apparel",
      "slug": "apparel",
      "status": "active",
      "parent": null,
      "products_count": 84,
      "share_percent": 46,
      "updated_at": "2026-02-20T14:00:00Z"
    }
  ]
}
```

**Success example (200)**:
```json
{
  "success": true,
  "message": "Categories fetched.",
  "data": {
    "items": [
      {
        "id": 101,
        "name": "Apparel",
        "slug": "apparel",
        "status": "active",
        "parent": null,
        "products_count": 84,
        "share_percent": 46,
        "updated_at": "2026-02-20T14:00:00Z"
      }
    ]
  },
  "errors": null,
  "meta": {
    "request_id": "req_list_001",
    "timestamp": "2026-02-20T16:30:00Z",
    "pagination": { "page": 1, "per_page": 20, "total": 5, "total_pages": 1 }
  }
}
```

**Validation error (422)**:
```json
{
  "success": false,
  "message": "Validation failed.",
  "data": null,
  "errors": [
    { "code": "validation_error", "field": "per_page", "message": "per_page may not be greater than 100." }
  ],
  "meta": { "request_id": "req_list_422", "timestamp": "2026-02-20T16:30:00Z" }
}
```

**Forbidden (403)**:
```json
{
  "success": false,
  "message": "You are not allowed to view categories.",
  "data": null,
  "errors": [
    { "code": "forbidden", "field": null, "message": "Missing permission: categories:read" }
  ],
  "meta": { "request_id": "req_list_403", "timestamp": "2026-02-20T16:30:00Z" }
}
```

**Not found (404)**:
```json
{
  "success": false,
  "message": "Parent category not found.",
  "data": null,
  "errors": [
    { "code": "not_found", "field": "parent_id", "message": "Category 99999 was not found." }
  ],
  "meta": { "request_id": "req_list_404", "timestamp": "2026-02-20T16:30:00Z" }
}
```

---

### 3) Create category
**Endpoint**: `/api/v1/admin/categories`
**Method**: `POST`
**Auth**: Bearer token in `Authorization` header

**Query params**: none

**Request body schema**:
```json
{
  "name": "string, required, 2..120",
  "slug": "string, optional (auto-generate if missing)",
  "status": "active|draft, required",
  "parent_id": "integer|null",
  "description": "string, optional, max 1000"
}
```

**Success (201)**:
```json
{
  "success": true,
  "message": "Category created.",
  "data": {
    "id": 106,
    "name": "Beauty",
    "slug": "beauty",
    "status": "active",
    "parent": null,
    "description": "Personal care and beauty essentials.",
    "products_count": 0,
    "share_percent": 0,
    "updated_at": "2026-02-20T16:30:00Z"
  },
  "errors": null,
  "meta": { "request_id": "req_create_201", "timestamp": "2026-02-20T16:30:00Z" }
}
```

**Validation error (422)**:
```json
{
  "success": false,
  "message": "Validation failed.",
  "data": null,
  "errors": [
    { "code": "validation_error", "field": "name", "message": "The name field is required." },
    { "code": "validation_error", "field": "slug", "message": "The slug has already been taken." }
  ],
  "meta": { "request_id": "req_create_422", "timestamp": "2026-02-20T16:30:00Z" }
}
```

**Forbidden (403)**:
```json
{
  "success": false,
  "message": "You are not allowed to create categories.",
  "data": null,
  "errors": [
    { "code": "forbidden", "field": null, "message": "Missing permission: categories:write" }
  ],
  "meta": { "request_id": "req_create_403", "timestamp": "2026-02-20T16:30:00Z" }
}
```

**Not found (404)**:
```json
{
  "success": false,
  "message": "Parent category not found.",
  "data": null,
  "errors": [
    { "code": "not_found", "field": "parent_id", "message": "Category 99999 was not found." }
  ],
  "meta": { "request_id": "req_create_404", "timestamp": "2026-02-20T16:30:00Z" }
}
```

---

### 4) Get one category (edit prefill)
**Endpoint**: `/api/v1/admin/categories/{category_id}`
**Method**: `GET`
**Auth**: Bearer token in `Authorization` header

**Query params**:
- `include` optional CSV: `parent_options,metrics`

**Request body**: none

**Success (200)**:
```json
{
  "success": true,
  "message": "Category fetched.",
  "data": {
    "id": 104,
    "name": "Accessories",
    "slug": "accessories",
    "status": "draft",
    "parent": { "id": 101, "name": "Apparel" },
    "description": "Bags, belts, and daily carry items.",
    "products_count": 52,
    "share_percent": 10,
    "updated_at": "2026-02-17T10:00:00Z",
    "parent_options": [
      { "id": 101, "name": "Apparel" },
      { "id": 102, "name": "Electronics" }
    ]
  },
  "errors": null,
  "meta": { "request_id": "req_get_200", "timestamp": "2026-02-20T16:30:00Z" }
}
```

**Validation error (422)**:
```json
{
  "success": false,
  "message": "Validation failed.",
  "data": null,
  "errors": [
    { "code": "validation_error", "field": "category_id", "message": "category_id must be an integer." }
  ],
  "meta": { "request_id": "req_get_422", "timestamp": "2026-02-20T16:30:00Z" }
}
```

**Forbidden (403)**:
```json
{
  "success": false,
  "message": "You are not allowed to view this category.",
  "data": null,
  "errors": [
    { "code": "forbidden", "field": null, "message": "Missing permission: categories:read" }
  ],
  "meta": { "request_id": "req_get_403", "timestamp": "2026-02-20T16:30:00Z" }
}
```

**Not found (404)**:
```json
{
  "success": false,
  "message": "Category not found.",
  "data": null,
  "errors": [
    { "code": "not_found", "field": "category_id", "message": "Category 99999 was not found." }
  ],
  "meta": { "request_id": "req_get_404", "timestamp": "2026-02-20T16:30:00Z" }
}
```

---

### 5) Update category
**Endpoint**: `/api/v1/admin/categories/{category_id}`
**Method**: `PATCH`
**Auth**: Bearer token in `Authorization` header

**Query params**: none

**Request body schema**:
```json
{
  "name": "string, optional",
  "slug": "string, optional",
  "status": "active|draft, optional",
  "parent_id": "integer|null, optional",
  "description": "string, optional, max 1000"
}
```

**Success (200)**:
```json
{
  "success": true,
  "message": "Category updated.",
  "data": {
    "id": 104,
    "name": "Accessories",
    "slug": "accessories",
    "status": "active",
    "parent": { "id": 101, "name": "Apparel" },
    "description": "Updated description.",
    "products_count": 52,
    "share_percent": 10,
    "updated_at": "2026-02-20T16:31:00Z"
  },
  "errors": null,
  "meta": { "request_id": "req_patch_200", "timestamp": "2026-02-20T16:31:00Z" }
}
```

**Validation error (422)**:
```json
{
  "success": false,
  "message": "Validation failed.",
  "data": null,
  "errors": [
    { "code": "validation_error", "field": "slug", "message": "The slug has already been taken." },
    { "code": "validation_error", "field": "parent_id", "message": "A category cannot be its own parent." }
  ],
  "meta": { "request_id": "req_patch_422", "timestamp": "2026-02-20T16:31:00Z" }
}
```

**Forbidden (403)**:
```json
{
  "success": false,
  "message": "You are not allowed to update categories.",
  "data": null,
  "errors": [
    { "code": "forbidden", "field": null, "message": "Missing permission: categories:write" }
  ],
  "meta": { "request_id": "req_patch_403", "timestamp": "2026-02-20T16:31:00Z" }
}
```

**Not found (404)**:
```json
{
  "success": false,
  "message": "Category not found.",
  "data": null,
  "errors": [
    { "code": "not_found", "field": "category_id", "message": "Category 99999 was not found." }
  ],
  "meta": { "request_id": "req_patch_404", "timestamp": "2026-02-20T16:31:00Z" }
}
```

---

### 6) Delete category
**Endpoint**: `/api/v1/admin/categories/{category_id}`
**Method**: `DELETE`
**Auth**: Bearer token in `Authorization` header

**Query params**: none

**Request body**: none

**Success (200)**:
```json
{
  "success": true,
  "message": "Category deleted.",
  "data": {
    "id": 105,
    "deleted": true
  },
  "errors": null,
  "meta": { "request_id": "req_delete_200", "timestamp": "2026-02-20T16:32:00Z" }
}
```

**Validation error (422)**:
```json
{
  "success": false,
  "message": "Validation failed.",
  "data": null,
  "errors": [
    { "code": "validation_error", "field": "category_id", "message": "category_id must be an integer." }
  ],
  "meta": { "request_id": "req_delete_422", "timestamp": "2026-02-20T16:32:00Z" }
}
```

**Forbidden (403)**:
```json
{
  "success": false,
  "message": "You are not allowed to delete categories.",
  "data": null,
  "errors": [
    { "code": "forbidden", "field": null, "message": "Missing permission: categories:delete" }
  ],
  "meta": { "request_id": "req_delete_403", "timestamp": "2026-02-20T16:32:00Z" }
}
```

**Not found (404)**:
```json
{
  "success": false,
  "message": "Category not found.",
  "data": null,
  "errors": [
    { "code": "not_found", "field": "category_id", "message": "Category 99999 was not found." }
  ],
  "meta": { "request_id": "req_delete_404", "timestamp": "2026-02-20T16:32:00Z" }
}
```

**Business-rule conflict (409, required for this page)**:
```json
{
  "success": false,
  "message": "Category cannot be deleted.",
  "data": null,
  "errors": [
    { "code": "has_products", "field": "category_id", "message": "Category has 52 products. Move products first." },
    { "code": "has_children", "field": "category_id", "message": "Category has 2 child categories. Reassign children first." }
  ],
  "meta": { "request_id": "req_delete_409", "timestamp": "2026-02-20T16:32:00Z" }
}
```

---

### 7) AI short description
**Endpoint**: `/api/v1/admin/categories/ai/description`
**Method**: `POST`
**Auth**: Bearer token in `Authorization` header

**Query params**: none

**Request body schema**:
```json
{
  "name": "string, required",
  "tone": "string, optional, default: concise",
  "max_chars": "integer, optional, default: 180"
}
```

**Success (200)**:
```json
{
  "success": true,
  "message": "Description generated.",
  "data": {
    "name": "Beauty",
    "description": "Beauty brings curated essentials with quality checks, fair pricing, and daily-use relevance.",
    "model": "demo-ai-v1",
    "processing_ms": 980
  },
  "errors": null,
  "meta": { "request_id": "req_ai_200", "timestamp": "2026-02-20T16:32:00Z" }
}
```

**Validation error (422)**:
```json
{
  "success": false,
  "message": "Validation failed.",
  "data": null,
  "errors": [
    { "code": "validation_error", "field": "name", "message": "Please add category name." }
  ],
  "meta": { "request_id": "req_ai_422", "timestamp": "2026-02-20T16:32:00Z" }
}
```

**Forbidden (403)**:
```json
{
  "success": false,
  "message": "You are not allowed to use AI generation.",
  "data": null,
  "errors": [
    { "code": "forbidden", "field": null, "message": "Missing permission: ai:generate" }
  ],
  "meta": { "request_id": "req_ai_403", "timestamp": "2026-02-20T16:32:00Z" }
}
```

**Not found (404)**:
```json
{
  "success": false,
  "message": "AI provider configuration not found.",
  "data": null,
  "errors": [
    { "code": "not_found", "field": "provider", "message": "No active AI provider configured for this workspace." }
  ],
  "meta": { "request_id": "req_ai_404", "timestamp": "2026-02-20T16:32:00Z" }
}
```

---

### 8) Suggestions drawer data
**Endpoint**: `/api/v1/admin/categories/suggestions`
**Method**: `GET`
**Auth**: Bearer token in `Authorization` header

**Query params**:
- `limit` (default `10`, max `50`)

**Request body**: none

**Success (200)**:
```json
{
  "success": true,
  "message": "Suggestions loaded.",
  "data": {
    "schedule": {
      "next_reset_in": "2h 40m",
      "next_reset_at": "2026-02-20T23:30:00Z"
    },
    "items": [
      {
        "id": "sug_1",
        "title": "Launch New Arrival Category",
        "note": "Create a dedicated New Arrival category for faster discovery.",
        "next_reset_in": "2h 40m",
        "next_reset_at": "2026-02-20T23:30:00Z"
      }
    ]
  },
  "errors": null,
  "meta": { "request_id": "req_suggestions_200", "timestamp": "2026-02-20T16:33:00Z" }
}
```

**Validation error (422)**:
```json
{
  "success": false,
  "message": "Validation failed.",
  "data": null,
  "errors": [
    { "code": "validation_error", "field": "limit", "message": "limit may not be greater than 50." }
  ],
  "meta": { "request_id": "req_suggestions_422", "timestamp": "2026-02-20T16:33:00Z" }
}
```

**Forbidden (403)**:
```json
{
  "success": false,
  "message": "You are not allowed to view suggestions.",
  "data": null,
  "errors": [
    { "code": "forbidden", "field": null, "message": "Missing permission: categories:read" }
  ],
  "meta": { "request_id": "req_suggestions_403", "timestamp": "2026-02-20T16:33:00Z" }
}
```

**Not found (404)**:
```json
{
  "success": false,
  "message": "Suggestion profile not found.",
  "data": null,
  "errors": [
    { "code": "not_found", "field": "workspace", "message": "No suggestion profile found for workspace." }
  ],
  "meta": { "request_id": "req_suggestions_404", "timestamp": "2026-02-20T16:33:00Z" }
}
```

---

### 9) Save Category Setup (staged commit)
**Endpoint**: `/api/v1/admin/categories/commit`
**Method**: `POST`
**Auth**: Bearer token in `Authorization` header

**Query params**: none

**Request body schema**:
```json
{
  "operations": [
    {
      "op": "create|update|delete",
      "id": "integer required for update/delete",
      "client_ref": "string optional",
      "payload": {
        "name": "string",
        "slug": "string",
        "status": "active|draft",
        "parent_id": "integer|null",
        "description": "string"
      }
    }
  ]
}
```

**Success (200)**:
```json
{
  "success": true,
  "message": "Category setup saved.",
  "data": {
    "applied": 3,
    "failed": 0,
    "results": [
      { "op": "create", "client_ref": "tmp_1", "id": 106, "status": "applied" },
      { "op": "update", "id": 104, "status": "applied" },
      { "op": "delete", "id": 105, "status": "applied" }
    ]
  },
  "errors": null,
  "meta": { "request_id": "req_commit_200", "timestamp": "2026-02-20T16:34:00Z" }
}
```

**Validation error (422)**:
```json
{
  "success": false,
  "message": "Validation failed.",
  "data": null,
  "errors": [
    { "code": "validation_error", "field": "operations", "message": "operations must contain at least one item." },
    { "code": "validation_error", "field": "operations.0.op", "message": "op must be create, update, or delete." }
  ],
  "meta": { "request_id": "req_commit_422", "timestamp": "2026-02-20T16:34:00Z" }
}
```

**Forbidden (403)**:
```json
{
  "success": false,
  "message": "You are not allowed to save category setup.",
  "data": null,
  "errors": [
    { "code": "forbidden", "field": null, "message": "Missing permission: categories:write" }
  ],
  "meta": { "request_id": "req_commit_403", "timestamp": "2026-02-20T16:34:00Z" }
}
```

**Not found (404)**:
```json
{
  "success": false,
  "message": "Category not found for one or more operations.",
  "data": null,
  "errors": [
    { "code": "not_found", "field": "operations.1.id", "message": "Category 99999 was not found." }
  ],
  "meta": { "request_id": "req_commit_404", "timestamp": "2026-02-20T16:34:00Z" }
}
```

---

## D) Frontend data flow summary

### On page load
1. Call `GET /api/v1/admin/categories/bootstrap`
2. Render:
- Metrics cards
- Add form parent dropdown options
- Table data
- Total badge
- Suggestions schedule/count data

### On search/filter/sort/pagination
1. Call `GET /api/v1/admin/categories` with updated query params
2. Replace table rows + pagination + total count

### On AI Write click (description)
1. Validate local category name is non-empty
2. Call `POST /api/v1/admin/categories/ai/description`
3. While pending:
- disable `+ Add Category`
- show `Processing...`
4. On success: fill description textarea
5. On error: show inline/status message

### On create (`+ Add Category`)
1. Call `POST /api/v1/admin/categories`
2. On success:
- prepend/append row in table or refetch list
- refresh metrics and parent dropdown options (either via bootstrap or targeted calls)

### On edit open (`Edit` button)
1. Option A (faster): prefill from row dataset
2. Option B (authoritative): call `GET /api/v1/admin/categories/{id}` and prefill
3. Hide create panel, show edit panel

### On update (`Update Category`)
1. Call `PATCH /api/v1/admin/categories/{id}`
2. On success update row + badges + timestamp; hide edit panel; show create panel

### On delete (`Delete`)
1. Call `DELETE /api/v1/admin/categories/{id}`
2. If API returns `409` with `has_products` or `has_children`: show inline error UI
3. On success remove row and update total/metrics

### On suggestions drawer open
1. Call `GET /api/v1/admin/categories/suggestions` (optional: lazy-load on first open)
2. Render schedule + suggestion items

### On top button `Save Category Setup`
1. If using staged edits in frontend, call `POST /api/v1/admin/categories/commit`
2. Show applied/failed summary toast
3. Optionally refetch bootstrap after commit

---

## Not required for this current UI
- Category image upload API: not required (no image field on page)
- Reorder API: not required (no drag-and-drop or reorder control on page)

