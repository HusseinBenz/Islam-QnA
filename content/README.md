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
created_at: 2026-04-30
tags:
  - basics
  - theology
---

Write the answer in Markdown here.
```

`order` is optional. Use it when the browse order should differ from the article
id order.

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
