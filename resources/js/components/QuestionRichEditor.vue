<template>
  <div class="rich-question-editor border border-slate-300 rounded-xl overflow-visible focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/20 shadow-2xs transition bg-white flex flex-col relative">
    <!-- TOOLBAR -->
    <div class="bg-slate-50/90 border-b border-slate-200 px-2 py-1.5 flex flex-wrap items-center gap-1 text-slate-700 select-none shrink-0 rounded-t-xl">
      <!-- 1. FONT SIZE SELECTOR -->
      <div class="flex items-center">
        <select
          v-model="currentHeading"
          @change="applyHeading"
          class="h-7 text-xs font-semibold bg-white border border-slate-200 rounded-lg px-2 text-slate-700 focus:outline-none focus:border-emerald-500"
          title="Ukuran Tulisan / Heading"
        >
          <option value="p">Teks Normal</option>
          <option value="h3">Teks Sedang (Subjudul)</option>
          <option value="h2">Teks Besar (Judul Soal)</option>
        </select>
      </div>

      <div class="w-px h-4 bg-slate-300 mx-0.5"></div>

      <!-- 2. BASIC FORMATTING: BOLD, ITALIC, UNDERLINE -->
      <button
        type="button"
        @click="exec('bold')"
        class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-bold transition"
        :class="activeFormats.bold ? 'bg-emerald-600 text-white shadow-2xs' : 'hover:bg-slate-200 text-slate-700'"
        title="Tebal (Ctrl+B)"
      >
        <Bold class="w-3.5 h-3.5" />
      </button>

      <button
        type="button"
        @click="exec('italic')"
        class="w-7 h-7 flex items-center justify-center rounded-lg text-xs transition"
        :class="activeFormats.italic ? 'bg-emerald-600 text-white shadow-2xs' : 'hover:bg-slate-200 text-slate-700'"
        title="Miring (Ctrl+I)"
      >
        <Italic class="w-3.5 h-3.5" />
      </button>

      <button
        type="button"
        @click="exec('underline')"
        class="w-7 h-7 flex items-center justify-center rounded-lg text-xs transition"
        :class="activeFormats.underline ? 'bg-emerald-600 text-white shadow-2xs' : 'hover:bg-slate-200 text-slate-700'"
        title="Garis Bawah (Ctrl+U)"
      >
        <Underline class="w-3.5 h-3.5" />
      </button>

      <div class="w-px h-4 bg-slate-300 mx-0.5"></div>

      <!-- 3. ALIGNMENT: LEFT, CENTER, RIGHT, JUSTIFY -->
      <div class="flex items-center gap-0.5 bg-slate-200/60 p-0.5 rounded-lg">
        <button
          type="button"
          @click="exec('justifyLeft')"
          class="w-6 h-6 flex items-center justify-center rounded-md text-xs transition"
          :class="activeFormats.justifyLeft ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
          title="Rata Kiri"
        >
          <AlignLeft class="w-3.5 h-3.5" />
        </button>
        <button
          type="button"
          @click="exec('justifyCenter')"
          class="w-6 h-6 flex items-center justify-center rounded-md text-xs transition"
          :class="activeFormats.justifyCenter ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
          title="Rata Tengah (Teks / Persamaan)"
        >
          <AlignCenter class="w-3.5 h-3.5" />
        </button>
        <button
          type="button"
          @click="exec('justifyRight')"
          class="w-6 h-6 flex items-center justify-center rounded-md text-xs transition"
          :class="activeFormats.justifyRight ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
          title="Rata Kanan (Teks Arab / PAI)"
        >
          <AlignRight class="w-3.5 h-3.5" />
        </button>
        <button
          type="button"
          @click="exec('justifyFull')"
          class="w-6 h-6 flex items-center justify-center rounded-md text-xs transition"
          :class="activeFormats.justifyFull ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
          title="Rata Kanan-Kiri (Justify Standar Cetak)"
        >
          <AlignJustify class="w-3.5 h-3.5" />
        </button>
      </div>

      <div class="w-px h-4 bg-slate-300 mx-0.5"></div>

      <!-- 4. SUPERSCRIPT & SUBSCRIPT (PANGKAT & INDEKS MTK/IPA) -->
      <div class="flex items-center gap-0.5">
        <button
          type="button"
          @click="exec('superscript')"
          class="px-2 h-7 flex items-center gap-1 rounded-lg text-xs font-bold transition"
          :class="activeFormats.superscript ? 'bg-indigo-600 text-white shadow-2xs' : 'hover:bg-slate-200 text-indigo-700 bg-indigo-50/60'"
          title="Pangkat / Superscript (x², cm³)"
        >
          <Superscript class="w-3.5 h-3.5" />
          <span class="text-[11px]">X²</span>
        </button>

        <button
          type="button"
          @click="exec('subscript')"
          class="px-2 h-7 flex items-center gap-1 rounded-lg text-xs font-bold transition"
          :class="activeFormats.subscript ? 'bg-indigo-600 text-white shadow-2xs' : 'hover:bg-slate-200 text-indigo-700 bg-indigo-50/60'"
          title="Indeks Bawah / Subscript (H₂O, x₁)"
        >
          <Subscript class="w-3.5 h-3.5" />
          <span class="text-[11px]">X₂</span>
        </button>
      </div>

      <div class="w-px h-4 bg-slate-300 mx-0.5"></div>

      <!-- 5. LISTS: NUMBERING & BULLETS & INDENTATION -->
      <!-- 5.1 NUMBERING DROPDOWN -->
      <div class="relative">
        <button
          type="button"
          @click="toggleNumberingDropdown"
          class="px-2 h-7 flex items-center gap-1 rounded-lg text-xs font-semibold transition"
          :class="activeFormats.insertOrderedList || showNumberingDropdown ? 'bg-emerald-600 text-white shadow-2xs' : 'hover:bg-slate-200 text-slate-700 bg-slate-100'"
          title="Pilihan Format Penomoran (Numbering)"
        >
          <ListOrdered class="w-3.5 h-3.5" />
          <span class="text-[11px] font-bold hidden sm:inline">123</span>
          <ChevronDown class="w-2.5 h-2.5 opacity-70" :class="{ 'rotate-180': showNumberingDropdown }" />
        </button>

        <!-- Numbering Dropdown Menu -->
        <div
          v-if="showNumberingDropdown"
          class="absolute left-0 top-full mt-1.5 w-60 bg-white border border-slate-200 rounded-xl shadow-xl z-40 py-1.5 text-xs text-slate-700 animate-in fade-in zoom-in-95"
        >
          <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 mb-1">
            Gaya Penomoran
          </div>
          <button
            type="button"
            v-for="item in numberingOptions"
            :key="item.id"
            @click="applyNumberingStyle(item)"
            class="w-full px-3 py-1.5 text-left flex items-center justify-between hover:bg-emerald-50 hover:text-emerald-800 transition"
          >
            <span class="font-medium">{{ item.label }}</span>
            <span class="font-mono text-emerald-600 font-bold text-[11px]">{{ item.preview }}</span>
          </button>
        </div>
      </div>

      <!-- 5.2 BULLETS DROPDOWN -->
      <div class="relative">
        <button
          type="button"
          @click="toggleBulletsDropdown"
          class="px-2 h-7 flex items-center gap-1 rounded-lg text-xs font-semibold transition"
          :class="activeFormats.insertUnorderedList || showBulletsDropdown ? 'bg-emerald-600 text-white shadow-2xs' : 'hover:bg-slate-200 text-slate-700 bg-slate-100'"
          title="Pilihan Simbol Butir (Bullets)"
        >
          <List class="w-3.5 h-3.5" />
          <span class="text-[11px] font-bold hidden sm:inline">• Poin</span>
          <ChevronDown class="w-2.5 h-2.5 opacity-70" :class="{ 'rotate-180': showBulletsDropdown }" />
        </button>

        <!-- Bullets Dropdown Menu -->
        <div
          v-if="showBulletsDropdown"
          class="absolute left-0 top-full mt-1.5 w-56 bg-white border border-slate-200 rounded-xl shadow-xl z-40 py-1.5 text-xs text-slate-700 animate-in fade-in zoom-in-95"
        >
          <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 mb-1">
            Gaya Simbol Butir
          </div>
          <button
            type="button"
            v-for="item in bulletOptions"
            :key="item.id"
            @click="applyBulletStyle(item)"
            class="w-full px-3 py-1.5 text-left flex items-center justify-between hover:bg-emerald-50 hover:text-emerald-800 transition"
          >
            <span class="font-medium">{{ item.label }}</span>
            <span class="text-emerald-600 font-bold text-sm">{{ item.symbol }}</span>
          </button>
        </div>
      </div>

      <!-- 5.3 INDENT & OUTDENT (PENGATUR MENJOROK) -->
      <div class="flex items-center gap-0.5 bg-slate-200/60 p-0.5 rounded-lg">
        <button
          type="button"
          @click="exec('outdent')"
          class="w-6 h-6 flex items-center justify-center rounded-md text-xs hover:bg-white text-slate-700 transition"
          title="Kurangi Inden / Geser Kiri (Outdent)"
        >
          <Outdent class="w-3.5 h-3.5" />
        </button>
        <button
          type="button"
          @click="exec('indent')"
          class="w-6 h-6 flex items-center justify-center rounded-md text-xs hover:bg-white text-slate-700 transition"
          title="Tambah Inden / Geser Kanan (Indent)"
        >
          <Indent class="w-3.5 h-3.5" />
        </button>
      </div>

      <div class="w-px h-4 bg-slate-300 mx-0.5"></div>

      <!-- 6. MATH & FORMULA SYMBOLS PALETTE DROPDOWN -->
      <div class="relative">
        <button
          type="button"
          @click="toggleSymbolPalette"
          class="px-2.5 h-7 flex items-center gap-1.5 rounded-lg text-xs font-bold bg-amber-500/10 text-amber-800 hover:bg-amber-500/20 border border-amber-300 transition"
          title="Buka Palet Simbol Matematika & Sains"
        >
          <Sigma class="w-3.5 h-3.5 text-amber-700" />
          <span>Simbol MTK</span>
          <ChevronDown class="w-3 h-3 text-amber-600 transition" :class="{ 'rotate-180': showSymbolPalette }" />
        </button>

        <!-- Backdrop to close on click outside -->
        <div
          v-if="showSymbolPalette"
          class="fixed inset-0 z-40"
          @click="showSymbolPalette = false"
        ></div>

        <!-- PALETTE POPUP DROPDOWN (Studio Palet Simbol MTK) -->
        <div
          v-if="showSymbolPalette"
          class="absolute right-0 top-full mt-2 w-[340px] sm:w-[450px] max-w-[calc(100vw-24px)] bg-white border border-slate-200 rounded-2xl shadow-2xl z-50 p-3 sm:p-4 animate-in fade-in zoom-in-95 text-slate-800"
        >
          <!-- Header -->
          <div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-2.5">
            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
              <Sigma class="w-4 h-4 text-amber-600" />
              <span>Studio Simbol Rumus MTK & Sains</span>
            </span>
            <button
              type="button"
              @click="showSymbolPalette = false"
              class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition"
              title="Tutup Palet"
            >
              <X class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Quick Search Filter -->
          <div class="relative mb-2.5">
            <input
              type="text"
              v-model="symbolSearch"
              placeholder="Cari simbol... (misal: akar, derajat, pi, sigma, x², pecahan)"
              class="w-full pl-8 pr-7 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:bg-white focus:border-amber-400 transition placeholder:text-slate-400"
            />
            <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <button
              v-if="symbolSearch"
              type="button"
              @click="symbolSearch = ''"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5"
            >
              <X class="w-3 h-3" />
            </button>
          </div>

          <!-- Category Tabs (Shown when not searching) -->
          <div v-if="!symbolSearch" class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-[11px] mb-2.5 overflow-x-auto">
            <button
              type="button"
              v-for="cat in symbolCategories"
              :key="cat.id"
              @click="activeCategory = cat.id"
              class="px-2.5 py-1 rounded-lg font-semibold shrink-0 transition text-xs whitespace-nowrap"
              :class="activeCategory === cat.id ? 'bg-white text-amber-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900'"
            >
              {{ cat.name }}
            </button>
          </div>

          <!-- When Search active: Result Title -->
          <div v-else class="text-[11px] font-bold text-amber-800 mb-1.5 px-1 flex items-center justify-between">
            <span>Hasil Pencarian: "{{ symbolSearch }}"</span>
            <span class="text-slate-400 font-normal">{{ currentSymbols.length }} simbol</span>
          </div>

          <!-- 1. Template Rumus View -->
          <div v-if="!symbolSearch && activeCategory === 'templates'" class="space-y-1.5 max-h-60 overflow-y-auto p-0.5">
            <button
              type="button"
              @click="insertFractionTemplate"
              class="w-full p-2 bg-amber-50/70 hover:bg-amber-100/80 text-left border border-amber-200/80 rounded-xl flex items-center justify-between transition group"
            >
              <div>
                <div class="text-xs font-bold text-amber-950">Pecahan Bertingkat Biasa</div>
                <div class="text-[11px] text-amber-700">Format atas/bawah bertingkat (a/b) dengan garis pembagi</div>
              </div>
              <span class="px-2.5 py-1 bg-white border border-amber-300 rounded-lg font-bold text-xs text-amber-800 shadow-2xs group-hover:scale-105 transition">a/b</span>
            </button>

            <button
              type="button"
              @click="insertMixedFractionTemplate"
              class="w-full p-2 bg-amber-50/70 hover:bg-amber-100/80 text-left border border-amber-200/80 rounded-xl flex items-center justify-between transition group"
            >
              <div>
                <div class="text-xs font-bold text-amber-950">Pecahan Campuran</div>
                <div class="text-[11px] text-amber-700">Bilangan bulat dengan pecahan bertingkat (contoh: 2 ½)</div>
              </div>
              <span class="px-2.5 py-1 bg-white border border-amber-300 rounded-lg font-bold text-xs text-amber-800 shadow-2xs group-hover:scale-105 transition">2 ½</span>
            </button>

            <button
              type="button"
              @click="insertRootTemplate"
              class="w-full p-2 bg-amber-50/70 hover:bg-amber-100/80 text-left border border-amber-200/80 rounded-xl flex items-center justify-between transition group"
            >
              <div>
                <div class="text-xs font-bold text-amber-950">Bentuk Akar Bergaris Atas</div>
                <div class="text-[11px] text-amber-700">Akar dengan garis penutup rumus di atas isian (√(b²-4ac))</div>
              </div>
              <span class="px-2.5 py-1 bg-white border border-amber-300 rounded-lg font-bold text-xs text-amber-800 shadow-2xs group-hover:scale-105 transition">√(x)</span>
            </button>

            <button
              type="button"
              @click="insertLinearSystemTemplate"
              class="w-full p-2 bg-amber-50/70 hover:bg-amber-100/80 text-left border border-amber-200/80 rounded-xl flex items-center justify-between transition group"
            >
              <div>
                <div class="text-xs font-bold text-amber-950">Sistem Persamaan Linear (SPLDV)</div>
                <div class="text-[11px] text-amber-700">Persamaan 2 baris berkurung kurawal { ...</div>
              </div>
              <span class="px-2.5 py-1 bg-white border border-amber-300 rounded-lg font-bold text-xs text-amber-800 shadow-2xs group-hover:scale-105 transition">{ SPL</span>
            </button>

            <button
              type="button"
              @click="insertStatsMeanTemplate"
              class="w-full p-2 bg-amber-50/70 hover:bg-amber-100/80 text-left border border-amber-200/80 rounded-xl flex items-center justify-between transition group"
            >
              <div>
                <div class="text-xs font-bold text-amber-950">Rumus Rata-Rata Statistika</div>
                <div class="text-[11px] text-amber-700">x̄ = (∑ fᵢ·xᵢ) / (∑ fᵢ)</div>
              </div>
              <span class="px-2.5 py-1 bg-white border border-amber-300 rounded-lg font-bold text-xs text-amber-800 shadow-2xs group-hover:scale-105 transition">x̄ = ∑/n</span>
            </button>

            <button
              type="button"
              @click="insertMatrixTemplate"
              class="w-full p-2 bg-amber-50/70 hover:bg-amber-100/80 text-left border border-amber-200/80 rounded-xl flex items-center justify-between transition group"
            >
              <div>
                <div class="text-xs font-bold text-amber-950">Matriks Ordo 2x2</div>
                <div class="text-[11px] text-amber-700">Matriks 2 baris 2 kolom dengan kurung siku [ a b ; c d ]</div>
              </div>
              <span class="px-2.5 py-1 bg-white border border-amber-300 rounded-lg font-bold text-xs text-amber-800 shadow-2xs group-hover:scale-105 transition">[ 2x2 ]</span>
            </button>
          </div>

          <!-- 2. Symbol Grid (Normal / Search) -->
          <div v-else>
            <div v-if="currentSymbols.length === 0" class="py-8 text-center text-xs text-slate-400">
              Tidak ada simbol yang cocok dengan "{{ symbolSearch }}".
            </div>
            <div v-else class="grid grid-cols-6 sm:grid-cols-7 gap-1.5 max-h-56 overflow-y-auto p-1">
              <button
                type="button"
                v-for="item in currentSymbols"
                :key="item.symbol"
                @click="insertSymbol(item.insert || item.symbol)"
                class="h-9 rounded-lg border border-slate-200 hover:border-amber-400 hover:bg-amber-50 flex items-center justify-center text-sm font-bold text-slate-800 transition active:scale-95 bg-slate-50/50"
                :title="item.label"
              >
                <span v-html="item.display || item.symbol"></span>
              </button>
            </div>

            <!-- Quick Template Hint in Fractions -->
            <div v-if="activeCategory === 'fractions' && !symbolSearch" class="mt-2 pt-2 border-t border-slate-100">
              <button
                type="button"
                @click="insertFractionTemplate"
                class="w-full py-1.5 px-3 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs rounded-xl border border-amber-200 flex items-center justify-center gap-1.5 transition"
              >
                <span>+ Sisipkan Format Pecahan Bertingkat (a/b)</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 7. TABLE INSERTION -->
      <button
        type="button"
        @click="insertTable"
        class="px-2 h-7 flex items-center gap-1 rounded-lg text-xs font-semibold hover:bg-slate-200 text-slate-700 transition"
        title="Sisipkan Tabel Data / Frekuensi"
      >
        <Table class="w-3.5 h-3.5 text-slate-600" />
        <span class="hidden sm:inline text-[11px]">Tabel</span>
      </button>

      <!-- 8. CLEAR FORMATTING -->
      <button
        type="button"
        @click="exec('removeFormat')"
        class="w-7 h-7 flex items-center justify-center rounded-lg text-xs hover:bg-rose-50 text-slate-500 hover:text-rose-600 transition"
        title="Hapus Pemformatan"
      >
        <Eraser class="w-3.5 h-3.5" />
      </button>

      <!-- 9. HTML SOURCE TOGGLE -->
      <button
        type="button"
        @click="toggleSourceMode"
        class="ml-auto px-2 h-7 flex items-center gap-1 rounded-lg text-[11px] font-mono transition"
        :class="isSourceMode ? 'bg-slate-800 text-white shadow-2xs' : 'hover:bg-slate-200 text-slate-600'"
        title="Lihat / Edit Kode HTML Langsung"
      >
        <Code class="w-3 h-3" />
        <span>{{ isSourceMode ? 'Visual' : '&lt;/&gt;' }}</span>
      </button>
    </div>

    <!-- EDITOR BODY -->
    <div class="relative flex-1 bg-white rounded-b-xl">
      <!-- WYSIWYG Editable Area -->
      <div
        v-show="!isSourceMode"
        ref="editorRef"
        contenteditable="true"
        @click="closeAllDropdowns"
        @input="onEditorInput"
        @blur="onEditorBlur"
        @paste="onEditorPaste"
        @keyup="updateActiveFormats"
        @mouseup="updateActiveFormats"
        class="editable-content p-3.5 sm:p-4 text-xs sm:text-sm text-slate-800 leading-relaxed focus:outline-none min-h-[140px] max-h-[400px] overflow-y-auto rounded-b-xl"
        :data-placeholder="placeholder"
      ></div>

      <!-- Raw HTML Source Mode Textarea -->
      <textarea
        v-if="isSourceMode"
        v-model="sourceContent"
        @input="onSourceInput"
        class="w-full p-3.5 sm:p-4 text-xs font-mono text-slate-800 bg-slate-900 text-emerald-300 leading-relaxed focus:outline-none min-h-[140px] max-h-[400px] resize-y rounded-b-xl"
        placeholder="Kode HTML soal..."
      ></textarea>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, computed, nextTick } from 'vue';
import {
  Bold,
  Italic,
  Underline,
  AlignLeft,
  AlignCenter,
  AlignRight,
  AlignJustify,
  Superscript,
  Subscript,
  List,
  ListOrdered,
  Indent,
  Outdent,
  Table,
  Sigma,
  ChevronDown,
  X,
  Eraser,
  Code,
  Search
} from 'lucide-vue-next';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Tuliskan rumusan butir soal secara jelas, lugas, dan tegas di sini...'
  }
});

