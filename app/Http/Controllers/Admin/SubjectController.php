<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends BaseController
{
    public function index(Request $request)
    {
        $query = Subject::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->input('search')}%")
                    ->orWhere('code', 'like', "%{$request->input('search')}%");
            });
        }

        $subjects = $query->orderBy('code')->paginate($request->get('per_page', 15));

        return $this->success($this->paginate($subjects, $subjects->items()));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $code = $this->generateCode($request->name);

        $subject = Subject::create([
            'code' => $code,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return $this->success($subject, 'Mata pelajaran dibuat', 201);
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $data = $request->only(['name', 'description']);

        if (isset($data['name'])) {
            $data['code'] = $this->generateCode($data['name'], $subject->id);
        }

        $subject->update($data);

        return $this->success($subject);
    }

    protected function generateCode(string $name, ?int $ignoreId = null): string
    {
        $words = explode(' ', preg_replace('/[^a-zA-Z\s]/', '', $name));
        $initial = '';
        foreach ($words as $word) {
            if (!empty($word) && strlen($initial) < 3) {
                $initial .= strtoupper(substr($word, 0, 1));
            }
        }
        $initial = strtoupper(substr($initial, 0, 3));
        if (empty($initial)) {
            $initial = 'MAP';
        }

        $counter = 1;
        do {
            $code = $initial . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $counter++;
        } while (
            Subject::where('code', $code)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        );

        return $code;
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return $this->success(null, 'Mata pelajaran dihapus');
    }
}
