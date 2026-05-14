<script setup>
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
  upload_history: Array,
  stats: Object
})

const form = useForm({
  csv_file: null,
  data_type: 'AUTO-DETECT'
})

const isDragging = ref(false)
const success = ref(null)
const error = ref(null)

const handleDrop = (e) => {
  isDragging.value = false
  const file = e.dataTransfer.files[0]
  if (file) form.csv_file = file
}

const submit = () => {
  form.post(route('upload.store'), {
    onSuccess: (page) => {
      success.value = page.props.flash?.success || 'DATA INGESTION COMPLETE'
      form.reset()
    },
    onError: () => {
        error.value = 'SYSTEM FAILURE: ETL PIPELINE INTERRUPTED'
    }
  })
}

const clearLogs = () => {
    if (confirm('REMOVE ALL ACTIVITY LOGS?')) {
        router.delete(route('upload.clear_logs'), {
            onSuccess: () => success.value = 'HISTORY WIPED'
        })
    }
}

const resetData = () => {
    if (confirm('CRITICAL: PERFORM GLOBAL SYSTEM RESET? ALL NODES WILL BE WIPED.')) {
        if (confirm('AUTHORIZATION REQUIRED: ARE YOU ABSOLUTELY SURE? THIS ACTION CANNOT BE UNDONE.')) {
            router.delete(route('upload.truncate'), {
                onSuccess: () => success.value = 'GLOBAL RESET COMPLETE'
            })
        }
    }
}

const deleteFile = (filename) => {
    if (confirm('DELETE THIS SESSION LOG?')) {
        router.delete(route('upload.destroy', { filename }), {
            preserveScroll: true
        })
    }
}
</script>

