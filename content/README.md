# Article Editing

Edit articles in Markdown, then regenerate the JSON file used by the static site.

## Common Commands

Create a new article draft:

```powershell
python scripts/new_article.py "Your question here" --tags basics,theology
```

Create a translation draft for an existing article:

```powershell
python scripts/new_article.py "السؤال هنا؟" --translation ar --id 1
```

Rebuild the site data after editing:

```powershell
python scripts/build_entries.py
```

Check whether `data/entries.json` is up to date:

```powershell
python scripts/build_entries.py --check
```

## Article Format

Default-language articles live in `content/articles/`.

```markdown
---
id: 12
order: 12
question: Your question here?
summary: One or two sentences that answer the question directly.
created_at: 2026-04-30
tags:
  - basics
  - god
sources:
  - ghazali-munqidh
---

Write the answer in Markdown here.
```

`order` is optional. Use it when the browse order should differ from the article
id order.

`summary` is optional. It appears as "The short answer" at the top of the
article and under the question on cards and in search results. Translations can
carry their own `summary`.

`sources` is optional. Each item is a key from `data/sources.json`, the registry
of books. Add a book there first; the build stops on an unknown key.

Translations live in `content/translations/<language-code>/`.

```markdown
---
id: 12
question: السؤال هنا؟
---

Write the translated answer in Markdown here.
```

Files whose names start with `_` are ignored, so you can keep local templates in
these folders if you want.
