<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherController extends BaseController
{
    public function index(Request $request)
    {
        $query = Teacher::with(['user', 'subjects', 'teachingClasses', 'subjectClasses.classRoom', 'subjectClasses.subject']);

        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(function ($qq) use ($q) {
                $qq->where('full_name', 'like', "%{$q}%")
                    ->orWhere('nip', 'like', "%{$q}%")
                    ->orWhereHas('subjects', function ($qs) use ($q) {
                        $qs->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $teachers = $query->orderBy('id', 'asc')
            ->paginate($request->get('per_page', 15));

        $data = $teachers->items();
        // Format is handled in Vue component now, as we'll pass the subjects array directly

        return $this->success([
            'data' => $data,
            'meta' => [
                'current_page' => $teachers->currentPage(),
                'last_page' => $teachers->lastPage(),
                'per_page' => $teachers->perPage(),
                'total' => $teachers->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => ['required', 'string', 'max:255', 'unique:teachers,nip'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'subjects' => ['nullable', 'array'],
            'subjects.*' => ['exists:subjects,id'],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:classes,id'],
        ]);

        // Auto-generate username/password/email from NIP
        $username = $request->nip;
        $password = bcrypt($request->nip);
        $email = $request->nip . '@teacher.example.com';

        // Jika username sudah dipakai, append counter
        $originalUsername = $username;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . '_' . $i++;
        }

        // Jika email sudah dipakai, append counter
        $originalEmail = $email;
        $j = 1;
        while (User::where('email', $email)->exists()) {
            $email = $originalEmail . '_' . $j++;
        }

        $user = User::create([
            'name' => $request->full_name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => 'teacher',
        ]);

        $teacher = $user->teacher()->create([
            'nip' => $request->nip,
            'full_name' => $request->full_name,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'position' => $request->position,
            'is_ppdb_committee' => (bool) $request->input('is_ppdb_committee', false),
        ]);

        if ($request->hasFile('photo')) {
            $teacher->photo = $request->file('photo')->store('teachers/photos', 'public');
            $teacher->save();
        }

        $this->syncSubjectAssignments($teacher, $request);

        return $this->success($teacher->load(['user', 'subjects', 'teachingClasses', 'subjectClasses.classRoom', 'subjectClasses.subject']), 'Guru dibuat dengan kredensial otomatis', 201);
    }

    public function show(Teacher $teacher)
    {
        return $this->success($teacher->load(['user', 'subjects', 'teachingClasses', 'classes.academicYear', 'subjectClasses.classRoom', 'subjectClasses.subject']));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'nip' => ['sometimes', 'string', 'max:255', Rule::unique('teachers', 'nip')->ignore($teacher->id)],
            'full_name' => ['sometimes', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'subjects' => ['nullable', 'array'],
            'subjects.*' => ['exists:subjects,id'],
        ]);

        $teacher->update($request->only([
            'nip', 'full_name', 'gender', 'phone', 'position', 'is_ppdb_committee'
        ]));

        if ($request->hasFile('photo')) {
            if ($teacher->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($teacher->photo);
            }
            $teacher->photo = $request->file('photo')->store('teachers/photos', 'public');
            $teacher->save();
        }

        $this->syncSubjectAssignments($teacher, $request);

        return $this->success($teacher->fresh()->load(['user', 'subjects', 'teachingClasses', 'subjectClasses.classRoom', 'subjectClasses.subject']));
    }

    private function syncSubjectAssignments(Teacher $teacher, Request $request): void
    {
        $assignmentsRaw = $request->input('subject_assignments');
        if (is_string($assignmentsRaw)) {
            $assignmentsRaw = json_decode($assignmentsRaw, true);
        }

        if (is_array($assignmentsRaw)) {
            $teacher->subjectClasses()->delete();

            $subjectIds = [];
            $allClassIds = [];

            foreach ($assignmentsRaw as $item) {
                $subId = $item['subject_id'] ?? null;
                $classIds = (array) ($item['class_ids'] ?? []);

                if ($subId && !empty($classIds)) {
                    $subjectIds[] = $subId;
                    foreach ($classIds as $clsId) {
                        $allClassIds[] = $clsId;
                        \App\Models\TeacherSubjectClass::create([
                            'teacher_id' => $teacher->id,
                            'subject_id' => $subId,
                            'class_id' => $clsId,
                        ]);
                    }
                }
            }

            $teacher->subjects()->sync(array_unique($subjectIds));
            $teacher->teachingClasses()->sync(array_unique($allClassIds));
        } else {
            // Fallback for simple subjects and classes arrays
            $subjects = $request->input('subjects', $request->input('subjects[]', null));
            if ($subjects !== null) {
                $teacher->subjects()->sync((array) $subjects);
            }
            $classes = $request->input('classes', $request->input('classes[]', null));
            if ($classes !== null) {
                $teacher->teachingClasses()->sync((array) $classes);
            }
        }
    }

    public function destroy(Teacher $teacher)
    {
        $user = $teacher->user;
        
        if ($teacher->photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($teacher->photo);
        }
        
        $teacher->delete();
        $user?->delete();

        return $this->success(null, 'Guru dihapus');
    }

    public function resetCredentials(Teacher $teacher)
    {
        $teacher->user->update([
            'password' => bcrypt($teacher->nip),
        ]);

        return $this->success(['reset' => true], 'Kredensial direset ke NUPTK');
    }

    public function impersonate(Request $request, Teacher $teacher)
    {
        // Strict security: Only Super Admin (role: admin) can impersonate
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses ditolak. Fitur Login Sebagai Guru hanya dapat digunakan oleh Super Admin.', 403);
        }

        $user = $teacher->user;
        if (! $user) {
            $user = User::where('username', $teacher->nip)
                ->orWhere('email', $teacher->email)
                ->first();
            if ($user) {
                $teacher->user_id = $user->id;
                $teacher->save();
            } else {
                return $this->error('Guru ini belum memiliki akun pengguna yang terdaftar.', 422);
            }
        }

        // Generate Sanctum token for target teacher user
        $token = $user->createToken('impersonation-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => \App\Http\Controllers\Auth\AuthController::formatUserPayload($user),
            'impersonated' => true,
        ], "Berhasil masuk sebagai {$teacher->full_name}");
    }
}

