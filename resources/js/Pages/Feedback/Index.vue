<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch } from 'vue'

const props = defineProps({
  reviews: Object,
  filters: Object,
})

const search = ref(props.filters.search || '')
const rating = ref(props.filters.rating || '')

watch([search, rating], ([s, r]) => {
  router.get(route('review.index'), { search: s, rating: r }, { preserveState: true, replace: true })
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).toUpperCase()
}
</script>

<template>
  <AppLayout>
    <Head title="Customer Feedback" />

    <div class="mb-10 animate-slide-up">
      <div class="flex items-center gap-4 mb-4">
         <div class="h-[2px] w-12 bg-[#d9ff00]"></div>
         <p class="text-xs font-black text-[#d9ff00] uppercase tracking-[0.5em]">Voice of Consumer</p>
      </div>
      <h1 class="page-title-premium mb-6 uppercase">Customer <br/> Feedback.</h1>
      
      <div class="flex flex-col md:flex-row gap-6 mt-8">
        <div class="relative flex-1">
          <input 
            v-model="search" 
            type="text" 
            placeholder="FILTER BY KEYWORD..." 
            class="w-full bg-white/5 border border-white/10 px-6 py-4 text-xs font-black text-white focus:border-[#d9ff00] outline-none transition-all placeholder:text-zinc-700 uppercase tracking-[0.2em]"
          />
        </div>
        <select v-model="rating" class="bg-black border border-white/10 text-xs font-black text-white px-8 py-4 uppercase tracking-[0.2em] focus:ring-0 focus:border-[#d9ff00]">
          <option value="">ALL RATINGS</option>
          <option v-for="r in [5,4,3,2,1]" :key="r" :value="r">{{ r }} STARS</option>
        </select>
      </div>
    </div>

    <div class="space-y-6 animate-slide-up" style="animation-delay: 0.1s">
      <div v-for="review in reviews.data" :key="review.id" class="card-premium p-5 bg-black group hover:bg-[#0a0a0a] transition-all">
        <div class="flex flex-col md:flex-row gap-6">
          <div class="md:w-64 flex-shrink-0">
            <div class="flex items-center gap-2 mb-4">
              <span v-for="i in 5" :key="i" :class="i <= review.rating ? 'text-[#d9ff00]' : 'text-zinc-800'" class="text-lg">★</span>
            </div>
            <p class="text-[10px] font-black text-white uppercase tracking-widest">{{ review.username }}</p>
            <p class="text-[9px] font-bold text-zinc-600 uppercase tracking-widest mt-1">{{ review.location }}</p>
            <p class="text-[9px] font-bold text-zinc-700 uppercase tracking-widest mt-4">{{ formatDate(review.review_date) }}</p>
          </div>
          
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-4">
               <div class="h-[1px] w-4 bg-[#d9ff00]/30"></div>
               <p class="text-[10px] font-black text-[#d9ff00] uppercase tracking-widest">{{ review.product_title }}</p>
            </div>
            <p class="text-zinc-400 font-bold leading-relaxed italic tracking-tight mb-8">"{{ review.review }}"</p>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-6 border-t border-white/5">
              <div v-if="review.fit_feedback">
                <p class="text-[9px] font-bold text-zinc-600 uppercase tracking-widest mb-1">FIT</p>
                <p class="text-[10px] font-black text-white uppercase">{{ review.fit_feedback }}</p>
              </div>
              <div v-if="review.comfort_feedback">
                <p class="text-[9px] font-bold text-zinc-600 uppercase tracking-widest mb-1">COMFORT</p>
                <p class="text-[10px] font-black text-white uppercase">{{ review.comfort_feedback }}</p>
              </div>
              <div v-if="review.label">
                <p class="text-[9px] font-bold text-zinc-600 uppercase tracking-widest mb-1">STATUS</p>
                <p class="text-[10px] font-black text-[#d9ff00] uppercase">{{ review.label }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- PAGINATION -->
    <div class="mt-16 p-8 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-6 animate-slide-up">
      <p class="text-[10px] font-black text-zinc-600 uppercase tracking-widest italic">
        Displaying {{ reviews.from }} - {{ reviews.to }} of {{ reviews.total }} feedback nodes
      </p>
      <div class="flex gap-1">
        <Link 
          v-for="link in reviews.links" 
          :key="link.label"
          :href="link.url || '#'"
          v-html="link.label"
          class="px-4 py-2 text-[10px] font-black transition-all uppercase tracking-widest"
          :class="[
            link.active ? 'bg-[#d9ff00] text-black' : 'bg-white/5 text-zinc-500 hover:bg-white/10',
            !link.url ? 'opacity-20 cursor-default' : 'cursor-pointer'
          ]"
        />
      </div>
    </div>
  </AppLayout>
</template>
