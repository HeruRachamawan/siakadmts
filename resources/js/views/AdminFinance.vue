<template>
  <div class="space-y-5 font-inter pb-12">
    <!-- Header Page (Deep Madrasah Emerald - Institutional & Dignified) -->
    <div class="relative bg-gradient-to-r from-emerald-950 via-emerald-900 to-teal-950 text-white rounded-lg border border-emerald-800/80 p-5 sm:p-6 shadow-sm overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
      <div class="absolute right-0 top-0 bottom-0 w-96 bg-radial from-emerald-500/10 to-transparent pointer-events-none"></div>

      <div class="relative z-10 flex items-center gap-3.5">
        <div class="w-12 h-12 bg-white/10 rounded-md border border-white/20 p-2 flex items-center justify-center shadow-inner flex-shrink-0">
          <Wallet class="w-6 h-6 text-emerald-300" />
        </div>
        <div>
          <div class="flex items-center gap-2 mb-0.5">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/20 text-emerald-200 border border-emerald-400/30">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
              Keuangan & Kasir Madrasah
            </span>
          </div>
          <h1 class="text-lg sm:text-xl font-bold tracking-tight text-white uppercase font-sans">
            Manajemen Pembayaran SPP & Kas Sekolah
          </h1>
          <p class="text-emerald-200/80 text-xs mt-0.5 max-w-2xl font-normal">
            Pencatatan kasir pembayaran SPP 12 bulan, pos tagihan siswa, cetak bukti kwitansi, dan pemantauan tunggakan per rombel.
          </p>
        </div>
      </div>

      <div class="relative z-10 flex flex-wrap items-center gap-2">
        <button
          @click="activeTab = 'cashier'"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white border border-emerald-500 shadow-2xs transition-colors cursor-pointer"
        >
          <CreditCard class="w-4 h-4" />
          <span>Buka Kasir Cepat</span>
        </button>

        <button
          @click="openAddPaymentTypeModal"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/20 shadow-2xs transition-colors cursor-pointer"
        >
          <Plus class="w-4 h-4 text-emerald-200" />
          <span>Tambah Pos Tagihan</span>
        </button>
      </div>
    </div>

    <!-- Quick Stat Cards (High-Density Enterprise Standard) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 no-print">
      <!-- Kas Masuk Bulan Ini -->
      <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Kas Masuk Bulan Ini</p>
          <p class="text-xl font-bold text-emerald-700 font-mono tabular-nums mt-0.5">
            {{ formatRupiah(summary.this_month_income) }}
          </p>
          <span class="text-[10px] text-slate-400 font-medium">Bulan berjalan</span>
        </div>
        <div class="w-10 h-10 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center flex-shrink-0">
          <ArrowDownLeft class="w-5 h-5" />
        </div>
      </div>

      <!-- Total Penerimaan Tahun Ini -->
      <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Penerimaan T.A. Ini</p>
          <p class="text-xl font-bold text-blue-700 font-mono tabular-nums mt-0.5">
            {{ formatRupiah(summary.total_year_income) }}
          </p>
          <span class="text-[10px] text-slate-400 font-medium font-mono tabular-nums">{{ summary.total_transactions_count }}x transaksi</span>
        </div>
        <div class="w-10 h-10 rounded-md bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center flex-shrink-0">
          <Receipt class="w-5 h-5" />
        </div>
      </div>

      <!-- Total Piutang / Tunggakan -->
      <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Piutang / Tunggakan</p>
          <p class="text-xl font-bold text-rose-700 font-mono tabular-nums mt-0.5">
            {{ formatRupiah(summary.total_unpaid_amount) }}
          </p>
          <span class="text-[10px] text-rose-500 font-medium">Sisa tagihan belum lunas</span>
        </div>
        <div class="w-10 h-10 rounded-md bg-rose-50 border border-rose-200 text-rose-700 flex items-center justify-center flex-shrink-0">
          <AlertCircle class="w-5 h-5" />
        </div>
      </div>

      <!-- Quick Status Bendahara -->
      <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status Bendahara</p>
          <p class="text-sm font-bold text-slate-800 mt-1">Kasir Online Aktif</p>
          <span class="text-[10px] text-emerald-700 font-medium font-mono">Siap Menerima Pembayaran</span>
        </div>
        <div class="w-10 h-10 rounded-md bg-slate-100 border border-slate-200 text-slate-600 flex items-center justify-center flex-shrink-0">
          <CheckCircle2 class="w-5 h-5 text-emerald-600" />
        </div>
      </div>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2 no-print overflow-x-auto scrollbar-none">
      <button
        @click="activeTab = 'cashier'"
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-semibold transition-all cursor-pointer select-none whitespace-nowrap"
        :class="activeTab === 'cashier' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
      >
        <CreditCard class="w-4 h-4" />
        <span>1. Kasir Cepat & Kartu SPP</span>
      </button>

      <button
        @click="activeTab = 'payment_types'"
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-semibold transition-all cursor-pointer select-none whitespace-nowrap"
        :class="activeTab === 'payment_types' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
      >
        <Coins class="w-4 h-4" />
        <span>2. Pos Tagihan & Generate Otomatis</span>
      </button>

      <button
        @click="activeTab = 'reports'"
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-semibold transition-all cursor-pointer select-none whitespace-nowrap"
        :class="activeTab === 'reports' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
      >
        <FileSpreadsheet class="w-4 h-4" />
        <span>3. Rekap Kas & Laporan Tunggakan</span>
      </button>
    </div>

    <!-- ==================== TAB 1: KASIR CEPAT & KARTU SPP ==================== -->
    <div v-if="activeTab === 'cashier'" class="space-y-4 no-print">
      <!-- Search Student Bar -->
      <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-2xs space-y-3">
        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
          Pilih / Cari Siswa Pembayar
        </label>
        <div class="flex flex-col sm:flex-row items-center gap-3">
          <!-- Class Filter Dropdown -->
          <div class="w-full sm:w-60 flex-shrink-0">
            <select
              v-model="selectedClassId"
              @change="fetchStudentsByClass"
              class="form-input text-xs font-semibold"
            >
              <option value="">-- Semua Kelas / Pilih Rombel --</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                Kelas {{ cls.name }} (Tingkat {{ cls.grade_level }})
              </option>
            </select>
          </div>

          <!-- Student Select Dropdown -->
          <div class="w-full sm:flex-1">
            <select
              v-model="selectedStudentId"
              @change="loadStudentCard"
              class="form-input text-xs font-semibold"
            >
              <option value="">-- Pilih Nama Siswa --</option>
              <option v-for="st in classStudents" :key="st.id" :value="st.id">
                {{ st.full_name }} (NISN: {{ st.nisn || '-' }} • {{ st.class_name || '-' }})
              </option>
            </select>
          </div>

          <button
            v-if="selectedStudentId"
            @click="loadStudentCard"
            class="btn btn-secondary flex-shrink-0"
          >
            <RefreshCw class="w-3.5 h-3.5 text-slate-500" :class="{ 'animate-spin': loadingCard }" />
            <span>Segarkan Kartu</span>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loadingCard" class="py-16 text-center text-slate-400 text-xs font-medium bg-white rounded-lg border border-slate-200">
        <div class="animate-spin h-7 w-7 border-2 border-emerald-600 border-t-transparent rounded-full mx-auto mb-2"></div>
        Memuat buku kendali tagihan siswa...
      </div>

      <!-- Empty State -->
      <div v-else-if="!selectedStudentId" class="py-16 text-center bg-white rounded-lg border border-slate-200 space-y-2">
        <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 border border-slate-200">
          <UserCheck class="w-6 h-6" />
        </div>
        <p class="text-xs font-bold text-slate-700">Silakan Pilih Siswa Terlebih Dahulu</p>
        <p class="text-[11px] text-slate-400 max-w-sm mx-auto">
          Pilih kelas dan nama siswa pada kotak pencarian di atas untuk membuka buku kendali 12 bulan SPP.
        </p>
      </div>

      <!-- Student Card Detail View -->
      <div v-else class="space-y-4">
        <!-- Student Identity Banner -->
        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0 flex items-center justify-center font-bold text-slate-600">
              <img v-if="studentCardData.student?.photo_url" :src="studentCardData.student.photo_url" class="w-full h-full object-cover" alt="Foto" />
              <span v-else class="text-base">{{ studentCardData.student?.full_name?.charAt(0) || 'S' }}</span>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-slate-900">{{ studentCardData.student?.full_name }}</h3>
                <span class="px-2 py-0.2 rounded text-[10px] font-bold" :class="studentCardData.student?.gender === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200'">
                  {{ studentCardData.student?.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                </span>
              </div>
              <p class="text-xs text-slate-500 font-mono mt-0.5">
                NISN: <span class="font-bold text-slate-700">{{ studentCardData.student?.nisn || '-' }}</span> • 
                NIS: <span class="font-bold text-slate-700">{{ studentCardData.student?.nis || '-' }}</span> • 
                Kelas: <span class="font-bold text-emerald-700">{{ studentCardData.student?.class_room?.name || '-' }}</span>
              </p>
            </div>
          </div>

          <!-- Mini Balance Summary -->
          <div class="flex items-center gap-3 flex-wrap">
            <div class="px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-right">
              <span class="text-[10px] text-slate-400 block font-semibold uppercase">Total Tagihan</span>
              <span class="text-xs font-bold font-mono text-slate-800">{{ formatRupiah(studentCardData.total_billed) }}</span>
            </div>
            <div class="px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-right">
              <span class="text-[10px] text-emerald-700 block font-semibold uppercase">Sudah Dibayar</span>
              <span class="text-xs font-bold font-mono text-emerald-800">{{ formatRupiah(studentCardData.total_paid) }}</span>
            </div>
            <div class="px-3 py-1.5 rounded-lg bg-rose-50 border border-rose-200 text-right">
              <span class="text-[10px] text-rose-700 block font-semibold uppercase">Sisa Tunggakan</span>
              <span class="text-xs font-bold font-mono text-rose-800">{{ formatRupiah(studentCardData.total_remaining) }}</span>
            </div>
          </div>
        </div>

        <!-- Matrix 12 Bulan SPP -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-2xs space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
              <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <Calendar class="w-4 h-4 text-emerald-600" />
                <span>Buku Kendali SPP Bulanan (12 Bulan)</span>
              </h4>
              <p class="text-[11px] text-slate-500 mt-0.5">Centang bulan yang ingin dibayarkan oleh siswa, lalu klik tombol Bayar.</p>
            </div>

            <!-- Select All Checkbox -->
            <div v-if="studentCardData.monthly_bills.filter(b => b.status !== 'paid').length > 0" class="flex items-center gap-2">
              <button
                type="button"
                @click="selectAllUnpaidMonthly"
                class="btn btn-secondary text-[11px]"
              >
                Pilih Semua Bulan Belum Lunas
              </button>
            </div>
          </div>

          <!-- Empty Bills Warning -->
          <div v-if="studentCardData.monthly_bills.length === 0" class="p-6 text-center text-slate-400 space-y-2 bg-slate-50 rounded-lg border border-dashed border-slate-200">
            <AlertCircle class="w-6 h-6 text-amber-500 mx-auto" />
            <p class="text-xs font-bold text-slate-700">Tagihan SPP Belum Terbentuk</p>
            <p class="text-[11px] text-slate-500">
              Siswa ini belum memiliki tagihan SPP bulanan. Silakan masuk ke tab <strong>"2. Pos Tagihan"</strong> dan klik <strong>"Generate Tagihan Otomatis"</strong>.
            </p>
          </div>

          <!-- 12-Month Grid Cards -->
          <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <div
              v-for="b in studentCardData.monthly_bills"
              :key="'bill-' + b.id"
              class="relative p-3 rounded-lg border transition-all select-none"
              :class="[
                b.status === 'paid' ? 'bg-emerald-50/70 border-emerald-200 text-emerald-900' :
                selectedBillIds.includes(b.id) ? 'bg-emerald-100/60 border-emerald-400 ring-2 ring-emerald-500/30' :
                'bg-white border-slate-200 hover:border-slate-300'
              ]"
            >
              <!-- Checkbox (Only for unpaid/partial) -->
              <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 font-mono">
                  #{{ b.month_order }}
                </span>

                <span
                  class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider"
                  :class="b.status === 'paid' ? 'bg-emerald-200 text-emerald-900' : 'bg-rose-100 text-rose-800'"
                >
                  {{ b.status === 'paid' ? 'Lunas' : 'Belum Bayar' }}
                </span>
              </div>

              <div class="space-y-0.5">
                <p class="text-xs font-bold text-slate-800">{{ b.month }}</p>
                <p class="text-xs font-mono font-bold text-slate-900">{{ formatRupiah(b.amount) }}</p>
              </div>

              <!-- Action or Paid info -->
              <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                <template v-if="b.status === 'paid'">
                  <span class="text-[10px] text-emerald-700 font-medium flex items-center gap-1">
                    <Check class="w-3 h-3 text-emerald-600" />
                    <span>Terbayar</span>
                  </span>
                </template>
                <template v-else>
                  <label :for="'cb-' + b.id" class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-700">
                    <input
                      :id="'cb-' + b.id"
                      type="checkbox"
                      :value="b.id"
                      v-model="selectedBillIds"
                      class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                    />
                    <span>Bayar</span>
                  </label>
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- Tagihan Lainnya (Non-SPP: Uang Gedung, Seragam, dsb) -->
        <div v-if="studentCardData.once_bills.length > 0" class="bg-white p-5 rounded-lg border border-slate-200 shadow-2xs space-y-3">
          <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
            <Coins class="w-4 h-4 text-emerald-600" />
            <span>Tagihan Tambahan (Uang Gedung, Seragam, Ujian, dll)</span>
          </h4>

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            <div
              v-for="ob in studentCardData.once_bills"
              :key="'once-' + ob.id"
              class="p-3.5 rounded-lg border transition-all"
              :class="ob.status === 'paid' ? 'bg-emerald-50/70 border-emerald-200' : 'bg-white border-slate-200'"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-bold text-slate-800">{{ ob.payment_type?.name }}</span>
                <span
                  class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase"
                  :class="ob.status === 'paid' ? 'bg-emerald-200 text-emerald-900' : 'bg-rose-100 text-rose-800'"
                >
                  {{ ob.status === 'paid' ? 'Lunas' : 'Belum Bayar' }}
                </span>
              </div>
              <p class="text-xs font-mono font-bold text-slate-900">{{ formatRupiah(ob.amount) }}</p>

              <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between">
                <span v-if="ob.status === 'paid'" class="text-[10px] text-emerald-700 font-semibold">Lunas Terbayar</span>
                <label v-else :for="'cb-once-' + ob.id" class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-700">
                  <input
                    :id="'cb-once-' + ob.id"
                    type="checkbox"
                    :value="ob.id"
                    v-model="selectedBillIds"
                    class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                  />
                  <span>Bayar Tagihan Ini</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Sticky Floating Payment Bar if items are selected -->
        <div
          v-if="selectedBillIds.length > 0"
          class="sticky bottom-4 z-30 bg-slate-900 text-white p-4 rounded-xl shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-slide-up"
        >
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center font-bold text-white flex-shrink-0">
              {{ selectedBillIds.length }}
            </div>
            <div>
              <p class="text-xs font-bold">{{ selectedBillIds.length }} Tagihan Dipilih untuk Dibayar</p>
              <p class="text-xs font-mono font-bold text-emerald-400 mt-0.5">
                Total Pembayaran: {{ formatRupiah(selectedTotalAmount) }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="selectedBillIds = []"
              class="px-3 py-2 text-xs font-semibold text-slate-300 hover:text-white transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button
              @click="openPaymentModal"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md cursor-pointer transition-all active:scale-95"
            >
              <CreditCard class="w-4 h-4" />
              <span>Proses Pembayaran</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== TAB 2: MASTER POS TAGIHAN ==================== -->
    <div v-else-if="activeTab === 'payment_types'" class="space-y-4 no-print">
      <!-- Toolbar & Generate Button -->
      <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Daftar Pos Pembayaran & Tarif</h3>
          <p class="text-[11px] text-slate-500 mt-0.5">Atur pos pembayaran SPP bulanan atau tagihan sekali bayar di madrasah.</p>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="openGenerateModal"
            class="btn btn-secondary text-xs"
          >
            <Zap class="w-3.5 h-3.5 text-amber-600" />
            <span>Generate Tagihan Otomatis</span>
          </button>

          <button
            @click="openAddPaymentTypeModal"
            class="btn btn-primary text-xs"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>Tambah Pos Baru</span>
          </button>
        </div>
      </div>

      <!-- Payment Types Table -->
      <div class="bg-white rounded-lg border border-slate-200 shadow-2xs overflow-hidden">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
              <th class="p-3 w-12 text-center">NO</th>
              <th class="p-3">NAMA POS TAGIHAN</th>
              <th class="p-3">TIPE</th>
              <th class="p-3">NOMINAL TARIF</th>
              <th class="p-3">TAGIHAN AKTIF</th>
              <th class="p-3 text-right">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="paymentTypes.length === 0">
              <td colspan="6" class="p-8 text-center text-slate-400">
                Belum ada pos tagihan. Klik "Tambah Pos Baru" untuk membuat master tarif.
              </td>
            </tr>
            <tr v-for="(pt, idx) in paymentTypes" :key="pt.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="p-3 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
              <td class="p-3">
                <p class="font-bold text-slate-900">{{ pt.name }}</p>
                <p class="text-[10px] text-slate-400 font-normal">{{ pt.description || '-' }}</p>
              </td>
              <td class="p-3">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                  :class="pt.type === 'monthly' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-blue-50 text-blue-800 border border-blue-200'"
                >
                  {{ pt.type === 'monthly' ? 'Bulanan (SPP)' : 'Sekali Bayar' }}
                </span>
              </td>
              <td class="p-3 font-mono font-bold text-slate-800">
                {{ formatRupiah(pt.amount) }}
              </td>
              <td class="p-3">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 font-mono">
                  {{ pt.bills_count || 0 }} Siswa
                </span>
              </td>
              <td class="p-3 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    @click="editPaymentType(pt)"
                    class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-slate-100 rounded-md transition-colors cursor-pointer"
                    title="Ubah Tarif"
                  >
                    <Pencil class="w-3.5 h-3.5" />
                  </button>
                  <button
                    @click="deletePaymentType(pt)"
                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-colors cursor-pointer"
                    title="Hapus Pos"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ==================== TAB 3: REKAP KAS & LAPORAN TUNGGAKAN ==================== -->
    <div v-else-if="activeTab === 'reports'" class="space-y-4 no-print">
      <!-- Filter Bar -->
      <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3 w-full sm:w-auto">
          <label class="text-xs font-bold text-slate-700 uppercase tracking-wider shrink-0">Filter Kelas:</label>
          <select
            v-model="reportClassId"
            @change="fetchReports"
            class="form-input text-xs font-semibold w-full sm:w-60"
          >
            <option value="">-- Seluruh Kelas --</option>
            <option v-for="cls in classes" :key="'rep-cls-' + cls.id" :value="cls.id">
              Kelas {{ cls.name }}
            </option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="fetchReports"
            class="btn btn-secondary text-xs"
          >
            <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': loadingReport }" />
            <span>Segarkan Data</span>
          </button>
        </div>
      </div>

      <!-- Report Summary Card -->
      <div v-if="reportData.summary" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white p-3 rounded-lg border border-slate-200 text-center">
          <span class="text-[10px] font-bold text-slate-400 uppercase">Total Siswa</span>
          <p class="text-lg font-bold font-mono text-slate-900 mt-0.5">{{ reportData.summary.total_students }}</p>
        </div>
        <div class="bg-white p-3 rounded-lg border border-slate-200 text-center">
          <span class="text-[10px] font-bold text-emerald-700 uppercase">Siswa Lunas</span>
          <p class="text-lg font-bold font-mono text-emerald-800 mt-0.5">{{ reportData.summary.lunas_count }}</p>
        </div>
        <div class="bg-white p-3 rounded-lg border border-slate-200 text-center">
          <span class="text-[10px] font-bold text-rose-700 uppercase">Siswa Menunggak</span>
          <p class="text-lg font-bold font-mono text-rose-800 mt-0.5">{{ reportData.summary.nunggak_count }}</p>
        </div>
        <div class="bg-white p-3 rounded-lg border border-slate-200 text-center">
          <span class="text-[10px] font-bold text-rose-700 uppercase">Total Tunggakan</span>
          <p class="text-sm font-bold font-mono text-rose-800 mt-1">{{ formatRupiah(reportData.summary.total_remaining) }}</p>
        </div>
      </div>

      <!-- Report Table -->
      <div class="bg-white rounded-lg border border-slate-200 shadow-2xs overflow-hidden">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
              <th class="p-3 w-12 text-center">NO</th>
              <th class="p-3">NAMA SISWA</th>
              <th class="p-3">NISN</th>
              <th class="p-3">KELAS</th>
              <th class="p-3">TOTAL TAGIHAN</th>
              <th class="p-3">SUDAH DIBAYAR</th>
              <th class="p-3">SISA TUNGGAKAN</th>
              <th class="p-3 text-center">STATUS</th>
              <th class="p-3 text-right">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(st, idx) in reportData.students" :key="st.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="p-3 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
              <td class="p-3 font-bold text-slate-900">{{ st.full_name }}</td>
              <td class="p-3 font-mono text-slate-600">{{ st.nisn }}</td>
              <td class="p-3 font-bold text-emerald-700">{{ st.class_name }}</td>
              <td class="p-3 font-mono text-slate-700">{{ formatRupiah(st.total_billed) }}</td>
              <td class="p-3 font-mono text-emerald-700 font-semibold">{{ formatRupiah(st.total_paid) }}</td>
              <td class="p-3 font-mono font-bold" :class="st.remaining > 0 ? 'text-rose-600' : 'text-slate-400'">
                {{ formatRupiah(st.remaining) }}
              </td>
              <td class="p-3 text-center">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                  :class="st.status === 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                >
                  {{ st.status === 'lunas' ? 'Lunas' : 'Nunggak' }}
                </span>
              </td>
              <td class="p-3 text-right">
                <button
                  @click="openStudentFromReport(st.id)"
                  class="btn btn-outline text-[11px]"
                >
                  <span>Buka Kasir</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ==================== MODAL 1: FORM BAYAR KASIR ==================== -->
    <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs no-print">
      <div class="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-md p-5 space-y-4 animate-scale-in">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
              <CreditCard class="w-4 h-4" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900">Konfirmasi Pembayaran Kasir</h3>
              <p class="text-[10px] text-slate-500">{{ studentCardData.student?.full_name }}</p>
            </div>
          </div>
          <button @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <div class="space-y-3">
          <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Jumlah yang Dibayar</span>
            <p class="text-xl font-bold font-mono text-emerald-700 mt-0.5">{{ formatRupiah(selectedTotalAmount) }}</p>
            <p class="text-[10px] text-slate-500 mt-1">Mencakup {{ selectedBillIds.length }} item tagihan terpilih.</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Pembayaran</label>
            <select v-model="paymentForm.payment_method" class="form-input text-xs">
              <option value="cash">Tunai / Cash di Madrasah</option>
              <option value="transfer">Transfer Bank</option>
              <option value="qris">QRIS / Dompet Digital</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Bayar</label>
            <input v-model="paymentForm.payment_date" type="date" class="form-input text-xs" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
            <input v-model="paymentForm.notes" type="text" placeholder="Contoh: Titipan orang tua..." class="form-input text-xs" />
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
          <button @click="showPaymentModal = false" class="btn btn-secondary">Batal</button>
          <button
            @click="submitPayment"
            :disabled="submittingPayment"
            class="btn btn-primary"
          >
            <div v-if="submittingPayment" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></div>
            <span>{{ submittingPayment ? 'Menyimpan...' : 'Simpan & Cetak Kwitansi' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ==================== MODAL 2: FORM TAMBAH / UBAH POS TAGIHAN ==================== -->
    <div v-if="showPaymentTypeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs no-print">
      <div class="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-md p-5 space-y-4 animate-scale-in">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="text-sm font-bold text-slate-900">{{ editingPaymentTypeId ? 'Ubah Pos Tagihan' : 'Tambah Pos Tagihan Baru' }}</h3>
          <button @click="showPaymentTypeModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pos Tagihan *</label>
            <input v-model="paymentTypeForm.name" type="text" placeholder="Contoh: SPP Bulanan, Uang Gedung" class="form-input text-xs" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Tagihan *</label>
            <select v-model="paymentTypeForm.type" class="form-input text-xs">
              <option value="monthly">Bulanan (Berulang 12 Bulan seperti SPP)</option>
              <option value="once">Sekali Bayar (Insidental seperti Seragam/Gedung)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal Tarif (Rp) *</label>
            <input v-model.number="paymentTypeForm.amount" type="number" placeholder="Contoh: 150000" class="form-input text-xs font-mono" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan Tambahan</label>
            <textarea v-model="paymentTypeForm.description" rows="2" class="form-input text-xs" placeholder="Keterangan opsional..."></textarea>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
          <button @click="showPaymentTypeModal = false" class="btn btn-secondary">Batal</button>
          <button @click="savePaymentType" :disabled="savingPaymentType" class="btn btn-primary">
            <span>{{ savingPaymentType ? 'Menyimpan...' : 'Simpan Pos Tagihan' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ==================== MODAL 3: GENERATE TAGIHAN OTOMATIS ==================== -->
    <div v-if="showGenerateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs no-print">
      <div class="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-md p-5 space-y-4 animate-scale-in">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2">
            <Zap class="w-4 h-4 text-amber-600" />
            <h3 class="text-sm font-bold text-slate-900">Generate Tagihan Siswa Otomatis</h3>
          </div>
          <button @click="showGenerateModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs leading-relaxed">
          💡 Fitur ini akan otomatis membuatkan tagihan (misal: 12 bulan SPP) untuk seluruh siswa aktif sesuai pos tagihan yang dipilih tanpa menimpa data yang sudah ada.
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Pos Tagihan *</label>
            <select v-model="generateForm.payment_type_id" class="form-input text-xs font-semibold">
              <option value="">-- Pilih Pos Tagihan --</option>
              <option v-for="pt in paymentTypes" :key="'gen-pt-' + pt.id" :value="pt.id">
                {{ pt.name }} ({{ formatRupiah(pt.amount) }} • {{ pt.type === 'monthly' ? 'Bulanan' : 'Sekali Bayar' }})
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Sasaran Siswa</label>
            <select v-model="generateForm.class_id" class="form-input text-xs font-semibold">
              <option value="">Semua Siswa Aktif (Seluruh Kelas 7, 8, 9)</option>
              <option v-for="cls in classes" :key="'gen-cls-' + cls.id" :value="cls.id">
                Hanya Kelas {{ cls.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
          <button @click="showGenerateModal = false" class="btn btn-secondary">Batal</button>
          <button @click="executeGenerateBills" :disabled="generatingBills" class="btn btn-primary">
            <div v-if="generatingBills" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></div>
            <span>{{ generatingBills ? 'Memproses Tagihan...' : 'Mulai Generate Tagihan' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ==================== MODAL 4: CETAK KWITANSI RESMI ==================== -->
    <div v-if="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs no-print">
      <div class="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-xl p-6 space-y-5 animate-scale-in">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2">
            <CheckCircle2 class="w-5 h-5 text-emerald-600" />
            <h3 class="text-sm font-bold text-slate-900">Bukti Pembayaran / Kwitansi Resmi</h3>
          </div>
          <button @click="showReceiptModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <!-- Receipt Card Preview -->
        <div id="receipt-print-area" class="p-6 bg-slate-50/70 border border-slate-200 rounded-xl space-y-4 text-xs font-sans">
          <!-- Kop Kwitansi -->
          <div class="text-center border-b border-slate-300 pb-3">
            <h2 class="font-extrabold text-sm uppercase tracking-wide text-slate-900">KWITANSI BUKTI PEMBAYARAN SISWA</h2>
            <p class="text-[11px] font-bold text-emerald-800">SIAKAD MTS AL - HASANAH</p>
            <p class="text-[9.5px] font-mono text-slate-500 mt-0.5">No. Kwitansi: {{ receiptData.invoice_number }}</p>
          </div>

          <!-- Metadata -->
          <div class="grid grid-cols-2 gap-2 text-[11px]">
            <div>
              <span class="text-slate-400 block text-[9.5px]">Telah Terima Dari:</span>
              <p class="font-bold text-slate-900">{{ receiptData.student?.full_name }}</p>
              <p class="text-slate-500 font-mono text-[10px]">NISN: {{ receiptData.student?.nisn || '-' }} • Kelas: {{ receiptData.student?.class_room?.name || '-' }}</p>
            </div>
            <div class="text-right">
              <span class="text-slate-400 block text-[9.5px]">Tanggal / Metode:</span>
              <p class="font-bold text-slate-800">{{ receiptData.payment_date }}</p>
              <p class="text-emerald-700 font-bold uppercase text-[10px]">Metode: {{ receiptData.payment_method }}</p>
            </div>
          </div>

          <!-- Items Table -->
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-y border-slate-300 bg-white font-bold text-[10px] uppercase text-slate-600">
                <th class="py-1.5 px-2">Rincian Pembayaran</th>
                <th class="py-1.5 px-2 text-right">Jumlah (Rp)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono">
              <tr v-for="(it, idx) in receiptData.items" :key="'rec-it-' + idx">
                <td class="py-1.5 px-2 font-sans font-semibold text-slate-800">{{ it.name }}</td>
                <td class="py-1.5 px-2 text-right font-bold text-slate-900">{{ formatRupiah(it.amount) }}</td>
              </tr>
              <tr class="border-t-2 border-slate-300 font-bold bg-white">
                <td class="py-2 px-2 text-right font-sans text-xs">TOTAL DIBAYARKAN:</td>
                <td class="py-2 px-2 text-right text-emerald-800 text-sm font-bold">{{ formatRupiah(receiptData.total_paid) }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Signatures -->
          <div class="grid grid-cols-2 pt-4 text-center text-[10px] text-slate-600">
            <div>
              <p>Penyetor / Wali Siswa,</p>
              <div class="h-12"></div>
              <p class="font-bold text-slate-800">( ........................................ )</p>
            </div>
            <div>
              <p>Petugas Kasir / Bendahara,</p>
              <div class="h-12"></div>
              <p class="font-bold text-slate-800">( {{ receiptData.receiver?.name || 'Bendahara Madrasah' }} )</p>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
          <button @click="showReceiptModal = false" class="btn btn-secondary">Tutup</button>
          <button @click="printReceipt" class="btn btn-primary">
            <Printer class="w-4 h-4" />
            <span>Cetak Kwitansi Sekarang</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import {
  Wallet,
  CreditCard,
  Coins,
  FileSpreadsheet,
  Plus,
  RefreshCw,
  UserCheck,
  Calendar,
  Check,
  CheckCircle2,
  AlertCircle,
  Receipt,
  ArrowDownLeft,
  Pencil,
  Trash2,
  Zap,
  Printer,
} from 'lucide-vue-next';

const toast = useToast();

const activeTab = ref('cashier');

const summary = ref({
  this_month_income: 0,
  total_year_income: 0,
  total_unpaid_amount: 0,
  total_transactions_count: 0,
});

const classes = ref([]);
const classStudents = ref([]);
const selectedClassId = ref('');
const selectedStudentId = ref('');

const studentCardData = ref({
  student: null,
  monthly_bills: [],
  once_bills: [],
  total_billed: 0,
  total_paid: 0,
  total_remaining: 0,
});

const selectedBillIds = ref([]);
const loadingCard = ref(false);

const paymentTypes = ref([]);
const showPaymentTypeModal = ref(false);
const editingPaymentTypeId = ref(null);
const savingPaymentType = ref(false);
const paymentTypeForm = ref({
  name: '',
  type: 'monthly',
  amount: 0,
  description: '',
});

const showGenerateModal = ref(false);
const generatingBills = ref(false);
const generateForm = ref({
  payment_type_id: '',
  class_id: '',
});

const showPaymentModal = ref(false);
const submittingPayment = ref(false);
const paymentForm = ref({
  payment_method: 'cash',
  payment_date: new Date().toISOString().substring(0, 10),
  notes: '',
});

const showReceiptModal = ref(false);
const receiptData = ref({});

const reportClassId = ref('');
const loadingReport = ref(false);
const reportData = ref({
  students: [],
  summary: null,
});

const selectedTotalAmount = computed(() => {
  if (selectedBillIds.value.length === 0) return 0;
  const allBills = [
    ...(studentCardData.value.monthly_bills || []),
    ...(studentCardData.value.once_bills || []),
  ];
  return allBills
    .filter(b => selectedBillIds.value.includes(b.id))
    .reduce((sum, b) => sum + (parseFloat(b.amount) - parseFloat(b.amount_paid)), 0);
});

const formatRupiah = (val) => {
  if (!val && val !== 0) return 'Rp 0';
  return 'Rp ' + Number(val).toLocaleString('id-ID');
};

const fetchSummary = async () => {
  try {
    const res = await api.get('admin/finance/summary');
    summary.value = res?.data || res || {};
  } catch (err) {
    console.error('Failed to fetch summary:', err);
  }
};

const fetchClasses = async () => {
  try {
    const res = await api.get('admin/classes');
    classes.value = res?.data?.classes || res?.classes || res || [];
  } catch (err) {
    console.error('Failed to load classes:', err);
  }
};

const fetchStudentsByClass = async () => {
  try {
    const params = selectedClassId.value ? { class_id: selectedClassId.value } : {};
    const res = await api.get('admin/students', params);
    classStudents.value = res?.data?.students || res?.students || [];
  } catch (err) {
    console.error('Failed to load students:', err);
  }
};

const loadStudentCard = async () => {
  if (!selectedStudentId.value) return;
  loadingCard.value = true;
  selectedBillIds.value = [];
  try {
    const res = await api.get(`admin/finance/student-card/${selectedStudentId.value}`);
    studentCardData.value = res?.data || {};
  } catch (err) {
    toast.error('Gagal memuat kartu pembayaran siswa');
  } finally {
    loadingCard.value = false;
  }
};

const selectAllUnpaidMonthly = () => {
  const unpaid = studentCardData.value.monthly_bills
    .filter(b => b.status !== 'paid')
    .map(b => b.id);
  selectedBillIds.value = unpaid;
};

const fetchPaymentTypes = async () => {
  try {
    const res = await api.get('admin/finance/payment-types');
    paymentTypes.value = res?.data || [];
  } catch (err) {
    console.error('Failed to fetch payment types:', err);
  }
};

const openAddPaymentTypeModal = () => {
  editingPaymentTypeId.value = null;
  paymentTypeForm.value = {
    name: '',
    type: 'monthly',
    amount: 0,
    description: '',
  };
  showPaymentTypeModal.value = true;
};

const editPaymentType = (pt) => {
  editingPaymentTypeId.value = pt.id;
  paymentTypeForm.value = {
    name: pt.name,
    type: pt.type,
    amount: pt.amount,
    description: pt.description || '',
  };
  showPaymentTypeModal.value = true;
};

const savePaymentType = async () => {
  if (!paymentTypeForm.value.name || !paymentTypeForm.value.amount) {
    toast.error('Nama dan nominal tarif wajib diisi');
    return;
  }
  savingPaymentType.value = true;
  try {
    if (editingPaymentTypeId.value) {
      await api.put(`admin/finance/payment-types/${editingPaymentTypeId.value}`, paymentTypeForm.value);
      toast.success('Pos tagihan berhasil diperbarui');
    } else {
      await api.post('admin/finance/payment-types', paymentTypeForm.value);
      toast.success('Pos tagihan baru berhasil ditambahkan');
    }
    showPaymentTypeModal.value = false;
    await fetchPaymentTypes();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan pos tagihan');
  } finally {
    savingPaymentType.value = false;
  }
};

const deletePaymentType = async (pt) => {
  if (!confirm(`Yakin ingin menghapus pos tagihan "${pt.name}"?`)) return;
  try {
    await api.delete(`admin/finance/payment-types/${pt.id}`);
    toast.success('Pos tagihan berhasil dihapus');
    await fetchPaymentTypes();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menghapus pos tagihan');
  }
};

const openGenerateModal = () => {
  generateForm.value = {
    payment_type_id: paymentTypes.value[0]?.id || '',
    class_id: '',
  };
  showGenerateModal.value = true;
};

const executeGenerateBills = async () => {
  if (!generateForm.value.payment_type_id) {
    toast.error('Silakan pilih pos tagihan terlebih dahulu');
    return;
  }
  generatingBills.value = true;
  try {
    const res = await api.post('admin/finance/generate-bills', generateForm.value);
    toast.success(res?.data?.message || res?.message || 'Tagihan berhasil dibuat!');
    showGenerateModal.value = false;
    await fetchSummary();
    if (selectedStudentId.value) {
      await loadStudentCard();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal memproses pembuatan tagihan');
  } finally {
    generatingBills.value = false;
  }
};

const openPaymentModal = () => {
  if (selectedBillIds.value.length === 0) {
    toast.error('Pilih minimal 1 bulan / tagihan untuk dibayar');
    return;
  }
  paymentForm.value = {
    payment_method: 'cash',
    payment_date: new Date().toISOString().substring(0, 10),
    notes: '',
  };
  showPaymentModal.value = true;
};

const submitPayment = async () => {
  submittingPayment.value = true;
  try {
    const payload = {
      student_id: selectedStudentId.value,
      bill_ids: selectedBillIds.value,
      payment_method: paymentForm.value.payment_method,
      payment_date: paymentForm.value.payment_date,
      notes: paymentForm.value.notes,
    };

    const res = await api.post('admin/finance/pay', payload);
    const invoiceNum = res?.data?.data?.invoice_number || res?.data?.invoice_number;

    toast.success('Pembayaran berhasil dicatat!');
    showPaymentModal.value = false;

    // Refresh student card & summary
    await loadStudentCard();
    await fetchSummary();

    // Load receipt and show receipt modal
    if (invoiceNum) {
      const receiptRes = await api.get(`admin/finance/receipt/${invoiceNum}`);
      receiptData.value = receiptRes?.data?.data || receiptRes?.data || {};
      showReceiptModal.value = true;
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal memproses pembayaran kasir');
  } finally {
    submittingPayment.value = false;
  }
};

const printReceipt = () => {
  window.print();
};

const fetchReports = async () => {
  loadingReport.value = true;
  try {
    const params = reportClassId.value ? { class_id: reportClassId.value } : {};
    const res = await api.get('admin/finance/reports', params);
    reportData.value = res?.data?.data || res?.data || {};
  } catch (err) {
    console.error('Failed to fetch reports:', err);
  } finally {
    loadingReport.value = false;
  }
};

const openStudentFromReport = (studentId) => {
  selectedStudentId.value = studentId;
  activeTab.value = 'cashier';
  loadStudentCard();
};

onMounted(async () => {
  await fetchSummary();
  await fetchClasses();
  await fetchPaymentTypes();
  await fetchStudentsByClass();
});
</script>

<style scoped>
@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.96) translateY(-6px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
.animate-scale-in {
  animation: scaleIn 0.16s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@media print {
  .no-print {
    display: none !important;
  }
  #receipt-print-area {
    display: block !important;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    padding: 20px;
    background: white !important;
    border: none !important;
  }
}
</style>
