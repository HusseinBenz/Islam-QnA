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
    entries.json     Generated Q&A entries with translations
    languages.json   Language definitions (code, label, direction, fonts)
    ui-strings.json  All UI text translations per language
  content/
    articles/        One Markdown file per article
    translations/    Optional translated Markdown files, grouped by language
  scripts/
    build_entries.py Generate data/entries.json from Markdown files
    new_article.py   Create a new article or translation draft
  assets/
    css/style.css    Main stylesheet (dark/light themes, responsive)
    js/i18n.js       Internationalization module
    js/theme.js      Dark/light theme toggle
    js/data.js       Data loading, search, and pagination
    js/votes.js      localStorage-based voting

How It Works
  - Articles are edited as Markdown files under content/
  - data/entries.json is generated from Markdown for the static site
  - JavaScript loads these files and renders everything client-side
  - Search is done in-browser with instant filtering
  - Voting is saved in the browser's localStorage
  - Language switching is fully client-side with RTL support
  - Theme preference (dark/light) persists in localStorage

Adding/Editing Content
  - To edit an article, edit its Markdown file in content/articles/
  - To add a new article draft:
      python scripts/new_article.py "Your question here" --tags basics,theology
  - To add an Arabic translation draft for article 1:
      python scripts/new_article.py "السؤال هنا؟" --translation ar --id 1
  - After editing Markdown, regenerate the JSON:
      python scripts/build_entries.py
  - To check that generated JSON is up to date:
      python scripts/build_entries.py --check
  - To add a new language, edit data/languages.json and data/ui-strings.json
  - To modify the UI text, edit data/ui-strings.json
  - To change site settings, edit data/config.json
  - Do not hand-edit data/entries.json unless you are intentionally bypassing
    the Markdown workflow; it will be overwritten by the build script

Markdown Article Format (content/articles/*.md)
  Each default-language article has front matter followed by the answer:
    ---
    id: 12
    order: 12
    question: Your question here?
    created_at: 2026-04-30
    tags:
      - basics
      - theology
    ---

    Write the answer in Markdown here.

  order is optional. Use it when you want the browse order to differ from the
  article id order.

Markdown Translation Format (content/translations/ar/*.md)
  Each translation points back to the article id:
    ---
    id: 12
    question: السؤال هنا؟
    ---

    Write the translated answer in Markdown here.

Generated Entry Format (data/entries.json)
  Each generated entry has:
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
  - Markdown-first article editing with a tiny standard-library generator
  - XSS protection in markdown rendering
  - localStorage-based voting (no server needed)
  - Responsive design (mobile-first)
  - Smooth animations and transitions
  - Modular architecture — edit JSON files to customize everything
  - Static output — pure HTML, CSS, JavaScript, and generated JSON

Deployment
  Upload all files to any static hosting provider. No server configuration needed.
  For local development, use any static file server:
    npx serve .
    python -m http.server
    php -S localhost:8000
