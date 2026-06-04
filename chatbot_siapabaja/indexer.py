"""
indexer.py

Indexer lokal SIAPABAJA RAG.
- Membaca dataset Markdown dari folder data/
- Membuat record terstruktur
- Membuat embedding memakai Ollama
- Menyimpan hasil ke storage/dataset_siapabaja.jsonl dan storage/embeddings.npy

Tidak memakai Qdrant.
Jalankan dari folder chatbot_siapabaja:
    python indexer.py
"""

from __future__ import annotations

import hashlib
import json
import os
import re
import time
from pathlib import Path
from typing import Any, Dict, Iterable, List, Optional, Tuple

import numpy as np
import requests

BASE_DIR = Path(__file__).resolve().parent

DATASET_DIR = Path(os.getenv("DATASET_DIR", str(BASE_DIR / "data")))
STORAGE_DIR = Path(os.getenv("STORAGE_DIR", str(BASE_DIR / "storage")))
STORAGE_DIR.mkdir(parents=True, exist_ok=True)

JSONL_PATH = STORAGE_DIR / "dataset_siapabaja.jsonl"
EMBEDDING_PATH = STORAGE_DIR / "embeddings.npy"
SIGNATURE_PATH = STORAGE_DIR / "dataset_signature.txt"

OLLAMA_BASE_URL = os.getenv("OLLAMA_BASE_URL", "http://localhost:11434")
EMBED_MODEL = os.getenv("EMBED_MODEL", "nomic-embed-text")
BATCH_SIZE = int(os.getenv("BATCH_SIZE", "16"))

EMBEDDING_CODE_VERSION = "v3_synonyms_targeted_kw_bonus_local_numpy"

INTENT_SYNONYMS = {
    "login": "masuk sistem autentikasi email password verifikasi sign in",
    "logout": "keluar sistem akhiri sesi sign out tutup sesi",
    "document_upload_denied_user": "unggah dokumen lampiran upload file pengunjung publik tidak bisa",
    "internal_login_denied_user": "login masuk akun internal dashboard pengunjung tidak bisa",
    "delete_permanent_warning": "hapus permanen dikembalikan recycle bin pulihkan data hilang",
    "archive_search_filter_feature": "pencarian filter saring kata kunci cari arsip",
    "account_status_issue": "akun tidak aktif status akun nonaktif bermasalah akun internal",
    "search_filter_no_result": "hasil pencarian kosong filter tidak ditemukan data tidak muncul",
    "document_upload_feature": "format file didukung pdf doc docx xls xlsx jpg png batas ukuran 10mb fitur sistem",
    "unit_upload_document_permission": "unit mengunggah lampiran arsip unit cakupan unit sendiri",
    "upload_failed": "gagal upload file ditolak tidak bisa diunggah ukuran format koneksi",
    "preview_download_issue": "preview dokumen download unduh tidak bisa lihat format docx xlsx",
    "super_admin_reset_password_scope": "reset password akun unit ppk lupa password pengguna lain",
    "super_admin_self_account_management": "akun pribadi super admin sendiri ganti password profil sendiri",
    "unit_other_unit_restriction": "arsip unit lain tidak bisa lihat tidak berwenang lintas unit",
    "unit_visibility_public_private": "arsip publik privat visibilitas milik sendiri bisa dilihat",
    "private_archive_visibility": "siapa bisa melihat arsip privat unit ppk super admin visibilitas",
}


def normalize_text(text: str) -> str:
    return re.sub(r"\s+", " ", str(text or "").lower().strip())


def stable_uid(*parts: str) -> str:
    raw = "||".join(str(p) for p in parts)
    return hashlib.sha256(raw.encode("utf-8")).hexdigest()


def slugify_dataset_name(file_path: Path) -> str:
    return file_path.stem.replace("(2)", "").replace("(1)", "").strip()


def detect_category_and_role(file_path: Path, text: str = "") -> Tuple[str, Optional[str]]:
    name = file_path.name.lower()
    full_text = (name + "\n" + text[:500]).lower()

    if "role_user" in full_text or "role: user" in full_text or "guest/publik" in full_text:
        return "role", "user"
    if "role_unit" in full_text or "role: unit" in full_text:
        return "role", "unit"
    if "role_ppk" in full_text or "role: ppk" in full_text:
        return "role", "ppk"
    if "role_superadmin" in full_text or "role_super_admin" in full_text or "role_superadmin" in name or "role_super" in name or "super admin" in full_text:
        return "role", "super_admin"

    if "arsitektur" in full_text or "fitur" in full_text:
        return "feature", None
    if "workflow" in full_text:
        return "workflow", None
    if "faq" in full_text:
        return "faq", None

    return "unknown", None


