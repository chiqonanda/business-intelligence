<template>
  <AppLayout>
    <div class="manager-page">

      <!-- Header -->
      <div class="page-header">
        <div>
          <p class="page-eyebrow">BUSINESS INSIGHT</p>
          <h1 class="page-title">MANAGER VIEW</h1>
        </div>
        <span class="readonly-badge">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          READ ONLY
        </span>
      </div>

      <!-- Kuartal cards -->
      <div class="quarter-grid">
        <div class="quarter-card" v-for="q in quarters" :key="q.kuartal">
          <div class="q-top">
            <span class="q-label">Q{{ q.kuartal }}</span>
            <span class="q-year">{{ currentYear }}</span>
          </div>
          <div class="q-revenue">{{ formatCurrency(q.revenue) }}</div>
          <div class="q-profit">
            <span class="profit-label">Profit</span>
            <span class="profit-val" :class="q.profit >= 0 ? 'pos' : 'neg'">
              {{ formatCurrency(q.profit) }}
            </span>
          </div>
          <div class="q-bar-wrap">
            <div class="q-bar" :style="{ width: qBarWidth(q.revenue) + '%' }"></div>
          </div>
        </div>
      </div>

      <!-- Charts -->
      <div class="charts-row">

        <!-- Revenue by product line -->
        <div class="chart-card wide">
          <div class="chart-header">
            <div>
              <p class="chart-label">REVENUE BY PRODUCT LINE</p>
              <p class="chart-sub">Footwear · Apparel · Equipment</p>
            </div>
          </div>
          <canvas ref="lineChartRef" height="80"></canvas>
        </div>

        <!-- Region donut -->
        <div class="chart-card narrow">
          <p class="chart-label">REVENUE BY REGION</p>
          <canvas ref="regionChartRef" height="180"></canvas>
        </div>
      </div>

      <!-- Insight cards -->
      <div class="insight-row">

        <!-- Top product lines tabel -->
        <div class="insight-card">
          <p class="chart-label">PRODUCT LINE PERFORMANCE</p>
          <div class="product-lines">
            <div v-for="(pl, i) in productLines" :key="pl.product_line" class="pl-row">
              <span class="pl-rank">{{ String(i+1).padStart(2,'0') }}</span>
              <div class="pl-info">
                <span class="pl-name">{{ pl.product_line || 'Lainnya' }}</span>
                <div class="pl-bar-wrap">
                  <div class="pl-bar" :style="{ width: plBarWidth(pl.revenue) + '%' }"></div>
                </div>
              </div>
              <span class="pl-revenue">{{ formatCurrency(pl.revenue) }}</span>
            </div>
          </div>
        </div>

        <!-- Rekomendasi bisnis -->
        <div class="insight-card rekomendasi">
          <p class="chart-label">REKOMENDASI BISNIS</p>
          <div class="rekom-list">
            <div v-for="r in rekomendasi" :key="r.id" class="rekom-item">
              <div class="rekom-icon" :class="'icon-' + r.type">{{ r.icon }}</div>
              <div class="rekom-content">
                <p class="rekom-title">{{ r.title }}</p>
                <p class="rekom-desc">{{ r.desc }}</p>
              </div>
              <span class="rekom-priority" :class="'priority-' + r.priority">{{ r.priority }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Chart from 'chart.js/auto'

const props = defineProps({ summary: Object })

const currentYear  = new Date().getFullYear()
const lineChartRef = ref(null)
const regionChartRef = ref(null)
let lineChart = null, regionChart = null

// ── Data dari props ───────────────────────────────────────────────────────────
const quarters    = computed(() => props.summary?.revenue_by_quarter || [])
const productLines = computed(() => props.summary?.top_product_lines || [])
const regionData  = computed(() => props.summary?.revenue_by_region  || [])

const maxQRevenue = computed(() => Math.max(...quarters.value.map(q => +q.revenue), 1))
const maxPlRevenue = computed(() => Math.max(...productLines.value.map(p => +p.revenue), 1))

function qBarWidth(val)  { return Math.max(4, (+val / maxQRevenue.value) * 100) }
function plBarWidth(val) { return Math.max(4, (+val / maxPlRevenue.value) * 100) }

// ── Rekomendasi statis berbasis data ──────────────────────────────────────────
const rekomendasi = [
  {
    id: 1, type: 'up', icon: '↑',
    title: 'Tingkatkan stok Footwear',
    desc:  'Product line Footwear konsisten menjadi kategori terlaris. Pertimbangkan ekspansi varian.',
    priority: 'HIGH',
  },
  {
    id: 2, type: 'target', icon: '◎',
    title: 'Fokus ke channel Online',
    desc:  'Channel online menunjukkan pertumbuhan lebih cepat. Alokasi marketing perlu ditingkatkan.',
    priority: 'MED',
  },
  {
    id: 3, type: 'warn', icon: '!',
    title: 'Evaluasi diskon Q2',
    desc:  'Profit margin Q2 lebih rendah dibanding Q1 & Q3. Review kebijakan diskon periode tersebut.',
    priority: 'LOW',
  },
  {
    id: 4, type: 'info', icon: 'i',
    title: 'Ekspansi region baru',
    desc:  'Beberapa region masih underperformed. Pertimbangkan kampanye targeted di wilayah tersebut.',
    priority: 'MED',
  },
]

function formatCurrency(n) {
  if (n === null || n === undefined) return '-'
  const abs = Math.abs(+n)
  const str = abs >= 1_000_000 ? 'Rp ' + (abs / 1_000_000).toFixed(1) + 'M'
            : abs >= 1_000     ? 'Rp ' + (abs / 1_000).toFixed(0) + 'K'
            : 'Rp ' + Math.round(abs)
  return +n < 0 ? '-' + str : str
}

// ── Charts ────────────────────────────────────────────────────────────────────
const GRID = 'rgba(255,255,255,0.06)'
const TICK = 'rgba(255,255,255,0.3)'

function buildLineChart() {
  if (!lineChartRef.value || !productLines.value.length) return
  if (lineChart) lineChart.destroy()

  lineChart = new Chart(lineChartRef.value, {
    type: 'bar',
    data: {
      labels: productLines.value.map(p => p.product_line || 'Lainnya'),
      datasets: [{
        label: 'Revenue',
        data: productLines.value.map(p => p.revenue),
        backgroundColor: productLines.value.map((_, i) =>
          i === 0 ? 'rgba(255,255,255,0.8)' : `rgba(255,255,255,${0.25 - i * 0.05})`
        ),
        borderRadius: 4,
        borderWidth: 0,
      }],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1a1a1a', borderColor: 'rgba(255,255,255,0.1)', borderWidth: 1,
          titleColor: '#fff', bodyColor: 'rgba(255,255,255,0.6)',
          callbacks: { label: c => ' ' + formatCurrency(c.raw) },
        },
      },
      scales: {
        x: { grid: { color: GRID }, ticks: { color: TICK, font: { size: 11 } } },
        y: { grid: { color: GRID }, ticks: { color: TICK, font: { size: 11 }, callback: v => formatCurrency(v) } },
      },
    },
  })
}

