<template>
  <AppLayout>
    <div class="upload-page">

      <!-- Header -->
      <div class="page-header">
        <div>
          <p class="page-eyebrow">ETL PIPELINE</p>
          <h1 class="page-title">UPLOAD CSV</h1>
        </div>
      </div>

      <!-- Upload zone + info row -->
      <div class="upload-row">

        <!-- Drop zone -->
        <div class="upload-zone-card">
          <p class="chart-label">FILE CSV NIKE</p>

          <div
            class="drop-zone"
            :class="{ 'drag-over': isDragging, 'has-file': selectedFile }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
            @click="triggerFileInput"
          >
            <input
              ref="fileInputRef"
              type="file"
              accept=".csv,.txt"
              class="hidden-input"
              @change="onFileChange"
            />

            <!-- No file selected -->
            <div v-if="!selectedFile" class="drop-content">
              <div class="drop-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <polyline points="16 16 12 12 8 16"/>
                  <line x1="12" y1="12" x2="12" y2="21"/>
                  <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                </svg>
              </div>
              <p class="drop-title">Drop file CSV di sini</p>
              <p class="drop-sub">atau klik untuk pilih file</p>
              <span class="drop-hint">.csv · Maks 10MB</span>
            </div>

            <!-- File selected -->
            <div v-else class="file-preview">
              <div class="file-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                  <polyline points="10 9 9 9 8 9"/>
                </svg>
              </div>
              <div class="file-meta">
                <p class="file-name">{{ selectedFile.name }}</p>
                <p class="file-size">{{ formatSize(selectedFile.size) }}</p>
              </div>
              <button class="btn-clear" @click.stop="clearFile">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Error -->
          <div v-if="uploadError" class="upload-error">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ uploadError }}
          </div>

          <!-- Progress bar -->
          <div v-if="isUploading" class="progress-wrap">
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: progress + '%' }"></div>
            </div>
            <span class="progress-label">{{ progressLabel }}</span>
          </div>

          <!-- Submit -->
          <button
            class="btn-upload"
            :disabled="!selectedFile || isUploading"
            @click="submitUpload"
          >
            <svg v-if="!isUploading" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="16 16 12 12 8 16"/>
              <line x1="12" y1="12" x2="12" y2="21"/>
              <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
            </svg>
            <span v-if="!isUploading">PROSES ETL</span>
            <span v-else class="loading-dots"><span></span><span></span><span></span></span>
          </button>
        </div>

        <!-- Info panel -->
        <div class="info-panel">
          <div class="info-card">
            <p class="chart-label">FORMAT CSV YANG DITERIMA</p>
            <div class="field-list">
              <div v-for="f in expectedFields" :key="f.name" class="field-item">
                <span class="field-name mono">{{ f.name }}</span>
                <span class="field-type" :class="'type-' + f.type">{{ f.type }}</span>
                <span v-if="f.required" class="field-required">WAJIB</span>
              </div>
            </div>
          </div>

          <div class="info-card etl-steps">
            <p class="chart-label">ETL PIPELINE</p>
            <div class="steps">
              <div v-for="(step, i) in etlSteps" :key="i" class="step-item">
                <div class="step-num">{{ i + 1 }}</div>
                <div class="step-info">
                  <p class="step-title">{{ step.title }}</p>
                  <p class="step-desc">{{ step.desc }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Upload history -->
      <div class="history-card">
        <p class="chart-label">RIWAYAT UPLOAD</p>
        <div class="history-list">
          <div v-if="!history.length" class="history-empty">
            Belum ada file yang diupload.
          </div>
          <div v-for="h in history" :key="h.filename" class="history-item">
            <div class="h-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
            </div>
            <span class="h-filename mono">{{ h.filename }}</span>
            <span class="h-size">{{ h.size }}</span>
            <span class="h-date">{{ h.uploaded_at }}</span>
            <span class="h-status status-done">✓ SELESAI</span>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props  = defineProps({ upload_history: Array })
const page   = usePage()

const fileInputRef = ref(null)
const selectedFile = ref(null)
const isDragging   = ref(false)
const isUploading  = ref(false)
const uploadError  = ref('')
const progress     = ref(0)
const progressLabel = ref('')
const history      = ref(props.upload_history || [])

// ── Field & step info ─────────────────────────────────────────────────────────
const expectedFields = [
  { name: 'Order_ID',        type: 'string',  required: true  },
  { name: 'Order_Date',      type: 'date',    required: true  },
  { name: 'Product_Name',    type: 'string',  required: true  },
  { name: 'Product_Line',    type: 'string',  required: false },
  { name: 'Gender_Category', type: 'string',  required: false },
  { name: 'Region',          type: 'string',  required: false },
  { name: 'Units_Sold',      type: 'number',  required: false },
  { name: 'MRP',             type: 'number',  required: false },
  { name: 'Revenue',         type: 'number',  required: false },
  { name: 'Profit',          type: 'number',  required: false },
  { name: 'Discount_Applied',type: 'number',  required: false },
  { name: 'Sales_Channel',   type: 'string',  required: false },
]

const etlSteps = [
  { title: 'Validasi File',    desc: 'Cek format, encoding, dan header CSV.' },
  { title: 'Cleaning Data',    desc: 'Normalize tanggal, region, nilai null/NaN.' },
  { title: 'Upsert Dimensi',   desc: 'Insert/update dim_produk, dim_pelanggan, dim_waktu.' },
  { title: 'Insert Fact',      desc: 'Masukkan ke fact_penjualan, skip duplikat Order_ID.' },
  { title: 'Laporan',          desc: 'Tampilkan jumlah inserted, skipped, error.' },
]

// ── File handling ─────────────────────────────────────────────────────────────
function triggerFileInput() { fileInputRef.value?.click() }

function onFileChange(e) {
  const file = e.target.files?.[0]
  if (file) setFile(file)
}

function onDrop(e) {
  isDragging.value = false
  const file = e.dataTransfer.files?.[0]
  if (file) setFile(file)
}

function setFile(file) {
  uploadError.value = ''
  if (!file.name.match(/\.(csv|txt)$/i)) {
    uploadError.value = 'Hanya file .csv yang diterima.'
    return
  }
  if (file.size > 10 * 1024 * 1024) {
    uploadError.value = 'Ukuran file melebihi 10MB.'
    return
  }
  selectedFile.value = file
}

function clearFile() {
  selectedFile.value = null
  uploadError.value  = ''
  if (fileInputRef.value) fileInputRef.value.value = ''
}

function formatSize(bytes) {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1024 / 1024).toFixed(2) + ' MB'
}

