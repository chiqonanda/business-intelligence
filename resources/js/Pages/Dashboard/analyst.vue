<template>
  <AppLayout>
    <div class="analyst-page">

      <!-- Header -->
      <div class="page-header">
        <div>
          <p class="page-eyebrow">DATA ANALYST</p>
          <h1 class="page-title">ANALYTICS</h1>
        </div>
        <div class="header-actions">
          <select v-model="filters.year" class="filter-select" @change="fetchAll">
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
          <select v-model="filters.region" class="filter-select" @change="fetchAll">
            <option value="">Semua Region</option>
            <option v-for="r in regions" :key="r" :value="r">{{ r }}</option>
          </select>
          <select v-model="filters.channel" class="filter-select" @change="fetchAll">
            <option value="">Semua Channel</option>
            <option value="Online">Online</option>
            <option value="Offline">Offline</option>
            <option value="Wholesale">Wholesale</option>
          </select>
          <a :href="exportUrl" class="btn-export">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            EXPORT CSV
          </a>
        </div>
      </div>

      <!-- KPI Strip -->
      <div class="kpi-strip">
        <div class="kpi-item" v-for="k in kpis" :key="k.label">
          <span class="kpi-label">{{ k.label }}</span>
          <span class="kpi-val">{{ k.value }}</span>
        </div>
      </div>

      <!-- Charts Row 1 -->
      <div class="charts-row-2">
        <div class="chart-card">
          <div class="chart-header">
            <div>
              <p class="chart-label">REVENUE & PROFIT TREND</p>
              <p class="chart-sub">Bulan {{ filters.year }}</p>
            </div>
          </div>
          <canvas ref="lineRef" height="90"></canvas>
        </div>

        <div class="chart-card">
          <div class="chart-header">
            <div>
              <p class="chart-label">TOP 10 PRODUK</p>
              <p class="chart-sub">Berdasarkan revenue</p>
            </div>
          </div>
          <canvas ref="barRef" height="90"></canvas>
        </div>
      </div>

      <!-- Charts Row 2 -->
      <div class="charts-row-3">
        <div class="chart-card">
          <p class="chart-label">REGION SPLIT</p>
          <canvas ref="regionRef" height="160"></canvas>
        </div>
        <div class="chart-card">
          <p class="chart-label">GENDER SPLIT</p>
          <canvas ref="genderRef" height="160"></canvas>
        </div>
        <div class="chart-card">
          <p class="chart-label">CHANNEL SPLIT</p>
          <canvas ref="channelRef" height="160"></canvas>
        </div>
      </div>

      <!-- Tabel Transaksi -->
      <div class="table-card">
        <div class="table-header">
          <div>
            <p class="chart-label">TABEL TRANSAKSI</p>
            <p class="chart-sub">{{ pagination.total }} records ditemukan</p>
          </div>
          <div class="table-search">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
              v-model="search"
              type="text"
              placeholder="Cari order ID..."
              class="search-input"
              @input="debounceFetch"
            />
          </div>
        </div>

        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>ORDER ID</th>
                <th>TANGGAL</th>
                <th>PRODUK</th>
                <th>REGION</th>
                <th>CHANNEL</th>
                <th>UNITS</th>
                <th class="text-right">REVENUE</th>
                <th class="text-right">PROFIT</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="8" class="loading-row">
                  <div class="loading-dots">
                    <span></span><span></span><span></span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="transactions.length === 0">
                <td colspan="8" class="empty-row">Tidak ada data ditemukan.</td>
              </tr>
              <tr v-else v-for="row in transactions" :key="row.id" class="data-row">
                <td class="mono">{{ row.order_id }}</td>
                <td>{{ row.waktu?.order_date }}</td>
                <td>
                  <span class="product-name">{{ row.produk?.product_name }}</span>
                  <span class="product-line">{{ row.produk?.product_line }}</span>
                </td>
                <td>{{ row.pelanggan?.region }}</td>
                <td>
                  <span class="channel-badge" :class="'ch-' + (row.sales_channel || '').toLowerCase()">
                    {{ row.sales_channel }}
                  </span>
                </td>
                <td>{{ row.units_sold }}</td>
                <td class="text-right mono">{{ formatCurrency(row.revenue) }}</td>
                <td class="text-right mono" :class="row.profit < 0 ? 'negative' : 'positive'">
                  {{ formatCurrency(row.profit) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <button
            class="page-btn"
            :disabled="pagination.current_page <= 1"
            @click="goPage(pagination.current_page - 1)"
          >‹</button>

          <span class="page-info">
            Halaman {{ pagination.current_page }} / {{ pagination.last_page }}
          </span>

          <button
            class="page-btn"
            :disabled="pagination.current_page >= pagination.last_page"
            @click="goPage(pagination.current_page + 1)"
          >›</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'
import Chart from 'chart.js/auto'

// ── State ─────────────────────────────────────────────────────────────────────
const filters = ref({ year: new Date().getFullYear(), region: '', channel: '' })
const years   = [2024, 2025]
const regions = ref([])
const search  = ref('')
const loading = ref(false)

const transactions = ref([])
const pagination   = ref({ current_page: 1, last_page: 1, total: 0 })

const kpis = ref([
  { label: 'TOTAL REVENUE',  value: '-' },
  { label: 'TOTAL PROFIT',   value: '-' },
  { label: 'TOTAL ORDERS',   value: '-' },
  { label: 'AVG ORDER VALUE',value: '-' },
  { label: 'PROFIT MARGIN',  value: '-' },
])

// Chart refs & instances
const lineRef    = ref(null)
const barRef     = ref(null)
const regionRef  = ref(null)
const genderRef  = ref(null)
const channelRef = ref(null)
let lineChart = null, barChart = null, regionChart = null, genderChart = null, channelChart = null

// ── Computed ──────────────────────────────────────────────────────────────────
const exportUrl = computed(() => {
  const p = new URLSearchParams({ year: filters.value.year })
  return `/analyst/export?${p}`
})

// ── Formatters ────────────────────────────────────────────────────────────────
function formatCurrency(n) {
  if (n === null || n === undefined) return '-'
  const abs = Math.abs(n)
  const str = abs >= 1_000_000 ? 'Rp ' + (abs / 1_000_000).toFixed(1) + 'M'
            : abs >= 1_000     ? 'Rp ' + (abs / 1_000).toFixed(0) + 'K'
            : 'Rp ' + Math.round(abs)
  return n < 0 ? '-' + str : str
}

function formatNumber(n) {
  return new Intl.NumberFormat('id-ID').format(n || 0)
}

// ── Chart helpers ─────────────────────────────────────────────────────────────
const GRID   = 'rgba(255,255,255,0.06)'
const TICK   = 'rgba(255,255,255,0.3)'
const DONUT_COLORS = ['#ffffff','rgba(255,255,255,0.55)','rgba(255,255,255,0.3)','rgba(255,255,255,0.15)','rgba(255,255,255,0.08)']

function baseTooltip() {
  return {
    backgroundColor: '#1a1a1a',
    borderColor: 'rgba(255,255,255,0.1)',
    borderWidth: 1,
    titleColor: '#fff',
    bodyColor: 'rgba(255,255,255,0.6)',
  }
}

function buildLine(data) {
  if (lineChart) lineChart.destroy()
  lineChart = new Chart(lineRef.value, {
    type: 'line',
    data: {
      labels: data.map(d => d.nama_bulan?.slice(0,3)),
      datasets: [
        {
          label: 'Revenue',
          data: data.map(d => d.revenue),
          borderColor: '#fff',
          backgroundColor: 'rgba(255,255,255,0.05)',
          borderWidth: 2, pointRadius: 3, fill: true, tension: 0.4,
        },
        {
          label: 'Profit',
          data: data.map(d => d.profit),
          borderColor: '#86efac',
          backgroundColor: 'rgba(134,239,172,0.04)',
          borderWidth: 1.5, pointRadius: 2, fill: true, tension: 0.4, borderDash: [4,3],
        },
      ],
    },
    options: {
      responsive: true,
      plugins: { legend: { labels: { color: TICK, font: { size: 11 }, boxWidth: 16 } }, tooltip: { ...baseTooltip(), callbacks: { label: c => ' ' + formatCurrency(c.raw) } } },
      scales: {
        x: { grid: { color: GRID }, ticks: { color: TICK, font: { size: 10 } } },
        y: { grid: { color: GRID }, ticks: { color: TICK, font: { size: 10 }, callback: v => formatCurrency(v) } },
      },
    },
  })
}

function buildBar(data) {
  if (barChart) barChart.destroy()
  barChart = new Chart(barRef.value, {
    type: 'bar',
    data: {
      labels: data.map(d => d.product_name?.length > 14 ? d.product_name.slice(0,14) + '…' : d.product_name),
      datasets: [{
        label: 'Revenue',
        data: data.map(d => d.revenue),
        backgroundColor: 'rgba(255,255,255,0.12)',
        borderColor: 'rgba(255,255,255,0.3)',
        borderWidth: 1,
        borderRadius: 3,
        hoverBackgroundColor: 'rgba(255,255,255,0.22)',
      }],
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      plugins: { legend: { display: false }, tooltip: { ...baseTooltip(), callbacks: { label: c => ' ' + formatCurrency(c.raw) } } },
      scales: {
        x: { grid: { color: GRID }, ticks: { color: TICK, font: { size: 10 }, callback: v => formatCurrency(v) } },
        y: { grid: { color: 'transparent' }, ticks: { color: TICK, font: { size: 10 } } },
      },
    },
  })
}

function buildDonut(ref_, data, labels, colorKey) {
  const instances = { regionChart, genderChart, channelChart }
  if (ref_ === regionRef  && regionChart)  regionChart.destroy()
  if (ref_ === genderRef  && genderChart)  genderChart.destroy()
  if (ref_ === channelRef && channelChart) channelChart.destroy()

  const chart = new Chart(ref_.value, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: DONUT_COLORS.slice(0, data.length),
        borderWidth: 0,
        hoverOffset: 4,
      }],
    },
    options: {
      cutout: '68%',
      plugins: {
        legend: { position: 'bottom', labels: { color: TICK, font: { size: 10 }, boxWidth: 10 } },
        tooltip: { ...baseTooltip(), callbacks: { label: c => ' ' + formatCurrency(c.raw) } },
      },
    },
  })
  if (ref_ === regionRef)  regionChart  = chart
  if (ref_ === genderRef)  genderChart  = chart
  if (ref_ === channelRef) channelChart = chart
}

