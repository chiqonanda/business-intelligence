<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { onMounted, ref } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  stats: Object,
  latest_transactions: Array,
  top_products: Array,
  charts: Object,
})

const trendChart = ref(null)
const regionChart = ref(null)
const channelChart = ref(null)

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(val || 0)
}

onMounted(() => {
  // Monthly Trend
  new Chart(trendChart.value, {
    type: 'line',
    data: {
      labels: props.charts.monthly.labels,
      datasets: [{
        label: 'Revenue',
        data: props.charts.monthly.data,
        borderColor: '#d9ff00',
        backgroundColor: 'rgba(217, 255, 0, 0.1)',
        fill: true,
        tension: 0.4,
        borderWidth: 3,
        pointRadius: 0,
        pointHoverRadius: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#52525b', font: { weight: 'bold' } } },
        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#52525b', font: { weight: 'bold' } } }
      }
    }
  })

  // Region Split
  new Chart(regionChart.value, {
    type: 'doughnut',
    data: {
      labels: props.charts.region.labels,
      datasets: [{
        data: props.charts.region.data,
        backgroundColor: ['#d9ff00', '#27272a', '#3f3f46', '#52525b', '#71717a'],
        borderWidth: 0,
        hoverOffset: 20
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { color: '#a1a1aa', usePointStyle: true, font: { weight: 'bold', size: 10 } } } },
      cutout: '70%'
    }
  })

  // Channel Split
  new Chart(channelChart.value, {
    type: 'bar',
    data: {
      labels: props.charts.channel.labels,
      datasets: [{
        label: 'Revenue',
        data: props.charts.channel.data,
        backgroundColor: '#d9ff00',
        borderRadius: 4,
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#52525b', font: { weight: 'bold' } } },
        y: { grid: { display: false }, ticks: { color: '#a1a1aa', font: { weight: 'bold' } } }
      }
    }
  })
})
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
      <h1 class="page-title-premium mb-6 uppercase">Market <br/> Intelligence.</h1>
      <p class="max-w-2xl text-zinc-500 font-bold text-lg leading-relaxed uppercase tracking-tight">
        Real-time visibility into Nike's global sales performance. <br/> Monitoring volume, revenue, and regional dominance.
      </p>
    </div>

    <!-- METRICS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16 animate-slide-up" style="animation-delay: 0.1s">
      <!-- Revenue -->
      <div class="card-premium p-8 group relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 text-[30px] font-black text-white/5 italic select-none">USD</div>
        <p class="text-[10px] font-black text-zinc-500 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">TOTAL REVENUE</p>
        <p class="text-4xl font-black italic tracking-tighter">{{ formatCurrency(stats.total_revenue) }}</p>
      </div>

      <!-- Units -->
      <div class="card-premium p-8 group relative overflow-hidden">
        <p class="text-[10px] font-black text-zinc-500 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">UNITS SOLD</p>
        <p class="text-4xl font-black italic tracking-tighter">{{ (stats.total_units || 0).toLocaleString() }}</p>
      </div>

      <!-- Top Product -->
      <div class="card-premium p-8 group relative overflow-hidden bg-[#d9ff00]">
        <p class="text-[10px] font-black text-black/40 uppercase tracking-[0.3em] mb-4">BEST SELLER</p>
        <p class="text-2xl font-black italic tracking-tighter text-black uppercase truncate">{{ top_products[0]?.product_name || 'N/A' }}</p>
        <p class="text-[10px] font-bold text-black/60 uppercase tracking-widest mt-1">{{ top_products[0]?.units.toLocaleString() }} UNITS</p>
      </div>

      <!-- Active Status -->
      <div class="card-premium p-8 group relative overflow-hidden border-[#d9ff00]/20">
        <p class="text-[10px] font-black text-zinc-500 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">SYSTEM STATUS</p>
        <div class="flex items-center gap-3">
          <div class="h-2 w-2 rounded-full bg-[#d9ff00] animate-pulse shadow-[0_0_8px_#d9ff00]"></div>
          <p class="text-xl font-black italic tracking-tighter uppercase">LIVE FEED</p>
        </div>
      </div>
    </div>

    <!-- MAIN CHARTS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-16 animate-slide-up" style="animation-delay: 0.2s">
      <!-- Monthly Trend -->
      <div class="lg:col-span-2 card-premium p-8 h-[400px] flex flex-col">
        <div class="flex items-center justify-between mb-8">
          <h3 class="text-xs font-black uppercase tracking-[0.3em] text-zinc-500">Monthly Revenue Trend</h3>
          <span class="text-[10px] font-black text-[#d9ff00] bg-white/5 px-2 py-1 italic">FISCAL YEAR 2024</span>
        </div>
        <div class="flex-1 min-h-0">
          <canvas ref="trendChart"></canvas>
        </div>
      </div>

      <!-- Region Split -->
      <div class="card-premium p-8 h-[400px] flex flex-col">
        <h3 class="text-xs font-black uppercase tracking-[0.3em] text-zinc-500 mb-8 text-center">Sales by Region</h3>
        <div class="flex-1 min-h-0">
          <canvas ref="regionChart"></canvas>
        </div>
      </div>
    </div>

    <!-- BOTTOM SECTION -->
    <div class="grid lg:grid-cols-3 gap-12 animate-slide-up" style="animation-delay: 0.3s">
      <!-- Top 5 Products -->
      <div>
        <h3 class="text-xl font-black italic uppercase tracking-tighter mb-8">Top 5 Products</h3>
        <div class="space-y-4">
          <div v-for="(product, idx) in top_products" :key="idx" 
               class="flex items-center justify-between p-5 bg-[#0a0a0a] group hover:bg-[#111] transition-all">
            <div class="flex items-center gap-4">
               <span class="text-xl font-black italic text-zinc-800 group-hover:text-[#d9ff00] transition-colors">0{{ idx + 1 }}</span>
               <div>
                 <p class="text-sm font-black uppercase tracking-tight group-hover:text-white">{{ product.product_name }}</p>
                 <p class="text-[10px] font-bold text-zinc-600 uppercase tracking-widest mt-1">{{ product.product_line }}</p>
               </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-black italic">{{ (product.units).toLocaleString() }}</p>
              <p class="text-[9px] font-bold text-zinc-600 uppercase tracking-widest">UNITS</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Sales Channel & Live Feed -->
      <div class="lg:col-span-2 grid md:grid-cols-2 gap-8">
        <!-- Channel Split -->
        <div class="card-premium p-8 flex flex-col h-full">
          <h3 class="text-xs font-black uppercase tracking-[0.3em] text-zinc-500 mb-8">Sales Channel Analysis</h3>
          <div class="flex-1 min-h-[200px]">
             <canvas ref="channelChart"></canvas>
          </div>
        </div>

        <!-- Latest Orders -->
        <div class="flex flex-col">
          <div class="flex items-center justify-between mb-8">
            <h3 class="text-xs font-black uppercase tracking-[0.3em] text-zinc-500">Live Order Feed</h3>
            <Link :href="route('login')" class="text-[10px] font-black text-[#d9ff00] hover:text-white uppercase tracking-widest border-b border-[#d9ff00] pb-1 transition-all">Login for Details</Link>
          </div>
          <div class="space-y-3">
            <div v-for="tx in latest_transactions" :key="tx.order_id" 
                 class="flex items-center justify-between p-4 bg-[#0a0a0a]/50 border-l border-white/5">
              <div class="flex items-center gap-4">
                 <div class="text-[9px] font-black text-zinc-600 italic">****</div>
                 <p class="text-[11px] font-black uppercase tracking-tight text-zinc-400">{{ tx.product_name }}</p>
              </div>
              <p class="text-[11px] font-black italic text-zinc-600">{{ formatCurrency(tx.revenue) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