def build_embedding_text(record: Dict[str, Any]) -> str:
    tags = ", ".join(record.get("tags", []))
    role = record.get("role_access") or "-"
    patterns = " | ".join(record.get("patterns", []))
    intent = record.get("intent", "")
    extra = INTENT_SYNONYMS.get(intent, "")

    return (
        f"Intent: {intent}\n"
        f"Tags: {tags}\n"
        f"Dataset: {record.get('dataset')}\n"
        f"Kategori: {record.get('category')}\n"
        f"Role: {role}\n"
        f"Contoh Pertanyaan: {patterns}\n"
        f"Jawaban Resmi: {record.get('response')}"
        + (f"\nSinonim Pembeda: {extra}" if extra else "")
    ).strip()


def parse_markdown_file(file_path: Path) -> List[Dict[str, Any]]:
    text = file_path.read_text(encoding="utf-8")
    dataset_name = slugify_dataset_name(file_path)
    category, role_access = detect_category_and_role(file_path, text)

    blocks = re.split(r"\n(?=##\s+\d+\.?\s*Intent:)", text)
    records: List[Dict[str, Any]] = []

    for block in blocks:
        intent_match = re.search(r"##\s+\d+\.?\s*Intent:\s*(.+)", block)
        if not intent_match:
            continue

        intent = intent_match.group(1).strip()

        tags_match = re.search(r"\*\s*\*\*Tags:\*\*\s*(.+)", block)
        tags = re.findall(r"`([^`]+)`", tags_match.group(1)) if tags_match else []

        patterns = []
        patterns_match = re.search(
            r"\*\s*\*\*Patterns:\*\*\s*(.*?)(?=\n\*\s*\*\*Responses:\*\*)",
            block,
            flags=re.DOTALL,
        )
        if patterns_match:
            patterns = re.findall(r'\*\s+"([^"]+)"', patterns_match.group(1))

        response_match = re.search(r"\*\s*\*\*Responses:\*\*\s*(.+)", block, flags=re.DOTALL)
        response = response_match.group(1).strip() if response_match else ""
        response = re.sub(r"\n{3,}", "\n\n", response).strip()

        uid = stable_uid(dataset_name, intent, role_access or "", response)

        record = {
            "uid": uid,
            "dataset": dataset_name,
            "source_file": file_path.name,
            "category": category,
            "role_access": role_access,
            "intent": intent,
            "tags": tags,
            "patterns": patterns,
            "response": response,
        }
        record["embedding_text"] = build_embedding_text(record)
        records.append(record)

    return records


def load_all_records(dataset_dir: Path) -> List[Dict[str, Any]]:
    if not dataset_dir.exists():
        raise FileNotFoundError(f"Folder dataset tidak ditemukan: {dataset_dir}")

    md_files = sorted(dataset_dir.glob("Dataset_*.md"))
    if not md_files:
        md_files = sorted(dataset_dir.glob("*.md"))
    if not md_files:
        raise FileNotFoundError(f"Tidak ada file .md di folder: {dataset_dir}")

    records: List[Dict[str, Any]] = []
    for file_path in md_files:
        parsed = parse_markdown_file(file_path)
        print(f"- {file_path.name}: {len(parsed)} intent")
        records.extend(parsed)
    return records


def validate_records(records: List[Dict[str, Any]]) -> None:
    if not records:
        raise ValueError("Tidak ada records yang berhasil diparse.")

    empty_response = [r for r in records if not r.get("response")]
    empty_patterns = [r for r in records if not r.get("patterns")]

    if empty_response:
        print("PERINGATAN: Ada record tanpa response:")
        for r in empty_response[:10]:
            print(" ", r.get("dataset"), r.get("intent"))

    if empty_patterns:
        print("PERINGATAN: Ada record tanpa patterns:")
        for r in empty_patterns[:10]:
            print(" ", r.get("dataset"), r.get("intent"))

    seen = {}
    duplicate_patterns = []
    for r in records:
        for p in r.get("patterns", []):
            key = normalize_text(p)
            src = f"{r.get('dataset')}::{r.get('intent')}"
            if key in seen and seen[key] != src:
                duplicate_patterns.append((p, seen[key], src))
            else:
                seen[key] = src

    if duplicate_patterns:
        print(f"PERINGATAN: Ditemukan {len(duplicate_patterns)} duplikasi pattern.")
        for p, a, b in duplicate_patterns[:10]:
            print(f"  - {p!r} muncul di {a} dan {b}")


