<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Job::all());
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       return response()->json($job);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
     $job->update($request->all());

        return response()->json([
            'message' => 'Job updated successfully',
            'job'     => $job,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $job->delete();

        return response()->json(['message' => 'Job deleted successfully']);
    }
}
