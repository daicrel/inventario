# 🚀 Ejecución del Proyecto y Acceso a Swagger

## 1. Requisitos previos

- **PHP >= 8.2**
- **Composer**
- **Symfony CLI** ([descargar aquí](https://symfony.com/download))

## 2. Instalación de dependencias

En la raíz del proyecto, ejecuta:

```bash
composer install
```

## 3. Ejecución del servidor de desarrollo Symfony

Inicia el servidor embebido de Symfony con el siguiente comando:

```bash
symfony server:start
```

Esto levantará el servidor en `http://localhost:8000` por defecto.

## 4. Acceso a la documentación Swagger

Con el servidor en ejecución, abre tu navegador y accede a:

```
http://localhost:8000/api/doc
```

Aquí encontrarás la interfaz Swagger UI con la documentación interactiva de la API.

---

### Notas adicionales

- El endpoint `/api/doc` está gestionado por el controlador `SwaggerController`.
- Si necesitas modificar la documentación, puedes editar el archivo `src/Controller/SwaggerController.php`.
- Si los estilos o scripts de Swagger UI no se cargan correctamente, ejecuta:

```bash
php bin/console assets:install
```

- Para detener el servidor, presiona `Ctrl + C` en la terminal donde lo ejecutaste.

---

¡Listo! Ahora puedes explorar y probar la API desde Swagger UI. 