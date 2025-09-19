#!/bin/bash
PMD_BIN=/tmp/pmd-bin-7.17.0/bin/pmd
PMD_ZIP=/tmp/pmd-dist-7.17.0-bin.zip
PMD_URL=https://github.com/pmd/pmd/releases/download/pmd_releases%2F7.17.0/pmd-dist-7.17.0-bin.zip
PMD_CACHE=/tmp/pmd-bin-7.17.0/cache
IMPORT_DIR="$(dirname $(readlink --canonicalize "$0"))"
if [ ! -f "$PMD_BIN" ]; then
    wget "$PMD_URL" -O "$PMD_ZIP"
    unzip "$PMD_ZIP" -d /tmp
    rm "$PMD_ZIP"
fi


# For rule definitions, see: https://pmd.github.io/pmd/pmd_rules_java.html
# Default rules for quickstart
#RULESET=category/java/bestpractices.xml

# Use specific rules
RULESET=category/java/errorprone.xml/CloseResource,category/java/bestpractices.xml/LooseCoupling,category/java/codestyle.xml/UseDiamondOperator

"$PMD_BIN" check -d "$IMPORT_DIR/index_java/src/org/tuefind" -R "$RULESET" -f text --cache="$PMD_CACHE" --aux-classpath="$IMPORT_DIR/lib/:$IMPORT_DIR/lib_local/" --no-progress
