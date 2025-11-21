<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Path;

class PathController extends Controller
{
    public function index()
    {
        return response()->json(Path::all());
    }

    public function show(Path $path)
    {
        $path->tips = json_decode($path->tips);
        return response()->json($path);
    }
}