// ── Data fetchers ─────────────────────────────────────────────────────────────
async function fetchRevenueTrend() {
  try {
    const res = await axios.get('/api/chart/revenue-trend', { params: { year: filters.value.year } })
    await nextTick(); buildLine(res.data)

    // Update KPIs dari trend data
    const totalRev    = res.data.reduce((s, d) => s + +d.revenue, 0)
    const totalProfit = res.data.reduce((s, d) => s + +d.profit, 0)
    kpis.value[0].value = formatCurrency(totalRev)
    kpis.value[1].value = formatCurrency(totalProfit)
    kpis.value[4].value = totalRev ? (totalProfit / totalRev * 100).toFixed(1) + '%' : '-'
  } catch {}
}

async function fetchTopProducts() {
  try {
    const res = await axios.get('/api/chart/top-products', { params: { limit: 10 } })
    await nextTick(); buildBar(res.data)
  } catch {}
}

async function fetchRegionSplit() {
  try {
    const res = await axios.get('/api/chart/region-split')
    regions.value = res.data.map(d => d.region).filter(Boolean)
    await nextTick(); buildDonut(regionRef, res.data.map(d => d.revenue), res.data.map(d => d.region))
  } catch {}
}

async function fetchGenderSplit() {
  try {
    const res = await axios.get('/api/chart/gender-split')
    await nextTick(); buildDonut(genderRef, res.data.map(d => d.revenue), res.data.map(d => d.gender_category))
  } catch {}
}