def check_ollama() -> None:
    url = f"{OLLAMA_BASE_URL}/api/tags"
    response = requests.get(url, timeout=30)
    response.raise_for_status()
    model_names = [m.get("name", "") for m in response.json().get("models", [])]
    print("Model Ollama tersedia:", model_names)
    if not any(EMBED_MODEL in m or m in EMBED_MODEL for m in model_names):
        print(f"PERINGATAN: model embedding {EMBED_MODEL!r} belum terlihat. Jalankan: ollama pull {EMBED_MODEL}")


def ollama_embed_batch(texts: List[str]) -> List[List[float]]:
    payload = {"model": EMBED_MODEL, "input": texts}
    response = requests.post(f"{OLLAMA_BASE_URL}/api/embed", json=payload, timeout=300)
    response.raise_for_status()
    data = response.json()
    embeddings = data.get("embeddings")
    if not embeddings:
        raise RuntimeError(f"Embedding kosong dari Ollama. Response: {data}")
    return embeddings


def batched(items: List[Any], size: int) -> Iterable[List[Any]]:
    for i in range(0, len(items), size):
        yield items[i : i + size]


def records_signature(records: List[Dict[str, Any]]) -> str:
    raw = json.dumps(
        {
            "embedding_code_version": EMBEDDING_CODE_VERSION,
            "records": [
                {
                    "dataset": r.get("dataset"),
                    "category": r.get("category"),
                    "role_access": r.get("role_access"),
                    "intent": r.get("intent"),
                    "tags": r.get("tags", []),
                    "patterns": r.get("patterns", []),
                    "response": r.get("response", ""),
                    "embedding_text": r.get("embedding_text", ""),
                }
                for r in records
            ],
        },
        ensure_ascii=False,
        sort_keys=True,
    )
    return hashlib.sha256(raw.encode("utf-8")).hexdigest()


def write_jsonl(records: List[Dict[str, Any]], path: Path) -> None:
    with path.open("w", encoding="utf-8") as f:
        for record in records:
            safe_record = {k: v for k, v in record.items() if k != "vector"}
            f.write(json.dumps(safe_record, ensure_ascii=False) + "\n")


def main() -> None:
    print("DATASET_DIR:", DATASET_DIR)
    print("STORAGE_DIR:", STORAGE_DIR)
    print("JSONL_PATH:", JSONL_PATH)
    print("EMBEDDING_PATH:", EMBEDDING_PATH)
    print("EMBED_MODEL:", EMBED_MODEL)

    check_ollama()

    print("\nMembaca dataset...")
    records = load_all_records(DATASET_DIR)
    validate_records(records)

    signature = records_signature(records)
    previous_signature = SIGNATURE_PATH.read_text(encoding="utf-8").strip() if SIGNATURE_PATH.exists() else ""
    if previous_signature == signature:
        print("Signature dataset sama dengan indexing terakhir. Tetap lanjut membuat ulang index lokal agar sinkron.")

    print(f"\nTotal records: {len(records)}")
    print("Membuat embedding...")

    texts = [r["embedding_text"] for r in records]
    vectors: List[List[float]] = []
    start = time.perf_counter()

    for batch_no, batch_texts in enumerate(batched(texts, BATCH_SIZE), start=1):
        batch_vectors = ollama_embed_batch(batch_texts)
        vectors.extend(batch_vectors)
        print(f"  Batch {batch_no}: {len(vectors)}/{len(texts)}")

    if len(vectors) != len(records):
        raise RuntimeError("Jumlah embedding tidak sama dengan jumlah records.")

    embeddings = np.array(vectors, dtype=np.float32)

    print(f"Embedding selesai dalam {time.perf_counter() - start:.2f} detik. Shape: {embeddings.shape}")

    np.save(EMBEDDING_PATH, embeddings)
    write_jsonl(records, JSONL_PATH)
    SIGNATURE_PATH.write_text(signature, encoding="utf-8")

    print("\nSelesai. Index lokal berhasil dibuat.")
    print("JSONL:", JSONL_PATH)
    print("Embeddings:", EMBEDDING_PATH)
    print("Signature:", SIGNATURE_PATH)


if __name__ == "__main__":
    main()
