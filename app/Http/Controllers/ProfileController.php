<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Admin sees all agents, customers see only their own
        if ($user->peran === 'admin') {
            $agents = Agent::all();
        } else {
            $agents = Agent::where('id_pengguna', $user->id_pengguna)->get();
        }

        return view('profile.index', compact('user', 'agents'));
    }
}
