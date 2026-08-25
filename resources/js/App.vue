<template>
  <div>
    <template v-if="$route.meta.requiresAuth">
      <div class="flex h-screen bg-[#F8FAFC] font-inter text-slate-900 antialiased">
        <!-- Modular Sidebar -->
        <AppSidebar
          :user="user"
          :appSettings="appSettings"
          :isCollapsed="isCollapsed"
          :isMobileSidebarOpen="isMobileSidebarOpen"
          :isHomeroomTeacher="isHomeroomTeacher"
          :isPpdbCommittee="isPpdbCommittee"
          :pendingResetRequestsCount="pendingResetRequests.length"
          :getImageUrl="getImageUrl"
          @close-mobile-sidebar="isMobileSidebarOpen = false"
          @open-reset-requests="fetchPendingResetRequests(); showNotificationsModal = true"
          @logout="handleLogout"
        />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#F8FAFC]">
          <!-- Modular Header -->
          <AppHeader
            :user="user"
            :currentTime="currentTime"
            :currentRouteName="currentRouteName"
            :totalNotificationsCount="totalNotificationsCount"
            @toggle-sidebar="toggleSidebar"
            @open-notifications="openNotificationModal"
            @open-change-password="showChangePasswordModal = true"
            @logout="handleLogout"
          />

          <!-- Main Scrollable Content -->
          <main class="flex-1 overflow-y-auto bg-[#F4F7F6]">
            <div class="p-8 max-w-[1400px] mx-auto">
              <router-view v-slot="{ Component }">
                <transition name="fade" mode="out-in">
                  <component :is="Component" />
                </transition>
              </router-view>
            </div>
          </main>
        </div>
      </div>
    </template>

    <template v-else>
      <router-view />
    </template>

    <!-- Modal Ubah Password Mandiri -->
    <Transition name="modal-fade">
      <div v-if="showChangePasswordModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 no-print">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showChangePasswordModal = false"></div>
        <div class="relative bg-white rounded-3xl p-6 shadow-2xl w-full max-w-md space-y-4 border border-slate-100 animate-slide-up">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
              </div>
              <h3 class="font-lexend font-black text-slate-800 text-base">Ubah Password Akun</h3>
            </div>
            <button @click="showChangePasswordModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
          </div>

          <form @submit.prevent="submitChangePassword" class="space-y-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Password Lama Saat Ini</label>
              <input
                v-model="changePassForm.current_password"
                type="password"
                placeholder="••••••••"
                class="form-input text-xs"
                required
              />
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Password Baru (Min. 6 Karakter)</label>
              <input
                v-model="changePassForm.new_password"
                type="password"
                placeholder="••••••••"
                class="form-input text-xs"
                required
              />
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Konfirmasi Password Baru</label>
              <input
                v-model="changePassForm.new_password_confirmation"
                type="password"
                placeholder="••••••••"
                class="form-input text-xs"
                required
              />
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
              <button type="button" @click="showChangePasswordModal = false" class="btn btn-secondary text-xs px-4">Batal</button>
              <button type="submit" :disabled="submittingChangePass" class="btn btn-primary text-xs px-5">
                <span v-if="submittingChangePass">Menyimpan...</span>
                <span v-else>Simpan Password Baru</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Modal Notifications: Admin & Teacher -->
    <Transition name="modal-fade">
      <div v-if="showNotificationsModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 no-print">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showNotificationsModal = false"></div>
        <div class="relative bg-white rounded-3xl p-6 shadow-2xl w-full max-w-xl space-y-4 border border-slate-100 animate-slide-up">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
              </div>
              <div>
                <h3 class="font-lexend font-black text-slate-800 text-base">
                  {{ user?.role === 'admin' ? 'Pusat Notifikasi Admin' : 'Pusat Notifikasi Koreksi Absen' }}
                </h3>
                <p class="text-[11px] text-slate-400 font-medium">
                  {{ user?.role === 'admin' ? 'Permohonan masuk dari Guru & Pengguna' : 'Status persetujuan pengajuan koreksi presensi Anda' }}
                </p>
              </div>
            </div>
            <button @click="showNotificationsModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
          </div>

          <!-- TEACHER VIEW NOTIFICATIONS -->
          <template v-if="user?.role === 'teacher'">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
              <button
                @click="notificationTab = 'attendance'"
                :class="[
                  notificationTab === 'attendance' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                  'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5'
                ]"
              >
                <span>Koreksi Absen</span>
                <span v-if="teacherAttendanceRequests.filter(req => (req.approval_status === 'approved' || req.approval_status === 'rejected') && !seenTeacherNotifIds.includes(req.id)).length > 0" class="px-1.5 py-0.5 bg-rose-500 text-white rounded-full text-[9px] font-black">
                  {{ teacherAttendanceRequests.filter(req => (req.approval_status === 'approved' || req.approval_status === 'rejected') && !seenTeacherNotifIds.includes(req.id)).length }}
                </span>
              </button>

              <button
                @click="notificationTab = 'password'"
                :class="[
                  notificationTab === 'password' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                  'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5'
                ]"
              >
                <span>Reset Password Saya</span>
                <span v-if="myPasswordResetRequests.filter(req => (req.status === 'approved' || req.status === 'rejected') && !seenTeacherPassNotifIds.includes(req.id)).length > 0" class="px-1.5 py-0.5 bg-rose-500 text-white rounded-full text-[9px] font-black">
                  {{ myPasswordResetRequests.filter(req => (req.status === 'approved' || req.status === 'rejected') && !seenTeacherPassNotifIds.includes(req.id)).length }}
                </span>
              </button>
            </div>

            <!-- Tab 1: Teacher Attendance Correction Requests -->
            <div v-if="notificationTab === 'attendance'" class="space-y-3 max-h-[380px] overflow-y-auto pr-1 custom-scrollbar">
              <div v-if="teacherAttendanceRequests.length === 0" class="py-8 text-center text-slate-400 text-xs font-medium border-2 border-dashed border-slate-200/80 rounded-2xl">
                Belum ada permohonan koreksi presensi yang diajukan.
              </div>

              <div
                v-for="myReq in teacherAttendanceRequests"
                :key="myReq.id"
                :class="[
                  myReq.approval_status === 'approved' ? 'border-emerald-200 bg-emerald-50/50' :
                  myReq.approval_status === 'rejected' ? 'border-rose-200 bg-rose-50/50' :
                  'border-amber-200 bg-amber-50/50',
                  'p-4 rounded-2xl border space-y-2 transition-all text-xs shadow-xs'
                ]"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-800 font-mono">📅 Tanggal: {{ myReq.date }}</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-white/80 border border-slate-200 text-slate-700">
                      Target: {{ myReq.target_status.replace('_', ' ') }}
                    </span>
                  </div>
                  <span :class="[
                    myReq.approval_status === 'approved' ? 'bg-emerald-600 text-white' :
                    myReq.approval_status === 'rejected' ? 'bg-rose-600 text-white' :
                    'bg-amber-500 text-white',
                    'px-2.5 py-0.5 rounded-full font-black text-[10px] uppercase shadow-xs'
                  ]">
                    {{ myReq.approval_status === 'approved' ? '🟢 DISETUJUI' : (myReq.approval_status === 'rejected' ? '🔴 DITOLAK' : '🟡 MENUNGGU') }}
                  </span>
                </div>

                <p class="text-slate-600 text-[11px]">
                  <b class="text-slate-700">Alasan Guru:</b> {{ myReq.reason }}
                </p>

                <div v-if="myReq.approval_status === 'approved'" class="p-2.5 bg-emerald-100/80 rounded-xl text-[11px] font-bold text-emerald-900 border border-emerald-200 flex items-center gap-1.5">
                  <span>✅ Permohonan koreksi Anda telah <b>DISETUJUI</b> oleh Admin. Data presensi telah diperbarui!</span>
                </div>

                <div v-else-if="myReq.approval_status === 'rejected'" class="p-2.5 bg-rose-100/80 rounded-xl text-[11px] font-bold text-rose-900 border border-rose-200 space-y-1">
                  <p>❌ Permohonan koreksi Anda <b>DITOLAK</b> oleh Admin.</p>
                  <p v-if="myReq.admin_notes" class="text-[10px] font-normal text-rose-800 italic">
                    <b>Catatan Admin:</b> "{{ myReq.admin_notes }}"
                  </p>
                </div>
              </div>
            </div>

            <!-- Tab 2: Teacher Password Reset Requests -->
            <div v-if="notificationTab === 'password'" class="space-y-3 max-h-[380px] overflow-y-auto pr-1 custom-scrollbar">
              <div v-if="myPasswordResetRequests.length === 0" class="py-8 text-center text-slate-400 text-xs font-medium border-2 border-dashed border-slate-200/80 rounded-2xl">
                Belum ada pengajuan reset password.
              </div>

              <div
                v-for="passReq in myPasswordResetRequests"
                :key="passReq.id"
                :class="[
                  passReq.status === 'approved' ? 'border-emerald-200 bg-emerald-50/50' :
                  passReq.status === 'rejected' ? 'border-rose-200 bg-rose-50/50' :
                  'border-amber-200 bg-amber-50/50',
                  'p-4 rounded-2xl border space-y-2 transition-all text-xs shadow-xs'
                ]"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-800 font-mono">📅 Diajukan: {{ passReq.created_at?.slice(0, 10) }}</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-white/80 border border-slate-200 text-slate-700">
                      Identitas: {{ passReq.identity }}
                    </span>
                  </div>
                  <span :class="[
                    passReq.status === 'approved' ? 'bg-emerald-600 text-white' :
                    passReq.status === 'rejected' ? 'bg-rose-600 text-white' :
                    'bg-amber-500 text-white',
                    'px-2.5 py-0.5 rounded-full font-black text-[10px] uppercase shadow-xs'
                  ]">
                    {{ passReq.status === 'approved' ? '🟢 DISETUJUI' : (passReq.status === 'rejected' ? '🔴 DITOLAK' : '🟡 MENUNGGU') }}
                  </span>
                </div>

                <div v-if="passReq.status === 'approved'" class="p-3 bg-emerald-100/80 rounded-xl text-[11px] font-bold text-emerald-900 border border-emerald-200 space-y-1">
                  <p>🔑 Permohonan reset password Anda telah <b>DISETUJUI</b> oleh Admin!</p>
                  <p class="text-[10px] font-normal text-emerald-800">
                    Password akun Anda telah di-reset ke password default/NIP Anda. Silakan login kembali & perbarui password Anda di menu <b>Ubah Password</b> demi keamanan.
                  </p>
                </div>

                <div v-else-if="passReq.status === 'rejected'" class="p-3 bg-rose-100/80 rounded-xl text-[11px] font-bold text-rose-900 border border-rose-200">
                  <p>❌ Permohonan reset password Anda <b>DITOLAK</b> oleh Admin. Silakan hubungi tata usaha atau Admin sekolah secara langsung.</p>
                </div>

                <div v-else class="p-3 bg-amber-100/80 rounded-xl text-[11px] font-bold text-amber-900 border border-amber-200">
                  <p>⏳ Permohonan reset password Anda sedang diverifikasi & diproses oleh Admin.</p>
                </div>
              </div>
            </div>
          </template>

          <!-- ADMIN VIEW NOTIFICATIONS -->
          <template v-else-if="user?.role === 'admin'">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
              <button
                @click="notificationTab = 'attendance'"
                :class="[
                  notificationTab === 'attendance' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                  'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5'
                ]"
              >
                <span>Koreksi Absen Guru</span>
                <span v-if="pendingAttendanceRequests.filter(r => r.approval_status === 'pending').length > 0" class="px-1.5 py-0.5 bg-red-600 text-white rounded-full text-[9px] font-black">
                  {{ pendingAttendanceRequests.filter(r => r.approval_status === 'pending').length }}
                </span>
              </button>

              <button
                @click="notificationTab = 'password'"
                :class="[
                  notificationTab === 'password' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                  'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5'
                ]"
              >
                <span>Reset Password</span>
                <span v-if="pendingResetRequests.length > 0" class="px-1.5 py-0.5 bg-rose-500 text-white rounded-full text-[9px] font-black">
                  {{ pendingResetRequests.length }}
                </span>
              </button>
            </div>

            <!-- Tab 1: Attendance Requests -->
            <div v-if="notificationTab === 'attendance'" class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
              <div v-if="pendingAttendanceRequests.length === 0" class="py-8 text-center text-slate-400 text-xs font-medium border-2 border-dashed border-slate-200/80 rounded-2xl">
                Tidak ada permohonan koreksi absen dari Guru.
              </div>

              <div
                v-for="reqItem in pendingAttendanceRequests"
                :key="reqItem.id"
                class="p-4 rounded-2xl border bg-slate-50/60 border-slate-200/80 space-y-2.5 hover:border-amber-300 transition-all text-xs"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-amber-100 text-amber-800">
                      👨‍🏫 {{ reqItem.teacher?.full_name || 'Guru' }}
                    </span>
                    <span class="font-bold text-slate-800 font-mono">Tgl: {{ reqItem.date }}</span>
                  </div>
                  <span :class="[
                    reqItem.approval_status === 'approved' ? 'bg-emerald-100 text-emerald-800' :
                    reqItem.approval_status === 'rejected' ? 'bg-red-100 text-red-800' :
                    'bg-amber-100 text-amber-800',
                    'px-2 py-0.5 rounded-md font-bold text-[10px] uppercase'
                  ]">
                    {{ reqItem.approval_status }}
                  </span>
                </div>

                <div class="p-2.5 bg-white rounded-xl border border-slate-100 space-y-1">
                  <p><span class="font-bold text-slate-500">Status Seharusnya:</span> <b class="uppercase text-indigo-600">{{ reqItem.target_status }}</b></p>
                  <p><span class="font-bold text-slate-500">Jam Requested:</span> In: {{ reqItem.requested_check_in_time || '-' }} | Out: {{ reqItem.requested_check_out_time || '-' }}</p>
                  <p class="text-amber-900 italic"><span class="font-bold text-slate-500 not-italic">Alasan:</span> "{{ reqItem.reason }}"</p>
                </div>

                <div v-if="reqItem.approval_status === 'pending'" class="flex items-center justify-end gap-2 pt-1">
                  <button
                    @click="processAttendanceRequest(reqItem.id, 'reject')"
                    class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-800 text-xs font-bold rounded-xl transition-all cursor-pointer"
                  >
                    Tolak 🔴
                  </button>
                  <button
                    @click="processAttendanceRequest(reqItem.id, 'approve')"
                    class="px-4 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center gap-1 cursor-pointer"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Setujui 🟢</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Tab 2: Password Resets -->
            <div v-if="notificationTab === 'password'" class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
              <div v-if="pendingResetRequests.length === 0" class="py-8 text-center text-slate-400 text-xs font-medium border-2 border-dashed border-slate-200/80 rounded-2xl">
                Tidak ada permintaan reset password baru.
              </div>

              <div
                v-for="reqItem in pendingResetRequests"
                :key="reqItem.id"
                class="p-4 rounded-2xl border bg-slate-50/60 border-slate-200/80 space-y-3 hover:border-purple-300 transition-all"
              >
                <div class="flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-800">
                      {{ reqItem.role === 'teacher' ? '👨‍🏫 Guru' : (reqItem.role === 'student' ? '🎓 Siswa' : '👤 Pengguna') }}
                    </span>
                    <h4 class="font-black text-xs text-slate-800">{{ reqItem.name }}</h4>
                  </div>
                  <span class="text-[10px] font-mono text-slate-400">{{ reqItem.created_at?.slice(0, 10) }}</span>
                </div>

                <div class="text-xs text-slate-600 space-y-1 bg-white p-2.5 rounded-xl border border-slate-100">
                  <div><span class="font-bold text-slate-500">Identitas:</span> <span class="font-mono font-bold text-purple-700">{{ reqItem.identity }}</span></div>
                  <div v-if="reqItem.reason"><span class="font-bold text-slate-500">Catatan:</span> {{ reqItem.reason }}</div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                  <button
                    @click="rejectResetRequest(reqItem.id)"
                    class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition-all cursor-pointer"
                  >
                    Tolak
                  </button>
                  <button
                    @click="approveResetRequest(reqItem.id)"
                    class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center gap-1 cursor-pointer"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Setujui & Reset</span>
                  </button>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </Transition>

    <Toast
      v-if="toastState.show"
      :message="toastState.message"
      :type="toastState.type"
      :modelValue="toastState.show"
      @update:modelValue="toastState.show = $event"
    />
    <ConfirmDialog />
    <GlobalLoadingOverlay />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useAuthStore } from './stores/auth';
