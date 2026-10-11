#!/usr/bin/env bash

set -euo pipefail

MODE="production"
RUN_DB_MIGRATIONS=true
FORCE=false

show_help() {
    cat <<'EOF'
Usage:
  deploy.sh [options] <archive.tar.gz>

Options:
  --dev
      Run PHP and Composer through the development Docker Compose environment.

      Requires:
        DEV_PROJECT_ROOT
            Root directory of the development Git repository containing
            docker-compose.yml.

  --no-db-migrations
      Skip database connection checks and Doctrine migrations.

      Without --force, an interactive confirmation is required.

  --force
      Skip confirmation prompts.

      This option does not disable validations and does not ignore errors.

  --help, -h
      Show this help and exit.

Environment variables:

  PROJECT_ROOT
      Required.

      Root directory of the target deployment environment.

      Expected structure:

        PROJECT_ROOT/
        ├── incoming/
        ├── releases/
        └── shared/

  DEV_PROJECT_ROOT
      Required only with --dev.

      Root directory of the development Git repository containing
      docker-compose.yml.

Examples:

  Production deployment:

    PROJECT_ROOT="/path/to/application" \
    ./deploy.sh rcl-2026-08-10-001.tar.gz

  Local deployment test:

    DEV_PROJECT_ROOT="$HOME/Projects/project" \
    PROJECT_ROOT="$HOME/tmp/deploy-test" \
    ./deploy.sh \
        --dev \
        --no-db-migrations \
        rcl-dev-20260810-190000.tar.gz

  Local deployment test without confirmation:

    DEV_PROJECT_ROOT="$HOME/Projects/project" \
    PROJECT_ROOT="$HOME/tmp/deploy-test" \
    ./deploy.sh \
        --dev \
        --no-db-migrations \
        --force \
        rcl-dev-20260810-190000.tar.gz
EOF
}

#
# Parse options
#

while [[ $# -gt 0 ]]; do
    case "$1" in
        --dev)
            MODE="dev"
            shift
            ;;
        --no-db-migrations)
            RUN_DB_MIGRATIONS=false
            shift
            ;;
        --force)
            FORCE=true
            shift
            ;;
        --help|-h)
            show_help
            exit 0
            ;;
        -*)
            echo "ERROR: Unknown option: $1"
            echo
            show_help
            exit 1
            ;;
        *)
            break
            ;;
    esac
done

ARCHIVE_NAME="${1:-}"

if [[ -z "${ARCHIVE_NAME}" ]]; then
    echo "ERROR: Archive name is required."
    echo
    show_help
    exit 1
fi

#
# Validate PROJECT_ROOT
#

if [[ -z "${PROJECT_ROOT:-}" ]]; then
    echo "ERROR: PROJECT_ROOT must be defined."
    exit 1
fi

if [[ ! -d "${PROJECT_ROOT}" ]]; then
    echo "ERROR: PROJECT_ROOT does not exist:"
    echo "  ${PROJECT_ROOT}"
    exit 1
fi

PROJECT_ROOT="$(realpath "${PROJECT_ROOT}")"

RELEASES_DIR="${PROJECT_ROOT}/releases"
SHARED_DIR="${PROJECT_ROOT}/shared"
INCOMING_DIR="${PROJECT_ROOT}/incoming"

ARCHIVE_PATH="${INCOMING_DIR}/${ARCHIVE_NAME}"
CHECKSUM_PATH="${ARCHIVE_PATH}.sha256"

RELEASE_NAME="${ARCHIVE_NAME#rcl-}"
RELEASE_NAME="${RELEASE_NAME%.tar.gz}"

RELEASE_DIR="${RELEASES_DIR}/${RELEASE_NAME}"

#
# Validate development environment
#

