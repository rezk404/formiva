# Static content contracts

These arrays are the temporary public-content source. They deliberately use
database-ready fields so a future `DatabaseContent` implementation can return
the same shapes without changing Blade, routes, or frontend JavaScript.

- `site.php`: site settings, navigation, contact details, metadata, legal, and social links.
- `services.php`: `id`/`slug` (when added), title, `lede`, capabilities, `form` visual state, and operational note.
- `projects.php`: `id`, `slug`, `title`, client, category, year, statement, description, `coverImage`, `thumbnail`, `clientLogo`, gallery, services, stack, challenge, solution, outcome, result, and featured flag.
- `insights.php`: index/slug, category, title, excerpt (`dek`), date, reading time, cover plate, and article body.
- `team.php`, `testimonials.php`, and `clients.php`: public proof content.

For the CMS phase, retain `ContentRepository` and replace `StaticContent`
with an Eloquent-backed implementation. The public controllers should keep
receiving arrays in these shapes; database models and media handling then stay
behind that interface.