const emit = defineEmits(['update:modelValue']);

const editorRef = ref(null);
const isSourceMode = ref(false);
const sourceContent = ref('');
const currentHeading = ref('p');
const showSymbolPalette = ref(false);
const showNumberingDropdown = ref(false);
const showBulletsDropdown = ref(false);
const activeCategory = ref('templates');
const symbolSearch = ref('');

const activeFormats = reactive({
  bold: false,
  italic: false,
  underline: false,
  justifyLeft: false,
  justifyCenter: false,
  justifyRight: false,
  justifyFull: false,
  superscript: false,
  subscript: false,
  insertUnorderedList: false,
  insertOrderedList: false
});

const closeAllDropdowns = () => {
  showNumberingDropdown.value = false;
  showBulletsDropdown.value = false;
  showSymbolPalette.value = false;
  symbolSearch.value = '';
};

const toggleNumberingDropdown = () => {
  showNumberingDropdown.value = !showNumberingDropdown.value;
  showBulletsDropdown.value = false;
  showSymbolPalette.value = false;
};

const toggleBulletsDropdown = () => {
  showBulletsDropdown.value = !showBulletsDropdown.value;
  showNumberingDropdown.value = false;
  showSymbolPalette.value = false;
};

const toggleSymbolPalette = () => {
  showSymbolPalette.value = !showSymbolPalette.value;
  showNumberingDropdown.value = false;
  showBulletsDropdown.value = false;
};

