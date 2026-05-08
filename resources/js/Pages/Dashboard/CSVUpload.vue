<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
  upload_history: Array
})

const fileInput = ref(null)
const isDragging = ref(false)
const uploading = ref(false)
const progress = ref(0)
const error = ref(null)
const success = ref(null)

const triggerFileSelect = () => fileInput.value.click()

const handleFileSelect = (e) => {
  const file = e.target.files[0]
  if (file) startUpload(file)
}

const handleDrop = (e) => {
  isDragging.value = false
  const file = e.dataTransfer.files[0]
  if (file) startUpload(file)
}

const startUpload = async (file) => {
  if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
    error.value = 'PROTOCOL ERROR: INVALID FILE TYPE. SYSTEM REQUIRES .CSV'
    return
  }

  uploading.value = true
  progress.value = 0
  error.value = null
  success.value = null

  const formData = new FormData()
  formData.append('csv_file', file)

  try {
    const res = await axios.post(route('upload.store'), formData, {
      onUploadProgress: (progressEvent) => {
        progress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
      }
    })
    
    success.value = 'INGESTION COMPLETE: DATA NODE SYNCHRONIZED'
    // Refresh history
    router.visit(route('upload.index'), { preserveScroll: true })
  } catch (err) {
    error.value = err.response?.data?.message || 'SYSTEM FAILURE: ETL PIPELINE INTERRUPTED'
  } finally {
    uploading.value = false
  }
}

const handleReset = async () => {
  if (!confirm('CRITICAL ACTION: PURGE ALL DATA NODES? THIS CANNOT BE UNDONE.')) return
  
  try {
    await axios.delete(route('upload.reset'))
    success.value = 'DATABASE PURGED: ALL DATA NODES WIPED'
    router.visit(route('upload.index'))
  } catch (err) {
    error.value = 'PURGE FAILURE: SYSTEM ACCESS DENIED'
  }
}

