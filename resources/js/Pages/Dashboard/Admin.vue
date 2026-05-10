<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  users: Array,
  roles: Array,
})

const page = usePage()
const flash = computed(() => page.props.flash || {})

const showCreateModal = ref(false)
const editingRole = ref(null) // track which user is being role-edited

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
    },
  })
}

const updateRole = (user, role) => {
  if (!confirm(`Change ${user.name.toUpperCase()} access to "${role.replace('_', ' ').toUpperCase()}"?`)) {
    return
  }
  editingRole.value = user.id
  useForm({ role }).patch(route('admin.updateRole', user.id), {
    onFinish: () => { editingRole.value = null },
  })
}

const deleteUser = (user) => {
  if (!confirm(`PERMANENTLY TERMINATE OPERATOR "${user.name.toUpperCase()}"?\n\nThis action cannot be undone.`)) return
  useForm({}).delete(route('admin.destroyUser', user.id))
}

const roleColor = (role) => ({
  super_admin: 'text-[#d9ff00] bg-[#d9ff00]/10 border-[#d9ff00]/30',
  analyst:     'text-blue-400 bg-blue-400/10 border-blue-400/30',
  manager:     'text-purple-400 bg-purple-400/10 border-purple-400/30',
  staff:       'text-zinc-400 bg-zinc-400/10 border-zinc-400/30',
}[role] || 'text-zinc-400 bg-zinc-400/10 border-zinc-400/30')

const roleIcon = (role) => ({
  super_admin: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
  analyst:     'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
  manager:     'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
  staff:       'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
}[role] || 'M16 7a4 4 0 11-8 0 4 4 0 018 0z')

const currentUser = computed(() => page.props.auth.user)
</script>

