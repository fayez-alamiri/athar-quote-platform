# Entity-Relationship Diagram (ERD) & Database Schema — QuoteIt

> **PDF Export Tip:** To generate the final PDF of this ERD for submission, open `docs/ERD_Printable.html` in your browser and press **`Ctrl + P`** $\rightarrow$ **Save as PDF**.

---

## 1. Mermaid Entity-Relationship Diagram

```mermaid
erDiagram
    USERS ||--o{ QUOTES : "publishes (user_id)"
    USERS ||--o{ QUOTE_LIKES : "likes (user_id)"
    USERS ||--o{ SAVED_QUOTES : "bookmarks (user_id)"
    QUOTES ||--o{ QUOTE_LIKES : "receives (quote_id)"
    QUOTES ||--o{ SAVED_QUOTES : "saved in (quote_id)"

    USERS {
        bigint id PK
        string name "2-50 chars"
        string email UK "unique"
        timestamp email_verified_at "nullable"
        string password "hashed"
        string avatar_path "nullable (public disk)"
        string locale "default 'ar' ('ar'|'en')"
        string remember_token "nullable"
        timestamp created_at
        timestamp updated_at
    }

    QUOTES {
        bigint id PK
        bigint user_id FK "references users(id) ON DELETE CASCADE"
        string type "default 'quick' ('quick'|'book')"
        text quote_text "3-155 chars (quick) | 3-1000 chars (book)"
        string book_title "nullable (required if type='book', max 150)"
        string book_cover_path "nullable (required if type='book', public disk)"
        string attributed_author "nullable (required if type='book', max 120)"
        timestamp created_at
        timestamp updated_at
    }

    QUOTE_LIKES {
        bigint id PK
        bigint user_id FK "references users(id) ON DELETE CASCADE"
        bigint quote_id FK "references quotes(id) ON DELETE CASCADE"
        timestamp created_at
        timestamp updated_at
    }

    SAVED_QUOTES {
        bigint id PK
        bigint user_id FK "references users(id) ON DELETE CASCADE"
        bigint quote_id FK "references quotes(id) ON DELETE CASCADE"
        timestamp created_at
        timestamp updated_at
    }
    2. Table Definitions, Constraints & Indexes2.1 users TableStores registered platform members, their profile avatar path, and their preferred interface language.Primary Key: idUnique Constraints: users_email_unique (email)Added Profile Columns (2026_10_27_000001_add_profile_fields_to_users_table.php):avatar_path (VARCHAR(255), nullable) — Relative path in storage/app/public/avatars.locale (VARCHAR(5), default 'ar') — Persisted language preference ('ar' or 'en').2.2 quotes Table (2026_10_27_000002_create_quotes_table.php)Unified table storing both Quick User Quotes (type = 'quick') and Book Quotes (type = 'book').Primary Key: idForeign Keys:user_id $\rightarrow$ users.id (ON DELETE CASCADE) — Identifies the platform publisher account.Domain Separation: user_id (the signed-in publisher who posted the quote) is strictly separate from attributed_author (the author/person to whom a book quote is attributed).Indexes:quotes_type_created_at_index (type, created_at)quotes_user_id_created_at_index (user_id, created_at)quotes_book_title_index (book_title)quotes_attributed_author_index (attributed_author)2.3 quote_likes Table (2026_10_27_000003_create_quote_likes_table.php)Stores user likes on quotes.Primary Key: idForeign Keys:user_id $\rightarrow$ users.id (ON DELETE CASCADE)quote_id $\rightarrow$ quotes.id (ON DELETE CASCADE)Unique Constraint: quote_likes_user_id_quote_id_unique (user_id, quote_id) — Guarantees a user can like a given quote at most once.Indexes: quote_likes_quote_id_index (quote_id)2.4 saved_quotes Table (2026_10_27_000004_create_saved_quotes_table.php)Stores user bookmarks for the private Saved Quotes (المحفوظات) collection.Primary Key: idForeign Keys:user_id $\rightarrow$ users.id (ON DELETE CASCADE)quote_id $\rightarrow$ quotes.id (ON DELETE CASCADE)Unique Constraint: saved_quotes_user_id_quote_id_unique (user_id, quote_id) — Guarantees a user cannot create duplicate saved records for the same quote.Indexes: saved_quotes_user_id_created_at_index (user_id, created_at)
---

#### 3. `docs/ERD_Printable.html` *(Create New File)*

*(Self-contained visual ERD & Schema sheet styled in the Warm Literary Editorial palette. Open in any browser and press `Ctrl + P` $\rightarrow$ **Save as PDF** to produce your final ERD PDF deliverable.)*

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QuoteIt — Final Entity-Relationship Diagram (ERD)</title>
    <style>
        :root {
            --bg-main: #F8F4EC;
            --bg-card: #FFFEFA;
            --bg-secondary: #EFE5D5;
            --cocoa: #4A3328;
            --espresso: #2B1D17;
            --gold: #B99A68;
            --muted: #827469;
            --border: #D2C2AC;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 32px;
            background: var(--bg-main);
            color: var(--espresso);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.5;
        }
        .sheet {
            max-width: 1120px;
            margin: 0 auto;
            background: var(--bg-card);
            border: 2px solid var(--border);
            border-radius: 16px;
            padding: 32px 36px;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--gold);
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        h1 {
            margin: 0;
            font-size: 26px;
            color: var(--cocoa);
        }
        .subtitle {
            margin: 4px 0 0;
            font-size: 13px;
            color: var(--muted);
        }
        .print-btn {
            background: var(--cocoa);
            color: var(--bg-card);
            border: none;
            padding: 9px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
        }
        .diagram-box {
            background: var(--bg-main);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 28px;
            overflow-x: auto;
        }
        svg {
            width: 100%;
            height: auto;
            display: block;
        }
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .entity-card {
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            background: var(--bg-card);
        }
        .entity-header {
            background: var(--cocoa);
            color: #FFFEFA;
            padding: 10px 14px;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }
        .entity-header span.badge {
            background: var(--gold);
            color: var(--espresso);
            padding: 1px 8px;
            border-radius: 6px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            padding: 7px 12px;
            text-align: left;
            border-bottom: 1px solid #EFE5D5;
        }
        th {
            background: var(--bg-secondary);
            color: var(--espresso);
            font-weight: 700;
        }
        .pk { color: #4A3328; font-weight: 700; }
        .fk { color: #8A5A2B; font-weight: 700; }
        .uk { color: #2D6A4F; font-weight: 700; }
        @media print {
            body { padding: 0; background: #FFFFFF; }
            .sheet { border: none; padding: 12px; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <header>
            <div>
                <h1>QuoteIt — Final Entity-Relationship Diagram (ERD)</h1>
                <p class="subtitle">Laravel 13 + SQLite Schema • Users, Unified Quotes (Quick &amp; Book), Likes, and Saved Quotes (المحفوظات)</p>
            </div>
            <button class="print-btn" onclick="window.print()">Print / Save as PDF (Ctrl+P)</button>
        </header>

        <!-- Visual SVG Entity Relationship Diagram -->
        <div class="diagram-box">
            <svg viewBox="0 0 1000 440" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="QuoteIt Entity Relationship Diagram">
                <!-- Relationship Connector Lines -->
                <!-- users (1) to quotes (0..N) -->
                <path d="M 290 120 L 670 120" stroke="#4A3328" stroke-width="2.5" fill="none"/>
                <text x="480" y="110" text-anchor="middle" font-size="12" font-weight="bold" fill="#4A3328">1 : N — publishes (user_id)</text>

                <!-- users (1) to quote_likes (0..N) -->
                <path d="M 160 235 L 160 320 L 310 320" stroke="#B99A68" stroke-width="2.5" fill="none"/>
                <text x="225" y="310" text-anchor="middle" font-size="11" font-weight="bold" fill="#4A3328">1 : N (user_id)</text>

                <!-- quotes (1) to quote_likes (0..N) -->
                <path d="M 760 250 L 760 320 L 580 320" stroke="#B99A68" stroke-width="2.5" fill="none"/>
                <text x="670" y="310" text-anchor="middle" font-size="11" font-weight="bold" fill="#4A3328">1 : N (quote_id)</text>

                <!-- users (1) to saved_quotes (0..N) -->
                <path d="M 110 235 L 110 400 L 310 400" stroke="#4A3328" stroke-width="2" stroke-dasharray="6,4" fill="none"/>
                <text x="205" y="392" text-anchor="middle" font-size="11" font-weight="bold" fill="#4A3328">1 : N (user_id)</text>

                <!-- quotes (1) to saved_quotes (0..N) -->
                <path d="M 830 250 L 830 400 L 580 400" stroke="#4A3328" stroke-width="2" stroke-dasharray="6,4" fill="none"/>
                <text x="710" y="392" text-anchor="middle" font-size="11" font-weight="bold" fill="#4A3328">1 : N (quote_id)</text>

                <!-- USERS Box -->
                <g transform="translate(30, 30)">
                    <rect width="260" height="205" rx="10" fill="#FFFEFA" stroke="#4A3328" stroke-width="2"/>
                    <rect width="260" height="34" rx="10" fill="#4A3328"/>
                    <text x="130" y="22" text-anchor="middle" fill="#FFFEFA" font-weight="bold" font-size="14">USERS</text>
                    <text x="14" y="56" font-size="12" fill="#2B1D17"><tspan font-weight="bold" fill="#4A3328">PK</tspan> id (bigint)</text>
                    <text x="14" y="76" font-size="12" fill="#2B1D17">name (varchar 50)</text>
                    <text x="14" y="96" font-size="12" fill="#2B1D17"><tspan font-weight="bold" fill="#2D6A4F">UK</tspan> email (varchar 255)</text>
                    <text x="14" y="116" font-size="12" fill="#2B1D17">password (varchar, hashed)</text>
                    <text x="14" y="136" font-size="12" fill="#2B1D17">avatar_path (varchar, nullable)</text>
                    <text x="14" y="156" font-size="12" fill="#2B1D17">locale (varchar 5, default 'ar')</text>
                    <text x="14" y="176" font-size="12" fill="#827469">created_at / updated_at</text>
                </g>

                <!-- QUOTES Box -->
                <g transform="translate(670, 30)">
                    <rect width="300" height="220" rx="10" fill="#FFFEFA" stroke="#4A3328" stroke-width="2"/>
                    <rect width="300" height="34" rx="10" fill="#4A3328"/>
                    <text x="150" y="22" text-anchor="middle" fill="#FFFEFA" font-weight="bold" font-size="14">QUOTES</text>
                    <text x="14" y="56" font-size="12" fill="#2B1D17"><tspan font-weight="bold" fill="#4A3328">PK</tspan> id (bigint)</text>
                    <text x="14" y="76" font-size="12" fill="#2B1D17"><tspan font-weight="bold" fill="#8A5A2B">FK</tspan> user_id → users.id (CASCADE)</text>
                    <text x="14" y="96" font-size="12" fill="#2B1D17">type ('quick' | 'book')</text>
                    <text x="14" y="116" font-size="12" fill="#2B1D17">quote_text (text)</text>
                    <text x="14" y="136" font-size="12" fill="#2B1D17">book_title (varchar 150, nullable)</text>
                    <text x="14" y="156" font-size="12" fill="#2B1D17">book_cover_path (varchar, nullable)</text>
                    <text x="14" y="176" font-size="12" fill="#2B1D17">attributed_author (varchar 120, nullable)</text>
                    <text x="14" y="198" font-size="12" fill="#827469">created_at / updated_at</text>
                </g>

                <!-- QUOTE_LIKES Box -->
                <g transform="translate(310, 275)">
                    <rect width="270" height="72" rx="10" fill="#FFFEFA" stroke="#B99A68" stroke-width="2"/>
                    <rect width="270" height="26" rx="8" fill="#B99A68"/>
                    <text x="135" y="18" text-anchor="middle" fill="#2B1D17" font-weight="bold" font-size="12">QUOTE_LIKES (Unique: user_id + quote_id)</text>
                    <text x="14" y="45" font-size="11.5" fill="#2B1D17"><tspan font-weight="bold">PK</tspan> id • <tspan font-weight="bold" fill="#8A5A2B">FK</tspan> user_id • <tspan font-weight="bold" fill="#8A5A2B">FK</tspan> quote_id</text>
                    <text x="14" y="62" font-size="11" fill="#827469">ON DELETE CASCADE • created_at / updated_at</text>
                </g>

                <!-- SAVED_QUOTES Box -->
                <g transform="translate(310, 360)">
                    <rect width="270" height="72" rx="10" fill="#FFFEFA" stroke="#4A3328" stroke-width="2"/>
                    <rect width="270" height="26" rx="8" fill="#4A3328"/>
                    <text x="135" y="18" text-anchor="middle" fill="#FFFEFA" font-weight="bold" font-size="12">SAVED_QUOTES (Unique: user_id + quote_id)</text>
                    <text x="14" y="45" font-size="11.5" fill="#2B1D17"><tspan font-weight="bold">PK</tspan> id • <tspan font-weight="bold" fill="#8A5A2B">FK</tspan> user_id • <tspan font-weight="bold" fill="#8A5A2B">FK</tspan> quote_id</text>
                    <text x="14" y="62" font-size="11" fill="#827469">ON DELETE CASCADE • created_at / updated_at</text>
                </g>
            </svg>
        </div>

        <!-- Detailed Relational Schema Tables -->
        <div class="tables-grid">
            <div class="entity-card">
                <div class="entity-header">
                    <span>1. users</span>
                    <span class="badge">Core Account Entity</span>
                </div>
                <table>
                    <thead>
                        <tr><th>Column</th><th>Type</th><th>Key / Constraint</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>id</td><td>INTEGER / BIGINT</td><td class="pk">PK, Auto-increment</td></tr>
                        <tr><td>name</td><td>VARCHAR(255)</td><td>NOT NULL (Validated 2–50 chars)</td></tr>
                        <tr><td>email</td><td>VARCHAR(255)</td><td class="uk">UNIQUE, NOT NULL</td></tr>
                        <tr><td>password</td><td>VARCHAR(255)</td><td>NOT NULL (Hashed, min 8 chars)</td></tr>
                        <tr><td>avatar_path</td><td>VARCHAR(255)</td><td>NULLABLE (storage/app/public/avatars)</td></tr>
                        <tr><td>locale</td><td>VARCHAR(5)</td><td>DEFAULT 'ar' ('ar' | 'en')</td></tr>
                        <tr><td>created_at / updated_at</td><td>TIMESTAMP</td><td>NULLABLE</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="entity-card">
                <div class="entity-header">
                    <span>2. quotes</span>
                    <span class="badge">Quick &amp; Book Quotes</span>
                </div>
                <table>
                    <thead>
                        <tr><th>Column</th><th>Type</th><th>Key / Constraint</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>id</td><td>INTEGER / BIGINT</td><td class="pk">PK, Auto-increment</td></tr>
                        <tr><td>user_id</td><td>INTEGER / BIGINT</td><td class="fk">FK → users.id (ON DELETE CASCADE)</td></tr>
                        <tr><td>type</td><td>VARCHAR(20)</td><td>DEFAULT 'quick' ('quick' | 'book')</td></tr>
                        <tr><td>quote_text</td><td>TEXT</td><td>NOT NULL (3–155 quick | 3–1000 book)</td></tr>
                        <tr><td>book_title</td><td>VARCHAR(150)</td><td>NULLABLE (Required when type='book')</td></tr>
                        <tr><td>book_cover_path</td><td>VARCHAR(255)</td><td>NULLABLE (Required when type='book')</td></tr>
                        <tr><td>attributed_author</td><td>VARCHAR(120)</td><td>NULLABLE (Required when type='book')</td></tr>
                        <tr><td>created_at / updated_at</td><td>TIMESTAMP</td><td>Indexed with type &amp; user_id</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="entity-card">
                <div class="entity-header">
                    <span>3. quote_likes</span>
                    <span class="badge">Pivot / 1-Per-User</span>
                </div>
                <table>
                    <thead>
                        <tr><th>Column</th><th>Type</th><th>Key / Constraint</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>id</td><td>INTEGER / BIGINT</td><td class="pk">PK, Auto-increment</td></tr>
                        <tr><td>user_id</td><td>INTEGER / BIGINT</td><td class="fk">FK → users.id (ON DELETE CASCADE)</td></tr>
                        <tr><td>quote_id</td><td>INTEGER / BIGINT</td><td class="fk">FK → quotes.id (ON DELETE CASCADE)</td></tr>
                        <tr><td>(user_id, quote_id)</td><td>COMPOSITE INDEX</td><td class="uk">UNIQUE CONSTRAINT</td></tr>
                        <tr><td>created_at / updated_at</td><td>TIMESTAMP</td><td>NULLABLE</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="entity-card">
                <div class="entity-header">
                    <span>4. saved_quotes</span>
                    <span class="badge">Saved Quotes (المحفوظات)</span>
                </div>
                <table>
                    <thead>
                        <tr><th>Column</th><th>Type</th><th>Key / Constraint</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>id</td><td>INTEGER / BIGINT</td><td class="pk">PK, Auto-increment</td></tr>
                        <tr><td>user_id</td><td>INTEGER / BIGINT</td><td class="fk">FK → users.id (ON DELETE CASCADE)</td></tr>
                        <tr><td>quote_id</td><td>INTEGER / BIGINT</td><td class="fk">FK → quotes.id (ON DELETE CASCADE)</td></tr>
                        <tr><td>(user_id, quote_id)</td><td>COMPOSITE INDEX</td><td class="uk">UNIQUE CONSTRAINT</td></tr>
                        <tr><td>created_at / updated_at</td><td>TIMESTAMP</td><td>Indexed (user_id, created_at)</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>