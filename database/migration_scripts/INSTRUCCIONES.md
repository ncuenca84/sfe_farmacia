# Migración Sistema Viejo → SFE Nuevo

## Archivos

| Archivo | Función |
|---|---|
| `migrate_from_old.sql` | Migra datos de BD vieja a BD nueva |
| `migrar_archivos.sh` | Copia XMLs, firmas P12 y logos |
| `post_migracion.php` | Cifra firmas, actualiza rutas, resetea passwords |
| `truncate_all.sql` | Limpia tablas nuevas (para re-ejecutar) |

## Configuración

Antes de ejecutar, editar las variables en cada archivo:

**migrate_from_old.sql** - Reemplazar `OLD_DB` y `NEW_DB`:
```bash
sed 's/OLD_DB/nombre_bd_vieja/g; s/NEW_DB/nombre_bd_nueva/g' migrate_from_old.sql > migrate_final.sql
```

**migrar_archivos.sh** - Líneas 5-6:
```bash
DIR_VIEJO="/ruta/emisores_old"
DIR_NUEVO="/ruta/emisores"
```

**post_migracion.php** - Línea 7:
```php
$SFE_DIR_BASE = '/ruta/emisores';
```

## Ejecución (en orden)

```bash
# 0. Backup
mysqldump -u usuario -p'password' bd_nueva > backup_antes.sql

# 1. Dar permisos de lectura al usuario nuevo sobre la BD vieja
mysql -u root -e "GRANT SELECT ON bd_vieja.* TO 'usuario_nuevo'@'localhost'; FLUSH PRIVILEGES;"

# 2. Generar script SQL con nombres de BD correctos
sed 's/OLD_DB/nombre_bd_vieja/g; s/NEW_DB/nombre_bd_nueva/g' migrate_from_old.sql > migrate_final.sql

# 3. Ejecutar migraciones Laravel (crear tablas)
php artisan migrate

# 4. Ejecutar migración SQL
mysql -u usuario -p'password' bd_nueva < migrate_final.sql

# 5. Migrar archivos
bash migrar_archivos.sh

# 6. Post migración (cifrar firmas, rutas, passwords)
cd /ruta/al/proyecto
php database/migration_scripts/post_migracion.php

# 7. Actualizar .env
# SFE_DIR_BASE=/ruta/emisores

# 8. Limpiar cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Si necesitas re-ejecutar

```bash
mysql -u usuario -p'password' bd_nueva < truncate_all.sql
mysql -u usuario -p'password' bd_nueva < migrate_final.sql
php database/migration_scripts/post_migracion.php
```

## Notas

- **INSERT IGNORE**: Se usa en todas las tablas para saltar duplicados de la BD vieja
- **Passwords**: Se resetean a RUC del emisor. Los usuarios entran con su username y el RUC como password
- **Proformas**: Muchas no migran porque no tienen pto_emision_id (campo obligatorio en el nuevo sistema). Es normal, son históricas
- **Firmas P12**: Se cifran con encrypt() de Laravel (el viejo las guardaba en texto plano)
