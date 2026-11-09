<template>
  <div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
      <div class="flex items-center gap-3">
        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
          <ClipboardCheck class="w-6 h-6" />
        </div>
        <div>
          <h1 class="text-xl font-bold text-slate-800">Monitoring & Telaah Soal Kurikulum</h1>
          <p class="text-sm text-slate-500">Supervisi kaidah butir soal, validasi kisi-kisi, dan penerbitan dokumen ujian resmi madrasah</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="fetchPackages"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition"
        >
          <RotateCw class="w-4 h-4 text-slate-500" :class="{ 'animate-spin': loading }" />
          <span>Refresh Data</span>
        </button>
      </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="text-xs font-semibold text-slate-400">Total Paket Ujian</div>
        <div class="text-2xl font-black text-slate-800 mt-1">{{ summary.total_packages }}</div>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="text-xs font-semibold text-amber-600 flex items-center gap-1">
          <Clock class="w-3.5 h-3.5" /> Menunggu Telaah
        </div>
        <div class="text-2xl font-black text-amber-600 mt-1">{{ summary.submitted }}</div>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="text-xs font-semibold text-rose-600 flex items-center gap-1">
          <AlertCircle class="w-3.5 h-3.5" /> Perlu Revisi
        </div>
        <div class="text-2xl font-black text-rose-600 mt-1">{{ summary.revision_needed }}</div>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="text-xs font-semibold text-emerald-600 flex items-center gap-1">
          <CheckCircle2 class="w-3.5 h-3.5" /> Disetujui (Approved)
        </div>
        <div class="text-2xl font-black text-emerald-600 mt-1">{{ summary.approved }}</div>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="text-xs font-semibold text-slate-400">Draft Guru</div>
        <div class="text-2xl font-black text-slate-600 mt-1">{{ summary.draft }}</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Status Telaah</label>
          <select v-model="filters.status_kurikulum" @change="fetchPackages" class="form-select">
            <option value="">Semua Status</option>
            <option value="submitted">Menunggu Telaah (Perlu Dicek)</option>
            <option value="revision_needed">Perlu Revisi</option>
            <option value="approved">Disetujui / Siap Cetak</option>
            <option value="draft">Draft Guru</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Mata Pelajaran</label>
          <select v-model="filters.subject_id" @change="fetchPackages" class="form-select">
            <option value="">Semua Mapel</option>
            <option v-for="s in options.subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Kelas</label>
          <select v-model="filters.class_room_id" @change="fetchPackages" class="form-select">
            <option value="">Semua Kelas</option>
            <option v-for="c in options.classes" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Pencarian</label>
          <div class="relative">
            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              v-model="filters.search"
              @keyup.enter="fetchPackages"
              placeholder="Cari mapel / guru..."
              class="form-input pl-9"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Package Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
      <div v-if="loading" class="flex justify-center py-16">
        <div class="flex flex-col items-center gap-3">
          <div class="w-8 h-8 border-3 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
          <p class="text-sm text-slate-500">Memuat berkas telaah soal...</p>
        </div>
      </div>

      <div v-else-if="packages.length === 0" class="p-12 text-center text-slate-500">
        <FileQuestion class="w-10 h-10 mx-auto text-slate-300 mb-2" />
        <p class="text-sm font-semibold">Tidak ditemukan berkas soal yang sesuai filter.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
            <tr>
              <th class="px-5 py-3.5">Paket Soal & Ujian</th>
              <th class="px-5 py-3.5">Guru Pengampu</th>
              <th class="px-5 py-3.5">Kelas & Fase</th>
              <th class="px-5 py-3.5 text-center">Jml Soal</th>
              <th class="px-5 py-3.5">Status Kurikulum</th>
              <th class="px-5 py-3.5 text-right">Aksi & Dokumen</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="pkg in packages" :key="pkg.id" class="hover:bg-slate-50/70 transition">
              <td class="px-5 py-4">
                <div class="font-bold text-slate-800 text-sm line-clamp-1">{{ pkg.title }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">
                  {{ pkg.subject?.name }} &bull; <span class="uppercase font-semibold text-slate-600">{{ pkg.exam_type }}</span>
                </div>
              </td>
              <td class="px-5 py-4">
                <div class="font-semibold text-slate-800">{{ pkg.teacher?.user?.name || pkg.teacher?.name || '-' }}</div>
                <div class="text-[11px] text-slate-400">NIP: {{ pkg.teacher?.nip || '-' }}</div>
              </td>
              <td class="px-5 py-4">
                <div class="font-semibold text-slate-700">Kelas {{ pkg.class_room?.name }}</div>
                <div class="text-[11px] text-slate-400">{{ pkg.academic_phase || 'Fase D' }}</div>
              </td>
              <td class="px-5 py-4 text-center">
                <span class="font-bold text-slate-800">{{ pkg.questions_count }}</span>
                <div class="text-[10px] text-slate-400">PG: {{ pkg.pg_count }} | Ess: {{ pkg.essay_count }}</div>
              </td>
              <td class="px-5 py-4">
                <span
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold capitalize"
                  :class="getStatusBadgeClass(pkg.status_kurikulum)"
                >
                  {{ getStatusLabel(pkg.status_kurikulum) }}
                </span>
                <div v-if="pkg.reviewed_by" class="text-[10px] text-slate-400 mt-0.5">
                  Oleh: {{ pkg.reviewed_by?.name }}
                </div>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    type="button"
                    @click="openReviewDrawer(pkg.id)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-2xs transition"
                  >
                    <Search class="w-3.5 h-3.5" />
                    <span>Telaah Soal</span>
                  </button>

                  <!-- Print Dropdown -->
                  <div class="relative group">
                    <button
                      type="button"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition"
                    >
                      <Printer class="w-3.5 h-3.5 text-slate-600" />
                      <span>Cetak</span>
                      <ChevronDown class="w-3 h-3 text-slate-400 group-hover:rotate-180 transition duration-200" />
                    </button>
                    <div class="hidden group-hover:block absolute right-0 top-full mt-1.5 w-56 bg-white border border-slate-200 rounded-xl shadow-xl z-30 py-1.5 text-left text-xs text-slate-700 divide-y divide-slate-100">
                      <div class="py-1">
                        <a :href="`/prints/exam/${pkg.id}/naskah?layout=2col`" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-indigo-50 hover:text-indigo-700 font-medium transition">
                          <span>📄</span>
                          <span>Naskah 2 Kolom (Hemat)</span>
                        </a>
                        <a :href="`/prints/exam/${pkg.id}/naskah?layout=1col`" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-indigo-50 hover:text-indigo-700 transition">
                          <span>📄</span>
                          <span>Naskah 1 Kolom</span>
                        </a>
                      </div>
                      <div class="py-1">
                        <a :href="`/prints/exam/${pkg.id}/kisi-kisi`" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-indigo-50 hover:text-indigo-700 transition">
                          <span>📊</span>
                          <span>Kisi-Kisi Resmi</span>
                        </a>
                        <a :href="`/prints/exam/${pkg.id}/kartu-soal`" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-indigo-50 hover:text-indigo-700 transition">
                          <span>🗂️</span>
                          <span>Kartu Soal</span>
                        </a>
                      </div>
                      <div class="py-1">
                        <a :href="`/prints/exam/${pkg.id}/telaah`" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-indigo-50 hover:text-indigo-700 transition">
                          <span>📝</span>
                          <span>Berita Acara Telaah</span>
                        </a>
                        <a :href="`/prints/exam/${pkg.id}/kunci`" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-indigo-50 hover:text-indigo-700 transition">
                          <span>🔑</span>
                          <span>Kunci & Rubrik Nilai</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- FULLSCREEN REVIEW & TELAAH DRAWER -->
    <div v-if="activePackage" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex flex-col justify-end lg:justify-center p-0 lg:p-6 overflow-hidden">
      <div class="bg-white w-full h-full lg:max-w-6xl lg:h-[94vh] lg:rounded-2xl shadow-2xl flex flex-col overflow-hidden">
        <!-- Reviewer Header -->
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-500/20 text-indigo-400 rounded-lg">
              <ClipboardCheck class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-base font-bold line-clamp-1">{{ activePackage.title }}</h2>
              <p class="text-xs text-slate-400">
                Guru: {{ activePackage.teacher?.user?.name }} &bull; {{ activePackage.subject?.name }} &bull; Kelas {{ activePackage.class_room?.name }}
              </p>
            </div>
          </div>
          <button @click="closeReviewDrawer" class="p-1.5 text-slate-400 hover:text-white rounded-lg">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Reviewer Body -->
        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden bg-slate-50">
          <!-- Question Nav List -->
          <div class="w-full lg:w-64 bg-white border-r border-slate-200 p-4 shrink-0 overflow-y-auto">
            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2.5">Pilih Nomor Soal</span>
            <div class="grid grid-cols-5 gap-2">
              <button
                v-for="(q, idx) in activePackage.questions"
                :key="q.id"
                type="button"
                @click="selectedQuestionIndex = idx"
                class="h-10 rounded-xl text-xs font-bold border transition flex flex-col items-center justify-center relative"
                :class="[
                  selectedQuestionIndex === idx
                    ? 'bg-indigo-900 text-white border-indigo-900 shadow-sm'
                    : q.telaah_status === 'layak'
                      ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                      : q.telaah_status === 'revisi'
                        ? 'bg-amber-50 text-amber-700 border-amber-200'
                        : 'bg-white text-slate-700 border-slate-200'
                ]"
              >
                <span>{{ q.question_number }}</span>
                <span class="text-[9px] uppercase font-bold" :class="q.telaah_status === 'layak' ? 'text-emerald-600' : 'text-amber-600'">
                  {{ q.telaah_status === 'layak' ? 'OK' : (q.telaah_status === 'revisi' ? 'Rev' : 'Tol') }}
                </span>
              </button>
            </div>

            <!-- Decision Box in left sidebar -->
            <div class="mt-6 pt-4 border-t border-slate-200 space-y-3">
              <span class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Keputusan Akhir Paket</span>
              <div class="space-y-2">
                <button
                  type="button"
                  @click="submitFinalDecision('approved')"
                  :disabled="submittingDecision"
                  class="w-full py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5"
                >
                  <CheckCircle2 class="w-4 h-4" />
                  <span>Setujui (Approve)</span>
                </button>
                <button
                  type="button"
                  @click="openRevisionModal"
                  class="w-full py-2 px-3 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5"
                >
                  <AlertCircle class="w-4 h-4" />
                  <span>Kembalikan untuk Revisi</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Active Question Detail & Checklist -->
          <div v-if="activePackage.questions[selectedQuestionIndex]" class="flex-1 p-6 overflow-y-auto space-y-5">
            <!-- Question Content Card -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-3">
              <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <span class="px-3 py-1 bg-slate-900 text-white font-bold text-xs rounded-lg">
                  Soal Nomor {{ activePackage.questions[selectedQuestionIndex].question_number }} ({{ strtoupper(activePackage.questions[selectedQuestionIndex].question_type) }})
                </span>
                <span class="text-xs text-slate-500 font-semibold">
                  Kunci: <strong class="text-emerald-600 text-sm">{{ activePackage.questions[selectedQuestionIndex].correct_answer || '-' }}</strong>
                </span>
              </div>

              <!-- Question text -->
              <div
                class="text-sm text-slate-800 leading-relaxed font-serif break-words"
                v-html="activePackage.questions[selectedQuestionIndex].question_text || 'Teks soal kosong.'"
              ></div>

              <!-- Question image if any -->
              <div v-if="activePackage.questions[selectedQuestionIndex].question_image" class="mt-2">
                <img :src="getStorageUrl(activePackage.questions[selectedQuestionIndex].question_image)" class="max-h-48 border rounded p-1" alt="Stimulus" />
              </div>

              <!-- Options / Answers Display based on AKM Type -->
              <div v-if="(activePackage.questions[selectedQuestionIndex].question_type === 'pg' || activePackage.questions[selectedQuestionIndex].question_type === 'pg_complex') && activePackage.questions[selectedQuestionIndex].options" class="grid grid-cols-2 gap-2 pt-2">
                <div
                  v-for="(val, key) in activePackage.questions[selectedQuestionIndex].options"
                  :key="key"
                  class="p-2 rounded-lg text-xs border"
                  :class="isKeySelected(activePackage.questions[selectedQuestionIndex].correct_answer, key) ? 'bg-indigo-50 border-indigo-300 font-bold text-indigo-800' : 'bg-slate-50 border-slate-200 text-slate-700'"
                >
                  <span class="font-bold mr-1">{{ key }}.</span> {{ val }}
                  <span v-if="isKeySelected(activePackage.questions[selectedQuestionIndex].correct_answer, key)" class="text-[10px] text-indigo-600 ml-1">✓ (Kunci)</span>
                </div>
              </div>

              <!-- True/False Statements Display -->
              <div v-else-if="activePackage.questions[selectedQuestionIndex].question_type === 'true_false' && activePackage.questions[selectedQuestionIndex].options?.statements" class="space-y-1.5 pt-2">
                <div
                  v-for="(st, sIdx) in activePackage.questions[selectedQuestionIndex].options.statements"
                  :key="sIdx"
                  class="flex items-center justify-between p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs"
                >
                  <span>{{ sIdx + 1 }}. {{ st.text }}</span>
                  <span class="px-2 py-0.5 rounded font-bold text-white text-[10px]" :class="st.answer === 'B' ? 'bg-emerald-600' : 'bg-rose-600'">
                    {{ st.answer === 'B' ? 'Benar' : 'Salah' }}
                  </span>
                </div>
              </div>

              <!-- Matching Pairs Display -->
              <div v-else-if="activePackage.questions[selectedQuestionIndex].question_type === 'matching' && activePackage.questions[selectedQuestionIndex].options?.pairs" class="space-y-1.5 pt-2">
                <div
                  v-for="(pair, pIdx) in activePackage.questions[selectedQuestionIndex].options.pairs"
                  :key="pIdx"
                  class="flex items-center justify-between p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs"
                >
                  <span>{{ pIdx + 1 }}. {{ pair.left }}</span>
                  <span class="font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                    Pasangan {{ pair.key }}: {{ pair.right }}
                  </span>
                </div>
              </div>

              <!-- Short Answer or Essay -->
              <div v-else-if="activePackage.questions[selectedQuestionIndex].correct_answer" class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-lg text-xs">
                <span class="font-bold text-emerald-800">Kunci / Pedoman Penskoran:</span>
                <p class="mt-1 text-slate-800 whitespace-pre-line">{{ activePackage.questions[selectedQuestionIndex].correct_answer }}</p>
              </div>

              <!-- Kisi-kisi preview -->
              <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs space-y-1">
                <div><strong>Materi:</strong> {{ activePackage.questions[selectedQuestionIndex].material || '-' }}</div>
                <div><strong>Indikator Soal:</strong> {{ activePackage.questions[selectedQuestionIndex].indicator || '-' }}</div>
                <div><strong>Level Kognitif:</strong> {{ activePackage.questions[selectedQuestionIndex].cognitive_level || 'L1' }} &bull; Kesukaran: {{ activePackage.questions[selectedQuestionIndex].difficulty_level }}</div>
              </div>
            </div>

            <!-- INSTRUMEN TELAAH FORM (3 ASPEK STANDAR) -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-4">
              <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Instrumen Telaah Butir Soal</h4>
                <div class="flex items-center gap-2">
                  <span class="text-xs text-slate-500 font-medium">Status Butir:</span>
                  <select v-model="activePackage.questions[selectedQuestionIndex].telaah_status" class="form-select py-1 text-xs font-bold">
                    <option value="layak">Layak (Memenuhi)</option>
                    <option value="revisi">Perlu Revisi</option>
                    <option value="tolak">Ditolak / Ganti</option>
                  </select>
                </div>
              </div>

              <!-- 3 Aspek Checklist -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <!-- Aspek Materi -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                  <div class="font-bold text-slate-700 border-b pb-1">A. Aspek Materi</div>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="ensureTelaah(activePackage.questions[selectedQuestionIndex]).materi.sesuai_indikator" class="rounded text-indigo-600" />
                    <span>Sesuai Indikator Kisi-Kisi</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="ensureTelaah(activePackage.questions[selectedQuestionIndex]).materi.materi_sesuai_fase" class="rounded text-indigo-600" />
                    <span>Materi Sesuai Fase Jenjang</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="ensureTelaah(activePackage.questions[selectedQuestionIndex]).materi.kunci_hanya_satu" class="rounded text-indigo-600" />
                    <span>Kunci Jawaban Tepat & Tunggal</span>
                  </label>
                </div>

                <!-- Aspek Konstruksi -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                  <div class="font-bold text-slate-700 border-b pb-1">B. Aspek Konstruksi</div>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="ensureTelaah(activePackage.questions[selectedQuestionIndex]).konstruksi.pokok_soal_tegas" class="rounded text-indigo-600" />
                    <span>Pokok Soal Dirumuskan Tegas</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="ensureTelaah(activePackage.questions[selectedQuestionIndex]).konstruksi.bebas_petunjuk_kunci" class="rounded text-indigo-600" />
                    <span>Bebas Petunjuk ke Kunci</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="ensureTelaah(activePackage.questions[selectedQuestionIndex]).konstruksi.bebas_negatif_ganda" class="rounded text-indigo-600" />
                    <span>Bebas Pernyataan Negatif Ganda</span>
                  </label>
                </div>

                <!-- Aspek Bahasa -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                  <div class="font-bold text-slate-700 border-b pb-1">C. Aspek Bahasa</div>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="ensureTelaah(activePackage.questions[selectedQuestionIndex]).bahasa.bahasa_baku_eyd" class="rounded text-indigo-600" />
                    <span>Menggunakan Bahasa Baku (EYD)</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="ensureTelaah(activePackage.questions[selectedQuestionIndex]).bahasa.komunikatif_mudah_dipahami" class="rounded text-indigo-600" />
                    <span>Komunikatif & Jelas Dipahami</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="ensureTelaah(activePackage.questions[selectedQuestionIndex]).bahasa.bebas_sara_dan_bias" class="rounded text-indigo-600" />
                    <span>Bebas Bias / Muatan SARA</span>
                  </label>
                </div>
              </div>

              <!-- Notes for question -->
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan Koreksi Butir Soal Ini (Opsional)</label>
                <input
                  type="text"
                  v-model="activePackage.questions[selectedQuestionIndex].telaah_notes"
                  placeholder="Misal: Perbaiki opsi B agar lebih homogen, atau perjelas narasi soal."
                  class="form-input text-xs"
                />
              </div>

              <!-- Save single telaah button -->
              <div class="flex justify-end">
                <button
                  type="button"
                  @click="saveQuestionReview(activePackage.questions[selectedQuestionIndex])"
                  :disabled="savingReview"
                  class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition"
                >
                  {{ savingReview ? 'Menyimpan...' : 'Simpan Telaah Butir Ini' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Catatan Revisi Kurikulum -->
    <div v-if="showRevisionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h3 class="text-base font-bold text-slate-800">Kembalikan untuk Revisi Guru</h3>
          <button @click="showRevisionModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-xs text-slate-500">
            Berikan arahan perbaikan yang jelas agar guru pengampu dapat segera merevisi butir soal atau kisi-kisi yang belum sesuai standar.
          </p>
          <textarea
            v-model="revisionNotes"
            rows="4"
            placeholder="Tuliskan catatan perbaikan di sini (misal: 'Soal nomor 5 dan 8 pilihan jawabannya ambigu, mohon disesuaikan dengan materi KD')..."
            class="form-textarea text-xs"
          ></textarea>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showRevisionModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-50">
              Batal
            </button>
            <button
              type="button"
              @click="submitFinalDecision('revision_needed')"
              :disabled="!revisionNotes || submittingDecision"
              class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm"
            >
              {{ submittingDecision ? 'Mengirim...' : 'Kirim Catatan Revisi' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api, { getImageUrl } from '../api';
import {
  ClipboardCheck,
  RotateCw,
  Search,
  FileQuestion,
  Printer,
  ChevronDown,
  Clock,
  AlertCircle,
  CheckCircle2,
  X
} from 'lucide-vue-next';

const loading = ref(false);
const packages = ref([]);
const summary = reactive({
  total_packages: 0,
  draft: 0,
  submitted: 0,
  approved: 0,
  revision_needed: 0
});

const options = reactive({
  classes: [],
  subjects: [],
  academic_years: []
});

const filters = reactive({
  academic_year_id: '',
  semester: '',
  subject_id: '',
  class_room_id: '',
  status_kurikulum: '',
  search: ''
});

// Review Drawer
const activePackage = ref(null);
const selectedQuestionIndex = ref(0);
const savingReview = ref(false);
const submittingDecision = ref(false);
const showRevisionModal = ref(false);
const revisionNotes = ref('');

const fetchOptions = async () => {
  try {
    const res = await api.get('/teacher/exam-bank/options');
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      Object.assign(options, d.data || d);
    }
  } catch (err) {
    console.error('Error fetching options:', err);
  }
};

const fetchPackages = async () => {
  loading.value = true;
  try {
    const res = await api.get('/admin/exam-reviews', filters);
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      packages.value = d.data?.data || d.data || [];
      if (d.summary || res.summary) {
        Object.assign(summary, d.summary || res.summary);
      }
    }
  } catch (err) {
    console.error('Error fetching packages for review:', err);
  } finally {
    loading.value = false;
  }
};

const openReviewDrawer = async (packageId) => {
  try {
    const res = await api.get(`/admin/exam-reviews/${packageId}`);
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      activePackage.value = d.data || d;
      selectedQuestionIndex.value = 0;
      revisionNotes.value = activePackage.value.review_notes || '';
    }
  } catch (err) {
    alert(err.response?.data?.message || err.message || 'Gagal memuat berkas soal.');
  }
};

const closeReviewDrawer = () => {
  activePackage.value = null;
  fetchPackages();
};

const ensureTelaah = (q) => {
  if (!q.telaah_materi) q.telaah_materi = { sesuai_indikator: true, materi_sesuai_fase: true, kunci_hanya_satu: true };
  if (!q.telaah_konstruksi) q.telaah_konstruksi = { pokok_soal_tegas: true, bebas_petunjuk_kunci: true, bebas_negatif_ganda: true };
  if (!q.telaah_bahasa) q.telaah_bahasa = { bahasa_baku_eyd: true, komunikatif_mudah_dipahami: true, bebas_sara_dan_bias: true };
  return {
    materi: q.telaah_materi,
    konstruksi: q.telaah_konstruksi,
    bahasa: q.telaah_bahasa
  };
};

const saveQuestionReview = async (q) => {
  if (!activePackage.value) return;
  savingReview.value = true;
  try {
    const res = await api.post(`/admin/exam-reviews/${activePackage.value.id}/questions/${q.id}/review`, {
      telaah_status: q.telaah_status || 'layak',
      telaah_notes: q.telaah_notes || null,
      telaah_materi: q.telaah_materi,
      telaah_konstruksi: q.telaah_konstruksi,
      telaah_bahasa: q.telaah_bahasa
    });
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      alert(d.message || res.message || 'Hasil telaah soal berhasil disimpan!');
    }
  } catch (err) {
    alert(err.response?.data?.message || err.message || 'Gagal menyimpan hasil telaah.');
  } finally {
    savingReview.value = false;
  }
};

