<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;

class UtilisateurController extends Controller
{
    private function genererMatricule($type)
    {

        $prefixes = [
        'etudiant' => 'ETU',
        'enseignant' => 'ENS',
        'administratif' => 'ADM'
    ];

    $prefix = $prefixes[$type];

    do {

        // nombre aléatoire entre 00001 et 99999
        $random = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $matricule = $prefix . '-' . $random;

        } while (Utilisateur::where('matricule', $matricule)->exists());

    return $matricule;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
         return Utilisateur::all();
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
        'nom' => 'required|string',
        'prenom' => 'required|string',
        'type' => 'required|string'
    ]);

    $data['matricule'] = $this->genererMatricule($data['type']);

    $utilisateur = Utilisateur::create($data);

    return response()->json($utilisateur, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        return Utilisateur::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Utilisateur $utilisateur)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $utilisateur = Utilisateur::findOrFail($id);

        $utilisateur->update($request->all());

        return response()->json($utilisateur);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        Utilisateur::destroy($id);

        return response()->json([
            "message" => "Utilisateur supprimé"
        ]);
    }
}
