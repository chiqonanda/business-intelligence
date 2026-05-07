<template>
  <AppLayout>
    <div class="admin-page">

      <!-- Header -->
      <div class="page-header">
        <div>
          <p class="page-eyebrow">SYSTEM ADMIN</p>
          <h1 class="page-title">USER MANAGEMENT</h1>
        </div>
        <button class="btn-add" @click="showAddModal = true">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          TAMBAH USER
        </button>
      </div>

      <!-- Stats strip -->
      <div class="stats-strip">
        <div class="strip-item" v-for="s in roleStats" :key="s.role">
          <span class="strip-count">{{ s.count }}</span>
          <span class="strip-label">{{ s.label }}</span>
          <span class="strip-dot" :class="'dot-' + s.role"></span>
        </div>
      </div>

      <!-- User table -->
      <div class="table-card">
        <div class="table-top">
          <p class="chart-label">DAFTAR USER ({{ users.length }})</p>
          <div class="search-wrap">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input v-model="search" type="text" placeholder="Cari nama atau email..." class="search-input" />
          </div>
        </div>

        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>NAMA</th>
              <th>EMAIL</th>
              <th>ROLE</th>
              <th>BERGABUNG</th>
              <th>AKSI</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="filteredUsers.length === 0">
              <td colspan="6" class="empty-row">Tidak ada user ditemukan.</td>
            </tr>
            <tr v-for="(user, i) in filteredUsers" :key="user.id" class="data-row">
              <td class="idx">{{ i + 1 }}</td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar">{{ initials(user.name) }}</div>
                  <span class="user-name">{{ user.name }}</span>
                </div>
              </td>
              <td class="mono">{{ user.email }}</td>
              <td>
                <select
                  :value="user.role"
                  class="role-select"
                  :class="'role-' + user.role"
                  :disabled="user.id === currentUser.id"
                  @change="updateRole(user, $event.target.value)"
                >
                  <option value="super_admin">Super Admin</option>
                  <option value="analyst">Analyst</option>
                  <option value="manager">Manager</option>
                  <option value="staff">Staff</option>
                </select>
              </td>
              <td class="date-cell">{{ formatDate(user.created_at) }}</td>
              <td>
                <button
                  class="btn-delete"
                  :disabled="user.id === currentUser.id"
                  @click="confirmDelete(user)"
                >
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ETL Log section -->
      <div class="log-card">
        <p class="chart-label">ETL UPLOAD LOG</p>
        <div class="log-list">
          <div v-if="!uploadLogs.length" class="log-empty">
            Belum ada upload CSV.
          </div>
          <div v-for="log in uploadLogs" :key="log.filename" class="log-item">
            <div class="log-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
            </div>
            <div class="log-info">
              <span class="log-filename">{{ log.filename }}</span>
              <span class="log-date">{{ log.uploaded_at }}</span>
            </div>
            <span class="log-size">{{ log.size }}</span>
            <span class="log-status status-done">DONE</span>
          </div>
        </div>
      </div>

      <!-- Delete confirm modal -->
      <Transition name="modal">
        <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
          <div class="modal-box">
            <p class="modal-title">HAPUS USER?</p>
            <p class="modal-desc">
              Akun <strong>{{ deleteTarget.name }}</strong> akan dihapus permanen.
              Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="modal-actions">
              <button class="btn-cancel" @click="deleteTarget = null">Batal</button>
              <button class="btn-confirm-delete" @click="deleteUser">Hapus</button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Add user modal -->
      <Transition name="modal">
        <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
          <div class="modal-box">
            <p class="modal-title">TAMBAH USER BARU</p>
            <form @submit.prevent="addUser" class="add-form">
              <div class="add-field">
                <label>NAMA</label>
                <input v-model="newUser.name" type="text" placeholder="Nama lengkap" required />
              </div>
              <div class="add-field">
                <label>EMAIL</label>
                <input v-model="newUser.email" type="email" placeholder="email@nike.test" required />
              </div>
              <div class="add-field">
                <label>PASSWORD</label>
                <input v-model="newUser.password" type="password" placeholder="Min. 8 karakter" required />
              </div>
              <div class="add-field">
                <label>ROLE</label>
                <select v-model="newUser.role">
                  <option value="staff">Staff</option>
                  <option value="analyst">Analyst</option>
                  <option value="manager">Manager</option>
                  <option value="super_admin">Super Admin</option>
                </select>
              </div>
              <div class="modal-actions">
                <button type="button" class="btn-cancel" @click="showAddModal = false">Batal</button>
                <button type="submit" class="btn-confirm-add">Tambah</button>
              </div>
            </form>
          </div>
        </div>
      </Transition>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ users: Array })