import Toast from './components/Toast.vue';
import ConfirmDialog from './components/ConfirmDialog.vue';
import GlobalLoadingOverlay from './components/GlobalLoadingOverlay.vue';
import AppSidebar from './components/layout/AppSidebar.vue';
import AppHeader from './components/layout/AppHeader.vue';
import { toastState, useToast } from './composables/useToast';
import { api } from './api';

const toast = useToast();
const router = useRouter();
const route = useRoute();
const auth = useAuthStore();
const { user } = storeToRefs(auth);

const getImageUrl = (path) => {
  if (!path) return '';
  if (typeof path !== 'string') return '';
  if (path.startsWith('http') || path.startsWith('data:')) return path;
  if (path.startsWith('/storage/')) return path;
  if (path.startsWith('storage/')) return '/' + path;
  return '/storage/' + path.replace(/^\//, '');
};

const isCollapsed = ref(false);
const isMobileSidebarOpen = ref(false);
const currentTime = ref('');

const showChangePasswordModal = ref(false);
const submittingChangePass = ref(false);
const changePassForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
});

const showNotificationsModal = ref(false);
const notificationTab = ref('attendance');
const pendingResetRequests = ref([]);
const pendingAttendanceRequests = ref([]);
const teacherAttendanceRequests = ref([]);
const myPasswordResetRequests = ref([]);
const seenTeacherNotifIds = ref(JSON.parse(localStorage.getItem('seen_teacher_notif_ids') || '[]'));
const seenTeacherPassNotifIds = ref(JSON.parse(localStorage.getItem('seen_teacher_pass_notif_ids') || '[]'));
const isHomeroomTeacher = ref(localStorage.getItem('is_homeroom_teacher') === 'true');
const isPpdbCommittee = computed(() => {
  if (user.value?.role === 'admin') return true;
  return !!(user.value?.teacher?.is_ppdb_committee || user.value?.is_ppdb_committee);
});