const deleteHistory = async (filename) => {
  if (!confirm('DELETE THIS SESSION LOG? DATA ALREADY INGESTED WILL REMAIN.')) return
  
  try {
    await axios.delete(route('upload.destroy', { filename }))
    router.visit(route('upload.index'), { preserveScroll: true })
  } catch (err) {
    error.value = 'ACTION FAILED: LOG PROTECTION ACTIVE'
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Nike Data Ingestion" />

    <div class="mb-16 animate-slide-up">
      <p class="text-[10px] font-black uppercase tracking-[0.5em] text-[#d9ff00] mb-4">ETL Operations</p>
      <h1 class="page-title-premium">DATA INGESTION</h1>
      <p class="text-zinc-600 font-bold text-sm uppercase tracking-widest mt-4">Inject raw transaction datasets into the Nike global intelligence node.</p>
    </div>

    <div class="grid lg:grid-cols-3 gap-12 animate-slide-up" style="animation-delay: 0.1s">
      <!-- UPLOAD TERMINAL -->
      <div class="lg:col-span-2 space-y-8">
        <div 
          @dragover.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @drop.prevent="handleDrop"
          class="relative group"
        >
          <div 
            class="card-premium p-20 border-2 border-dashed flex flex-col items-center justify-center text-center transition-all duration-500"
            :class="[
              isDragging ? 'border-[#d9ff00] bg-[#d9ff00]/5' : 'border-white/5 bg-black',
              uploading ? 'opacity-50 pointer-events-none' : ''
            ]"
          >
            <input 
              ref="fileInput" 
              type="file" 
              class="hidden" 
              accept=".csv" 
              @change="handleFileSelect"
            />
            
            <div class="h-20 w-20 bg-white/5 flex items-center justify-center mb-8 group-hover:bg-[#d9ff00] transition-colors">
               <svg class="h-10 w-10 text-zinc-500 group-hover:text-black transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
               </svg>
            </div>

            <h3 class="text-xl font-black italic uppercase tracking-tighter text-white mb-2">INITIALIZE UPLOAD</h3>
            <p class="text-[10px] font-black text-zinc-600 uppercase tracking-widest mb-10">DROP SOURCE FILE OR CLICK TO BROWSE</p>

            <button 
              @click="triggerFileSelect"
              class="px-10 py-4 bg-white text-black text-[10px] font-black uppercase tracking-[0.3em] hover:bg-[#d9ff00] transition-all active:scale-95"
            >
              BROWSE FILESYSTEM
            </button>
          </div>

          <!-- PROGRESS OVERLAY -->
          <div v-if="uploading" class="absolute inset-0 bg-black/90 z-20 flex flex-col items-center justify-center p-12">
            <div class="w-full max-w-md">
               <div class="flex justify-between items-end mb-4">
                  <p class="text-[10px] font-black text-[#d9ff00] uppercase tracking-widest animate-pulse">INGESTING DATA...</p>
                  <p class="text-3xl font-black italic text-white leading-none">{{ progress }}%</p>
               </div>
               <div class="h-1 w-full bg-white/5">
                  <div class="h-full bg-[#d9ff00] transition-all duration-300" :style="{ width: progress + '%' }"></div>
               </div>
            </div>
          </div>
        </div>

        <!-- STATUS MESSAGES -->
        <div v-if="error" class="p-6 bg-rose-500/10 border-l-4 border-rose-600 animate-slide-up">
           <div class="flex items-center gap-4">
              <svg class="h-6 w-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
              <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest">{{ error }}</p>
           </div>
        </div>

        <div v-if="success" class="p-6 bg-[#d9ff00]/10 border-l-4 border-[#d9ff00] animate-slide-up">
           <div class="flex items-center gap-4">
              <svg class="h-6 w-6 text-[#d9ff00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              <p class="text-[10px] font-black text-[#d9ff00] uppercase tracking-widest">{{ success }}</p>
           </div>
        </div>

        <!-- DANGER ZONE -->
        <div class="pt-20 border-t border-white/5">
           <h3 class="text-[10px] font-black text-rose-600 uppercase tracking-[0.5em] mb-6">DANGER ZONE</h3>
           <div class="card-premium p-8 bg-black border-rose-900/30 flex flex-col md:flex-row justify-between items-center gap-6">
              <div>
                 <p class="text-xs font-black text-white uppercase tracking-widest">PURGE ALL SYSTEM DATA</p>
                 <p class="text-[9px] font-bold text-zinc-600 uppercase tracking-widest mt-1">This will permanently delete all transactions, products, and customer nodes.</p>
              </div>
              <button 
                @click="handleReset"
                class="px-8 py-3 bg-rose-600 text-white text-[9px] font-black uppercase tracking-[0.3em] hover:bg-rose-500 transition-all"
              >
                RESET SYSTEM
              </button>
           </div>
        </div>
      </div>

      <!-- INGESTION LOGS -->
      <div class="space-y-6">
        <div class="flex justify-between items-center">
           <h3 class="text-xs font-black text-white uppercase tracking-[0.3em]">Session History</h3>
           <span class="text-[9px] font-bold text-zinc-600 uppercase tracking-widest">{{ upload_history.length }} NODES</span>
        </div>
        
        <div class="space-y-4">
          <div v-for="log in upload_history" :key="log.id" class="p-6 bg-[#0a0a0a] border border-white/5 flex justify-between items-center group hover:border-white/20 transition-all">
            <div class="flex-1">
              <p class="text-[11px] font-black text-white uppercase tracking-tight group-hover:text-[#d9ff00] transition-colors line-clamp-1 pr-4" :title="log.original_name">
                 {{ log.original_name }}
              </p>
              <div class="flex items-center gap-3 mt-1">
                 <p class="text-[9px] font-bold text-zinc-600 uppercase tracking-widest">{{ log.size }} • {{ log.uploaded_at }}</p>
                 <span v-if="log.rows_inserted > 0" class="text-[8px] font-black text-[#d9ff00]/60 uppercase tracking-widest border-l border-white/10 pl-3">
                    {{ log.rows_inserted.toLocaleString() }} ROWS
                 </span>
              </div>
            </div>
            
            <div class="flex items-center gap-6">
              <!-- STATUS BADGE -->
              <div class="flex flex-col items-end">
                 <span 
                   class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5"
                   :class="{
                     'text-[#d9ff00] bg-[#d9ff00]/10': log.status === 'SUCCESS',
                     'text-amber-500 bg-amber-500/10': log.status === 'PARTIAL',
                     'text-rose-600 bg-rose-600/10': log.status === 'FAILED'
                   }"
                 >
                   {{ log.status }}
                 </span>
                 <p v-if="log.rows_skipped > 0" class="text-[7px] font-bold text-zinc-600 uppercase tracking-widest mt-1">
                    {{ log.rows_skipped }} SKIPPED
                 </p>
              </div>

              <button 
                @click="deleteHistory(log.filename)"
                class="h-10 px-4 bg-rose-600/10 border border-rose-600/20 flex items-center justify-center hover:bg-rose-600 group/del transition-all"
                title="Delete Session Log"
              >
                 <svg class="h-4 w-4 text-rose-600 group-hover/del:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                 </svg>
                 <span class="ml-2 text-[8px] font-black text-rose-600 group-hover/del:text-white uppercase tracking-widest">DELETE</span>
              </button>
            </div>
          </div>
          <div v-if="!upload_history.length" class="p-10 border border-white/5 text-center text-[10px] font-black text-zinc-600 uppercase tracking-widest italic">
            NO PRIOR SESSIONS DETECTED
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>