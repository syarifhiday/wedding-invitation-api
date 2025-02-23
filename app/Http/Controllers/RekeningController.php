<?php

namespace App\Http\Controllers;

use App\Models\Rekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Undangan;

class RekeningController extends Controller {

    public function store(Request $request) {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'undangan_id' => 'required|exists:undangan,id',
            'account_name' => 'required|string',
            'account_number' => 'required|string',
            'bank' => 'required|string'
        ]);

        $undangan = Undangan::where('id', $validated['undangan_id'])->where('user_id', $user->id)->first();

        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        $rekening = Rekening::create($validated);
        return response()->json($rekening, 201);
    }

    public function getByUndangan($undangan_id) {
        $user = Auth::user();
        $undangan = Undangan::where('id', $undangan_id)->where('user_id', $user->id)->first();

        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        $rekening = Rekening::where('undangan_id', $undangan_id)->get();

        if ($rekening->isEmpty()) {
            return response()->json(['message' => 'No rekening found for this invitation'], 404);
        }

        return response()->json($rekening);
    }

    public function update(Request $request, Rekening $rekening) {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'undangan_id' => 'required|exists:undangan,id',
            'account_name' => 'sometimes|string',
            'account_number' => 'sometimes|string',
            'bank' => 'sometimes|string'
        ]);

        $undangan = Undangan::where('id', $validated['undangan_id'])->where('user_id', $user->id)->first();
        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        $rekening = Rekening::where('id', $rekening->id)->where('undangan_id', $undangan->id)->first();
        if (!$rekening) {
            return response()->json(['message' => 'Rekening not found or unauthorized'], 404);
        }

        $rekening->update($validated);
        return response()->json($rekening);
    }

    public function destroy(Rekening $rekening) {
        $rekening->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
