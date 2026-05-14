<script setup>
import { computed, ref, onMounted } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)
const role = computed(() => user.value?.role)
const isSidebarOpen = ref(false)

// Set sidebar open by default only on desktop
onMounted(() => {
  if (window.innerWidth >= 1024) {
    isSidebarOpen.value = true
  }
})

const navItems = computed(() => {
  const items = [
    { label: 'Network Overview', route: 'dashboard', icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z', roles: ['public','super_admin','analyst','manager','staff'] },
    { label: 'Product Catalog', route: 'catalog.index', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', roles: ['public','super_admin','analyst','manager','staff'] },
    { label: 'Customer Voice', route: 'review.index', icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', roles: ['public','super_admin','analyst','manager','staff'] },
    { label: 'Data Intelligence', route: 'analyst.index', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', roles: ['super_admin','analyst'] },
    { label: 'Growth Strategy', route: 'manager.index', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', roles: ['super_admin','manager'] },
    { label: 'Ingestion Pipeline', route: 'upload.index', icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12', roles: ['super_admin','staff'] },
    { label: 'Team Directory', route: 'admin.index', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', roles: ['super_admin'] },
  ]
  return items.filter(i => i.roles.includes(role.value || 'public'))
})

const logout = () => router.post(route('logout'))
</script>

<template>
  <div class="flex min-h-screen bg-black nike-grid text-white selection:bg-[#d9ff00]/30 selection:text-[#d9ff00]">
    
    <!-- SIDEBAR BACKDROP (MOBILE ONLY) -->
    <div 
        v-if="isSidebarOpen" 
        @click="isSidebarOpen = false"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden transition-opacity duration-500"
    ></div>

    <!-- NIKE SIDEBAR -->
    <aside 
      class="fixed inset-y-0 left-0 z-50 w-72 bg-black border-r border-white/5 transition-transform duration-500 lg:translate-x-0 shadow-[20px_0_50px_rgba(0,0,0,0.5)]"
      :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="flex h-full flex-col p-10">
        <!-- Brand Mark -->
        <div class="mb-20">
          <div class="flex items-center gap-3 mb-3 group cursor-pointer" @click="router.visit('/')">
            <span class="text-5xl font-black italic tracking-tighter leading-none font-header group-hover:text-[#d9ff00] transition-colors">NIKE</span>
            <div class="flex flex-col">
                <span class="text-[10px] font-black tracking-[0.4em] text-[#d9ff00]">INTEL</span>
                <div class="h-[2px] w-full bg-[#d9ff00]/20 mt-1 group-hover:bg-[#d9ff00] transition-all"></div>
            </div>
          </div>
          <p class="text-[8px] font-black text-zinc-600 uppercase tracking-[0.5em] animate-pulse">Operational Hub v2.0</p>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-2 overflow-y-auto custom-scrollbar pr-2">
          <p class="text-[9px] font-black text-zinc-700 uppercase tracking-[0.3em] mb-6 pl-4">System Nodes</p>
          <Link
            v-for="item in navItems"
            :key="item.route"
            :href="route(item.route)"
            @click="window.innerWidth < 1024 ? isSidebarOpen = false : null"
            class="group flex items-center gap-5 px-5 py-4 text-[9px] font-black uppercase tracking-[0.25em] transition-all duration-500 relative overflow-hidden"
            :class="route().current(item.route) 
              ? 'text-[#d9ff00] bg-white/[0.03]' 
              : 'text-zinc-600 hover:text-white hover:bg-white/[0.01]'"
          >
            <div 
                v-if="route().current(item.route)" 
                class="absolute left-0 top-0 bottom-0 w-[3px] bg-[#d9ff00] shadow-[0_0_15px_#d9ff00]"
            ></div>
            <svg class="h-4 w-4 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
            </svg>
            {{ item.label }}
          </Link>
        </nav>

        <!-- Profile / Auth -->
        <div class="mt-auto pt-10 border-t border-white/5">
          <div v-if="user" class="flex flex-col gap-6">
            <div class="px-5">
              <p class="text-[8px] font-black text-zinc-500 uppercase tracking-widest mb-2">Authenticated As</p>
              <p class="text-[11px] font-black text-white truncate font-header">{{ user.name.toUpperCase() }}</p>
              <p class="text-[9px] font-bold text-[#d9ff00] uppercase tracking-widest mt-1 italic opacity-80">{{ role?.replace('_', ' ') }}</p>
            </div>
            <button @click="logout" class="flex items-center gap-4 px-5 py-4 text-[9px] font-black text-zinc-600 hover:text-rose-500 uppercase tracking-widest transition-all hover:bg-rose-500/5 group">
               <svg class="h-4 w-4 transition-transform group-hover:rotate-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
               Terminate Session
            </button>
          </div>
          <div v-else>
            <Link :href="route('login')" class="btn-premium w-full !bg-[#d9ff00] !text-black hover:!bg-white">
              Access Terminal
            </Link>
          </div>
        </div>
      </div>
    </aside>

    <!-- MAIN FRAME -->
    <main class="flex-1 lg:pl-72 transition-all duration-500">
      <!-- High-Tech Header -->
      <header class="h-24 flex items-center justify-between px-6 md:px-12 border-b border-white/5 bg-black/80 backdrop-blur-xl sticky top-0 z-40">
        <div class="flex items-center gap-10">
          <button @click="isSidebarOpen = !isSidebarOpen" class="lg:hidden text-white hover:text-[#d9ff00] transition-colors p-2 -ml-2">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
          </button>
          
          <div class="hidden sm:flex items-center gap-4">
             <div class="h-2 w-2 rounded-full bg-[#d9ff00] shadow-[0_0_10px_#d9ff00] animate-pulse"></div>
             <div class="flex flex-col">
                <p class="text-[9px] font-black text-white uppercase tracking-[0.3em]">System Online</p>
                <p class="text-[8px] font-bold text-zinc-600 uppercase tracking-widest">Global Data Sync Active</p>
             </div>
          </div>
        </div>

        <div class="flex items-center gap-12">
           <div class="hidden xl:flex items-center gap-8">
               <div class="flex flex-col items-end">
                  <p class="text-[8px] font-black text-zinc-600 uppercase tracking-widest mb-1">Network Latency</p>
                  <p class="text-[10px] font-black text-white font-header italic">0.024ms</p>
               </div>
               <div class="h-8 w-[1px] bg-white/10 rotate-12"></div>
               <div class="flex flex-col items-end text-right">
                  <p class="text-[8px] font-black text-zinc-600 uppercase tracking-widest mb-1">Current Node</p>
                  <p class="text-[10px] font-black text-white font-header">{{ new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).toUpperCase() }}</p>
               </div>
           </div>
           
           <div class="flex items-center gap-4">
              <div class="h-10 w-10 bg-white/5 border border-white/10 flex items-center justify-center group cursor-pointer hover:border-[#d9ff00] transition-all">
                <svg class="h-4 w-4 text-zinc-500 group-hover:text-[#d9ff00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
              </div>
           </div>
        </div>
      </header>

      <!-- Dashboard Viewport -->
      <div class="p-4 md:p-8 w-full mx-auto min-h-[calc(100vh-6rem)]">
        <slot />
      </div>

      <!-- Tech Footer -->
      <footer class="px-6 md:px-12 py-10 border-t border-white/5 bg-black/50">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <p class="text-[9px] font-black text-zinc-700 uppercase tracking-[0.4em] text-center md:text-left">© NIKE INTELLIGENCE NETWORK. ALL RIGHTS RESERVED.</p>
            <div class="flex items-center gap-8">
                <a href="#" class="text-[9px] font-black text-zinc-700 hover:text-white uppercase tracking-widest transition-colors">Privacy Protocol</a>
                <a href="#" class="text-[9px] font-black text-zinc-700 hover:text-white uppercase tracking-widest transition-colors">System status</a>
            </div>
        </div>
      </footer>
    </main>
  </div>
</template>