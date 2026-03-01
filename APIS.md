URL: http://localhost:8000/admin/products/create
On this url, when users click to save product run the product creating url, with ajax, 

"Type Checklist" build this section with js,

curl -X POST http://localhost:8082/api/admin/products \
  -H "x-refresh-token: YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "physical",
    "name": "Classic T-Shirt",
    "category": "Apparel",
    "short_description": "100% cotton tee",
    "description": "Premium quality cotton t-shirt available in multiple sizes and colors",

    "is_variants": true,
    "product_price": null,
    "bargaining_price": null,
    "available_qty": null,
    "stock_alert": null,
    "weight": null,
    "shipping_profile": "standard",

    "is_discount_offer": "limited",
    "is_discount_type": "percentage",
    "discount_value": 15,
    "discount_start_at": "2026-03-01T00:00:00Z",
    "discount_end_at": "2026-03-31T23:59:59Z",

    "publish_type": "immediately",
    "publish_at": null,

    "cover": "https://cdn.example.com/tshirt-cover.jpg",
    "is_slider": true,
    "media_items": [
      { "media_type": "image",        "source_url": "https://cdn.example.com/tshirt-front.jpg" },
      { "media_type": "image",        "source_url": "https://cdn.example.com/tshirt-back.jpg" },
      { "media_type": "upload_video", "source_url": "https://cdn.example.com/tshirt-demo.mp4" }
    ],

    "variants": [
      { "have_size": true, "size": "S", "have_color": true, "color": "Red",   "qty": 50, "alert_qty": 5,  "weight": 0.3 },
      { "have_size": true, "size": "M", "have_color": true, "color": "Red",   "qty": 80, "alert_qty": 10, "weight": 0.3 },
      { "have_size": true, "size": "L", "have_color": true, "color": "Red",   "qty": 30, "alert_qty": 5,  "weight": 0.35 },
      { "have_size": true, "size": "S", "have_color": true, "color": "Black", "qty": 60, "alert_qty": 5,  "weight": 0.3 },
      { "have_size": true, "size": "M", "have_color": true, "color": "Black", "qty": 90, "alert_qty": 10, "weight": 0.3 }
    ],

    "tags": ["tshirt", "cotton", "apparel"],
    "slug": "classic-tshirt",
    "meta_title": "Classic T-Shirt | Shop",
    "meta_description": "Buy our premium cotton t-shirt in multiple sizes",
    "seo_tags": ["buy tshirt online", "cotton tee shop"]
  }'
Response 201:
{ "id": 1, "message": "product created" }

---
Update

curl -X PUT http://localhost:8082/api/admin/products/1 \
  -H "x-refresh-token: YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "physical",
    "name": "Classic T-Shirt — Updated",
    "category": "Apparel",
    "short_description": "100% organic cotton tee",
    "description": "Now made with organic cotton",

    "is_variants": true,
    "product_price": null,
    "bargaining_price": null,
    "available_qty": null,
    "stock_alert": null,
    "weight": null,
    "shipping_profile": "express",

    "is_discount_offer": "inactive",
    "is_discount_type": "inactive",
    "discount_value": null,
    "discount_start_at": null,
    "discount_end_at": null,

    "publish_type": "immediately",
    "publish_at": null,

    "cover": "https://cdn.example.com/tshirt-cover-v2.jpg",
    "is_slider": true,
    "media_items": [
      { "media_type": "image", "source_url": "https://cdn.example.com/tshirt-front-v2.jpg" }
    ],

    "variants": [
      { "have_size": true, "size": "S", "have_color": true, "color": "White", "qty": 100, "alert_qty": 10, "weight": 0.3 },
      { "have_size": true, "size": "M", "have_color": true, "color": "White", "qty": 120, "alert_qty": 15, "weight": 0.3 },
      { "have_size": true, "size": "XL","have_color": true, "color": "Navy",  "qty": 40,  "alert_qty": 5,  "weight": 0.38 }
    ],

    "tags": ["tshirt", "organic", "apparel"],
    "slug": "classic-tshirt",
    "meta_title": "Classic T-Shirt Organic | Shop",
    "meta_description": "Organic cotton t-shirt in multiple sizes",
    "seo_tags": ["organic tshirt", "eco fashion"]
  }'