async function fetchChannelSplit() {
  try {
    const res = await axios.get('/api/chart/channel-split')
    await nextTick(); buildDonut(channelRef, res.data.map(d => d.revenue), res.data.map(d => d.sales_channel))
  } catch {}
}

async function fetchTransactions(page = 1) {
  loading.value = true
  try {
    const res = await axios.get('/api/transactions', {
      params: { ...filters.value, search: search.value, page },
    })
    transactions.value = res.data.data
    pagination.value   = {
      current_page: res.data.current_page,
      last_page:    res.data.last_page,
      total:        res.data.total,
    }
    kpis.value[2].value = formatNumber(res.data.total)
    const avgVal = res.data.total > 0
      ? (res.data.data.reduce((s, d) => s + +d.revenue, 0) / res.data.data.length)
      : 0
    kpis.value[3].value = formatCurrency(avgVal)
  } catch {} finally {
    loading.value = false
  }
}

let debounceTimer = null
function debounceFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchTransactions(1), 400)
}

function goPage(page) { fetchTransactions(page) }

function fetchAll() {
  fetchRevenueTrend()
  fetchTopProducts()
  fetchRegionSplit()
  fetchGenderSplit()
  fetchChannelSplit()
  fetchTransactions(1)
}

onMounted(fetchAll)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&family=JetBrains+Mono:wght@400&display=swap');

