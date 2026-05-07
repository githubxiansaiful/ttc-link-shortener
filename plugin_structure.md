# TTC Link Shortener — WordPress Plugin Structure

A complete build specification for a custom WordPress URL shortener plugin. Use this document as the source of truth when generating the plugin with Claude Code (VS Code CLI).

---

## 1. Plugin Overview

| Item | Value |
|---|---|
| **Plugin Name** | TTC Link Shortener |
| **Plugin Slug** | `ttc-link-shortener` |
| **Text Domain** | `ttc-link-shortener` |
| **Main File** | `ttc-link-shortener.php` |
| **Min WP Version** | 6.0 |
| **Min PHP Version** | 7.4 |
| **License** | GPL-2.0+ |
| **Brand Color** | `#D94C14` |
| **Font** | Inter (loaded from Google Fonts) |
| **Database** | Custom table (not WP options/posts) |

### What it does

1. Adds a custom WordPress role `short_manager`.
2. On activation, creates a custom DB table and a page titled **Link ShortURL** containing the dashboard shortcode.
3. The dashboard renders a sidebar + top-nav layout where logged-in `short_manager` users can:
   - See total links and total clicks.
   - Create new short links (6-character random slug).
   - View recent links and a full list with copy / delete / stats.
4. Hides the WP admin top bar (frontend) for `short_manager` users.
5. When any visitor opens `example.com/{slug}`, they are 301-redirected to the destination URL and a click is recorded.

---

## 2. Folder & File Structure

```
ttc-link-shortener/
├── ttc-link-shortener.php          # Main plugin bootstrap (header + loader)
├── readme.txt                      # WP readme (optional but recommended)
├── uninstall.php                   # Cleanup on uninstall
│
├── includes/                       # PHP classes (one class per file)
│   ├── class-ttcls-plugin.php      # Main singleton; bootstraps everything
│   ├── class-ttcls-activator.php   # Activation: DB table + role + page creation
│   ├── class-ttcls-deactivator.php # Deactivation: remove role (keep data)
│   ├── class-ttcls-db.php          # All DB queries (CRUD on links + clicks)
│   ├── class-ttcls-roles.php       # Custom role + capabilities
│   ├── class-ttcls-rewrite.php     # Rewrite rule + redirect handler
│   ├── class-ttcls-shortcode.php   # [ttc_link_shortener] shortcode renderer
│   ├── class-ttcls-ajax.php        # AJAX endpoints (create/delete/list)
│   ├── class-ttcls-assets.php      # Enqueue CSS/JS conditionally
│   ├── class-ttcls-admin-bar.php   # Hide WP top bar for short_manager
│   └── class-ttcls-helpers.php     # Slug generator, URL validation, sanitizers
│
├── templates/                      # Frontend dashboard templates
│   ├── dashboard-layout.php        # Wrapper: sidebar + topbar + content slot
│   ├── view-dashboard.php          # Stats cards + create form + recent links
│   ├── view-all-links.php          # Full table of links
│   ├── partial-sidebar.php         # Left navigation sidebar
│   ├── partial-topbar.php          # Top navigation bar
│   └── view-login.php              # Custom login prompt (if not logged in)
│
├── assets/
│   ├── css/
│   │   └── dashboard.css           # All dashboard styles (uses brand color + Inter)
│   ├── js/
│   │   └── dashboard.js            # AJAX shorten/delete, copy-to-clipboard, theme toggle
│   └── images/
│       └── logo.svg                # Optional brand logo
│
└── languages/
    └── ttc-link-shortener.pot      # Translation template
```

---

## 3. Main Plugin File — `ttc-link-shortener.php`