Response 200:
{ "message": "product updated" }

---
PHYSICAL — No Variants

Create

curl -X POST http://localhost:8082/api/admin/products \
  -H "x-refresh-token: YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "physical",
    "name": "Leather Wallet",
    "category": "Accessories",
    "short_description": "Slim genuine leather wallet",
    "description": "Handcrafted slim wallet made from full-grain leather",

    "is_variants": false,
    "product_price": 49.99,
    "bargaining_price": 44.99,
    "available_qty": 200,
    "stock_alert": 20,
    "weight": 0.15,
    "shipping_profile": "standard",

    "is_discount_offer": "lifetime",
    "is_discount_type": "fixed",
    "discount_value": 5,
    "discount_start_at": null,
    "discount_end_at": null,

    "publish_type": "immediately",
    "publish_at": null,

    "cover": "https://cdn.example.com/wallet-cover.jpg",
    "is_slider": true,
    "media_items": [
      { "media_type": "image",   "source_url": "https://cdn.example.com/wallet-open.jpg" },
      { "media_type": "image",   "source_url": "https://cdn.example.com/wallet-closed.jpg" },
      { "media_type": "yt_video","source_url": "https://youtube.com/watch?v=abc123" }
    ],

    "variants": [],

    "tags": ["wallet", "leather", "accessories"],
    "slug": "leather-wallet",
    "meta_title": "Slim Leather Wallet | Shop",
    "meta_description": "Handcrafted full-grain leather slim wallet",
    "seo_tags": ["leather wallet", "slim wallet", "mens wallet"]
  }'
Response 201:
{ "id": 2, "message": "product created" }

---
Update

curl -X PUT http://localhost:8082/api/admin/products/2 \
  -H "x-refresh-token: YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "physical",
    "name": "Leather Wallet — Premium",
    "category": "Accessories",
    "short_description": "Slim premium leather wallet",
    "description": "Upgraded with RFID blocking technology",

    "is_variants": false,
    "product_price": 59.99,
    "bargaining_price": 54.99,
    "available_qty": 150,
    "stock_alert": 15,
    "weight": 0.18,
    "shipping_profile": "standard",

    "is_discount_offer": "inactive",
    "is_discount_type": "inactive",
    "discount_value": null,
    "discount_start_at": null,
    "discount_end_at": null,

    "publish_type": "immediately",
    "publish_at": null,

    "cover": "https://cdn.example.com/wallet-premium-cover.jpg",
    "is_slider": false,
    "media_items": [
      { "media_type": "image", "source_url": "https://cdn.example.com/wallet-premium.jpg" }
    ],

    "variants": [],

    "tags": ["wallet", "leather", "rfid", "premium"],
    "slug": "leather-wallet",
    "meta_title": "Premium RFID Leather Wallet | Shop",
    "meta_description": "Premium leather wallet with RFID protection",
    "seo_tags": ["rfid wallet", "premium leather wallet"]
  }'
Response 200:
{ "message": "product updated" }

---
DOWNLOADABLE

Create

curl -X POST http://localhost:8082/api/admin/products \
  -H "x-refresh-token: YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "downloadable",
    "name": "Go Programming Masterclass",
    "category": "eBooks",
    "short_description": "Complete Go programming guide",
    "description": "Master Go from basics to advanced concurrency patterns",

    "is_variants": false,
    "product_price": 29.99,
    "bargaining_price": 24.99,
    "available_qty": 999,
    "stock_alert": 0,
    "weight": null,
    "shipping_profile": null,

    "is_discount_offer": "limited",
    "is_discount_type": "percentage",
    "discount_value": 20,
    "discount_start_at": "2026-03-01T00:00:00Z",
    "discount_end_at": "2026-03-31T23:59:59Z",

    "publish_type": "immediately",
    "publish_at": null,

    "cover": "https://cdn.example.com/gobook-cover.jpg",
    "is_slider": false,
    "media_items": [
      { "media_type": "image",   "source_url": "https://cdn.example.com/gobook-preview.jpg" },
      { "media_type": "yt_video","source_url": "https://youtube.com/watch?v=preview123" }
    ],

    "variants": [],

    "downloadables": [
      {
        "access_type": "direct",
        "drive_link": "https://drive.google.com/file/d/abc123/view",
        "access_instruction": "Click the link to download the PDF immediately"
      },
      {
        "access_type": "email",
        "drive_link": "https://drive.google.com/file/d/xyz456/view",
        "access_instruction": "A download link will be sent to your email within 5 minutes"
      }
    ],

    "tags": ["ebook", "golang", "programming"],
    "slug": "go-programming-masterclass",
    "meta_title": "Go Programming Masterclass eBook",
    "meta_description": "Master Go programming with this comprehensive guide",
    "seo_tags": ["golang ebook", "learn go programming", "go tutorial pdf"]
  }'
