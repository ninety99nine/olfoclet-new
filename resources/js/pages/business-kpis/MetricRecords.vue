<template>
  <div class="min-h-screen bg-slate-50 pb-12">

    <!-- Header -->
    <div class="bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-6 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <Button
              size="xs"
              type="basic"
              leftIconSize="24"
              :leftIcon="ArrowLeft"
              buttonClass="rounded-lg"
              :action="goToBusinessKpis"
            />
            <div>
              <h1 class="text-2xl font-semibold text-slate-900">
                {{ metric ? metric.name : 'Metric records' }}
              </h1>
              <nav class="flex mt-2" aria-label="bydcrumb">
                <ol class="flex items-center space-x-2 text-sm text-slate-500">
                  <li>
                    <router-link
                      :to="{ name: 'show-app-business-kpis', params: { app_id: app?.id } }"
                      class="hover:text-slate-900 hover:underline"
                    >
                      Business KPIs
                    </router-link>
                  </li>
                  <li>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </li>
                  <li class="font-medium text-slate-700">
                    {{ metric ? metric.name : 'Records' }}
                  </li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-6 py-8">

      <!-- Loading -->
      <div
        v-if="loading && !pagination"
        class="flex justify-center items-center py-24"
      >
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Fetch error -->
      <div
        v-else-if="fetchError"
        class="bg-rose-50 border border-rose-200 rounded-2xl p-6 text-center"
      >
        <p class="text-rose-700">{{ fetchError }}</p>
        <Button
          size="md"
          type="light"
          mode="outline"
          buttonClass="mt-4 rounded-lg"
          :action="() => fetchRecords()"
        >
          Retry
        </Button>
      </div>

      <!-- Metric not found -->
      <div
        v-else-if="metric === null && metricId"
        class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12 text-center"
      >
        <p class="text-slate-600 mb-4">This metric could not be found. It may have been deleted.</p>
        <div class="flex justify-center">
          <Button
            size="md"
            type="primary"
            buttonClass="rounded-lg"
            :action="goToBusinessKpis"
          >
            Back to Business KPIs
          </Button>
        </div>
      </div>

      <!-- Table section (when metric is loaded) -->
      <div v-else-if="metric" class="space-y-4">
        <p class="text-sm text-slate-600">
          Underlying session records that contributed to this metric. Click a row to open session details.
        </p>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <!-- Table header with count & pagination (like Sessions.vue) -->
          <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-sm">
            <div class="text-slate-600">
              Showing
              <strong>{{ pagination?.meta?.from ?? 0 }}–{{ pagination?.meta?.to ?? 0 }}</strong>
              of
              <strong>{{ (pagination?.meta?.total ?? 0).toLocaleString() }}</strong>
            </div>
            <div v-if="pagination?.meta?.links?.length" class="flex items-center gap-2">
              <button
                v-for="(link, index) in pagination.meta.links"
                :key="index"
                :disabled="link.page == null"
                @click="changePage(link)"
                :class="[
                  'px-3 py-1.5 rounded-lg border transition-colors',
                  { 'border-slate-300 text-slate-600 hover:bg-slate-100 cursor-pointer': link.page != null && !link.active },
                  { 'border-indigo-600 bg-indigo-600 text-white font-medium hover:bg-indigo-700 cursor-pointer': link.page != null && link.active },
                  { 'border-slate-300 text-slate-400 disabled:opacity-50 disabled:cursor-not-allowed': link.page == null }
                ]"
              >
                <template v-if="index === 0">Previous</template>
                <template v-else-if="index === pagination.meta.links.length - 1">Next</template>
                <template v-else>{{ link.label }}</template>
              </button>
            </div>
          </div>

          <!-- Empty state -->
          <div v-if="displayRecords.length === 0 && !loading" class="p-12 text-center text-slate-500 flex flex-col items-center">
            <TableIcon class="w-12 h-12 mb-4 text-slate-300" />
            <p class="text-lg font-medium text-slate-700">No records yet</p>
            <p class="mt-2 text-sm">
              Record data will appear here once this metric is linked to your build and sessions are captured.
            </p>
            <div class="flex justify-center mt-4">
              <Button
                size="md"
                type="light"
                mode="outline"
                buttonClass="rounded-lg"
                :action="goToBusinessKpis"
              >
                Back to Business KPIs
              </Button>
            </div>
          </div>

          <!-- Table -->
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50/70">
                <tr>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    Country / Network
                  </th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    Phone
                  </th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    Duration
                  </th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    When
                  </th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    Steps
                  </th>
                  <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    Device
                  </th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    {{ metric.name }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white">
                <tr
                  v-for="(row, index) in displayRecords"
                  :key="row.id || index"
                  class="hover:bg-slate-50 transition-colors"
                  @click="getSessionId(row) ? navigateToSession(getSessionId(row)) : null"
                  :class="{ 'cursor-pointer': getSessionId(row) }"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                      <img
                        v-if="getRowCountry(row)"
                        :src="`/svgs/country-flags/${getRowCountry(row).toLowerCase()}.svg`"
                        class="w-6 h-6 rounded-full object-cover border border-slate-200 shadow-sm"
                        onerror="this.style.display='none'"
                      />
                      <span class="text-sm font-medium text-slate-900">{{ getRowNetwork(row) || '—' }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                    {{ row.phone || row.msisdn || row.ussd_session?.msisdn || '—' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                    {{ formatDuration(row.total_duration_ms ?? row.duration_ms ?? row.ussd_session?.total_duration_ms) || '—' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                    {{ row.created_at ? formattedRelativeDate(row.created_at) : (row.ussd_session?.created_at ? formattedRelativeDate(row.ussd_session.created_at) : '—') }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span
                      :class="row.successful ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                      class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full"
                    >
                      {{ row.successful ? 'Success' : (row.status ?? 'Failed') }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-slate-700">
                    {{ row.total_steps ?? row.steps ?? row.ussd_session?.total_steps ?? '—' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span
                      :class="row.simulated ? 'bg-amber-50 text-amber-700' : 'bg-indigo-50 text-indigo-700'"
                      class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full"
                    >
                      {{ row.simulated ? 'Simulator' : (row.ussd_session?.simulated ? 'Simulator' : 'Mobile') }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                    {{ formatMetricValue(row.metric_value) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Bottom pagination (like Sessions.vue) -->
          <div
            v-if="!loading && pagination?.meta?.total >= 5"
            class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-sm"
          >
            <div class="text-slate-600">
              Showing
              <strong>{{ pagination?.meta?.from ?? 0 }}–{{ pagination?.meta?.to ?? 0 }}</strong>
              of
              <strong>{{ (pagination?.meta?.total ?? 0).toLocaleString() }}</strong>
            </div>
            <div class="flex items-center gap-2">
              <button
                v-for="(link, index) in pagination?.meta?.links"
                :key="index"
                :disabled="link.page == null"
                @click="changePage(link)"
                :class="[
                  'px-3 py-1.5 rounded-lg border transition-colors',
                  { 'border-slate-300 text-slate-600 hover:bg-slate-100 cursor-pointer': link.page != null && !link.active },
                  { 'border-indigo-600 bg-indigo-600 text-white font-medium hover:bg-indigo-700 cursor-pointer': link.page != null && link.active },
                  { 'border-slate-300 text-slate-400 disabled:opacity-50 disabled:cursor-not-allowed': link.page == null }
                ]"
              >
                <template v-if="index === 0">Previous</template>
                <template v-else-if="index === pagination.meta.links.length - 1">Next</template>
                <template v-else>{{ link.label }}</template>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Button from '@Partials/Button.vue';
import { ArrowLeft, Table as TableIcon } from 'lucide-vue-next';
import { formatDuration } from '@Utils/stringUtils';
import { formattedRelativeDate } from '@Utils/dateUtils';
import currencies from '@Json/currencies.json';

const PER_PAGE = 15;

export default {
  name: 'MetricRecords',
  components: { Button, TableIcon },
  inject: ['appState'],
  data() {
    return {
      ArrowLeft,
      metric: null,
      records: [],
      pagination: null,
      loading: false,
      fetchError: null,
    };
  },
  computed: {
    app() {
      return this.appState?.app ?? null;
    },
    displayRecords() {
      return this.records;
    },
    metricId() {
      const id = this.$route?.params?.metric_id;
      if (id == null) return null;
      return String(id);
    },
  },
  watch: {
    metricId: { handler: 'onMetricOrAppChange', immediate: false },
    'app.id': { handler: 'onMetricOrAppChange', immediate: false },
  },
  mounted() {
    this.fetchMetric();
    this.fetchRecords();
  },
  methods: {
    formatDuration,
    formattedRelativeDate,
    onMetricOrAppChange() {
      this.metric = null;
      this.records = [];
      this.pagination = null;
      this.fetchMetric();
      this.fetchRecords();
    },
    fetchMetric() {
      if (!this.metricId || !this.app?.id) return;
      axios
        .get(`/api/apps/${this.app.id}/business-kpis/${this.metricId}`)
        .then((response) => {
          this.metric = response?.data?.data ?? response?.data ?? null;
        })
        .catch(() => {
          this.metric = null;
        });
    },
    getRecordsParams(page = null) {
      const params = {
        per_page: PER_PAGE,
        _relationships: ['ussdSession'].join(','),
      };
      if (page != null) params.page = page;
      return { params };
    },
    fetchRecords(page = null) {
      if (!this.metricId || !this.app?.id) {
        this.records = [];
        this.pagination = null;
        return;
      }
      this.loading = true;
      this.fetchError = null;
      axios
        .get(`/api/apps/${this.app.id}/business-kpis/${this.metricId}/records`, this.getRecordsParams(page))
        .then((response) => {
          const raw = response?.data;
          if (raw && typeof raw === 'object' && Array.isArray(raw.data)) {
            this.records = raw.data;
            this.pagination = raw;
          } else {
            this.records = Array.isArray(raw) ? raw : [];
            this.pagination = null;
          }
        })
        .catch((err) => {
          this.fetchError = err?.response?.data?.message ?? err?.message ?? 'Failed to load records';
          this.records = [];
          this.pagination = null;
        })
        .finally(() => {
          this.loading = false;
        });
    },
    changePage(link) {
      if (link?.page != null) this.fetchRecords(link.page);
    },
    formatMetricValue(value) {
      if (value == null || value === '') return '—';
      if (!this.metric) return String(value);
      const type = this.metric.metricType || this.metric.metric_type;
      if (type === 'money') {
        const currencyCode = this.metric.currency || 'USD';
        const num = Number(value);
        if (Number.isNaN(num)) return String(value);
        const decimals = currencies[currencyCode]?.decimal_digits ?? 2;
        const formatted = num.toLocaleString('en-US', {
          minimumFractionDigits: Math.min(decimals, 2),
          maximumFractionDigits: 2,
        });
        const symbol = currencies[currencyCode]?.symbol_native ?? currencies[currencyCode]?.symbol ?? currencyCode;
        const symbolAfter = ['EUR', 'CAD', 'AUD', 'BRL', 'CZK', 'DKK', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'THB', 'TRY'];
        if (symbolAfter.includes(currencyCode)) return `${formatted} ${symbol}`;
        return `${symbol}${formatted}`;
      }
      if (type === 'percentage') {
        const num = Number(value);
        return Number.isNaN(num) ? String(value) : `${num}%`;
      }
      return String(value);
    },
    getSessionId(row) {
      return row.session_id ?? row.ussd_session_id ?? row.ussd_session?.id ?? null;
    },
    getRowCountry(row) {
      return row.country ?? row.ussd_session?.country ?? null;
    },
    getRowNetwork(row) {
      return row.network ?? row.ussd_session?.network ?? null;
    },
    goToBusinessKpis() {
      this.$router.push({
        name: 'show-app-business-kpis',
        params: { app_id: this.app?.id },
      });
    },
    navigateToSession(sessionId) {
      this.$router.push({
        name: 'show-app-session',
        params: { app_id: this.app?.id, session_id: sessionId },
      });
    },
  },
};
</script>
