<?php

namespace App\Http\Controllers;

use App\Events\FinalizarReunion;
use App\Models\Reunion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReunionController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('prototipos.reunion.reunion');
    }

    public function create()
    {        
        $codigoUnico = Str::uuid();
        $link = "http://localhost:8000/pizarra/".$codigoUnico ;
        $reunion = new Reunion();           
        $reunion->fecha = Carbon::now()->toDateString();
        $reunion->id_usuario = auth()->user()->id;
        $reunion->link = $link;
        $reunion->rol_acceso = 'Administrador';
        $reunion->estado = 'valido';
        $reunion->save();        
        return redirect()->route('pizarra',[$codigoUnico]);        
    }

    public function join(Request $request)
    {
        $link = $request->get('collaborationId');   
        $divLink = explode("/",$link);
        $codigoUnico = end($divLink);   
        $reuniones = Reunion::where('link',$link)->get();  
        
        if(empty( trim($link)) || count($reuniones) === 0 ){//no hay link o no existe ese link
            return redirect('/reunion');
        }else{                                              
            foreach($reuniones as $reunion){
                if($reunion->rol_acceso === "Administrador" && $reunion->estado === "no valido"){
                    return redirect('/reunion');
                }else{
                    if($reunion->id_usuario === auth()->user()->id && $link === $reunion->link){
                        //ya existe admin o invitado y es valido entonces redirige
                        return redirect()->route('pizarra',[$codigoUnico]);
                    }
                }
            }
            $reunion = new Reunion();
            $reunion->fecha = Carbon::now()->toDateString();
            $reunion->id_usuario = auth()->user()->id;
            $reunion->link = $link;
            $reunion->rol_acceso = 'Invitado';
            $reunion->estado = 'valido';
            $reunion->save();                
            return redirect()->route('pizarra',[$codigoUnico]);                                
        }
    }

    
    function finalizar()
    {
        $url = url()->previous();//ruta anterior        
        $userActual = auth()->user();
        $permiso = Reunion::where('id_usuario',$userActual->id)
                            ->where('link',$url)->get();
        $permiso = $permiso[0]; 

        if( $permiso->rol_acceso === "Administrador"){

            $reuniones = Reunion::where('link',$url)->get();            
            foreach($reuniones as $reunion){
                $reunion->estado = "no valido";
                $reunion->update();                   
            }
        }
        return redirect('/reunion');
    }


    function broadcastFinalizar(Request $request){
        $url = url()->previous();
        $userActual = auth()->user();
        $permiso = Reunion::where('id_usuario',$userActual->id)
                            ->where('link',$url)->get();
        $permiso = $permiso[0]; 

        if( $permiso->rol_acceso === "Administrador"){
            broadcast(new FinalizarReunion('ok'))->toOthers();            
        }else{
            broadcast(new FinalizarReunion('no'))->toOthers();
        }
        return response()->json(['status' => 'success']);
    }

}
