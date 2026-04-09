#!/bin/bash
# ============================================
# MIGRACIÓN DE ARCHIVOS: Sistema viejo → Sistema nuevo
# ============================================
# CONFIGURACIÓN: Cambiar estas 2 rutas
DIR_VIEJO="/home/andres/emisores_old"
DIR_NUEVO="/home/andres/emisores"
# ============================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "============================================"
echo "  Migración de archivos SFE"
echo "  Origen: $DIR_VIEJO"
echo "  Destino: $DIR_NUEVO"
echo "============================================"

if [ ! -d "$DIR_VIEJO" ]; then
    echo -e "${RED}ERROR: No existe directorio origen: $DIR_VIEJO${NC}"
    exit 1
fi

mkdir -p "$DIR_NUEVO"

TOTAL_EMISORES=0

for EMISOR_DIR in "$DIR_VIEJO"/*/; do
    [ -d "$EMISOR_DIR" ] || continue
    RUC=$(basename "$EMISOR_DIR")
    TOTAL_EMISORES=$((TOTAL_EMISORES + 1))

    echo -e "${YELLOW}[$TOTAL_EMISORES] Migrando $RUC...${NC}"

    mkdir -p "$DIR_NUEVO/$RUC/firmas" "$DIR_NUEVO/$RUC/logos"

    # Firma P12
    find "$EMISOR_DIR" -maxdepth 1 -name "*.p12" -exec cp {} "$DIR_NUEVO/$RUC/firmas/" \;

    # Logo
    find "$EMISOR_DIR" -maxdepth 1 \( -name "*.png" -o -name "*.jpg" -o -name "*.jpeg" -o -name "*.gif" \) \
        -exec cp {} "$DIR_NUEVO/$RUC/logos/" \;

    # XMLs: usar clave_acceso del contenido para nombre único
    find "$EMISOR_DIR" -mindepth 2 -name "*.xml" | while read XML_FILE; do
        CLAVE=$(grep -oP '<claveAcceso>\K[0-9]{49}' "$XML_FILE" 2>/dev/null | head -1)
        if [ -n "$CLAVE" ]; then
            [ ! -f "$DIR_NUEVO/$RUC/$CLAVE.xml" ] && cp "$XML_FILE" "$DIR_NUEVO/$RUC/$CLAVE.xml"
        else
            CLIENTE_DIR=$(basename "$(dirname "$XML_FILE")")
            FILENAME=$(basename "$XML_FILE")
            [ ! -f "$DIR_NUEVO/$RUC/${CLIENTE_DIR}_${FILENAME}" ] && cp "$XML_FILE" "$DIR_NUEVO/$RUC/${CLIENTE_DIR}_${FILENAME}"
        fi
    done

    chmod -R 775 "$DIR_NUEVO/$RUC" 2>/dev/null
    echo -e "  ${GREEN}OK${NC}"
done

# Conteo final
XML_COUNT=$(find "$DIR_NUEVO" -name "*.xml" | wc -l)
P12_COUNT=$(find "$DIR_NUEVO" -name "*.p12" | wc -l)
LOGO_COUNT=$(find "$DIR_NUEVO" \( -name "*.png" -o -name "*.jpg" -o -name "*.jpeg" \) | wc -l)

echo ""
echo "============================================"
echo -e "${GREEN}  Migración completada${NC}"
echo "  Emisores: $TOTAL_EMISORES"
echo "  XMLs: $XML_COUNT"
echo "  Firmas P12: $P12_COUNT"
echo "  Logos: $LOGO_COUNT"
echo "============================================"
