<?php

namespace App\Http\Controllers;

use App\Events\CambiarColor;
use App\Events\ClearCanvas;
use App\Events\ComponentDropped;
use App\Events\ComponentMoved;
use App\Events\ElementSelected;
use App\Events\ImportarImagen;
use App\Events\ResizeElemento;
use App\Models\Pizarra;
use App\Models\Reunion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PhpParser\Node\Stmt\TryCatch;

class PizarraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    function index()
    {
        $urlCompleta = request()->url();//ruta actual
        return view('prototipos.pizarra.pizarra',compact('urlCompleta'));
    }

    public function broadcastButtonDropped(Request $request)
    {
        $componenteData = $request->all();
        $componenteData = json_encode($componenteData);    
        broadcast(new ComponentDropped($componenteData))->toOthers();
        return response()->json(['status' => 'success']);
    }

    public function broadcastButtonMoved(Request $request)
    {
        $componenteData = $request->all();
        $componenteData = json_encode($componenteData);  
        broadcast(new ComponentMoved($componenteData))->toOthers();
        return response()->json(['status' => 'success']);
    }
    
    public function selectElement(Request $request)
    {
        $data = $request->all();
        $data = json_encode($data);  
        broadcast(new ElementSelected($data))->toOthers();
        return response()->json(['status' => 'success']);
    }
    
    public function clearCanvas(Request $request)
    {        
        $data = $request->all();
        $data = json_encode($data);  
        broadcast(new ClearCanvas($data))->toOthers();
        return response()->json(['status' => 'success']);
    }
    
    public function cambiarColor(Request $request)
    {        
        $data = $request->all();
        $data = json_encode($data);  
        broadcast(new CambiarColor($data))->toOthers();
        return response()->json(['status' => 'success']);
    }
    
    public function resizeElemento(Request $request)
    {        
        $data = $request->all();
        $data = json_encode($data);  
        broadcast(new ResizeElemento($data))->toOthers();
        return response()->json(['status' => 'success']);
    }


    public function guardar(Request $request){

        $data = $request->json()->all();
        $url = url()->previous();
        $reunion = Reunion::where('link',$url)->get();

        $pizarra = new Pizarra();
        $pizarra->namefile = $data['nameFile'];
        $pizarra->fecha = Carbon::now()->toDateString();
        $pizarra->id_reunion =$reunion[0]->id;
        //$pizarra->save();
        return response()->json(['status' => true]);
    }

    public function detectarObjeto(Request $request)
    {        
        $request->validate([
            'imagen' => 'required|image',
        ], [
            'imagen.required' => 'Debes subir una imagen.',
            'imagen.image' => 'El archivo debe ser una imagen válida.',            
        ]);

        $file = $request->file('imagen');
        
        $response = Http::attach(
            'file',
            file_get_contents($file->getRealPath()),
            $file->getClientOriginalName()
        )->post('http://localhost:8001/detect/');

        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data);
        } else {
            return response()->json(['error' => 'Error en el microservicio'], 500);
        }
    }
    
}
