<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Resources\SectionResource;
use Illuminate\Support\Facades\Auth;

class SectionController extends Controller
{
    public function index()
    {
        $this->authorizeSuperAdminOrAdmin();
        return SectionResource::collection(Section::all());
    }

    public function show($id)
    {
        $this->authorizeSuperAdminOrAdmin();
        $section = Section::findOrFail($id);
        return new SectionResource($section);
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdminOrAdmin();

        $request->validate([
            'SectionName' => 'required|string|max:100',
            'library_branch_id' => 'required|exists:library_branches,id',
        ]);

        $section = Section::create($request->all());
        return response()->json([
            'message' => 'Section created successfully.',
            'section' => new SectionResource($section)
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeSuperAdminOrAdmin();

        $section = Section::findOrFail($id);

        $request->validate([
            'SectionName' => 'sometimes|string|max:100',
            'library_branch_id' => 'sometimes|exists:library_branches,id',
        ]);

        $section->update($request->all());

        return response()->json([
            'message' => 'Section updated successfully.',
            'section' => new SectionResource($section)
        ]);
    }

    public function destroy($id)
    {
        $this->authorizeSuperAdminOrAdmin();

        $section = Section::findOrFail($id);
        $section->delete();

        return response()->json(['message' => 'Section deleted successfully.']);
    }

    protected function authorizeSuperAdminOrAdmin()
    {
        if (!(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin'))) {
            abort(403, 'Unauthorized. Admin or Super-admin only.');
        }
    }
}