// Numbering Styles Options
const numberingOptions = [
  { id: 'decimal', label: '1. 2. 3. (Angka Biasa)', preview: '1, 2, 3', type: 'decimal' },
  { id: 'paren_num', label: '(1) (2) (3) (Format AKM)', preview: '(1), (2), (3)', customParen: true },
  { id: 'lower_alpha', label: 'a. b. c. (Huruf Kecil)', preview: 'a, b, c', type: 'lower-alpha' },
  { id: 'paren_alpha', label: '(a) (b) (c) (Huruf Kurung)', preview: '(a), (b), (c)', customParenAlpha: true },
  { id: 'upper_alpha', label: 'A. B. C. (Huruf Besar)', preview: 'A, B, C', type: 'upper-alpha' },
  { id: 'upper_roman', label: 'I. II. III. (Romawi Besar)', preview: 'I, II, III', type: 'upper-roman' },
  { id: 'lower_roman', label: 'i. ii. iii. (Romawi Kecil)', preview: 'i, ii, iii', type: 'lower-roman' }
];

// Bullets Styles Options
const bulletOptions = [
  { id: 'disc', label: 'Bulat Penuh (Disc)', symbol: '•', type: 'disc' },
  { id: 'dash', label: 'Garis / Strip Naskah', symbol: '–', customSymbol: '–' },
  { id: 'square', label: 'Kotak Rapi (Square)', symbol: '▪', type: 'square' },
  { id: 'circle', label: 'Lingkaran (Circle)', symbol: '○', type: 'circle' },
  { id: 'check', label: 'Centang / Checklist', symbol: '✓', customSymbol: '✓' },
  { id: 'arrow', label: 'Panah Poin', symbol: '➢', customSymbol: '➢' }
];

