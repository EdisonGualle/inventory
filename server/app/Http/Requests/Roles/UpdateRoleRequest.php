<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name,' . $this->route('role')->id,
            ],
            'permissions' => [
                'required',
                'array',
            ],
            'permissions.*' => [
                'string',
                'exists:permissions,name',
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     * This method is called before the validation rules are applied.
     * You can use it to modify the input data.
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'name' => ucfirst(strtolower(trim($this->name))),
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Este nombre de rol ya existe.',
            'permissions.required' => 'Debe seleccionar al menos un permiso.',
            'permissions.*.exists' => 'Alguno de los permisos seleccionados no es válido.',
        ];
    }
}
