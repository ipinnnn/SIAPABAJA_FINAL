"""
api.py

FastAPI backend chatbot RAG SIAPABAJA.
Sinkron dengan pipeline ChatBOT_SIAPABAJA(16).ipynb.
- FastAPI, bukan Flask
- Local numpy vector search, bukan Qdrant
- Ollama untuk embedding dan generasi

Jalankan dari folder chatbot_siapabaja:
    python -m uvicorn api:app --reload --host 0.0.0.0 --port 5000
"""

from __future__ import annotations

import json
import os
import re
import time
from difflib import SequenceMatcher
from pathlib import Path
from typing import Any, Dict, List, Optional, Tuple

import numpy as np
import requests
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field

BASE_DIR = Path(__file__).resolve().parent
STORAGE_DIR = Path(os.getenv("STORAGE_DIR", str(BASE_DIR / "storage")))
JSONL_PATH = STORAGE_DIR / "dataset_siapabaja.jsonl"
EMBEDDING_PATH = STORAGE_DIR / "embeddings.npy"

OLLAMA_BASE_URL = os.getenv("OLLAMA_BASE_URL", "http://localhost:11434")
EMBED_MODEL = os.getenv("EMBED_MODEL", "nomic-embed-text")
CHAT_MODEL = os.getenv("CHAT_MODEL", "llama3.1:8b")

TOP_K = int(os.getenv("TOP_K", "6"))
CONTEXT_K = int(os.getenv("CONTEXT_K", "3"))
SEARCH_LIMIT = int(os.getenv("SEARCH_LIMIT", "120"))
SIMILARITY_THRESHOLD = float(os.getenv("SIMILARITY_THRESHOLD", "0.18"))

DIRECT_ANSWER_MODE = os.getenv("DIRECT_ANSWER_MODE", "true").lower() in {"1", "true", "yes", "y"}
DIRECT_MIN_SCORE = float(os.getenv("DIRECT_MIN_SCORE", "2.30"))
DIRECT_MIN_GAP = float(os.getenv("DIRECT_MIN_GAP", "0.35"))

NUM_PREDICT = int(os.getenv("NUM_PREDICT", "600"))
TEMPERATURE = float(os.getenv("TEMPERATURE", "0.1"))
REPEAT_PENALTY = float(os.getenv("REPEAT_PENALTY", "1.15"))

ROLE_OPTIONS = {
    "1": "user", "user": "user", "publik": "user", "public": "user",
    "guest": "user", "pengunjung": "user", "user publik": "user", "user_publik": "user",
    "2": "unit", "unit": "unit",
    "3": "ppk", "ppk": "ppk",
    "4": "super_admin", "superadmin": "super_admin", "super admin": "super_admin",
    "super_admin": "super_admin", "admin": "super_admin",
}

SYSTEM_PROMPT = """
Anda adalah Asisten AI resmi sistem SIAPABAJA (Sistem Informasi Arsip Pengadaan Barang/Jasa).

TUGAS:
Jawab pertanyaan pengguna HANYA berdasarkan KONTEKS RESMI yang diberikan.

ATURAN WAJIB:
1. Jangan membuat fitur, menu, role, tombol, atau aturan yang TIDAK ADA di konteks.
2. Jika role pengguna ditentukan, prioritaskan aturan akses untuk role tersebut.
3. Jika konteks Role dan Workflow bertentangan, aturan ROLE menang — jangan berikan langkah workflow jika role tidak punya wewenang.
4. Jika konteks menyatakan suatu fitur tidak tersedia, jawab sesuai konteks. JANGAN mengarang alternatif.
5. Jika konteks tidak cukup atau tidak relevan, jawab: "Informasi tersebut tidak tersedia pada dataset SIAPABAJA."
6. Jawab dalam Bahasa Indonesia, singkat, jelas, langsung menjawab pertanyaan.
7. Jangan mengulang pertanyaan. Jangan menyebut nomor konteks ([1], [2]) di dalam jawaban.
8. Jika ada beberapa konteks, utamakan konteks peringkat [1].
""".strip()

app = FastAPI(title="SIAPABAJA RAG Chatbot API", version="2.0.0-local-numpy")
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