// Category symbols for Mathematics & Science
const symbolCategories = [
  { id: 'templates', name: '⭐ Template Rumus' },
  { id: 'arithmetic', name: 'Aljabar & Hitung' },
  { id: 'fractions', name: 'Pecahan & Akar' },
  { id: 'exponents', name: 'Pangkat & Indeks' },
  { id: 'statistics', name: 'Statistika' },
  { id: 'geometry', name: 'Geometri & Sudut' },
  { id: 'relations', name: 'Himpunan & Logika' },
  { id: 'trigonometry', name: 'Trigo & Kalkulus' },
  { id: 'greek', name: 'Yunani & Sains' }
];

const symbolsDatabase = {
  arithmetic: [
    { symbol: '+', label: 'Tambah (+)' },
    { symbol: '−', label: 'Kurang / Minus (−)' },
    { symbol: '×', label: 'Kali (×)' },
    { symbol: '÷', label: 'Bagi (÷)' },
    { symbol: '±', label: 'Plus Minus (±)' },
    { symbol: '∓', label: 'Minus Plus (∓)' },
    { symbol: '·', label: 'Titik Perkalian (·)' },
    { symbol: '=', label: 'Sama Dengan (=)' },
    { symbol: '≠', label: 'Tidak Sama Dengan (≠)' },
    { symbol: '≈', label: 'Mendekati / Kira-kira (≈)' },
    { symbol: '≡', label: 'Identik / Kongruen (≡)' },
    { symbol: '<', label: 'Kurang Dari (<)' },
    { symbol: '>', label: 'Lebih Dari (>)' },
    { symbol: '≤', label: 'Kurang Dari atau Sama Dengan (≤)' },
    { symbol: '≥', label: 'Lebih Dari atau Sama Dengan (≥)' },
    { symbol: '∝', label: 'Sebanding / Proporsional (∝)' },
    { symbol: '%', label: 'Persen (%)' },
    { symbol: '‰', label: 'Permil (‰)' },
    { symbol: '∞', label: 'Tak Terhingga (∞)' },
    { symbol: '|x|', insert: '|x|', label: 'Nilai Mutlak (|x|)' }
  ],
  fractions: [
    { symbol: '½', label: 'Satu Perdua (½)' },
    { symbol: '⅓', label: 'Satu Pertiga (⅓)' },
    { symbol: '¼', label: 'Satu Perempat (¼)' },
    { symbol: '¾', label: 'Tiga Perempat (¾)' },
    { symbol: '⅔', label: 'Dua Pertiga (⅔)' },
    { symbol: '⅛', label: 'Satu Perdelapan (⅛)' },
    { symbol: '⅜', label: 'Tiga Perdelapan (⅜)' },
    { symbol: '⅝', label: 'Lima Perdelapan (⅝)' },
    { symbol: '⅞', label: 'Tujuh Perdelapan (⅞)' },
    { symbol: '√', label: 'Akar Kuadrat (√)' },
    { symbol: '∛', label: 'Akar Pangkat Tiga (∛)' },
    { symbol: '∜', label: 'Akar Pangkat Empat (∜)' }
  ],
  exponents: [
    { symbol: '²', label: 'Pangkat 2 (²)' },
    { symbol: '³', label: 'Pangkat 3 (³)' },
    { symbol: '⁴', label: 'Pangkat 4 (⁴)' },
    { symbol: '⁵', label: 'Pangkat 5 (⁵)' },
    { symbol: 'ⁿ', label: 'Pangkat n (ⁿ)' },
    { symbol: '⁰', label: 'Pangkat 0 (⁰)' },
    { symbol: '⁻¹', label: 'Pangkat minus 1 (⁻¹)' },
    { symbol: '⁺¹', label: 'Pangkat plus 1 (⁺¹)' },
    { symbol: 'x²', label: 'x kuadrat (x²)' },
    { symbol: 'y²', label: 'y kuadrat (y²)' },
    { symbol: 'xⁿ', label: 'x pangkat n (xⁿ)' },
    { symbol: 'x₁', label: 'x indeks 1 (x₁)' },
    { symbol: 'x₂', label: 'x indeks 2 (x₂)' },
    { symbol: 'xₙ', label: 'x indeks n (xₙ)' },
    { symbol: '₀', label: 'Indeks 0 (₀)' },
    { symbol: '₁', label: 'Indeks 1 (₁)' },
    { symbol: '₂', label: 'Indeks 2 (₂)' },
    { symbol: '₃', label: 'Indeks 3 (₃)' },
    { symbol: '₄', label: 'Indeks 4 (₄)' },
    { symbol: 'ᵢ', label: 'Indeks i (ᵢ)' },
    { symbol: 'ⱼ', label: 'Indeks j (ⱼ)' }
  ],
  statistics: [
    { symbol: 'x̄', label: 'Rata-rata x (x̄)' },
    { symbol: 'ȳ', label: 'Rata-rata y (ȳ)' },
    { symbol: '∑', label: 'Sigma / Penjumlahan (∑)' },
    { symbol: '∏', label: 'Produk / Perkalian (∏)' },
    { symbol: 'Me', label: 'Median (Me)' },
    { symbol: 'Mo', label: 'Modus (Mo)' },
    { symbol: 's', label: 'Standar Deviasi Sampel (s)' },
    { symbol: 's²', label: 'Varians Sampel (s²)' },
    { symbol: 'σ', label: 'Standar Deviasi Populasi (σ)' },
    { symbol: 'σ²', label: 'Varians Populasi (σ²)' },
    { symbol: 'μ', label: 'Rata-rata Populasi (μ)' },
    { symbol: 'fᵢ', label: 'Frekuensi ke-i (fᵢ)' },
    { symbol: 'xᵢ', label: 'Nilai data ke-i (xᵢ)' },
    { symbol: 'P(A)', label: 'Peluang Kejadian A' },
    { symbol: 'n(S)', label: 'Banyak Ruang Sampel' },
    { symbol: 'n(A)', label: 'Banyak Titik Sampel' }
  ],
  geometry: [
    { symbol: '°', label: 'Derajat (°)' },
    { symbol: '′', label: 'Menit Sudut (′)' },
    { symbol: '″', label: 'Detik Sudut (″)' },
    { symbol: '∠', label: 'Sudut (∠)' },
    { symbol: '∡', label: 'Sudut Terukur (∡)' },
    { symbol: '△', label: 'Segitiga (△)' },
    { symbol: '□', label: 'Persegi / Segiempat (□)' },
    { symbol: '⊙', label: 'Lingkaran (⊙)' },
    { symbol: '⊥', label: 'Tegak Lurus (⊥)' },
    { symbol: '∥', label: 'Sejajar (∥)' },
    { symbol: '∦', label: 'Tidak Sejajar (∦)' },
    { symbol: '≅', label: 'Kongruen (≅)' },
    { symbol: '∼', label: 'Sebangun (∼)' },
    { symbol: 'π', label: 'Pi 3.14 (π)' },
    { symbol: 'rad', label: 'Radian (rad)' },
    { symbol: 'cm²', label: 'Sentimeter persegi (cm²)' },
    { symbol: 'cm³', label: 'Sentimeter kubik (cm³)' },
    { symbol: 'm²', label: 'Meter persegi (m²)' },
    { symbol: 'm³', label: 'Meter kubik (m³)' },
    { symbol: 'dm³', label: 'Desimeter kubik / Liter (dm³)' }
  ],
  relations: [
    { symbol: '∈', label: 'Anggota Himpunan (∈)' },
    { symbol: '∉', label: 'Bukan Anggota Himpunan (∉)' },
    { symbol: '⊂', label: 'Himpunan Bagian Murni (⊂)' },
    { symbol: '⊃', label: 'Memuat Murni (⊃)' },
    { symbol: '⊆', label: 'Himpunan Bagian atau Sama (⊆)' },
    { symbol: '⊇', label: 'Memuat atau Sama (⊇)' },
    { symbol: '∪', label: 'Gabungan / Union (∪)' },
    { symbol: '∩', label: 'Irisan / Intersection (∩)' },
    { symbol: '∅', label: 'Himpunan Kosong (∅)' },
    { symbol: 'A′', label: 'Komplemen A (A′)' },
    { symbol: '∀', label: 'Untuk Semua / Semesta (∀)' },
    { symbol: '∃', label: 'Terdapat / Ada (∃)' },
    { symbol: '∧', label: 'Konjungsi / Dan (∧)' },
    { symbol: '∨', label: 'Disjungsi / Atau (∨)' },
    { symbol: '~', label: 'Negasi / Ingkaran (~)' },
    { symbol: '¬', label: 'Negasi Not (¬)' },
    { symbol: '⇒', label: 'Implikasi (⇒)' },
    { symbol: '⇔', label: 'Biimplikasi (⇔)' },
    { symbol: '∴', label: 'Kesimpulan / Jadi (∴)' },
    { symbol: '∵', label: 'Karena (∵)' }
  ],
  trigonometry: [
    { symbol: 'sin', label: 'Sinus (sin)' },
    { symbol: 'cos', label: 'Cosinus (cos)' },
    { symbol: 'tan', label: 'Tangen (tan)' },
    { symbol: 'sec', label: 'Secan (sec)' },
    { symbol: 'csc', label: 'Cosecan (csc)' },
    { symbol: 'cot', label: 'Cotangen (cot)' },
    { symbol: 'log', label: 'Logaritma basis 10 (log)' },
    { symbol: 'ln', label: 'Logaritma Natural (ln)' },
    { symbol: 'lim', label: 'Limit (lim)' },
    { symbol: '∫', label: 'Integral (∫)' },
    { symbol: '∬', label: 'Integral Lipat Dua (∬)' },
    { symbol: 'dx', label: 'Diferensial x (dx)' },
    { symbol: 'dy', label: 'Diferensial y (dy)' },
    { symbol: 'dt', label: 'Diferensial t (dt)' },
    { symbol: 'Δ', label: 'Delta Perubahan (Δ)' },
    { symbol: '∂', label: 'Turunan Parsial (∂)' }
  ],
  greek: [
    { symbol: 'α', label: 'Alpha (α)' },
    { symbol: 'β', label: 'Beta (β)' },
    { symbol: 'γ', label: 'Gamma (γ)' },
    { symbol: 'δ', label: 'Delta kecil (δ)' },
    { symbol: 'Δ', label: 'Delta besar (Δ)' },
    { symbol: 'ε', label: 'Epsilon (ε)' },
    { symbol: 'θ', label: 'Theta sudut (θ)' },
    { symbol: 'λ', label: 'Lambda panjang gelombang (λ)' },
    { symbol: 'μ', label: 'Mu / Mikro (μ)' },
    { symbol: 'π', label: 'Pi (π)' },
    { symbol: 'ρ', label: 'Rho / Massa Jenis (ρ)' },
    { symbol: 'σ', label: 'Sigma kecil (σ)' },
    { symbol: 'Σ', label: 'Sigma besar (Σ)' },
    { symbol: 'τ', label: 'Tau / Torsi (τ)' },
    { symbol: 'φ', label: 'Phi (φ)' },
    { symbol: 'ω', label: 'Omega kecil / Kecepatan Sudut (ω)' },
    { symbol: 'Ω', label: 'Omega besar / Hambatan Ohm (Ω)' },
    { symbol: '°C', label: 'Derajat Celcius (°C)' },
    { symbol: '°F', label: 'Derajat Fahrenheit (°F)' },
    { symbol: 'K', label: 'Kelvin (K)' },
    { symbol: '→', label: 'Panah Reaksi (→)' },
    { symbol: '⇄', label: 'Reaksi Bolak Balik (⇄)' },
    { symbol: '⇌', label: 'Kesetimbangan Kimia (⇌)' },
    { symbol: '↑', label: 'Gas Terbentuk (↑)' },
    { symbol: '↓', label: 'Endapan (↓)' }
  ]
};

