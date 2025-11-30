<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;

class JobController extends Controller
{
    
    //------------------------عرض جميع الوظائف-----------------------
     
    public function index()
    {
        return response()->json(Job::all());
    }

 
    public function create()
    {
        //
    }

    
    // ----------------------تخزين وظيفة جديدة-----------------
     
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

    
    //---------------------------عرض وظيفة محددة-----------------
     
    public function show(string $id)
    {
        $job = Job::findOrFail($id);

        return response()->json($job);
    }

    
    public function edit(string $id)
    {
        //
    }

    
    //----------------------------تحديث بيانات وظيفة----------------
     
    public function update(Request $request, string $id)
    {
        $job = Job::findOrFail($id);

        $job->update($request->all());

        return response()->json([
            'message' => 'Job updated successfully',
            'job'     => $job,
        ]);
    }

    
    // ---------------------------حذف وظيفة----------------------
     
    public function destroy(string $id)
    {
        $job = Job::findOrFail($id);

        $job->delete();

        return response()->json(['message' => 'Job deleted successfully']);
    }
}