ALL_RECORDS: List[Dict[str, Any]] = []
EMBEDDINGS: Optional[np.ndarray] = None


class ChatRequest(BaseModel):
    question: Optional[str] = Field(default=None)
    query: Optional[str] = Field(default=None)
    role: Optional[str] = Field(default=None, description="user, unit, ppk, super_admin")
    top_k: int = Field(default=TOP_K, ge=1, le=20)
    show_sources: bool = True
    force_generate: bool = False


class ChatResponse(BaseModel):
    success: bool = True
    answer: str
    role: Optional[str]
    query_category: str
    answer_source: str
    retrieval_time: float
    generation_time: float
    total_time: float
    sources: Optional[List[Dict[str, Any]]] = None


def load_records_from_jsonl(path: Path) -> List[Dict[str, Any]]:
    if not path.exists():
        raise FileNotFoundError(f"File {path} tidak ditemukan. Jalankan `python indexer.py` terlebih dahulu.")
    records = []
    with path.open("r", encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if line:
                records.append(json.loads(line))
    return records


@app.on_event("startup")
def startup_event() -> None:
    global ALL_RECORDS, EMBEDDINGS

    try:
        ALL_RECORDS = load_records_from_jsonl(JSONL_PATH)
        print(f"Loaded records: {len(ALL_RECORDS)} dari {JSONL_PATH}")
    except Exception as exc:
        print("PERINGATAN load records:", exc)
        ALL_RECORDS = []

    try:
        if not EMBEDDING_PATH.exists():
            raise FileNotFoundError(f"File embedding tidak ditemukan: {EMBEDDING_PATH}. Jalankan python indexer.py terlebih dahulu.")
        EMBEDDINGS = np.load(EMBEDDING_PATH).astype(np.float32)
        print(f"Loaded embeddings: {EMBEDDINGS.shape} dari {EMBEDDING_PATH}")
        if len(ALL_RECORDS) != EMBEDDINGS.shape[0]:
            print(f"PERINGATAN: records={len(ALL_RECORDS)} tidak sama dengan embeddings={EMBEDDINGS.shape[0]}")
    except Exception as exc:
        print("PERINGATAN load embeddings:", exc)
        EMBEDDINGS = None


def normalize_text(text: str) -> str:
    return re.sub(r"\s+", " ", str(text or "").lower().strip())


def normalize_role(role: Optional[str]) -> Optional[str]:
    if not role:
        return None
    return ROLE_OPTIONS.get(normalize_text(role))


def infer_role_from_query(query: str) -> Optional[str]:
    q = normalize_text(query)
    patterns = [
        (r"\b(user publik|pengunjung|guest|tamu)\b", "user"),
        (r"\bsuper[_ ]?admin\b", "super_admin"),
        (r"\bppk\b", "ppk"),
        (r"\bunit\b", "unit"),
    ]
    for pat, role in patterns:
        if re.search(pat, q):
            return role
    return None


def rule_based_category(query: str) -> Optional[str]:
    q = normalize_text(query)

    strong_faq = [
        "kenapa", "mengapa", "tidak bisa", "gagal", "error", "forbidden",
        "tidak muncul", "ditolak", "tidak terunduh", "lupa password",
        "invalid credentials", "tidak aktif", "bermasalah", "tidak merespons",
        "tidak tersimpan", "tidak terbuka", "tidak tampil",
        "terhapus", "dikembalikan", "dipulihkan",
    ]
    if any(k in q for k in strong_faq):
        return "faq"

    strong_workflow = [
        "bagaimana cara", "cara ", "langkah-langkah", "langkah ",
        "prosedur", "bagaimana membuka", "bagaimana upload",
        "bagaimana menambah", "bagaimana menghapus", "bagaimana edit",
        "bagaimana export", "bagaimana reset", "bagaimana mengubah status",
        "bagaimana filter", "gimana ", "bagaimana login", "bagaimana logout",
    ]
    if any(k in q for k in strong_workflow):
        return "workflow"

    strong_feature = [
        "apa itu ", "fitur apa", "apa saja fitur", "fungsi utama",
        "cakupan sistem", "digunakan untuk apa", "apa isi menu",
        "apa saja menu", "apa format", "format file", "format dokumen",
        "format apa", "apa itu status", "apa saja statistik",
        "statistik apa", "apa isi dashboard", "apa fitur",
        "batas ukuran", "ukuran maksimal",
    ]
    if any(k in q for k in strong_feature):
        return "feature"

    faq_who = ["siapa yang bisa melihat", "siapa yang dapat melihat", "siapa yang berhak"]
    if any(k in q for k in faq_who):
        return "faq"

    role_signals = [
        "apakah", "bisakah", "bolehkah", "dapatkah",
        "hak akses", "kewenangan", "wewenang",
        "siapa yang bisa", "siapa yang boleh", "siapa saja yang",
        "bisa tidak", "bisa nggak", "boleh tidak",
        "bisa ", "boleh ", "dapat ",
    ]
    action_words = [
        "mengelola", "mengedit", "menghapus", "menambah", "melihat",
        "mengakses", "mengubah", "upload", "download", "export",
        "login", "bulk delete", "hapus massal", "crud", "reset password",
        "membuat akun", "akun", "menu", "fitur", "arsip",
    ]
    if any(k in q for k in role_signals) and any(k in q for k in action_words):
        return "role"

    return None


def detect_query_category(query: str) -> str:
    return rule_based_category(query) or "general"


def get_candidate_indices(records: List[Dict[str, Any]], query_category: str, selected_role: Optional[str]) -> List[int]:
    indices: List[int] = []
    for idx, rec in enumerate(records):
        cat = rec.get("category")
        role = rec.get("role_access")
        keep = False

        if query_category == "role":
            if selected_role:
                keep = (cat == "role" and role == selected_role)
            else:
                keep = (cat == "role") or (cat == "faq")
        elif query_category == "workflow":
            if selected_role == "user":
                keep = (cat == "role" and role == "user")
            elif selected_role:
                keep = (cat == "workflow") or (cat == "role" and role == selected_role)
            else:
                keep = (cat == "workflow")
        elif query_category == "faq":
            if selected_role:
                keep = (cat == "faq") or (cat == "role" and role == selected_role)
            else:
                keep = (cat == "faq") or (cat == "feature")
        elif query_category == "feature":
            if selected_role:
                keep = (cat == "feature") or (cat == "role" and role == selected_role)
            else:
                keep = (cat == "feature")
        else:
            if selected_role:
                keep = (cat == "role" and role == selected_role) or cat in ["faq", "feature", "workflow"]
            else:
                keep = True
        if keep:
            indices.append(idx)
    return indices or list(range(len(records)))


def cosine_scores(query_vec: List[float], matrix: np.ndarray) -> np.ndarray:
    q = np.array(query_vec, dtype=np.float32)
    q /= (np.linalg.norm(q) + 1e-12)
    m = matrix / (np.linalg.norm(matrix, axis=1, keepdims=True) + 1e-12)
    return np.dot(m, q)


def metadata_boost(record: Dict[str, Any], query_category: str, selected_role: Optional[str]) -> float:
    cat = record.get("category")
    role = record.get("role_access")
    boost = 0.0
    cat_bonus = {"role": 0.55, "workflow": 0.45, "faq": 0.45, "feature": 0.40}
    if cat == query_category:
        boost += cat_bonus.get(cat, 0.0)

    if selected_role:
        if cat == "role" and role == selected_role:
            boost += 0.85 if query_category == "role" else 0.05
        elif cat == "role" and role and role != selected_role:
            boost -= 0.85
        if selected_role == "user" and cat == "workflow":
            boost -= 0.60
        if selected_role in ("unit", "ppk", "super_admin") and query_category == "workflow" and cat == "workflow":
            boost += 0.35
    return boost


def pattern_similarity_bonus(query: str, record: Dict[str, Any]) -> Tuple[float, float, str]:
    q = normalize_text(query)
    best_ratio = 0.0
    for pattern in record.get("patterns", []):
        ratio = SequenceMatcher(None, q, normalize_text(pattern)).ratio()
        if ratio > best_ratio:
            best_ratio = ratio
    if best_ratio >= 0.97:
        return 1.50, best_ratio, "exact_or_near_pattern"
    if best_ratio >= 0.88:
        return 0.90, best_ratio, "soft_pattern_high"
    if best_ratio >= 0.78:
        return 0.45, best_ratio, "soft_pattern_mid"
    if best_ratio >= 0.65:
        return 0.20, best_ratio, "soft_pattern_low"
    return 0.0, best_ratio, "semantic"


def tag_overlap_score(query: str, record: Dict[str, Any]) -> float:
    q_words = set(normalize_text(query).split())
    tags = [normalize_text(t).replace("_", " ") for t in record.get("tags", [])]
    tag_words = set(" ".join(tags).split())
    if not tag_words:
        return 0.0
    return min(len(q_words & tag_words) * 0.08, 0.40)


def exact_pattern_matches(query: str, records: List[Dict[str, Any]], candidate_indices: List[int]) -> List[Tuple[int, Dict[str, Any]]]:
    q = normalize_text(query)
    matches = []
    for idx in candidate_indices:
        rec = records[idx]
        for p in rec.get("patterns", []):
            if normalize_text(p) == q:
                matches.append((idx, rec))
                break
    return matches


def is_procedural_workflow_query(query: str) -> bool:
    q = normalize_text(query)
    procedural_signals = [
        "bagaimana cara", "cara ", "langkah-langkah", "langkah ",
        "prosedur", "apa langkah", "bagaimana upload",
    ]
    return any(signal in q for signal in procedural_signals)


def sort_retrieval_results(results: List[Dict[str, Any]], query: str, query_category: str, selected_role: Optional[str]) -> List[Dict[str, Any]]:
    sorted_results = sorted(results, key=lambda x: x["score"], reverse=True)

    if query_category != "workflow" or selected_role == "user" or not is_procedural_workflow_query(query):
        return sorted_results
    if not sorted_results or sorted_results[0].get("category") == "workflow":
        return sorted_results

    workflow_candidates = [
        r for r in sorted_results
        if r.get("category") == "workflow"
        and (r.get("pattern_bonus", 0) > 0 or r.get("pattern_ratio", 0) >= 0.78)
    ]
    if not workflow_candidates:
        return sorted_results

    best_workflow = workflow_candidates[0]
    top_score = sorted_results[0].get("score", 0)
    if best_workflow.get("score", 0) < top_score - 0.75:
        return sorted_results

    return [best_workflow] + [r for r in sorted_results if r is not best_workflow]


def keyword_intent_bonus(query: str, record: Dict[str, Any]) -> float:
    q = normalize_text(query)
    intent = record.get("intent", "")
    cat = record.get("category", "")
    bonus = 0.0

    login_kw = ["login", "masuk", "sign in", "cara masuk", "cara login"]
    logout_kw = ["logout", "keluar", "sign out", "akhiri sesi"]
    if any(k in q for k in login_kw) and not any(k in q for k in logout_kw):
        if intent == "login": bonus += 1.20
        if intent == "logout": bonus -= 1.20
    if any(k in q for k in logout_kw):
        if intent == "logout": bonus += 1.20
        if intent == "login": bonus -= 1.20

    upload_kw = ["upload", "unggah", "mengunggah", "lampiran", "dokumen"]
    if any(k in q for k in upload_kw):
        if intent == "document_upload_denied_user": bonus += 1.00
        if intent == "internal_login_denied_user": bonus -= 0.80

    delete_kw = ["terhapus", "dikembalikan", "pulihkan", "recycle", "hapus permanen"]
    if any(k in q for k in delete_kw):
        if intent == "delete_permanent_warning": bonus += 1.20
        if intent == "archive_search_filter_feature": bonus -= 1.00
        if "master_data" in intent: bonus -= 1.00

    akun_kw = ["akun", "tidak aktif", "nonaktif", "akun bermasalah", "status akun"]
    if any(k in q for k in akun_kw):
        if intent == "account_status_issue": bonus += 1.00
        if intent == "search_filter_no_result": bonus -= 0.80

    if "gagal" in q and any(k in q for k in ["upload", "unggah", "file"]):
        if intent == "upload_failed": bonus += 1.00
        if intent == "preview_download_issue": bonus -= 0.80

    format_kw = ["format file", "format dokumen", "format apa", "bisa diunggah", "bisa diupload", "ukuran", "batas ukuran"]
    if any(k in q for k in format_kw) and cat == "feature":
        if intent == "document_upload_feature": bonus += 1.00
    if any(k in q for k in format_kw) and cat == "role":
        bonus -= 0.80

    kelola_menu_kw = ["kelola menu", "master data", "menu kelola"]
    if any(k in q for k in kelola_menu_kw):
        if intent == "super_admin_master_data_management": bonus += 0.80
        if intent == "master_data_general": bonus -= 0.60

    if "reset password" in q or ("password" in q and ("unit" in q or "ppk" in q or "akun" in q)):
        if intent == "super_admin_reset_password_scope": bonus += 1.00
        if intent == "super_admin_self_account_management": bonus -= 0.60

    siapa_kw = ["siapa yang bisa melihat", "siapa saja yang bisa", "siapa yang dapat melihat"]
    if any(k in q for k in siapa_kw) and "privat" in q:
        if intent == "private_archive_visibility": bonus += 1.00
        if intent == "unit_visibility_public_private": bonus -= 0.40

    publik_kw = ["user publik", "pengunjung", "guest", "tamu"]
    if any(k in q for k in publik_kw):
        if intent == "super_admin_user_public_scope": bonus += 1.00
        if intent == "super_admin_account_management": bonus -= 0.80

    return bonus


def ollama_embedding(text: str) -> List[float]:
    response = requests.post(
        f"{OLLAMA_BASE_URL}/api/embed",
        json={"model": EMBED_MODEL, "input": text},
        timeout=120,
    )
    response.raise_for_status()
    data = response.json()
    embeddings = data.get("embeddings")
    if not embeddings:
        raise RuntimeError(f"Embedding kosong dari Ollama: {data}")
    return embeddings[0]


def ollama_generate(prompt: str) -> str:
    response = requests.post(
        f"{OLLAMA_BASE_URL}/api/generate",
        json={
            "model": CHAT_MODEL,
            "prompt": prompt,
            "stream": False,
            "options": {
                "temperature": TEMPERATURE,
                "top_p": 0.9,
                "num_predict": NUM_PREDICT,
                "repeat_penalty": REPEAT_PENALTY,
            },
        },
        timeout=300,
    )
    if response.status_code != 200:
        raise RuntimeError(f"Ollama error {response.status_code}: {response.text[:500]}")
    return response.json().get("response", "").strip()


def retrieve_context(query: str, selected_role: Optional[str] = None, top_k: int = TOP_K) -> Dict[str, Any]:
    if EMBEDDINGS is None:
        raise RuntimeError("Embeddings belum dimuat. Jalankan python indexer.py, lalu restart FastAPI.")
    if not ALL_RECORDS:
        raise RuntimeError("Records belum dimuat. Jalankan python indexer.py, lalu restart FastAPI.")

    norm_role = normalize_role(selected_role) or infer_role_from_query(query)
    query_vec = ollama_embedding(query)
    query_category = detect_query_category(query)
    cand_idx = get_candidate_indices(ALL_RECORDS, query_category, norm_role)
    base_scores = cosine_scores(query_vec, EMBEDDINGS)

    results = []
    for idx in cand_idx:
        rec = ALL_RECORDS[idx]
        raw_score = float(base_scores[idx])
        meta = metadata_boost(rec, query_category, norm_role)
        p_bonus, p_ratio, match_type = pattern_similarity_bonus(query, rec)
        tag_bonus = tag_overlap_score(query, rec)
        kw_bonus = keyword_intent_bonus(query, rec)
        final = raw_score + meta + p_bonus + tag_bonus + kw_bonus

        if final < SIMILARITY_THRESHOLD and p_bonus <= 0 and kw_bonus <= 0:
            continue

        results.append({
            "score": final,
            "raw_score": raw_score,
            "meta_boost": meta,
            "pattern_bonus": p_bonus,
            "tag_bonus": tag_bonus,
            "kw_bonus": kw_bonus,
            "pattern_ratio": p_ratio,
            "match_type": match_type,
            "dataset": rec.get("dataset"),
            "category": rec.get("category"),
            "role_access": rec.get("role_access"),
            "intent": rec.get("intent"),
            "tags": rec.get("tags", []),
            "response": rec.get("response"),
            "record": rec,
        })

    for idx, rec in exact_pattern_matches(query, ALL_RECORDS, cand_idx):
        meta = metadata_boost(rec, query_category, norm_role)
        exact_entry = {
            "score": 3.50 + meta,
            "raw_score": 1.0,
            "meta_boost": meta,
            "pattern_bonus": 1.50,
            "tag_bonus": 0.0,
            "kw_bonus": 0.0,
            "pattern_ratio": 1.0,
            "match_type": "exact_pattern",
            "dataset": rec.get("dataset"),
            "category": rec.get("category"),
            "role_access": rec.get("role_access"),
            "intent": rec.get("intent"),
            "tags": rec.get("tags", []),
            "response": rec.get("response"),
            "record": rec,
        }
        results = [r for r in results if not (r.get("dataset") == exact_entry["dataset"] and r.get("intent") == exact_entry["intent"])]
        results.append(exact_entry)

    results = sort_retrieval_results(results, query, query_category, norm_role)[:top_k]
    return {
        "query": query,
        "selected_role": norm_role,
        "query_category": query_category,
        "candidate_count": len(cand_idx),
        "results": results,
    }


def build_context_text(results: List[Dict[str, Any]]) -> str:
    parts = []
    for i, r in enumerate(results[:CONTEXT_K], 1):
        parts.append(
            f"[{i}]\n"
            f"Dataset: {r.get('dataset')}\n"
            f"Kategori: {r.get('category')}\n"
            f"Role: {r.get('role_access') or '-'}\n"
            f"Intent: {r.get('intent')}\n"
            f"Jawaban Resmi: {r.get('response')}"
        )
    return "\n\n".join(parts)


def build_prompt(query: str, retrieved: Dict[str, Any]) -> str:
    return (
        f"{SYSTEM_PROMPT}\n\n"
        f"Role pengguna saat ini: {retrieved.get('selected_role') or 'tidak ditentukan'}\n"
        f"Jenis pertanyaan terdeteksi: {retrieved.get('query_category')}\n\n"
        f"KONTEKS RESMI:\n{build_context_text(retrieved.get('results', []))}\n\n"
        f"PERTANYAAN USER:\n{query}\n\n"
        "JAWABAN:"
    )


def should_direct_answer(retrieved: Dict[str, Any]) -> bool:
    if not DIRECT_ANSWER_MODE:
        return False
    results = retrieved.get("results", [])
    if not results:
        return False
    top = results[0]
    gap = top["score"] - (results[1]["score"] if len(results) > 1 else -999)
    mt = top.get("match_type", "")
    if mt == "exact_pattern":
        return True
    if mt == "exact_or_near_pattern" and top.get("pattern_ratio", 0) >= 0.97:
        return True
    if top["score"] >= DIRECT_MIN_SCORE and gap >= DIRECT_MIN_GAP:
        return True
    return False


def sanitize_answer_text(answer: str) -> str:
    text = str(answer or "").strip()
    text = re.sub(r"(?is)(?:^|(?<=[.!?])\s+)informasi[^.!?]*\bkonteks\s*\[\d+\][^.!?]*[.!?]\s*", "", text)
    text = re.sub(r"(?i)\s*(?:berdasarkan|pada|di|dari)\s+konteks\s*\[\d+\]\s*,?\s*", " ", text)
    text = re.sub(r"\s*\[\d+\]", "", text)
    text = re.sub(r"[ \t]{2,}", " ", text)
    text = re.sub(r"\n{3,}", "\n\n", text)
    return text.strip()


def answer_question(query: str, selected_role: Optional[str] = None, top_k: int = TOP_K, force_generate: bool = False) -> Dict[str, Any]:
    total_start = time.perf_counter()
    retrieval_start = time.perf_counter()
    retrieved = retrieve_context(query, selected_role=selected_role, top_k=top_k)
    retrieval_time = time.perf_counter() - retrieval_start

    if not retrieved.get("results"):
        return {
            "query": query,
            "selected_role": normalize_role(selected_role) or infer_role_from_query(query),
            "query_category": detect_query_category(query),
            "answer": "Informasi tersebut tidak tersedia pada dataset SIAPABAJA.",
            "answer_source": "fallback_no_context",
            "retrieved": retrieved,
            "retrieval_time": retrieval_time,
            "generation_time": 0.0,
            "total_time": time.perf_counter() - total_start,
        }

    generation_start = time.perf_counter()
    if should_direct_answer(retrieved) and not force_generate:
        answer = retrieved["results"][0].get("response") or "Informasi tersebut tidak tersedia pada dataset SIAPABAJA."
        source = "direct_context"
    else:
        answer = ollama_generate(build_prompt(query, retrieved))
        source = "ollama_generate"
    answer = sanitize_answer_text(answer)
    generation_time = time.perf_counter() - generation_start

    return {
        "query": query,
        "selected_role": retrieved.get("selected_role"),
        "query_category": retrieved.get("query_category"),
        "answer": answer,
        "answer_source": source,
        "retrieved": retrieved,
        "retrieval_time": retrieval_time,
        "generation_time": generation_time,
        "total_time": time.perf_counter() - total_start,
    }


@app.get("/health")
def health() -> Dict[str, Any]:
    embedding_shape = list(EMBEDDINGS.shape) if EMBEDDINGS is not None else None
    return {
        "status": "ok" if ALL_RECORDS and EMBEDDINGS is not None else "not_ready",
        "storage_mode": "local_numpy",
        "records_loaded": len(ALL_RECORDS),
        "embedding_loaded": EMBEDDINGS is not None,
        "embedding_shape": embedding_shape,
        "jsonl_path": str(JSONL_PATH),
        "embedding_path": str(EMBEDDING_PATH),
        "chat_model": CHAT_MODEL,
        "embed_model": EMBED_MODEL,
    }


@app.get("/api/health")
def api_health() -> Dict[str, Any]:
    return health()


def _make_response(result: Dict[str, Any], show_sources: bool) -> ChatResponse:
    sources = None
    if show_sources:
        sources = []
        for r in result.get("retrieved", {}).get("results", []):
            sources.append({
                "score": round(r.get("score", 0.0), 4),
                "raw_score": round(r.get("raw_score", 0.0), 4),
                "meta_boost": round(r.get("meta_boost", 0.0), 4),
                "pattern_bonus": round(r.get("pattern_bonus", 0.0), 4),
                "tag_bonus": round(r.get("tag_bonus", 0.0), 4),
                "kw_bonus": round(r.get("kw_bonus", 0.0), 4),
                "pattern_ratio": round(r.get("pattern_ratio", 0.0), 4),
                "match_type": r.get("match_type"),
                "dataset": r.get("dataset"),
                "category": r.get("category"),
                "role_access": r.get("role_access"),
                "intent": r.get("intent"),
                "response": r.get("response"),
            })

    return ChatResponse(
        success=True,
        answer=result["answer"],
        role=result.get("selected_role"),
        query_category=result.get("query_category"),
        answer_source=result.get("answer_source"),
        retrieval_time=round(result.get("retrieval_time", 0.0), 4),
        generation_time=round(result.get("generation_time", 0.0), 4),
        total_time=round(result.get("total_time", 0.0), 4),
        sources=sources,
    )


@app.post("/api/chat", response_model=ChatResponse)
def api_chat(request: ChatRequest) -> ChatResponse:
    question = (request.question or request.query or "").strip()
    if not question:
        raise HTTPException(status_code=400, detail="Pertanyaan tidak boleh kosong.")

    try:
        result = answer_question(
            query=question,
            selected_role=request.role,
            top_k=request.top_k,
            force_generate=request.force_generate,
        )
        return _make_response(result, request.show_sources)
    except requests.HTTPError as exc:
        raise HTTPException(status_code=502, detail=f"Ollama HTTP error: {exc}")
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc))


@app.post("/chat", response_model=ChatResponse)
def chat_alias(request: ChatRequest) -> ChatResponse:
    return api_chat(request)


if __name__ == "__main__":
    import uvicorn
    uvicorn.run("api:app", host="0.0.0.0", port=5000, reload=False)
