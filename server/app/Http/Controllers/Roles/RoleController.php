<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        $data = [
            'roles' => $roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'created_at' => $role->created_at->format('Y/m/d H:i:s'),

            ])
        ];

        return $this->successResponse('Roles obtenidos correctamente', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        try {
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'api',
            ]);

            $role->syncPermissions($request->permissions);

            $data = [
                'id' => $role->id,
                'name' => $role->name,
                'created_at' => $role->created_at->format('Y-m-d H:i:s'),
            ];

            return $this->successResponse('Rol creado correctamente', $data, 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Ocurrió un error al crear el rol', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $role = Role::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
        ]);

        try {
            $role->update([
                'name' => $request->name,
            ]);

            $role->syncPermissions($request->permissions);

            $data = [
                'id' => $role->id,
                'name' => $role->name,
                'updated_at' => $role->updated_at->format('Y-m-d H:i:s'),
            ];

            return $this->successResponse('Rol actualizado correctamente', $data);

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
}