```php
<?php
/**
 * Plugin Name:       TTC Link Shortener
 * Plugin URI:        https://example.com/
 * Description:       Custom URL shortener with a branded dashboard for the short_manager role.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Your Name
 * License:           GPL-2.0+
 * Text Domain:       ttc-link-shortener
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Constants
define( 'TTCLS_VERSION',   '1.0.0' );
define( 'TTCLS_FILE',      __FILE__ );
define( 'TTCLS_PATH',      plugin_dir_path( __FILE__ ) );
define( 'TTCLS_URL',       plugin_dir_url( __FILE__ ) );
define( 'TTCLS_BASENAME',  plugin_basename( __FILE__ ) );
define( 'TTCLS_TABLE',     'ttcls_links' );        // wpdb prefix added later
define( 'TTCLS_PAGE_SLUG', 'link-shorturl' );      // The auto-created page slug
define( 'TTCLS_ROLE',      'short_manager' );

// Autoload includes
require_once TTCLS_PATH . 'includes/class-ttcls-helpers.php';
require_once TTCLS_PATH . 'includes/class-ttcls-db.php';
require_once TTCLS_PATH . 'includes/class-ttcls-roles.php';
require_once TTCLS_PATH . 'includes/class-ttcls-activator.php';
require_once TTCLS_PATH . 'includes/class-ttcls-deactivator.php';
require_once TTCLS_PATH . 'includes/class-ttcls-rewrite.php';
require_once TTCLS_PATH . 'includes/class-ttcls-shortcode.php';
require_once TTCLS_PATH . 'includes/class-ttcls-ajax.php';
require_once TTCLS_PATH . 'includes/class-ttcls-assets.php';
require_once TTCLS_PATH . 'includes/class-ttcls-admin-bar.php';
require_once TTCLS_PATH . 'includes/class-ttcls-plugin.php';

// Activation / Deactivation
register_activation_hook(   __FILE__, [ 'TTCLS_Activator',   'activate'   ] );
register_deactivation_hook( __FILE__, [ 'TTCLS_Deactivator', 'deactivate' ] );

// Boot
add_action( 'plugins_loaded', function () {
    TTCLS_Plugin::instance();
} );
```

---

## 4. Database Schema

### Table: `{prefix}ttcls_links`

| Column | Type | Notes |
|---|---|---|
| `id` | BIGINT(20) UNSIGNED AUTO_INCREMENT | Primary key |
| `slug` | VARCHAR(10) NOT NULL | 6-char random, **UNIQUE** |
| `destination_url` | TEXT NOT NULL | Original URL (validated) |
| `clicks` | BIGINT(20) UNSIGNED DEFAULT 0 | Total click counter |
| `created_by` | BIGINT(20) UNSIGNED DEFAULT 0 | WP user ID |
| `created_at` | DATETIME NOT NULL | UTC |
| `last_clicked_at` | DATETIME NULL | Updated on each redirect |
| `status` | TINYINT(1) DEFAULT 1 | 1 = active, 0 = disabled |

**Indexes:** `UNIQUE KEY slug (slug)`, `KEY created_by (created_by)`, `KEY status (status)`.

### `class-ttcls-db.php` — schema creation snippet

```php
public static function create_table() {
    global $wpdb;
    $table   = $wpdb->prefix . TTCLS_TABLE;
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        slug VARCHAR(10) NOT NULL,
        destination_url TEXT NOT NULL,
        clicks BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
        created_by BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        last_clicked_at DATETIME NULL,
        status TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY  (id),
        UNIQUE KEY slug (slug),
        KEY created_by (created_by),
        KEY status (status)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
```

### Required DB methods

```php
TTCLS_DB::insert_link( $slug, $url, $user_id )      // returns insert ID
TTCLS_DB::get_by_slug( $slug )                      // returns row or null
TTCLS_DB::increment_click( $slug )                  // updates clicks + last_clicked_at
TTCLS_DB::get_recent( $limit = 5, $user_id = null ) // for dashboard
TTCLS_DB::get_all( $args = [] )                     // paginated for All Links
TTCLS_DB::delete_link( $id, $user_id )              // ownership check
TTCLS_DB::total_links( $user_id = null )            // count
TTCLS_DB::total_clicks( $user_id = null )           // SUM(clicks)
TTCLS_DB::slug_exists( $slug )                      // collision check
```

All queries use `$wpdb->prepare()`. No raw interpolation.

---

