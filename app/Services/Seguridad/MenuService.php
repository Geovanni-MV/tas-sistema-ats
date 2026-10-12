<?php

namespace App\Services\Seguridad;

use App\DTO\Seguridad\MenuDTO;
use App\Models\Seguridad\MenuModel;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio para la entidad seguridad.menu
 */
final class MenuService
{
    private const RELACIONES = ['padre:id_menu,nombre_menu'];

    /**
     * Obtiene todos los registros ordenados por "orden" y luego por identificador.
     */
    public function listar(): Collection
    {
        return MenuModel::query()
            ->with(self::RELACIONES)
            ->orderBy('orden')
            ->orderBy('id_menu')
            ->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el controlador.
     */
    public function crear(MenuDTO $dto): MenuModel
    {
        $menu = MenuModel::query()->create([
            'id_menu_padre' => $dto->id_menu_padre,
            'es_padre' => $dto->es_padre,
            'es_colapsable' => $dto->es_colapsable,
            'nombre_menu' => $dto->nombre_menu,
            'icono_clase' => $dto->icono_clase,
            'descripcion' => $dto->descripcion,
            'orden' => $dto->orden,
            'ruta' => $dto->ruta,
        ]);

        return $menu->refresh()->load(self::RELACIONES);
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona en cambiarEstatus().
     */
    public function actualizar(MenuDTO $dto): MenuModel
    {
        $menu = MenuModel::query()->findOrFail($dto->id_menu);

        $menu->fill([
            'id_menu_padre' => $dto->id_menu_padre,
            'es_padre' => $dto->es_padre,
            'es_colapsable' => $dto->es_colapsable,
            'nombre_menu' => $dto->nombre_menu,
            'icono_clase' => $dto->icono_clase,
            'descripcion' => $dto->descripcion,
            'orden' => $dto->orden,
            'ruta' => $dto->ruta,
            'fecha_actualizacion' => now(),
        ])->save();

        return $menu->refresh()->load(self::RELACIONES);
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $id_menu): MenuModel
    {
        $menu = MenuModel::query()->findOrFail($id_menu);

        $menu->fill([
            'activo' => ! $menu->activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $menu->refresh()->load(self::RELACIONES);
    }
}
