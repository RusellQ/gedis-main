<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
            'debe_cambiar_password' => true,
            'roles' => $request->roles,
            'id_dependencia' => $request->id_dependencia,
            'id_departamento' => $request->id_departamento,
        ]);

        return response()->json(['message' => '¡Usuario creado exitosamente! La contraseña es temporal y deberá cambiarla en su primer inicio de sesión.']);
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

            if (Auth::user()->debe_cambiar_password) {
                return redirect()->route('password.cambiar');
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function mostrarCambioContrasena()
    {
        if (! Auth::user()->debe_cambiar_password) {
            return redirect('/dashboard');
        }

        return view('auth.cambiar-contrasena');
    }

    public function guardarCambioContrasena(Request $request)
    {
        $user = Auth::user();

        if (! $user->debe_cambiar_password) {
            return redirect('/dashboard');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'Escribe la contraseña nueva.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación no coincide con la contraseña.',
        ]);

        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'La contraseña nueva debe ser distinta de la temporal.',
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->debe_cambiar_password = false;
        $user->save();

        $request->session()->regenerate();

        return redirect('/dashboard');
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
            'id_dependencia' => 'required|integer|exists:dependencias,id',
            'id_departamento' => [
                'required',
                'integer',
                Rule::exists('departamentos', 'id')->where(function ($query) use ($request) {
                    $query->where('id_dependencia', $request->input('id_dependencia'));
                }),
            ],
            'roles' => 'array',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8';
        }

        // Aplicar la validación del correo electrónico solo si se proporciona un nuevo correo electrónico
        if ($request->email !== $user->email) {
            $rules['email'] = 'required|string|email|max:255|unique:users,email';
        } else {
            $rules['email'] = 'required|string|email|max:255';
        }

        $validatedData = $request->validate($rules, [
            'password.min' => 'La contraseña temporal debe tener al menos 8 caracteres.',
            'id_dependencia.required' => 'Selecciona una dependencia.',
            'id_dependencia.exists' => 'La dependencia seleccionada no existe.',
            'id_departamento.required' => 'Selecciona un departamento.',
            'id_departamento.exists' => 'El departamento no pertenece a la dependencia seleccionada.',
        ]);

        unset($validatedData['password']);

        $user->update($validatedData);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
                'debe_cambiar_password' => true,
            ]);
        }

        return response()->json(['message' => 'Usuario actualizado exitosamente']);
    }

    public function currentUser()
    {
        $user = Auth::user()->load(['dependencia', 'departamento.dependencia']);
        return response()->json($user);
    }
}