Response 201:
{ "id": 3, "message": "product created" }

---
Update

curl -X PUT http://localhost:8082/api/admin/products/3 \
  -H "x-refresh-token: YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "downloadable",
    "name": "Go Programming Masterclass — 2nd Edition",
    "category": "eBooks",
    "short_description": "Updated complete Go programming guide",
    "description": "Now includes Go 1.22 generics and new standard library features",

    "is_variants": false,
    "product_price": 34.99,
    "bargaining_price": 29.99,
    "available_qty": 999,
    "stock_alert": 0,
    "weight": null,
    "shipping_profile": null,

    "is_discount_offer": "inactive",
    "is_discount_type": "inactive",
    "discount_value": null,
    "discount_start_at": null,
    "discount_end_at": null,

    "publish_type": "immediately",
    "publish_at": null,

    "cover": "https://cdn.example.com/gobook-v2-cover.jpg",
    "is_slider": false,
    "media_items": [
      { "media_type": "image", "source_url": "https://cdn.example.com/gobook-v2-preview.jpg" }
    ],

    "variants": [],

    "downloadables": [
      {
        "access_type": "direct",
        "drive_link": "https://drive.google.com/file/d/newfile123/view",
        "access_instruction": "Click the link to download the updated 2nd edition PDF"
      }
    ],

    "tags": ["ebook", "golang", "programming", "generics"],
    "slug": "go-programming-masterclass",
    "meta_title": "Go Programming Masterclass 2nd Edition",
    "meta_description": "Learn Go 1.22 including generics and new stdlib",
    "seo_tags": ["golang generics", "go 1.22 ebook", "golang pdf 2026"]
  }'
Response 200:
{ "message": "product updated" }

---
SUBSCRIPTION

Create

curl -X POST http://localhost:8082/api/admin/products \
  -H "x-refresh-token: YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "subscription",
    "name": "Netflix Premium Account",
    "category": "Streaming",
    "short_description": "4K Netflix shared account",
    "description": "Full 4K access with 4 screens. Valid for 1 month from purchase date",

    "is_variants": false,
    "product_price": 19.99,
    "bargaining_price": 17.99,
    "available_qty": 5,
    "stock_alert": 1,
    "weight": null,
    "shipping_profile": null,

    "is_discount_offer": "inactive",
    "is_discount_type": "inactive",
    "discount_value": null,
    "discount_start_at": null,
    "discount_end_at": null,

    "publish_type": "immediately",
    "publish_at": null,

    "cover": "https://cdn.example.com/netflix-cover.jpg",
    "is_slider": false,
    "media_items": [
      { "media_type": "image", "source_url": "https://cdn.example.com/netflix-screenshot.jpg" }
    ],

    "variants": [],

    "subscriptions": [
      {
        "email": "account1@gmail.com",
        "number": "+60123456789",
        "username": "netflix_user_01",
        "password": "SecurePass@01"
      },
      {
        "email": "account2@gmail.com",
        "number": "+60198765432",
        "username": "netflix_user_02",
        "password": "SecurePass@02"
      }
    ],

    "tags": ["netflix", "streaming", "subscription"],
    "slug": "netflix-premium-account",
    "meta_title": "Netflix Premium 4K Account",
    "meta_description": "Buy a shared Netflix premium 4K account",
    "seo_tags": ["netflix account", "buy netflix", "netflix subscription"]
  }'
