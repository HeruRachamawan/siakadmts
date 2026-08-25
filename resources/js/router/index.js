import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

function roleDashboard(r) {
    if (r === 'admin') return '/admin/dashboard';
    if (r === 'teacher') return '/teacher/dashboard';
    if (r === 'student') return '/student/dashboard';
    return '/login';
}

const userFields = () => [
    { name: 'name', label: 'Nama Pengguna', type: 'text' },
    { name: 'email', label: 'Email', type: 'email' },
    { name: 'password', label: 'Password', type: 'password' },
    { name: 'role', label: 'Peran', type: 'select', options: [
        { value: 'admin', label: 'Admin' },
        { value: 'teacher', label: 'Guru' },
        { value: 'student', label: 'Siswa' },
    ] },
];

const postFields = () => [
    { name: 'title', label: 'Judul', type: 'text' },
    { name: 'content', label: 'Konten / Isi Artikel', type: 'textarea' },
    { name: 'image', label: 'Gambar (Opsional)', type: 'file' },
    { name: 'status', label: 'Status', type: 'select', options: [
        { value: 'published', label: 'Published' },
        { value: 'draft', label: 'Draft' },
    ] },
];

const galleryFields = () => [
    { name: 'title', label: 'Judul Foto', type: 'text' },
    { name: 'description', label: 'Deskripsi', type: 'textarea' },
    { name: 'image', label: 'Pilih Gambar', type: 'file' },
];

