<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Room::withCount('student');

        if ($request->filled('gender')){
            $query->where('gender', $request->gender);
        }

        return response()->json($query->orderBy('room_number')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'room_number' => 'required|string|unique:rooms,room_number',
            'gender' => 'required|in:male,female',
            'floor' => 'required|integer|min:o',
            'capacity' => 'required|integer|min:1',
            'status' =>'somtimes|in:avilable,full,maintenance',
        ]);

        $room = Room::create($data);

        return response()->json($room,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        return response()->json($room->load('students'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'room_number' => 'required|string|unique:rooms,room_number,' . $room->id,
            'gender' => 'required|in:male,female',
            'floor' => 'required|integer|min:o',
            'capacity' => 'required|integer|min:1',
            'status' =>'somtimes|in:avilable,full,maintenance',
        ]);

        $room->update($data);

        return response()->json($room);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        $room->delete();

        return response()->json(['message' => 'Room Delete']);
    }
}