const page  = usePage()
const currentUser = computed(() => page.props.auth.user)

const search       = ref('')
const deleteTarget = ref(null)
const showAddModal = ref(false)
const uploadLogs   = ref(page.props.upload_logs || [])

const newUser = ref({ name: '', email: '', password: '', role: 'staff' })

// ── Computed ──────────────────────────────────────────────────────────────────
const filteredUsers = computed(() => {
  const q = search.value.toLowerCase()
  if (!q) return props.users
  return props.users.filter(u =>
    u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)
  )
})

const roleStats = computed(() => {
  const roles = ['super_admin', 'analyst', 'manager', 'staff']
  const labels = { super_admin: 'Super Admin', analyst: 'Analyst', manager: 'Manager', staff: 'Staff' }
  return roles.map(r => ({
    role:  r,
    label: labels[r],
    count: props.users.filter(u => u.role === r).length,
  }))
})

// ── Helpers ───────────────────────────────────────────────────────────────────
function initials(name) {
  return name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || '?'
}

function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }) : '-'
}

// ── Actions ───────────────────────────────────────────────────────────────────
function updateRole(user, role) {
  router.patch(route('admin.users.role', user.id), { role }, { preserveScroll: true })
}

function confirmDelete(user) { deleteTarget.value = user }

function deleteUser() {
  if (!deleteTarget.value) return
  router.delete(route('admin.users.destroy', deleteTarget.value.id), {
    preserveScroll: true,
    onFinish: () => { deleteTarget.value = null },
  })
}