.analyst-page { display: flex; flex-direction: column; gap: 1.25rem; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

.page-header { display: flex; align-items: flex-end; justify-content: space-between; }
.page-eyebrow { font-size: 11px; letter-spacing: 3px; color: rgba(255,255,255,0.25); margin-bottom: 0.25rem; }
.page-title { font-family: 'Bebas Neue', sans-serif; font-size: 42px; color: #fff; letter-spacing: 1px; line-height: 1; }

.header-actions { display: flex; align-items: center; gap: 8px; }

.filter-select {
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px;
  padding: 7px 12px; font-size: 12px; color: rgba(255,255,255,0.6);
  font-family: 'DM Sans', sans-serif; outline: none; cursor: pointer;
}

.btn-export {
  display: flex; align-items: center; gap: 7px;
  background: #fff; color: #000; border: none; border-radius: 4px;
  padding: 7px 14px; font-family: 'Bebas Neue', sans-serif;
  font-size: 14px; letter-spacing: 2px; text-decoration: none;
  cursor: pointer; transition: background 0.15s;
}
.btn-export:hover { background: #e5e5e5; }

/* KPI Strip */
.kpi-strip {
  display: grid; grid-template-columns: repeat(5, 1fr);
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; overflow: hidden;
}
.kpi-item {
  display: flex; flex-direction: column; gap: 4px;
  padding: 1rem 1.25rem; border-right: 1px solid rgba(255,255,255,0.07);
}
.kpi-item:last-child { border-right: none; }
.kpi-label { font-size: 10px; letter-spacing: 2px; color: rgba(255,255,255,0.25); }
.kpi-val { font-family: 'Bebas Neue', sans-serif; font-size: 24px; color: #fff; letter-spacing: 0.5px; }

/* Charts */
.charts-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.charts-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }

.chart-card {
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; padding: 1.25rem;
}
.chart-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; }
.chart-label { font-size: 10px; letter-spacing: 2.5px; color: rgba(255,255,255,0.3); margin-bottom: 2px; }
.chart-sub { font-size: 12px; color: rgba(255,255,255,0.35); }

/* Table */
.table-card {
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; overflow: hidden;
}
.table-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.07);
}
.table-search {
  display: flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 4px; padding: 6px 12px; color: rgba(255,255,255,0.3);
}
.search-input {
  background: transparent; border: none; outline: none;
  font-size: 13px; color: #fff; font-family: 'DM Sans', sans-serif; width: 180px;
}
.search-input::placeholder { color: rgba(255,255,255,0.25); }

.table-wrap { overflow-x: auto; }

.data-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.data-table thead tr { border-bottom: 1px solid rgba(255,255,255,0.07); }
.data-table th {
  padding: 10px 14px; text-align: left;
  font-size: 10px; letter-spacing: 1.5px; color: rgba(255,255,255,0.25);
  font-weight: 500; white-space: nowrap;
}
.data-table .text-right { text-align: right; }
.data-row { border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.1s; }
.data-row:hover { background: rgba(255,255,255,0.03); }
.data-table td { padding: 10px 14px; color: rgba(255,255,255,0.6); }

.mono { font-family: 'JetBrains Mono', monospace; font-size: 11px; }
.positive { color: #86efac; }
.negative { color: #fca5a5; }

.product-name { display: block; color: rgba(255,255,255,0.7); }
.product-line { display: block; font-size: 10px; color: rgba(255,255,255,0.25); margin-top: 1px; }

.channel-badge {
  font-size: 10px; letter-spacing: 1px; padding: 2px 7px; border-radius: 3px;
}
.ch-online    { background: rgba(59,130,246,0.15);  color: #93c5fd; }
.ch-offline   { background: rgba(234,179,8,0.15);   color: #fde047; }
.ch-wholesale { background: rgba(139,92,246,0.15);  color: #c4b5fd; }

.loading-row, .empty-row { text-align: center; padding: 2rem; color: rgba(255,255,255,0.2); }
.loading-dots { display: flex; justify-content: center; gap: 6px; }
.loading-dots span { width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,0.3); animation: bounce 1.2s infinite; }
.loading-dots span:nth-child(2) { animation-delay: 0.2s; }
.loading-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes bounce { 0%,80%,100% { transform: translateY(0); opacity: 0.3; } 40% { transform: translateY(-6px); opacity: 1; } }

/* Pagination */
.pagination {
  display: flex; align-items: center; justify-content: center; gap: 1rem;
  padding: 1rem; border-top: 1px solid rgba(255,255,255,0.07);
}
.page-btn {
  background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 4px; width: 30px; height: 30px; color: rgba(255,255,255,0.6);
  cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center;
  transition: all 0.15s;
}
.page-btn:hover:not(:disabled) { background: rgba(255,255,255,0.12); color: #fff; }
.page-btn:disabled { opacity: 0.3; cursor: not-allowed; }
.page-info { font-size: 12px; color: rgba(255,255,255,0.3); }
</style>