<script setup>
import { computed, ref } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)
const role = computed(() => user.value?.role)
const isSidebarOpen = ref(true)

const navItems = computed(() => {
  const items = [
    { label: 'CONTROL', route: 'dashboard', icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z', roles: ['public','super_admin','analyst','manager','staff'] },
    { label: 'INTEL', route: 'analyst.index', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', roles: ['super_admin','analyst'] },
    { label: 'STRATEGY', route: 'manager.index', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', roles: ['super_admin','manager'] },
    { label: 'PIPELINE', route: 'upload.index', icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12', roles: ['super_admin','staff'] },
    { label: 'IDENTITY', route: 'admin.index', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', roles: ['super_admin'] },
  ]
  return items.filter(i => i.roles.includes(role.value || 'public'))
})

const logout = () => router.post(route('logout'))
</script>

<template>
  <div class="flex min-h-screen bg-[#050505] nike-grid text-white selection:bg-[#d9ff00]/30 selection:text-[#d9ff00]">
    
    <!-- NIKE SIDEBAR -->
    <aside 
      class="fixed inset-y-0 left-0 z-50 w-64 bg-black border-r border-white/5 transition-transform duration-300 lg:translate-x-0"
      :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="flex h-full flex-col p-8">
        <!-- Brand Mark -->
        <div class="mb-16">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-4xl font-black italic tracking-tighter leading-none">NIKE</span>
            <span class="text-[10px] font-black tracking-[0.3em] text-[#d9ff00] rotate-90 origin-left">BI</span>
          </div>
          <p class="text-[9px] font-black text-zinc-600 uppercase tracking-[0.4em]">Intelligence Network</p>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-1">
          <Link
            v-for="item in navItems"
            :key="item.route"
            :href="route(item.route)"
            class="group flex items-center gap-4 px-4 py-4 text-[10px] font-black uppercase tracking-[0.2em] transition-all duration-300 border-l-2"
            :class="route().current(item.route) 
              ? 'border-[#d9ff00] text-[#d9ff00] bg-white/[0.02]' 
              : 'border-transparent text-zinc-500 hover:text-white hover:bg-white/[0.01]'"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
            </svg>
            {{ item.label }}
          </Link>
        </nav>

        <!-- Profile / Auth -->
        <div class="mt-auto pt-8 border-t border-white/5">
          <div v-if="user" class="flex flex-col gap-4">
            <div class="px-4">
              <p class="text-[10px] font-black text-white truncate">{{ user.name.toUpperCase() }}</p>
              <p class="text-[9px] font-bold text-[#d9ff00] uppercase tracking-widest mt-1 opacity-70">{{ role?.replace('_', ' ') }}</p>
            </div>
            <button @click="logout" class="flex items-center gap-3 px-4 py-3 text-[9px] font-black text-zinc-500 hover:text-rose-500 uppercase tracking-widest transition-colors">
               <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
               Terminate Session
            </button>
          </div>
          <div v-else>
            <Link :href="route('login')" class="flex w-full items-center justify-center py-4 text-[10px] font-black bg-[#d9ff00] text-black uppercase tracking-[0.2em] hover:bg-white transition-colors">
              Access Terminal
            </Link>
          </div>
        </div>
      </div>
    </aside>

    <!-- MAIN FRAME -->
    <main class="flex-1 lg:pl-64">
      <!-- High-Tech Header -->
      <header class="h-20 flex items-center justify-between px-10 border-b border-white/5 bg-black/80 backdrop-blur-sm sticky top-0 z-40">
        <div class="flex items-center gap-6">
          <button @click="isSidebarOpen = !isSidebarOpen" class="lg:hidden text-white">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
          </button>
          <div class="flex items-center gap-3">
             <div class="h-1 w-1 rounded-full bg-[#d9ff00] animate-ping"></div>
             <p class="text-[10px] font-black text-zinc-500 uppercase tracking-[0.4em]">System Status: Optimal</p>
          </div>
        </div>

        <div class="hidden md:flex items-center gap-8">
           <div class="flex flex-col items-end">
              <p class="text-[9px] font-black text-zinc-600 uppercase tracking-widest">Latency</p>
              <p class="text-[10px] font-bold text-white">14ms</p>
           </div>
           <div class="h-8 w-[1px] bg-white/5"></div>
           <div class="flex flex-col items-end text-right">
              <p class="text-[9px] font-black text-zinc-600 uppercase tracking-widest">Date Node</p>
              <p class="text-[10px] font-bold text-white">{{ new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).toUpperCase() }}</p>
           </div>
        </div>
      </header>

      <!-- Dashboard Viewport -->
      <div class="p-10 max-w-[1600px] mx-auto">
        <slot />
      </div>
    </main>

  </div>
</template>