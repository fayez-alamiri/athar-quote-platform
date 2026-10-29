# QuoteIt — Bilingual Literary Quote Platform (منصة الاقتباسات الأدبية)

> **Branding Note:** `QuoteIt` is the temporary technical placeholder name. All displayed Arabic and English brand names, wordmark subtitles, and taglines are centrally configured in `config/brand.php` so they can be updated in one place without editing individual views or templates.

QuoteIt is a bilingual (**Arabic `RTL` & English `LTR`**) literary quote platform built with **Laravel 13**, **Blade**, **Tailwind CSS 4**, **Vite**, and **SQLite**. It provides a calm, editorial reading environment (**Warm Literary Editorial**) where visitors can browse, search, filter, copy quotes, and generate high-resolution (`1080 × 1080` PNG) share cards across four visual templates, while authenticated members can publish **Quick Quotes** and **Book Quotes**, like quotes, curate a private **Saved Quotes (`المحفوظات`)** collection, and manage their posts and profile avatars.

---

## Key Features

- **Bilingual Interface (`AR / EN`) & Direction Switching (`RTL / LTR`):**
  - Defaults to Arabic (`dir="rtl"`), with instant switching to English (`dir="ltr"`) via the navbar `AR / EN` toggle.
  - Preserves original quote text, book titles, and author names without auto-translating user content.
- **Persistent Light & Dark Editorial Themes:**
  - **Light Mode:** Warm ivory (`#F8F4EC`), paper white (`#FFFEFA`), sandy beige (`#EFE5D5`), cocoa brown (`#4A3328`), espresso (`#2B1D17`), and muted gold (`#B99A68`).
  - **Dark Mode:** Espresso night (`#211A16`), dark card (`#2C231D`), warm parchment text (`#F2E8DA`), and gold accent (`#C9A66B`).
  - Controlled via an icon-only Sun/Moon toggle in the navbar and persisted in `localStorage` (`quoteit_theme`) with zero-flicker initialization.
- **Two Content Types:**
  - **Quick User Quotes (`quick`):** Concise reflections or poetry (`3–155` characters) with script-aware quotation marks (`«...»` in Arabic, `“...”` in English) and optional `— Author` attribution directly underneath.
  - **Quotes from Books (`book`):** Collaborative book passages requiring four mandatory fields: **Book Title**, **Uploaded Book Cover Image**, **Quote Text**, and **Attributed Quote Author** (kept distinct from the platform publisher).
- **Search, Filtering & Sorting:**
  - Real-time database search across quote text, book title, attributed author, and platform publisher name.
  - Filter tabs with live counts (**All Quotes**, **User Quotes**, **Book Quotes**) and sorting (**Newest First**, **Most Liked**).
- **Likes & Private Saved Quotes (`المحفوظات`):**
  - Scroll-stable AJAX Like/Unlike and Save/Unsave actions backed by database uniqueness constraints on `(user_id, quote_id)`.
  - Guests clicking protected actions receive a localized **Sign in required** modal prompt.
- **High-Resolution PNG Share Card Generator:**
  - Available to both guests and signed-in users.
  - Four selectable templates: **Warm Editorial**, **Quiet Paper**, **Dark Elegant**, and **Literary Magazine**.
  - Supports **Download PNG** (`1080 × 1080` px), **Share Image via Device** (Web Share API when supported), and **Copy Quote Text** fallback.
- **My Quotes (`اقتباساتي`) & My Account (`حسابي`):**
  - Owner-scoped post management with type tabs, authorized editing, confirmed single-post deletion, and confirmed **Delete All My Posts** bulk deletion.
  - Profile avatar upload (`JPG`, `PNG`, `WEBP` up to `2 MB`), live preview, safe removal, styled initials fallback, and real database statistics (published quotes and total received likes).

---

## Technology Stack

| Layer | Technology |
|---|---|
| **Backend Framework** | PHP `^8.3`, Laravel `^13.17` |
| **Database** | SQLite (`database/database.sqlite` in development; `:memory:` in PHPUnit tests) |
| **Views & Templating** | Laravel Blade (`@extends`, `@yield`, reusable `<x-*>` components) |
| **Styling & Typography** | Tailwind CSS `^4.0.0`, CSS Custom Properties, Google Fonts (`Tajawal`, `Lora`, `Inter`) |
| **Frontend Build & Interactivity** | Vite `^8.0.0`, Vanilla ES Modules (`resources/js/app.js`, `resources/js/share-generator.js`), HTML5 Canvas API |
| **Testing** | PHPUnit `^12.5.12` (Feature & Regression test suites) |

---

## Prerequisites (Windows Local Development)

Ensure the following tools and PHP extensions are installed and available in **Windows PowerShell**:

- **PHP 8.3+** with the following extensions enabled in `php.ini`:
  - `pdo_sqlite` and `sqlite3` (required for SQLite database & tests)
  - `gd` and `fileinfo` (required for image validation and book cover / avatar uploads)
  - `mbstring`, `openssl`, `ctype`, `json`, `tokenizer`, `xml`
- **Composer 2.x**
- **Node.js 20+** and **npm 10+**

Check your environment in PowerShell:

```powershell
php -v
php -m | Select-String "sqlite|gd|fileinfo|mbstring"
composer --version
node -v
npm -v