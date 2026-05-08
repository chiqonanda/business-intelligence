<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  stats: Object,
  latest_transactions: Array,
})

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(val || 0)
}
</script>

<template>
  <AppLayout>
    <Head title="Nike BI Control" />

    <!-- HERO SECTION -->
    <div class="mb-16 animate-slide-up">
      <div class="flex items-center gap-4 mb-4">
         <div class="h-[2px] w-12 bg-[#d9ff00]"></div>
         <p class="text-xs font-black text-[#d9ff00] uppercase tracking-[0.5em]">Global Sales Network</p>
      </div>
      <h1 class="page-title-premium mb-6">UNSTOPPABLE <br/> PERFORMANCE.</h1>
      <p class="max-w-2xl text-zinc-500 font-bold text-lg leading-relaxed uppercase tracking-tight">
        Real-time telemetry from Nike's global distribution channels. <br/> Monitoring efficiency, revenue flow, and market dominance.
      </p>
    </div>

    <!-- METRICS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16 animate-slide-up" style="animation-delay: 0.1s">
      <!-- Revenue -->
      <div class="card-premium p-10 group overflow-hidden relative">
        <div class="absolute top-0 right-0 p-4 text-[40px] font-black text-white/5 italic select-none">USD</div>
        <p class="text-[11px] font-black text-zinc-500 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">TOTAL REVENUE</p>
        <div class="flex items-baseline gap-3">
          <p class="text-5xl font-black italic tracking-tighter">{{ formatCurrency(stats.total_revenue) }}</p>
          <span class="text-xs font-bold text-[#d9ff00]">+12.4%</span>
        </div>
        <div class="mt-8 h-[1px] w-full bg-white/5 relative">
           <div class="absolute inset-0 bg-[#d9ff00] w-[70%] shadow-[0_0_10px_#d9ff00]"></div>
        </div>
      </div>

      <!-- Units -->
      <div class="card-premium p-10 group">
        <p class="text-[11px] font-black text-zinc-500 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">UNITS DISPATCHED</p>
        <div class="flex items-baseline gap-3">
          <p class="text-5xl font-black italic tracking-tighter">{{ (stats.total_units || 0).toLocaleString() }}</p>
          <span class="text-xs font-bold text-zinc-500 italic">Global</span>
        </div>
        <div class="mt-8 h-[1px] w-full bg-white/5 relative">
           <div class="absolute inset-0 bg-white w-[45%]"></div>
        </div>
      </div>

      <!-- Profit -->
      <div class="card-premium p-10 group">
        <p class="text-[11px] font-black text-zinc-500 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">NET MARGIN</p>
        <div class="flex items-baseline gap-3">
          <p class="text-5xl font-black italic tracking-tighter">{{ formatCurrency(stats.total_profit) }}</p>
          <span class="text-xs font-bold text-[#d9ff00]">Optimal</span>
        </div>
        <div class="mt-8 h-[1px] w-full bg-white/5 relative">
           <div class="absolute inset-0 bg-[#d9ff00] w-[62%]"></div>
        </div>
      </div>
    </div>

    <!-- RECENT DATA SECTION -->
    <div class="grid lg:grid-cols-3 gap-12 animate-slide-up" style="animation-delay: 0.2s">
      <!-- Transaction Table -->
      <div class="lg:col-span-2">
        <div class="flex items-center justify-between mb-8">
          <h3 class="text-xl font-black italic uppercase tracking-tighter">Live Order Feed</h3>
          <Link :href="route('analyst.index')" class="text-[10px] font-black text-[#d9ff00] hover:text-white uppercase tracking-widest border-b border-[#d9ff00] pb-1 transition-all">View All Intel</Link>
        </div>
        
        <div class="space-y-4">
          <div v-for="tx in latest_transactions" :key="tx.order_id" 
               class="flex items-center justify-between p-6 bg-[#0a0a0a] border-l-2 border-transparent hover:border-[#d9ff00] transition-all group">
            <div class="flex items-center gap-6">
               <div class="text-[10px] font-black text-zinc-600 bg-white/5 px-3 py-1 italic">{{ tx.order_id }}</div>
               <div>
                 <p class="text-sm font-black uppercase tracking-tight group-hover:text-[#d9ff00] transition-colors">{{ tx.product_name }}</p>
                 <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mt-1">{{ tx.product_line }}</p>
               </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-black italic">{{ formatCurrency(tx.revenue) }}</p>
              <p class="text-[9px] font-bold text-zinc-600 uppercase tracking-widest">{{ tx.order_date }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Side Content -->
      <div class="space-y-8">
         <div class="card-premium p-8 bg-gradient-to-br from-[#111] to-[#050505]">
            <h4 class="text-xs font-black uppercase tracking-[0.3em] text-[#d9ff00] mb-6">Mission Critical</h4>
            <p class="text-sm font-bold text-zinc-400 mb-6 leading-relaxed">
              Ensure all data pipelines are synchronized before EOD reporting. Discrepancies must be reported to the Security Control unit immediately.
            </p>
            <div class="flex items-center gap-3">
               <span class="h-1.5 w-1.5 rounded-full bg-[#d9ff00]"></span>
               <span class="text-[10px] font-black uppercase tracking-widest">Protocol: Active</span>
            </div>
         </div>

         <div class="relative group cursor-pointer overflow-hidden">
            <div class="absolute inset-0 bg-black/40 group-hover:bg-black/0 transition-all duration-500 z-10"></div>
            <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&q=80&w=600" class="w-full grayscale group-hover:grayscale-0 transition-all duration-700 scale-110 group-hover:scale-100" />
            <div class="absolute bottom-0 left-0 p-6 z-20">
               <p class="text-2xl font-black italic text-white uppercase leading-none tracking-tighter">NIKE AIR MAX</p>
               <p class="text-[10px] font-black text-[#d9ff00] uppercase tracking-[0.3em] mt-2">New Season Analytics Available</p>
            </div>
         </div>
      </div>
    </div>

  </AppLayout>
</template>
