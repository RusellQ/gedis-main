<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{




    public function store(Request $request)
    {
        // Valida los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'roles' => 'array',
            'id_dependencia' => 'required|integer|exists:dependencias,id',
            'id_departamento' => 'required|integer|exists:departamentos,id',
        ]);

        // Crea el usuario
        $user = User::create([
            'nombre' => $request->nombre,
            'apellido_materno' => $request->apellido_materno,
            'apellido_paterno' => $request->apellido_paterno,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'roles' => $request->roles,
            'id_dependencia' => $request->id_dependencia,
            'id_departamento' => $request->id_departamento,
        ]);

        return response()->json(['message' => '¡Usuario creado exitosamente!']);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }


    public function dashboard()
    {
        $user = Auth::user();
        $userName = $user->nombre . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno;
        return view('administrativas.dashboard', ['userName' => $userName]);
    }


    public function listUsers()
    {
        $rolesAdministrativos = ['editor', 'lector', 'consultor'];
        $users = User::where(function ($query) use ($rolesAdministrativos) {
            foreach ($rolesAdministrativos as $rol) {
                $query->orWhereJsonContains('roles', $rol);
            }
        })->get();
        return response()->json($users);
    }


    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'nombre' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'area_asignada' => 'nullable|string|max:255', // validación para el área administrativa
            'roles' => 'array'
        ];

        // Aplicar la validación del correo electrónico solo si se proporciona un nuevo correo electrónico
        if ($request->email !== $user->email) {
            $rules['email'] = 'required|string|email|max:255|unique:users,email';
        } else {
            $rules['email'] = 'required|string|email|max:255';
        }

        // Validar la solicitud
        $validatedData = $request->validate($rules);

        // Actualizar los campos del usuario
        $user->update($validatedData);

        // Si la contraseña está presente y no está vacía, actualizarla
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json(['message' => 'Usuario actualizado exitosamente']);
    }

    public function currentUser()
    {
        $user = Auth::user()->load(['dependencia', 'departamento.dependencia']);
        return response()->json($user);
    }
}
