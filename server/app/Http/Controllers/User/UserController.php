<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $users = User::where('name', 'ilike', '%' . $search . '%')
            ->orderBy('id', 'desc')
            ->get();

        $data = [
            'users' => $users->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'full_name' => $user->name . ' ' . $user->surname,
                'email' => $user->email,
                'role' => [
                    'id' => $user->role->id,
                    'name' => $user->role->name,
                ],
                'sucursale' => [
                    'id' => $user->sucursale->id,
                    'name' => $user->sucursale->name,
                ],
                'avatar' => $user->avatar ? env("APP_URL") . "/storage/" . $user->avatar : null,
                'type_document' => $user->type_document,
                'number_document' => $user->number_document,
                'gender' => $user->gender,
                'phone' => $user->phone,
                'created_at' => $user->created_at?->format('Y-m-d  A H:i'),
            ])
        ];

        return $this->successResponse('Usuarios obtenidos correctamente', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'sucursale_id' => 'required|exists:sucursales,id',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phone' => 'nullable|string|max:255',
            'type_document' => 'required|string|max:255',
            'number_document' => 'required|numeric',
            'gender' => 'required|in:1,2',
        ]);

        try {
            // Guardar imagen
            if ($request->hasFile('avatar')) {
                $path = Storage::putFile("users", $request->file("imagen"));
                $request->merge(['avatar' => $path]);
            }

            // Encriptar contraseña
            if ($request->password) {
                $request->merge(['password' => bcrypt($request->password)]);
            }

            $user = User::create($request->all());
            $role = Role::findOrFail($request->role_id);
            $user->assignRole($role);

            $data = [
                'user' => $user->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'full_name' => $user->name . ' ' . $user->surname,
                    'email' => $user->email,
                    'role' => [
                        'id' => $user->role->id,
                        'name' => $user->role->name,
                    ],
                    'sucursale' => [
                        'id' => $user->sucursale->id,
                        'name' => $user->sucursale->name,
                    ],
                    'avatar' => $user->avatar ? env("APP_URL") . "/storage/" . $user->avatar : null,
                    'type_document' => $user->type_document,
                    'number_document' => $user->number_document,
                    'gender' => $user->gender,
                    'phone' => $user->phone,
                    'created_at' => $user->created_at?->format('Y-m-d  A H:i'),
                ])
            ];

            return $this->successResponse('Usuario creado correctamente', $data, 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Ocurrió un error al crear el usuario', 500);
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
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'surname' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id',
            'sucursale_id' => 'nullable|exists:sucursales,id',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phone' => 'nullable|string|max:255',
            'type_document' => 'nullable|string|max:255',
            'number_document' => 'nullable|numeric',
            'gender' => 'nullable|in:1,2',
        ]);

        try {
            // Guardar imagen
            if ($request->hasFile('avatar')) {

                // Eliminar imagen anterior
                if ($user->avatar) {
                    Storage::delete($user->avatar);
                }

                $path = Storage::putFile("users", $request->file("imagen"));
                $request->merge(['avatar' => $path]);
            }

            // Encriptar contraseña
            if ($request->password) {
                $request->merge(['password' => bcrypt($request->password)]);
            }

            $user ->update($request->all());
            if ($request->role_id != $user->role_id) {
                // Eliminar rol anterior
                $role_old = Role::findOrFail($request->role_id);
                $user->removeRole($role_old);

                // Asignar nuevo rol
                $role_new = Role::findOrFail($request->role_id);
                $user->assignRole($role_new);
            }

            $data = [
                'user' => $user->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'full_name' => $user->name . ' ' . $user->surname,
                    'email' => $user->email,
                    'role' => [
                        'id' => $user->role->id,
                        'name' => $user->role->name,
                    ],
                    'sucursale' => [
                        'id' => $user->sucursale->id,
                        'name' => $user->sucursale->name,
                    ],
                    'avatar' => $user->avatar ? env("APP_URL") . "/storage/" . $user->avatar : null,
                    'type_document' => $user->type_document,
                    'number_document' => $user->number_document,
                    'gender' => $user->gender,
                    'phone' => $user->phone,
                    'created_at' => $user->created_at?->format('Y-m-d  A H:i'),
                ])
            ];

            return $this->successResponse('Usuario creado correctamente', $data, 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Ocurrió un error al crear el usuario', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        try {
            $user->delete();

            return $this->successResponse('Usuario eliminado correctamente');

        } catch (\Exception $e) {
            return $this->errorResponse('Ocurrió un error al eliminar el usuario', 500);
        }
    }
}
