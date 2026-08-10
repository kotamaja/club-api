#!/usr/bin/env bash

set -euo pipefail

FORCE=false

show_help() {
    cat <<'EOF'
Usage:
  rollback.sh [options] <release-name>

Options:
  --force
      Skip confirmation prompt.

  --help, -h
      Show this help and exit.

Environment variables:

  PROJECT_ROOT
      Required.

      Root directory of the deployment environment.

Examples:

  PROJECT_ROOT="/path/to/application" \
  ./rollback.sh 2026-08-10-001

  PROJECT_ROOT="$HOME/tmp/rcl-deploy-test" \
  ./rollback.sh dev-20260809-092054

  PROJECT_ROOT="$HOME/tmp/rcl-deploy-test" \
  ./rollback.sh \
      --force \
      dev-20260809-092054
EOF
}

#
# Parse options
#

while [[ $# -gt 0 ]]; do
    case "$1" in
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

RELEASE_NAME="${1:-}"

if [[ -z "${RELEASE_NAME}" ]]; then
    echo "ERROR: Release name is required."
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
RELEASE_DIR="${RELEASES_DIR}/${RELEASE_NAME}"

#
# Validate target release
#

if [[ ! -d "${RELEASE_DIR}" ]]; then
    echo "ERROR: Release does not exist:"
    echo "  ${RELEASE_DIR}"
    exit 1
fi

for path in \
    "${RELEASE_DIR}/public/index.php" \
    "${RELEASE_DIR}/public/index.html" \
    "${RELEASE_DIR}/bin/console"
do
    if [[ ! -e "${path}" ]]; then
        echo "ERROR: Invalid release. Required file missing:"
        echo "  ${path}"
        exit 1
    fi
done

#
# Determine current release
#

CURRENT_RELEASE=""

if [[ -L "${PROJECT_ROOT}/current_release" ]]; then
    CURRENT_RELEASE="$(basename "$(readlink -f "${PROJECT_ROOT}/current_release")")"
fi

echo
echo "========================================"
echo " RCL rollback"
echo
echo " Current release:"
echo "   ${CURRENT_RELEASE:-unknown}"
echo
echo " Target release:"
echo "   ${RELEASE_NAME}"
echo "========================================"
echo

if [[ "${CURRENT_RELEASE}" == "${RELEASE_NAME}" ]]; then
    echo "ERROR: Target release is already active."
    exit 1
fi

echo "WARNING:"
echo "This rollback changes application code only."
echo "Database migrations will NOT be reverted."
echo

#
# Confirmation
#

if [[ "${FORCE}" == false ]]; then
    read -r -p "Continue rollback? [y/N] " answer

    case "${answer}" in
        y|Y)
            ;;
        *)
            echo "Rollback aborted."
            exit 1
            ;;
    esac
fi

#
# Activate target release
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

#
# Verify activation
#

ACTIVE_RELEASE="$(basename "$(readlink -f "${PROJECT_ROOT}/current_release")")"

if [[ "${ACTIVE_RELEASE}" != "${RELEASE_NAME}" ]]; then
    echo "ERROR: Rollback activation verification failed."
    exit 1
fi

echo
echo "========================================"
echo " Rollback completed successfully"
echo
echo " Active release:"
echo "   ${RELEASE_NAME}"
echo
echo " Previous release:"
echo "   ${CURRENT_RELEASE:-unknown}"
echo "========================================"