async function checkTeacherHomeroom() {
  if (user.value?.role === 'teacher') {
    if (user.value?.is_homeroom_teacher !== undefined) {
      const isHomeroom = !!user.value.is_homeroom_teacher;
      isHomeroomTeacher.value = isHomeroom;
      localStorage.setItem('is_homeroom_teacher', isHomeroom ? 'true' : 'false');
      return;
    }
    try {
      const clsRes = await api.get('teacher/classes');
      const list = Array.isArray(clsRes) ? clsRes : (clsRes.data || []);
      const isHomeroom = list.length > 0;
      isHomeroomTeacher.value = isHomeroom;
      localStorage.setItem('is_homeroom_teacher', isHomeroom ? 'true' : 'false');
    } catch (e) {
      console.error(e);
    }
  }
}

const totalNotificationsCount = computed(() => {
  const r = (user.value?.role || '').toLowerCase();
  if (r === 'admin') {
    const resetCount = pendingResetRequests.value.length;
    const attCount = pendingAttendanceRequests.value.filter(req => req.approval_status === 'pending').length;
    return resetCount + attCount;
  } else if (r === 'teacher') {
    const attCount = teacherAttendanceRequests.value.filter(req => 
      (req.approval_status === 'approved' || req.approval_status === 'rejected') &&
      !seenTeacherNotifIds.value.includes(req.id)
    ).length;
    const passCount = myPasswordResetRequests.value.filter(req => 
      (req.status === 'approved' || req.status === 'rejected') &&
      !seenTeacherPassNotifIds.value.includes(req.id)
    ).length;
    return attCount + passCount;
  }
  return 0;
});

