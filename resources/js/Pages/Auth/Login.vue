<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <div
        class="min-h-screen bg-black nike-grid flex flex-col justify-center items-center p-6 selection:bg-[#d9ff00]/30 selection:text-[#d9ff00]">

        <Head title="Access Terminal" />

        <!-- Brand Mark -->
        <div class="mb-12 text-center animate-slide-up">
            <h1 class="text-6xl font-black italic tracking-tighter text-white leading-none mb-2">NIKE</h1>
            <p class="text-[10px] font-black text-zinc-600 uppercase tracking-[0.6em]">Intelligence Network</p>
        </div>

        <div class="w-full max-w-[420px] bg-[#0a0a0a] border border-white/5 p-10 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.7)] animate-slide-up"
            style="animation-delay: 0.1s">
            <div class="mb-10">
                <h2 class="text-2xl font-black italic uppercase tracking-tighter text-white">Access Terminal</h2>
                <p class="text-[10px] font-black text-zinc-500 uppercase tracking-[0.2em] mt-2">Identity Verification
                    Required</p>
            </div>

            <div v-if="status"
                class="mb-6 text-[10px] font-bold text-[#d9ff00] uppercase tracking-widest bg-[#d9ff00]/5 p-3 border border-[#d9ff00]/20">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <div>
                    <label class="block text-[10px] font-black text-zinc-500 uppercase tracking-[0.3em] mb-3">Neural
                        Identifier (Email)</label>
                    <input v-model="form.email" type="email" required
                        class="w-full bg-black border border-white/5 px-4 py-4 text-sm font-bold text-white focus:border-[#d9ff00] focus:ring-0 outline-none transition-all placeholder:text-zinc-800"
                        placeholder="NAME@NIKE.TEST" />
                    <p v-if="form.errors.email"
                        class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-widest">{{ form.errors.email
                        }}</p>
                </div>

                <div>
                    <div class="flex justify-between mb-3">
                        <label class="block text-[10px] font-black text-zinc-500 uppercase tracking-[0.3em]">Access
                            Protocol (Password)</label>
                        <Link v-if="canResetPassword" :href="route('password.request')"
                            class="text-[9px] font-black text-zinc-600 hover:text-white uppercase tracking-widest">Lost
                            Protocol?</Link>
                    </div>
                    <input v-model="form.password" type="password" required
                        class="w-full bg-black border border-white/5 px-4 py-4 text-sm font-bold text-white focus:border-[#d9ff00] focus:ring-0 outline-none transition-all placeholder:text-zinc-800"
                        placeholder="••••••••" />
                    <p v-if="form.errors.password"
                        class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-widest">{{
                        form.errors.password }}</p>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer group">
                        <input type="checkbox" v-model="form.remember" class="hidden" />
                        <div
                            class="h-4 w-4 border border-white/10 bg-black flex items-center justify-center transition-all group-hover:border-[#d9ff00]">
                            <div v-if="form.remember" class="h-2 w-2 bg-[#d9ff00]"></div>
                        </div>
                        <span
                            class="ml-3 text-[10px] font-black text-zinc-500 uppercase tracking-widest group-hover:text-white transition-colors">Maintain
                            Persistence</span>
                    </label>
                </div>

                <button type="submit" :disabled="form.processing"
                    class="w-full py-5 bg-[#d9ff00] text-black text-xs font-black uppercase tracking-[0.3em] hover:bg-white transition-all active:scale-[0.98] disabled:opacity-50">
                    {{ form.processing ? 'INITIALIZING...' : 'INITIALIZE LOGIN' }}
                </button>
            </form>
        </div>

        <div class="mt-12 flex gap-8 text-[9px] font-black text-zinc-700 uppercase tracking-[0.4em] animate-slide-up"
            style="animation-delay: 0.3s">
            <span>Security: AES-256</span>
            <span>Node: OR-PDX-01</span>
            <span>Protocol: HTTPS</span>
        </div>
    </div>
</template>