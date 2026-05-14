<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import Chart from 'chart.js/auto'
import axios from 'axios'

// Manual Debounce Implementation
const debounce = (fn, delay) => {
  let timeoutId;
  return (...args) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn(...args), delay);
  };
};

const props = defineProps({
  years: Array,
  regions: Array,
  kpis: Array
})

const filters = ref({
  year: props.years[0] || new Date().getFullYear(),
  region: '',
})

const search = ref('')
const transactions = ref([])
const kpis = ref([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const loading = ref(false)

const lineRef = ref(null)
const barRef = ref(null)
const regionRef = ref(null)
const genderRef = ref(null)
const channelRef = ref(null)

const exportUrl = computed(() => {
  const params = new URLSearchParams(filters.value)
  return `/analyst/export?${params.toString()}`
})

const fetchAll = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/analyst/data', { 
      params: { 
        ...filters.value, 
        search: search.value, 
        page: pagination.value.current_page 
      } 
    })
    transactions.value = res.data.transactions.data
    kpis.value = res.data.kpis
    pagination.value = {
      current_page: res.data.transactions.current_page,
      last_page: res.data.transactions.last_page,
      total: res.data.transactions.total,
    }
    updateCharts(res.data.charts)
  } catch (e) {
    console.error('Data sync failed:', e)
  } finally {
    loading.value = false
  }
}

const debounceFetch = debounce(fetchAll, 500)

const goPage = (p) => {
  pagination.value.current_page = p
  fetchAll()
}

let charts = {}

const updateCharts = (data) => {
  const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { 
      legend: { display: false },
      tooltip: {
        backgroundColor: '#000',
        borderColor: '#d9ff00',
        borderWidth: 1,
        titleFont: { size: 10, weight: 'bold' },
        bodyFont: { size: 10 },
        padding: 10,
        cornerRadius: 0
      }
    },
    scales: {
      y: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: '#444', font: { size: 9, weight: '900' } } },
      x: { grid: { display: false }, ticks: { color: '#444', font: { size: 9, weight: '900' } } }
    }
  }

  // Line Chart
  if (charts.line) charts.line.destroy()
  charts.line = new Chart(lineRef.value, {
    type: 'line',
    data: {
      labels: data.trends.labels,
      datasets: [
        { label: 'Revenue', data: data.trends.revenue, borderColor: '#d9ff00', borderWidth: 4, tension: 0.1, pointRadius: 0, fill: false },
        { label: 'Profit', data: data.trends.profit, borderColor: '#ffffff', borderWidth: 2, tension: 0.1, pointRadius: 0, borderDash: [5,5] }
      ]
    },
    options: commonOptions
  })

  // Bar Chart
  if (charts.bar) charts.bar.destroy()
  charts.bar = new Chart(barRef.value, {
    type: 'bar',
    data: {
      labels: data.products.labels,
      datasets: [{ data: data.products.data, backgroundColor: '#d9ff00', borderRadius: 0 }]
    },
    options: commonOptions
  })

  // Pie Charts
  const pieOptions = { ...commonOptions, scales: { x: { display: false }, y: { display: false } }, plugins: { ...commonOptions.plugins, legend: { display: false } } }
  
  if (charts.region) charts.region.destroy()
  charts.region = new Chart(regionRef.value, {
    type: 'doughnut',
    data: { labels: data.regions.labels, datasets: [{ data: data.regions.data, backgroundColor: ['#d9ff00','#ffffff','#333','#666','#999'], borderWidth: 0, cutout: '80%' }] },
    options: pieOptions
  })

  if (charts.gender) charts.gender.destroy()
  charts.gender = new Chart(genderRef.value, {
    type: 'doughnut',
    data: { labels: data.gender.labels, datasets: [{ data: data.gender.data, backgroundColor: ['#ffffff','#d9ff00'], borderWidth: 0, cutout: '80%' }] },
    options: pieOptions
  })

  if (charts.channel) charts.channel.destroy()
  charts.channel = new Chart(channelRef.value, {
    type: 'doughnut',
    data: { labels: data.channel.labels, datasets: [{ data: data.channel.data, backgroundColor: ['#d9ff00','#ffffff','#444'], borderWidth: 0, cutout: '80%' }] },
    options: pieOptions
  })
}

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(val || 0)
}

onMounted(() => {
  fetchAll()
})
</script>

