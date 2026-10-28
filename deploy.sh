#!/bin/bash
set -e

# OZNA PWA Deploy Script
# Run on the server from the project root directory
# Usage: bash deploy.sh

ROOT="/var/www/api.ladyzenko.pro/tg/ozna"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "=== OZNA PWA Deploy ==="
echo "Target: $ROOT"
echo ""

# Check target directory
if [ ! -d "$ROOT" ]; then
    echo "ERROR: Directory $ROOT not found"
    exit 1
fi

# Check required source files
for f in "pwa_frontend.b64" "api/index.php" "api/controllers/TabelController.php" "api/data/activity-tree.json"; do
    if [ ! -f "$SCRIPT_DIR/$f" ]; then
        echo "ERROR: Source file not found: $SCRIPT_DIR/$f"
        exit 1
    fi
done

# 1. Deploy frontend (PWA)
echo "[1/4] Deploying frontend..."
APP_DIR="$ROOT/app"
mkdir -p "$APP_DIR"
rm -rf "$APP_DIR/assets" "$APP_DIR/icons"
rm -f "$APP_DIR"/*.js "$APP_DIR"/*.html "$APP_DIR"/*.json "$APP_DIR"/*.png
cat "$SCRIPT_DIR/pwa_frontend.b64" | base64 -d | tar -xzf - -C "$APP_DIR"
echo "      Frontend extracted to $APP_DIR"

# 2. Deploy API router
echo "[2/4] Deploying api/index.php..."
if [ "$SCRIPT_DIR/api/index.php" != "$ROOT/api/index.php" ]; then
    cp "$SCRIPT_DIR/api/index.php" "$ROOT/api/index.php"
else
    echo "      (source = target, skipping copy)"
fi
echo "      Done"

# 3. Deploy TabelController
echo "[3/4] Deploying api/controllers/TabelController.php..."
if [ "$SCRIPT_DIR/api/controllers/TabelController.php" != "$ROOT/api/controllers/TabelController.php" ]; then
    cp "$SCRIPT_DIR/api/controllers/TabelController.php" "$ROOT/api/controllers/TabelController.php"
else
    echo "      (source = target, skipping copy)"
fi
echo "      Done"

# 4. Deploy activity tree
echo "[4/4] Deploying api/data/activity-tree.json..."
if [ "$SCRIPT_DIR/api/data/activity-tree.json" != "$ROOT/api/data/activity-tree.json" ]; then
    cp "$SCRIPT_DIR/api/data/activity-tree.json" "$ROOT/api/data/activity-tree.json"
else
    echo "      (source = target, skipping copy)"
fi
echo "      Done"

echo ""
echo "=== Deploy complete ==="
echo ""

# 5. Database migration: rename "Режимная наладка" → "ИИ на рабочей среде" in tabel entries
echo "[DB] Renaming 'Режимная наладка' → 'ИИ на рабочей среде' in ozna_tabel..."
php -r "
\$c = require('$ROOT/api/config.php');
\$db = new mysqli(\$c['db']['host'], \$c['db']['username'], \$c['db']['password'], \$c['db']['database']);
if (\$db->connect_error) { echo \"  DB error: \" . \$db->connect_error . \"\n\"; exit(1); }
\$db->set_charset('utf8mb4');
\$count = \$db->query(\"SELECT COUNT(*) as cnt FROM ozna_tabel WHERE message LIKE '%Режимная наладка%'\")->fetch_assoc()['cnt'];
echo \"  Found \$count entries to update\n\";
if (\$count > 0) {
    \$db->query(\"UPDATE ozna_tabel SET message = REPLACE(message, 'Режимная наладка', 'ИИ на рабочей среде') WHERE message LIKE '%Режимная наладка%'\");
    echo \"  Updated \" . \$db->affected_rows . \" rows\n\";
}
\$db->close();
"
echo "      Done"

echo ""
echo "IMPORTANT: Users should clear browser site data or re-login"
echo "to pick up the updated activity tree (cached in localStorage)."
