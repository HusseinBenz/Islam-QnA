#!/usr/bin/env python3
"""Create a new Markdown article draft."""

from __future__ import annotations

import argparse
from datetime import date
from pathlib import Path

from build_entries import (
    ARTICLES_DIR,
    TRANSLATIONS_DIR,
    ContentError,
    build_entries,
    slugify,
)


def format_tags(tags: list[str]) -> str:
    if not tags:
        return "tags: []"
    return "tags:\n" + "\n".join(f"  - {tag}" for tag in tags)


def next_article_id() -> int:
    try:
        entries = build_entries()
    except ContentError as exc:
        if "No Markdown articles found" in str(exc):
            entries = []
        else:
            raise
    if not entries:
        return 1
    return max(entry["id"] for entry in entries) + 1


def write_article(question: str, tags: list[str], created_at: str) -> Path:
    entry_id = next_article_id()
    ARTICLES_DIR.mkdir(parents=True, exist_ok=True)
    path = ARTICLES_DIR / f"{entry_id:03d}-{slugify(question)}.md"
    if path.exists():
        raise FileExistsError(path)

    content = (
        "---\n"
        f"id: {entry_id}\n"
        f"order: {entry_id}\n"
        f"question: {question}\n"
        f"created_at: {created_at}\n"
        f"{format_tags(tags)}\n"
        "---\n\n"
        "Write the answer here.\n"
    )
    path.write_text(content, encoding="utf-8", newline="\n")
    return path


def write_translation(entry_id: int, language: str, question: str) -> Path:
    language_dir = TRANSLATIONS_DIR / language
    language_dir.mkdir(parents=True, exist_ok=True)
    path = language_dir / f"{entry_id:03d}-{slugify(question)}.md"
    if path.exists():
        raise FileExistsError(path)

    content = (
        "---\n"
        f"id: {entry_id}\n"
        f"question: {question}\n"
        "---\n\n"
        "Write the translated answer here.\n"
    )
    path.write_text(content, encoding="utf-8", newline="\n")
    return path


def parse_tags(raw: str) -> list[str]:
    return [tag.strip() for tag in raw.split(",") if tag.strip()]


def main() -> int:
    parser = argparse.ArgumentParser(description="Create a Markdown article draft.")
    parser.add_argument("question", help="article question/title")
    parser.add_argument(
        "--tags",
        default="",
        help="comma-separated article tags, for example: basics,theology",
    )
    parser.add_argument(
        "--date",
        default=date.today().isoformat(),
        help="created_at date for a new article, default: today",
    )
    parser.add_argument(
        "--translation",
        metavar="LANG",
        help="create a translation draft for an existing article instead",
    )
    parser.add_argument(
        "--id",
        type=int,
        help="article id for --translation",
    )
    args = parser.parse_args()

    try:
        if args.translation:
            if not args.id:
                parser.error("--translation requires --id")
            path = write_translation(args.id, args.translation, args.question)
        else:
            path = write_article(args.question, parse_tags(args.tags), args.date)
        print(path)
        return 0
    except FileExistsError as exc:
        print(f"Error: {exc.filename} already exists")
        return 1
    except ContentError as exc:
        print(f"Error: {exc}")
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