const currentSymbols = computed(() => {
  if (symbolSearch.value.trim()) {
    const q = symbolSearch.value.toLowerCase().trim();
    const results = [];
    Object.values(symbolsDatabase).forEach(list => {
      list.forEach(item => {
        if (
          item.symbol.toLowerCase().includes(q) ||
          (item.label && item.label.toLowerCase().includes(q))
        ) {
          if (!results.some(r => r.symbol === item.symbol)) {
            results.push(item);
          }
        }
      });
    });
    return results;
  }
  return symbolsDatabase[activeCategory.value] || [];
});

const syncContent = () => {
  if (editorRef.value && editorRef.value.innerHTML !== props.modelValue) {
    editorRef.value.innerHTML = props.modelValue || '';
  }
  sourceContent.value = props.modelValue || '';
};

watch(() => props.modelValue, (newVal) => {
  if (isSourceMode.value) {
    sourceContent.value = newVal || '';
  } else if (editorRef.value && editorRef.value.innerHTML !== newVal) {
    editorRef.value.innerHTML = newVal || '';
  }
});

const exec = (command, value = null) => {
  if (isSourceMode.value) return;
  editorRef.value?.focus();
  document.execCommand(command, false, value);
  updateActiveFormats();
  emitContent();
};

const applyHeading = () => {
  if (isSourceMode.value) return;
  const tag = currentHeading.value;
  exec('formatBlock', `<${tag}>`);
};

