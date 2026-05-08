<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { onMounted, ref } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  summary: Object,
  regions: Array,
  quarters: Array,
  product_lines: Array,
})

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(val || 0)
}

const regionChartRef = ref(null)
const productChartRef = ref(null)

onMounted(() => {
  // Hanya inisialisasi grafik jika ada data yang valid
  const regionsData = props.regions || [];
  const productsData = props.product_lines || [];

  if (regionsData.length > 0) {
    new Chart(regionChartRef.value, {
      type: 'bar',
      data: {
        labels: regionsData.map(r => (r.region || 'UNKNOWN').toUpperCase()),
        datasets: [{
          label: 'Revenue',
          data: regionsData.map(r => r.total_revenue || 0),
          backgroundColor: '#d9ff00',
          borderRadius: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#444', font: { size: 9, weight: '900' } } },
          x: { grid: { display: false }, ticks: { color: '#444', font: { size: 9, weight: '900' } } }
        }
      }
    })
  }

  if (productsData.length > 0) {
    new Chart(productChartRef.value, {
      type: 'doughnut',
      data: {
        labels: productsData.map(p => (p.product_line || 'UNKNOWN').toUpperCase()),
        datasets: [{
          data: productsData.map(p => p.total_revenue || 0),
          backgroundColor: ['#d9ff00', '#ffffff', '#222', '#444', '#666'],
          borderWidth: 0,
          cutout: '80%'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
      }
    })
  }
})
</script>

<template>
  <AppLayout>
    <Head title="Nike Strategy Hub" />

    <div class="mb-16 animate-slide-up">
      <p class="text-[10px] font-black uppercase tracking-[0.5em] text-[#d9ff00] mb-4">Strategic Operations</p>
      <h1 class="page-title-premium">STRATEGY HUB</h1>
      <p class="text-zinc-600 font-bold text-sm uppercase tracking-widest mt-4">High-level executive oversight and market optimization metrics.</p>
    </div>

    <!-- SUMMARY CARDS -->
    <div v-if="summary" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-16 animate-slide-up" style="animation-delay: 0.1s">
      <div class="card-premium p-8 bg-black relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-4 text-[40px] font-black text-white/5 italic select-none">REV</div>
        <p class="text-[10px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">YTD REVENUE</p>
        <p class="text-3xl font-black italic tracking-tighter text-white">{{ formatCurrency(summary.total_revenue || 0) }}</p>
      </div>
      <div class="card-premium p-8 bg-black group">
        <p class="text-[10px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">TOTAL PROFIT</p>
        <p class="text-3xl font-black italic tracking-tighter text-white">{{ formatCurrency(summary.total_profit || 0) }}</p>
      </div>
      <div class="card-premium p-8 bg-black group">
        <p class="text-[10px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">UNITS SHIPPED</p>
        <p class="text-3xl font-black italic tracking-tighter text-white">{{ (summary.total_units || 0).toLocaleString() }}</p>
      </div>
      <div class="card-premium p-8 bg-black group">
        <p class="text-[10px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">AVG ORDER VALUE</p>
        <p class="text-3xl font-black italic tracking-tighter text-white">{{ formatCurrency(summary.avg_revenue || 0) }}</p>
      </div>
    </div>

    <!-- STRATEGIC ANALYSIS -->
    <div class="grid lg:grid-cols-2 gap-10 mb-16 animate-slide-up" style="animation-delay: 0.2s">
      <!-- Region Performance -->
      <div class="card-premium p-10 bg-black">
        <h3 class="text-xs font-black text-white uppercase tracking-[0.3em] mb-10">Market Dominance by Region</h3>
        <div class="h-80">
          <canvas ref="regionChartRef"></canvas>
        </div>
      </div>

      <!-- Product Mix -->
      <div class="card-premium p-10 bg-black">
        <h3 class="text-xs font-black text-white uppercase tracking-[0.3em] mb-10">Product Portfolio Mix</h3>
        <div class="h-80 relative">
          <canvas ref="productChartRef"></canvas>
          <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
             <div class="text-center" v-if="product_lines && product_lines.length > 0">
                <p class="text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Dominant Line</p>
                <p class="text-xs font-black text-white mt-1">{{ (product_lines[0]?.product_line || 'N/A').toUpperCase() }}</p>
             </div>
             <div class="text-center" v-else>
                <p class="text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Awaiting Data</p>
             </div>
          </div>
        </div>
      </div>
    </div>

    <!-- QUARTERLY BREAKDOWN -->
    <div class="card-premium bg-black animate-slide-up" style="animation-delay: 0.3s">
      <div class="p-8 border-b border-white/5">
        <h3 class="text-xs font-black text-white uppercase tracking-[0.3em]">Fiscal Quarter Analysis</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-white/[0.02]">
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Quarter Node</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Revenue</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Profit</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Units</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Growth Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="q in quarters" :key="q.kuartal" class="hover:bg-white/[0.01] transition-colors group">
              <td class="px-8 py-6">
                 <span class="text-lg font-black italic text-white tracking-tighter">Q{{ q.kuartal }}</span>
                 <span class="ml-2 text-[9px] font-black text-zinc-600 uppercase tracking-widest">{{ q.tahun }}</span>
              </td>
              <td class="px-8 py-6 text-[11px] font-black text-white italic">{{ formatCurrency(q.total_revenue) }}</td>
              <td class="px-8 py-6 text-[11px] font-black text-[#d9ff00] italic">{{ formatCurrency(q.total_profit) }}</td>
              <td class="px-8 py-6 text-[11px] font-black text-white italic">{{ q.total_units.toLocaleString() }}</td>
              <td class="px-8 py-6">
                 <div class="flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#d9ff00]"></span>
                    <span class="text-[9px] font-black text-zinc-400 uppercase tracking-[0.2em]">STABLE PERFORMANCE</span>
                 </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>