// ── Submit ────────────────────────────────────────────────────────────────────
function submitUpload() {
  if (!selectedFile.value) return

  isUploading.value  = true
  uploadError.value  = ''
  progress.value     = 10
  progressLabel.value = 'Mengunggah file...'

  const formData = new FormData()
  formData.append('csv_file', selectedFile.value)

  // Simulate progress steps
  const steps = [
    { pct: 25, label: 'Validasi header CSV...' },
    { pct: 50, label: 'Cleaning data...' },
    { pct: 75, label: 'Proses dimensi & fakta...' },
    { pct: 90, label: 'Finalisasi insert...' },
  ]
  let stepIdx = 0
  const stepTimer = setInterval(() => {
    if (stepIdx < steps.length) {
      progress.value      = steps[stepIdx].pct
      progressLabel.value = steps[stepIdx].label
      stepIdx++
    }
  }, 800)

  router.post(route('upload.store'), formData, {
    forceFormData: true,
    onSuccess: () => {
      clearInterval(stepTimer)
      progress.value      = 100
      progressLabel.value = 'Selesai!'
      setTimeout(() => {
        isUploading.value = false
        clearFile()
        progress.value    = 0
      }, 1000)
    },
    onError: (errors) => {
      clearInterval(stepTimer)
      isUploading.value = false
      progress.value    = 0
      uploadError.value = errors.csv_file || 'Terjadi kesalahan saat upload.'
    },
  })
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&family=JetBrains+Mono:wght@400&display=swap');

.upload-page { display: flex; flex-direction: column; gap: 1.25rem; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

.page-header { display: flex; align-items: flex-end; justify-content: space-between; }
.page-eyebrow { font-size: 11px; letter-spacing: 3px; color: rgba(255,255,255,0.25); margin-bottom: 0.25rem; }
.page-title { font-family: 'Bebas Neue', sans-serif; font-size: 42px; color: #fff; letter-spacing: 1px; line-height: 1; }

/* Upload row */
.upload-row { display: grid; grid-template-columns: 1fr 360px; gap: 10px; align-items: start; }

.upload-zone-card {
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;
}

.chart-label { font-size: 10px; letter-spacing: 2.5px; color: rgba(255,255,255,0.3); }

/* Drop zone */
.drop-zone {
  border: 2px dashed rgba(255,255,255,0.12); border-radius: 8px;
  min-height: 180px; display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all 0.2s; position: relative;
}
.drop-zone:hover { border-color: rgba(255,255,255,0.25); background: rgba(255,255,255,0.02); }
.drop-zone.drag-over { border-color: #fff; background: rgba(255,255,255,0.04); }
.drop-zone.has-file { border-style: solid; border-color: rgba(255,255,255,0.15); }

.hidden-input { display: none; }

.drop-content { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.drop-icon { color: rgba(255,255,255,0.2); margin-bottom: 4px; }
.drop-title { font-size: 15px; font-weight: 500; color: rgba(255,255,255,0.6); }
.drop-sub { font-size: 13px; color: rgba(255,255,255,0.25); }
.drop-hint {
  font-size: 11px; letter-spacing: 1.5px; color: rgba(255,255,255,0.2);
  background: rgba(255,255,255,0.05); padding: 4px 12px; border-radius: 20px; margin-top: 4px;
}

.file-preview {
  display: flex; align-items: center; gap: 16px; padding: 1.5rem;
  width: 100%;
}
.file-icon { color: rgba(255,255,255,0.5); flex-shrink: 0; }
.file-meta { flex: 1; }
.file-name { font-size: 14px; font-weight: 500; color: rgba(255,255,255,0.8); font-family: 'JetBrains Mono', monospace; word-break: break-all; }
.file-size { font-size: 12px; color: rgba(255,255,255,0.3); margin-top: 4px; }

.btn-clear {
  width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.4);
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; transition: all 0.15s;
}
.btn-clear:hover { background: rgba(255,255,255,0.12); color: #fff; }

/* Upload error */
.upload-error {
  display: flex; align-items: center; gap: 8px; padding: 10px 14px;
  background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
  border-radius: 4px; font-size: 13px; color: #fca5a5;
}

/* Progress */
.progress-wrap { display: flex; flex-direction: column; gap: 8px; }
.progress-bar {
  height: 4px; background: rgba(255,255,255,0.08);
  border-radius: 2px; overflow: hidden;
}
.progress-fill {
  height: 100%; background: #fff; border-radius: 2px;
  transition: width 0.5s ease;
}
.progress-label { font-size: 12px; color: rgba(255,255,255,0.4); }

/* Submit button */
.btn-upload {
  width: 100%; background: #fff; color: #000; border: none; border-radius: 4px;
  padding: 13px; font-family: 'Bebas Neue', sans-serif; font-size: 18px;
  letter-spacing: 3px; cursor: pointer; transition: background 0.15s;
  display: flex; align-items: center; justify-content: center; gap: 10px;
  min-height: 50px;
}
.btn-upload:hover:not(:disabled) { background: #e5e5e5; }
.btn-upload:disabled { opacity: 0.4; cursor: not-allowed; }

.loading-dots { display: flex; gap: 5px; align-items: center; }
.loading-dots span { width: 6px; height: 6px; border-radius: 50%; background: #000; animation: bounce 1.2s infinite; }
.loading-dots span:nth-child(2) { animation-delay: 0.2s; }
.loading-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes bounce { 0%,80%,100% { transform: translateY(0); opacity: 0.4; } 40% { transform: translateY(-5px); opacity: 1; } }

/* Info panel */
.info-panel { display: flex; flex-direction: column; gap: 10px; }
.info-card {
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; padding: 1.25rem;
}

.field-list { display: flex; flex-direction: column; gap: 6px; margin-top: 0.75rem; }
.field-item { display: flex; align-items: center; gap: 8px; font-size: 12px; }
.field-name { color: rgba(255,255,255,0.6); flex: 1; }
.mono { font-family: 'JetBrains Mono', monospace; font-size: 11px; }
.field-type {
  font-size: 10px; letter-spacing: 1px; padding: 2px 6px; border-radius: 3px;
}
.type-string { background: rgba(147,197,253,0.15); color: #93c5fd; }
.type-number { background: rgba(134,239,172,0.15); color: #86efac; }
.type-date   { background: rgba(251,191,36,0.15);  color: #fbbf24; }
.field-required { font-size: 9px; letter-spacing: 1px; color: #fca5a5; background: rgba(239,68,68,0.1); padding: 2px 6px; border-radius: 3px; }

/* ETL steps */
.steps { display: flex; flex-direction: column; gap: 10px; margin-top: 0.75rem; }
.step-item { display: flex; align-items: flex-start; gap: 10px; }
.step-num {
  width: 22px; height: 22px; border-radius: 50%; background: rgba(255,255,255,0.1);
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 600; color: rgba(255,255,255,0.6); flex-shrink: 0;
}
.step-title { font-size: 12px; font-weight: 500; color: rgba(255,255,255,0.7); margin-bottom: 2px; }
.step-desc { font-size: 11px; color: rgba(255,255,255,0.3); line-height: 1.5; }

/* History */
.history-card {
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; padding: 1.25rem;
}
.history-list { display: flex; flex-direction: column; gap: 6px; margin-top: 1rem; }
.history-empty { font-size: 13px; color: rgba(255,255,255,0.2); padding: 0.25rem 0; }
.history-item {
  display: flex; align-items: center; gap: 12px; padding: 10px 12px;
  border-radius: 6px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);
}
.h-icon { color: rgba(255,255,255,0.25); display: flex; flex-shrink: 0; }
.h-filename { flex: 1; font-size: 12px; color: rgba(255,255,255,0.6); }
.h-size, .h-date { font-size: 11px; color: rgba(255,255,255,0.25); }
.h-status { font-size: 10px; letter-spacing: 1px; padding: 3px 8px; border-radius: 3px; font-weight: 600; }
.status-done { background: rgba(34,197,94,0.15); color: #86efac; }
</style>