async function openNotificationModal() {
  const r = (user.value?.role || '').toLowerCase();
  if (r === 'admin') {
    await fetchAllAdminNotifications();
  } else if (r === 'teacher') {
    await Promise.all([
      fetchTeacherAttendanceRequests(),
      fetchMyPasswordResetRequests(),
    ]);
    const resolvedAttIds = teacherAttendanceRequests.value
      .filter(req => req.approval_status === 'approved' || req.approval_status === 'rejected')
      .map(req => req.id);
    seenTeacherNotifIds.value = Array.from(new Set([...seenTeacherNotifIds.value, ...resolvedAttIds]));
    localStorage.setItem('seen_teacher_notif_ids', JSON.stringify(seenTeacherNotifIds.value));

    const resolvedPassIds = myPasswordResetRequests.value
      .filter(req => req.status === 'approved' || req.status === 'rejected')
      .map(req => req.id);
    seenTeacherPassNotifIds.value = Array.from(new Set([...seenTeacherPassNotifIds.value, ...resolvedPassIds]));
    localStorage.setItem('seen_teacher_pass_notif_ids', JSON.stringify(seenTeacherPassNotifIds.value));
  }
  showNotificationsModal.value = true;
}

async function fetchTeacherAttendanceRequests() {
  try {
    const res = await api.get('teacher/presensi/requests');
    teacherAttendanceRequests.value = res?.requests || res?.data?.requests || [];
  } catch (err) {
    console.error('Failed to fetch teacher attendance requests', err);
  }
}