<template>
  <AppLayout>
    <Head title="Nike Intel Workbench" />

    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-10 animate-slide-up">
      <div>
        <p class="text-[10px] font-black uppercase tracking-[0.5em] text-[#d9ff00] mb-4">Performance Intel</p>
        <h1 class="page-title-premium">WORKBENCH</h1>
        <p class="text-zinc-600 font-bold text-sm uppercase tracking-widest mt-4">Multi-dimensional transaction auditing and pattern discovery.</p>
      </div>
      
      <!-- Filter Terminal -->
      <div class="flex items-center gap-1 p-1 bg-white/5 border border-white/5">
        <select v-model="filters.year" class="bg-black border-none text-[10px] font-black text-white focus:ring-0 cursor-pointer px-6 py-3 uppercase tracking-widest" @change="fetchAll">
          <option v-for="y in years" :key="y" :value="y">{{ y }} SERIES</option>
        </select>
        <select v-model="filters.region" class="bg-black border-none text-[10px] font-black text-white focus:ring-0 cursor-pointer px-6 py-3 uppercase tracking-widest" @change="fetchAll">
          <option value="">GLOBAL NODES</option>
          <option v-for="r in regions" :key="r" :value="r">{{ r.toUpperCase() }}</option>
        </select>
        <a :href="exportUrl" class="px-8 py-3 bg-[#d9ff00] text-black text-[10px] font-black uppercase tracking-[0.2em] hover:bg-white transition-colors">
          EXPORT
        </a>
      </div>
    </div>

    <!-- KPI STRIP -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-px bg-white/5 mb-10 animate-slide-up" style="animation-delay: 0.1s">
      <div v-for="k in kpis" :key="k.label" class="bg-black p-6">
        <p class="text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-4">{{ k.label }}</p>
        <p class="text-2xl font-black italic tracking-tighter text-white">{{ k.value }}</p>
      </div>
    </div>

    <!-- ANALYSIS GRID -->
    <div class="grid gap-6 lg:grid-cols-3 mb-10 animate-slide-up" style="animation-delay: 0.2s">
      <!-- Main Visualization -->
      <div class="lg:col-span-2 card-premium p-6 bg-black">
        <div class="flex justify-between items-center mb-10">
          <h3 class="text-xs font-black text-white uppercase tracking-[0.3em]">Revenue Flow Dynamics</h3>
          <div class="flex items-center gap-6">
             <div class="flex items-center gap-2">
                <div class="h-1 w-4 bg-[#d9ff00]"></div>
                <span class="text-[9px] font-black text-zinc-500 uppercase tracking-widest">REVENUE</span>
             </div>
             <div class="flex items-center gap-2">
                <div class="h-1 w-4 bg-white"></div>
                <span class="text-[9px] font-black text-zinc-500 uppercase tracking-widest">PROFIT</span>
             </div>
          </div>
        </div>
        <div class="h-[400px]">
          <canvas ref="lineRef"></canvas>
        </div>
      </div>

      <!-- Secondary Chart -->
      <div class="card-premium p-6 bg-black">
        <h3 class="text-xs font-black text-white uppercase tracking-[0.3em] mb-10">Product Line Performance</h3>
        <div class="h-[400px]">
          <canvas ref="barRef"></canvas>
        </div>
      </div>
    </div>

    <!-- SUB-METRICS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 animate-slide-up" style="animation-delay: 0.3s">
      <div class="card-premium p-6 bg-black text-center">
        <h4 class="text-[10px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-8">Region Distribution</h4>
        <div class="h-48 relative">
           <canvas ref="regionRef"></canvas>
           <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <p class="text-[9px] font-black text-white tracking-widest">LOCATIONS</p>
           </div>
        </div>
      </div>
      <div class="card-premium p-6 bg-black text-center">
        <h4 class="text-[10px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-8">Demographic Split</h4>
        <div class="h-48 relative">
           <canvas ref="genderRef"></canvas>
           <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <p class="text-[9px] font-black text-white tracking-widest">GENDER</p>
           </div>
        </div>
      </div>
      <div class="card-premium p-6 bg-black text-center">
        <h4 class="text-[10px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-8">Channel Efficiency</h4>
        <div class="h-48 relative">
           <canvas ref="channelRef"></canvas>
           <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <p class="text-[9px] font-black text-white tracking-widest">NETWORK</p>
           </div>
        </div>
      </div>
    </div>

    <!-- DATA TABLE TERMINAL -->
    <div class="card-premium bg-black overflow-hidden animate-slide-up" style="animation-delay: 0.4s">
      <div class="p-6 border-b border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
        <h3 class="text-xs font-black text-white uppercase tracking-[0.3em]">Atomic Transaction Log</h3>
        <div class="relative w-full md:w-80">
          <input 
            v-model="search" 
            @input="debounceFetch"
            type="text" 
            placeholder="SEARCH BY ORDER ID..." 
            class="w-full bg-white/5 border border-white/10 px-4 py-3 text-[10px] font-black text-white focus:border-[#d9ff00] outline-none transition-all placeholder:text-zinc-700 uppercase tracking-widest"
          />
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-white/[0.02]">
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Identifier</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Product Spec</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Region</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Units</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Revenue</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Timestamp</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="tx in transactions" :key="tx.order_id" class="hover:bg-white/[0.02] transition-colors group">
              <td class="px-8 py-6 text-[10px] font-black text-[#d9ff00] italic">{{ tx.order_id }}</td>
              <td class="px-8 py-6">
                <p class="text-[11px] font-black uppercase tracking-tight text-white">{{ tx.product_name }}</p>
                <p class="text-[9px] font-bold text-zinc-600 uppercase tracking-widest mt-1">{{ tx.product_line }}</p>
              </td>
              <td class="px-8 py-6 text-[10px] font-black text-zinc-400 uppercase tracking-widest">{{ tx.region }}</td>
              <td class="px-8 py-6 text-[11px] font-black text-white italic">{{ tx.units_sold }}</td>
              <td class="px-8 py-6 text-[11px] font-black text-white italic">{{ formatCurrency(tx.revenue) }}</td>
              <td class="px-8 py-6 text-[10px] font-bold text-zinc-600 uppercase tracking-widest">{{ tx.order_date }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION TERMINAL -->
      <div class="p-6 border-t border-white/5 flex justify-between items-center">
        <p class="text-[9px] font-black text-zinc-600 uppercase tracking-widest">Displaying Node {{ (pagination.current_page - 1) * 10 + 1 }} - {{ Math.min(pagination.current_page * 10, pagination.total) }} of {{ pagination.total }}</p>
        <div class="flex items-center gap-1">
          <button @click="goPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-4 py-2 bg-white/5 text-[9px] font-black hover:bg-[#d9ff00] hover:text-black disabled:opacity-20 transition-all uppercase tracking-widest">PREV</button>
          <div class="px-6 py-2 border border-white/5 text-[9px] font-black">{{ pagination.current_page }} / {{ pagination.last_page }}</div>
          <button @click="goPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="px-4 py-2 bg-white/5 text-[9px] font-black hover:bg-[#d9ff00] hover:text-black disabled:opacity-20 transition-all uppercase tracking-widest">NEXT</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>