=== TTC Link Shortener ===
Contributors: xiansaiful
Author: Xian Saiful
Author URI: https://xiansaiful.com/
Tags: url shortener, links, redirect, short links
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Custom URL shortener with a branded dashboard for the short_manager role.

== Description ==

TTC Link Shortener turns your WordPress site into a private URL shortener:

* Custom DB table for short links and click counts
* Auto-created "Link ShortURL" page at `/link-shorturl/` with the dashboard
* Custom `short_manager` role limited to the dashboard (no wp-admin access)
* Six-character random slugs (cryptographically secure) — or **custom slugs** (3–64 chars, letters/numbers/hyphens/underscores)
* Reserved-slug protection (blocks `wp-admin`, `wp-login`, the dashboard page slug, etc.) and live collision check against existing WordPress pages
* 301 redirects from `yoursite.com/{slug}` to the destination
* AJAX-driven dashboard: create, copy, delete, list
* Brand color `#D94C14` and Inter font, with a built-in dark/light theme toggle

== Installation ==

1. Upload the `ttc-link-shortener` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. The plugin auto-creates a page **Link ShortURL** at `/link-shorturl/`.
4. Create a user with the **Short Manager** role (or use any administrator).
5. Visit `/link-shorturl/` and start shortening.

== Frequently Asked Questions ==

= How are slugs generated? =
Six random alphanumeric characters using `random_int()`. Collisions are checked against the DB; the slug length grows automatically if too many collisions occur. Auto-generated slugs are also screened against the reserved-slug list.

= Can I choose my own slug? =
Yes. The **Shorten a URL** form has an optional slug field. Allowed characters: letters, numbers, hyphens, underscores. Length 3–64. Slug must start and end with a letter or number. Reserved slugs (such as `wp-admin`, `wp-login`, the dashboard page slug, sitemaps, feeds) are rejected, as are slugs that match an existing WordPress page or another short link. Leave the field blank to fall back to the auto-generated 6-character code.

= How do I add or remove reserved slugs? =
Use the `ttcls_reserved_slugs` filter:

`add_filter( 'ttcls_reserved_slugs', function ( $list ) {
    $list[] = 'shop';
    return $list;
} );`

= Will the redirect collide with my pages? =
No. The handler checks `get_page_by_path()` first — real WordPress pages always win.

= What happens on uninstall? =
The DB table is dropped, the dashboard page is deleted, the `short_manager` role is removed, and the custom capability is removed from administrators. Deactivation removes only the role; data is preserved.

== Changelog ==

= 1.1.0 =
* Added optional custom slug field on the dashboard (3–64 chars; letters, numbers, hyphens, underscores).
* Added reserved-slug protection with a `ttcls_reserved_slugs` filter.
* Auto-generated slugs are now screened against the reserved list.
* Schema: `slug` column widened from `VARCHAR(10)` to `VARCHAR(64)`. Existing installs are migrated automatically on next page load via `dbDelta`.
* Rewrite regex now accepts 3–64 character slugs containing letters, numbers, hyphens, and underscores.

= 1.0.0 =
* Initial release.
