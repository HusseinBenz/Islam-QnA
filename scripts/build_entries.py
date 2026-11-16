#!/usr/bin/env python3
"""Build data/entries.json from Markdown content files.

Content lives in:
  content/articles/*.md
  content/translations/<lang>/*.md

The script intentionally uses only the Python standard library.
"""

from __future__ import annotations

import argparse
import json
import re
import sys
from pathlib import Path
from typing import Any


ROOT = Path(__file__).resolve().parents[1]
ARTICLES_DIR = ROOT / "content" / "articles"
TRANSLATIONS_DIR = ROOT / "content" / "translations"
ENTRIES_JSON = ROOT / "data" / "entries.json"
SOURCES_JSON = ROOT / "data" / "sources.json"


class ContentError(ValueError):
    """Raised when Markdown content cannot be converted safely."""


def slugify(value: str) -> str:
    value = value.lower()
    value = re.sub(r"[^a-z0-9]+", "-", value)
    value = re.sub(r"-{2,}", "-", value).strip("-")
    return value or "article"


def parse_scalar(value: str) -> Any:
    value = value.strip()
    if not value:
        return ""
    if (value.startswith('"') and value.endswith('"')) or (
        value.startswith("'") and value.endswith("'")
    ):
        return value[1:-1]
    if value.isdigit():
        return int(value)
    if value.startswith("[") and value.endswith("]"):
        inner = value[1:-1].strip()
        if not inner:
            return []
        return [parse_scalar(part.strip()) for part in inner.split(",")]
    return value


def parse_front_matter(text: str, path: Path) -> tuple[dict[str, Any], str]:
    text = text.replace("\r\n", "\n").replace("\r", "\n")
    if text.startswith("\ufeff"):
        text = text[1:]
    if not text.startswith("---\n"):
        raise ContentError(f"{path}: missing front matter delimiter '---'")

    end = text.find("\n---", 4)
    if end == -1:
        raise ContentError(f"{path}: missing closing front matter delimiter '---'")

    raw_meta = text[4:end].strip("\n")
    body = text[end + 4 :]
    body = body.lstrip("\n").rstrip()

    meta: dict[str, Any] = {}
    current_list_key: str | None = None

    for line_number, raw_line in enumerate(raw_meta.split("\n"), start=2):
        line = raw_line.rstrip()
        stripped = line.strip()
        if not stripped or stripped.startswith("#"):
            continue

        if current_list_key and line.startswith((" ", "\t")):
            item = stripped
            if not item.startswith("- "):
                raise ContentError(
                    f"{path}:{line_number}: expected '- value' list item for {current_list_key}"
                )
            meta[current_list_key].append(parse_scalar(item[2:]))
            continue

        current_list_key = None
        if ":" not in line:
            raise ContentError(f"{path}:{line_number}: expected 'key: value'")

        key, value = line.split(":", 1)
        key = key.strip()
        value = value.strip()
        if not key:
            raise ContentError(f"{path}:{line_number}: empty front matter key")
        if key in meta:
            raise ContentError(f"{path}:{line_number}: duplicate key '{key}'")

        if value == "":
            meta[key] = []
            current_list_key = key
        else:
            meta[key] = parse_scalar(value)

    return meta, body


def require(value: dict[str, Any], key: str, path: Path) -> Any:
    if key not in value or value[key] in ("", None):
        raise ContentError(f"{path}: missing required front matter field '{key}'")
    return value[key]


def as_int(value: Any, key: str, path: Path) -> int:
    if isinstance(value, int):
        return value
    if isinstance(value, str) and value.isdigit():
        return int(value)
    raise ContentError(f"{path}: field '{key}' must be an integer")


def as_tags(value: Any, path: Path) -> list[str]:
    if value is None:
        return []
    if not isinstance(value, list):
        raise ContentError(f"{path}: field 'tags' must be a list")
    tags = []
    for tag in value:
        if not isinstance(tag, str) or not tag.strip():
            raise ContentError(f"{path}: every tag must be a non-empty string")
        tags.append(tag.strip())
    return tags