async function fetchMyPasswordResetRequests() {
  try {
    const res = await api.get('/my-password-reset-requests');
    let list = [];
    if (Array.isArray(res)) list = res;
    else if (Array.isArray(res?.data)) list = res.data;
    else if (Array.isArray(res?.data?.data)) list = res.data.data;
    myPasswordResetRequests.value = list;
  } catch (err) {
    console.error('Failed to fetch my password reset requests', err);
  }
}

function fetchRoleNotifications() {
  const r = (user.value?.role || '').toLowerCase();
  if (r === 'admin') {
    fetchAllAdminNotifications();
  } else if (r === 'teacher') {
    fetchTeacherAttendanceRequests();
    fetchMyPasswordResetRequests();
    checkTeacherHomeroom();
  }
}

async function fetchAllAdminNotifications() {
  const r = (user.value?.role || '').toLowerCase();
  if (r !== 'admin') return;
  await Promise.all([
    fetchPendingResetRequests(),
    fetchPendingAttendanceRequests(),
  ]);
}

async function fetchPendingAttendanceRequests() {
  try {
    const res = await api.get('admin/teacher-attendance-requests');
    pendingAttendanceRequests.value = res?.requests || res?.data?.requests || [];
  } catch (err) {
    console.error('Failed to fetch teacher attendance requests', err);
  }
}

