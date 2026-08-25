<template>
  <div id="beranda" class="min-h-screen bg-slate-50 font-inter text-slate-800 overflow-x-hidden selection:bg-emerald-500 selection:text-white">
    
    <!-- Navbar (Smart Adaptive: Deep Emerald when top, Clean White when scrolled) -->
    <nav
      class="fixed top-0 inset-x-0 z-50 transition-all duration-300"
      :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-xs border-b border-slate-200/80' : 'bg-[#032218]/90 backdrop-blur-md border-b border-emerald-900/70'"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 sm:h-20">
          <!-- Logo & Title -->
          <a href="#beranda" class="flex items-center gap-3 group cursor-pointer">
            <div
              class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center flex-shrink-0 p-1.5 shadow-2xs transition-all duration-300 group-hover:scale-105"
              :class="scrolled ? 'bg-emerald-50 border border-emerald-200 text-emerald-600' : 'bg-emerald-900/80 border border-emerald-700/60 text-emerald-300 shadow-inner'"
            >
              <img v-if="settings.app_logo && !logoErr" :src="getStorageUrl(settings.app_logo)" @error="logoErr = true" class="w-full h-full object-contain" alt="Logo" />
              <School v-else class="w-5 h-5" />
            </div>
            <div class="flex flex-col">
              <span
                class="font-extrabold text-sm sm:text-base tracking-wider uppercase truncate max-w-[170px] sm:max-w-none transition-colors"
                :class="scrolled ? 'text-slate-900 group-hover:text-emerald-700' : 'text-white group-hover:text-emerald-300'"
              >
                {{ settings.app_name || 'MTs AL - HASANAH' }}
              </span>
              <span class="text-[10px] font-semibold tracking-wider uppercase hidden sm:block" :class="scrolled ? 'text-emerald-700' : 'text-emerald-400'">
                {{ isSchoolHours ? '🟢 Jam Belajar Aktif (07:00 - 15:00)' : '🌙 Di Luar Jam KBM' }}
              </span>
            </div>
          </a>
          
          <!-- Center Menu -->
          <div class="hidden lg:flex space-x-1 xl:space-x-2">
            <a href="#beranda" class="nav-item" :class="scrolled ? 'nav-scrolled' : 'nav-top'">Beranda</a>
            <a href="#profil" class="nav-item" :class="scrolled ? 'nav-scrolled' : 'nav-top'">Profil & Visi Misi</a>
            <a href="#guru" class="nav-item" :class="scrolled ? 'nav-scrolled' : 'nav-top'">Dewan Guru</a>
            <a href="#wali-kelas" class="nav-item" :class="scrolled ? 'nav-scrolled' : 'nav-top'">Wali Kelas</a>
            <a href="#prestasi" class="nav-item" :class="scrolled ? 'nav-scrolled' : 'nav-top'">Prestasi</a>
            <a href="#berita" class="nav-item" :class="scrolled ? 'nav-scrolled' : 'nav-top'">Berita</a>
            <a href="#fasilitas" class="nav-item" :class="scrolled ? 'nav-scrolled' : 'nav-top'">Sarana</a>
            <a href="#galeri" class="nav-item" :class="scrolled ? 'nav-scrolled' : 'nav-top'">Galeri</a>
          </div>

          <!-- Right side: Login & Mobile Toggle -->
          <div class="flex items-center gap-3">
            <RouterLink
              to="/login"
              class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl shadow-md shadow-emerald-600/20 transition-all duration-200 hover:scale-105 active:scale-95 cursor-pointer"
            >
              <LogIn class="w-4 h-4" />
              <span>Masuk Portal</span>
            </RouterLink>
            
            <button
              @click="toggleMobileMenu"
              class="lg:hidden p-2 rounded-lg transition-colors focus:outline-none cursor-pointer"
              :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-emerald-300 hover:text-white hover:bg-emerald-900/60'"
            >
              <MenuIcon v-if="!isMobileMenuOpen" class="w-6 h-6" />
              <XIcon v-else class="w-6 h-6" />
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Menu Dropdown -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-4"
      >
        <div v-if="isMobileMenuOpen" class="lg:hidden absolute top-full left-0 w-full bg-white/95 backdrop-blur-xl shadow-xl border-t border-slate-200 py-4 px-4 flex flex-col space-y-1">
          <RouterLink to="/ppdb" @click="closeMobileMenu" class="mobile-nav-link text-emerald-800 font-bold bg-emerald-50 border border-emerald-200">
            ✨ Pendaftaran Siswa Baru (PPDB Online)
          </RouterLink>
          <a href="#beranda" @click="closeMobileMenu" class="mobile-nav-link">Beranda</a>
          <a href="#profil" @click="closeMobileMenu" class="mobile-nav-link">Profil & Visi Misi</a>
          <a href="#guru" @click="closeMobileMenu" class="mobile-nav-link">Dewan Guru</a>
          <a href="#wali-kelas" @click="closeMobileMenu" class="mobile-nav-link">Wali Kelas</a>
          <a href="#prestasi" @click="closeMobileMenu" class="mobile-nav-link">Prestasi Siswa</a>
          <a href="#berita" @click="closeMobileMenu" class="mobile-nav-link">Berita & Informasi</a>
          <a href="#fasilitas" @click="closeMobileMenu" class="mobile-nav-link">Sarana & Prasarana</a>
          <a href="#galeri" @click="closeMobileMenu" class="mobile-nav-link">Galeri Foto</a>
          
          <div class="pt-2 mt-2 border-t border-slate-100 sm:hidden">
            <RouterLink to="/login" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-bold rounded-xl shadow-md">
              <LogIn class="w-4 h-4" />
              <span>Masuk Portal SIAKAD</span>
            </RouterLink>
          </div>
        </div>
      </Transition>
    </nav>

    <!-- 1. Hero Section (Deep Emerald Dark Luxury with Animated Stats) -->
    <EtherealBeamsHero :settings="settings" :stats="stats" />

    <!-- 2. Profil & Visi Misi Section (Pristine White Slate) -->
    <section id="profil" class="py-20 relative bg-slate-50/60 border-t border-slate-200/80 overflow-hidden font-inter">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-stretch">
          
          <!-- Profil Kepala Sekolah with Interactive 3D Hover -->
          <div class="lg:col-span-5 flex flex-col justify-center">
            <div
              @mousemove="handleCardTilt"
              @mouseleave="resetCardTilt"
              class="interactive-tilt-card bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-emerald-300 hover:shadow-xl transition-all duration-300"
            >
              <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>
              
              <div class="flex flex-col sm:flex-row sm:items-center gap-5 mb-5">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden border-2 border-emerald-100 shadow-sm flex-shrink-0 bg-emerald-50 relative group-hover:scale-105 transition-transform duration-300">
                  <img v-if="settings.principal_photo" :src="getStorageUrl(settings.principal_photo)" alt="Kepala Sekolah" class="w-full h-full object-cover relative z-10" />
                  <div v-else class="absolute inset-0 bg-emerald-600 flex items-center justify-center text-white text-2xl font-bold font-lexend z-0">KS</div>
                </div>
                <div class="space-y-1">
                  <h3 class="text-xl font-bold text-slate-900 tracking-tight group-hover:text-emerald-700 transition-colors">{{ settings.principal_name || 'Dr. H. Ahmad Fauzi, M.Pd.I.' }}</h3>
                  <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">
                    Kepala Madrasah
                  </div>
                  <p class="text-xs text-slate-500 italic mt-1 leading-relaxed">"{{ settings.principal_message || 'Mendidik dengan hati, membentuk generasi islami yang unggul dan melek teknologi.' }}"</p>
                </div>
              </div>
              <p class="text-slate-600 leading-relaxed text-xs sm:text-sm whitespace-pre-line border-t border-slate-100 pt-4">
                {{ settings.principal_description || 'Selamat datang di website resmi MTs Al - Hasanah. Sebagai institusi pendidikan, kami berkomitmen untuk tidak hanya fokus pada pencapaian akademik, tetapi juga pembangunan karakter islami dan kompetensi era digital.' }}
              </p>
            </div>
          </div>

          <!-- Visi & Misi -->
          <div class="lg:col-span-7 flex flex-col justify-center space-y-6">
            <div>
              <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider mb-2">
                <Compass class="w-3.5 h-3.5 text-emerald-600" />
                <span>Pedoman Pendidikan</span>
              </div>
              <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Visi & Misi Madrasah</h2>
            </div>
            
            <div class="space-y-4">
              <!-- Visi -->
              <div
                @mousemove="handleCardTilt"
                @mouseleave="resetCardTilt"
                class="interactive-tilt-card bg-white p-6 rounded-2xl border border-slate-200/80 hover:border-emerald-300 hover:shadow-md transition-all shadow-xs flex items-start gap-4 group cursor-pointer"
              >
                <div class="w-11 h-11 bg-emerald-50 text-emerald-700 border border-emerald-200/80 rounded-xl flex items-center justify-center flex-shrink-0 shadow-2xs group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                  <Compass class="w-5 h-5" />
                </div>
                <div class="space-y-1">
                  <h4 class="text-base font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">Visi Kami</h4>
                  <p class="text-slate-600 leading-relaxed text-xs sm:text-sm whitespace-pre-line">
                    {{ settings.school_vision || 'Menjadi lembaga pendidikan madrasah unggul yang menghasilkan lulusan berakhlak mulia, berprestasi akademik dan non-akademik, serta berwawasan global di era digital.' }}
                  </p>
                </div>
              </div>

              <!-- Misi -->
              <div
                @mousemove="handleCardTilt"
                @mouseleave="resetCardTilt"
                class="interactive-tilt-card bg-white p-6 rounded-2xl border border-slate-200/80 hover:border-emerald-300 hover:shadow-md transition-all shadow-xs flex items-start gap-4 group cursor-pointer"
              >
                <div class="w-11 h-11 bg-emerald-50 text-emerald-700 border border-emerald-200/80 rounded-xl flex items-center justify-center flex-shrink-0 shadow-2xs group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                  <Target class="w-5 h-5" />
                </div>
                <div class="space-y-1 w-full">
                  <h4 class="text-base font-bold text-slate-900 mb-2 group-hover:text-emerald-700 transition-colors">Misi Kami</h4>
                  <ul class="space-y-2.5" v-if="formattedMissions.length > 0">
                    <li v-for="(mission, index) in formattedMissions" :key="index" class="flex items-start gap-2.5 text-xs sm:text-sm text-slate-600">
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 mt-2 flex-shrink-0"></span>
                      <span class="leading-relaxed">{{ mission }}</span>
                    </li>
                  </ul>
                  <ul v-else class="space-y-2.5 text-xs sm:text-sm text-slate-600">
                    <li class="flex items-start gap-2.5">
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 mt-2 flex-shrink-0"></span>
                      <span class="leading-relaxed">Menyelenggarakan proses pembelajaran inovatif, interaktif, dan berbasis nilai-nilai keislaman.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 mt-2 flex-shrink-0"></span>
                      <span class="leading-relaxed">Membekali peserta didik dengan pemanfaatan teknologi digital yang produktif dan berakhlak.</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 3. Dewan Guru Section with Live Interactive Search -->
    <section id="guru" class="py-20 bg-slate-50/80 relative border-t border-slate-200/80 font-inter">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
          <div class="space-y-2">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">
              <UserCheck class="w-3.5 h-3.5 text-emerald-600" />
              <span>Tenaga Pendidik</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Profil Dewan Guru</h2>
            <p class="text-slate-600 text-xs sm:text-sm font-normal">Mengenal lebih dekat para pendidik berdedikasi yang membimbing siswa-siswi menjadi generasi berakhlak dan berprestasi.</p>
          </div>

          <!-- Interactive Search Input -->
          <div class="relative w-full md:w-72">
            <input
              v-model="teacherSearchQuery"
              type="text"
              placeholder="Cari nama guru atau mapel..."
              class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 pl-10 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all shadow-xs"
            />
            <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          </div>
        </div>

        <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
          <div v-for="i in 8" :key="i" class="animate-pulse rounded-2xl border border-slate-200/80 bg-white p-5 h-48"></div>
        </div>
        <div v-else-if="filteredTeachers.length === 0" class="text-center py-16 bg-white rounded-2xl border border-slate-200/80">
          <p class="text-slate-400 text-xs font-normal">Guru tidak ditemukan dengan kata kunci pencarian tersebut.</p>
        </div>
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
          <div
            v-for="(teacher, tIndex) in filteredTeachers"
            :key="teacher.id"
            @click="openLightbox(teacher, 'teacher', tIndex, filteredTeachers)"
            @mousemove="handleCardTilt"
            @mouseleave="resetCardTilt"
            class="interactive-tilt-card group bg-white rounded-2xl border border-slate-200/80 hover:border-emerald-300 hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col items-center p-5 text-center cursor-pointer relative"
          >
            <div class="relative mb-4">
              <img v-if="teacher.photo_url" :src="getStorageUrl(teacher.photo_url)" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-emerald-100 shadow-2xs group-hover:scale-105 group-hover:border-emerald-300 transition-all duration-300" alt="Guru" />
              <div v-else class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-emerald-50 border-2 border-emerald-100/80 shadow-2xs flex items-center justify-center text-emerald-700 text-2xl font-extrabold">
                {{ (teacher.full_name || 'G').charAt(0) }}
              </div>
              <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] shadow-xs">
                <Check class="w-3 h-3" />
              </span>
            </div>
            
            <h3 class="font-bold text-slate-900 text-sm group-hover:text-emerald-700 transition-colors line-clamp-2 leading-tight mb-1">{{ teacher.full_name }}</h3>
            <p class="text-xs text-slate-500 font-normal mb-3">{{ teacher.position || 'Guru Pengampu' }}</p>
            
            <div class="mt-auto flex flex-wrap justify-center gap-1">
              <span v-for="subject in teacher.subjects" :key="subject.id" class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200/70 rounded-md text-[10px] font-semibold whitespace-nowrap">
                {{ subject.name }}
              </span>
              <span v-if="!teacher.subjects || teacher.subjects.length === 0" class="px-2 py-0.5 bg-slate-50 text-slate-400 rounded-md text-[10px] font-medium">
                Pendidik
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 5. Wali Kelas Section (White Canvas) -->
    <section id="wali-kelas" class="py-20 bg-white relative border-t border-slate-200/80 font-inter">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
          <div class="space-y-1.5">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">
              <Building2 class="w-3.5 h-3.5 text-emerald-600" />
              <span>Pembimbing Rombel</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Profil Wali Kelas</h2>
            <p class="text-slate-600 text-xs sm:text-sm font-normal">Penanggung jawab yang mendampingi perkembangan akademik dan karakter siswa di setiap rombongan belajar.</p>
          </div>
        </div>

        <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
          <div v-for="i in 4" :key="i" class="animate-pulse rounded-2xl p-4 flex items-center gap-3.5 border border-slate-200/80 bg-slate-50">
            <div class="w-14 h-14 bg-slate-200 rounded-xl"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-slate-200 rounded w-1/3"></div>
              <div class="h-3 bg-slate-100 rounded w-3/4"></div>
            </div>
          </div>
        </div>
        <div v-else-if="classrooms.length === 0" class="text-center py-12 bg-slate-50/60 rounded-2xl border border-slate-200/80">
          <p class="text-slate-500 text-xs font-normal">Data wali kelas belum tersedia.</p>
        </div>
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
          <div
            v-for="(classroom, cIdx) in classrooms"
            :key="classroom.id"
            @click="classroom.homeroom_teacher && openLightbox(classroom.homeroom_teacher, 'teacher', cIdx, classrooms.map(c => c.homeroom_teacher).filter(Boolean))"
            @mousemove="handleCardTilt"
            @mouseleave="resetCardTilt"
            class="interactive-tilt-card group bg-white rounded-2xl border border-slate-200/80 hover:border-emerald-300 hover:shadow-lg transition-all duration-300 p-4 flex items-center gap-3.5 cursor-pointer"
          >
            <div class="relative flex-shrink-0">
              <img v-if="classroom.homeroom_teacher && classroom.homeroom_teacher.photo_url" :src="getStorageUrl(classroom.homeroom_teacher.photo_url)" class="w-14 h-14 rounded-xl object-cover border border-emerald-100 shadow-2xs group-hover:scale-105 transition-transform" alt="Guru" />
              <div v-else class="w-14 h-14 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700 text-lg font-bold">
                {{ (classroom.homeroom_teacher?.full_name || 'G').charAt(0) }}
              </div>
            </div>
            
            <div class="flex-1 min-w-0 space-y-1">
              <div class="inline-flex items-center px-2 py-0.5 bg-emerald-50 border border-emerald-200/70 text-emerald-800 text-[10px] font-bold rounded-md uppercase tracking-wider">
                Kelas {{ classroom.name }}
              </div>
              <h3 class="font-bold text-slate-900 text-xs sm:text-sm group-hover:text-emerald-700 transition-colors line-clamp-1 leading-snug">
                {{ classroom.homeroom_teacher ? classroom.homeroom_teacher.full_name : 'Belum Ditentukan' }}
              </h3>
              <p class="text-[11px] text-slate-500 font-normal">Wali Kelas</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 6. Prestasi Siswa Section with Filter Tabs -->
    <section id="prestasi" class="py-20 bg-slate-50/80 relative border-t border-slate-200/80 font-inter">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
          <div class="space-y-1.5">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">
              <Trophy class="w-3.5 h-3.5 text-emerald-600" />
              <span>Kebanggaan Madrasah</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Prestasi & Penghargaan</h2>
            <p class="text-slate-600 text-xs sm:text-sm font-normal">Deretan prestasi gemilang yang ditorehkan oleh siswa-siswi dan pendidik terbaik.</p>
          </div>
        </div>

        <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <div v-for="i in 4" :key="i" class="rounded-2xl border border-slate-200/80 bg-white p-5 h-44 animate-pulse"></div>
        </div>
        <div v-else-if="achievements.length === 0" class="text-center py-16 bg-white rounded-2xl border border-slate-200/80">
          <p class="text-slate-500 text-xs font-normal">Belum ada data prestasi.</p>
        </div>
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <div
            v-for="(achievement, aIdx) in achievements"
            :key="achievement.id"
            @click="openLightbox(achievement, 'achievement', aIdx, achievements)"
            @mousemove="handleCardTilt"
            @mouseleave="resetCardTilt"
            class="interactive-tilt-card group bg-white rounded-2xl border border-slate-200/80 hover:border-emerald-300 hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col p-5 cursor-pointer"
          >
            <div class="flex items-center gap-3.5 mb-3">
              <div class="w-12 h-12 bg-emerald-50 border border-emerald-200/80 rounded-xl flex items-center justify-center text-emerald-700 shadow-2xs flex-shrink-0 relative overflow-hidden group-hover:scale-105 transition-transform">
                <img v-if="achievement.photo_url" :src="getStorageUrl(achievement.photo_url)" class="absolute inset-0 w-full h-full object-cover" />
                <Trophy v-else class="w-5 h-5 text-emerald-600" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider mb-0.5">{{ achievement.level || 'Penghargaan' }}</div>
                <div class="font-bold text-slate-900 text-xs truncate" :title="achievement.student_name">{{ achievement.student_name }}</div>
              </div>
            </div>
            
            <h3 class="font-bold text-slate-800 text-sm group-hover:text-emerald-700 transition-colors line-clamp-2 leading-snug mb-2">
              {{ achievement.title }}
            </h3>
            
            <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-2.5">
              <span class="text-[11px] font-medium text-slate-400">Tahun {{ achievement.year }}</span>
              <span class="text-emerald-600 group-hover:translate-x-1 transition-transform">
                <ArrowRight class="w-3.5 h-3.5" />
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 7. Facilities Section (Interactive Gallery & Card Flip) -->
    <section id="fasilitas" class="py-20 bg-white relative border-t border-slate-200/80 font-inter">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
          <div class="space-y-1.5">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">
              <Warehouse class="w-3.5 h-3.5 text-emerald-600" />
              <span>Fasilitas Madrasah</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Sarana & Prasarana</h2>
            <p class="text-slate-600 text-xs sm:text-sm font-normal">Fasilitas unggulan modern untuk menunjang kenyamanan kegiatan belajar mengajar.</p>
          </div>
        </div>

        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 3" :key="'f-load-'+i" class="rounded-2xl border border-slate-200/80 p-4 h-[320px] animate-pulse bg-slate-50"></div>
        </div>
        <div v-else-if="facilities.length === 0" class="text-center py-16 bg-slate-50/60 rounded-2xl border border-slate-200/80">
          <p class="text-slate-500 text-xs font-normal">Data sarana prasarana belum tersedia.</p>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="(item, fIdx) in facilities"
            :key="item.id"
            @click="openLightbox(item, 'facility', fIdx, facilities)"
            @mousemove="handleCardTilt"
            @mouseleave="resetCardTilt"
            class="interactive-tilt-card group bg-white rounded-3xl border border-slate-200/80 hover:border-emerald-300 hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col cursor-pointer"
          >
            <div class="h-52 bg-slate-100 relative overflow-hidden">
              <img v-if="item.image" :src="getStorageUrl(item.image)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Fasilitas" />
              <div v-else class="w-full h-full bg-emerald-50/50 flex items-center justify-center">
                <Warehouse class="w-10 h-10 text-emerald-400 opacity-60" />
              </div>
              <div class="absolute bottom-3 right-3 px-2.5 py-1 bg-slate-900/70 backdrop-blur-md rounded-lg text-[10px] font-bold text-white uppercase tracking-wider">
                Klik untuk Memperbesar 🔍
              </div>
            </div>
            <div class="p-6 flex flex-col flex-grow space-y-2">
              <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">{{ item.name }}</h3>
              <p class="text-slate-600 text-xs line-clamp-2 leading-relaxed font-normal">{{ item.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 8. Galeri Dokumentasi with Category Filter Tabs -->
    <section id="galeri" class="py-20 bg-slate-50/80 relative border-t border-slate-200/80 font-inter">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
          <div class="space-y-1.5">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">
              <ImageIcon class="w-3.5 h-3.5 text-emerald-600" />
              <span>Dokumentasi Kegiatan</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Galeri Madrasah</h2>
            <p class="text-slate-600 text-xs sm:text-sm font-normal">Momen-momen berharga dari berbagai aktivitas akademik, religi, dan ekstrakurikuler.</p>
          </div>

          <!-- Interactive Category Filter Tabs -->
          <div class="flex items-center gap-2 overflow-x-auto pb-1 max-w-full">
            <button
              v-for="cat in galleryCategories"
              :key="cat"
              @click="selectedGalleryCategory = cat"
              :class="[
                selectedGalleryCategory === cat ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200',
                'px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all duration-200 cursor-pointer active:scale-95'
              ]"
            >
              {{ cat }}
            </button>
          </div>
        </div>

        <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
          <div v-for="i in 8" :key="'g-load-'+i" class="h-44 rounded-2xl bg-slate-200 animate-pulse"></div>
        </div>
        <div v-else-if="filteredGalleries.length === 0" class="text-center py-16 bg-white rounded-2xl border border-slate-200/80">
          <p class="text-slate-500 text-xs font-normal">Belum ada foto pada kategori ini.</p>
        </div>
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
          <div
            v-for="(item, gIdx) in filteredGalleries"
            :key="item.id"
            @click="openLightbox(item, 'gallery', gIdx, filteredGalleries)"
            class="group relative h-48 sm:h-56 rounded-2xl overflow-hidden shadow-2xs border border-slate-200/80 cursor-pointer transform hover:-translate-y-1.5 transition-all duration-300"
          >
            <img :src="getStorageUrl(item.image)" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Galeri" />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
              <p class="text-white text-xs font-bold truncate">{{ item.title }}</p>
              <p class="text-emerald-300 text-[10px]">{{ item.category || 'Aktivitas' }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 9. Lokasi & Kontak Google Maps (White Canvas) -->
    <section id="lokasi" class="py-20 bg-slate-50/60 text-slate-900 border-t border-slate-200/80 relative overflow-hidden font-inter">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-12">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto space-y-2.5">
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">
            <MapPin class="w-3.5 h-3.5 text-emerald-600" />
            <span>Lokasi & Kontak Resmi</span>
          </div>
          <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Kunjungi Madrasah Kami</h2>
          <p class="text-slate-600 max-w-2xl mx-auto text-sm font-normal">Temukan lokasi resmi madrasah kami melalui navigasi Google Maps interaktif di bawah ini.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
          <!-- Left: Contact Details Card with 3D Tilt -->
          <div
            @mousemove="handleCardTilt"
            @mouseleave="resetCardTilt"
            class="interactive-tilt-card lg:col-span-5 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-6 hover:border-emerald-300 transition-all duration-300"
          >
            <div class="space-y-6">
              <!-- School Header -->
              <div class="flex items-center gap-3.5 pb-4 border-b border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200/70 flex items-center justify-center text-emerald-700 flex-shrink-0 shadow-2xs">
                  <Building class="w-6 h-6" />
                </div>
                <div>
                  <h3 class="text-lg font-bold text-slate-900 uppercase tracking-tight">{{ settings.app_name || 'MTs AL - HASANAH' }}</h3>
                  <p class="text-xs text-emerald-700 font-medium">{{ settings.app_tagline || 'Sistem Informasi Manajemen Madrasah' }}</p>
                </div>
              </div>

              <!-- Details List -->
              <div class="space-y-4 text-sm">
                <!-- Address -->
                <div class="flex items-start gap-3.5">
                  <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0 mt-0.5">
                    <MapPin class="w-4 h-4" />
                  </div>
                  <div>
                    <span class="text-[11px] font-semibold uppercase text-slate-400 block tracking-wider">Alamat Resmi</span>
                    <p class="text-slate-800 font-medium leading-relaxed text-xs sm:text-sm">{{ settings.school_address || 'Jl. Raya Ciomas No. 123, Kabupaten Bogor' }}</p>
                  </div>
                </div>

                <!-- Phone -->
                <div class="flex items-start gap-3.5">
                  <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0 mt-0.5">
                    <Phone class="w-4 h-4" />
                  </div>
                  <div>
                    <span class="text-[11px] font-semibold uppercase text-slate-400 block tracking-wider">No. Telepon / WhatsApp</span>
                    <p class="text-slate-800 font-bold font-mono text-xs sm:text-sm">{{ settings.school_phone || '(0251) 1234567' }}</p>
                  </div>
                </div>

                <!-- Email -->
                <div class="flex items-start gap-3.5">
                  <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0 mt-0.5">
                    <Mail class="w-4 h-4" />
                  </div>
                  <div>
                    <span class="text-[11px] font-semibold uppercase text-slate-400 block tracking-wider">Email Resmi</span>
                    <p class="text-slate-800 font-semibold font-mono text-xs sm:text-sm">{{ settings.school_email || 'info@sekolahdigital.sch.id' }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Direct Directions Button (Shadcn Emerald) -->
            <div class="pt-2">
              <a
                :href="mapDirectionsUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="w-full py-3.5 px-5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 transition-all hover:scale-102 active:scale-98 cursor-pointer"
              >
                <ExternalLink class="w-4 h-4" />
                <span>Petunjuk Arah (Buka di Google Maps)</span>
              </a>
            </div>
          </div>

          <!-- Right: Interactive Google Maps Iframe -->
          <div class="lg:col-span-7 rounded-3xl overflow-hidden min-h-[380px] shadow-xs border border-slate-200/80 relative bg-slate-100">
            <iframe
              v-if="cleanMapsUrl"
              :src="cleanMapsUrl"
              class="w-full h-full min-h-[380px] border-0"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
            <div v-else class="w-full h-full min-h-[380px] flex flex-col items-center justify-center p-8 text-center bg-white space-y-2">
              <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                <MapPin class="w-6 h-6" />
              </div>
              <h4 class="font-bold text-slate-800 text-sm">Peta Lokasi Google Maps</h4>
              <p class="text-xs text-slate-500 max-w-sm">Admin belum mengatur Link Embed Google Maps di Pengaturan Aplikasi.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 11. Deep Emerald Footer -->
    <footer class="relative bg-gradient-to-b from-[#032218] via-[#052e20] to-[#02150f] text-emerald-200 py-16 border-t border-emerald-900/70 overflow-hidden font-inter">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-10">
        <div class="flex flex-col md:flex-row justify-between items-center gap-8">
          <div class="flex items-center gap-4 group cursor-pointer">
            <div class="w-13 h-13 flex items-center justify-center flex-shrink-0 bg-emerald-900/80 rounded-2xl border border-emerald-700/60 shadow-lg p-2 group-hover:scale-105 transition-transform duration-300">
              <img v-if="settings.app_logo && !logoErr" :src="getStorageUrl(settings.app_logo)" @error="logoErr = true" class="w-full h-full object-contain" alt="Logo" />
              <School v-else class="w-7 h-7 text-emerald-400" />
            </div>
            <div>
              <h3 class="font-bold text-xl text-white uppercase tracking-wider mb-0.5 group-hover:text-emerald-300 transition-colors">{{ settings.app_name || 'MTs AL - HASANAH' }}</h3>
              <p class="text-xs text-emerald-300/80">{{ settings.app_tagline || 'Sistem Informasi Manajemen Madrasah Terpadu' }}</p>
            </div>
          </div>
          <div class="flex flex-col items-center md:items-end gap-2">
            <div class="text-xs text-emerald-400/80 font-normal">
              &copy; {{ new Date().getFullYear() }} {{ settings.app_name || 'MTs Al - Hasanah' }}. Hak Cipta Dilindungi.
            </div>
          </div>
        </div>
      </div>
    </footer>

    <!-- Interactive Floating Quick Actions (WhatsApp & Scroll Top) -->
    <div class="fixed bottom-6 right-6 z-40 flex flex-col items-center gap-3">
      <!-- WhatsApp Floating Action -->
      <a
        v-if="settings.school_phone"
        :href="`https://wa.me/${cleanPhoneForWa(settings.school_phone)}?text=Halo%20Admin%20SIAKAD%20MTs%20Al-Hasanah,%20saya%20ingin%20bertanya%20informasi.`"
        target="_blank"
        rel="noopener noreferrer"
        class="w-12 h-12 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center shadow-xl shadow-emerald-600/40 hover:scale-110 active:scale-95 transition-all duration-200 group cursor-pointer"
        title="Hubungi Kami via WhatsApp"
      >
        <Phone class="w-5 h-5" />
      </a>

      <!-- Back to Top Button -->
      <Transition name="fade">
        <button
          v-if="scrolled"
          @click="scrollToTop"
          class="w-11 h-11 rounded-full bg-slate-900/90 hover:bg-slate-900 text-white backdrop-blur-md flex items-center justify-center shadow-lg border border-slate-700/60 hover:scale-110 active:scale-95 transition-all duration-200 cursor-pointer"
          title="Kembali ke Atas"
        >
          <ArrowRight class="w-4 h-4 -rotate-90" />
        </button>
      </Transition>
    </div>

    <!-- Interactive Lightbox Modal with Next / Prev Navigation -->
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="activeLightbox" class="fixed inset-0 z-[100] bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-4 sm:p-6" @click.self="closeLightbox">
        <div class="relative w-full max-w-4xl bg-white border border-slate-200 rounded-3xl overflow-hidden flex flex-col md:flex-row shadow-2xl animate-scale-up">
          
          <!-- Close Button -->
          <button @click="closeLightbox" class="absolute top-4 right-4 z-10 w-9 h-9 bg-slate-900/70 hover:bg-slate-900 text-white rounded-full flex items-center justify-center backdrop-blur-md transition-colors cursor-pointer">
            <XIcon class="w-5 h-5" />
          </button>

          <!-- Nav Prev & Next Buttons -->
          <button
            v-if="lightboxList.length > 1"
            @click="prevLightboxItem"
            class="absolute left-4 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-slate-900/60 hover:bg-slate-900 text-white flex items-center justify-center backdrop-blur-md transition-all cursor-pointer"
          >
            <ChevronRight class="w-5 h-5 rotate-180" />
          </button>
          <button
            v-if="lightboxList.length > 1"
            @click="nextLightboxItem"
            class="absolute right-4 md:right-[35%] top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-slate-900/60 hover:bg-slate-900 text-white flex items-center justify-center backdrop-blur-md transition-all cursor-pointer"
          >
            <ChevronRight class="w-5 h-5" />
          </button>

          <div class="md:w-2/3 bg-slate-950 flex items-center justify-center min-h-[280px] md:min-h-[460px] relative">
            <template v-if="activeLightbox.type === 'teacher'">
              <img v-if="activeLightbox.photo_url" :src="getStorageUrl(activeLightbox.photo_url)" class="w-full h-full object-contain max-h-[70vh] md:max-h-[85vh] bg-slate-950" />
              <div v-else class="w-full h-full flex flex-col items-center justify-center text-emerald-400 gap-4 bg-slate-950">
                <div class="w-28 h-28 rounded-2xl bg-emerald-900/80 flex items-center justify-center text-emerald-300 text-4xl font-extrabold">
                  {{ (activeLightbox?.full_name || 'G').charAt(0) }}
                </div>
              </div>
            </template>
            <template v-else-if="activeLightbox.type === 'achievement'">
              <img v-if="activeLightbox.photo_url" :src="getStorageUrl(activeLightbox.photo_url)" class="w-full h-full object-contain max-h-[70vh] md:max-h-[85vh]" />
              <div v-else class="w-full h-full flex flex-col items-center justify-center text-emerald-400 gap-4 bg-slate-950">
                <Trophy class="w-20 h-20 text-emerald-400" />
              </div>
            </template>
            <template v-else>
              <img v-if="activeLightbox.image" :src="getStorageUrl(activeLightbox.image)" class="w-full h-full object-contain max-h-[70vh] md:max-h-[85vh]" />
              <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-500 gap-4 bg-slate-950">
                <ImageIcon class="w-16 h-16 opacity-30 text-slate-400" />
                <span class="text-xs text-slate-400">Tidak ada gambar</span>
              </div>
            </template>
          </div>

          <div class="md:w-1/3 p-6 md:p-8 flex flex-col max-h-[50vh] md:max-h-[85vh] overflow-y-auto custom-scrollbar text-slate-900 bg-white">
            <template v-if="activeLightbox.type === 'teacher'">
              <div class="text-xs font-bold text-emerald-700 mb-2 uppercase tracking-wider">Profil Tenaga Pendidik</div>
              <h3 class="text-xl font-bold text-slate-900 mb-2">{{ activeLightbox.full_name }}</h3>
              <div class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-medium mb-5">{{ activeLightbox.position || 'Guru Pengampu' }}</div>
              
              <div class="space-y-4">
                <div>
                  <div class="text-xs font-bold text-slate-400 uppercase mb-1">Mata Pelajaran</div>
                  <div class="flex flex-wrap gap-1.5">
                    <span v-for="subject in activeLightbox.subjects" :key="subject.id" class="px-2.5 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-xs font-medium">
                      {{ subject.name }}
                    </span>
                    <span v-if="!activeLightbox.subjects || activeLightbox.subjects.length === 0" class="text-xs text-slate-400">Pendidik Umum</span>
                  </div>
                </div>
                
                <div v-if="activeLightbox.gender">
                  <div class="text-xs font-bold text-slate-400 uppercase mb-1">Jenis Kelamin</div>
                  <div class="text-xs font-medium text-slate-700">{{ activeLightbox.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                </div>
              </div>
            </template>
            <template v-else-if="activeLightbox.type === 'achievement'">
              <div class="text-xs font-bold text-emerald-700 mb-2 uppercase tracking-wider">Penghargaan {{ activeLightbox.level }}</div>
              <h3 class="text-xl font-bold text-slate-900 mb-2 leading-tight">{{ activeLightbox.title }}</h3>
              
              <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-xs font-bold mb-5">
                <Trophy class="w-3.5 h-3.5 text-emerald-600" />
                <span>{{ activeLightbox.student_name }}</span>
              </div>

              <div class="space-y-4">
                <div>
                  <div class="text-xs font-bold text-slate-400 uppercase mb-1">Tahun Prestasi</div>
                  <div class="text-xs font-medium text-slate-700">{{ activeLightbox.year }}</div>
                </div>
                <div v-if="activeLightbox.description">
                  <div class="text-xs font-bold text-slate-400 uppercase mb-2">Detail</div>
                  <div class="text-xs text-slate-600 leading-relaxed">
                    <p class="whitespace-pre-line">{{ activeLightbox.description }}</p>
                  </div>
                </div>
              </div>
            </template>
            <template v-else>
              <div class="text-xs font-bold text-emerald-700 mb-2">{{ activeLightbox.created_at ? formatDate(activeLightbox.created_at) : (activeLightbox.category || 'Dokumentasi') }}</div>
              <h3 class="text-xl font-bold text-slate-900 mb-4">{{ activeLightbox.name || activeLightbox.title }}</h3>
              <div class="text-xs text-slate-600 leading-relaxed">
                <p class="whitespace-pre-line">{{ activeLightbox.description || activeLightbox.content || 'Dokumentasi kegiatan madrasah terpadu.' }}</p>
              </div>
            </template>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { api } from '../../api';
import EtherealBeamsHero from '../../components/EtherealBeamsHero.vue';
import {
  LogIn,
  Menu as MenuIcon,
  X as XIcon,
  ArrowRight,
  BookOpen,
  Trophy,
  Building,
  Building2,
  Sparkles,
  MapPin,
  Phone,
  Mail,
  ExternalLink,
  ChevronRight,
  Check,
  Compass,
  Target,
  Newspaper,
  UserCheck,
  Warehouse,
  Image as ImageIcon,
  School,
  Search,
  GraduationCap,
  Award,
  Globe,
  UserPlus
} from 'lucide-vue-next';

// Logo fallback state
const logoErr = ref(false);

// School operational hours detector
const isSchoolHours = computed(() => {
  const hour = new Date().getHours();
  const day = new Date().getDay();
  return day >= 1 && day <= 6 && hour >= 7 && hour < 15;
});

// Interactive 3D Card Tilt Function
function handleCardTilt(e) {
  const card = e.currentTarget;
  const rect = card.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;
  
  const centerX = rect.width / 2;
  const centerY = rect.height / 2;
  
  const rotateX = ((y - centerY) / centerY) * -4;
  const rotateY = ((x - centerX) / centerX) * 4;
  
  card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.015, 1.015, 1.015)`;
}

function resetCardTilt(e) {
  const card = e.currentTarget;
  card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
}

// Lightbox with List Navigation
const activeLightbox = ref(null);
const lightboxList = ref([]);
const lightboxCurrentIndex = ref(0);

function openLightbox(item, type = 'general', index = 0, list = []) {
  activeLightbox.value = { ...item, type };
  lightboxList.value = list.length > 0 ? list : [item];
  lightboxCurrentIndex.value = index;
  document.body.style.overflow = 'hidden';
}

function closeLightbox() {
  activeLightbox.value = null;
  lightboxList.value = [];
  document.body.style.overflow = '';
}

function nextLightboxItem() {
  if (lightboxList.value.length <= 1) return;
  lightboxCurrentIndex.value = (lightboxCurrentIndex.value + 1) % lightboxList.value.length;
  activeLightbox.value = { ...lightboxList.value[lightboxCurrentIndex.value], type: activeLightbox.value?.type };
}

function prevLightboxItem() {
  if (lightboxList.value.length <= 1) return;
  lightboxCurrentIndex.value = (lightboxCurrentIndex.value - 1 + lightboxList.value.length) % lightboxList.value.length;
  activeLightbox.value = { ...lightboxList.value[lightboxCurrentIndex.value], type: activeLightbox.value?.type };
}

// Keyboard shortcuts for lightbox
function handleKeyDown(e) {
  if (!activeLightbox.value) return;
  if (e.key === 'Escape') closeLightbox();
  if (e.key === 'ArrowRight') nextLightboxItem();
  if (e.key === 'ArrowLeft') prevLightboxItem();
}

// Flagship Programs
const flagshipPrograms = [
  {
    title: 'Tahfidz Al-Quran & Kitab',
    description: 'Program hafalan juz 30 & pilihan disertai kajian kitab akhlak dasar untuk membentuk karakter santri modern.',
    icon: BookOpen,
    bgColor: 'bg-emerald-600',
    tag: 'Keagamaan Unggulan'
  },
  {
    title: 'Digital Class & Coding',
    description: 'Pembelajaran berbasis multimedia interaktif, pengenalan logika informatika, dan e-learning terpadu.',
    icon: Globe,
    bgColor: 'bg-indigo-600',
    tag: 'Sains & Teknologi'
  },
  {
    title: 'Bilingual Language Club',
    description: 'Pengembangan kemampuan komunikasi aktif Bahasa Arab & Bahasa Inggris melalui kegiatan harian dan muhadhoroh.',
    icon: GraduationCap,
    bgColor: 'bg-amber-600',
    tag: 'Bahasa Asing'
  },
  {
    title: 'Bina Prestasi & Olimpiade',
    description: 'Bimbingan intensif KSM (Kompetisi Sains Madrasah) dan ekstrakurikuler kepemimpinan, olahraga & seni.',
    icon: Award,
    bgColor: 'bg-rose-600',
    tag: 'Prestasi Juara'
  }
];

// Interactive FAQs
const activeFaq = ref(0);
const faqs = [
  {
    q: 'Bagaimana cara mengakses portal SIAKAD bagi Siswa dan Guru?',
    a: 'Siswa dan Guru dapat mengklik tombol "Masuk Portal" di pojok kanan atas, lalu memilih tab peran yang sesuai (Guru atau Siswa) dan memasukkan NIP/NISN serta password yang telah diberikan pihak madrasah.'
  },
  {
    q: 'Apakah presensi kehadiran guru menggunakan sistem GPS dan Selfie?',
    a: 'Ya, SIAKAD MTs dilengkapi dengan fitur Presensi Cerdas berbasis validasi radius GPS sekolah dan foto selfie langsung untuk menjamin kedisiplinan dan akurasi kehadiran tenaga pendidik.'
  },
  {
    q: 'Bagaimana jika siswa atau guru lupa kata sandi akun?',
    a: 'Pada halaman login, klik menu "Lupa Password? Minta Reset ke Admin", lalu masukkan NISN/NIP Anda. Admin madrasah akan langsung memverifikasi dan menyetujui permohonan reset password.'
  },
  {
    q: 'Bagaimana cara wali murid melihat transkrip nilai dan rekap absensi anak?',
    a: 'Wali murid dapat masuk menggunakan akun siswa untuk melihat rekap kehadiran bulanan, jurnal penilaian harian, dan transkrip nilai semester yang dapat langsung diunduh secara resmi.'
  }
];

const posts = ref([]);
const galleries = ref([]);
const facilities = ref([]);
const teachers = ref([]);
const classrooms = ref([]);
const achievements = ref([]);
const settings = ref({});
const stats = ref({ students: 0, teachers: 0, classes: 0 });
const loading = ref(true);
const scrolled = ref(false);
const isMobileMenuOpen = ref(false);

// Interactive Teacher Search
const teacherSearchQuery = ref('');
const filteredTeachers = computed(() => {
  if (!teacherSearchQuery.value.trim()) return teachers.value;
  const q = teacherSearchQuery.value.toLowerCase().trim();
  return teachers.value.filter(t => {
    const nameMatch = (t.full_name || '').toLowerCase().includes(q);
    const posMatch = (t.position || '').toLowerCase().includes(q);
    const subMatch = (t.subjects || []).some(s => (s.name || '').toLowerCase().includes(q));
    return nameMatch || posMatch || subMatch;
  });
});

// Interactive Gallery Category Filter
const selectedGalleryCategory = ref('Semua');
const galleryCategories = computed(() => {
  const cats = ['Semua'];
  galleries.value.forEach(g => {
    if (g.category && !cats.includes(g.category)) {
      cats.push(g.category);
    }
  });
  return cats;
});

const filteredGalleries = computed(() => {
  if (selectedGalleryCategory.value === 'Semua') return galleries.value;
  return galleries.value.filter(g => g.category === selectedGalleryCategory.value);
});

function getStorageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) {
    return path;
  }
  const cleanPath = path.replace(/^\/?storage\//, '').replace(/^\//, '');
  return `/storage/${cleanPath}`;
}

function cleanPhoneForWa(phone) {
  if (!phone) return '';
  let clean = phone.replace(/[^0-9]/g, '');
  if (clean.startsWith('0')) {
    clean = '62' + clean.slice(1);
  }
  return clean;
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

const cleanMapsUrl = computed(() => {
  const embed = settings.value?.google_maps_embed || '';
  if (!embed) return '';
  const match = embed.match(/src=["']([^"']+)["']/);
  if (match) return match[1];
  if (embed.startsWith('http')) return embed;
  return '';
});

const mapDirectionsUrl = computed(() => {
  if (settings.value?.google_maps_link) return settings.value.google_maps_link;
  if (settings.value?.google_maps_embed) {
    const match = settings.value.google_maps_embed.match(/src=["']([^"']+)["']/);
    if (match) return match[1];
  }
  return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(settings.value?.school_address || 'Madrasah')}`;
});

const formattedMissions = computed(() => {
  const missionStr = settings.value?.school_mission || '';
  if (!missionStr) return [];
  return missionStr
    .split(/[\n\/]+/)
    .map(m => m.trim())
    .filter(m => m.length > 0)
    .map(m => m.replace(/^[0-9]+[.)-]\s*/, ''));
});

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
}

function closeMobileMenu() {
  isMobileMenuOpen.value = false;
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  }).format(date);
}

