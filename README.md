# Plantilla Laravel ATS TAS

Base visual y arquitectónica para el ATS.

## Convención de rutas

### Web
- `/sistema-ats-tas/dashboard`
- `/sistema-ats-tas/reclutamiento/...`
- `/sistema-ats-tas/reportes/...`
- `/sistema-ats-tas/catalogos/...`
- `/sistema-ats-tas/seguridad/...`

### API REST v1
- `/api/v1/reclutamiento/...`
- `/api/v1/reportes/...`
- `/api/v1/catalogos/...`
- `/api/v1/seguridad/...`

El módulo patrón incluido es `Catálogos > Sexos`.

## Arquitectura PHP

`Controller -> Validator -> DTO -> Service -> Model -> PostgreSQL`

La vista web no carga registros desde PHP. Los datos llegan mediante REST y jQuery.

## Arquitectura JavaScript

- `resources/js/core/Api.js`: peticiones jQuery AJAX comunes.
- `resources/js/modulos/catalogos/sexos/SexosConfig.js`: URLs, selectores, textos y configuración.
- `resources/js/modulos/catalogos/sexos/Sexos.js`: estado, peticiones, DataTable, modal y formulario.

Flujo:

`API REST -> estado.registros -> DataTable -> UI`

`estado.registros` conserva la respuesta para poder recuperar un registro al editar o cambiar estatus sin hacer un GET individual.

## DataTables

DataTables se utiliza como motor de tabla, pero su interfaz visual por defecto se oculta. La búsqueda, cantidad de filas y paginación usan controles Tailwind propios para conservar el diseño del ATS.

## Dependencias frontend

```bash
npm install
npm run dev
```

Incluye:
- Tailwind CSS 4
- jQuery 4
- DataTables 3
- Vite

## API del catálogo Sexo

- `GET /api/v1/catalogos/sexos`
- `POST /api/v1/catalogos/sexos`
- `PUT /api/v1/catalogos/sexos/{idSexo}`
- `PATCH /api/v1/catalogos/sexos/{idSexo}/estatus`

Respuesta exitosa estándar:

```json
{
  "estatus": true,
  "mensaje": "Sexos obtenidos correctamente.",
  "data": []
}
```

## Laravel 13

`bootstrap/app.php` registra de forma explícita tanto `routes/web.php` como `routes/api.php`.
