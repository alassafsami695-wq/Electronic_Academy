<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Path;

class PathController extends Controller
{
    
    public function index()
    {
        return response()->json(Path::all());
    }


    public function show(Path $path)
    {
        // $path->tips = json_decode($path->tips); 
        return response()->json($path);
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255|unique:paths,title',
            'description' => 'nullable|string',
            'tips'        => 'nullable|array',
        ]);

        $path = Path::create([
            'title'        => $request->title,
            'description' => $request->description,
            'tips'        => $request->tips, 
        ]);

        return response()->json([
            'message' => 'Path created successfully',
            'data'    => $path
        ], 201);
    }

   
    public function update(Request $request, Path $path)
    {
        $request->validate([
            'title'        => 'required|string|max:255|unique:paths,title,' . $path->id,
            'description' => 'nullable|string',
            'tips'        => 'nullable|array',
        ]);

        $path->update([
            'title'        => $request->title,
            'description' => $request->description,
            'tips'        => $request->tips,
        ]);

        return response()->json([
            'message' => 'Path updated successfully',
            'data'    => $path
        ]);
    }

  
    public function destroy(Path $path)
    {
        $path->delete();

        return response()->json([
            'message' => 'Path deleted successfully'
        ]);
    }
}