const routes = [
    { path: '/', component: () => import('../views/public/Home.vue') },
    { path: '/ppdb', component: () => import('../views/public/PpdbRegister.vue') },
    { path: '/login', component: () => import('../views/Login.vue'), meta: { requiresGuest: true } },
    { path: '/register', component: () => import('../views/Register.vue'), meta: { requiresGuest: true } },
    { path: '/dashboard', component: () => import('../views/DashboardHome.vue'), meta: { requiresAuth: true } },

    // Admin Routes
    { path: '/admin/dashboard', component: () => import('../views/AdminDashboard.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/ppdb', component: () => import('../views/AdminPpdb.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/users', component: () => import('../components/CrudPage.vue'), meta: { requiresAuth: true, role: 'admin' }, props: { endpoint: 'admin/users', resource: 'admin/users', title: 'Pengguna', formFields: userFields(), fields: userFields(), columns: [
        { label: 'Nama', field: 'name' },
        { label: 'Email', field: 'email' },
        { label: 'Peran', field: 'role' },
    ], hideFields: ['password'] } },
    { path: '/admin/students', component: () => import('../views/AdminStudents.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/teachers', component: () => import('../views/AdminTeachers.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/classes', component: () => import('../views/AdminClasses.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/subjects', component: () => import('../views/AdminSubjects.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/schedules', component: () => import('../views/AdminSchedules.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/print-center', component: () => import('../views/AdminPrintCenter.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/attendance-reports', component: () => import('../views/AdminAttendanceReports.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/daily-student-attendance', component: () => import('../views/AdminDailyAttendanceMonitoring.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/teacher-presensi-monitoring', component: () => import('../views/AdminTeacherAttendanceMonitoring.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/settings', component: () => import('../views/AdminSettings.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/academic-years', component: () => import('../views/AdminAcademicYears.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/grades', component: () => import('../views/AdminGrades.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/attendances', component: () => import('../views/AdminAttendances.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/attendance', component: () => import('../views/TeacherAttendance.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/profile', component: () => import('../views/AdminProfile.vue'), meta: { requiresAuth: true, role: 'admin' } },

    { path: '/admin/posts', component: () => import('../components/CrudPage.vue'), meta: { requiresAuth: true, role: 'admin' }, props: { endpoint: 'admin/posts', resource: 'admin/posts', title: 'Berita & Artikel', formFields: postFields(), fields: postFields(), columns: [
        { label: 'Gambar', field: 'image' },
        { label: 'Judul', field: 'title' },
        { label: 'Status', field: 'status' },
    ] } },
    { path: '/admin/galleries', component: () => import('../components/CrudPage.vue'), meta: { requiresAuth: true, role: 'admin' }, props: { endpoint: 'admin/galleries', resource: 'admin/galleries', title: 'Galeri Foto', formFields: galleryFields(), fields: galleryFields(), columns: [
        { label: 'Gambar', field: 'image' },
        { label: 'Judul Foto', field: 'title' },
        { label: 'Deskripsi', field: 'description' },
    ] } },
    { path: '/admin/calendar-events', component: () => import('../views/AdminCalendar.vue'), meta: { requiresAuth: true, role: 'admin' } },
    { path: '/admin/facilities', component: () => import('../components/CrudPage.vue'), meta: { requiresAuth: true, role: 'admin' }, props: { endpoint: 'admin/facilities', resource: 'admin/facilities', title: 'Sarana & Prasarana', formFields: [
        { name: 'name', label: 'Nama Fasilitas', type: 'text' },
        { name: 'description', label: 'Deskripsi', type: 'textarea' },
        { name: 'image', label: 'Gambar', type: 'file' },
        { name: 'status', label: 'Status', type: 'select', options: [
            { value: 'published', label: 'Published' },
            { value: 'draft', label: 'Draft' },
        ] }
    ], fields: [
        { name: 'name', label: 'Nama Fasilitas', type: 'text' },
        { name: 'description', label: 'Deskripsi', type: 'textarea' },
        { name: 'image', label: 'Gambar', type: 'file' },
        { name: 'status', label: 'Status', type: 'select', options: [
            { value: 'published', label: 'Published' },
            { value: 'draft', label: 'Draft' },
        ] }
    ], columns: [
        { label: 'Gambar', field: 'image' },
        { label: 'Nama Fasilitas', field: 'name' },
        { label: 'Deskripsi', field: 'description' },
        { label: 'Status', field: 'status' },
    ] } },
    { path: '/admin/achievements', component: () => import('../components/CrudPage.vue'), meta: { requiresAuth: true, role: 'admin' }, props: { endpoint: 'admin/achievements', resource: 'admin/achievements', title: 'Prestasi Siswa', formFields: [
        { name: 'title', label: 'Judul Prestasi', type: 'text' },
        { name: 'student_name', label: 'Nama Siswa', type: 'text' },
        { name: 'level', label: 'Tingkat (mis: Nasional)', type: 'text' },
        { name: 'year', label: 'Tahun', type: 'number' },
        { name: 'description', label: 'Deskripsi', type: 'textarea' },
        { name: 'image', label: 'Foto/Gambar', type: 'file' },
        { name: 'status', label: 'Status', type: 'select', options: [
            { value: 'published', label: 'Published' },
            { value: 'draft', label: 'Draft' },
        ] }
    ], fields: [
        { name: 'title', label: 'Judul Prestasi', type: 'text' },
        { name: 'student_name', label: 'Nama Siswa', type: 'text' },
        { name: 'level', label: 'Tingkat (mis: Nasional)', type: 'text' },
        { name: 'year', label: 'Tahun', type: 'number' },
        { name: 'description', label: 'Deskripsi', type: 'textarea' },
        { name: 'image', label: 'Foto/Gambar', type: 'file' },
        { name: 'status', label: 'Status', type: 'select', options: [
            { value: 'published', label: 'Published' },
            { value: 'draft', label: 'Draft' },
        ] }
    ], columns: [
        { label: 'Foto', field: 'image' },
        { label: 'Judul', field: 'title' },
        { label: 'Siswa', field: 'student_name' },
        { label: 'Tingkat', field: 'level' },
        { label: 'Tahun', field: 'year' },
    ] } },

    // Teacher Routes
    { path: '/teacher/dashboard', component: () => import('../views/TeacherDashboard.vue'), meta: { requiresAuth: true, role: 'teacher' } },
    { path: '/teacher/ppdb', component: () => import('../views/AdminPpdb.vue'), meta: { requiresAuth: true, role: 'teacher' } },
    { path: '/teacher/profile', component: () => import('../views/TeacherProfile.vue'), meta: { requiresAuth: true, role: 'teacher' } },
    { path: '/teacher/presensi', component: () => import('../views/TeacherPresensi.vue'), meta: { requiresAuth: true, role: 'teacher' } },
    { path: '/teacher/presensi-recap', component: () => import('../views/TeacherAttendanceRecap.vue'), meta: { requiresAuth: true, role: 'teacher' } },
    { path: '/teacher/students', component: () => import('../views/TeacherStudents.vue'), meta: { requiresAuth: true, role: 'teacher' } },
    { path: '/teacher/homeroom-attendance', component: () => import('../views/HomeroomDailyAttendance.vue'), meta: { requiresAuth: true, role: 'teacher' } },
    { path: '/teacher/attendance', component: () => import('../views/TeacherAttendance.vue'), meta: { requiresAuth: true, role: 'teacher' } },
    { path: '/teacher/attendance-reports', component: () => import('../views/AdminAttendanceReports.vue'), meta: { requiresAuth: true, role: 'teacher' } },
    { path: '/teacher/grades', component: () => import('../views/TeacherGrades.vue'), meta: { requiresAuth: true, role: 'teacher' } },
    { path: '/teacher/schedules', component: () => import('../views/TeacherSchedules.vue'), meta: { requiresAuth: true, role: 'teacher' } },
    { path: '/teacher/calendar', component: () => import('../views/TeacherCalendar.vue'), meta: { requiresAuth: true, role: 'teacher' } },

    // Student Routes
    { path: '/student/dashboard', component: () => import('../views/StudentDashboard.vue'), meta: { requiresAuth: true, role: 'student' } },
    { path: '/student/profile', component: () => import('../views/StudentProfile.vue'), meta: { requiresAuth: true, role: 'student' } },
    { path: '/student/attendances', component: () => import('../views/StudentAttendance.vue'), meta: { requiresAuth: true, role: 'student' } },
    { path: '/student/transcript', component: () => import('../views/StudentTranscript.vue'), meta: { requiresAuth: true, role: 'student' } },

    { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

let initialized = false;

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (!initialized && auth.token) {
        await auth.fetchMe();
        initialized = true;
    } else {
        initialized = true;
    }

    if (to.meta.requiresAuth) {
        if (!auth.token) {
            return { path: '/login', query: { redirect: to.fullPath } };
        }
        if (to.meta.role && auth.user?.role !== to.meta.role) {
            return roleDashboard(auth.user?.role);
        }
    }

    if (to.meta.requiresGuest && auth.token && auth.user) {
        return roleDashboard(auth.user.role);
    }

    return true;
});

export default router;