<template>
  <AppLayout>
    <Head title="Ingestion Pipeline" />

    <div class="mb-24 animate-slide-up">
      <div class="flex items-center gap-6 mb-8">
         <div class="h-[1px] w-20 bg-[#d9ff00]"></div>
         <p class="text-[10px] font-black text-[#d9ff00] uppercase tracking-[0.6em]">Operational Data Node</p>
      </div>
      <h1 class="page-title-premium mb-8">Data <br/> <span class="text-stroke">Ingestion.</span></h1>
      <p class="max-w-2xl text-zinc-500 font-bold text-lg leading-relaxed uppercase tracking-tight">
        Calibrate system nodes by injecting fresh mission data. <br/> Supporting Sales, Product Catalog, and Sentiment feeds.
      </p>
    </div>

    <div class="grid lg:grid-cols-3 gap-12 animate-slide-up" style="animation-delay: 0.1s">
      <!-- UPLOAD FORM -->
      <div class="lg:col-span-2">
        <form @submit.prevent="submit" class="space-y-12">
          <div class="card-premium p-12 bg-white/[0.01]">
            <div class="flex flex-col md:flex-row gap-10">
              <!-- DROPZONE -->
              <div class="flex-1">
                 <label for="csv_file" class="block group cursor-pointer"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop">
                    <div class="border-2 border-dashed border-white/5 group-hover:border-[#d9ff00]/30 transition-all p-12 flex flex-col items-center justify-center min-h-[300px] bg-black"
                         :class="{'border-[#d9ff00]/40 bg-[#d9ff00]/5': isDragging}">
                       <div v-if="!form.csv_file" class="text-center">
                          <svg class="h-12 w-12 text-zinc-800 group-hover:text-[#d9ff00] transition-colors mb-6 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                          <p class="text-xs font-black text-white uppercase tracking-[0.3em] mb-2">Drop Mission CSV</p>
                          <p class="text-[9px] font-bold text-zinc-700 uppercase tracking-widest">or click to browse local storage</p>
                       </div>
                       <div v-else class="text-center">
                          <div class="h-12 w-12 rounded-full bg-[#d9ff00] flex items-center justify-center mb-6 mx-auto shadow-[0_0_20px_#d9ff00]">
                             <svg class="h-6 w-6 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7" /></svg>
                          </div>
                          <p class="text-xs font-black text-[#d9ff00] uppercase tracking-[0.3em] mb-1">Source Locked</p>
                          <p class="text-[10px] font-bold text-white truncate max-w-[200px]">{{ form.csv_file.name }}</p>
                       </div>
                    </div>
                    <input id="csv_file" type="file" @input="form.csv_file = $event.target.files[0]" class="hidden" accept=".csv" />
                 </label>
                 <div v-if="form.errors.csv_file" class="mt-4 text-[10px] font-black text-rose-500 uppercase tracking-widest italic">! ERR: {{ form.errors.csv_file }}</div>
              </div>

              <!-- CONTROLS -->
              <div class="md:w-72 flex flex-col justify-center gap-8">
                 <div>
                    <label class="text-[9px] font-black text-zinc-600 uppercase tracking-[0.4em] mb-4 block">Target Node</label>
                    <div class="space-y-3">
                       <button 
                          v-for="t in ['AUTO-DETECT', 'SALES', 'PRODUCTS', 'REVIEWS']" 
                          :key="t"
                          type="button"
                          @click="form.data_type = t"
                          class="w-full text-left px-5 py-3 text-[10px] font-black tracking-widest border transition-all"
                          :class="form.data_type === t ? 'bg-[#d9ff00] text-black border-[#d9ff00]' : 'bg-black text-zinc-600 border-white/5 hover:border-white/20'"
                       >
                          {{ t }}
                       </button>
                    </div>
                 </div>

                 <button type="submit" :disabled="form.processing" class="btn-premium w-full !bg-white !text-black hover:!bg-[#d9ff00] disabled:opacity-20 transition-all active:scale-95">
                    <span v-if="form.processing">Processing...</span>
                    <span v-else>Engage Pipeline</span>
                 </button>
              </div>
            </div>
          </div>
        </form>

        <!-- STATUS OVERLAYS -->
        <div v-if="success" class="p-6 bg-[#d9ff00]/10 border-l-4 border-[#d9ff00] animate-slide-up flex justify-between items-center">
            <p class="text-[10px] font-black text-[#d9ff00] uppercase tracking-widest">{{ success }}</p>
            <button @click="success = null" class="text-zinc-600 hover:text-white transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
      </div>

      <!-- MAINTENANCE / PROTOCOLS -->
      <div class="space-y-12">
         <div class="card-premium p-10 border-rose-500/10 hover:border-rose-500/30 group">
            <h3 class="text-[10px] font-black text-rose-500 uppercase tracking-[0.4em] mb-8 italic">Maintenance Node</h3>
            <div class="space-y-4">
               <button @click="clearLogs" class="w-full flex items-center justify-between p-4 bg-rose-500/5 hover:bg-rose-500/10 transition-all">
                  <span class="text-[10px] font-black text-rose-200 uppercase tracking-widest">Wipe Pipeline Logs</span>
                  <svg class="h-4 w-4 text-rose-500 group-hover:rotate-90 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
               </button>
               <button @click="resetData" class="w-full flex items-center justify-between p-4 bg-rose-500/10 hover:bg-rose-600 hover:text-white transition-all text-rose-500">
                  <span class="text-[10px] font-black uppercase tracking-widest">Global System Reset</span>
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
               </button>
            </div>
         </div>

         <div class="p-10 glass-effect">
            <h4 class="text-[10px] font-black text-white uppercase tracking-[0.3em] mb-6">Protocol Specs</h4>
            <ul class="space-y-5 text-[9px] font-bold text-zinc-600 uppercase tracking-[0.2em] leading-relaxed">
               <li class="flex gap-4">
                  <span class="text-[#d9ff00] font-black">01</span>
                  Only .CSV file extensions are validated.
               </li>
               <li class="flex gap-4">
                  <span class="text-[#d9ff00] font-black">02</span>
                  System fuzzy-matches standard Nike headers.
               </li>
               <li class="flex gap-4">
                  <span class="text-[#d9ff00] font-black">03</span>
                  Auto-detection handles Sales, Product, and Reviews.
               </li>
            </ul>
         </div>
      </div>
    </div>

    <!-- LOGS TABLE -->
    <div class="mt-24 animate-slide-up" style="animation-delay: 0.2s">
      <div class="flex items-center justify-between mb-10">
         <div class="flex items-center gap-4">
            <div class="h-8 w-1 bg-white/20"></div>
            <h3 class="text-2xl font-black italic uppercase tracking-tighter font-header">Ingestion History</h3>
         </div>
         <div class="flex items-center gap-8">
             <div class="text-right">
                <p class="text-[8px] font-black text-zinc-600 uppercase tracking-widest mb-1">Total Ingested</p>
                <p class="text-[11px] font-black text-[#d9ff00] font-header">{{ stats.data_valid.toLocaleString() }} ROWS</p>
             </div>
             <div class="h-8 w-[1px] bg-white/5"></div>
             <div class="text-right">
                <p class="text-[8px] font-black text-zinc-600 uppercase tracking-widest mb-1">Audit Trail</p>
                <p class="text-[11px] font-black text-white font-header italic">ACTIVE</p>
             </div>
         </div>
      </div>
      
      <div class="card-premium overflow-hidden">
         <table class="w-full text-left border-collapse">
            <thead>
               <tr class="bg-white/[0.03] border-b border-white/5">
                  <th class="p-6 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Timestamp</th>
                  <th class="p-6 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Data Node</th>
                  <th class="p-6 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Source Resource</th>
                  <th class="p-6 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Status</th>
                  <th class="p-6 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Load Intensity</th>
                  <th class="p-6 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Actions</th>
               </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
               <tr v-for="log in upload_history" :key="log.id" class="hover:bg-white/[0.01] transition-colors group">
                  <td class="p-6 text-[11px] font-black text-zinc-500 group-hover:text-white font-header">{{ log.uploaded_at }}</td>
                  <td class="p-6">
                     <span class="badge-premium !border-[#d9ff00]/20 !text-[#d9ff00]">{{ log.data_type }}</span>
                  </td>
                  <td class="p-6 text-[11px] font-bold text-zinc-600 group-hover:text-zinc-400 truncate max-w-[200px]" :title="log.original_name">{{ log.original_name }}</td>
                  <td class="p-6">
                     <div class="flex items-center gap-2">
                        <div class="h-1.5 w-1.5 rounded-full" 
                             :class="log.status === 'SUCCESS' ? 'bg-[#d9ff00] shadow-[0_0_8px_#d9ff00]' : 'bg-rose-500'"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest" 
                              :class="log.status === 'SUCCESS' ? 'text-[#d9ff00]' : 'text-rose-500'">{{ log.status }}</span>
                     </div>
                  </td>
                  <td class="p-6 text-[11px] font-black italic text-zinc-600 group-hover:text-white font-header">{{ log.rows_inserted.toLocaleString() }} ROWS</td>
                  <td class="p-6">
                     <button @click="deleteFile(log.filename)" class="text-[9px] font-black text-zinc-700 hover:text-rose-500 uppercase tracking-widest transition-colors">Remove Log</button>
                  </td>
               </tr>
               <tr v-if="!upload_history.length">
                  <td colspan="6" class="p-20 text-center text-[10px] font-black text-zinc-800 uppercase tracking-[0.5em]">No ingestion activity detected in local node</td>
               </tr>
            </tbody>
         </table>
      </div>
    </div>
  </AppLayout>
</template>