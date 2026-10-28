<template>
  <div class="space-y-6 font-inter pb-12">
    <!-- Header Hero: Deep Emerald Academic Dignity -->
    <div class="relative bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-950 text-white rounded-2xl p-6 sm:p-7 shadow-lg shadow-emerald-900/20 overflow-hidden border border-emerald-700/60 no-print">
      <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.15)_1px,transparent_1px)] [background-size:20px_20px] opacity-40 pointer-events-none"></div>
      <div class="absolute -top-12 -right-12 w-56 h-56 bg-emerald-500/20 rounded-full blur-2xl pointer-events-none"></div>

      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 bg-white/15 backdrop-blur-md rounded-2xl border border-white/30 p-2.5 flex items-center justify-center flex-shrink-0 text-white shadow-md">
            <Wallet class="w-7 h-7 text-emerald-300" />
          </div>
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/30 text-emerald-200 border border-emerald-400/30">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Portal Administrasi & SPP Siswa
              </span>
              <span class="text-xs font-medium text-emerald-200/80 font-mono">
                {{ financeData.academic_year?.name || 'T.A. Berjalan' }}
              </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white uppercase font-sans">
              Buku Kendali SPP & Pembayaran
            </h1>
            <p class="text-emerald-100/80 text-xs mt-0.5 font-normal">
              Informasi tagihan SPP 12 bulan, rekap pembayaran, dan cetak bukti kwitansi resmi madrasah.
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <button
            @click="fetchCard"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-md transition-colors cursor-pointer"
          >
            <RefreshCw class="w-3.5 h-3.5 text-emerald-300" :class="{ 'animate-spin': loading }" />
            <span>Segarkan Data</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="py-20 text-center bg-white rounded-2xl border border-slate-200 shadow-2xs">
      <div class="animate-spin h-8 w-8 border-3 border-emerald-600 border-t-transparent rounded-full mx-auto mb-3"></div>
      <p class="text-xs font-semibold text-slate-600">Memuat data buku kendali SPP siswa...</p>
    </div>

    <template v-else>
      <!-- Identity & Saldo Cards Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 no-print">
        <!-- Student Info Card (4 Cols) -->
        <div class="lg:col-span-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center gap-4">
          <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0 flex items-center justify-center font-bold text-slate-600 shadow-2xs">
            <img v-if="financeData.student?.photo_url" :src="financeData.student.photo_url" class="w-full h-full object-cover" alt="Foto Siswa" />
            <span v-else class="text-xl text-emerald-700 font-extrabold">{{ financeData.student?.full_name?.charAt(0) || 'S' }}</span>
          </div>
          <div class="min-w-0">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Siswa Aktif</span>
            <h2 class="text-sm font-black text-slate-900 truncate">{{ financeData.student?.full_name }}</h2>
            <p class="text-xs text-slate-500 font-mono mt-0.5">
              NISN: <span class="font-bold text-slate-700">{{ financeData.student?.nisn || '-' }}</span>
            </p>
            <div class="mt-1.5 flex items-center gap-1.5">
              <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                Kelas {{ financeData.student?.class_name }}
              </span>
              <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                Tingkat {{ financeData.student?.grade_level }}
              </span>
            </div>
          </div>
        </div>

        <!-- 3 Financial Stat Cards (8 Cols) -->
        <div class="lg:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-3.5">
          <!-- Total Tagihan -->
          <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex flex-col justify-between">
            <div>
              <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Biaya T.A. Ini</span>
              <p class="text-lg sm:text-xl font-bold font-mono text-slate-900 mt-1">
                {{ formatRupiah(financeData.total_billed) }}
              </p>
            </div>
            <div class="mt-2 text-[10px] text-slate-400 font-medium flex items-center gap-1">
              <Calendar class="w-3 h-3 text-slate-400" />
              <span>1 Tahun Ajaran Penuh</span>
            </div>
          </div>

          <!-- Total Terbayar -->
          <div class="bg-white p-4 rounded-2xl border border-emerald-200/90 shadow-2xs flex flex-col justify-between bg-emerald-50/20">
            <div>
              <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Lunas Dibayarkan</span>
              <p class="text-lg sm:text-xl font-bold font-mono text-emerald-700 mt-1">
                {{ formatRupiah(financeData.total_paid) }}
              </p>
            </div>
            <div class="mt-2 text-[10px] text-emerald-700 font-bold flex items-center gap-1">
              <CheckCircle2 class="w-3 h-3 text-emerald-600" />
              <span>{{ percentPaid }}% Terpenuhi</span>
            </div>
          </div>

          <!-- Sisa Tunggakan -->
          <div
            class="p-4 rounded-2xl border shadow-2xs flex flex-col justify-between transition-colors"
            :class="financeData.total_remaining > 0 ? 'bg-rose-50/40 border-rose-200 text-rose-900' : 'bg-emerald-50/40 border-emerald-200 text-emerald-900'"
          >
            <div>
              <span class="text-[10px] font-bold uppercase tracking-wider" :class="financeData.total_remaining > 0 ? 'text-rose-700' : 'text-emerald-700'">
                Sisa Kewajiban
              </span>
              <p class="text-lg sm:text-xl font-bold font-mono mt-1" :class="financeData.total_remaining > 0 ? 'text-rose-700' : 'text-emerald-700'">
                {{ formatRupiah(financeData.total_remaining) }}
              </p>
            </div>
            <div class="mt-2 text-[10px] font-bold flex items-center gap-1">
              <AlertCircle v-if="financeData.total_remaining > 0" class="w-3 h-3 text-rose-600" />
              <Check v-else class="w-3 h-3 text-emerald-600" />
              <span :class="financeData.total_remaining > 0 ? 'text-rose-600' : 'text-emerald-700'">
                {{ financeData.total_remaining > 0 ? 'Belum Diselesaikan' : 'Administrasi Lunas' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Section: Buku Kendali SPP 12 Bulan (Juli s.d. Juni) -->
      <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-2xs space-y-4 no-print">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
          <div>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
              <Calendar class="w-4 h-4 text-emerald-600" />
              <span>Buku Kendali SPP Bulanan (12 Bulan Pendidikan)</span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
              Jadwal pembayaran SPP terbagi dari bulan Juli sampai Juni sesuai kalender pendidikan madrasah.
            </p>
          </div>

          <!-- Status Legend -->
          <div class="flex items-center gap-3 text-[11px] font-semibold text-slate-600 flex-wrap">
            <span class="inline-flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
              <span>Lunas Terbayar</span>
            </span>
            <span class="inline-flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
              <span>Cicilan/Sebagian</span>
            </span>
            <span class="inline-flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
              <span>Belum Dibayar</span>
            </span>
          </div>
        </div>

        <!-- 12-Month Grid Matrix -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
          <div
            v-for="b in (financeData.monthly_bills || [])"
            :key="'st-bill-' + (b?.id || b?.month_order)"
            class="relative p-3.5 rounded-xl border transition-all select-none"
            :class="[
              b?.status === 'paid' ? 'bg-emerald-50/70 border-emerald-300/80 shadow-2xs' :
              b?.status === 'partial' ? 'bg-amber-50/70 border-amber-300/80 shadow-2xs' :
              b?.status === 'unbilled' ? 'bg-slate-50 border-slate-200 opacity-60' :
              'bg-white border-rose-200 hover:border-rose-300 shadow-2xs'
            ]"
          >
            <!-- Header Card (Month Order & Status Badge) -->
            <div class="flex items-center justify-between mb-2">
              <span class="text-[10px] font-mono font-bold uppercase text-slate-400">
                #{{ b?.month_order }}
              </span>
              <span
                class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider"
                :class="[
                  b?.status === 'paid' ? 'bg-emerald-100 text-emerald-800' :
                  b?.status === 'partial' ? 'bg-amber-100 text-amber-800' :
                  b?.status === 'unbilled' ? 'bg-slate-200 text-slate-600' :
                  'bg-rose-100 text-rose-800'
                ]"
              >
                {{ b?.status === 'paid' ? 'Lunas' : b?.status === 'partial' ? 'Sebagian' : b?.status === 'unbilled' ? 'Belum Ada' : 'Belum Bayar' }}
              </span>
            </div>

            <!-- Month Name & Amount -->
            <div class="space-y-0.5">
              <p class="text-xs font-bold text-slate-900">{{ b?.month }}</p>
              <p class="text-xs font-mono font-bold" :class="b?.status === 'paid' ? 'text-emerald-700' : 'text-slate-800'">
                {{ formatRupiah(b?.amount) }}
              </p>
            </div>

            <!-- Footer Card: Payment Info -->
            <div class="mt-3 pt-2 border-t border-slate-200/60 flex items-center justify-between text-[10px]">
              <template v-if="b?.status === 'paid'">
                <span class="text-emerald-700 font-semibold flex items-center gap-1">
                  <CheckCircle2 class="w-3 h-3 text-emerald-600" />
                  <span>Selesai</span>
                </span>
              </template>
              <template v-else-if="b?.status === 'partial'">
                <span class="text-amber-800 font-medium">
                  Sisa: <strong class="font-mono">{{ formatRupiah(b?.remaining) }}</strong>
                </span>
              </template>
              <template v-else-if="b?.status === 'unpaid'">
                <span class="text-rose-600 font-medium">Belum Lunas</span>
              </template>
              <template v-else>
                <span class="text-slate-400">Belum Ditagihkan</span>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Section: Tagihan Tambahan (Non-SPP) jika ada -->
      <div v-if="financeData.once_bills && financeData.once_bills.length > 0" class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-2xs space-y-3 no-print">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
          <Coins class="w-4 h-4 text-emerald-600" />
          <span>Tagihan Tambahan (Uang Gedung, Seragam, Ujian, dll)</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
          <div
            v-for="ob in (financeData.once_bills || [])"
            :key="'st-once-' + (ob?.id || '')"
            class="p-4 rounded-xl border transition-all"
            :class="ob?.status === 'paid' ? 'bg-emerald-50/50 border-emerald-200' : 'bg-white border-slate-200'"
          >
            <div class="flex items-center justify-between mb-1">
              <span class="text-xs font-bold text-slate-900">{{ ob?.payment_type_name }}</span>
              <span
                class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider"
                :class="ob?.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
              >
                {{ ob?.status === 'paid' ? 'Lunas' : 'Belum Bayar' }}
              </span>
            </div>
            <p class="text-xs font-mono font-bold text-slate-800">{{ formatRupiah(ob?.amount) }}</p>
            <div class="mt-2 pt-2 border-t border-slate-100 text-[10px] text-slate-500">
              <span v-if="ob?.status === 'paid'" class="text-emerald-700 font-semibold flex items-center gap-1">
                <Check class="w-3 h-3 text-emerald-600" />
                <span>Terbayar Lunas</span>
              </span>
              <span v-else class="text-rose-600 font-medium">
                Sisa yang harus dibayar: <strong class="font-mono">{{ formatRupiah(ob?.remaining) }}</strong>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Section: Riwayat Transaksi & Kwitansi Resmi -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden no-print">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-3">
          <div>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
              <Receipt class="w-4 h-4 text-emerald-600" />
              <span>Riwayat Transaksi & Bukti Pembayaran Resmi</span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
              Daftar seluruh transaksi yang telah dicatat oleh kasir TU madrasah. Anda dapat mengunduh bukti kwitansi kapan saja.
            </p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                <th class="p-3.5 w-12 text-center">NO</th>
                <th class="p-3.5">NO. KWITANSI</th>
                <th class="p-3.5">TANGGAL</th>
                <th class="p-3.5">ITEM PEMBAYARAN</th>
                <th class="p-3.5">JUMLAH (RP)</th>
                <th class="p-3.5">METODE</th>
                <th class="p-3.5">PENERIMA</th>
                <th class="p-3.5 text-right">AKSI</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="!financeData.transactions || financeData.transactions.length === 0">
                <td colspan="8" class="p-8 text-center text-slate-400">
                  Belum ada riwayat transaksi pembayaran yang tercatat pada tahun ajaran ini.
                </td>
              </tr>
              <tr
                v-for="(tr, idx) in (financeData.transactions || [])"
                :key="'tr-' + (tr?.id || idx)"
                class="hover:bg-slate-50/70 transition-colors"
              >
                <td class="p-3.5 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="p-3.5 font-mono font-bold text-emerald-700">{{ tr?.invoice_number }}</td>
                <td class="p-3.5 text-slate-700">{{ tr?.payment_date }}</td>
                <td class="p-3.5 font-semibold text-slate-900">{{ tr?.payment_type_name }}</td>
                <td class="p-3.5 font-mono font-bold text-slate-900">{{ formatRupiah(tr?.amount_paid) }}</td>
                <td class="p-3.5">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                    {{ tr?.payment_method }}
                  </span>
                </td>
                <td class="p-3.5 text-slate-600">{{ tr?.receiver_name }}</td>
                <td class="p-3.5 text-right">
                  <button
                    @click="viewReceipt(tr?.invoice_number)"
                    class="btn btn-outline text-[11px] py-1 px-2.5 inline-flex items-center gap-1.5"
                  >
                    <Printer class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Cetak Kwitansi</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Section: Petunjuk Pembayaran & Rekening Madrasah -->
      <div class="bg-gradient-to-br from-slate-900 to-slate-950 text-white p-6 rounded-2xl border border-slate-800 shadow-md space-y-4 no-print">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center flex-shrink-0">
            <Building2 class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-white">Petunjuk Pembayaran Administrasi Sekolah</h3>
            <p class="text-xs text-slate-400">Pembayaran dapat dilakukan melalui loket tunai madrasah atau transfer bank resmi.</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
          <!-- Cara 1: Loket TU Madrasah -->
          <div class="bg-white/5 border border-white/10 rounded-xl p-4 space-y-1.5">
            <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Metode 1: Loket Tunai</span>
            <p class="text-xs font-bold text-white">Loket Tata Usaha (TU) MTs Al - Hasanah</p>
            <p class="text-xs text-slate-300 leading-relaxed">
              Buka setiap hari Senin &ndash; Sabtu, pukul 07.30 &ndash; 14.30 WIB. Datang langsung dan temui staf bendahara madrasah.
            </p>
          </div>

          <!-- Cara 2: Transfer Bank -->
          <div class="bg-white/5 border border-white/10 rounded-xl p-4 space-y-1.5">
            <span class="text-[10px] font-bold text-teal-400 uppercase tracking-wider">Metode 2: Transfer Bank Resmi</span>
            <p class="text-xs font-bold text-white font-mono">
              {{ financeData.payment_info?.bank_name || 'Bank Syariah Indonesia (BSI)' }} : 
              <span class="text-emerald-300 font-extrabold">{{ financeData.payment_info?.bank_account || '1234567890' }}</span>
            </p>
            <p class="text-xs text-slate-300">
              A.n. <strong class="text-white">{{ financeData.payment_info?.bank_holder || 'MTs Al - Hasanah' }}</strong>. 
              Setelah transfer, kirimkan bukti setoran via WhatsApp ke nomor madrasah.
            </p>
          </div>
        </div>
      </div>
    </template>

    <!-- ==================== MODAL: CETAK KWITANSI RESMI ==================== -->
    <div v-if="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs no-print">
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-xl p-6 space-y-5 animate-scale-in">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2">
            <CheckCircle2 class="w-5 h-5 text-emerald-600" />
            <h3 class="text-sm font-bold text-slate-900">Bukti Pembayaran / Kwitansi Resmi</h3>
          </div>
          <button @click="showReceiptModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
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
              <p class="text-slate-500 font-mono text-[10px]">
                NISN: {{ receiptData.student?.nisn || '-' }} • Kelas: {{ receiptData.student?.class_room?.name || '-' }}
              </p>
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
              <tr v-for="(it, idx) in (receiptData.items || [])" :key="'rec-it-' + idx">
                <td class="py-1.5 px-2 font-sans font-semibold text-slate-800">{{ it?.name }}</td>
                <td class="py-1.5 px-2 text-right font-bold text-slate-900">{{ formatRupiah(it?.amount) }}</td>
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
          <button @click="showReceiptModal = false" class="btn btn-secondary cursor-pointer">Tutup</button>
          <button @click="printReceipt" class="btn btn-primary cursor-pointer">
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
  Calendar,
  Check,
  CheckCircle2,
  AlertCircle,
  Receipt,
  Printer,
  RefreshCw,
  Coins,
  Building2,
} from 'lucide-vue-next';

