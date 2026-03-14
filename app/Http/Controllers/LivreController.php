<?php

namespace App\Http\Controllers;

use App\Models\Livre;
use Illuminate\Http\Request;

class LivreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Voir tous les livres
        return Livre::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Créer un livre ou ajouter un livre
        $data = $request->validate([
        'isbn' => 'required|string|unique:livres',
        'titre' => 'required|string',
        'auteur' => 'required|string',
        'categorie' => 'required|string',
        'annee_pub' => 'required|integer',
        'exemplaires_total' => 'required|integer',
        //'exemplaires_disponible' => 'required|integer'
    ]);

    // logique métier
    $data['exemplaires_disponible'] = $data['exemplaires_total'];

    $livre = Livre::create($data);

    return response()->json($livre, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $livre = Livre::find($id);
        if(!$livre){
            return response()->json([
                'message' => 'Livre non trouvé'
            ],404);
        }
        return response()->json($livre);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Livre $livre)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $livre = Livre::find($id);
        if (!$livre) {
            return response()->json([
                'message' => 'Livre non trouvé'
            ], 404);
        }
        $livre->update($request->all());

        return response()->json([
            'message' => 'Livre mis à jour',
            'data' => $livre
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $livre = Livre::find($id);

        if (!$livre) {
            return response()->json([
                'message' => 'Livre non trouvé'
            ], 404);
        }

        $livre->delete();

        return response()->json([
            'message' => 'Livre supprimé'
        ]);
    }
}
