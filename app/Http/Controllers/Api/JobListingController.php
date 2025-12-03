<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobListing;
use App\Http\Resources\JobListingResource;

class JobListingController extends Controller
{
    public function index()
    {
        return JobListingResource::collection(JobListing::all())
            ->additional(['message' => 'تم جلب الوظائف بنجاح']);
    }

    public function show(string $id)
    {
        $job = JobListing::findOrFail($id);
        return (new JobListingResource($job))
            ->additional(['message' => 'تم جلب الوظيفة بنجاح']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'salary'      => 'nullable|numeric|min:0',
        ]);

        $job = JobListing::create($request->only(['title', 'description', 'salary']));

        return (new JobListingResource($job))
            ->additional(['message' => 'تم إنشاء الوظيفة بنجاح'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'salary'      => 'nullable|numeric|min:0',
        ]);

        $job = JobListing::findOrFail($id);
        $job->update($request->only(['title', 'description', 'salary']));
        $job->refresh();

        return (new JobListingResource($job))
            ->additional(['message' => 'تم تحديث الوظيفة بنجاح']);
    }

    public function destroy(string $id)
    {
        $job = JobListing::findOrFail($id);
        $job->delete();

        return response()->json(['message' => 'تم حذف الوظيفة بنجاح']);
    }
}
