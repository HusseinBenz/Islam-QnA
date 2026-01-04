Anti Shuboohat - plain HTML Q&A

What's here
- index.php: public search page with multilingual UI buttons, search bar, and a "Browse" button for recent entries.
- admin.php: admin console for managing entries, translations, UI strings, and available languages.
- qa.sqlite: SQLite database created automatically beside the PHP files.

Language
- UI language is selected via on-page buttons (pulled from the Languages list). Arabic renders right-to-left.
- Admin can add new languages (code + label + direction). Once added and translated, they appear as buttons on the public page.
- Admin can edit UI strings per language (title, tagline, search labels, etc.) so the public UI becomes fully translated.
- Only entries available in the selected language are shown. If a translation is missing for that language, the entry is hidden until translated.
- Search works inside the selected language only (English searches originals; other languages search their translations).
- Everything is stored and served as UTF-8; keep new translations in UTF-8 text.

Public page
- Search bar + "Search" button for keyword lookups, plus a "Browse" button to page through all entries.
- Language buttons reflect the configured languages; admin access is a button too.
- Results list question and answer blocks with minimal separators; no technical details are shown.
- Pagination: user-selectable page size (5/10/25/50/100) with previous/next navigation.

Admin console (admin.php)
- Monolingual (English only) admin page.
- Default admin credentials: username admin / password admin.
- Manage languages: add code/label/direction; UI becomes aware immediately.
- Manage UI strings: select a language and save translations for all visible UI text keys.
- Filter entries by keyword and sort by updated date, created date, or question text (ASC/DESC).
- Actions per entry: Edit, Delete, Translate. Edit view lets you edit existing translations (pick a language that already exists, including the default). Translate view only adds new translations for languages that are still missing.
- "Available languages" column includes the default English plus any saved translations.
- Editing updates the "Updated" timestamp; deleting also removes translations for that entry.
- "Add new entry" form is available to admins; add new translations from Translate, edit existing translations from Edit.

Data model
- qa: id, question, answer, created_at, updated_at (timestamps use SQLite CURRENT_TIMESTAMP).
- qa_translations: id, qa_id, lang (e.g., en, ar, fr), question, answer, created_at, updated_at; unique per qa_id + lang.
- Answers are stored fully; the admin list shows a truncated preview only.

Seeding and storage
- If qa is empty, a batch of sample rows (40+ items) is inserted automatically to exercise pagination.
- All content is stored locally in qa.sqlite; back it up if needed.
