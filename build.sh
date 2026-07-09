#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "${SCRIPT_DIR}"

PLUGIN_SLUG="the-events-calendar-addon2"
BUILD_DIR="build/${PLUGIN_SLUG}"
ZIP_NAME="${PLUGIN_SLUG}.zip"

echo "$(tput setaf 6 2>/dev/null || true)"
echo "Building production version for ${PLUGIN_SLUG}..."

npm run production
echo -ne "Production version created......              (30%)\r"

rm -rf build
mkdir -p "${BUILD_DIR}"

echo -ne "Cleanup and building files started........            (40%)\r"

rsync -a --delete \
	--exclude '/.git/' \
	--exclude '/.svn/' \
	--exclude '/build/' \
	--exclude '/dist/' \
	--exclude '/node_modules/' \
	--exclude '/dev/' \
	--exclude '/.vscode/' \
	--exclude '/.idea/' \
	--exclude '/scripts/' \
	--exclude '.DS_Store' \
	--exclude '__MACOSX/' \
	--exclude '.AppleDouble/' \
	--exclude '.LSOverride/' \
	--exclude '.Trashes/' \
	--exclude '.AppleDB/' \
	./ "${BUILD_DIR}/"

echo -ne "Files copied............        (60%)\r"

rm -rf "${BUILD_DIR}/mix-manifest.json" \
	"${BUILD_DIR}/package.json" \
	"${BUILD_DIR}/package-lock.json" \
	"${BUILD_DIR}/webpack.mix.js" \
	"${BUILD_DIR}/.babelrc" \
	"${BUILD_DIR}/.gitignore" \
	"${BUILD_DIR}/.AppleDouble" \
	"${BUILD_DIR}/.LSOverride" \
	"${BUILD_DIR}/.Trashes" \
	"${BUILD_DIR}/.AppleDB" \
	"${BUILD_DIR}/.idea" \
	"${BUILD_DIR}/build.sh" \
	"${BUILD_DIR}/yarn.lock" \
	"${BUILD_DIR}/composer.json" \
	"${BUILD_DIR}/composer.lock" \
	"${BUILD_DIR}/task.txt" \
	"${BUILD_DIR}/scripts" \
	"${BUILD_DIR}/includes/gs-common-pages/assets/gs-plugins-common-pages.scss" \
	"${BUILD_DIR}/includes/integration/gutenberg/src" \
	"${BUILD_DIR}/includes/integration/divi/assets/js/teca-divi-editor.js"

find "${BUILD_DIR}" -type f -name '.DS_Store' -delete
find "${BUILD_DIR}" -type f -name '*.LICENSE.txt' -delete

if [[ -f "${BUILD_DIR}/the-events-calendar-addon.php" ]]; then
	cp "${BUILD_DIR}/the-events-calendar-addon.php" "${BUILD_DIR}/${PLUGIN_SLUG}.php"
	rm -f "${BUILD_DIR}/the-events-calendar-addon.php"
fi

echo -ne "Creating ${ZIP_NAME} file................    (80%)\r"

(
	cd build
	zip -r "${ZIP_NAME}" "${PLUGIN_SLUG}/"
	rm -rf "${PLUGIN_SLUG}"
)

echo -ne "Congratulations... Successfully done....................(100%)\r"

npm run development
echo -ne "Development version restored....................(100%)\r"

echo "$(tput setaf 2 2>/dev/null || true)"
echo "Clean process completed!"
echo "ZIP created at: ${SCRIPT_DIR}/build/${ZIP_NAME}"
echo "$(tput sgr0 2>/dev/null || true)"
