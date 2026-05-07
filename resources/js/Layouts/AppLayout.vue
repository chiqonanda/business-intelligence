<script setup>
import { computed } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)
const role = computed(() => user.value?.role)

const navItems = computed(() => {
  const items = [
    { label: 'Overview', route: 'dashboard', icon: '▦', roles: ['super_admin','analyst','manager','staff'] },
    { label: 'Analytics', route: 'analyst.index', icon: '◈', roles: ['super_admin','analyst'] },
    { label: 'Insights', route: 'manager.index', icon: '◉', roles: ['super_admin','manager'] },
    { label: 'Upload', route: 'upload.index', icon: '⊕', roles: ['super_admin','staff'] },
    { label: 'Admin', route: 'admin.index', icon: '⚙', roles: ['super_admin'] },
  ]
  return items.filter(i => i.roles.includes(role.value))
})

const roleBadgeColor = computed(() => ({
  super_admin: '#c8ff00',
  analyst: '#60a5fa',
  manager: '#f59e0b',
  staff: '#aaa',
}[role.value] ?? '#aaa'))

const roleLabel = computed(() => ({
  super_admin: 'Super Admin',
  analyst: 'Data Analyst',
  manager: 'Manager',
  staff: 'Staff Input',
}[role.value] ?? role.value))

const logout = () => router.post(route('logout'))
</script>

<template>
  <div class="app-shell">
    <!-- TOPBAR -->
    <nav class="topbar">
      <div class="topbar-left">
        <div class="topbar-logo">NIKE</div>
        <div class="topbar-divider"></div>
        <div class="topbar-product">Sales Intelligence</div>
      </div>
      <div class="topbar-center">
        <Link
          v-for="item in navItems"
          :key="item.route"
          :href="route(item.route)"
          class="top-nav-link"
          :class="{ active: $page.url.startsWith('/' + item.route.replace('.index','').replace('dashboard','dashboard')) }"
        >
          <span class="nav-icon">{{ item.icon }}</span>
          {{ item.label }}
        </Link>
      </div>
      <div class="topbar-right">
        <div class="user-pill">
          <span class="role-dot" :style="{ background: roleBadgeColor }"></span>
          <span class="role-label">{{ roleLabel }}</span>
          <span class="user-name">{{ user?.name }}</span>
        </div>
        <button class="logout-btn" @click="logout">↪</button>
      </div>
    </nav>

    <!-- MAIN -->
    <main class="main-content">
      <slot />
    </main>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800;900&family=Barlow:wght@300;400;500&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

.app-shell {
  min-height: 100vh;
  background: #0d0d0d;
  font-family: 'Barlow', sans-serif;
  display: flex;
  flex-direction: column;
}

/* TOPBAR */
.topbar {
  height: 52px;
  background: #0a0a0a;
  border-bottom: 1px solid rgba(255,255,255,0.07);
  display: flex;
  align-items: center;
  padding: 0 24px;
  gap: 0;
  position: sticky;
  top: 0;
  z-index: 100;
  backdrop-filter: blur(10px);
}
.topbar-left { display: flex; align-items: center; gap: 12px; margin-right: 32px; }
.topbar-logo {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 18px;
  font-weight: 900;
  letter-spacing: 4px;
  color: #f5f4f0;
}
.topbar-divider { width: 1px; height: 18px; background: rgba(255,255,255,0.1); }
.topbar-product {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 10px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #555;
}
.topbar-center { display: flex; align-items: center; flex: 1; gap: 2px; }
.top-nav-link {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #555;
  padding: 6px 14px;
  border-radius: 5px;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: color .15s, background .15s;
}
.top-nav-link:hover { color: #f5f4f0; }
.top-nav-link.active { color: #0a0a0a; background: #f5f4f0; }
.nav-icon { font-size: 12px; }

.topbar-right { display: flex; align-items: center; gap: 10px; margin-left: auto; }
.user-pill {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 4px 12px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px;
}
.role-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.role-label {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 10px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: #888;
}
.user-name {
  font-size: 12px;
  color: #f5f4f0;
  font-weight: 500;
}
.logout-btn {
  background: none;
  border: 1px solid rgba(255,255,255,0.1);
  color: #666;
  width: 30px; height: 30px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  display: flex; align-items: center; justify-content: center;
  transition: color .15s, border-color .15s, background .15s;
}
.logout-btn:hover { color: #f5f4f0; border-color: rgba(255,255,255,0.3); background: rgba(255,255,255,0.05); }

.main-content { flex: 1; padding: 28px 28px; }
</style>