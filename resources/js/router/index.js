import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

function roleDashboard(r) {
    if (r === 'admin') return '/admin/dashboard';
    if (r === 'operator') return '/operator/dashboard';
    if (r === 'kurikulum') return '/kurikulum/dashboard';
    if (r === 'teacher') return '/teacher/dashboard';
    if (r === 'student') return '/student/dashboard';
    return '/login';
}

const userFields = () => [
    { name: 'name', label: 'Nama Lengkap Pengguna', type: 'text', placeholder: 'Contoh: Bapak Ahmad Fauzi, S.Pd' },
    { name: 'username', label: 'Username Login', type: 'text', placeholder: 'Contoh: operator, kurikulum, kepsek' },
    { name: 'password', label: 'Password', type: 'password', placeholder: 'Minimal 6 karakter' },
    { name: 'role', label: 'Peran Akun', type: 'select', options: [
        { value: 'admin', label: 'Administrator (Superadmin / Kepala Madrasah)' },
        { value: 'operator', label: 'Operator / Tata Usaha (TU)' },
        { value: 'kurikulum', label: 'Waka Kurikulum' },
        { value: 'teacher', label: 'Guru Mata Pelajaran' },
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
    { path: '/', component: () => import('../views/public/Home.vue'), meta: { title: 'Portal Resmi Madrasah' } },
    { path: '/ppdb', component: () => import('../views/public/PpdbRegister.vue'), meta: { title: 'PPDB Online Siswa Baru' } },
    { path: '/login', component: () => import('../views/Login.vue'), meta: { requiresGuest: true, title: 'Masuk Portal' } },
    { path: '/register', component: () => import('../views/Register.vue'), meta: { requiresGuest: true, title: 'Registrasi Akun' } },
    { path: '/dashboard', component: () => import('../views/DashboardHome.vue'), meta: { requiresAuth: true, title: 'Dashboard' } },

    // Operator TU Dedicated Routes
    { path: '/operator/dashboard', component: () => import('../views/OperatorDashboard.vue'), meta: { requiresAuth: true, roles: ['operator', 'admin'], title: 'Dashboard Operator TU' } },
    { path: '/operator/letters', component: () => import('../views/AdminLetters.vue'), meta: { requiresAuth: true, roles: ['operator', 'admin'], title: 'Buku Agenda Persuratan' } },

    // Waka Kurikulum Dedicated Routes
    { path: '/kurikulum/dashboard', component: () => import('../views/KurikulumDashboard.vue'), meta: { requiresAuth: true, roles: ['kurikulum', 'admin'], title: 'Dashboard Waka Kurikulum' } },

    // Admin & Shared Routes
    { path: '/admin/dashboard', component: () => import('../views/AdminDashboard.vue'), meta: { requiresAuth: true, role: 'admin', title: 'Dashboard Admin' } },
    { path: '/admin/letters', component: () => import('../views/AdminLetters.vue'), meta: { requiresAuth: true, roles: ['admin', 'operator'], title: 'Buku Agenda Persuratan' } },
    { path: '/admin/ppdb', component: () => import('../views/AdminPpdb.vue'), meta: { requiresAuth: true, roles: ['admin', 'operator'], title: 'Penerimaan Siswa (PPDB)' } },
    { path: '/admin/users', component: () => import('../components/CrudPage.vue'), meta: { requiresAuth: true, role: 'admin', title: 'Manajemen Pengguna' }, props: { endpoint: 'admin/users', resource: 'admin/users', title: 'Pengguna', formFields: userFields(), fields: userFields(), columns: [
        { label: 'Nama Pengguna', field: 'name' },
        { label: 'Username Login', field: 'username' },
        { label: 'Peran Akun', field: 'role' },
    ], hideFields: ['password'] } },
    { path: '/admin/students', component: () => import('../views/AdminStudents.vue'), meta: { requiresAuth: true, roles: ['admin', 'operator', 'kurikulum'], title: 'Manajemen Data Siswa' } },
    { path: '/admin/teachers', component: () => import('../views/AdminTeachers.vue'), meta: { requiresAuth: true, roles: ['admin', 'operator', 'kurikulum'], title: 'Manajemen Data Guru' } },
    { path: '/admin/classes', component: () => import('../views/AdminClasses.vue'), meta: { requiresAuth: true, roles: ['admin', 'kurikulum'], title: 'Manajemen Kelas' } },
    { path: '/admin/subjects', component: () => import('../views/AdminSubjects.vue'), meta: { requiresAuth: true, roles: ['admin', 'kurikulum'], title: 'Mata Pelajaran' } },
    { path: '/admin/schedules', component: () => import('../views/AdminSchedules.vue'), meta: { requiresAuth: true, roles: ['admin', 'kurikulum'], title: 'Jadwal Pelajaran' } },
    { path: '/admin/print-center', component: () => import('../views/AdminPrintCenter.vue'), meta: { requiresAuth: true, roles: ['admin', 'operator'], title: 'Pusat Cetak Dokumen' } },
    { path: '/admin/attendance-reports', component: () => import('../views/AdminAttendanceReports.vue'), meta: { requiresAuth: true, roles: ['admin', 'kurikulum'], title: 'Rekap Presensi Siswa' } },
    { path: '/admin/daily-student-attendance', component: () => import('../views/AdminDailyAttendanceMonitoring.vue'), meta: { requiresAuth: true, roles: ['admin', 'kurikulum'], title: 'Monitoring Presensi Siswa' } },
    { path: '/admin/teacher-presensi-monitoring', component: () => import('../views/AdminTeacherAttendanceMonitoring.vue'), meta: { requiresAuth: true, roles: ['admin', 'kurikulum'], title: 'Monitoring Presensi Guru' } },
    { path: '/admin/settings', component: () => import('../views/AdminSettings.vue'), meta: { requiresAuth: true, role: 'admin', title: 'Pengaturan Sekolah' } },
    { path: '/admin/academic-years', component: () => import('../views/AdminAcademicYears.vue'), meta: { requiresAuth: true, roles: ['admin', 'kurikulum'], title: 'Tahun Ajaran' } },
    { path: '/admin/grades', component: () => import('../views/AdminGrades.vue'), meta: { requiresAuth: true, roles: ['admin', 'kurikulum'], title: 'Rekap Nilai Siswa' } },
    { path: '/admin/attendances', component: () => import('../views/AdminAttendances.vue'), meta: { requiresAuth: true, roles: ['admin', 'kurikulum'], title: 'Presensi Siswa' } },
    { path: '/admin/attendance', component: () => import('../views/TeacherAttendance.vue'), meta: { requiresAuth: true, roles: ['admin', 'kurikulum'], title: 'Presensi Harian' } },
    { path: '/admin/profile', component: () => import('../views/AdminProfile.vue'), meta: { requiresAuth: true, roles: ['admin', 'operator', 'kurikulum'], title: 'Profil Pengguna' } },

    { path: '/admin/posts', component: () => import('../components/CrudPage.vue'), meta: { requiresAuth: true, role: 'admin', title: 'Berita & Informasi' }, props: { endpoint: 'admin/posts', resource: 'admin/posts', title: 'Berita & Artikel', formFields: postFields(), fields: postFields(), columns: [
        { label: 'Gambar', field: 'image' },
        { label: 'Judul', field: 'title' },
        { label: 'Status', field: 'status' },
    ] } },
    { path: '/admin/galleries', component: () => import('../components/CrudPage.vue'), meta: { requiresAuth: true, role: 'admin', title: 'Galeri Foto' }, props: { endpoint: 'admin/galleries', resource: 'admin/galleries', title: 'Galeri Foto', formFields: galleryFields(), fields: galleryFields(), columns: [
        { label: 'Gambar', field: 'image' },
        { label: 'Judul Foto', field: 'title' },
        { label: 'Deskripsi', field: 'description' },
    ] } },
    { path: '/admin/calendar-events', component: () => import('../views/AdminCalendar.vue'), meta: { requiresAuth: true, roles: ['admin', 'kurikulum'], title: 'Kalender Akademik' } },
    { path: '/admin/facilities', component: () => import('../components/CrudPage.vue'), meta: { requiresAuth: true, role: 'admin', title: 'Sarana & Prasarana' }, props: { endpoint: 'admin/facilities', resource: 'admin/facilities', title: 'Sarana & Prasarana', formFields: [
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
        { name: 'status', label: 'Status', type: 'select', options: [
            { value: 'published', label: 'Published' },
            { value: 'draft', label: 'Draft' },
        ] },
        { name: 'image', label: 'Pilih Gambar', type: 'file' },
    ], columns: [
        { label: 'Gambar', field: 'image' },
        { label: 'Nama Fasilitas', field: 'name' },
        { label: 'Deskripsi', field: 'description' },
        { label: 'Status', field: 'status' },
    ] } },
    { path: '/admin/achievements', component: () => import('../components/CrudPage.vue'), meta: { requiresAuth: true, role: 'admin', title: 'Prestasi Siswa' }, props: { endpoint: 'admin/achievements', resource: 'admin/achievements', title: 'Prestasi Siswa', formFields: [
        { name: 'title', label: 'Nama Prestasi / Lomba', type: 'text' },
        { name: 'student_name', label: 'Nama Siswa Pemenang', type: 'text' },
        { name: 'level', label: 'Tingkat Prestasi', type: 'select', options: [
            { value: 'Sekolah', label: 'Sekolah' },
            { value: 'Kecamatan', label: 'Kecamatan' },
            { value: 'Kabupaten/Kota', label: 'Kabupaten/Kota' },
            { value: 'Provinsi', label: 'Provinsi' },
            { value: 'Nasional', label: 'Nasional' },
            { value: 'Internasional', label: 'Internasional' },
        ] },
        { name: 'year', label: 'Tahun', type: 'text' },
        { name: 'description', label: 'Keterangan Tambahan', type: 'textarea' },
        { name: 'photo', label: 'Foto Dokumentasi', type: 'file' },
    ], fields: [
        { name: 'title', label: 'Nama Prestasi', type: 'text' },
        { name: 'student_name', label: 'Nama Siswa', type: 'text' },
        { name: 'level', label: 'Tingkat', type: 'select', options: [
            { value: 'Sekolah', label: 'Sekolah' },
            { value: 'Kecamatan', label: 'Kecamatan' },
            { value: 'Kabupaten/Kota', label: 'Kabupaten/Kota' },
            { value: 'Provinsi', label: 'Provinsi' },
            { value: 'Nasional', label: 'Nasional' },
            { value: 'Internasional', label: 'Internasional' },
        ] },
        { name: 'year', label: 'Tahun', type: 'text' },
        { name: 'photo', label: 'Foto', type: 'file' },
    ], columns: [
        { label: 'Foto', field: 'photo' },
        { label: 'Nama Prestasi', field: 'title' },
        { label: 'Nama Siswa', field: 'student_name' },
        { label: 'Tingkat', field: 'level' },
        { label: 'Tahun', field: 'year' },
    ] } },

    // Teacher Routes (Accessible by Teacher, Admin, Operator, Kurikulum)
    { path: '/teacher/dashboard', component: () => import('../views/TeacherDashboard.vue'), meta: { requiresAuth: true, roles: ['teacher', 'admin', 'operator', 'kurikulum'], title: 'Dashboard Guru' } },
    { path: '/teacher/ppdb', component: () => import('../views/AdminPpdb.vue'), meta: { requiresAuth: true, roles: ['teacher', 'admin', 'operator', 'kurikulum'], title: 'Panitia PPDB' } },
    { path: '/teacher/profile', component: () => import('../views/TeacherProfile.vue'), meta: { requiresAuth: true, roles: ['teacher', 'admin', 'operator', 'kurikulum'], title: 'Profil Guru' } },
    { path: '/teacher/presensi', component: () => import('../views/TeacherPresensi.vue'), meta: { requiresAuth: true, roles: ['teacher', 'admin', 'operator', 'kurikulum'], title: 'Presensi Guru GPS & QR' } },
    { path: '/teacher/presensi-recap', component: () => import('../views/TeacherAttendanceRecap.vue'), meta: { requiresAuth: true, roles: ['teacher', 'admin', 'operator', 'kurikulum'], title: 'Rekap Presensi Saya' } },
    { path: '/teacher/students', component: () => import('../views/TeacherStudents.vue'), meta: { requiresAuth: true, roles: ['teacher', 'admin', 'operator', 'kurikulum'], title: 'Data Siswa Binaan' } },
    { path: '/teacher/homeroom-attendance', component: () => import('../views/HomeroomDailyAttendance.vue'), meta: { requiresAuth: true, roles: ['teacher', 'admin', 'operator', 'kurikulum'], title: 'Presensi Harian Wali Kelas' } },
    { path: '/teacher/attendance', component: () => import('../views/TeacherAttendance.vue'), meta: { requiresAuth: true, roles: ['teacher', 'admin', 'operator', 'kurikulum'], title: 'Presensi Mata Pelajaran' } },
    { path: '/teacher/attendance-reports', component: () => import('../views/AdminAttendanceReports.vue'), meta: { requiresAuth: true, roles: ['teacher', 'admin', 'operator', 'kurikulum'], title: 'Laporan Rekap Presensi' } },
    { path: '/teacher/grades', component: () => import('../views/TeacherGrades.vue'), meta: { requiresAuth: true, roles: ['teacher', 'admin', 'operator', 'kurikulum'], title: 'Input Nilai Siswa' } },
    { path: '/teacher/schedules', component: () => import('../views/TeacherSchedules.vue'), meta: { requiresAuth: true, roles: ['teacher', 'admin', 'operator', 'kurikulum'], title: 'Jadwal Mengajar' } },
    { path: '/teacher/calendar', component: () => import('../views/TeacherCalendar.vue'), meta: { requiresAuth: true, roles: ['teacher', 'admin', 'operator', 'kurikulum'], title: 'Kalender Akademik' } },

    // Student Routes
    { path: '/student/dashboard', component: () => import('../views/StudentDashboard.vue'), meta: { requiresAuth: true, role: 'student', title: 'Dashboard Siswa' } },
    { path: '/student/profile', component: () => import('../views/StudentProfile.vue'), meta: { requiresAuth: true, role: 'student', title: 'Profil Saya' } },
    { path: '/student/attendances', component: () => import('../views/StudentAttendance.vue'), meta: { requiresAuth: true, role: 'student', title: 'Kehadiran Saya' } },
    { path: '/student/transcript', component: () => import('../views/StudentTranscript.vue'), meta: { requiresAuth: true, role: 'student', title: 'Transkrip Nilai Saya' } },

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
        if (to.meta.roles && Array.isArray(to.meta.roles)) {
            if (!to.meta.roles.includes(auth.user?.role)) {
                return roleDashboard(auth.user?.role);
            }
        } else if (to.meta.role && auth.user?.role !== to.meta.role) {
            return roleDashboard(auth.user?.role);
        }
    }

    if (to.meta.requiresGuest && auth.token && auth.user) {
        return roleDashboard(auth.user.role);
    }

    return true;
});

router.afterEach((to) => {
    const title = to.meta?.title || to.props?.title || '';
    if (title) {
        document.title = `SIAKAD MTs - ${title}`;
    } else {
        document.title = 'SIAKAD MTs - Sistem Informasi Akademik Madrasah';
    }
});

export default router;
