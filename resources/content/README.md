# Static content contracts

These arrays are the temporary public-content source. They deliberately use
database-ready fields so a future `DatabaseContent` implementation can return
the same shapes without changing Blade, routes, or frontend JavaScript.

## Files

- `site.php`: brand, metadata, contact details, social links, legal. Navigation
  is **not** here — the primary nav is four routes set in
  `components/navbar.blade.php`, and each homepage chapter declares its own 3D
  state via `data-world` on the section.
- `services.php`: three pillars, each with `index`, `slug`, `title`, `form`
  (the 3D posture it requests), `lede`, `why`, `outcome`, `note`, and an
  `items` list of individual capabilities.
- `projects.php`: `slug` plus optional `index`, `title`, `name`, `client`,
  `category`, `year`, `statement`, `description`, `challenge`, `solution`,
  `outcome`, `coverImage`, `plate`, `gallery`, `services`, `stack`, `result`,
  `clientLogo`, `alt`, `featured`.
- `insights.php`: `slug` plus optional `index`, `category`, `title`, `dek`,
  `date`, `date_label`, `reading`, `body`, `plate`, `alt`.
- `case-study.php`: the featured narrative — `beats` and `metrics` are always
  returned as lists.
- `intake.php`: project types, per-group budget ranges, timelines, company
  sizes, and the estimator `rules` (`fit`, `complexity`, `timeline_hint`).
  These are the single source of truth for the estimator; nothing in
  JavaScript duplicates them.
- `team.php`, `testimonials.php`, `clients.php`, `studio.php`, `process.php`:
  public proof and studio content.

## The shape guarantee

`App\Content\StaticContent` normalises `projects` and `insights` into a fixed
shape before any view sees them: strings are always strings, lists are always
lists, and nested `result`/`plate`/`coverImage` always have their keys. Views
therefore render without testing for missing keys, and one absent optional
field cannot take a page down.

Two consequences worth knowing:

1. A field added to a content file is **dropped** unless the normaliser is
   taught to carry it. This is deliberate — the contract stays explicit.
2. A record with no artwork gets a deterministic plate seed derived from its
   slug, so generated visuals never change between requests.

`tests/Unit/ContentShapeTest.php` pins this contract.

## Moving to a database

Keep `ContentRepository` and replace `StaticContent` with an Eloquent-backed
implementation returning the same shapes; swap the binding in
`AppServiceProvider`. The normalisation above matters more there than here — a
nullable column is a far easier mistake to make than a missing array key.
