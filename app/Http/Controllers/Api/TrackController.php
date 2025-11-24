<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function index()
    {
        return response()->json(
            Track::select('id','name','tips')->get()
        );
    }

    public function show(string $name)
    {
        $track = Track::where('name', $name)->first();
        if (!$track) {
            return response()->json(['message' => 'Track not found'], 404);
        }
        return response()->json($track);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|in:Backend,Frontend,AI',
            'tips' => 'required|array',
            'tips.*' => 'string|min:3',
        ]);

        $track = Track::updateOrCreate(
            ['name' => $data['name']],
            ['tips' => $data['tips']]
        );

        return response()->json($track, 201);
    }
}

