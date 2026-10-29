#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Publica un proyecto Laravel en Render con un solo comando.
#
#   ./publicar.sh                                   -> publica ESTE proyecto
#   ./publicar.sh ~/Desktop/proyecto-profe          -> publica OTRO proyecto Laravel
#   ./publicar.sh ~/Desktop/proyecto-profe "msg"    -> con mensaje de commit
#
# Qué hace:
#   1. Si es otro proyecto, le copia el Dockerfile y el entrypoint (kit de despliegue).
#   2. Hace commit y lo sube al repositorio conectado a Render (git push).
#   3. Render construye y publica solo; las migraciones corren solas al arrancar.
#   4. Si hay API Key en .deploy.env, espera y muestra cuándo quedó en línea.
# ---------------------------------------------------------------------------
set -euo pipefail

KIT="$(cd "$(dirname "$0")" && pwd)"
DESTINO="$(cd "${1:-$KIT}" && pwd)"
MENSAJE="${2:-Despliegue $(date '+%Y-%m-%d %H:%M')}"

REPO="git@github.com:israel-carata/inventario-api.git"
RENDER_API_KEY=""
RENDER_SERVICE_ID=""
RENDER_URL=""
# .deploy.env (no se sube a git) puede definir REPO, RENDER_API_KEY, RENDER_SERVICE_ID, RENDER_URL
[ -f "$KIT/.deploy.env" ] && source "$KIT/.deploy.env"

if [ ! -f "$DESTINO/artisan" ]; then
    echo "❌ $DESTINO no parece un proyecto Laravel (no tiene el archivo artisan)."
    exit 1
fi

if [ "$DESTINO" != "$KIT" ]; then
    echo "📦 Copiando kit de despliegue a $DESTINO"
    cp "$KIT/Dockerfile" "$KIT/.dockerignore" "$DESTINO/"
    mkdir -p "$DESTINO/docker"
    cp "$KIT/docker/entrypoint.sh" "$DESTINO/docker/entrypoint.sh"
fi

cd "$DESTINO"

# Nunca subir secretos ni dependencias
[ -f .gitignore ] || printf "/vendor\n/node_modules\n.env\n" > .gitignore
for linea in ".env" ".deploy.env" "/vendor" "/node_modules"; do
    grep -qxF "$linea" .gitignore || echo "$linea" >> .gitignore
done

[ -d .git ] || git init -q -b main
git remote get-url render >/dev/null 2>&1 || git remote add render "$REPO"

git add -A
git commit -q -m "$MENSAJE" && echo "📝 Commit: $MENSAJE" || echo "ℹ️  Sin cambios nuevos para commitear"

echo "🚀 Subiendo a GitHub ($REPO)…"
git push -q -f render HEAD:main

if [ -z "$RENDER_API_KEY" ] || [ -z "$RENDER_SERVICE_ID" ]; then
    echo "✅ Subido. Render lo desplegará en unos minutos (revisa el panel de Render)."
    exit 0
fi

SHA="$(git rev-parse HEAD)"
echo "⏳ Esperando a Render (commit ${SHA:0:7})…"
for _ in $(seq 1 120); do
    sleep 10
    ESTADO="$(curl -s -H "Authorization: Bearer $RENDER_API_KEY" \
        "https://api.render.com/v1/services/$RENDER_SERVICE_ID/deploys?limit=10" | python3 -c '
import json, sys
sha = sys.argv[1]
try:
    deploys = json.load(sys.stdin)
except Exception:
    print("sin-respuesta"); sys.exit()
for item in deploys:
    d = item.get("deploy", item)
    if (d.get("commit") or {}).get("id") == sha:
        print(d.get("status", "?")); break
else:
    print("en-cola")
' "$SHA")"
    printf "\r   Estado: %-28s" "$ESTADO"
    case "$ESTADO" in
        live)
            echo; echo "✅ Publicado: ${RENDER_URL:-(ver panel de Render)}"; exit 0 ;;
        build_failed|update_failed|pre_deploy_failed|canceled)
            echo; echo "❌ El despliegue falló ($ESTADO). Revisa los logs en Render."; exit 1 ;;
    esac
done
echo; echo "⚠️  Se agotó el tiempo de espera; revisa el panel de Render."
