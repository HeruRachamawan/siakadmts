<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Gallery;
use App\Models\Facility;
use App\Models\Setting;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\AcademicYear;

class WebsiteController extends Controller
{
    public function index()
    {
        $latestPosts = Post::where('status', 'published')->latest()->take(3)->get();
        $galleries = Gallery::latest()->take(6)->get();
        $facilities = Facility::where('status', 'published')->latest()->get();
        $settings = Setting::pluck('value', 'key');
        
        $settings['principal_name'] = 'Dr. Nama Kepsek, M.Pd.';
        $settings['principal_photo'] = 'https://ui-avatars.com/api/?name=Kepala+Sekolah&background=6366f1&color=fff&size=150';

        if (isset($settings['principal_teacher_id']) && $settings['principal_teacher_id']) {
            $principal = Teacher::find($settings['principal_teacher_id']);
            if ($principal) {
                $settings['principal_name'] = $principal->full_name;
                if ($principal->photo_url) {
                    $settings['principal_photo'] = $principal->photo_url;
                }
            }
        }

        // Ambil data Rombel Utama (Kelas Reguler Induk) pada Tahun Ajaran Aktif
        $activeYearId = AcademicYear::where('is_active', true)->value('id');

        $classesQuery = ClassRoom::query();
        if ($activeYearId && ClassRoom::where('academic_year_id', $activeYearId)->exists()) {
            $classesQuery->where('academic_year_id', $activeYearId);
        }

        // Filter: Rombel Utama adalah kelas induk reguler (mengecualikan kelas kelompok jadwal lokal khusus standalone '7' dan '8')
        $allClasses = $classesQuery->get();
        $utamaClasses = $allClasses->filter(function ($c) {
            $clean = strtolower(trim(preg_replace('/^(kelas|kls)\s*/i', '', (string)$c->name)));
            return !in_array($clean, ['7', '8']);
        })->values();

        $stats = [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'classes' => $utamaClasses->count(),
        ];

        $teachers = Teacher::with('subjects')->get();
        $classrooms = $utamaClasses->filter(function ($c) {
            return !empty($c->homeroom_teacher_id);
        })->load('homeroomTeacher.subjects')->values();
        $achievements = \App\Models\Achievement::where('status', 'published')->latest()->get();

        $data = [
            'posts' => $latestPosts,
            'galleries' => $galleries,
            'facilities' => $facilities,
            'teachers' => $teachers,
            'classrooms' => $classrooms,
            'achievements' => $achievements,
            'settings' => $settings,
            'stats' => $stats
        ];

        return response()->json($data);
    }

    public function posts()
    {
        $posts = Post::where('status', 'published')->latest()->paginate(9);
        return response()->json($posts);
    }

    public function post($slug)
    {
        $post = Post::where('slug', $slug)->where('status', 'published')->firstOrFail();
        return response()->json($post);
    }

    public function galleries()
    {
        $galleries = Gallery::latest()->paginate(12);
        return response()->json($galleries);
    }
}