## 5. Activation Flow — `class-ttcls-activator.php`

On `register_activation_hook`:

1. **Create DB table** via `TTCLS_DB::create_table()`.
2. **Add role** `short_manager` via `TTCLS_Roles::add_role()` with capabilities:
   - `read` (so they can log in to frontend)
   - `ttcls_manage_links` (custom cap, granted to administrator + short_manager)
3. **Create the dashboard page** (only if it doesn't already exist):
   ```php
   $existing = get_page_by_path( TTCLS_PAGE_SLUG );
   if ( ! $existing ) {
       wp_insert_post( [
           'post_title'   => 'Link ShortURL',
           'post_name'    => TTCLS_PAGE_SLUG,
           'post_content' => '[ttc_link_shortener]',
           'post_status'  => 'publish',
           'post_type'    => 'page',
           'post_author'  => get_current_user_id(),
       ] );
   }
   ```
4. **Flush rewrite rules** so the `/{slug}` redirect rule is registered.

---

## 6. Custom Role — `class-ttcls-roles.php`

```php
public static function add_role() {
    add_role( TTCLS_ROLE, __( 'Short Manager', 'ttc-link-shortener' ), [
        'read'               => true,
        'ttcls_manage_links' => true,
    ] );
    // Also give admins the cap so they can manage too
    $admin = get_role( 'administrator' );
    if ( $admin ) { $admin->add_cap( 'ttcls_manage_links' ); }
}

public static function remove_role() {
    remove_role( TTCLS_ROLE );
    $admin = get_role( 'administrator' );
    if ( $admin ) { $admin->remove_cap( 'ttcls_manage_links' ); }
}
```

---

## 7. Hide WP Admin Bar — `class-ttcls-admin-bar.php`

```php
add_action( 'after_setup_theme', function () {
    if ( is_user_logged_in() ) {
        $user = wp_get_current_user();
        if ( in_array( TTCLS_ROLE, (array) $user->roles, true ) ) {
            show_admin_bar( false );
        }
    }
} );

// Also block wp-admin access for short_manager — redirect them to the dashboard page
add_action( 'admin_init', function () {
    if ( ! current_user_can( 'manage_options' )
         && in_array( TTCLS_ROLE, (array) wp_get_current_user()->roles, true )
         && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
        wp_safe_redirect( home_url( '/' . TTCLS_PAGE_SLUG . '/' ) );
        exit;
    }
} );
```

---

## 8. Rewrite & Redirect — `class-ttcls-rewrite.php`

The plugin must intercept `example.com/{slug}` (6-char alphanumeric) **without** colliding with real WP pages.

### Strategy

Use `template_redirect` to inspect the request. This avoids messing with WP's page resolution and only triggers when WP would otherwise 404.

```php
add_action( 'template_redirect', function () {
    if ( is_admin() || is_user_logged_in() && is_page() ) return;

    // Get path: e.g. "abcdef"
    $path = trim( wp_parse_url( add_query_arg( [] ), PHP_URL_PATH ), '/' );
    if ( ! preg_match( '/^[A-Za-z0-9]{6}$/', $path ) ) return;

    // Don't shadow existing pages
    if ( get_page_by_path( $path ) ) return;

    $row = TTCLS_DB::get_by_slug( $path );
    if ( ! $row || (int) $row->status !== 1 ) return;

    TTCLS_DB::increment_click( $path );
    wp_redirect( esc_url_raw( $row->destination_url ), 301 );
    exit;
} );
```

> **Note:** Using `template_redirect` is simpler and safer than registering a custom rewrite rule because the slug pattern (6 alphanumerics) could collide with future page slugs. The check `get_page_by_path()` ensures real pages always win.

---

## 9. Slug Generation — `class-ttcls-helpers.php`

```php
public static function generate_slug( $length = 6 ) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $max   = strlen( $chars ) - 1;
    do {
        $slug = '';
        for ( $i = 0; $i < $length; $i++ ) {
            $slug .= $chars[ random_int( 0, $max ) ];
        }
    } while ( TTCLS_DB::slug_exists( $slug ) );
    return $slug;
}

public static function validate_url( $url ) {
    $url = esc_url_raw( trim( $url ) );
    if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) return false;
    $scheme = wp_parse_url( $url, PHP_URL_SCHEME );
    if ( ! in_array( $scheme, [ 'http', 'https' ], true ) ) return false;
    return $url;
}
```

Use `random_int` (cryptographically secure), not `rand()`.

---

## 10. Shortcode — `class-ttcls-shortcode.php`

```php
add_shortcode( 'ttc_link_shortener', [ __CLASS__, 'render' ] );

public static function render( $atts ) {
    if ( ! is_user_logged_in() ) {
        ob_start();
        include TTCLS_PATH . 'templates/view-login.php';
        return ob_get_clean();
    }

    if ( ! current_user_can( 'ttcls_manage_links' ) ) {
        return '<p>' . esc_html__( 'You do not have permission to access this page.', 'ttc-link-shortener' ) . '</p>';
    }

    // Determine which view based on ?view= query
    $view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'dashboard';
    $view = in_array( $view, [ 'dashboard', 'all-links' ], true ) ? $view : 'dashboard';

    ob_start();
    include TTCLS_PATH . 'templates/dashboard-layout.php';
    return ob_get_clean();
}
```

`dashboard-layout.php` includes the sidebar, topbar, and the matching `view-*.php`.

---

## 11. AJAX Endpoints — `class-ttcls-ajax.php`

All endpoints use a single nonce: `ttcls_nonce`. Capability check on every call.

| Action | Method | Purpose |
|---|---|---|
| `wp_ajax_ttcls_create` | POST | Create a new short link. Returns `{ slug, short_url, destination, clicks, created_at }`. |
| `wp_ajax_ttcls_delete` | POST | Delete by ID (ownership-checked). |
| `wp_ajax_ttcls_list` | POST | Paginated list of links for "All Links" view. |

Skeleton:

```php
add_action( 'wp_ajax_ttcls_create', [ __CLASS__, 'create' ] );

public static function create() {
    check_ajax_referer( 'ttcls_nonce', 'nonce' );
    if ( ! current_user_can( 'ttcls_manage_links' ) ) {
        wp_send_json_error( [ 'message' => 'Forbidden' ], 403 );
    }
    $url = TTCLS_Helpers::validate_url( $_POST['url'] ?? '' );
    if ( ! $url ) {
        wp_send_json_error( [ 'message' => 'Invalid URL' ], 400 );
    }
    $slug = TTCLS_Helpers::generate_slug();
    $id   = TTCLS_DB::insert_link( $slug, $url, get_current_user_id() );
    if ( ! $id ) {
        wp_send_json_error( [ 'message' => 'DB error' ], 500 );
    }
    wp_send_json_success( [
        'slug'        => $slug,
        'short_url'   => home_url( '/' . $slug ),
        'destination' => $url,
        'clicks'      => 0,
        'created_at'  => current_time( 'mysql' ),
    ] );
}
```

Note: AJAX is `admin-ajax.php`, available on the frontend too — only the `wp_ajax_` prefix is needed because `short_manager` is logged in.

---

## 12. Asset Loading — `class-ttcls-assets.php`

Only enqueue on the dashboard page. Do not pollute every page.

```php
add_action( 'wp_enqueue_scripts', function () {
    global $post;
    if ( ! is_a( $post, 'WP_Post' ) ) return;
    if ( ! has_shortcode( $post->post_content, 'ttc_link_shortener' ) ) return;

    // Inter font
    wp_enqueue_style(
        'ttcls-inter',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
        [], null
    );

    wp_enqueue_style(
        'ttcls-dashboard',
        TTCLS_URL . 'assets/css/dashboard.css',
        [ 'ttcls-inter' ],
        TTCLS_VERSION
    );

    wp_enqueue_script(
        'ttcls-dashboard',
        TTCLS_URL . 'assets/js/dashboard.js',
        [],
        TTCLS_VERSION,
        true
    );

    wp_localize_script( 'ttcls-dashboard', 'TTCLS', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'ttcls_nonce' ),
        'home_url' => home_url( '/' ),
        'i18n'     => [
            'invalid_url' => __( 'Please enter a valid URL', 'ttc-link-shortener' ),
            'copied'      => __( 'Copied!', 'ttc-link-shortener' ),
            'confirm_del' => __( 'Delete this link?', 'ttc-link-shortener' ),
        ],
    ] );
} );
```

---

## 13. Dashboard UI Spec

### Layout

```
┌──────────────────────────────────────────────────────────────────┐
│ ttc.link            │  ☰   TTC URL Shortener            🌙       │  ← topbar
│ URL Shortener       ├──────────────────────────────────────────  │
│                     │                                            │
│ NAVIGATION          │  Dashboard                                 │
│ ▣ Dashboard         │  Create short links for example.com        │
│ 🔗 All Links        │                                            │
│                     │  ┌──────────────┐  ┌──────────────┐        │
│                     │  │ Total Links  │  │ Total Clicks │        │
│                     │  │      2       │  │      0       │        │
│                     │  └──────────────┘  └──────────────┘        │
│                     │                                            │
│                     │  Shorten a URL                             │
│                     │  ┌──────────────────────────┐ [Shorten]    │
│                     │  └──────────────────────────┘              │
│                     │                                            │
│                     │  📈 Recent links                           │
│                     │  example.com/abcdef        0 clicks        │
│                     │  https://destination.com/...               │
└──────────────────────────────────────────────────────────────────┘
```

### Color tokens (in CSS `:root`)

```css
:root {
    --ttcls-brand: #D94C14;
    --ttcls-brand-hover: #B83E0F;
    --ttcls-bg: #0F0F10;
    --ttcls-surface: #1A1A1C;
    --ttcls-surface-2: #232326;
    --ttcls-border: #2A2A2E;
    --ttcls-text: #F5F5F5;
    --ttcls-text-muted: #9A9AA0;
    --ttcls-radius: 10px;
    --ttcls-font: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Light mode override */
[data-theme="light"] {
    --ttcls-bg: #FFFFFF;
    --ttcls-surface: #F7F7F8;
    --ttcls-surface-2: #EFEFF1;
    --ttcls-border: #E4E4E7;
    --ttcls-text: #18181B;
    --ttcls-text-muted: #71717A;
}
```

### Components

- **Sidebar** (`partial-sidebar.php`): brand block + nav links (`?view=dashboard`, `?view=all-links`). Active state uses `--ttcls-brand` accent.
- **Topbar** (`partial-topbar.php`): collapse toggle, page title, theme switch (sun/moon).
- **Stat cards**: rounded `var(--ttcls-radius)`, `--ttcls-surface` background, label + value.
- **Form**: input + button. Button uses `--ttcls-brand`.
- **Recent links list**: each row shows `home_url/slug`, destination URL (truncated), and clicks badge.
- **All Links view**: same row design, plus copy and delete icon buttons.

### JS behaviors (`dashboard.js`)

- **Submit**: validate URL client-side → POST to `ttcls_create` → prepend new row to recent list → update stat counters.
- **Copy**: use `navigator.clipboard.writeText(short_url)` → flash "Copied!" tooltip.
- **Delete**: confirm → POST to `ttcls_delete` → remove row → update counts.
- **Theme toggle**: `data-theme` on `<html>`, persisted in `localStorage`.
- **Sidebar collapse**: toggle a `.is-collapsed` class on the wrapper.

No frameworks — vanilla JS, ES2017+.

---

## 14. Security Checklist

- [x] All AJAX uses `check_ajax_referer( 'ttcls_nonce', 'nonce' )`.
- [x] All AJAX checks `current_user_can( 'ttcls_manage_links' )`.
- [x] All DB queries use `$wpdb->prepare()`.
- [x] URL inputs run through `esc_url_raw()` + `FILTER_VALIDATE_URL` + scheme allow-list (http/https only).
- [x] Output uses `esc_html()`, `esc_url()`, `esc_attr()` everywhere.
- [x] Slug regex `^[A-Za-z0-9]{6}$` prevents path traversal.
- [x] `wp_safe_redirect()` for internal redirects; `wp_redirect()` only for the public short-link redirect (since destinations are external).
- [x] Slug uniqueness enforced at the DB level (`UNIQUE KEY`).
- [x] `random_int()` for slug generation (CSPRNG).
- [x] `short_manager` is blocked from `wp-admin` except AJAX.

---

## 15. Uninstall — `uninstall.php`

```php
<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;
$table = $wpdb->prefix . 'ttcls_links';
$wpdb->query( "DROP TABLE IF EXISTS {$table}" );

remove_role( 'short_manager' );

// Remove the auto-created page
$page = get_page_by_path( 'link-shorturl' );
if ( $page ) { wp_delete_post( $page->ID, true ); }

// Remove caps from administrator
$admin = get_role( 'administrator' );
if ( $admin ) { $admin->remove_cap( 'ttcls_manage_links' ); }
```

> Deactivation removes the role only. Uninstall removes everything (table + page + role + caps).

---

## 16. Build Order (for Claude Code)

Generate files in this order to keep dependencies clean:

1. `ttc-link-shortener.php` (main file with constants)
2. `includes/class-ttcls-helpers.php`
3. `includes/class-ttcls-db.php`
4. `includes/class-ttcls-roles.php`
5. `includes/class-ttcls-activator.php`
6. `includes/class-ttcls-deactivator.php`
7. `includes/class-ttcls-rewrite.php`
8. `includes/class-ttcls-admin-bar.php`
9. `includes/class-ttcls-assets.php`
10. `includes/class-ttcls-ajax.php`
11. `includes/class-ttcls-shortcode.php`
12. `includes/class-ttcls-plugin.php` (registers all hooks)
13. `templates/partial-sidebar.php`
14. `templates/partial-topbar.php`
15. `templates/dashboard-layout.php`
16. `templates/view-dashboard.php`
17. `templates/view-all-links.php`
18. `templates/view-login.php`
19. `assets/css/dashboard.css`
20. `assets/js/dashboard.js`
21. `uninstall.php`
22. `readme.txt`

---

## 17. Testing Checklist

After installation:

- [ ] Activate plugin → `wp_ttcls_links` table exists.
- [ ] A page **Link ShortURL** at `/link-shorturl/` was created with the shortcode.
- [ ] Role **Short Manager** appears in Users → Add New.
- [ ] Create a test user with role `short_manager` and log in.
- [ ] WP admin bar is hidden on the frontend for that user.
- [ ] Visiting `/wp-admin/` redirects them to `/link-shorturl/`.
- [ ] Dashboard renders with sidebar + topbar.
- [ ] Submit a URL → new row appears, stats update, no page reload.
- [ ] Visit `example.com/{slug}` → 301 redirect to destination, click counter increments.
- [ ] Invalid slug (e.g. `example.com/abc`) → normal 404.
- [ ] Existing page with a 6-char slug → page wins, no redirect.
- [ ] Logged-out visitor on `/link-shorturl/` → login prompt view.
- [ ] Deactivate → role removed, table preserved.
- [ ] Uninstall → table dropped, page deleted.

---

## 18. Notes & Future Enhancements

- **Custom slugs**: optional input to override the random slug (validate uniqueness + length).
- **QR codes**: generate a QR for each short URL (use `chillerlan/php-qrcode` or a JS lib).
- **Per-link analytics**: separate `ttcls_clicks` table with IP, UA, referer, timestamp.
- **Expiration**: `expires_at` column → check in redirect handler.
- **Bulk import**: CSV upload on All Links view.
- **REST API**: expose `/wp-json/ttcls/v1/links` for external integrations.

---

**End of specification.** Hand this file to Claude Code with a prompt like:

> Build the WordPress plugin described in `plugin_structure.md`. Create every file listed in section 2, follow the security checklist in section 14, and use the color/font tokens in section 13.
