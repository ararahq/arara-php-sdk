#!/usr/bin/env bash
# Decide se Arara::VERSION deve virar release.
# Saída (stdout): "tag=vX.Y.Z" e "publish=true|false".
# Falha (exit 1) se a versão for inválida, não tiver seção "## [X.Y.Z]" no CHANGELOG.md
# ou for menor que a última tag vX.Y.Z existente.
set -euo pipefail

version="${1:?uso: release-version.sh <versao>}"
changelog="${CHANGELOG_FILE:-CHANGELOG.md}"

if ! [[ "$version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  echo "Versao invalida em Arara::VERSION: $version" >&2
  exit 1
fi

tag="v$version"
echo "tag=$tag"

if git rev-parse -q --verify "refs/tags/$tag" > /dev/null; then
  echo "publish=false"
  exit 0
fi

if ! grep -Fq "## [$version]" "$changelog"; then
  echo "CHANGELOG sem a secao ## [$version]" >&2
  exit 1
fi

latest=$(git tag --list 'v[0-9]*.[0-9]*.[0-9]*' | sed 's/^v//' | sort -V | tail -1)

if [[ -n "$latest" ]]; then
  highest=$(printf '%s\n%s\n' "$latest" "$version" | sort -V | tail -1)
  if [[ "$highest" != "$version" ]]; then
    echo "Versao $version nao e maior que a ultima tag v$latest" >&2
    exit 1
  fi
fi

echo "publish=true"
