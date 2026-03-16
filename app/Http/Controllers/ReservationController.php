<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return Reservation::all();
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
        'livre_isbn' => 'required|exists:livres,isbn'
    ]);

        $data['date_reser'] = now();
        $data['statut'] = 'active';

        $reservation = Reservation::create($data);
        $reservation -> refresh();

        return response()->json($reservation,201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        return Reservation::findOrFail($id);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $reservation = Reservation::findOrFail($id);

        $reservation->update($request->all());

        return response()->json($reservation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $reservation = Reservation::findOrFail($id);
        if(!$reservation){
             return response()->json([
                'message' => 'reservation non trouvée'
            ], 404);
        }
        $reservation ->delete();
        return response()->json([
            "message" => "Reservation supprimée"
        ]);
    }
}