async function processAttendanceRequest(id, action) {
  try {
    const res = await api.post(`admin/teacher-attendance-requests/${id}/process`, { action });
    toast.success(res.message || res.data?.message || 'Berhasil memproses permohonan!');
    await fetchPendingAttendanceRequests();
  } catch (err) {
    toast.error('Gagal memproses permohonan koreksi.');
  }
}

async function submitChangePassword() {
  if (changePassForm.new_password !== changePassForm.new_password_confirmation) {
    toast.error('Konfirmasi password baru tidak cocok.');
    return;
  }
  submittingChangePass.value = true;
  try {
    const res = await api.post('/change-password', changePassForm);
    toast.success(res.data?.message || 'Password berhasil diperbarui!');
    showChangePasswordModal.value = false;
    changePassForm.current_password = '';
    changePassForm.new_password = '';
    changePassForm.new_password_confirmation = '';
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal mengubah password. Pastikan password lama sesuai.');
  } finally {
    submittingChangePass.value = false;
  }
}

async function fetchPendingResetRequests() {
  const r = (user.value?.role || '').toLowerCase();
  if (r !== 'admin') return;
  try {
    const res = await api.get('/admin/password-reset-requests');
    let list = [];
    if (Array.isArray(res)) {
      list = res;
    } else if (Array.isArray(res?.data)) {
      list = res.data;
    } else if (Array.isArray(res?.data?.data)) {
      list = res.data.data;
    }
    pendingResetRequests.value = list;
  } catch (err) {
    console.error('Failed to fetch password reset requests', err);
  }
}

