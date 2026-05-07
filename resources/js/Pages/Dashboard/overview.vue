<template>
  <AppLayout>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-slate-50 py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 rounded-3xl bg-white p-6 shadow-sm">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Business Intelligence</p>
              <h1 class="mt-3 text-3xl font-semibold text-slate-900">Dashboard Penjualan</h1>
              <p class="mt-2 max-w-2xl text-sm text-slate-600">
                Ringkasan angka utama dan transaksi terbaru yang sudah tersimpan di database.
              </p>
            </div>
          </div>

          <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-3xl bg-slate-950 p-6 shadow-sm text-white">
              <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Total Revenue</p>
              <p class="mt-4 text-3xl font-semibold">{{ formatCurrency(stats.total_revenue) }}</p>
            </div>
            <div class="rounded-3xl bg-slate-950 p-6 shadow-sm text-white">
              <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Total Profit</p>
              <p class="mt-4 text-3xl font-semibold">{{ formatCurrency(stats.total_profit) }}</p>
            </div>
            <div class="rounded-3xl bg-slate-950 p-6 shadow-sm text-white">
              <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Total Orders</p>
              <p class="mt-4 text-3xl font-semibold">{{ stats.total_orders }}</p>
            </div>
            <div class="rounded-3xl bg-white p-6 shadow-sm">
              <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Avg Order Value</p>
              <p class="mt-4 text-3xl font-semibold">{{ formatCurrency(stats.avg_order_value) }}</p>
            </div>
            <div class="rounded-3xl bg-white p-6 shadow-sm">
              <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Profit Margin</p>
              <p class="mt-4 text-3xl font-semibold">{{ stats.profit_margin }}%</p>
            </div>
            <div class="rounded-3xl bg-white p-6 shadow-sm">
              <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Top Channel</p>
              <p class="mt-4 text-3xl font-semibold">{{ stats.top_channel }}</p>
            </div>
          </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
          <div class="lg:col-span-2 rounded-3xl bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
              <div>
                <h2 class="text-xl font-semibold text-slate-900">Transaksi Terbaru</h2>
                <p class="text-sm text-slate-500">10 order terakhir dari database.</p>
              </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200">
              <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Order</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Produk</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Region</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Channel</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Revenue</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Profit</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                  <tr v-for="row in latestTransactions" :key="row.order_id">
                    <td class="px-4 py-4 text-sm text-slate-700">{{ row.order_id }}</td>
                    <td class="px-4 py-4 text-sm text-slate-700">{{ row.order_date }}</td>
                    <td class="px-4 py-4 text-sm text-slate-700">
                      <div class="font-semibold">{{ row.product_name }}</div>
                      <div class="text-sm text-slate-500">{{ row.product_line }}</div>
                    </td>
                    <td class="px-4 py-4 text-sm text-slate-700">{{ row.region }}</td>
                    <td class="px-4 py-4 text-sm text-slate-700">{{ row.sales_channel }}</td>
                    <td class="px-4 py-4 text-right text-sm font-medium text-slate-900">{{ formatCurrency(row.revenue) }}</td>
                    <td class="px-4 py-4 text-right text-sm font-medium" :class="row.profit < 0 ? 'text-rose-600' : 'text-emerald-600'">{{ formatCurrency(row.profit) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="rounded-3xl bg-white p-6 shadow-sm">
            <div class="mb-4">
              <h2 class="text-xl font-semibold text-slate-900">Top Produk</h2>
              <p class="text-sm text-slate-500">Berdasarkan revenue tertinggi.</p>
            </div>
            <div class="space-y-4">
              <div v-for="product in topProducts" :key="product.product_name" class="rounded-3xl border border-slate-200 p-4">
                <p class="text-sm font-semibold text-slate-900">{{ product.product_name }}</p>
                <p class="text-sm text-slate-500">{{ product.product_line }}</p>
                <div class="mt-3 flex items-center justify-between text-sm text-slate-700">
                  <span>{{ product.units }} units</span>
                  <span class="font-semibold">{{ formatCurrency(product.revenue) }}</span>
                </div>
              </div>
              <div v-if="topProducts.length === 0" class="rounded-3xl border border-slate-200 p-4 text-sm text-slate-500">
                Tidak ada data produk tersedia.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  stats: Object,
  latestTransactions: Array,
  topProducts: Array,
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 2,
  }).format(Number(value ?? 0));
};
</script>