function buildRegionChart() {
  if (!regionChartRef.value || !regionData.value.length) return
  if (regionChart) regionChart.destroy()

  const colors = ['#ffffff','rgba(255,255,255,0.55)','rgba(255,255,255,0.35)','rgba(255,255,255,0.2)','rgba(255,255,255,0.1)']

  regionChart = new Chart(regionChartRef.value, {
    type: 'doughnut',
    data: {
      labels: regionData.value.map(r => r.region),
      datasets: [{
        data: regionData.value.map(r => r.revenue),
        backgroundColor: colors.slice(0, regionData.value.length),
        borderWidth: 0, hoverOffset: 4,
      }],
    },
    options: {
      cutout: '68%',
      plugins: {
        legend: { position: 'bottom', labels: { color: TICK, font: { size: 10 }, boxWidth: 10 } },
        tooltip: {
          backgroundColor: '#1a1a1a', borderColor: 'rgba(255,255,255,0.1)', borderWidth: 1,
          titleColor: '#fff', bodyColor: 'rgba(255,255,255,0.6)',
          callbacks: { label: c => ' ' + formatCurrency(c.raw) },
        },
      },
    },
  })
}

onMounted(async () => {
  await nextTick()
  buildLineChart()
  buildRegionChart()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap');

.manager-page { display: flex; flex-direction: column; gap: 1.25rem; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

.page-header { display: flex; align-items: flex-end; justify-content: space-between; }
.page-eyebrow { font-size: 11px; letter-spacing: 3px; color: rgba(255,255,255,0.25); margin-bottom: 0.25rem; }
.page-title { font-family: 'Bebas Neue', sans-serif; font-size: 42px; color: #fff; letter-spacing: 1px; line-height: 1; }

.readonly-badge {
  display: flex; align-items: center; gap: 6px;
  padding: 6px 12px; border: 1px solid rgba(234,179,8,0.3);
  border-radius: 4px; font-size: 11px; letter-spacing: 2px;
  color: #fde047; background: rgba(234,179,8,0.08);
}

/* Quarter cards */
.quarter-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
.quarter-card {
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; padding: 1.25rem;
}
.q-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
.q-label { font-family: 'Bebas Neue', sans-serif; font-size: 28px; color: #fff; }
.q-year { font-size: 11px; color: rgba(255,255,255,0.2); }
.q-revenue { font-family: 'Bebas Neue', sans-serif; font-size: 22px; color: #fff; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
.q-profit { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
.profit-label { font-size: 11px; color: rgba(255,255,255,0.25); }
.profit-val { font-size: 13px; font-weight: 500; }
.pos { color: #86efac; }
.neg { color: #fca5a5; }
.q-bar-wrap { height: 3px; background: rgba(255,255,255,0.08); border-radius: 2px; overflow: hidden; }
.q-bar { height: 100%; background: rgba(255,255,255,0.4); border-radius: 2px; transition: width 0.6s ease; }

/* Charts */
.charts-row { display: grid; grid-template-columns: 1fr 240px; gap: 10px; }
.chart-card {
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; padding: 1.25rem;
}
.chart-header { display: flex; justify-content: space-between; margin-bottom: 1.25rem; }
.chart-label { font-size: 10px; letter-spacing: 2.5px; color: rgba(255,255,255,0.3); margin-bottom: 2px; }
.chart-sub { font-size: 12px; color: rgba(255,255,255,0.35); }

/* Insight row */
.insight-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.insight-card {
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; padding: 1.25rem;
}

/* Product lines */
.product-lines { display: flex; flex-direction: column; gap: 14px; margin-top: 1rem; }
.pl-row { display: flex; align-items: center; gap: 12px; }
.pl-rank { font-family: 'Bebas Neue', sans-serif; font-size: 18px; color: rgba(255,255,255,0.15); width: 28px; }
.pl-info { flex: 1; }
.pl-name { display: block; font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 6px; }
.pl-bar-wrap { height: 3px; background: rgba(255,255,255,0.08); border-radius: 2px; overflow: hidden; }
.pl-bar { height: 100%; background: #fff; border-radius: 2px; transition: width 0.6s ease; }
.pl-revenue { font-size: 12px; color: rgba(255,255,255,0.5); white-space: nowrap; }

/* Rekomendasi */
.rekom-list { display: flex; flex-direction: column; gap: 10px; margin-top: 1rem; }
.rekom-item {
  display: flex; align-items: flex-start; gap: 12px;
  padding: 12px; border-radius: 6px;
  background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);
}
.rekom-icon {
  width: 28px; height: 28px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700; flex-shrink: 0;
}
.icon-up     { background: rgba(134,239,172,0.15); color: #86efac; }
.icon-target { background: rgba(147,197,253,0.15); color: #93c5fd; }
.icon-warn   { background: rgba(251,191,36,0.15);  color: #fbbf24; }
.icon-info   { background: rgba(255,255,255,0.1);  color: rgba(255,255,255,0.5); }

.rekom-content { flex: 1; }
.rekom-title { font-size: 13px; font-weight: 500; color: rgba(255,255,255,0.8); margin-bottom: 3px; }
.rekom-desc  { font-size: 12px; color: rgba(255,255,255,0.3); line-height: 1.5; }

.rekom-priority {
  font-size: 9px; letter-spacing: 1px; padding: 3px 7px; border-radius: 3px;
  flex-shrink: 0; margin-top: 2px; font-weight: 600;
}
.priority-HIGH { background: rgba(239,68,68,0.15); color: #fca5a5; }
.priority-MED  { background: rgba(234,179,8,0.15); color: #fde047; }
.priority-LOW  { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.35); }
</style>