async function approveResetRequest(id) {
  try {
    const res = await api.post(`/admin/password-reset-requests/${id}/approve`);
    toast.success(res.data?.message || 'Password berhasil di-reset!');
    fetchPendingResetRequests();
  } catch (err) {
    toast.error('Gagal memproses permohonan reset.');
  }
}

async function rejectResetRequest(id) {
  try {
    const res = await api.post(`/admin/password-reset-requests/${id}/reject`);
    toast.success(res.data?.message || 'Permintaan ditolak.');
    fetchPendingResetRequests();
  } catch (err) {
    toast.error('Gagal menolak permohonan.');
  }
}

const appSettings = ref({});

async function loadSettings() {
  try {
    const res = await api.get('/public');
    appSettings.value = res?.settings || res || {};
  } catch (error) {
    console.error('Failed to load settings:', error);
    appSettings.value = {};
  }
}

function toggleSidebar() {
  if (window.innerWidth < 640) {
    isMobileSidebarOpen.value = true;
  } else {
    isCollapsed.value = !isCollapsed.value;
  }
}

function updateTime() {
  const now = new Date();
  const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const day = days[now.getDay()];
  const date = now.getDate();
  const month = months[now.getMonth()];
  const year = now.getFullYear();
  const hh = String(now.getHours()).padStart(2, '0');
  const mm = String(now.getMinutes()).padStart(2, '0');
  const ss = String(now.getSeconds()).padStart(2, '0');
  currentTime.value = `${day}, ${date} ${month} ${year} ${hh}:${mm}:${ss}`;
}

let timer;
let notifTimer;
onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);
  loadSettings();
  fetchRoleNotifications();
  notifTimer = setInterval(fetchRoleNotifications, 15000);
});
onUnmounted(() => {
  clearInterval(timer);
  clearInterval(notifTimer);
});

watch(user, () => {
  fetchRoleNotifications();
}, { immediate: true });

watch(() => route.path, () => {
  isMobileSidebarOpen.value = false;
  fetchRoleNotifications();
});

const currentRouteName = computed(() => {
  const path = route.path;
  if (path.includes('dashboard')) return 'Dashboard';
  if (path.includes('students')) return 'Data Siswa';
  if (path.includes('teachers')) return 'Data Guru';
  if (path.includes('classes')) return 'Manajemen Kelas';
  if (path.includes('subjects')) return 'Mata Pelajaran';
  if (path.includes('academic-years')) return 'Tahun Ajaran';
  if (path.includes('schedules')) return 'Jadwal & Kegiatan';
  if (path.includes('print-center')) return 'Pusat Cetak';
  if (path.includes('attendance-reports')) return 'Rekap Presensi';
  if (path.includes('daily-student-attendance') || path.includes('homeroom-attendance')) return 'Presensi Siswa';
  if (path.includes('teacher-presensi-monitoring') || path.includes('presensi-recap') || path.includes('presensi')) return 'Presensi Guru';
  if (path.includes('settings')) return 'Pengaturan';
  if (path.includes('grades')) return 'Nilai & Transkrip';
  if (path.includes('posts')) return 'Berita & Artikel';
  if (path.includes('galleries')) return 'Galeri Foto';
  if (path.includes('achievements')) return 'Prestasi Siswa';
  if (path.includes('facilities')) return 'Sarana Prasarana';
  if (path.includes('calendar')) return 'Kalender Akademik';
  if (path.includes('profile')) return 'Profil Pengguna';
  return 'Dashboard';
});

async function handleLogout() {
  try {
    await auth.logout();
  } catch { /* silent */ }
  finally {
    router.push('/login');
  }
}
</script>

<style>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }

/* Sidebar label transition */
.label-fade-enter-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.label-fade-leave-active { transition: opacity 0.1s ease; }
.label-fade-enter-from  { opacity: 0; transform: translateX(-6px); }
.label-fade-leave-to    { opacity: 0; }

/* Page fade transition */
.fade-enter-active,
.fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fade-enter-from { opacity: 0; transform: translateY(8px); }
.fade-leave-to   { opacity: 0; transform: translateY(-8px); }

/* Dark sidebar scrollbar */
.custom-scrollbar-dark::-webkit-scrollbar { width: 3px; }
.custom-scrollbar-dark::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-dark::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }

/* Global scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
