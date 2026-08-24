<template>
  <div 
    class="rounded-2xl w-full max-w-4xl mx-auto bg-white border border-slate-200 overflow-hidden shadow-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 transition-all duration-300"
    role="group"
    aria-roledescription="carousel"
    aria-label="Gallery image carousel"
    aria-live="polite"
    aria-atomic="true"
    tabindex="0"
    @keydown="handleKeyDown"
  >
    <!-- Image Slider -->
    <div class="relative h-96 bg-slate-100 overflow-hidden group">
      <Transition name="slide-fade" mode="out-in">
        <img
          :key="currentIndex"
          :src="currentSlide.image"
          class="absolute w-full h-full object-cover transition-opacity duration-300"
          :alt="currentSlide.title || 'Gallery image'"
          role="img"
        />
      </Transition>

      <!-- Navigation Buttons -->
      <button
        type="button"
        @click.stop="paginate(-1)"
        class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 backdrop-blur-md border border-slate-200 flex items-center justify-center hover:bg-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 rounded-full shadow-lg opacity-0 group-hover:opacity-100 duration-300 z-10"
        aria-label="Previous slide"
      >
        <svg class="w-5 h-5 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      </button>
      <button
        type="button"
        @click.stop="paginate(1)"
        class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 backdrop-blur-md border border-slate-200 flex items-center justify-center hover:bg-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 rounded-full shadow-lg opacity-0 group-hover:opacity-100 duration-300 z-10"
        aria-label="Next slide"
      >
        <svg class="w-5 h-5 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      </button>

      <span class="sr-only" role="status">
        Slide {{ currentIndex + 1 }} of {{ slideCount }}
      </span>
    </div>

    <!-- Content -->
    <div class="p-8 border-t border-slate-100 bg-white" role="tablist" aria-label="Slide navigation">
      <Transition name="fade" mode="out-in">
        <div :key="currentIndex" role="tabpanel">
          <h2 class="text-2xl font-bold font-lexend text-slate-800 mb-2 line-clamp-1">
            {{ currentSlide.title || 'Tanpa Judul' }}
          </h2>
          <p class="text-slate-500 leading-relaxed line-clamp-2 min-h-[3rem]" aria-live="polite">
            {{ currentSlide.description || 'Tidak ada deskripsi.' }}
          </p>
        </div>
      </Transition>

      <!-- Dot Indicators -->
      <div class="flex items-center gap-2 mt-6">
        <button
          v-for="(slide, index) in activeSlides"
          :key="slide.id || index"
          type="button"
          @click="goToSlide(index)"
          :class="[
            'rounded-full h-1.5 transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500',
            index === currentIndex ? 'w-8 bg-emerald-600' : 'w-1.5 bg-slate-200 hover:bg-slate-300'
          ]"
          :aria-label="`Go to slide ${index + 1}`"
          role="tab"
          :aria-selected="index === currentIndex"
          :tabindex="index === currentIndex ? 0 : -1"
        >
          <span class="sr-only">
            {{ slide.title }} ({{ index + 1 }} of {{ slideCount }})
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  slides: {
    type: Array,
    default: () => []
  }
});

// Default dummy slides if empty
const dummySlides = [
  {
    id: 1,
    image: "https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800&q=80",
    title: "Minimalist Design",
    description: "Clean lines and simple forms create timeless elegance.",
  },
  {
    id: 2,
    image: "https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=800&q=80",
    title: "Modern Simplicity",
    description: "Less is more in contemporary visual language.",
  },
  {
    id: 3,
    image: "https://images.unsplash.com/photo-1618005198919-d3d4b5a92ead?w=800&q=80",
    title: "Pure Essence",
    description: "Stripped down to the essential elements.",
  }
];

const activeSlides = computed(() => {
  return props.slides && props.slides.length > 0 ? props.slides : dummySlides;
});

const slideCount = computed(() => activeSlides.value.length);
const currentIndex = ref(0);

const currentSlide = computed(() => {
  return activeSlides.value[currentIndex.value] || {};
});

const paginate = (direction) => {
  let nextIndex = currentIndex.value + direction;
  if (nextIndex < 0) {
    nextIndex = slideCount.value - 1;
  } else if (nextIndex >= slideCount.value) {
    nextIndex = 0;
  }
  currentIndex.value = nextIndex;
};

const goToSlide = (index) => {
  if (index !== currentIndex.value) {
    currentIndex.value = index;
  }
};

const handleKeyDown = (event) => {
  switch (event.key) {
    case 'ArrowLeft':
      event.preventDefault();
      paginate(-1);
      break;
    case 'ArrowRight':
      event.preventDefault();
      paginate(1);
      break;
    case 'Home':
      event.preventDefault();
      goToSlide(0);
      break;
    case 'End':
      event.preventDefault();
      goToSlide(slideCount.value - 1);
      break;
  }
};
</script>

<style scoped>
/* Slide and fade animation for image */
.slide-fade-enter-active {
  transition: all 0.4s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: scale(0.98);
}

/* Simple fade for content */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