Response 201:
{ "id": 4, "message": "product created" }

---
Update

curl -X PUT http://localhost:8082/api/admin/products/4 \
  -H "x-refresh-token: YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "subscription",
    "name": "Netflix Premium Account — Restocked",
    "category": "Streaming",
    "short_description": "4K Netflix shared account — fresh batch",
    "description": "New accounts. Valid for 1 month from purchase date",

    "is_variants": false,
    "product_price": 19.99,
    "bargaining_price": 17.99,
    "available_qty": 10,
    "stock_alert": 2,
    "weight": null,
    "shipping_profile": null,

    "is_discount_offer": "limited",
    "is_discount_type": "fixed",
    "discount_value": 2,
    "discount_start_at": "2026-03-05T00:00:00Z",
    "discount_end_at": "2026-03-10T23:59:59Z",

    "publish_type": "immediately",
    "publish_at": null,

    "cover": "https://cdn.example.com/netflix-cover-v2.jpg",
    "is_slider": false,
    "media_items": [
      { "media_type": "image", "source_url": "https://cdn.example.com/netflix-screenshot-v2.jpg" }
    ],

    "variants": [],

    "subscriptions": [
      {
        "email": "fresh1@gmail.com",
        "number": "+60111111111",
        "username": "netflix_fresh_01",
        "password": "NewPass@01"
      },
      {
        "email": "fresh2@gmail.com",
        "number": "+60122222222",
        "username": "netflix_fresh_02",
        "password": "NewPass@02"
      },
      {
        "email": "fresh3@gmail.com",
        "number": "+60133333333",
        "username": "netflix_fresh_03",
        "password": "NewPass@03"
      }
    ],

    "tags": ["netflix", "streaming", "subscription"],
    "slug": "netflix-premium-account",
    "meta_title": "Netflix Premium 4K Account — Restocked",
    "meta_description": "Fresh Netflix accounts, valid 1 month",
    "seo_tags": ["netflix account", "buy netflix 2026"]
  }'
Response 200:
{ "message": "product updated" }

---
DELETE — Same for all types

# Delete product ID 1 (physical with variants)
curl -X DELETE http://localhost:8082/api/admin/products/1 \
  -H "x-refresh-token: YOUR_TOKEN"

# Delete product ID 2 (physical no variants)
curl -X DELETE http://localhost:8082/api/admin/products/2 \
  -H "x-refresh-token: YOUR_TOKEN"

# Delete product ID 3 (downloadable)
curl -X DELETE http://localhost:8082/api/admin/products/3 \
  -H "x-refresh-token: YOUR_TOKEN"

# Delete product ID 4 (subscription)
curl -X DELETE http://localhost:8082/api/admin/products/4 \
  -H "x-refresh-token: YOUR_TOKEN"
Response 200:
{ "message": "product deleted" }
Response 404 (wrong ID or not your product):
{ "error": "product not found" }

---
Quick reference

┌────────────────────────┬─────────────┬───────────────┬────────────┬───────────────────────────────────────┐
│          Type          │ is_variants │ product_price │   weight   │          Type-specific table          │
├────────────────────────┼─────────────┼───────────────┼────────────┼───────────────────────────────────────┤
│ physical + variants    │ true        │ null          │ null       │ product_variants (multiple rows)      │
├────────────────────────┼─────────────┼───────────────┼────────────┼───────────────────────────────────────┤
│ physical + no variants │ false       │ real value    │ real value │ —                                     │
├────────────────────────┼─────────────┼───────────────┼────────────┼───────────────────────────────────────┤
│ downloadable           │ false       │ real value    │ null       │ product_downloadable (multiple rows)  │
├────────────────────────┼─────────────┼───────────────┼────────────┼───────────────────────────────────────┤
│ subscription           │ false       │ real value    │ null       │ product_subscriptions (multiple rows) │
└────────────────────────┴─────────────┴───────────────┴────────────┴───────────────────────────────────────┘