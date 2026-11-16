#!/usr/bin/env bash
# Theme checks: PHP lint, theme.json validity, no em dashes.
set -u
cd "$(dirname "$0")/.."
fail=0

php_files=$(find . -name '*.php' -not -path './.git/*')
if [ -z "$php_files" ]; then echo "FAIL: no PHP files found"; fail=1; fi
for f in $php_files; do
  php -l "$f" >/dev/null || { echo "FAIL: php -l $f"; fail=1; }
done

if [ ! -f theme.json ]; then
  echo "FAIL: theme.json missing"; fail=1
elif ! python3 -c 'import json,sys; d=json.load(open("theme.json")); sys.exit(0 if d.get("version")==3 else 1)'; then
  echo "FAIL: theme.json is not valid JSON with version 3"; fail=1
fi

for f in style.css functions.php theme.json templates/index.html parts/header.html parts/footer.html screenshot.png README.md; do
  [ -f "$f" ] || { echo "FAIL: missing $f"; fail=1; }
done

if grep -rIl $'\xe2\x80\x94' --exclude-dir=.git --exclude-dir=fonts . ; then
  echo "FAIL: em dash found in the files above"; fail=1
fi

if [ "$fail" -eq 0 ]; then echo "PASS"; else exit 1; fi
