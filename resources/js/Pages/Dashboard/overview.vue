<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { onMounted, ref } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  stats: Object,
  latest_transactions: Array,
  top_products: Array,
  review_stats: Object,
  charts: Object,
})

const trendChart = ref(null)
const regionChart = ref(null)
const channelChart = ref(null)
const reviewChart = ref(null)

const formatCurrency = (val) => {
  if (val === 'HIDDEN' || val === '***') return '••••••'
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(val || 0)
}

onMounted(() => {
  const isGuest = props.stats.is_guest;

  // Monthly Trend
  if (trendChart.value) {
  new Chart(trendChart.value, {
    type: 'line',
    data: {
      labels: props.charts.monthly.labels,
      datasets: [{
        label: isGuest ? 'Trend (Masked)' : 'Revenue',
        data: props.charts.monthly.data,
        borderColor: isGuest ? '#3f3f46' : '#d9ff00',
        backgroundColor: 'transparent',
        fill: false,
        tension: 0.5,
        borderWidth: 4,
        pointRadius: 0,
        pointHoverRadius: isGuest ? 0 : 8,
        pointHoverBackgroundColor: '#d9ff00',
        pointHoverBorderColor: '#000',
        pointHoverBorderWidth: 4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { 
        legend: { display: false },
        tooltip: { 
            enabled: !isGuest,
            backgroundColor: '#000',
            titleFont: { family: 'Outfit', weight: '900' },
            bodyFont: { family: 'Outfit' },
            padding: 15,
            displayColors: false,
            borderWidth: 1,
            borderColor: 'rgba(255,255,255,0.1)'
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#52525b', font: { weight: '900', size: 10 } } },
        y: { 
          grid: { color: 'rgba(255,255,255,0.05)', drawBorder: false }, 
          ticks: { 
            display: !isGuest,
            color: '#52525b', 
            font: { weight: '900', size: 10 } 
          } 
        }
      }
    }
    
  })
}

  // Region Split
  
  new Chart(regionChart.value, {
    type: 'doughnut',
    data: {
      labels: props.charts.region.labels,
      datasets: [{
        data: isGuest ? props.charts.region.data.map(() => 1) : props.charts.region.data,
        backgroundColor: isGuest ? ['#18181b', '#27272a', '#3f3f46', '#52525b', '#71717a'] : ['#d9ff00', '#ffffff', '#3f3f46', '#52525b', '#71717a'],
        borderWidth: 5,
        borderColor: '#000',
        hoverOffset: isGuest ? 0 : 30
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { 
        legend: { 
            position: 'bottom', 
            labels: { color: '#a1a1aa', usePointStyle: true, font: { weight: '900', size: 10, family: 'Outfit' }, padding: 20 } 
        },
        tooltip: { enabled: !isGuest }
      },
      cutout: '80%'
    }
  })

  // Channel Split
  new Chart(channelChart.value, {
    type: 'bar',
    data: {
      labels: props.charts.channel.labels,
      datasets: [{
        label: 'Volume',
        data: props.charts.channel.data,
        backgroundColor: isGuest ? '#18181b' : '#d9ff00',
        borderRadius: 0,
        barThickness: 12,
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { 
        legend: { display: false },
        tooltip: { enabled: !isGuest }
      },
      scales: {
        x: { 
            grid: { display: false }, 
            ticks: { display: !isGuest, color: '#52525b', font: { weight: '900' } } 
        },
        y: { grid: { display: false }, ticks: { color: '#a1a1aa', font: { weight: '900', size: 10 } } }
      }
    }
  })

  // Athlete Sentiment Doughnut (Optimized for Readability)
  new Chart(reviewChart.value, {
    type: 'doughnut',
    data: {
      labels: ['5 Star', '4 Star', '3 Star', '2 Star', '1 Star'],
      datasets: [{
        data: [
          props.charts.reviews.data[props.charts.reviews.labels.indexOf('5.0 Star')] || props.charts.reviews.data[props.charts.reviews.labels.indexOf('5 Star')] || 0,
          props.charts.reviews.data[props.charts.reviews.labels.indexOf('4.0 Star')] || props.charts.reviews.data[props.charts.reviews.labels.indexOf('4 Star')] || 0,
          props.charts.reviews.data[props.charts.reviews.labels.indexOf('3.0 Star')] || props.charts.reviews.data[props.charts.reviews.labels.indexOf('3 Star')] || 0,
          props.charts.reviews.data[props.charts.reviews.labels.indexOf('2.0 Star')] || props.charts.reviews.data[props.charts.reviews.labels.indexOf('2 Star')] || 0,
          props.charts.reviews.data[props.charts.reviews.labels.indexOf('1.0 Star')] || props.charts.reviews.data[props.charts.reviews.labels.indexOf('1 Star')] || 0,
        ],
        backgroundColor: [
          '#d9ff00', // 5 Star - Volt
          '#ffffff', // 4 Star - White
          '#a1a1aa', // 3 Star - Gray
          '#3f3f46', // 2 Star - Dark Gray
          '#e11d48', // 1 Star - Rose (Alert)
        ],
        borderWidth: 0,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { 
        legend: { 
          position: 'right', 
          labels: { 
            color: '#a1a1aa', 
            font: { weight: '900', size: 10, family: 'Outfit' },
            padding: 20,
            usePointStyle: true,
            pointStyle: 'rect'
          } 
        },
        tooltip: {
          backgroundColor: '#000',
          titleFont: { size: 10, weight: 'bold' },
          bodyFont: { size: 12, weight: 'black' },
          padding: 15,
          displayColors: true,
          borderColor: 'rgba(255,255,255,0.1)',
          borderWidth: 1
        }
      },
      cutout: '75%',
    }
  })
})
</script>

<template>
  <AppLayout>
    <Head title="Nike Intel Hub" />

    <!-- GUEST NOTICE -->
    <div v-if="stats.is_guest" class="mb-12 p-6 glass-effect flex items-center justify-between animate-slide-up">
        <div class="flex items-center gap-6">
            <div class="h-3 w-3 rounded-full bg-[#d9ff00] shadow-[0_0_15px_#d9ff00] animate-pulse"></div>
            <div>
                <p class="text-[11px] font-black text-[#d9ff00] uppercase tracking-[0.3em] mb-1 italic">Authorized Eyes Only</p>
                <p class="text-[9px] font-bold text-zinc-500 uppercase tracking-widest">Global financial data is currently masked. Please authenticate for full mission clearance.</p>
            </div>
        </div>
        <Link :href="route('login')" class="btn-premium !py-3 !px-8 !bg-[#d9ff00] !text-black hover:!bg-white">
          Authenticate
        </Link>
    </div>

    <!-- HERO SECTION -->
    <div class="mb-12 animate-slide-up" :class="{'opacity-40': stats.is_guest}">
      <div class="flex items-center gap-6 mb-8">
         <div class="h-[1px] w-20 bg-[#d9ff00]"></div>
         <p class="text-[10px] font-black text-[#d9ff00] uppercase tracking-[0.6em]">Just Do It. Data Driven.</p>
      </div>
      <h1 class="page-title-premium mb-8">Market <br/> <span class="text-stroke">Intelligence.</span></h1>
      <p class="max-w-3xl text-zinc-500 font-bold text-base md:text-xl leading-relaxed uppercase tracking-tight">
        Monitoring Nike's global heartbeat. Real-time visibility into sales volume, <br class="hidden md:block"/> product dominance, and the voice of our athletes worldwide.
      </p>
    </div>

    <!-- METRICS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 animate-slide-up" style="animation-delay: 0.1s">
      <!-- Revenue -->
      <div class="card-premium p-6 group relative overflow-hidden min-h-[160px] flex flex-col justify-between">
        <div class="absolute -top-2 -right-2 p-6 text-[40px] font-black text-white/5 italic select-none font-header">$$</div>
        <p class="text-[9px] font-black text-zinc-500 uppercase tracking-[0.4em] group-hover:text-[#d9ff00] transition-colors">Net Revenue</p>
        <div class="mt-4">
          <p class="text-3xl font-black italic tracking-tighter font-header leading-none">{{ formatCurrency(stats.total_revenue) }}</p>
          <p v-if="!stats.is_guest" class="text-[8px] font-bold text-zinc-700 uppercase tracking-widest mt-3 italic">AOV: {{ formatCurrency(stats.avg_order_value) }}</p>
        </div>
      </div>

      <!-- Profit (MEMBER ONLY) -->
      <div class="card-premium p-6 group relative overflow-hidden min-h-[160px] flex flex-col justify-between bg-[#d9ff00]/5 border-[#d9ff00]/20">
        <p class="text-[9px] font-black text-[#d9ff00] uppercase tracking-[0.4em]">Gross Profit</p>
        <div class="mt-4">
          <p class="text-3xl font-black italic tracking-tighter text-white font-header leading-none">{{ formatCurrency(stats.total_profit) }}</p>
          <p v-if="!stats.is_guest" class="text-[8px] font-bold text-[#d9ff00] uppercase tracking-widest mt-3 italic">MARGIN: {{ stats.profit_margin }}%</p>
          <p v-else class="text-[8px] font-bold text-zinc-700 uppercase tracking-widest mt-3 italic">CLEARANCE REQUIRED</p>
        </div>
      </div>

      <!-- Units -->
      <div class="card-premium p-6 group relative overflow-hidden min-h-[160px] flex flex-col justify-between border-white/5">
        <p class="text-[9px] font-black text-zinc-500 uppercase tracking-[0.4em] group-hover:text-[#d9ff00] transition-colors">Units Moved</p>
        <div class="mt-4">
          <p class="text-3xl font-black italic tracking-tighter text-white font-header leading-none">{{ (stats.total_units || 0).toLocaleString() }}</p>
          <p class="text-[8px] font-bold text-zinc-700 uppercase tracking-widest mt-3 italic">NODES ACTIVE: {{ stats.total_orders.toLocaleString() }}</p>
        </div>
      </div>

      <!-- Athlete Satisfaction -->
      <div class="card-premium p-6 group relative overflow-hidden min-h-[160px] flex flex-col justify-between bg-[#d9ff00] !border-none">
        <p class="text-[9px] font-black text-black/40 uppercase tracking-[0.4em]">Consumer Sentiment</p>
        <div class="mt-4">
          <p class="text-3xl font-black italic tracking-tighter text-black font-header leading-none">{{ review_stats.avg_rating }}</p>
          <p class="text-[8px] font-bold text-black/60 uppercase tracking-widest mt-3 italic">{{ review_stats.total_reviews.toLocaleString() }} FEEDBACK NODES</p>
        </div>
      </div>
    </div>

    <!-- MAIN CHARTS (REVENUE HIDDEN FOR GUESTS) -->
    <div v-if="!stats.is_guest" class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10 animate-slide-up" style="animation-delay: 0.2s">
      <!-- Monthly Trend -->
      <div class="lg:col-span-2 card-premium p-6 h-[400px] flex flex-col">
        <div class="flex items-center justify-between mb-10">
          <div>
            <h3 class="text-[10px] font-black uppercase tracking-[0.4em] text-white mb-1">Revenue Trajectory</h3>
            <p class="text-[8px] font-bold text-zinc-600 uppercase tracking-[0.2em]">Live monthly performance index</p>
          </div>
          <span class="badge-premium !text-[#d9ff00] !border-[#d9ff00]/30">Global Sync</span>
        </div>
        <div class="flex-1 min-h-0">
          <canvas ref="trendChart"></canvas>
        </div>
      </div>

      <!-- Region Split -->
      <div class="card-premium p-6 h-[400px] flex flex-col">
        <h3 class="text-[10px] font-black uppercase tracking-[0.4em] text-white mb-10 text-center">Regional Dominance</h3>
        <div class="flex-1 min-h-0">
          <canvas ref="regionChart"></canvas>
        </div>
      </div>
    </div>

    <!-- REVIEW & CHANNEL SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 mb-20 animate-slide-up" style="animation-delay: 0.3s">
        <!-- Athlete Sentiment Doughnut (Sleek & Professional) -->
        <div class="card-premium p-10 h-[500px] flex flex-col" :class="{'lg:col-span-3': stats.is_guest}">
          <div class="flex items-center justify-between mb-10">
            <h3 class="text-xs font-black uppercase tracking-[0.4em] text-white">Sentiment Audit</h3>
            <span class="text-[9px] font-black text-[#d9ff00] bg-[#d9ff00]/10 px-3 py-1 border border-[#d9ff00]/20">PROPORTIONAL</span>
          </div>
          <div class="flex-1 min-h-0 flex items-center justify-center relative">
             <canvas ref="reviewChart"></canvas>
          </div>
          <div class="mt-8 pt-6 border-t border-white/5">
             <p class="text-[8px] font-bold text-zinc-700 uppercase tracking-widest text-center">
               Interact with legend to filter audit sectors
             </p>
          </div>
        </div>

        <!-- Channel Split (MEMBER ONLY) -->
        <div v-if="!stats.is_guest" class="lg:col-span-2 card-premium p-10 h-[500px] flex flex-col">
            <h3 class="text-[10px] font-black uppercase tracking-[0.4em] text-white mb-10">Channel Distribution</h3>
            <div class="flex-1 min-h-0">
                <canvas ref="channelChart"></canvas>
            </div>
        </div>
    </div>

    <!-- BOTTOM SECTION -->
    <div class="grid lg:grid-cols-3 gap-10 animate-slide-up" style="animation-delay: 0.4s">
      <!-- Top Performer Products -->
      <div>
        <div class="flex items-center gap-4 mb-10">
            <div class="h-8 w-1 bg-[#d9ff00]"></div>
            <h3 class="text-2xl font-black italic uppercase tracking-tighter font-header">Elite Performers</h3>
        </div>
        <div class="space-y-6">
          <div v-for="(product, idx) in top_products" :key="idx" 
               class="flex items-center justify-between p-4 glass-effect group hover:bg-[#d9ff00]/10 transition-all cursor-pointer">
            <div class="flex items-center gap-6">
               <span class="text-3xl font-black italic text-zinc-900 group-hover:text-[#d9ff00] transition-colors font-header">0{{ idx + 1 }}</span>
               <div>
                 <p class="text-xs font-black uppercase tracking-tight group-hover:text-white">{{ product.product_name }}</p>
                 <p class="text-[9px] font-bold text-zinc-700 uppercase tracking-widest mt-1">{{ product.product_line }}</p>
               </div>
            </div>
            <div class="text-right">
              <p class="text-base font-black italic font-header">{{ (product.units).toLocaleString() }}</p>
              <p class="text-[8px] font-bold text-zinc-700 uppercase tracking-widest">UNITS</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Latest Orders -->
      <div class="lg:col-span-2 flex flex-col">
        <div class="flex items-center justify-between mb-10">
          <div class="flex items-center gap-4">
              <div class="h-8 w-1 bg-white/20"></div>
              <h3 class="text-2xl font-black italic uppercase tracking-tighter font-header">Live Mission Feed</h3>
          </div>
          <Link v-if="stats.is_guest" :href="route('login')" class="text-[9px] font-black text-[#d9ff00] hover:text-white uppercase tracking-[0.2em] border-b border-[#d9ff00] pb-1 transition-all">Clearance Required</Link>
        </div>
        <div class="grid md:grid-cols-2 gap-6">
          <div v-for="tx in latest_transactions" :key="tx.order_id" 
               class="flex items-center justify-between p-4 bg-white/[0.02] border-l-2 border-white/10 hover:border-[#d9ff00] transition-all group cursor-crosshair">
            <div class="flex items-center gap-6">
               <div class="text-[9px] font-black text-zinc-700 italic font-header group-hover:text-[#d9ff00]">#{{ tx.order_id.toString().slice(-4) }}</div>
               <div class="overflow-hidden">
                 <p class="text-[12px] font-black uppercase tracking-tight text-zinc-400 group-hover:text-white truncate">{{ tx.product_name }}</p>
                 <p class="text-[9px] text-zinc-600 uppercase tracking-widest mt-1">{{ tx.region }}</p>
               </div>
            </div>
            <p v-if="!stats.is_guest" class="text-sm font-black italic text-[#d9ff00] font-header">{{ formatCurrency(tx.revenue) }}</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

