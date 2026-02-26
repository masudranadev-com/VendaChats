Act like a senior full-stack JavaScript engineer specialized in Laravel + Blade (HTML/CSS/Vanilla JS) and reusable UI component patterns for admin panels.

Your goal is to replace my old “hardcoded data from controller” approach on this page:
http://localhost:8000/admin/bot-settings
with a fresh AJAX-driven flow that calls my backend API directly and renders the UI from the real response.

Task: Give me an exact, step-by-step implementation (Laravel route/controller cleanup + Blade HTML + vanilla JS) that:
- Removes hardcoded controller data
- Calls the API endpoint via AJAX
- Shows a reusable “Loading UI component”
- Updates the DOM based on the API response (connected vs disconnected)

Backend API to call (from browser):
curl http://localhost:8082/api/admin/facebook-auth \                                                                                                                                -H "x-refresh-token: YOUR_TOKEN"
If connected:
  {                                                                                                                                                                                         "page_id": "988224154376053",                                                                                                                                                 
    "page_name": "Advance Metafy",
    "is_message": true,
    "is_comment": true,
    "is_detect_emotion": false,
    "is_detect_interest": false,
    "is_suggest_product": false,
    "is_bergain": false,
    "is_detect_voice": false,
    "is_detect_image": false
  }

  If not connected:
  {
    "status": "disconnected",
    "msg": "please connect your facebook to enjoy this features"
  }

  If no record found:
  {
    "error": "not found"
  }
Requirements (non-negotiable):
1) Do NOT hardcode any feature flags in Blade or Controller (no dummy booleans). The UI must always reflect the API response.
2) Use vanilla JS AJAX (fetch recommended) to call the endpoint dynamically using page_id from the page (dataset/hidden input/url param).
3) JWT must be taken from sessionStorage at runtime and passed as x-refresh-token.
4) Build a reusable Loading component in plain HTML/CSS/JS (like a small module/function/class) so I can drop it into other pages.
5) Render UI conditionally:
   - If connected: show page name + switches/indicators for each boolean field (is_message, is_comment, etc.) populated from response.
   - If disconnected: show a “Connect Facebook” warning area using msg from response.
6) Include clean DOM update code (no frameworks) + minimal CSS.
7) Include robust error handling:
   - missing token
   - network error / timeout
   - non-200 responses
   - unexpected JSON shape
   - Read JWT from sessionStorage (or an equivalent session value available on the page) and send it in the AJAX request header.

Output format (must follow exactly):
1) Remove hardcoded controller data (what to delete, what controller should return now)
2) Blade HTML skeleton (container + placeholders + data-page-id strategy)
3) Reusable Loading Component (HTML/CSS + JS module/function)
4) AJAX fetch code (headers, token read, endpoint build, response parse)
5) Conditional DOM rendering (connected vs disconnected) with example UI for boolean flags
6) Error handling patterns + user-friendly messages

Constraints:
- Use only HTML, CSS, and Vanilla JS (no React, no Vue).
- Keep styling minimal, clean, and reusable.
- No extra features beyond what’s required above.
- Use clear variable names and copy-paste ready snippets.

Take a deep breath and work on this problem step-by-step.