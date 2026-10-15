<?php

namespace App\Services\Seguridad;

use App\DTO\Seguridad\MenuDTO;
use App\Models\Seguridad\MenuModel;
use App\Models\Seguridad\UsuarioModel;
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
            
        ])->save();

        return $menu->refresh()->load(self::RELACIONES);
    }
    /**
     * Obtiene los menus y genera la agrupacion de esquemas
     */
    private function agruparMenus(
        Collection $menus,
        ?int $idMenuPadre = null
    ): array {

        $resultado = [];

        foreach ($menus as $menu) {

            if ($menu->id_menu_padre !== $idMenuPadre) {
                continue;
            }

            $hijos = $this->agruparMenus(
                $menus,
                (int) $menu->id_menu
            );

            $resultado[] = [
                'id_menu' => $menu->id_menu,
                'id_menu_padre' => $menu->id_menu_padre,
                'nombre_menu' => $menu->nombre_menu,
                'ruta' => $menu->ruta,
                'icono_clase' => $menu->icono_clase,
                'orden' => $menu->orden,
                'es_padre' => $menu->es_padre,
                'es_colapsable' => $menu->es_colapsable,
                'hijos' => $hijos,
            ];
        }

        return $resultado;
    }

    public function obtenerMenusUsuario(
        UsuarioModel $usuario
    ): array {

        $menus = MenuModel::query()
            ->select(
                'seguridad.menu.id_menu',
                'seguridad.menu.id_menu_padre',
                'seguridad.menu.nombre_menu',
                'seguridad.menu.ruta',
                'seguridad.menu.icono_clase',
                'seguridad.menu.orden',
                'seguridad.menu.es_padre',
                'seguridad.menu.es_colapsable'
            )
            ->join(
                'seguridad.permisos_roles',
                'seguridad.permisos_roles.id_menu',
                '=',
                'seguridad.menu.id_menu'
            )
            ->where(
                'seguridad.permisos_roles.id_rol',
                $usuario->id_rol
            )
            ->where(
                'seguridad.permisos_roles.activo',
                true
            )
            ->where(
                'seguridad.menu.activo',
                true
            )
            ->distinct()
            ->orderBy(
                'seguridad.menu.orden'
            )
            ->get();

        return $this->agruparMenus($menus);
    }
}