function addUser() {
  router.post(route('admin.users.store'), newUser.value, {
    preserveScroll: true,
    onSuccess: () => {
      showAddModal.value = false
      newUser.value = { name: '', email: '', password: '', role: 'staff' }
    },
  })
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&family=JetBrains+Mono:wght@400&display=swap');

.admin-page { display: flex; flex-direction: column; gap: 1.25rem; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

.page-header { display: flex; align-items: flex-end; justify-content: space-between; }
.page-eyebrow { font-size: 11px; letter-spacing: 3px; color: rgba(255,255,255,0.25); margin-bottom: 0.25rem; }
.page-title { font-family: 'Bebas Neue', sans-serif; font-size: 42px; color: #fff; letter-spacing: 1px; line-height: 1; }

.btn-add {
  display: flex; align-items: center; gap: 7px;
  background: #fff; color: #000; border: none; border-radius: 4px;
  padding: 8px 16px; font-family: 'Bebas Neue', sans-serif;
  font-size: 14px; letter-spacing: 2px; cursor: pointer; transition: background 0.15s;
}
.btn-add:hover { background: #e5e5e5; }

/* Stats strip */
.stats-strip {
  display: grid; grid-template-columns: repeat(4, 1fr);
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; overflow: hidden;
}
.strip-item {
  display: flex; align-items: center; gap: 10px;
  padding: 1rem 1.25rem; border-right: 1px solid rgba(255,255,255,0.07);
}
.strip-item:last-child { border-right: none; }
.strip-count { font-family: 'Bebas Neue', sans-serif; font-size: 32px; color: #fff; }
.strip-label { font-size: 12px; color: rgba(255,255,255,0.3); flex: 1; }
.strip-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dot-super_admin { background: #fff; }
.dot-analyst     { background: #93c5fd; }
.dot-manager     { background: #fde047; }
.dot-staff       { background: #86efac; }

/* Table */
.table-card {
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; overflow: hidden;
}
.table-top {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.07);
}
.chart-label { font-size: 10px; letter-spacing: 2.5px; color: rgba(255,255,255,0.3); }
.search-wrap {
  display: flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 4px; padding: 6px 12px; color: rgba(255,255,255,0.3);
}
.search-input {
  background: transparent; border: none; outline: none;
  font-size: 13px; color: #fff; font-family: 'DM Sans', sans-serif; width: 200px;
}
.search-input::placeholder { color: rgba(255,255,255,0.25); }

.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table thead tr { border-bottom: 1px solid rgba(255,255,255,0.07); }
.data-table th {
  padding: 10px 16px; text-align: left;
  font-size: 10px; letter-spacing: 1.5px; color: rgba(255,255,255,0.25); font-weight: 500;
}
.data-row { border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.1s; }
.data-row:hover { background: rgba(255,255,255,0.02); }
.data-table td { padding: 12px 16px; color: rgba(255,255,255,0.6); }
.idx { color: rgba(255,255,255,0.15); font-size: 12px; }
.mono { font-family: 'JetBrains Mono', monospace; font-size: 12px; }
.date-cell { font-size: 12px; color: rgba(255,255,255,0.3); }

.user-cell { display: flex; align-items: center; gap: 10px; }
.user-avatar {
  width: 30px; height: 30px; border-radius: 50%;
  background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.12);
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 500; color: #fff; flex-shrink: 0;
}
.user-name { color: rgba(255,255,255,0.8); font-weight: 500; }

.role-select {
  background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 4px; padding: 4px 10px; font-size: 12px;
  font-family: 'DM Sans', sans-serif; outline: none; cursor: pointer;
  transition: border-color 0.15s;
}
.role-select:disabled { opacity: 0.4; cursor: not-allowed; }
.role-select.role-super_admin { color: #fff; }
.role-select.role-analyst     { color: #93c5fd; }
.role-select.role-manager     { color: #fde047; }
.role-select.role-staff       { color: #86efac; }

.btn-delete {
  display: flex; align-items: center; justify-content: center;
  width: 28px; height: 28px; background: transparent;
  border: 1px solid rgba(239,68,68,0.2); border-radius: 4px;
  color: rgba(239,68,68,0.5); cursor: pointer; transition: all 0.15s;
}
.btn-delete:hover:not(:disabled) { background: rgba(239,68,68,0.1); color: #fca5a5; border-color: rgba(239,68,68,0.4); }
.btn-delete:disabled { opacity: 0.2; cursor: not-allowed; }

.empty-row { text-align: center; padding: 2rem; color: rgba(255,255,255,0.2); }

/* Log card */
.log-card {
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 8px; padding: 1.25rem;
}
.log-list { display: flex; flex-direction: column; gap: 6px; margin-top: 1rem; }
.log-empty { font-size: 13px; color: rgba(255,255,255,0.2); padding: 0.5rem 0; }
.log-item {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 12px; border-radius: 6px;
  background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);
}
.log-icon { color: rgba(255,255,255,0.3); flex-shrink: 0; display: flex; }
.log-info { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.log-filename { font-size: 13px; color: rgba(255,255,255,0.7); font-family: 'JetBrains Mono', monospace; }
.log-date { font-size: 11px; color: rgba(255,255,255,0.25); }
.log-size { font-size: 11px; color: rgba(255,255,255,0.25); }
.log-status {
  font-size: 10px; letter-spacing: 1px; padding: 3px 8px; border-radius: 3px; font-weight: 600;
}
.status-done { background: rgba(34,197,94,0.15); color: #86efac; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.7);
  display: flex; align-items: center; justify-content: center;
  z-index: 200; backdrop-filter: blur(4px);
}
.modal-box {
  background: #1a1a1a; border: 1px solid rgba(255,255,255,0.12);
  border-radius: 10px; padding: 2rem; width: 100%; max-width: 400px;
}
.modal-title {
  font-family: 'Bebas Neue', sans-serif; font-size: 24px;
  color: #fff; letter-spacing: 1px; margin-bottom: 0.75rem;
}
.modal-desc { font-size: 14px; color: rgba(255,255,255,0.45); line-height: 1.6; margin-bottom: 1.5rem; }
.modal-desc strong { color: rgba(255,255,255,0.8); }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; }

.btn-cancel {
  padding: 8px 18px; background: transparent; border: 1px solid rgba(255,255,255,0.12);
  border-radius: 4px; color: rgba(255,255,255,0.5); font-size: 13px; cursor: pointer; transition: all 0.15s;
}
.btn-cancel:hover { border-color: rgba(255,255,255,0.3); color: #fff; }

.btn-confirm-delete {
  padding: 8px 18px; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3);
  border-radius: 4px; color: #fca5a5; font-size: 13px; cursor: pointer; transition: all 0.15s;
}
.btn-confirm-delete:hover { background: rgba(239,68,68,0.25); }

.btn-confirm-add {
  padding: 8px 18px; background: #fff; border: none;
  border-radius: 4px; color: #000; font-family: 'Bebas Neue', sans-serif;
  font-size: 16px; letter-spacing: 2px; cursor: pointer; transition: background 0.15s;
}
.btn-confirm-add:hover { background: #e5e5e5; }

/* Add form */
.add-form { display: flex; flex-direction: column; gap: 1rem; }
.add-field { display: flex; flex-direction: column; gap: 6px; }
.add-field label { font-size: 10px; letter-spacing: 2px; color: rgba(255,255,255,0.3); }
.add-field input, .add-field select {
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 4px; padding: 8px 12px; font-size: 13px; color: #fff;
  font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.15s;
}
.add-field input:focus, .add-field select:focus { border-color: rgba(255,255,255,0.3); }
.add-field input::placeholder { color: rgba(255,255,255,0.2); }

/* Modal transition */
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s, transform 0.2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal-box, .modal-leave-to .modal-box { transform: scale(0.96) translateY(8px); }
</style>