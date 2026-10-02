<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::with('room');

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        return response()->json($query->orderBy('name')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'       => 'required|exists:users,id|unique:students,user_id',
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:students,email',
            'gender'        => 'required|in:male,female',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:255',
            'room_id'       => 'nullable|exists:rooms,id',
            'date_of_birth' => 'nullable|date',
        ]);

        if (!empty($data['room_id'])) {
            $error = $this->roomProblem($data['room_id'], $data['gender']);
            if ($error) {
                return response()->json(['errors' => ['room_id' => [$error]]], 422);
            }
        }

        $student = Student::create($data);

        return response()->json($student->load('room'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        return response()->json($student->load(['room', 'user']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'email'         => 'sometimes|email|unique:students,email,' . $student->id,
            'gender'        => 'sometimes|in:male,female',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:255',
            'room_id'       => 'nullable|exists:rooms,id',
            'date_of_birth' => 'nullable|date',
        ]);

        $gender = $data['gender'] ?? $student->gender;

        if (!empty($data['room_id']) && $data['room_id'] != $student->room_id) {
            $error = $this->roomProblem($data['room_id'], $gender);
            if ($error) {
                return response()->json(['errors' => ['room_id' => [$error]]], 422);
            }
        }

        $student->update($data);

        return response()->json($student->load('room'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json(['message' => 'Student deleted']);
    }

    public function assignRoom(Request $request, Student $student)
    {
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
        ]);

        $error = $this->roomProblem($data['room_id'], $student->gender);
        if ($error) {
            return response()->json(['errors' => ['room_id' => [$error]]], 422);
        }

        $student->update(['room_id' => $data['room_id']]);

        return response()->json($student->load('room'));
    }

    private function roomProblem(int $roomId, ?string $studentGender): ?string
    {
        $room = Room::withCount('students')->find($roomId);

        if ($room->gender !== $studentGender) {
            return "This room is for {$room->gender} students only.";
        }

        if ($room->students_count >= $room->capacity) {
            return 'This room is already full.';
        }

        return null;
    }
}
