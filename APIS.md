From the migrations and .env, the seeded user is admin@domain.com / 00000000 and the service runs on port 8082.

  ---
  Health check

  curl -s http://localhost:8082/health

  ---
  1. Login — get your token

  curl -s -X POST http://localhost:8082/api/login \
    -H "Content-Type: application/json" \
    -d '{"email":"admin@domain.com","password":"00000000"}'
  Response:
  {
    "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_at": "2026-02-26T10:00:00Z"
  }
  Copy the refresh_token — use it in every request below as TOKEN.

  ---
  2. Wrong credentials — verify 401

  curl -s -X POST http://localhost:8082/api/login \
    -H "Content-Type: application/json" \
    -d '{"email":"admin@domain.com","password":"wrongpass"}'

  ---
  3. Create facebook page auth

  curl -s -X POST http://localhost:8082/api/admin/facebook-auth \
    -H "Content-Type: application/json" \
    -H "x-refresh-token: TOKEN" \
    -d '{
      "page_name": "My Business Page",
      "page_id": "123456789",
      "page_access_token": "EAAexample_token"
    }'

  ---
  4. List all pages for the logged-in user

  curl -s -X GET http://localhost:8082/api/admin/facebook-auth \
    -H "x-refresh-token: TOKEN"

  ---
  5. Get one page by page_id

  curl -s -X GET http://localhost:8082/api/admin/facebook-auth/123456789 \
    -H "x-refresh-token: TOKEN"

  ---
  6. Update a page

  curl -s -X PUT http://localhost:8082/api/admin/facebook-auth/123456789 \
    -H "Content-Type: application/json" \
    -H "x-refresh-token: TOKEN" \
    -d '{
      "page_name": "Updated Page Name",
      "page_access_token": "EAAupdated_token"
    }'

  ---
  7. Delete a page

  curl -s -X DELETE http://localhost:8082/api/admin/facebook-auth/123456789 \
    -H "x-refresh-token: TOKEN"

  ---
  8. Request without token — verify 401

  curl -s -X GET http://localhost:8082/api/admin/facebook-auth

  ---
  9. Logout — revokes token immediately

  curl -s -X POST http://localhost:8082/api/logout \
    -H "x-refresh-token: TOKEN"

  ---
  10. Use revoked token after logout — verify 401

  curl -s -X GET http://localhost:8082/api/admin/facebook-auth \
    -H "x-refresh-token: TOKEN"