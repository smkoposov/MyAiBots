#!/usr/bin/env bash
set -e

REPO_DIR="/var/www/test.myaibots.ru"
WEB_ROOT="/var/www/test.myaibots.ru/public"
BRANCH="test"
REV_FILE="$WEB_ROOT/REVISION"

echo "[deploy:test] $(date -Iseconds) start"
cd "$REPO_DIR"

# тянем всё самое свежее
git fetch --all --prune

# выравниваем локальную ветку под origin/test
git checkout "$BRANCH" || git checkout -b "$BRANCH" "origin/$BRANCH"
git reset --hard "origin/$BRANCH"

# выкладываем публичные файлы
rsync -a --delete "$REPO_DIR/public/" "$WEB_ROOT/"

# подробная метка версии
SHA=$(git rev-parse --short HEAD)
DATE=$(date -Iseconds)
AUTHOR=$(git log -1 --pretty=format:'%an <%ae>')
MESSAGE=$(git log -1 --pretty=format:'%s')

{
  echo "Revision: $SHA"
  echo "Deployed: $DATE"
  echo "Branch:   $BRANCH"
  echo "Author:   $AUTHOR"
  echo "Message:  $MESSAGE"
} > "$REV_FILE"

echo "[deploy:test] revision $SHA"
echo "[deploy:test] done"
