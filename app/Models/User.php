<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido_materno',
        'apellido_paterno',
        'email',
        'password',
        'id_dependencia',
        'id_departamento',
        'roles',
    ];

    protected $casts = [
        'roles' => 'array', // Importante para convertir JSON a array
    ];

    public function hasRole($roles)
    {
        $roles = is_array($roles) ? $roles : [$roles];

        foreach ($roles as $role) {
            if (in_array($role, $this->roles)) {
                return true;
            }
        }

        return false;
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia');
    }

    public function departamento()
{
    return $this->belongsTo(Departamento::class, 'id_departamento');
}

}
