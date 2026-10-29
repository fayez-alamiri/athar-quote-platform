# Software Requirements Specification (SRS) — QuoteIt Literary Platform

> **Brand Configuration Note:** `QuoteIt` is used as the current technical placeholder name. The displayed Arabic and English brand names, wordmark subtitles, and taglines are centrally configured in `config/brand.php` so they can be updated in one place without modifying views or templates.

---

## 1. Introduction & Product Purpose

**QuoteIt** is a bilingual (Arabic/English) literary quote platform built with Laravel 13, Blade, Tailwind CSS 4, Vite, and SQLite. Designed around the **Warm Literary Editorial** visual identity, the platform provides a calm reading and curation sanctuary where visitors and signed-in readers can browse, search, copy, and share literary passages as high-resolution PNG cards.

### 1.1 Supported Content Types
1. **Quick User Quote (`quick`):** A concise quote or literary reflection (`3–155` characters) published by an authenticated user. The signed-in user's account name is displayed as the platform publisher.
2. **Book Quote (`book`):** A literary passage from a book published by any authenticated user. Requires four mandatory fields:
   * **Book Title** (`1–150` characters)
   * **Book Cover Image** (uploaded `JPG`, `PNG`, or `WEBP` image, max `4 MB`)
   * **Quote Text** (`3–1000` characters)
   * **Attributed Quote Author** (`2–120` characters — kept distinct from the platform publisher account)

---

## 2. User Roles & Permissions Matrix

