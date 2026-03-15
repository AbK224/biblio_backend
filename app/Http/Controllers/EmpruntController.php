<?php

namespace App\Http\Controllers;

use App\Models\Emprunt;
use App\Services\EmpruntService;
use Illuminate\Http\Request;


class EmpruntController extends Controller
{
    private $service;

    public function __construct(EmpruntService $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return Emprunt::with(['utilisateur','livre'])->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->validate([
        'utilisateur_id' => 'required|exists:utilisateurs,id',
        'livre_isbn' => 'required|exists:livres,isbn',
        'date_emprunt' => 'required|date'
        //'date_retour_prevue' => 'required|date'

    ]);


    try {

        $emprunt = $this->service->creerEmprunt($data);

        return response()->json($emprunt,201);

    } catch(\Exception $e){

        return response()->json([
            "message"=>$e->getMessage()
        ],400);

    }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        return Emprunt::with(['utilisateur','livre'])->findOrFail($id);
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Emprunt $emprunt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        //
        $emprunt = Emprunt::find($id);
        if (!$emprunt) {
            return response()->json([
                'message' => 'emprunt non trouvé'
            ], 404);
        }
        $emprunt->update($request->all());

        return response()->json([
            'message' => 'emprunt mis à jour',
            'data' => $emprunt
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $emprunt = Emprunt::find($id);
        if (!$emprunt) {
            return response()->json([
                'message' => 'emprunt non trouvé'
            ], 404);
        }
        $emprunt ->delete();
        return response()->json([
            "message" => "Emprunt supprimé"
        ]);
    }
}
