# rental_website

Static rental listings site (vanilla HTML/CSS/JS, no build step). Deploys to GitHub Pages via `.github/workflows/static.yml`.

## Pages

- `index.html` — hero search (primary task first), featured rentals, how it works
- `listings.html` — filter/sort with live result counts, favorites view (`?favorites=1`)
- `listing.html` — rental details and contact form (`?id=...`)

## Design principles applied

- **Simple & focused** — one core task (find a rental), minimal pages
- **Uniform elements** — shared design tokens in `css/styles.css` (colors, spacing, radii, cards, buttons)
- **Most important information first** — price, location, and beds/baths lead every card and detail page
- **Outcome feedback** — toasts for save/sort/clear actions, live result counts, inline form validation and success messages

Run locally: `python3 -m http.server` and open `http://localhost:8000`.