<template>
  <AppLayout>
    <Head title="Nike Identity Control" />

    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-10 mb-16 animate-slide-up">
      <div>
        <p class="text-[10px] font-black uppercase tracking-[0.5em] text-[#d9ff00] mb-4">Identity Governance</p>
        <h1 class="page-title-premium">SECURITY</h1>
        <p class="text-zinc-600 font-bold text-sm uppercase tracking-widest mt-4">
          Manage network access, operator roles, and authentication protocols.
        </p>
      </div>
      <button
        @click="showCreateModal = true"
        class="flex items-center gap-3 px-10 py-4 bg-[#d9ff00] text-black text-[10px] font-black uppercase tracking-[0.3em] hover:bg-white transition-all active:scale-95"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        DEPLOY NEW OPERATOR
      </button>
    </div>

    <!-- FLASH MESSAGES -->
    <div v-if="flash.success" class="mb-8 p-5 bg-[#d9ff00]/10 border-l-4 border-[#d9ff00] flex items-center gap-4 animate-slide-up">
      <svg class="h-5 w-5 text-[#d9ff00] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="text-[10px] font-black text-[#d9ff00] uppercase tracking-widest">{{ flash.success }}</p>
    </div>
    <div v-if="flash.error" class="mb-8 p-5 bg-rose-500/10 border-l-4 border-rose-600 flex items-center gap-4 animate-slide-up">
      <svg class="h-5 w-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">{{ flash.error }}</p>
    </div>

    <!-- STATS ROW -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-px bg-white/5 mb-12 animate-slide-up" style="animation-delay: 0.05s">
      <div v-for="r in roles" :key="r" class="bg-black p-6">
        <p class="text-[9px] font-black uppercase tracking-[0.3em] mb-3" :class="roleColor(r).split(' ')[0]">
          {{ r.replace('_', ' ') }}
        </p>
        <p class="text-2xl font-black italic tracking-tighter text-white">
          {{ users.filter(u => u.role === r).length }}
        </p>
        <p class="text-[9px] font-bold text-zinc-700 uppercase tracking-widest mt-1">operators</p>
      </div>
    </div>

    <!-- OPERATOR TABLE -->
    <div class="card-premium bg-black overflow-hidden animate-slide-up" style="animation-delay: 0.1s">
      <div class="p-8 border-b border-white/5 bg-white/[0.02] flex items-center justify-between">
        <h3 class="text-xs font-black text-white uppercase tracking-[0.3em]">Network Identity Registry</h3>
        <span class="text-[9px] font-black text-zinc-600 uppercase tracking-widest">{{ users.length }} OPERATORS</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-white/5">
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Operator</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Clearance Level</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Status</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em]">Joined</th>
              <th class="px-8 py-5 text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em] text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr
              v-for="u in users"
              :key="u.id"
              class="hover:bg-white/[0.015] transition-colors group"
              :class="u.id === currentUser.id ? 'bg-[#d9ff00]/[0.02]' : ''"
            >
              <!-- Identity -->
              <td class="px-8 py-6">
                <div class="flex items-center gap-4">
                  <div class="relative h-11 w-11 shrink-0">
                    <div
                      class="h-11 w-11 flex items-center justify-center text-sm font-black italic border transition-colors"
                      :class="u.id === currentUser.id
                        ? 'bg-[#d9ff00]/10 border-[#d9ff00]/50 text-[#d9ff00]'
                        : 'bg-white/5 border-white/10 text-white group-hover:border-white/30'"
                    >
                      {{ u.name.charAt(0).toUpperCase() }}
                    </div>
                    <div
                      v-if="u.id === currentUser.id"
                      class="absolute -top-1 -right-1 h-3 w-3 bg-[#d9ff00] rounded-full"
                      title="You"
                    ></div>
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <p class="text-[11px] font-black text-white uppercase tracking-tight">{{ u.name }}</p>
                      <span v-if="u.id === currentUser.id" class="text-[7px] font-black text-[#d9ff00] uppercase tracking-[0.2em] px-1.5 py-0.5 bg-[#d9ff00]/10 border border-[#d9ff00]/20">YOU</span>
                    </div>
                    <p class="text-[9px] font-bold text-zinc-600 lowercase tracking-wider mt-0.5">{{ u.email }}</p>
                  </div>
                </div>
              </td>

              <!-- Role -->
              <td class="px-8 py-6">
                <div class="flex items-center gap-3">
                  <svg class="h-3.5 w-3.5 shrink-0" :class="roleColor(u.role).split(' ')[0]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="roleIcon(u.role)" />
                  </svg>
                  <select
                    @change="updateRole(u, $event.target.value)"
                    :disabled="editingRole === u.id"
                    class="bg-transparent border-none p-0 text-[10px] font-black uppercase tracking-widest focus:ring-0 cursor-pointer disabled:opacity-50 disabled:cursor-wait"
                    :class="roleColor(u.role).split(' ')[0]"
                  >
                    <option
                      v-for="r in roles"
                      :key="r"
                      :value="r"
                      :selected="u.role === r"
                      class="bg-black text-white"
                    >
                      {{ r.replace('_', ' ') }}
                    </option>
                  </select>
                  <div v-if="editingRole === u.id" class="h-3 w-3 border border-[#d9ff00] border-t-transparent rounded-full animate-spin"></div>
                </div>
              </td>

              <!-- Status -->
              <td class="px-8 py-6">
                <div class="flex items-center gap-2">
                  <div class="h-1.5 w-1.5 rounded-full bg-[#d9ff00] animate-pulse"></div>
                  <span class="text-[9px] font-black text-zinc-500 uppercase tracking-[0.2em]">ACTIVE</span>
                </div>
              </td>

              <!-- Joined date -->
              <td class="px-8 py-6">
                <p class="text-[9px] font-bold text-zinc-600 uppercase tracking-widest">
                  {{ new Date(u.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                </p>
              </td>

              <!-- Actions -->
              <td class="px-8 py-6 text-right">
                <div v-if="u.id !== currentUser.id">
                  <button
                    @click="deleteUser(u)"
                    class="inline-flex items-center gap-2 px-4 py-2 text-[9px] font-black text-zinc-600 hover:text-rose-500 hover:bg-rose-500/10 border border-transparent hover:border-rose-500/20 uppercase tracking-widest transition-all"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    TERMINATE
                  </button>
                </div>
                <span v-else class="text-[9px] font-black text-[#d9ff00]/40 uppercase tracking-widest">SELF</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ─────────────────────────────────────────────────────────────────────── -->
    <!-- CREATE OPERATOR MODAL -->
    <!-- ─────────────────────────────────────────────────────────────────────── -->
    <Transition name="modal">
      <div
        v-if="showCreateModal"
        class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-black/95 backdrop-blur-md"
        @click.self="showCreateModal = false"
      >
        <div class="w-full max-w-md bg-[#080808] border border-white/10 shadow-[0_0_80px_rgba(217,255,0,0.05)] relative animate-slide-up">
          <!-- Header bar -->
          <div class="flex items-center justify-between px-10 py-7 border-b border-white/5">
            <div>
              <h2 class="text-xl font-black italic uppercase tracking-tighter text-white">DEPLOY AGENT</h2>
              <p class="text-[9px] font-black text-[#d9ff00]/50 uppercase tracking-[0.3em] mt-1">New Operator Registration</p>
            </div>
            <button
              @click="showCreateModal = false"
              class="h-9 w-9 flex items-center justify-center text-zinc-600 hover:text-[#d9ff00] hover:bg-white/5 transition-all"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Body -->
          <form @submit.prevent="submitCreate" class="p-10 space-y-7">
            <!-- Name -->
            <div>
              <label class="block text-[9px] font-black text-zinc-500 uppercase tracking-[0.3em] mb-3">Identity Name</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="OPERATOR NAME"
                required
                class="w-full bg-black border px-5 py-3.5 text-xs font-bold text-white outline-none transition-all placeholder:text-zinc-800"
                :class="form.errors.name ? 'border-rose-600' : 'border-white/10 focus:border-[#d9ff00]'"
              />
              <p v-if="form.errors.name" class="mt-2 text-[9px] font-black text-rose-500 uppercase tracking-widest">{{ form.errors.name }}</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-[9px] font-black text-zinc-500 uppercase tracking-[0.3em] mb-3">Neural Link (Email)</label>
              <input
                v-model="form.email"
                type="email"
                placeholder="agent@nike.intel"
                required
                class="w-full bg-black border px-5 py-3.5 text-xs font-bold text-white outline-none transition-all placeholder:text-zinc-800"
                :class="form.errors.email ? 'border-rose-600' : 'border-white/10 focus:border-[#d9ff00]'"
              />
              <p v-if="form.errors.email" class="mt-2 text-[9px] font-black text-rose-500 uppercase tracking-widest">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-[9px] font-black text-zinc-500 uppercase tracking-[0.3em] mb-3">Security Key (Password)</label>
              <input
                v-model="form.password"
                type="password"
                placeholder="••••••••"
                required
                class="w-full bg-black border px-5 py-3.5 text-xs font-bold text-white outline-none transition-all"
                :class="form.errors.password ? 'border-rose-600' : 'border-white/10 focus:border-[#d9ff00]'"
              />
              <p v-if="form.errors.password" class="mt-2 text-[9px] font-black text-rose-500 uppercase tracking-widest">{{ form.errors.password }}</p>
            </div>

            <!-- Role -->
            <div>
              <label class="block text-[9px] font-black text-zinc-500 uppercase tracking-[0.3em] mb-3">Access Clearance</label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  v-for="r in roles"
                  :key="r"
                  type="button"
                  @click="form.role = r"
                  class="flex items-center gap-2 px-4 py-3 border text-[9px] font-black uppercase tracking-wider transition-all"
                  :class="form.role === r
                    ? 'bg-[#d9ff00]/10 border-[#d9ff00] text-[#d9ff00]'
                    : 'bg-black border-white/10 text-zinc-500 hover:border-white/30 hover:text-white'"
                >
                  <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="roleIcon(r)" />
                  </svg>
                  {{ r.replace('_', ' ') }}
                </button>
              </div>
            </div>

            <!-- Submit -->
            <div class="pt-2">
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-4 bg-[#d9ff00] text-black text-[11px] font-black uppercase tracking-[0.4em] hover:bg-white transition-all active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-3"
              >
                <div v-if="form.processing" class="h-4 w-4 border-2 border-black border-t-transparent rounded-full animate-spin"></div>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                {{ form.processing ? 'DEPLOYING...' : 'CONFIRM DEPLOYMENT' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

  </AppLayout>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>