const applyNumberingStyle = (item) => {
  exec('insertOrderedList');
  nextTick(() => {
    const sel = window.getSelection();
    if (sel && sel.anchorNode) {
      const node = sel.anchorNode.nodeType === 3 ? sel.anchorNode.parentElement : sel.anchorNode;
      const ol = node.closest('ol');
      if (ol) {
        ol.classList.remove('list-paren-num', 'list-paren-alpha');
        if (item.customParen) {
          ol.classList.add('list-paren-num');
          ol.style.listStyleType = 'none';
        } else if (item.customParenAlpha) {
          ol.classList.add('list-paren-alpha');
          ol.style.listStyleType = 'none';
        } else if (item.type) {
          ol.style.listStyleType = item.type;
        }
      }
    }
    showNumberingDropdown.value = false;
    emitContent();
  });
};

const applyBulletStyle = (item) => {
  exec('insertUnorderedList');
  nextTick(() => {
    const sel = window.getSelection();
    if (sel && sel.anchorNode) {
      const node = sel.anchorNode.nodeType === 3 ? sel.anchorNode.parentElement : sel.anchorNode;
      const ul = node.closest('ul');
      if (ul) {
        ul.classList.remove('list-dash', 'list-check', 'list-arrow');
        if (item.customSymbol === '–') {
          ul.classList.add('list-dash');
          ul.style.listStyleType = `"${item.customSymbol} "`;
        } else if (item.customSymbol === '✓') {
          ul.classList.add('list-check');
          ul.style.listStyleType = `"${item.customSymbol} "`;
        } else if (item.customSymbol === '➢') {
          ul.classList.add('list-arrow');
          ul.style.listStyleType = `"${item.customSymbol} "`;
        } else if (item.type) {
          ul.style.listStyleType = item.type;
        }
      }
    }
    showBulletsDropdown.value = false;
    emitContent();
  });
};

