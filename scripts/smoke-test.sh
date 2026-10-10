#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Smoke test API Waspada.
#
# Mengetes endpoint utama lewat HTTP dan memastikan status code sesuai
# harapan. Berhenti (exit 1) pada kegagalan pertama.
#
# Penggunaan:
#   bash scripts/smoke-test.sh
#   BASE_URL=http://waspada.test bash scripts/smoke-test.sh
# ---------------------------------------------------------------------------
set -u

BASE_URL="${BASE_URL:-http://localhost:8000}"
BASE_URL="${BASE_URL%/}"  # buang trailing slash jika ada

PASS=0
FAIL=0

step() {
    # $1 = nama step, $2 = status aktual, $3 = status yang diharapkan
    local name="$1" actual="$2" expected="$3"
    if [ "$actual" = "$expected" ]; then
        PASS=$((PASS + 1))
        echo "[PASS] $name (HTTP $actual)"
    else
        FAIL=$((FAIL + 1))
        echo "[FAIL] $name — dapat HTTP $actual, harapkan HTTP $expected"
        exit 1
    fi
}

echo "Smoke test Waspada → $BASE_URL"
echo "----------------------------------------"

# 1. Halaman utama
code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/")
step "GET /" "$code" "200"

# 2. Cek nomor valid → verdict
body=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/api/check" \
    -H "Content-Type: application/json" -H "Accept: application/json" \
    -d '{"type":"phone","value":"081234567890"}')
code=$(echo "$body" | tail -n1)
step "POST /api/check (nomor valid)" "$code" "200"
if ! echo "$body" | grep -q '"verdict"'; then
    echo "[FAIL] POST /api/check — respons tidak mengandung verdict"
    exit 1
fi
echo "[PASS] POST /api/check — respons mengandung verdict"

# 3. Cek input invalid → error validasi yang ramah
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL/api/check" \
    -H "Content-Type: application/json" -H "Accept: application/json" \
    -d '{"type":"phone","value":""}')
step "POST /api/check (nilai kosong)" "$code" "422"

# 4. Kirim laporan komunitas
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL/api/reports" \
    -H "Content-Type: application/json" -H "Accept: application/json" \
    -d '{"type":"phone","value":"081234567890","category":"phising","description":"Nomor ini mengirim pesan undian berhadiah palsu dan meminta transfer.","reporter_name":"QA Smoke Test"}')
step "POST /api/reports" "$code" "201"

# 5. Daftar laporan komunitas
code=$(curl -s -o /dev/null -w "%{http_code}" -H "Accept: application/json" \
    "$BASE_URL/api/reports")
step "GET /api/reports" "$code" "200"

# 6. Threat feed publik
code=$(curl -s -o /dev/null -w "%{http_code}" -H "Accept: application/json" \
    "$BASE_URL/api/threat-feed")
step "GET /api/threat-feed" "$code" "200"

echo "----------------------------------------"
echo "Semua smoke test lolos ($PASS checks)."
