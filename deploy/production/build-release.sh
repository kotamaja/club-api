#!/usr/bin/env bash

set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

SYMFONY_DIR="${PROJECT_ROOT}/symfony"
FRONTEND_DIR="${PROJECT_ROOT}/frontend"

echo "PROJECT_ROOT=${PROJECT_ROOT}"
echo "SYMFONY_DIR=${SYMFONY_DIR}"
echo "FRONTEND_DIR=${FRONTEND_DIR}"

BUILD_DIR="${PROJECT_ROOT}/build"
RELEASE_DIR="${BUILD_DIR}/release"
DIST_DIR="${PROJECT_ROOT}/dist"

MODE="release"
RUN_TESTS=true

show_help() {
    cat <<'EOF'
Usage:
  build-release.sh [options]

Options:
  --dev
      Create a development artifact from the current working tree.

      The Git working tree does not need to be clean and HEAD does not need
      to point to a Git tag.

      Development artifacts must not be deployed to production.

  --no-tests
      Skip Symfony tests.

      This option is only available together with --dev.

  --help, -h
      Show this help and exit.

Examples:

  Official release build:

    ./deploy/production/build-release.sh

  Development build:

    ./deploy/production/build-release.sh --dev

  Development build without tests:

    ./deploy/production/build-release.sh --dev --no-tests
EOF
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        --dev)
            MODE="dev"
            shift
            ;;
        --no-tests)
            RUN_TESTS=false
            shift
            ;;
        --help|-h)
            show_help
            exit 0
            ;;
        *)
            echo "ERROR: Unknown option: $1"
            echo
            show_help
            exit 1
            ;;
    esac
done

if [[ "${RUN_TESTS}" == false && "${MODE}" != "dev" ]]; then
    echo "ERROR: --no-tests is only available with --dev."
    exit 1
fi

cleanup() {
    echo
    echo "Stopping Docker services..."
    docker compose down
}

trap cleanup EXIT


cd "${PROJECT_ROOT}"

echo
echo "========================================"
echo " RCL release build"
echo " Mode: ${MODE}"
echo "========================================"
echo

#
# Git validation
#

if [[ "${MODE}" == "release" ]]; then
    if [[ -n "$(git status --porcelain)" ]]; then
        echo "ERROR: Git working tree is not clean."
        exit 1
    fi

    TAG="$(git describe --tags --exact-match HEAD 2>/dev/null || true)"

    if [[ -z "${TAG}" ]]; then
        echo "ERROR: HEAD is not positioned on a Git tag."
        exit 1
    fi

    RELEASE_NAME="${TAG}"
else
    echo "WARNING: development build"
    echo "This artifact must not be deployed to production."
    echo

    RELEASE_NAME="dev-$(date '+%Y%m%d-%H%M%S')"
fi

ARCHIVE_NAME="rcl-${RELEASE_NAME}.tar.gz"
ARCHIVE_PATH="${DIST_DIR}/${ARCHIVE_NAME}"

#
# Check Docker
#

command -v docker >/dev/null 2>&1 || {
    echo "ERROR: Docker is not available."
    exit 1
}

docker compose version >/dev/null 2>&1 || {
    echo "ERROR: Docker Compose is not available."
    exit 1
}

#
# Start required services
#

echo "Starting required Docker services..."

docker compose up -d mysql-dev php node

#
# Backend dependencies
#

echo
echo "Installing Symfony dependencies..."

docker compose exec -T php composer install


#
# Backend tests
#


if [[ "${RUN_TESTS}" == true ]]; then

  echo
  echo "Creating test database if necessary..."

  docker compose exec -T php \
      php bin/console doctrine:database:create \
      --env=test \
      --if-not-exists


    echo
    echo "Running Symfony tests..."

    if docker compose exec -T php vendor/bin/phpunit; then
        echo "Symfony tests passed."
    else
        if [[ "${MODE}" == "dev" ]]; then
            echo
            echo "WARNING: Symfony tests failed."
            echo "Development mode: build will continue."
            echo
        else
            echo
            echo "ERROR: Symfony tests failed."
            echo "Release build aborted."
            exit 1
        fi
    fi
else
    echo
    echo "WARNING: Tests are disabled."
    echo "Development mode only."
fi

#
# Frontend dependencies
#

echo
echo "Validating Composer configuration..."

if docker compose exec -T php composer validate --strict --no-check-publish; then
    echo "Composer configuration is valid."