const toast = useToast();

const loading = ref(true);
const financeData = ref({
  student: null,
  academic_year: null,
  monthly_bills: [],
  once_bills: [],
  total_billed: 0,
  total_paid: 0,
  total_remaining: 0,
  transactions: [],
  payment_info: null,
});

const showReceiptModal = ref(false);
const receiptData = ref({});

const formatRupiah = (val) => {
  if (!val && val !== 0) return 'Rp 0';
  return 'Rp ' + Number(val).toLocaleString('id-ID');
};

const percentPaid = computed(() => {
  if (!financeData.value.total_billed || financeData.value.total_billed === 0) return 0;
  return Math.min(100, Math.round((financeData.value.total_paid / financeData.value.total_billed) * 100));
});

const fetchCard = async () => {
  loading.value = true;
  try {
    const res = await api.get('student/finance/card');
    const raw = res?.data || res || {};
    financeData.value = {
      student: raw.student || null,
      academic_year: raw.academic_year || null,
      monthly_bills: Array.isArray(raw.monthly_bills) ? raw.monthly_bills.filter(Boolean) : [],
      once_bills: Array.isArray(raw.once_bills) ? raw.once_bills.filter(Boolean) : [],
      total_billed: Number(raw.total_billed || 0),
      total_paid: Number(raw.total_paid || 0),
      total_remaining: Number(raw.total_remaining || 0),
      transactions: Array.isArray(raw.transactions) ? raw.transactions.filter(Boolean) : [],
      payment_info: raw.payment_info || null,
    };
  } catch (err) {
    console.error('Failed to load student finance card:', err);
    toast.error('Gagal memuat buku kendali SPP siswa');
  } finally {
    loading.value = false;
  }
};

const viewReceipt = async (invoiceNumber) => {
  if (!invoiceNumber) return;
  try {
    const res = await api.get(`student/finance/receipt/${invoiceNumber}`);
    receiptData.value = res?.data || res || {};
    showReceiptModal.value = true;
  } catch (err) {
    toast.error('Gagal memuat bukti kwitansi resmi');
  }
};

const printReceipt = () => {
  window.print();
};

onMounted(() => {
  fetchCard();
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
    padding: 24px;
    background: white !important;
    border: none !important;
  }
}
</style>