const openRevisionModal = () => {
  showRevisionModal.value = true;
};

const submitFinalDecision = async (status) => {
  if (!activePackage.value) return;
  submittingDecision.value = true;
  try {
    const res = await api.post(`/admin/exam-reviews/${activePackage.value.id}/decision`, {
      status_kurikulum: status,
      review_notes: status === 'revision_needed' ? revisionNotes.value : null
    });
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      alert(d.message || res.message);
      showRevisionModal.value = false;
      closeReviewDrawer();
    }
  } catch (err) {
    alert(err.response?.data?.message || err.message || 'Gagal mengirim keputusan telaah.');
  } finally {
    submittingDecision.value = false;
  }
};

const getStorageUrl = (path) => {
  return getImageUrl(path);
};

const getStatusLabel = (status) => {
  switch (status) {
    case 'submitted': return 'Menunggu Telaah';
    case 'revision_needed': return 'Perlu Revisi';
    case 'approved': return 'Disetujui Kurikulum';
    default: return 'Draft';
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'submitted': return 'bg-amber-100 text-amber-800';
    case 'revision_needed': return 'bg-rose-100 text-rose-800';
    case 'approved': return 'bg-emerald-100 text-emerald-800';
    default: return 'bg-slate-100 text-slate-700';
  }
};

const isKeySelected = (correctAnswer, key) => {
  if (!correctAnswer) return false;
  return correctAnswer.toString().split(',').map(k => k.trim()).includes(key);
};

onMounted(async () => {
  await fetchOptions();
  await fetchPackages();
});
</script>

<style scoped>
.form-input, .form-select, .form-textarea {
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
.form-input:focus, .form-select:focus, .form-textarea:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}
</style>
