<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Http\Resources\AnnouncementResource;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class AnnouncementController extends Controller
{
    public function __construct()
    {
        // This will automatically map controller methods to policy methods
        $this->authorizeResource(Announcement::class, 'announcement');
    }

    public function index()
    {
        $this->authorize('viewAny', Announcement::class);

        $authUser = auth()->user();

        $query = Announcement::with('user')->latest();

        if ($authUser->hasRole('superadmin')) {
            // Superadmin sees all
            $announcements = $query->get();
        } else {
            $userBranchId = $authUser->library_branch_id;

            // Get all user IDs from the same library branch
            $superadminUserIds = \App\Models\User::query()
                ->role('superadmin')
                ->pluck('id');

            $sameBranchUserIds = \App\Models\User::where('library_branch_id', $userBranchId)
                ->pluck('id');

            $announcements = $query
                ->where(function ($q) use ($superadminUserIds, $sameBranchUserIds) {
                    $q->whereIn('user_id', $superadminUserIds)
                        ->orWhereIn('user_id', $sameBranchUserIds);
                })
                ->get();
        }

        return AnnouncementResource::collection($announcements);
    }

    public function StudentAnnouncementIndex()
    {
        $this->authorize('viewAny', Announcement::class);

        $authUser = auth()->user();
        $query = Announcement::with('user')->where('is_published', true)->latest();

        if ($authUser->hasRole('superadmin')) {
            $announcements = $query->get();
        } else {
            $userBranchId = $authUser->library_branch_id;

            $sameBranchUserIds = \App\Models\User::where('library_branch_id', $userBranchId)->pluck('id');
            $superadminUserIds = User::query()->role('superadmin')->pluck('id');

            $announcements = $query
                ->where(function ($q) use ($superadminUserIds, $sameBranchUserIds) {
                    $q->whereIn('user_id', $superadminUserIds)
                        ->orWhereIn('user_id', $sameBranchUserIds);
                })
                ->get();
        }

        return AnnouncementResource::collection($announcements);
    }

    public function librarianindex()
    {
        $this->authorize('viewAny', Announcement::class);
        $announcements = Announcement::latest()->get();
        return AnnouncementResource::collection($announcements);
    }
    public function adminindex()
    {
        $this->authorize('viewAny', Announcement::class);
        $announcements = Announcement::latest()->get();
        return AnnouncementResource::collection($announcements);
    }

    public function superadminindex()
    {
        $this->authorize('viewAny', Announcement::class);
        $announcements = Announcement::latest()->get();
        return AnnouncementResource::collection($announcements);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Announcement::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_published' => 'sometimes|boolean'
        ]);

        $validated['user_id'] = Auth::id();
        $announcement = Announcement::create($validated);

        return new AnnouncementResource($announcement);
    }

    public function show(Announcement $announcement)
    {
        $this->authorize('view', $announcement);
        return new AnnouncementResource($announcement);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorize('update', $announcement);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'is_published' => 'sometimes|boolean'
        ]);

        $announcement->update($validated);
        return new AnnouncementResource($announcement);
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorize('delete', $announcement);
        $announcement->delete();
        return response()->noContent();
    }

    public function togglePublish(Announcement $announcement)
    {
        $this->authorize('update', $announcement);
        $announcement->update([
            'is_published' => !$announcement->is_published
        ]);
        return new AnnouncementResource($announcement);
    }
}