if [[ "${MODE}" == "dev" ]]; then
    if [[ -z "${DEV_PROJECT_ROOT:-}" ]]; then
        echo "ERROR: DEV_PROJECT_ROOT must be defined when using --dev."
        exit 1
    fi

    if [[ ! -d "${DEV_PROJECT_ROOT}" ]]; then
        echo "ERROR: DEV_PROJECT_ROOT does not exist:"
        echo "  ${DEV_PROJECT_ROOT}"
        exit 1
    fi

    DEV_PROJECT_ROOT="$(realpath "${DEV_PROJECT_ROOT}")"

    if [[ ! -f "${DEV_PROJECT_ROOT}/docker-compose.yml" ]]; then
        echo "ERROR: docker-compose.yml not found in DEV_PROJECT_ROOT:"
        echo "  ${DEV_PROJECT_ROOT}"
        exit 1
    fi

    docker compose \
        --project-directory "${DEV_PROJECT_ROOT}" \
        version >/dev/null 2>&1 || {
            echo "ERROR: Docker Compose is not available."
            exit 1
        }
fi

#
# Confirmation when migrations are disabled
#

if [[ "${RUN_DB_MIGRATIONS}" == false && "${FORCE}" == false ]]; then
    echo
    echo "WARNING: Database checks and migrations will NOT be executed."
    echo

    read -r -p "Continue deployment? [y/N] " answer

    case "${answer}" in
        y|Y)
            ;;
        *)
            echo "Deployment aborted."
            exit 1
            ;;
    esac
fi

#
# Command helpers
#
# In --dev mode, PROJECT_ROOT is mounted as /deployment.
#
# This is important because relative symlinks inside the release must keep
# working exactly as they would on the production server.
#

run_composer() {
    if [[ "${MODE}" == "dev" ]]; then
        docker compose \
            --project-directory "${DEV_PROJECT_ROOT}" \
            run \
            --rm \
            --no-deps \
            -e APP_ENV=prod \
            -e APP_DEBUG=0 \
            -v "${PROJECT_ROOT}:/deployment" \
            -w "/deployment/releases/${RELEASE_NAME}" \
            php \
            composer "$@"
    else
        composer "$@"
    fi
}

run_php() {
    if [[ "${MODE}" == "dev" ]]; then
        docker compose \
            --project-directory "${DEV_PROJECT_ROOT}" \
            run \
            --rm \
            --no-deps \
            -e APP_ENV=prod \
            -e APP_DEBUG=0 \
            -v "${PROJECT_ROOT}:/deployment" \
            -w "/deployment/releases/${RELEASE_NAME}" \
            php \
            php "$@"
    else
        php "$@"
    fi
}

#
# Header
#

echo
echo "========================================"
echo " RCL deployment"
echo " Mode: ${MODE}"
echo " Release: ${RELEASE_NAME}"
echo " Project root: ${PROJECT_ROOT}"

if [[ "${MODE}" == "dev" ]]; then
    echo " Development project root: ${DEV_PROJECT_ROOT}"
fi

echo "========================================"
echo

if [[ "${MODE}" == "dev" ]]; then
    echo "WARNING: development deployment mode."
    echo "PHP and Composer will be executed through Docker Compose."
    echo
fi

if [[ "${RUN_DB_MIGRATIONS}" == false ]]; then
    echo "WARNING: database checks and migrations are disabled."
    echo
fi

#
# Basic filesystem checks
#

mkdir -p "${RELEASES_DIR}"

if [[ ! -d "${INCOMING_DIR}" ]]; then
    echo "ERROR: Incoming directory not found:"
    echo "  ${INCOMING_DIR}"
    exit 1
fi

if [[ ! -f "${ARCHIVE_PATH}" ]]; then
    echo "ERROR: Archive not found:"
    echo "  ${ARCHIVE_PATH}"
    exit 1
fi

if [[ ! -f "${CHECKSUM_PATH}" ]]; then
    echo "ERROR: Checksum file not found:"
    echo "  ${CHECKSUM_PATH}"
    exit 1
fi

if [[ -e "${RELEASE_DIR}" ]]; then
    echo "ERROR: Release already exists:"
    echo "  ${RELEASE_DIR}"
    exit 1