| Feature / Action | Guest (Unauthenticated Visitor) | Authenticated User |
|---|---|---|
| Browse public feed (Quick Quotes & Book Quotes) | Allowed | Allowed |
| Filter by type (**All**, **User Quotes**, **Book Quotes**) | Allowed | Allowed |
| Search across quote text, book title, attributed author, and publisher | Allowed | Allowed |
| Sort feed (**Newest First**, **Most Liked**) | Allowed | Allowed |
| Copy quote text to clipboard | Allowed | Allowed |
| Preview & download/share multi-template PNG quote cards | Allowed | Allowed |
| Switch interface language (`AR / EN`) & direction (`RTL / LTR`) | Allowed (Session + Cookie) | Allowed (Session + Cookie + `users.locale` DB column) |
| Toggle Light / Dark theme (Sun / Moon icon) | Allowed (`localStorage`) | Allowed (`localStorage`) |
| Like or unlike a quote | Sign-in prompt modal shown | Allowed (1 like per user/quote pair enforced in DB) |
| Save or unsave a quote (`المحفوظات`) | Sign-in prompt modal shown | Allowed for any quote (own, others', or book quotes) |
| View private **Saved Quotes (`المحفوظات`)** page | Sign-in required | Allowed (shows only current user's saved quotes) |
| Publish a Quick Quote or Book Quote | Sign-in required | Allowed |
| View **My Quotes (`اقتباساتي`)** management page | Sign-in required | Allowed (shows only current user's published posts) |
| Edit or delete an individual post | Not allowed | Post owner (`user_id`) only (`QuotePolicy`) |
| **Delete All My Posts (`حذف جميع منشوراتي`)** | Not allowed | Post owner only, with explicit confirmation modal |
| Manage profile avatar & view account statistics (`حسابي`) | Sign-in required | Own account only |

---

## 3. Functional Requirements (FR)

### FR-01: Authentication & Account Management
* **FR-01.1 (Registration):** Users register with a Full Name (`2–50` characters), a unique valid Email Address, and a Password of at least `8` characters (with confirmation). Passwords are stored using Laravel's secure bcrypt/argon hashing.
* **FR-01.2 (Sign In & Rate Limiting):** Users authenticate with email and password. Failed sign-in attempts are rate-limited (5 attempts per email/IP key) to prevent brute-force attacks.
* **FR-01.3 (Log Out):** Authenticated users can log out via a CSRF-protected `POST /logout` control available both in the main navigation bar and inside the avatar account dropdown menu.

### FR-02: Localization (`AR / EN`) & Theme Switching
* **FR-02.1 (Bilingual Interface):** Arabic (`ar`, `dir="rtl"`) is the default language; English (`en`, `dir="ltr"`) is selectable via the compact `AR / EN` switch in the navigation bar.
* **FR-02.2 (Content Preservation):** Switching interface language updates all UI labels, validation messages, dialogs, and layout directions while preserving quote text, book titles, and author names in their original language.
* **FR-02.3 (Theme Toggle):** A compact icon-only switch displays a **Sun icon** when Light Mode is active and a **Moon icon** when Dark Mode is active, persisting the user's choice in `localStorage`.

### FR-03: Public Feed, Search, Filtering, and Sorting
* **FR-03.1 (Feed & Pagination):** The homepage displays both quick quotes and book quotes in a responsive grid (9 quotes per page) with eager-loaded publisher data, like counts, and user state.
* **FR-03.2 (Filtering & Search):** Visitors can filter by **All Quotes**, **User Quotes (`quick`)**, or **Book Quotes (`book`)**, sort by **Newest First** or **Most Liked**, and search across `quote_text`, `book_title`, `attributed_author`, and publisher `users.name`.

### FR-04: Publishing Quick Quotes & Book Quotes
* **FR-04.1 (Quick Quotes):** Authenticated users submit `quote_text` (`3–155` characters). No book metadata or cover is required.
* **FR-04.2 (Book Quotes):** Authenticated users submit `book_title`, `book_cover` (`JPG`, `PNG`, or `WEBP`, max `4 MB`), `quote_text` (`3–1000` characters), and `attributed_author` (`2–120` characters). Missing or invalid fields are rejected by server validation.

### FR-05: Likes & Saved Quotes (`المحفوظات`)
* **FR-05.1 (Likes):** Authenticated users can like a quote once and unlike it later without page reload or scroll jump. Database uniqueness is enforced on `(user_id, quote_id)`.
* **FR-05.2 (Saved Quotes):** Authenticated users can save any quote to their private **Saved Quotes (`المحفوظات`)** collection and remove it at any time. Saving creates a `saved_quotes` relationship without duplicating the quote or changing its owner.
* **FR-05.3 (Guest Prompt):** Guests clicking Like, Save, Saved Quotes, or Add Quote are shown a localized modal prompting them to Sign In or Register.

### FR-06: PNG Share Card Generator
* **FR-06.1 (Multi-Template Preview & Export):** Clicking **Share (`مشاركة`)** on any quote card opens the PNG Share Modal (available to guests and signed-in users) with a `1080 × 1080` px HTML5 Canvas preview and 4 selectable templates:
  1. **Warm Editorial (`editorial`)** — Core brand ivory, paper, cocoa brown, and muted gold.
  2. **Quiet Paper (`paper`)** — Minimalist archival double-bordered book page.
  3. **Dark Elegant (`dark`)** — Espresso night (`#211A16` / `#2C231D`) with gold accents (`#C9A66B`).
  4. **Literary Magazine (`magazine`)** — Cocoa masthead header with gold rule and colophon footer.
* **FR-06.2 (Export & Fallbacks):** Supports high-resolution **Download PNG**, **Share Image via Device** (Web Share API file sharing where supported by the browser/OS), and **Copy Quote Text** fallback.

### FR-07: My Quotes Management & Bulk Deletion
* **FR-07.1 (My Quotes):** Displays all quick and book quotes published by the signed-in user with type tabs and search.
* **FR-07.2 (Authorized Edit & Single Delete):** Post owners can edit their quotes (validated by post type) or delete an individual post after confirming in a modal dialog.
* **FR-07.3 (Delete All My Posts):** Post owners can delete all of their own posts across both types after confirming in a destructive-action modal that states the exact number of posts to be removed. Associated book cover files and dependent likes/bookmarks are cleaned up automatically.

### FR-08: My Account, Avatar Upload & Real Statistics
* **FR-08.1 (Avatar Management):** Signed-in users can upload/replace their profile avatar (`JPG`, `PNG`, `WEBP`, max `2 MB`) with live preview or remove it to revert to the styled initials avatar.
* **FR-08.2 (Live Statistics):** Displays real database counts for total published quotes (with quick vs. book breakdown), total likes received across the user's published posts, and total saved quotes.

---

## 4. Non-Functional Requirements (NFR)

* **NFR-01 (Performance):** Core quote pages load in under 3 seconds in local/test environments using eager loading (`with`, `withCount`, `withExists`) and indexed columns to avoid N+1 query overhead.
* **NFR-02 (Security):** Server-side `FormRequest` validation, `QuotePolicy` authorization, CSRF protection on all POST/PUT/DELETE routes, password hashing, and MIME/size verification on uploaded images.
* **NFR-03 (Visual Identity & Accessibility):** Adheres to the **Warm Literary Editorial** light (`#F8F4EC`, `#FFFEFA`, `#EFE5D5`, `#4A3328`, `#2B1D17`, `#B99A68`, `#827469`) and dark (`#211A16`, `#2C231D`, `#F2E8DA`, `#C3B4A0`, `#C9A66B`) palettes, responsive mobile/tablet/desktop layouts, semantic HTML, keyboard navigation, and `prefers-reduced-motion`.
* **NFR-04 (Data Preservation):** Uses additive migrations on SQLite (`database/database.sqlite`) and runs automated tests against an isolated `:memory:` SQLite connection.