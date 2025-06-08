<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    function showLoginForm(){
        return view('prototipos.usuario.login');
    }

    function login(Request $request){

        $request->validate([
           'email'=>'required|email',
           'password'=>'required|min:8|max:30'
        ],[
            'email.exists'=>'This email is not exists in admins table'
        ]);
        $creds = $request->only('email','password');
        
        if( Auth::attempt($creds) ){            
            return redirect()->route('reunion');               
        }else{
            return redirect()->route('login')->with('fail','Incorrect credentials');
        }
       
    }

    function showRegistrationForm(){
        return view('prototipos.usuario.register');
    }

    function register(Request $request){
        $request->validate([
            'name'=>'required',
            'email'=>'required|email|unique:usuario',
            'password'=>'required|min:8|max:30',
         ],[
             'name.required'=>'ingrese nombre',             
             'email.required'=>'ingrese correo electronico',
             'email.exists'=>'This email is not exists in admins table',
             'password.required'=>'ingrese contraseña',
         ]);


        $usuario = Usuario::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);        
        Auth::login($usuario);
        return redirect()->route('reunion');
    }

    
   function logout(){
        Auth::logout();
        return redirect('/');
    }
}
