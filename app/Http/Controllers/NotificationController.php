<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Http\Resources\NotificationResource;
use App\Models\NotificationType;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return NotificationResource::collection(Notification::with(['user', 'type'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'type' => 'required|string',
        ]);

        $user = $request->user();
        $notificationType = NotificationType::where('type', $validated['type'])->firstOrFail();

        $notification = Notification::create([
            'message' => $validated['message'],
            'user_id' => $user->id,
            'notification_type_id' => $notificationType->id,
        ]);
        return new NotificationResource($notification);
    }

    public function update(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        $validated = $request->validate([
            'message' => 'required|string',
            'type' => 'required|string',
        ]);

        $user = $request->user();
        $notificationType = NotificationType::where('type', $validated['type'])->firstOrFail();

        $notification->update([
            'message' => $validated['message'],
            'user_id' => $user->id,
            'notification_type_id' => $notificationType->id,
        ]);
        return new NotificationResource($notification);
    }

    public function show($id)
    {
        $notification = Notification::with(['user', 'type'])->findOrFail($id);
        return new NotificationResource($notification);
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
