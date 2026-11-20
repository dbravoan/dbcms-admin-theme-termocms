# Mantener termoCMS

Este paquete posee exclusivamente la presentación de Termosalud: identidad,
logos, paleta clara y overrides admin. Core posee el kit de controles, formularios,
autorización y flujos. Reutiliza `<x-admin.*>` y tokens; los cambios de negocio
pertenecen al propietario correspondiente.

El perfil base verifica identidad, login, dashboard, errores, logos locales y
superficies del admin bar. Los overrides `modules/forms/admin/dashboard` siguen
activos: Forms pertenece a core y registra explícitamente ese namespace. Los de
Blog y Shop se usan solo con esos módulos y tienen perfiles separados.

Un cambio de tokens se valida con esta marca; un cambio de controles compartidos
se corrige en core y se comprueba también en su admin nativo. Mantén recursos de
marca fuera de core y prueba el dashboard sin módulos opcionales.

Los procedimientos comunes tienen una sola fuente pública:

- [Desarrollo Docker](https://github.com/dbravoan/dbcms-module-template/blob/main/docs/DEVELOPMENT.md)
- [Pruebas, perfiles y locks](https://github.com/dbravoan/dbcms-module-template/blob/main/docs/TESTING.md)
- [Publicación por tag con SSH local](https://github.com/dbravoan/dbcms-module-template/blob/main/docs/PUBLICATION.md)
- [Arquitectura y contratos](https://github.com/dbravoan/dbcms-core/tree/main/docs)

Tras cambiar dependencias o composición, regenera y revisa sus locks desde Docker.
Conserva el tooling local necesario para contribuir sin repositorios privados;
las actualizaciones de la plantilla se incorporan y prueban en este propietario.

La comparación de las dos marcas vive en `tests/Browser/admin-brand.spec.mjs`.
Exporta las respuestas de `AdminBrandCompatibilityTest` con
`DBCMS_ADMIN_FIXTURE_DIR` y conserva el build real del perfil base. Instala las
herramientas con `npm --prefix tests/Browser ci` y ejecuta Playwright desde Node 22:

```bash
DBCMS_ADMIN_FIXTURE_DIR=/ruta/fixtures \
DBCMS_ADMIN_ASSET_ROOT="$PWD/.build/app/public" \
DBCMS_ADMIN_THEME_ROOT="$PWD" \
DBCMS_BROWSER_RESULTS=/ruta/resultados \
npm exec --prefix tests/Browser -- playwright test --config tests/Browser/playwright.config.mjs
```

`DBCMS_BROWSER_CHROME` selecciona un Chromium existente. La suite exige los
recursos reales y verifica las dos marcas en móvil, tableta y escritorio.