def as_text(value: Any, key: str, path: Path) -> str:
    """Optional one-line text field. A bare 'key:' parses as an empty list."""
    if value is None or value == []:
        return ""
    if isinstance(value, list):
        raise ContentError(f"{path}: field '{key}' must be a single line of text")
    return str(value).strip()


def load_source_keys() -> set[str]:
    """Keys of the book registry in data/sources.json."""
    if not SOURCES_JSON.exists():
        return set()
    try:
        return set(json.loads(SOURCES_JSON.read_text(encoding="utf-8")).keys())
    except json.JSONDecodeError as exc:
        raise ContentError(f"{SOURCES_JSON}: invalid JSON ({exc})") from exc


def as_sources(value: Any, known: set[str], path: Path) -> list[str]:
    if value is None:
        return []
    if not isinstance(value, list):
        raise ContentError(f"{path}: field 'sources' must be a list")
    sources = []
    for key in value:
        key = str(key).strip()
        if key not in known:
            raise ContentError(
                f"{path}: unknown source '{key}'. Add it to data/sources.json first."
            )
        if key not in sources:
            sources.append(key)
    return sources


def iter_markdown_files(directory: Path) -> list[Path]:
    if not directory.exists():
        return []
    return sorted(
        path
        for path in directory.glob("*.md")
        if not path.name.startswith("_") and path.name.lower() != "readme.md"
    )


def load_article(path: Path, known_sources: set[str] | None = None) -> dict[str, Any]:
    meta, body = parse_front_matter(path.read_text(encoding="utf-8"), path)
    entry_id = as_int(require(meta, "id", path), "id", path)
    order = as_int(meta.get("order", entry_id), "order", path)
    question = str(require(meta, "question", path)).strip()
    created_at = str(require(meta, "created_at", path)).strip()
    tags = as_tags(meta.get("tags", []), path)
    summary = as_text(meta.get("summary"), "summary", path)
    sources = as_sources(meta.get("sources"), known_sources or set(), path)
    if not body:
        raise ContentError(f"{path}: article body is empty")
    entry: dict[str, Any] = {
        "id": entry_id,
        "_order": order,
        "question": question,
    }
    if summary:
        entry["summary"] = summary
    entry["answer"] = body
    entry["tags"] = tags
    if sources:
        entry["sources"] = sources
    entry["created_at"] = created_at
    entry["translations"] = {}
    return entry


def load_translation(path: Path) -> tuple[int, dict[str, str]]:
    meta, body = parse_front_matter(path.read_text(encoding="utf-8"), path)
    entry_id = as_int(require(meta, "id", path), "id", path)
    question = str(require(meta, "question", path)).strip()
    summary = as_text(meta.get("summary"), "summary", path)
    if not body:
        raise ContentError(f"{path}: translation body is empty")
    translation = {"question": question}
    if summary:
        translation["summary"] = summary
    translation["answer"] = body
    return entry_id, translation


def build_entries() -> list[dict[str, Any]]:
    entries_by_id: dict[int, dict[str, Any]] = {}
    known_sources = load_source_keys()

    for path in iter_markdown_files(ARTICLES_DIR):
        entry = load_article(path, known_sources)
        entry_id = entry["id"]
        if entry_id in entries_by_id:
            raise ContentError(f"{path}: duplicate article id {entry_id}")
        entries_by_id[entry_id] = entry

    if not entries_by_id:
        raise ContentError(f"No Markdown articles found in {ARTICLES_DIR}")

    if TRANSLATIONS_DIR.exists():
        for language_dir in sorted(path for path in TRANSLATIONS_DIR.iterdir() if path.is_dir()):
            language = language_dir.name
            for path in iter_markdown_files(language_dir):
                entry_id, translation = load_translation(path)
                if entry_id not in entries_by_id:
                    raise ContentError(
                        f"{path}: translation references missing article id {entry_id}"
                    )
                if language in entries_by_id[entry_id]["translations"]:
                    raise ContentError(
                        f"{path}: duplicate {language} translation for article id {entry_id}"
                    )
                entries_by_id[entry_id]["translations"][language] = translation

    ordered_entries = sorted(
        entries_by_id.values(),
        key=lambda entry: (entry.get("_order", entry["id"]), entry["id"]),
    )
    output = []
    for entry in ordered_entries:
        item = dict(entry)
        item.pop("_order", None)
        output.append(item)
    return output


