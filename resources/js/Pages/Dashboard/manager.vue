<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { onMounted, ref } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  summary: Object,
  byLine: Array,
  byRegion: Array,
  byGender: Array,
  yearlyComparison: Array,
  topProducts: Array,
  channelPerf: Array,
  reviewSummary: Object,
  monthlyTrend: Object,
})

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(val || 0)
}

const trendChartRef = ref(null)
const regionChartRef = ref(null)
const productChartRef = ref(null)

onMounted(() => {
  // 1. Monthly Revenue & Profit Trend
  if (props.monthlyTrend) {
    new Chart(trendChartRef.value, {
      type: 'line',
      data: {
        labels: props.monthlyTrend.labels,
        datasets: [
          { 
            label: 'REVENUE', 
            data: props.monthlyTrend.revenue, 
            borderColor: '#d9ff00', 
            backgroundColor: 'transparent',
            borderWidth: 4, 
            tension: 0.4, 
            pointRadius: 0,
            pointHoverRadius: 6,
            pointHoverBackgroundColor: '#d9ff00'
          },
          { 
            label: 'PROFIT', 
            data: props.monthlyTrend.profit, 
            borderColor: '#ffffff', 
            backgroundColor: 'transparent',
            borderWidth: 2, 
            borderDash: [5, 5],
            tension: 0.4, 
            pointRadius: 0 
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#52525b', font: { size: 10, weight: '900' } } },
          x: { grid: { display: false }, ticks: { color: '#52525b', font: { size: 10, weight: '900' } } }
        }
      }
    })
  }

  // 2. Region Bar Chart
  if (props.byRegion?.length) {
    new Chart(regionChartRef.value, {
      type: 'bar',
      data: {
        labels: props.byRegion.map(r => r.region.toUpperCase()),
        datasets: [{
          data: props.byRegion.map(r => r.revenue),
          backgroundColor: '#d9ff00',
          borderRadius: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { display: false },
          x: { grid: { display: false }, ticks: { color: '#52525b', font: { size: 10, weight: '900' } } }
        }
      }
    })
  }

  // 3. Product Line Doughnut
  if (props.byLine?.length) {
    new Chart(productChartRef.value, {
      type: 'doughnut',
      data: {
        labels: props.byLine.map(p => p.product_line.toUpperCase()),
        datasets: [{
          data: props.byLine.map(p => p.revenue),
          backgroundColor: ['#d9ff00', '#ffffff', '#18181b', '#27272a', '#3f3f46'],
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

    <div class="mb-10 animate-slide-up">
      <p class="text-[10px] font-black uppercase tracking-[0.5em] text-[#d9ff00] mb-4">Strategic Operations</p>
      <h1 class="page-title-premium">STRATEGY HUB</h1>
      <p class="text-zinc-600 font-bold text-sm uppercase tracking-widest mt-4">High-level executive oversight and market optimization metrics.</p>
    </div>

    <!-- KPI STRIP -->
    <div v-if="summary" class="grid grid-cols-2 md:grid-cols-4 gap-px bg-white/5 mb-10 animate-slide-up" style="animation-delay: 0.1s">
      <div class="bg-black p-6 group">
        <p class="text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">YTD Revenue</p>
        <p class="text-2xl font-black italic tracking-tighter text-white font-header">{{ formatCurrency(summary.total_revenue) }}</p>
      </div>
      <div class="bg-black p-6 group">
        <p class="text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">Gross Profit</p>
        <p class="text-2xl font-black italic tracking-tighter text-[#d9ff00] font-header">{{ formatCurrency(summary.total_profit) }}</p>
      </div>
      <div class="bg-black p-6 group">
        <p class="text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">Units Shipped</p>
        <p class="text-2xl font-black italic tracking-tighter text-white font-header">{{ (summary.total_units || 0).toLocaleString() }}</p>
      </div>
      <div class="bg-black p-6 group">
        <p class="text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-4 group-hover:text-[#d9ff00] transition-colors">Capture Rate</p>
        <p class="text-2xl font-black italic tracking-tighter text-white font-header">{{ (summary.total_orders || 0).toLocaleString() }} TRX</p>
      </div>
    </div>

    <!-- SENTIMENT STRIP -->
    <div v-if="reviewSummary" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 animate-slide-up" style="animation-delay: 0.15s">
       <div class="card-premium p-6 flex items-center justify-between bg-black/40">
          <div>
            <p class="text-[8px] font-black text-zinc-600 uppercase tracking-widest mb-1">Consumer Sentiment</p>
            <p class="text-sm font-black text-white italic font-header">{{ reviewSummary.avg_rating }} ★ RATING</p>
          </div>
          <div class="h-8 w-8 rounded-full border border-[#d9ff00]/20 flex items-center justify-center">
             <div class="h-1.5 w-1.5 rounded-full bg-[#d9ff00] animate-pulse"></div>
          </div>
       </div>
       <div class="card-premium p-6 flex items-center justify-between bg-black/40">
          <div>
            <p class="text-[8px] font-black text-zinc-600 uppercase tracking-widest mb-1">Recommendation Rate</p>
            <p class="text-sm font-black text-white italic font-header">{{ ((reviewSummary.recommended / reviewSummary.total_reviews) * 100).toFixed(1) }}% YES</p>
          </div>
          <svg class="h-5 w-5 text-zinc-700" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" /></svg>
       </div>
       <div class="card-premium p-6 flex items-center justify-between bg-black/40 border-[#d9ff00]/20">
          <div>
            <p class="text-[8px] font-black text-zinc-600 uppercase tracking-widest mb-1">Market Fit Accuracy</p>
            <p class="text-sm font-black text-[#d9ff00] italic font-header">{{ reviewSummary.true_to_size }} TRUE NODES</p>
          </div>
          <svg class="h-5 w-5 text-[#d9ff00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
       </div>
    </div>

    <!-- MAIN GROWTH CHART -->
    <div class="card-premium p-6 mb-10 animate-slide-up" style="animation-delay: 0.2s">
       <div class="flex items-center justify-between mb-8">
          <div>
            <h3 class="text-xs font-black text-white uppercase tracking-[0.4em]">Revenue Flow Trajectory</h3>
            <p class="text-[8px] font-bold text-zinc-600 uppercase tracking-widest mt-1">Multi-cycle performance audit</p>
          </div>
          <div class="flex items-center gap-6">
             <div class="flex items-center gap-2">
                <div class="h-1 w-4 bg-[#d9ff00]"></div>
                <span class="text-[8px] font-black text-zinc-500 uppercase tracking-widest">REVENUE</span>
             </div>
             <div class="flex items-center gap-2">
                <div class="h-1 w-4 bg-white"></div>
                <span class="text-[8px] font-black text-zinc-500 uppercase tracking-widest">PROFIT</span>
             </div>
          </div>
       </div>
       <div class="h-80">
          <canvas ref="trendChartRef"></canvas>
       </div>
    </div>

    <!-- SUB-ANALYSIS GRID -->
    <div class="grid lg:grid-cols-2 gap-6 mb-10 animate-slide-up" style="animation-delay: 0.25s">
       <!-- Region Distribution -->
       <div class="card-premium p-6">
          <div class="flex items-center justify-between mb-8">
             <h3 class="text-xs font-black text-white uppercase tracking-[0.3em]">Regional Dominance</h3>
             <span class="text-[9px] font-black text-zinc-700 uppercase tracking-widest italic">Node Analysis</span>
          </div>
          <div class="h-48">
             <canvas ref="regionChartRef"></canvas>
          </div>
       </div>
       <!-- Product Mix -->
       <div class="card-premium p-6">
          <div class="flex items-center justify-between mb-8">
             <h3 class="text-xs font-black text-white uppercase tracking-[0.3em]">Product Line Mix</h3>
             <div class="h-2 w-2 rounded-full bg-[#d9ff00] animate-ping"></div>
          </div>
          <div class="h-48 relative">
             <canvas ref="productChartRef"></canvas>
             <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="text-center" v-if="byLine?.length">
                   <p class="text-[8px] font-black text-zinc-700 uppercase tracking-widest">Elite Line</p>
                   <p class="text-[10px] font-black text-white uppercase mt-0.5">{{ byLine[0].product_line }}</p>
                </div>
             </div>
          </div>
       </div>
    </div>

    <!-- TOP PERFORMER TABLE -->
    <div class="card-premium bg-black animate-slide-up" style="animation-delay: 0.3s">
      <div class="p-6 border-b border-white/5 flex items-center justify-between">
        <h3 class="text-xs font-black text-white uppercase tracking-[0.3em]">Elite Unit Performance</h3>
        <span class="badge-premium !text-[#d9ff00] !border-[#d9ff00]/20">Top 10 Nodes</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-white/[0.02]">
              <th class="px-6 py-4 text-[8px] font-black text-zinc-600 uppercase tracking-[0.3em]">Identifier</th>
              <th class="px-6 py-4 text-[8px] font-black text-zinc-600 uppercase tracking-[0.3em]">Yield (Rev)</th>
              <th class="px-6 py-4 text-[8px] font-black text-zinc-600 uppercase tracking-[0.3em]">Units Moved</th>
              <th class="px-6 py-4 text-[8px] font-black text-zinc-600 uppercase tracking-[0.3em]">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="(p, idx) in topProducts" :key="idx" class="hover:bg-white/[0.01] transition-colors group">
              <td class="px-6 py-5">
                 <div class="flex items-center gap-4">
                    <span class="text-xl font-black italic text-zinc-800 group-hover:text-white transition-colors font-header">0{{ idx + 1 }}</span>
                    <div>
                       <p class="text-[11px] font-black text-white uppercase tracking-tight line-clamp-1">{{ p.product_name }}</p>
                       <p class="text-[8px] font-bold text-zinc-600 uppercase tracking-widest">{{ p.product_line }}</p>
                    </div>
                 </div>
              </td>
              <td class="px-6 py-5 text-[10px] font-black text-[#d9ff00] italic font-header">{{ formatCurrency(p.revenue) }}</td>
              <td class="px-6 py-5 text-[10px] font-black text-white italic font-header">{{ p.units.toLocaleString() }}</td>
              <td class="px-6 py-5">
                 <span class="text-[8px] font-black px-3 py-1 bg-white/5 text-zinc-400 uppercase tracking-widest border border-white/5 group-hover:border-[#d9ff00]/30 group-hover:text-[#d9ff00] transition-all">High Yield</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>