const updateActiveFormats = () => {
  if (isSourceMode.value) return;
  try {
    activeFormats.bold = document.queryCommandState('bold');
    activeFormats.italic = document.queryCommandState('italic');
    activeFormats.underline = document.queryCommandState('underline');
    activeFormats.justifyLeft = document.queryCommandState('justifyLeft');
    activeFormats.justifyCenter = document.queryCommandState('justifyCenter');
    activeFormats.justifyRight = document.queryCommandState('justifyRight');
    activeFormats.justifyFull = document.queryCommandState('justifyFull');
    activeFormats.superscript = document.queryCommandState('superscript');
    activeFormats.subscript = document.queryCommandState('subscript');
    activeFormats.insertUnorderedList = document.queryCommandState('insertUnorderedList');
    activeFormats.insertOrderedList = document.queryCommandState('insertOrderedList');
  } catch {
    // Ignore selection error
  }
};

const emitContent = () => {
  if (isSourceMode.value) {
    emit('update:modelValue', sourceContent.value);
  } else if (editorRef.value) {
    emit('update:modelValue', editorRef.value.innerHTML);
  }
};

const onEditorInput = () => {
  emitContent();
  updateActiveFormats();
};

const onEditorBlur = () => {
  emitContent();
};

// Word Paste Sanitizer & Prefix Stripper
const cleanQuestionNumberPrefix = (text) => {
  if (!text) return '';
  // Strips leading question number prefixes like: "9. ", "9) ", "No. 9. ", "Soal No. 9: ", "9 - "
  return text.replace(/^(\s*(?:no\.?|soal)?\s*\(?\d+\)?[\.\:\-\)]\s*)/i, '');
};

const getFirstTextNode = (node) => {
  if (!node) return null;
  if (node.nodeType === 3 && node.nodeValue && node.nodeValue.trim()) {
    return node;
  }
  for (let i = 0; i < node.childNodes.length; i++) {
    const res = getFirstTextNode(node.childNodes[i]);
    if (res) return res;
  }
  return null;
};

