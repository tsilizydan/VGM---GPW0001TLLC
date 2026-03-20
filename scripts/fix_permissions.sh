#!/bin/bash
#
# fix_permissions.sh — Vanilla Groupe Madagascar
# Run via SSH on Namecheap shared hosting:
#
#   bash scripts/fix_permissions.sh
#
# This script corrects file and directory permissions to prevent
# 403 Forbidden errors caused by too-restrictive permissions.
# ================================================================

# Detect project root (directory containing this script's parent)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo "==================================="
echo " Vanilla Groupe Madagascar"
echo " Permission Fix Script"
echo "==================================="
echo "Project root: $PROJECT_ROOT"
echo ""

# ── Directories: 755 ────────────────────────────────────────────
echo "[1/4] Setting directories to 755..."
find "$PROJECT_ROOT" -type d \
    ! -path "*/node_modules/*" \
    ! -path "*/.git/*" \
    -exec chmod 755 {} \;

# ── Files: 644 ──────────────────────────────────────────────────
echo "[2/4] Setting files to 644..."
find "$PROJECT_ROOT" -type f \
    ! -path "*/node_modules/*" \
    ! -path "*/.git/*" \
    -exec chmod 644 {} \;

# ── Writable directories: 775 ───────────────────────────────────
echo "[3/4] Setting writable directories to 775..."
WRITABLE_DIRS=(
    "$PROJECT_ROOT/storage"
    "$PROJECT_ROOT/storage/cache"
    "$PROJECT_ROOT/storage/logs"
    "$PROJECT_ROOT/public/assets/img"
    "$PROJECT_ROOT/public/assets/img/products"
    "$PROJECT_ROOT/public/assets/img/recipes"
    "$PROJECT_ROOT/public/assets/cache"
)

for DIR in "${WRITABLE_DIRS[@]}"; do
    if [ -d "$DIR" ]; then
        chmod 775 "$DIR"
        echo "  → 775 $DIR"
    else
        mkdir -p "$DIR" && chmod 775 "$DIR"
        echo "  → created + 775 $DIR"
    fi
done

# ── Protect .env: 600 (owner only) ──────────────────────────────
echo "[4/4] Protecting sensitive files..."
if [ -f "$PROJECT_ROOT/.env" ]; then
    chmod 600 "$PROJECT_ROOT/.env"
    echo "  → 600 .env"
fi

# ── Create log/cache dirs if missing ────────────────────────────
for DIR in storage/logs storage/cache public/assets/cache; do
    FULL="$PROJECT_ROOT/$DIR"
    if [ ! -d "$FULL" ]; then
        mkdir -p "$FULL" && chmod 775 "$FULL"
        echo "  → created $FULL"
    fi
done

# ── Verify critical files are readable ──────────────────────────
echo ""
echo "=== Critical file check ==="
CHECK_FILES=(
    "public/index.php"
    "public/.htaccess"
    ".htaccess"
    "core/Application.php"
    "config/database.php"
)

ALL_OK=true
for FILE in "${CHECK_FILES[@]}"; do
    FULL="$PROJECT_ROOT/$FILE"
    if [ -r "$FULL" ]; then
        PERMS=$(stat -c "%a" "$FULL" 2>/dev/null || stat -f "%OLp" "$FULL" 2>/dev/null)
        echo "  ✅  $FILE ($PERMS)"
    else
        echo "  ❌  MISSING or UNREADABLE: $FILE"
        ALL_OK=false
    fi
done

echo ""
if [ "$ALL_OK" = true ]; then
    echo "✅  All permissions fixed. No 403 issues expected from permissions."
else
    echo "⚠️   Some files are missing. Check the output above."
fi
echo ""
echo "Log results at: $PROJECT_ROOT/storage/logs/403_debug.log"
echo "Run diagnose.php for full server-side diagnostic."