function handleScroll() {
  scrolled.value = window.scrollY > 50;
}

onMounted(async () => {
  window.addEventListener('scroll', handleScroll);
  window.addEventListener('keydown', handleKeyDown);
  try {
    const res = await api.get('/public');
    const data = (res && typeof res === 'object' && ('posts' in res || 'settings' in res)) ? res : (res?.data || {});

    posts.value = data.posts || [];
    galleries.value = data.galleries || [];
    facilities.value = data.facilities || [];
    teachers.value = data.teachers || [];
    classrooms.value = data.classrooms || [];
    achievements.value = data.achievements || [];
    settings.value = data.settings || {};
    stats.value = data.stats || { students: 0, teachers: 0, classes: 0 };
  } catch (err) {
    console.error('Failed to load public data', err);
  } finally {
    loading.value = false;
  }
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  window.removeEventListener('keydown', handleKeyDown);
});
</script>

<style scoped>
.nav-item {
  padding: 0.375rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  transition: all 0.2s ease;
}
.nav-item:hover {
  transform: translateY(-2px);
}
.nav-top {
  color: #a7f3d0;
}
.nav-top:hover {
  color: #ffffff;
  background-color: rgba(6, 78, 59, 0.6);
}
.nav-scrolled {
  color: #475569;
}
.nav-scrolled:hover {
  color: #047857;
  background-color: #ecfdf5;
}

.mobile-nav-link {
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
}
.mobile-nav-link:hover {
  color: #047857;
  background-color: #ecfdf5;
}

.interactive-tilt-card {
  transition: transform 0.15s ease-out, box-shadow 0.3s ease, border-color 0.3s ease, background-color 0.3s ease;
  will-change: transform;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
