<script setup>
import { ref } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)

const submit = () => {
  form.post(route('login.post'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Login — Nike Sales BI" />

  <div class="nike-login">
    <!-- Left Panel -->
    <div class="left-panel">
      <div class="left-content">
        <div class="brand-mark">NIKE</div>
        <div class="tagline">
          <span class="tag-line1">JUST</span>
          <span class="tag-line2">ANALYZE.</span>
        </div>
        <p class="brand-desc">
          Sales Intelligence Platform.<br>
          India Region FY 2024–25.
        </p>
        <div class="stat-row">
          <div class="stat"><span class="stat-num">2,500</span><span class="stat-lbl">Transactions</span></div>
          <div class="stat-div"></div>
          <div class="stat"><span class="stat-num">9</span><span class="stat-lbl">Cities</span></div>
          <div class="stat-div"></div>
          <div class="stat"><span class="stat-num">5</span><span class="stat-lbl">Product Lines</span></div>
        </div>
      </div>
      <div class="swoosh-bg">NIKE</div>
    </div>

    <!-- Right Panel -->
    <div class="right-panel">
      <div class="form-wrap">
        <div class="form-eyebrow">Sales Intelligence Platform</div>
        <h1 class="form-title">Sign In</h1>
        <p class="form-sub">Access your dashboard</p>

        <form @submit.prevent="submit" class="nike-form">
          <!-- Email -->
          <div class="field-group" :class="{ 'has-error': form.errors.email }">
            <label class="field-label">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="field-input"
              placeholder="analyst@nike.test"
              autocomplete="username"
              required
            />
            <span v-if="form.errors.email" class="field-error">{{ form.errors.email }}</span>
          </div>

          <!-- Password -->
          <div class="field-group" :class="{ 'has-error': form.errors.password }">
            <label class="field-label">Password</label>
            <div class="input-wrap">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="field-input"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              />
              <button type="button" class="toggle-pw" @click="showPassword = !showPassword">
                {{ showPassword ? 'HIDE' : 'SHOW' }}
              </button>
            </div>
            <span v-if="form.errors.password" class="field-error">{{ form.errors.password }}</span>
          </div>

          <!-- Remember -->
          <div class="check-row">
            <label class="check-label">
              <input v-model="form.remember" type="checkbox" class="check-input" />
              <span class="check-custom"></span>
              <span class="check-text">Remember me</span>
            </label>
          </div>

          <!-- Submit -->
          <button type="submit" class="nike-btn" :disabled="form.processing">
            <span v-if="!form.processing">SIGN IN</span>
            <span v-else class="loading-dots">
              <span></span><span></span><span></span>
            </span>
          </button>
        </form>

        <!-- Test accounts hint -->
        <div class="test-accounts">
          <div class="test-title">TEST ACCOUNTS</div>
          <div class="test-grid">
            <div class="test-item" @click="form.email = 'superadmin@nike.test'; form.password = 'password'">
              <span class="test-role">Super Admin</span>
              <span class="test-email">superadmin@nike.test</span>
            </div>
            <div class="test-item" @click="form.email = 'analyst@nike.test'; form.password = 'password'">
              <span class="test-role">Analyst</span>
              <span class="test-email">analyst@nike.test</span>
            </div>
            <div class="test-item" @click="form.email = 'manager@nike.test'; form.password = 'password'">
              <span class="test-role">Manager</span>
              <span class="test-email">manager@nike.test</span>
            </div>
            <div class="test-item" @click="form.email = 'staff@nike.test'; form.password = 'password'">
              <span class="test-role">Staff</span>
              <span class="test-email">staff@nike.test</span>
            </div>
          </div>
          <div class="test-pw">Password semua: <strong>password</strong></div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

.nike-login {
  display: flex;
  min-height: 100vh;
  font-family: 'Barlow', sans-serif;
}

/* LEFT */
.left-panel {
  flex: 1;
  background: #0a0a0a;
  color: #f5f4f0;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 48px;
  position: relative;
  overflow: hidden;
}
.swoosh-bg {
  position: absolute;
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 280px;
  font-weight: 900;
  color: rgba(255,255,255,0.03);
  letter-spacing: -10px;
  top: -40px;
  right: -60px;
  pointer-events: none;
  user-select: none;
}
.left-content { position: relative; z-index: 1; }
.brand-mark {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 6px;
  color: #666;
  margin-bottom: 24px;
}
.tagline {
  display: flex;
  flex-direction: column;
  font-family: 'Barlow Condensed', sans-serif;
  font-weight: 900;
  line-height: 0.85;
  margin-bottom: 24px;
}
.tag-line1 { font-size: clamp(64px, 8vw, 100px); color: #333; }
.tag-line2 { font-size: clamp(64px, 8vw, 100px); color: #f5f4f0; }
.brand-desc {
  font-size: 13px;
  color: #666;
  line-height: 1.7;
  margin-bottom: 32px;
  font-weight: 300;
}
.stat-row {
  display: flex;
  align-items: center;
  gap: 20px;
}
.stat { display: flex; flex-direction: column; gap: 2px; }
.stat-num {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 28px;
  font-weight: 800;
  color: #f5f4f0;
  letter-spacing: -1px;
}
.stat-lbl {
  font-size: 10px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #555;
}
.stat-div {
  width: 1px;
  height: 32px;
  background: rgba(255,255,255,0.08);
}

/* RIGHT */
.right-panel {
  width: 480px;
  background: #f5f4f0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 40px;
}
.form-wrap { width: 100%; max-width: 360px; }
.form-eyebrow {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 10px;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: #999;
  margin-bottom: 8px;
}
.form-title {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 48px;
  font-weight: 900;
  color: #0a0a0a;
  letter-spacing: -1px;
  line-height: 1;
  margin-bottom: 6px;
}
.form-sub {
  font-size: 13px;
  color: #888;
  margin-bottom: 36px;
}

/* FORM */
.nike-form { display: flex; flex-direction: column; gap: 20px; }
.field-group { display: flex; flex-direction: column; gap: 6px; }
.field-label {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 10px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #555;
  font-weight: 600;
}
.field-input {
  background: #fff;
  border: 1.5px solid #e0e0e0;
  border-radius: 6px;
  padding: 12px 14px;
  font-family: 'Barlow', sans-serif;
  font-size: 14px;
  color: #0a0a0a;
  outline: none;
  transition: border-color .2s;
  width: 100%;
}
.field-input:focus { border-color: #0a0a0a; }
.has-error .field-input { border-color: #e05555; }
.field-error { font-size: 11px; color: #e05555; }
.input-wrap { position: relative; }
.input-wrap .field-input { padding-right: 60px; }
.toggle-pw {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 10px;
  letter-spacing: 1px;
  color: #999;
  cursor: pointer;
  font-weight: 600;
}
.toggle-pw:hover { color: #0a0a0a; }

/* CHECKBOX */
.check-row { display: flex; align-items: center; }
.check-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
.check-input { display: none; }
.check-custom {
  width: 16px; height: 16px;
  border: 1.5px solid #ccc;
  border-radius: 3px;
  background: #fff;
  transition: all .15s;
  flex-shrink: 0;
  position: relative;
}
.check-input:checked + .check-custom {
  background: #0a0a0a;
  border-color: #0a0a0a;
}
.check-input:checked + .check-custom::after {
  content: '';
  position: absolute;
  left: 3px; top: 1px;
  width: 5px; height: 9px;
  border: 2px solid #fff;
  border-top: none;
  border-left: none;
  transform: rotate(45deg);
}
.check-text { font-size: 12px; color: #666; }

/* BUTTON */
.nike-btn {
  background: #0a0a0a;
  color: #f5f4f0;
  border: none;
  border-radius: 6px;
  padding: 14px;
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 13px;
  letter-spacing: 3px;
  font-weight: 700;
  cursor: pointer;
  transition: background .2s, transform .1s;
  margin-top: 4px;
}
.nike-btn:hover:not(:disabled) { background: #222; }
.nike-btn:active:not(:disabled) { transform: scale(0.99); }
.nike-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.loading-dots { display: flex; justify-content: center; gap: 4px; }
.loading-dots span {
  width: 5px; height: 5px;
  background: #f5f4f0;
  border-radius: 50%;
  animation: dot .8s infinite;
}
.loading-dots span:nth-child(2) { animation-delay: .15s; }
.loading-dots span:nth-child(3) { animation-delay: .3s; }
@keyframes dot { 0%,80%,100% { opacity:.2; transform:scale(.8); } 40% { opacity:1; transform:scale(1); } }

/* TEST ACCOUNTS */
.test-accounts {
  margin-top: 32px;
  padding: 16px;
  background: #fff;
  border: 1px solid #e8e8e8;
  border-radius: 8px;
}
.test-title {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 9px;
  letter-spacing: 2px;
  color: #aaa;
  margin-bottom: 10px;
}
.test-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 10px; }
.test-item {
  padding: 8px 10px;
  background: #f5f4f0;
  border-radius: 5px;
  cursor: pointer;
  transition: background .15s;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.test-item:hover { background: #e8e8e8; }
.test-role {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 10px;
  letter-spacing: 1px;
  text-transform: uppercase;
  font-weight: 700;
  color: #0a0a0a;
}
.test-email { font-size: 10px; color: #888; }
.test-pw { font-size: 11px; color: #aaa; }
.test-pw strong { color: #555; }

@media (max-width: 768px) {
  .nike-login { flex-direction: column; }
  .left-panel { min-height: 220px; padding: 32px 24px; justify-content: flex-end; }
  .right-panel { width: 100%; padding: 32px 24px; }
  .tag-line1, .tag-line2 { font-size: 52px; }
}
</style>