fi

#
# Check shared resources
#

echo "Checking shared resources..."

for path in \
    "${SHARED_DIR}/.env.prod.local" \
    "${SHARED_DIR}/config/jwt/private.pem" \
    "${SHARED_DIR}/config/jwt/public.pem" \
    "${SHARED_DIR}/public/.htaccess" \
    "${SHARED_DIR}/var/log"
do
    if [[ ! -e "${path}" ]]; then
        echo "ERROR: Required shared resource is missing:"
        echo "  ${path}"
        exit 1
    fi
done

#
# Verify checksum
#

echo
echo "Verifying archive checksum..."

(
    cd "${INCOMING_DIR}"
    sha256sum -c "$(basename "${CHECKSUM_PATH}")"
)

#
# Create release
#

echo
echo "Creating release directory..."

mkdir -p "${RELEASE_DIR}"

#
# Extract archive
#

echo
echo "Extracting archive..."

tar -xzf "${ARCHIVE_PATH}" -C "${RELEASE_DIR}"

#
# Validate extracted release
#

echo
echo "Checking extracted release..."

for path in \
    "${RELEASE_DIR}/composer.json" \
    "${RELEASE_DIR}/composer.lock" \
    "${RELEASE_DIR}/bin/console" \
    "${RELEASE_DIR}/public/index.php" \
    "${RELEASE_DIR}/public/index.html"
do
    if [[ ! -e "${path}" ]]; then
        echo "ERROR: Required release file is missing:"
        echo "  ${path}"
        exit 1
    fi
done

#
# Shared symlinks
#

echo
echo "Creating shared symlinks..."

rm -f "${RELEASE_DIR}/.env.prod.local"

ln -s \
    "../../shared/.env.prod.local" \
    "${RELEASE_DIR}/.env.prod.local"

rm -rf "${RELEASE_DIR}/config/jwt"

ln -s \
    "../../shared/config/jwt" \
    "${RELEASE_DIR}/config/jwt"

mkdir -p "${RELEASE_DIR}/var"

rm -rf "${RELEASE_DIR}/var/log"

ln -s \
    "../../../shared/var/log" \
    "${RELEASE_DIR}/var/log"

rm -f "${RELEASE_DIR}/public/.htaccess"

ln -s \
    "../../../shared/public/.htaccess" \
    "${RELEASE_DIR}/public/.htaccess"

#
# Symfony environment
#

export APP_ENV=prod
export APP_DEBUG=0

cd "${RELEASE_DIR}"

#
# Composer
#

echo
echo "Installing production Composer dependencies..."

run_composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

#
# Dump production environment
#

echo
echo "Preparing Symfony production environment..."

run_composer dump-env prod

#
# Symfony validation
#

echo
echo "Checking Symfony..."

run_php bin/console about

#
# Cache
#

echo
echo "Building Symfony cache..."

run_php bin/console cache:clear
run_php bin/console cache:warmup

#
# Database
#

if [[ "${RUN_DB_MIGRATIONS}" == true ]]; then
    echo
    echo "Checking database connection..."

    run_php bin/console dbal:run-sql "SELECT 1"

    echo
    echo "Checking migrations..."

    run_php bin/console doctrine:migrations:status

    echo
    echo "Running migrations..."

    run_php bin/console doctrine:migrations:migrate --no-interaction
else
    echo
    echo "WARNING: Database checks and migrations skipped."
fi

#
# Final Symfony validation
#

echo
echo "Final Symfony validation..."

run_php bin/console about

#
# Activate release
#

echo
echo "Activating release..."

cd "${PROJECT_ROOT}"

ln -sfn \
    "releases/${RELEASE_NAME}/public" \
    current

ln -sfn \
    "releases/${RELEASE_NAME}" \
    current_release

echo
echo "========================================"
echo " Deployment completed successfully"
echo
echo " Active release:"
echo "   ${RELEASE_NAME}"
echo "========================================"