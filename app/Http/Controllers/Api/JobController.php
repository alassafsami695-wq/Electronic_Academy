<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;

class JobController extends Controller
{
   
    public function index()
    {
        return response()->json(Job::all());
    }

    
    public function create()
    {
        //
    }

   
    public function store(Request $request)
    {
    $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'salary'      => 'nullable|numeric',
        ]);

        $job = Job::create($request->all());

        return response()->json([
            'message' => 'Job created successfully',
            'job'     => $job,
        ], 201);   
    }

    
    public function show(string $id)
    {
       return response()->json($job);
    }

   
    public function edit(string $id)
    {
        //
    }

    
    public function update(Request $request, string $id)
    {
     $job->update($request->all());

        return response()->json([
            'message' => 'Job updated successfully',
            'job'     => $job,
        ]);
    }

    
    public function destroy(string $id)
    {
         $job->delete();

        return response()->json(['message' => 'Job deleted successfully']);
    }
}
