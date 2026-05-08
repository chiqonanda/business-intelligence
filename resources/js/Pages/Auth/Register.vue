<template>
  <div class="login-layout min-h-screen flex items-center justify-center p-6 bg-[#020617] relative overflow-hidden">
    <Head title="Registration" />

    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
      <img src="/nike_bi_login_bg_1778173179043.png" class="w-full h-full object-cover opacity-20" alt="Background" />
      <div class="absolute inset-0 bg-gradient-to-br from-[#020617] via-transparent to-[#020617]"></div>
    </div>

    <!-- Decorative Elements -->
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-600/10 rounded-full blur-[120px] animate-pulse"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-600/10 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s"></div>

    <!-- Register Card -->
    <div class="w-full max-w-[500px] z-10 animate-slide-up">
      <div class="mb-12 text-center">
        <h1 class="text-3xl font-black text-white tracking-tighter uppercase mb-2">Create Credentials</h1>
        <p class="text-slate-400 text-sm font-medium uppercase tracking-widest">Onboarding new operative to the network.</p>
      </div>

      <div class="card-premium p-10 bg-white/[0.03] backdrop-blur-3xl border-white/5 shadow-2xl">
        <form @submit.prevent="submit">
          <div class="space-y-6">
            <!-- Name -->
            <div class="space-y-2">
              <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Full Name</label>
              <div class="relative group">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-indigo-400 transition-colors">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </div>
                <input 
                  type="text" 
                  v-model="form.name" 
                  required 
                  class="w-full bg-white/[0.03] border border-white/10 rounded-2xl pl-12 pr-4 py-4 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all placeholder:text-slate-700"
                  placeholder="Operational Name"
                />
              </div>
              <p v-if="form.errors.name" class="text-[10px] font-bold text-rose-500 mt-1 uppercase">{{ form.errors.name }}</p>
            </div>

            <!-- Email -->
            <div class="space-y-2">
              <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Corporate Email</label>
              <div class="relative group">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-indigo-400 transition-colors">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
                <input 
                  type="email" 
                  v-model="form.email" 
                  required 
                  class="w-full bg-white/[0.03] border border-white/10 rounded-2xl pl-12 pr-4 py-4 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all placeholder:text-slate-700"
                  placeholder="operative@nike.corp"
                />
              </div>
              <p v-if="form.errors.email" class="text-[10px] font-bold text-rose-500 mt-1 uppercase">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Password</label>
                <input 
                  type="password" 
                  v-model="form.password" 
                  required 
                  class="w-full bg-white/[0.03] border border-white/10 rounded-2xl px-4 py-4 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all placeholder:text-slate-700"
                  placeholder="••••••••"
                />
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Confirm</label>
                <input 
                  type="password" 
                  v-model="form.password_confirmation" 
                  required 
                  class="w-full bg-white/[0.03] border border-white/10 rounded-2xl px-4 py-4 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all placeholder:text-slate-700"
                  placeholder="••••••••"
                />
              </div>
            </div>
            <p v-if="form.errors.password" class="text-[10px] font-bold text-rose-500 mt-1 uppercase">{{ form.errors.password }}</p>

            <!-- Submit -->
            <button 
              type="submit" 
              :disabled="form.processing"
              class="w-full btn-premium bg-white text-black py-4 justify-center text-sm tracking-widest font-black shadow-xl shadow-white/5 hover:shadow-indigo-500/10 mt-4"
            >
              <span v-if="!form.processing">REGISTER OPERATIVE</span>
              <div v-else class="h-4 w-4 border-2 border-black border-t-transparent rounded-full animate-spin"></div>
            </button>
          </div>
        </form>

        <div class="mt-10 pt-8 border-t border-white/5 text-center">
          <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
            Already registered? <Link :href="route('login')" class="text-indigo-400 hover:text-indigo-300">Initiate Access</Link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<style scoped>
@keyframes slide-up {
  from { opacity: 0; transform: translateY(40px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-slide-up {
  animation: slide-up 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