const onEditorPaste = (e) => {
  e.preventDefault();
  const clipboardData = e.clipboardData || window.clipboardData;
  if (!clipboardData) return;

  const htmlData = clipboardData.getData('text/html');
  const textData = clipboardData.getData('text/plain') || '';

  if (htmlData) {
    try {
      const parser = new DOMParser();
      const doc = parser.parseFromString(htmlData, 'text/html');

      // 1. Remove Word list ignore markers & Word bullet spans
      doc.querySelectorAll('[style*="mso-list:Ignore"], span.MsoListBullet').forEach(el => {
        el.remove();
      });

      // 2. Remove negative text-indent and negative margin-left on all elements
      doc.querySelectorAll('*').forEach(el => {
        if (el.style) {
          if (el.style.textIndent && el.style.textIndent.includes('-')) {
            el.style.textIndent = '0px';
          }
          if (el.style.marginLeft && el.style.marginLeft.includes('-')) {
            el.style.marginLeft = '0px';
          }
        }
        if (el.className && typeof el.className === 'string') {
          el.className = el.className.replace(/\bMso\w+\b/g, '').trim();
        }
      });

      // 3. Strip leading question number from first text node
      const firstText = getFirstTextNode(doc.body);
      if (firstText && firstText.nodeValue) {
        firstText.nodeValue = cleanQuestionNumberPrefix(firstText.nodeValue);
      }

      let cleanedHtml = doc.body.innerHTML;
      // Defensive regex to clean any remaining negative text-indent or negative margin in inline styles
      cleanedHtml = cleanedHtml
        .replace(/text-indent\s*:\s*-[^;"]+;?/gi, 'text-indent: 0;')
        .replace(/margin-left\s*:\s*-[^;"]+;?/gi, 'margin-left: 0;')
        .replace(/<!--[\s\S]*?-->/gi, '')
        .replace(/<o:p>[\s\S]*?<\/o:p>/gi, '');

      insertHtml(cleanedHtml);
    } catch {
      // Fallback to textData if parsing fails
      const cleaned = cleanQuestionNumberPrefix(textData);
      insertHtml(cleaned.replace(/\r?\n/g, '<br>'));
    }
  } else {
    // Plain text paste: clean leading question number
    const cleaned = cleanQuestionNumberPrefix(textData);
    const formatted = cleaned
      .split(/\r?\n\r?\n/)
      .map(p => p.trim())
      .filter(Boolean)
      .map(p => `<p>${p.replace(/\r?\n/g, '<br>')}</p>`)
      .join('');
    insertHtml(formatted || cleaned);
  }

  nextTick(() => {
    emitContent();
  });
};

const onSourceInput = () => {
  emit('update:modelValue', sourceContent.value);
};

const toggleSourceMode = () => {
  if (!isSourceMode.value) {
    sourceContent.value = editorRef.value?.innerHTML || '';
    isSourceMode.value = true;
  } else {
    isSourceMode.value = false;
    nextTick(() => {
      if (editorRef.value) {
        editorRef.value.innerHTML = sourceContent.value || '';
      }
    });
  }
};

// Insert HTML or Symbol at cursor position
const insertHtml = (html) => {
  if (isSourceMode.value) {
    sourceContent.value += html;
    emitContent();
    return;
  }

  editorRef.value?.focus();
  const sel = window.getSelection();
  if (sel && sel.getRangeAt && sel.rangeCount) {
    const range = sel.getRangeAt(0);
    range.deleteContents();
    const el = document.createElement('div');
    el.innerHTML = html;
    const frag = document.createDocumentFragment();
    let node;
    let lastNode;
    while ((node = el.firstChild)) {
      lastNode = frag.appendChild(node);
    }
    range.insertNode(frag);
    if (lastNode) {
      range.setStartAfter(lastNode);
      range.collapse(true);
      sel.removeAllRanges();
      sel.addRange(range);
    }
  } else {
    document.execCommand('insertHTML', false, html);
  }
  emitContent();
};

const insertSymbol = (sym) => {
  insertHtml(sym);
  showSymbolPalette.value = false;
};

const insertFractionTemplate = () => {
  const fractionHtml = `<span style="display:inline-flex; flex-direction:column; vertical-align:middle; text-align:center; font-size:0.9em; line-height:1.1; margin:0 3px;"><span style="border-bottom:1px solid currentColor; padding:0 4px; min-width:14px;">a</span><span style="padding:0 4px; min-width:14px;">b</span></span>&nbsp;`;
  insertHtml(fractionHtml);
  showSymbolPalette.value = false;
};

const insertMixedFractionTemplate = () => {
  const mixedHtml = `2<span style="display:inline-flex; flex-direction:column; vertical-align:middle; text-align:center; font-size:0.9em; line-height:1.1; margin:0 3px;"><span style="border-bottom:1px solid currentColor; padding:0 4px; min-width:14px;">1</span><span style="padding:0 4px; min-width:14px;">2</span></span>&nbsp;`;
  insertHtml(mixedHtml);
  showSymbolPalette.value = false;
};

const insertRootTemplate = () => {
  const rootHtml = `<span style="white-space:nowrap; vertical-align:middle;">&radic;<span style="border-top:1px solid currentColor; padding-top:1px; margin-left:1px; display:inline-block;">b&sup2; - 4ac</span></span>&nbsp;`;
  insertHtml(rootHtml);
  showSymbolPalette.value = false;
};

const insertLinearSystemTemplate = () => {
  const spldvHtml = `<span style="display:inline-flex; align-items:center; vertical-align:middle; margin:4px 0;"><span style="font-size:2em; font-weight:300; line-height:1; margin-right:3px;">{</span><span style="display:flex; flex-direction:column; font-size:0.9em; line-height:1.3;"><span>2x + 3y = 8</span><span>x - 2y = 1</span></span></span>&nbsp;`;
  insertHtml(spldvHtml);
  showSymbolPalette.value = false;
};

const insertStatsMeanTemplate = () => {
  const meanHtml = `<span style="white-space:nowrap; vertical-align:middle;">x&#772; = <span style="display:inline-flex; flex-direction:column; vertical-align:middle; text-align:center; font-size:0.85em; line-height:1.1; margin:0 2px;"><span style="border-bottom:1px solid currentColor; padding:0 3px;">&sum; f&#7522;&middot;x&#7522;</span><span style="padding:0 3px;">&sum; f&#7522;</span></span></span>&nbsp;`;
  insertHtml(meanHtml);
  showSymbolPalette.value = false;
};

const insertMatrixTemplate = () => {
  const matrixHtml = `<span style="display:inline-flex; align-items:center; vertical-align:middle; margin:4px 0;"><span style="border-left:2px solid currentColor; border-top:2px solid currentColor; border-bottom:2px solid currentColor; width:4px; height:2.2em; display:inline-block; margin-right:2px;"></span><table style="display:inline-table; border-collapse:collapse; text-align:center; font-size:0.9em; margin:0 2px;"><tr><td style="padding:1px 6px; border:none;">a</td><td style="padding:1px 6px; border:none;">b</td></tr><tr><td style="padding:1px 6px; border:none;">c</td><td style="padding:1px 6px; border:none;">d</td></tr></table><span style="border-right:2px solid currentColor; border-top:2px solid currentColor; border-bottom:2px solid currentColor; width:4px; height:2.2em; display:inline-block; margin-left:2px;"></span></span>&nbsp;`;
  insertHtml(matrixHtml);
  showSymbolPalette.value = false;
};

const insertTable = () => {
  const tableHtml = `
    <table style="border-collapse:collapse; width:100%; margin:10px 0; border:1px solid #cbd5e1; font-size:12px;">
      <thead>
        <tr style="background:#f8fafc;">
          <th style="border:1px solid #cbd5e1; padding:6px; text-align:center;">No</th>
          <th style="border:1px solid #cbd5e1; padding:6px; text-align:left;">Variabel / Kategori</th>
          <th style="border:1px solid #cbd5e1; padding:6px; text-align:center;">Frekuensi (f)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="border:1px solid #cbd5e1; padding:6px; text-align:center;">1</td>
          <td style="border:1px solid #cbd5e1; padding:6px;">Data A</td>
          <td style="border:1px solid #cbd5e1; padding:6px; text-align:center;">10</td>
        </tr>
        <tr>
          <td style="border:1px solid #cbd5e1; padding:6px; text-align:center;">2</td>
          <td style="border:1px solid #cbd5e1; padding:6px;">Data B</td>
          <td style="border:1px solid #cbd5e1; padding:6px; text-align:center;">25</td>
        </tr>
      </tbody>
    </table>
    <p><br></p>
  `;
  insertHtml(tableHtml);
};

onMounted(() => {
  syncContent();
});
</script>

<style scoped>
/* Word & Rich Text Paste Fixes: Neutralize negative text-indent from Word to prevent text cutoff on the left */
.editable-content,
.editable-content * {
  text-indent: 0 !important;
}
.editable-content p,
.editable-content div,
.editable-content span {
  text-indent: 0 !important;
}

.editable-content:empty:before {
  content: attr(data-placeholder);
  color: #94a3b8;
  pointer-events: none;
}
.editable-content table {
  border-collapse: collapse;
  margin: 8px 0;
}
.editable-content th,
.editable-content td {
  border: 1px solid #cbd5e1;
  padding: 4px 8px;
}
.editable-content th {
  background-color: #f8fafc;
}
.editable-content ul,
.editable-content ol {
  padding-left: 20px;
  margin: 6px 0;
}
.editable-content ul li,
.editable-content ol li {
  margin-bottom: 2px;
}

/* Custom Paren AKM Numbering: (1), (2), (3) */
.editable-content ol.list-paren-num {
  counter-reset: item;
  list-style: none !important;
  padding-left: 24px;
}
.editable-content ol.list-paren-num > li {
  counter-increment: item;
  position: relative;
}
.editable-content ol.list-paren-num > li::before {
  content: "(" counter(item) ") ";
  font-weight: 700;
  color: #1e293b;
  display: inline-block;
  margin-left: -24px;
  width: 24px;
}

/* Custom Paren Alpha: (a), (b), (c) */
.editable-content ol.list-paren-alpha {
  counter-reset: alpha-item;
  list-style: none !important;
  padding-left: 24px;
}
.editable-content ol.list-paren-alpha > li {
  counter-increment: alpha-item;
  position: relative;
}
.editable-content ol.list-paren-alpha > li::before {
  content: "(" counter(alpha-item, lower-alpha) ") ";
  font-weight: 700;
  color: #1e293b;
  display: inline-block;
  margin-left: -24px;
  width: 24px;
}

/* Custom Bullet Styles */
.editable-content ul.list-dash {
  list-style-type: '– ';
}
.editable-content ul.list-check {
  list-style-type: '✓ ';
}
.editable-content ul.list-arrow {
  list-style-type: '➢ ';
}
</style>
