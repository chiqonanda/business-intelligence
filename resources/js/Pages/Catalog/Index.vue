<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch } from 'vue'

const props = defineProps({
  products: Object,
  filters: Object,
})

const search = ref(props.filters.search || '')

watch(search, (val) => {
  router.get(route('catalog.index'), { search: val }, { preserveState: true, replace: true })
})

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val || 0)
}
</script>

<template>
  <AppLayout>
    <Head title="Product Assets" />

    <div class="mb-12 animate-slide-up">
      <div class="flex items-center gap-6 mb-8">
         <div class="h-[1px] w-20 bg-[#d9ff00]"></div>
         <p class="text-[10px] font-black text-[#d9ff00] uppercase tracking-[0.6em]">Premium Gear Catalog</p>
      </div>
      <h1 class="page-title-premium mb-8">Product <br/> <span class="text-stroke">Inventory.</span></h1>
      
      <div class="flex flex-col md:flex-row gap-6 mt-16 max-w-4xl">
        <div class="relative flex-1 group">
          <div class="absolute inset-y-0 left-6 flex items-center pointer-events-none">
             <svg class="h-4 w-4 text-zinc-700 group-focus-within:text-[#d9ff00] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
          </div>
          <input 
            v-model="search" 
            type="text" 
            placeholder="SEARCH GLOBAL ASSETS..." 
            class="input-premium w-full !pl-12 !bg-white/[0.02]"
          />
        </div>
      </div>
    </div>

    <div v-if="products.data.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6 animate-slide-up" style="animation-delay: 0.1s">
      <div v-for="product in products.data" :key="product.id" class="card-premium group overflow-hidden flex flex-col bg-black hover:border-[#d9ff00]/30">
        <div class="aspect-[4/5] relative overflow-hidden bg-zinc-950">
          <img 
            :src="product.images?.split('|')[0]?.trim() || 'https://via.placeholder.com/400'" 
            class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-1000 opacity-90 group-hover:opacity-100"
          />
          <div class="absolute top-6 right-6 badge-premium !bg-black/80 !backdrop-blur-md !text-[#d9ff00] !border-[#d9ff00]/20">
            {{ product.brand?.toUpperCase() }}
          </div>
        </div>
        
        <div class="p-8 flex-1 flex flex-col bg-gradient-to-b from-transparent to-black/20">
          <p class="text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-3">{{ product.sub_title }}</p>
          <h3 class="text-sm font-black uppercase tracking-tight mb-6 group-hover:text-[#d9ff00] transition-colors line-clamp-2 font-header leading-snug">{{ product.name }}</h3>
          
          <div class="mt-auto flex items-end justify-between pt-6 border-t border-white/5">
            <div>
              <p class="text-[9px] font-bold text-zinc-700 uppercase tracking-widest mb-1">MSRP</p>
              <p class="text-xl font-black italic font-header">{{ formatCurrency(product.price) }}</p>
            </div>
            <div class="text-right">
              <p class="text-[9px] font-bold text-zinc-700 uppercase tracking-widest mb-1">RATING</p>
              <p class="text-sm font-black text-[#d9ff00] italic font-header">{{ product.avg_rating }} ★</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- EMPTY STATE -->
    <div v-else class="py-40 glass-effect border-dashed flex flex-col items-center justify-center animate-slide-up">
       <div class="h-16 w-16 rounded-full bg-white/5 flex items-center justify-center mb-8 border border-white/10 group">
          <svg class="h-8 w-8 text-zinc-700 group-hover:text-[#d9ff00] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
       </div>
       <p class="text-sm font-black text-white uppercase tracking-[0.4em] mb-3">No Assets Detected</p>
       <p class="text-[10px] font-bold text-zinc-600 uppercase tracking-[0.2em] max-w-sm text-center leading-relaxed">The catalog node is currently empty. Please upload the product baseline CSV to initialize the gear database.</p>
    </div>

    <!-- PAGINATION -->
    <div class="mt-24 p-10 glass-effect flex flex-col md:flex-row justify-between items-center gap-8 animate-slide-up">
      <div class="flex items-center gap-4">
          <div class="h-1 w-1 bg-[#d9ff00] rounded-full"></div>
          <p class="text-[10px] font-black text-zinc-600 uppercase tracking-widest italic">
            Displaying node {{ products.from }} - {{ products.to }} of {{ products.total }} gear assets
          </p>
      </div>
      <div class="flex gap-2">
        <Link 
          v-for="link in products.links" 
          :key="link.label"
          :href="link.url || '#'"
          v-html="link.label"
          class="px-5 py-3 text-[10px] font-black transition-all uppercase tracking-widest border border-transparent"
          :class="[
            link.active ? 'bg-[#d9ff00] text-black' : 'bg-white/5 text-zinc-600 hover:text-white hover:border-white/10',
            !link.url ? 'opacity-20 cursor-default' : 'cursor-pointer'
          ]"
        />
      </div>
    </div>
  </AppLayout>
</template>
