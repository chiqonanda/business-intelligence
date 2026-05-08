<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  users: Array,
  roles: Array,
})

const showCreateModal = ref(false)
const form = useForm({
  name: '',
  email: '',
  password: '',
  role: 'staff',
})

const submitCreate = () => {
  form.post(route('admin.store'), {
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    }
  })
}

const updateRole = (user, role) => {
  if (confirm(`INITIATE ROLE ESCALATION FOR ${user.name.toUpperCase()}?`)) {
    useForm({ role }).patch(route('admin.updateRole', user.id))
  }
}

const deleteUser = (user) => {
  if (confirm(`TERMINATE OPERATOR IDENTITY: ${user.name.toUpperCase()}? THIS ACTION IS IRREVERSIBLE.`)) {
    useForm({}).delete(route('admin.destroyUser', user.id))
  }
}

const getRoleColor = (role) => {
  const map = {
    super_admin: '#d9ff00',
    analyst: '#ffffff',
    manager: '#ffffff',
    staff: '#444'
  }
  return map[role] || '#444'
}
</script>

<template>
  <AppLayout>
    <Head title="Nike Identity Control" />

    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-10 mb-16 animate-slide-up">
      <div>
        <p class="text-[10px] font-black uppercase tracking-[0.5em] text-[#d9ff00] mb-4">Identity Governance</p>
        <h1 class="page-title-premium">SECURITY</h1>
        <p class="text-zinc-600 font-bold text-sm uppercase tracking-widest mt-4">Manage network access, operator roles, and system authentication protocols.</p>
      </div>
      
      <button 
        @click="showCreateModal = true"
        class="px-10 py-4 bg-white text-black text-[10px] font-black uppercase tracking-[0.3em] hover:bg-[#d9ff00] transition-all active:scale-95"
      >
        DEPLOY NEW OPERATOR
      </button>
    </div>

    <!-- OPERATOR GRID -->
    <div class="card-premium bg-black overflow-hidden animate-slide-up" style="animation-delay: 0.1s">
      <div class="p-8 border-b border-white/5 bg-white/[0.02]">
        <h3 class="text-xs font-black text-white uppercase tracking-[0.3em]">Network Identity List</h3>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-black">
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Operator Identity</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Access Role</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Network Status</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="u in users" :key="u.id" class="hover:bg-white/[0.01] transition-colors group">
              <td class="px-8 py-6">
                <div class="flex items-center gap-4">
                  <div class="h-10 w-10 bg-white/5 flex items-center justify-center text-xs font-black italic border border-white/10 group-hover:border-[#d9ff00] transition-colors">
                    {{ u.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="text-[11px] font-black text-white uppercase tracking-tight">{{ u.name }}</p>
                    <p class="text-[9px] font-bold text-zinc-600 lowercase tracking-wider mt-0.5">{{ u.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-8 py-6">
                <select 
                  @change="updateRole(u, $event.target.value)"
                  class="bg-transparent border-none p-0 text-[10px] font-black uppercase tracking-widest focus:ring-0 cursor-pointer"
                  :style="{ color: getRoleColor(u.role) }"
                >
                  <option v-for="r in roles" :key="r" :value="r" :selected="u.role === r">{{ r.replace('_', ' ') }}</option>
                </select>
              </td>
              <td class="px-8 py-6">
                <div class="flex items-center gap-2">
                   <div class="h-1.5 w-1.5 rounded-full bg-[#d9ff00]"></div>
                   <span class="text-[9px] font-black text-zinc-400 uppercase tracking-[0.2em]">ENCRYPTED</span>
                </div>
              </td>
              <td class="px-8 py-6">
                <button 
                  v-if="u.id !== $page.props.auth.user.id"
                  @click="deleteUser(u)"
                  class="text-[9px] font-black text-zinc-600 hover:text-rose-600 uppercase tracking-widest transition-colors"
                >
                  TERMINATE
                </button>
                <span v-else class="text-[9px] font-black text-zinc-800 uppercase tracking-widest">OWNER</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- CREATE OPERATOR MODAL -->
    <div v-if="showCreateModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-black/95 backdrop-blur-md animate-fade-in">
       <div class="w-full max-w-md bg-black border border-[#d9ff00]/20 p-12 shadow-[0_0_50px_rgba(217,255,0,0.05)] relative">
          <!-- Close Button -->
          <button @click="showCreateModal = false" class="absolute top-6 right-6 text-zinc-600 hover:text-[#d9ff00] transition-colors">
             <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
          
          <div class="mb-12">
            <h2 class="text-3xl font-black italic uppercase tracking-tighter text-white">DEPLOY AGENT</h2>
            <p class="text-[9px] font-black text-[#d9ff00]/50 uppercase tracking-[0.3em] mt-2">Authentication Protocol Initialization</p>
          </div>

          <form @submit.prevent="submitCreate" class="space-y-8">
             <div>
                <label class="block text-[10px] font-black text-zinc-600 uppercase tracking-widest mb-3">Identity Name</label>
                <input v-model="form.name" type="text" placeholder="OPERATOR NAME" required class="w-full bg-white/5 border border-white/10 px-5 py-4 text-xs font-bold text-white focus:border-[#d9ff00] transition-all outline-none placeholder:text-zinc-800" />
             </div>
             <div>
                <label class="block text-[10px] font-black text-zinc-600 uppercase tracking-widest mb-3">Neural Link (Email)</label>
                <input v-model="form.email" type="email" placeholder="agent@nike.intel" required class="w-full bg-white/5 border border-white/10 px-5 py-4 text-xs font-bold text-white focus:border-[#d9ff00] transition-all outline-none placeholder:text-zinc-800" />
             </div>
             <div>
                <label class="block text-[10px] font-black text-zinc-600 uppercase tracking-widest mb-3">Security Key (Password)</label>
                <input v-model="form.password" type="password" placeholder="••••••••" required class="w-full bg-white/5 border border-white/10 px-5 py-4 text-xs font-bold text-white focus:border-[#d9ff00] transition-all outline-none" />
             </div>
             <div>
                <label class="block text-[10px] font-black text-zinc-600 uppercase tracking-widest mb-3">Access Clearance</label>
                <select v-model="form.role" class="w-full bg-[#111] border border-white/10 px-5 py-4 text-[10px] font-black text-white uppercase tracking-widest focus:border-[#d9ff00] transition-all outline-none appearance-none">
                   <option v-for="r in roles" :key="r" :value="r" class="bg-black">{{ r.replace('_', ' ') }}</option>
                </select>
             </div>

             <div class="pt-4">
               <button type="submit" class="w-full py-5 bg-[#d9ff00] text-black text-[11px] font-black uppercase tracking-[0.4em] hover:bg-white transition-all active:scale-95 flex items-center justify-center gap-3">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 4v16m8-8H4" /></svg>
                  CONFIRM DEPLOYMENT
               </button>
             </div>
          </form>
       </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.animate-fade-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>