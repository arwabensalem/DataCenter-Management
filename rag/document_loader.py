"""Chargement et extraction des documents RAG (PDF officiels + Markdown générés)."""

from __future__ import annotations

import logging
import re
from dataclasses import dataclass, field
from pathlib import Path
from typing import Iterator

import pymupdf

from . import config

logger = logging.getLogger(__name__)


@dataclass
class DocumentPage:
    """Une page (PDF) ou section logique (Markdown)."""

    text: str
    page: int | None = None


@dataclass
class LoadedDocument:
    """Document source prêt pour le chunking."""

    filename: str
    filepath: str
    source_type: str  # official | generated
    source: str
    title: str
    category: str
    year: int | None
    pages: list[DocumentPage] = field(default_factory=list)

    @property
    def full_text(self) -> str:
        return "\n\n".join(p.text for p in self.pages if p.text.strip())


# Mapping filename patterns → metadata enrichie
_CATEGORY_HINTS: list[tuple[re.Pattern[str], str]] = [
    (re.compile(r"pue|power.?usage|ere.?metric|wp49", re.I), "pue"),
    (re.compile(r"cool|thermal|air.?manage|liquid.?cool|compressor", re.I), "cooling"),
    (re.compile(r"photo|pv|photovolta|solaire", re.I), "photovoltaic"),
    (re.compile(r"co2|carbon|environ", re.I), "co2"),
    (re.compile(r"energy|efficien|dcoi|femp|lbnl|best.?practice|design", re.I), "energy_efficiency"),
    (re.compile(r"metric|sustainab|decision", re.I), "metrics"),
    (re.compile(r"ashrae|power.?white", re.I), "power"),
]

_SOURCE_HINTS: list[tuple[re.Pattern[str], str]] = [
    (re.compile(r"doe|process.?manual|dcoi", re.I), "DOE"),
    (re.compile(r"green.?grid|wp49|ere.?metric|task.?force", re.I), "The Green Grid"),
    (re.compile(r"ashrae", re.I), "ASHRAE"),
    (re.compile(r"lbnl", re.I), "LBNL"),
    (re.compile(r"femp|nrel", re.I), "FEMP/NREL"),
    (re.compile(r"referentiel|anme|installation.?des.?systemes.?photo", re.I), "ANME"),
    (re.compile(r"herrlin|pgande", re.I), "Herrlin/PG&E"),
    (re.compile(r"thermal.?guidelines", re.I), "ASHRAE TC 9.9"),
]


def _guess_category(filename: str, title: str = "") -> str:
    hay = f"{filename} {title}"
    for pattern, cat in _CATEGORY_HINTS:
        if pattern.search(hay):
            return cat
    return "general"


def _guess_source(filename: str, source_type: str) -> str:
    for pattern, src in _SOURCE_HINTS:
        if pattern.search(filename):
            return src
    return "official" if source_type == "official" else "generated"


def _guess_year(filename: str, text_sample: str = "") -> int | None:
    for candidate in (filename, text_sample[:2000]):
        matches = re.findall(r"(?:19|20)\d{2}", candidate)
        years = [int(y) for y in matches if 1990 <= int(y) <= 2030]
        if years:
            return max(years)
    return None


def _title_from_filename(filename: str) -> str:
    stem = Path(filename).stem
    stem = re.sub(r"[_\-]+", " ", stem)
    stem = re.sub(r"\s+", " ", stem).strip()
    return stem[:200] or filename


def _title_from_markdown(text: str, fallback: str) -> str:
    for line in text.splitlines():
        line = line.strip()
        if line.startswith("# "):
            return line[2:].strip()[:200]
    return fallback


def clean_text(text: str) -> str:
    """Nettoyage léger du texte extrait."""
    text = text.replace("\x00", " ")
    text = text.replace("\r\n", "\n").replace("\r", "\n")
    # Ligatures / espaces insécables
    text = text.replace("\u00a0", " ").replace("\u202f", " ")
    # Espaces multiples (hors sauts de ligne)
    text = re.sub(r"[ \t]+", " ", text)
    # Plus de 3 sauts de ligne consécutifs
    text = re.sub(r"\n{4,}", "\n\n\n", text)
    return text.strip()


def load_pdf(path: Path) -> LoadedDocument:
    """Extrait le texte page par page via PyMuPDF."""
    filename = path.name
    source_type = "official"
    pages: list[DocumentPage] = []

    with pymupdf.open(path) as doc:
        for i, page in enumerate(doc, start=1):
            raw = page.get_text("text") or ""
            cleaned = clean_text(raw)
            if cleaned:
                pages.append(DocumentPage(text=cleaned, page=i))

    sample = pages[0].text if pages else ""
    title = _title_from_filename(filename)

    return LoadedDocument(
        filename=filename,
        filepath=str(path.resolve()),
        source_type=source_type,
        source=_guess_source(filename, source_type),
        title=title,
        category=_guess_category(filename, title),
        year=_guess_year(filename, sample),
        pages=pages,
    )


def load_markdown(path: Path) -> LoadedDocument:
    """Charge un document Markdown généré comme une seule « page » logique."""
    filename = path.name
    source_type = "generated"
    raw = path.read_text(encoding="utf-8", errors="replace")
    cleaned = clean_text(raw)
    title = _title_from_markdown(cleaned, _title_from_filename(filename))

    pages = [DocumentPage(text=cleaned, page=1)] if cleaned else []

    return LoadedDocument(
        filename=filename,
        filepath=str(path.resolve()),
        source_type=source_type,
        source=_guess_source(filename, source_type),
        title=title,
        category=_guess_category(filename, title),
        year=_guess_year(filename, cleaned[:2000]),
        pages=pages,
    )


def iter_document_paths() -> Iterator[tuple[Path, str]]:
    """Yield (path, source_type) pour tous les documents supportés."""
    if config.OFFICIAL_DIR.is_dir():
        for path in sorted(config.OFFICIAL_DIR.glob("*.pdf")):
            yield path, "official"
    if config.GENERATED_DIR.is_dir():
        for path in sorted(config.GENERATED_DIR.glob("*.md")):
            yield path, "generated"


def load_all_documents() -> list[LoadedDocument]:
    """Charge tous les documents du corpus RAG."""
    documents: list[LoadedDocument] = []
    for path, source_type in iter_document_paths():
        try:
            if source_type == "official":
                doc = load_pdf(path)
            else:
                doc = load_markdown(path)
            if not doc.pages:
                logger.warning("Document vide ignoré: %s", path.name)
                continue
            documents.append(doc)
            logger.info(
                "Chargé %s (%s) — %d page(s), catégorie=%s",
                doc.filename,
                doc.source_type,
                len(doc.pages),
                doc.category,
            )
        except Exception:
            logger.exception("Échec chargement %s", path)
    return documents
