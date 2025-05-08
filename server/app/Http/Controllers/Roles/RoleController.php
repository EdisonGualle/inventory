<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\StoreRoleRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $roles = Role::where('name', 'ilike', '%' . $search . '%')
            ->orderBy('id', 'desc')
            ->get();

        return $this->successResponse('Roles obtenidos correctamente', [
            'roles' => $this->formatRoles($roles)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        try {
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'api',
            ]);

            $role->syncPermissions($request->permissions);

            return $this->successResponse('Rol creado correctamente', [
                'role' => $this->formatRole($role)
            ], 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Ocurrió un error al crear el rol', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return $this->successResponse('Rol obtenido correctamente', [
            'role' => $this->formatRole($role)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        try {
            $role->update([
                'name' => $request->name,
            ]);

            $role->syncPermissions($request->permissions);

            return $this->successResponse('Rol actualizado correctamente', [
                'role' => $this->formatRole($role)
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Ocurrió un error al actualizar el rol', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        try {
            $role->delete();

            return $this->successResponse('Rol eliminado correctamente');

        } catch (\Exception $e) {
            return $this->errorResponse('Ocurrió un error al eliminar el rol', 500);
        }
    }

    /**
     * Format a single role along with its permissions.
     */
    private function formatRole(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'created_at' => $role->created_at->format('Y/m/d H:i:s'),
            'permissions' => $role->permissions->map(fn($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
            ]),
            'permissions_pluck' => $role->permissions->pluck('name'),
        ];
    }

    /**
     * Format a collection of roles.
     */
    private function formatRoles($roles): array
    {
        return $roles->map(fn($role) => $this->formatRole($role))->toArray();
    }
}