def format_json(entries: list[dict[str, Any]]) -> str:
    return json.dumps(entries, ensure_ascii=False, indent=2) + "\n"


def format_list(values: list[str]) -> str:
    if not values:
        return "tags: []"
    return "tags:\n" + "\n".join(f"  - {value}" for value in values)


def article_filename(entry: dict[str, Any]) -> str:
    return f"{entry['id']:03d}-{slugify(entry['question'])}.md"


def markdown_from_entry(
    entry: dict[str, Any],
    *,
    translation_language: str | None = None,
    order: int | None = None,
) -> str:
    if translation_language:
        translated = entry["translations"][translation_language]
        return (
            "---\n"
            f"id: {entry['id']}\n"
            f"question: {translated['question']}\n"
            "---\n\n"
            f"{translated['answer'].rstrip()}\n"
        )

    order_line = f"order: {order}\n" if order is not None else ""
    return (
        "---\n"
        f"id: {entry['id']}\n"
        f"{order_line}"
        f"question: {entry['question']}\n"
        f"created_at: {entry['created_at']}\n"
        f"{format_list(entry.get('tags', []))}\n"
        "---\n\n"
        f"{entry['answer'].rstrip()}\n"
    )


def export_existing_entries(force: bool) -> int:
    if not ENTRIES_JSON.exists():
        raise ContentError(f"{ENTRIES_JSON} does not exist")

    entries = json.loads(ENTRIES_JSON.read_text(encoding="utf-8"))
    ARTICLES_DIR.mkdir(parents=True, exist_ok=True)
    TRANSLATIONS_DIR.mkdir(parents=True, exist_ok=True)

    written = 0
    for order, entry in enumerate(entries, start=1):
        path = ARTICLES_DIR / article_filename(entry)
        if path.exists() and not force:
            raise ContentError(f"{path} already exists. Use --force to overwrite.")
        path.write_text(
            markdown_from_entry(entry, order=order),
            encoding="utf-8",
            newline="\n",
        )
        written += 1

        for language in sorted((entry.get("translations") or {}).keys()):
            language_dir = TRANSLATIONS_DIR / language
            language_dir.mkdir(parents=True, exist_ok=True)
            translation_path = language_dir / article_filename(entry)
            if translation_path.exists() and not force:
                raise ContentError(
                    f"{translation_path} already exists. Use --force to overwrite."
                )
            translation_path.write_text(
                markdown_from_entry(entry, translation_language=language),
                encoding="utf-8",
                newline="\n",
            )
            written += 1

    return written


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Build data/entries.json from Markdown article files."
    )
    parser.add_argument(
        "--check",
        action="store_true",
        help="verify data/entries.json is already in sync without writing it",
    )
    parser.add_argument(
        "--export-from-json",
        action="store_true",
        help="create Markdown files from the current data/entries.json",
    )
    parser.add_argument(
        "--force",
        action="store_true",
        help="overwrite existing Markdown files when used with --export-from-json",
    )
    args = parser.parse_args()

    try:
        if args.export_from_json:
            count = export_existing_entries(force=args.force)
            print(f"Exported {count} Markdown file(s).")
            return 0

        output = format_json(build_entries())
        if args.check:
            current = ENTRIES_JSON.read_text(encoding="utf-8") if ENTRIES_JSON.exists() else ""
            if current != output:
                print("data/entries.json is not in sync with Markdown content.", file=sys.stderr)
                return 1
            print("data/entries.json is in sync.")
            return 0

        ENTRIES_JSON.write_text(output, encoding="utf-8", newline="\n")
        print(f"Built {ENTRIES_JSON.relative_to(ROOT)}")
        return 0
    except ContentError as exc:
        print(f"Error: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
