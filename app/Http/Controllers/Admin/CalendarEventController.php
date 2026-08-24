<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CalendarEvent;

class CalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        return CalendarEvent::orderBy('start_date', 'asc')->paginate($perPage);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'color' => 'required|string|max:20',
        ]);

        $event = CalendarEvent::create($validated);
        return response()->json($event, 201);
    }

    public function show($id)
    {
        return CalendarEvent::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $event = CalendarEvent::findOrFail($id);

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'color' => 'required|string|max:20',
        ]);

        $event->update($validated);
        return response()->json($event);
    }

    public function destroy($id)
    {
        $event = CalendarEvent::findOrFail($id);
        $event->delete();
        return response()->json(null, 204);
    }
}
