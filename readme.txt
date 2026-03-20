Anti Shuboohat — Static Islamic Q&A Platform

A fully static, zero-dependency website for Islamic questions and answers.
No PHP, no database, no server required — just open index.html in a browser
or deploy to any static hosting (GitHub Pages, Netlify, Vercel, etc.).

Project Structure
  index.html         Landing/welcome page
  browse.html        Search & browse Q&A entries
  article.html       Individual article view with voting
  data/
    config.json      Site configuration (name, defaults, pagination options)
    entries.json     All Q&A entries with translations
    languages.json   Language definitions (code, label, direction, fonts)
    ui-strings.json  All UI text translations per language
  assets/
    css/style.css    Main stylesheet (dark/light themes, responsive)
    js/i18n.js       Internationalization module
    js/theme.js      Dark/light theme toggle
    js/data.js       Data loading, search, and pagination
    js/votes.js      localStorage-based voting

How It Works
  - All content is stored in JSON files under data/
  - JavaScript loads these files and renders everything client-side
  - Search is done in-browser with instant filtering
  - Voting is saved in the browser's localStorage
  - Language switching is fully client-side with RTL support
  - Theme preference (dark/light) persists in localStorage

Adding/Editing Content
  - To add a new Q&A entry, edit data/entries.json
  - To add a new language, edit data/languages.json and data/ui-strings.json
  - To modify the UI text, edit data/ui-strings.json
  - To change site settings, edit data/config.json
  - All files are standard JSON — no build step required

Entry Format (data/entries.json)
  Each entry has:
    id            Unique integer
    question      The question text
    answer        The answer in Markdown format
    tags          Array of tag strings
    created_at    Date string (YYYY-MM-DD)
    translations  Object keyed by language code, each with question + answer

Language Format (data/languages.json)
  Each language has:
    label         Display name (e.g., "English", "العربية")
    dir           Text direction: "ltr" or "rtl"
    font          CSS font-family string
    fontUrl       Google Fonts URL (or empty string)

Features
  - Beautiful, modern design with Islamic aesthetic
  - Dark/light theme with system preference detection
  - Full multilingual support (LTR + RTL)
  - Client-side search with instant results
  - Pagination with configurable page sizes
  - Markdown rendering for answers (via marked.js)
  - XSS protection in markdown rendering
  - localStorage-based voting (no server needed)
  - Responsive design (mobile-first)
  - Smooth animations and transitions
  - Modular architecture — edit JSON files to customize everything
  - Zero build step — pure HTML, CSS, and JavaScript

Deployment
  Upload all files to any static hosting provider. No server configuration needed.
  For local development, use any static file server:
    npx serve .
    python -m http.server
    php -S localhost:8000