else
    if [[ "${MODE}" == "dev" ]]; then
        echo
        echo "WARNING: Composer validation failed."
        echo "Development mode: build will continue."
        echo
    else
        echo
        echo "ERROR: composer.json / composer.lock validation failed."
        echo "Release build aborted."
        exit 1
    fi
fi



echo
echo "Installing frontend dependencies..."

docker compose exec -T node npm ci

#
# Angular production build
#

echo
echo "Building Angular..."

docker compose exec -T node npm run build

#
# Locate Angular build
#

ANGULAR_BUILD_ROOT="${FRONTEND_DIR}/dist/my-app"

if [[ -f "${ANGULAR_BUILD_ROOT}/browser/index.html" ]]; then
    ANGULAR_BUILD_DIR="${ANGULAR_BUILD_ROOT}/browser"
elif [[ -f "${ANGULAR_BUILD_ROOT}/index.html" ]]; then
    ANGULAR_BUILD_DIR="${ANGULAR_BUILD_ROOT}"
else
    echo "ERROR: Angular build output not found."
    exit 1
fi

echo "Angular build found in:"
echo "  ${ANGULAR_BUILD_DIR}"

#
# Prepare release directory
#

echo
echo "Preparing release directory..."

rm -rf "${RELEASE_DIR}"
mkdir -p "${RELEASE_DIR}"
mkdir -p "${RELEASE_DIR}/public"

#
# Copy Symfony runtime files
#

for path in \
    bin \
    config \
    migrations \
    src \
    templates
do
    if [[ -e "${SYMFONY_DIR}/${path}" ]]; then
        cp -a "${SYMFONY_DIR}/${path}" "${RELEASE_DIR}/"
    fi
done

for file in \
    composer.json \
    composer.lock \
    symfony.lock \
    .env
do
    if [[ -f "${SYMFONY_DIR}/${file}" ]]; then
        cp -a "${SYMFONY_DIR}/${file}" "${RELEASE_DIR}/"
    fi
done

#
# Copy Symfony public directory
#

cp -a "${SYMFONY_DIR}/public/." "${RELEASE_DIR}/public/"

#
# .htaccess is installed from shared/ on the production server
#

rm -f "${RELEASE_DIR}/public/.htaccess"

#
# Copy Angular build into Symfony public/
#

echo
echo "Adding Angular build..."

cp -a "${ANGULAR_BUILD_DIR}/." "${RELEASE_DIR}/public/"

#
# Ensure forbidden runtime/development files are absent
#

rm -rf \
    "${RELEASE_DIR}/vendor" \
    "${RELEASE_DIR}/var" \
    "${RELEASE_DIR}/tests" \
    "${RELEASE_DIR}/config/jwt"

rm -f \
    "${RELEASE_DIR}/.env.local" \
    "${RELEASE_DIR}/.env.dev" \
    "${RELEASE_DIR}/.env.test" \
    "${RELEASE_DIR}/.env.prod.local" \
    "${RELEASE_DIR}/phpunit.dist.xml" \
    "${RELEASE_DIR}/.gitignore" \
    "${RELEASE_DIR}/.editorconfig"

#
# Basic release checks
#

echo
echo "Checking release content..."

[[ -f "${RELEASE_DIR}/public/index.php" ]] || {
    echo "ERROR: Symfony public/index.php is missing."
    exit 1
}

[[ -f "${RELEASE_DIR}/public/index.html" ]] || {
    echo "ERROR: Angular public/index.html is missing."
    exit 1
}

[[ -f "${RELEASE_DIR}/composer.json" ]] || {
    echo "ERROR: composer.json is missing."
    exit 1
}

#
# Create archive
#

echo
echo "Creating archive..."

mkdir -p "${DIST_DIR}"
rm -f "${ARCHIVE_PATH}" "${ARCHIVE_PATH}.sha256"

tar \
    -C "${RELEASE_DIR}" \
    -czf "${ARCHIVE_PATH}" \
    .

#
# SHA-256
#

(
    cd "${DIST_DIR}"
    sha256sum "${ARCHIVE_NAME}" > "${ARCHIVE_NAME}.sha256"
)

echo
echo "========================================"
echo " Build completed successfully"
echo
echo " Archive:"
echo "   ${ARCHIVE_PATH}"
echo
echo " Checksum:"
echo "   ${ARCHIVE_PATH}.sha256"
echo "========================================"