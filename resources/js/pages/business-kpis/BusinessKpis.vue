<template>
  <div class="min-h-screen bg-slate-50 pb-16">
    <!-- Header -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Business KPIs</h1>
          <p class="mt-1 text-sm text-slate-500">
            USSD Mobile Banking • {{ timeRangeLabel }}
          </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
          <div class="flex bg-slate-100 rounded-lg p-1">
            <button
              v-for="range in quickRanges"
              :key="range.value"
              @click="setQuickRange(range.value)"
              :class="[
                'px-4 py-1.5 text-sm rounded-md transition-all',
                currentRange === range.value
                  ? 'bg-white shadow-sm text-indigo-700 font-medium'
                  : 'text-slate-600 hover:text-slate-800'
              ]"
            >
              {{ range.label }}
            </button>
          </div>

          <button
            @click="showDateRangeModal = true"
            class="px-5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Custom
            <span v-if="currentRange === 'custom'" class="ml-1 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full">
              {{ customRangeLabel }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <KpiCard title="Monthly Active Transacting Users" :value="kpi.matu.toLocaleString()" trend="+18.2%" :trend-positive="true" />
        <KpiCard title="Transaction Success Rate" :value="kpi.txnSuccess + '%'" trend="-0.6%" :trend-positive="false" />
        <KpiCard title="Avg Transactions per User" :value="kpi.avgTxPerUser" trend="+1.3" :trend-positive="true" />
        <KpiCard title="Loan Approvals (30d)" :value="kpi.loanApprovals.toLocaleString()" trend="+34%" :trend-positive="true" />
      </div>

      <!-- Charts Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">
        <!-- Transaction Success by Type -->
        <ChartCard title="Transaction Success Rate by Type" class="lg:col-span-2">
          <div class="h-80"><canvas ref="txnSuccessChartRef"></canvas></div>
        </ChartCard>

        <!-- P2P Transfer Funnel -->
        <ChartCard title="P2P Transfer Conversion Funnel">
          <div class="h-80"><canvas ref="transferFunnelRef"></canvas></div>
        </ChartCard>

        <!-- Airtime & Bill Payments Volume -->
        <ChartCard title="Airtime & Bill Payment Trends">
          <div class="h-80"><canvas ref="airtimeBillChartRef"></canvas></div>
        </ChartCard>

        <!-- Top Failure Reasons -->
        <ChartCard title="Top Transaction Failure Reasons (30d)">
          <div class="h-72"><canvas ref="failureReasonsRef"></canvas></div>
        </ChartCard>

        <!-- Loan Application Funnel -->
        <ChartCard title="Loan Application to Approval Funnel" class="lg:col-span-2">
          <div class="h-80"><canvas ref="loanFunnelRef"></canvas></div>
        </ChartCard>

        <!-- Agent Cash-In vs Cash-Out -->
        <ChartCard title="Agent Cash-In vs Cash-Out Volume">
          <div class="h-72"><canvas ref="agentCashChartRef"></canvas></div>
        </ChartCard>
      </div>
    </div>

    <!-- Custom Date Range Modal -->
    <div
      v-if="showDateRangeModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="showDateRangeModal = false"
    >
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h2 class="text-xl font-semibold mb-4">Select Date Range</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
            <input v-model="customStart" type="date" class="w-full border border-slate-300 rounded-lg px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">End Date</label>
            <input v-model="customEnd" type="date" class="w-full border border-slate-300 rounded-lg px-3 py-2" />
          </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
          <button @click="showDateRangeModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg">
            Cancel
          </button>
          <button @click="applyCustomRange" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            Apply
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Chart, registerables } from 'chart.js'
import 'chartjs-adapter-date-fns'

Chart.register(...registerables)

// ── State ────────────────────────────────────────────────────────────────
const currentRange = ref('30d')
const showDateRangeModal = ref(false)
const customStart = ref('')
const customEnd = ref('')

const chartInstances = ref({
  txnSuccess: null,
  transferFunnel: null,
  airtimeBill: null,
  failureReasons: null,
  loanFunnel: null,
  agentCash: null
})

const txnSuccessChartRef = ref(null)
const transferFunnelRef = ref(null)
const airtimeBillChartRef = ref(null)
const failureReasonsRef = ref(null)
const loanFunnelRef = ref(null)
const agentCashChartRef = ref(null)

const kpi = ref({
  matu: 124700,
  txnSuccess: 96.4,
  avgTxPerUser: 9.2,
  loanApprovals: 2840
})

const quickRanges = [
  { value: 'today', label: 'Today' },
  { value: '7d',    label: '7 days' },
  { value: '30d',   label: '30 days' },
  { value: '90d',   label: '90 days' },
  { value: 'ytd',   label: 'YTD' }
]

// ── Computed ─────────────────────────────────────────────────────────────
const timeRangeLabel = computed(() => {
  if (currentRange.value === 'custom') return customRangeLabel.value
  return quickRanges.find(r => r.value === currentRange.value)?.label || 'Custom'
})

const customRangeLabel = computed(() => {
  if (!customStart.value || !customEnd.value) return ''
  const s = new Date(customStart.value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
  const e = new Date(customEnd.value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
  return `${s} – ${e}`
})

// ── Chart Creation ───────────────────────────────────────────────────────
const destroyCharts = () => {
  Object.values(chartInstances.value).forEach(chart => chart?.destroy())
  Object.keys(chartInstances.value).forEach(key => chartInstances.value[key] = null)
}

const createCharts = () => {
  destroyCharts()

  // 1. Transaction Success Rate by Type (Grouped Bar + Line)
  if (txnSuccessChartRef.value) {
    chartInstances.value.txnSuccess = new Chart(txnSuccessChartRef.value, {
      type: 'bar',
      data: {
        labels: ['Airtime Top-up', 'P2P Transfer', 'Bill Payment', 'Cash Out', 'Loan Repayment'],
        datasets: [
          {
            type: 'bar',
            label: 'Successful Transactions',
            data: [42800, 26500, 9800, 7200, 4100],
            backgroundColor: 'rgba(99, 102, 241, 0.65)',
            borderColor: 'rgb(99, 102, 241)',
            borderWidth: 1,
            borderRadius: 6,
            yAxisID: 'y'
          },
          {
            type: 'line',
            label: 'Success Rate (%)',
            data: [98.7, 94.2, 91.8, 95.1, 97.3],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.2)',
            borderWidth: 2.5,
            tension: 0.3,
            fill: true,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#10b981',
            pointBorderWidth: 2,
            pointRadius: 4,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { position: 'top', align: 'start' },
          tooltip: { backgroundColor: 'rgba(30, 41, 59, 0.94)', titleColor: '#f1f5f9', bodyColor: '#cbd5e1' }
        },
        scales: {
          y: { beginAtZero: true, title: { display: true, text: 'Transactions' } },
          y1: { position: 'right', min: 80, max: 100, title: { display: true, text: 'Success Rate' }, grid: { drawOnChartArea: false } }
        }
      }
    })
  }

  // 2. P2P Transfer Funnel (Horizontal Bar)
  if (transferFunnelRef.value) {
    chartInstances.value.transferFunnel = new Chart(transferFunnelRef.value, {
      type: 'bar',
      data: {
        labels: ['Reach Transfer Menu', 'Enter Recipient', 'Enter Amount', 'Confirm PIN', 'Success'],
        datasets: [{
          label: 'Users',
          data: [42000, 38000, 34000, 29000, 26000],
          backgroundColor: 'rgba(59, 130, 246, 0.7)',
          borderColor: 'rgb(59, 130, 246)',
          borderWidth: 1,
          borderRadius: 6
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const prev = ctx.dataIndex > 0 ? ctx.chart.data.datasets[0].data[ctx.dataIndex - 1] : ctx.raw
                const pct = ctx.dataIndex > 0 ? ((ctx.raw / prev) * 100).toFixed(1) : 100
                return `${ctx.raw.toLocaleString()} users (${pct}%)`
              }
            }
          }
        },
        scales: { x: { beginAtZero: true, title: { display: true, text: 'Users' } } }
      }
    })
  }

  // 3. Airtime & Bill Payments (Stacked Bar or Dual Axis)
  if (airtimeBillChartRef.value) {
    chartInstances.value.airtimeBill = new Chart(airtimeBillChartRef.value, {
      type: 'bar',
      data: {
        labels: ['Jan 5','Jan 6','Jan 7','Jan 8','Jan 9','Jan 10','Jan 11'],
        datasets: [
          { label: 'Airtime Top-up (K)', data: [42, 38, 51, 47, 55, 62, 58], backgroundColor: 'rgba(249, 115, 22, 0.7)' },
          { label: 'Bill Payments (K)', data: [18, 15, 22, 19, 24, 28, 26], backgroundColor: 'rgba(139, 92, 246, 0.7)' }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: { x: { stacked: false }, y: { beginAtZero: true, stacked: false } }
      }
    })
  }

  // 4. Top Failure Reasons (Horizontal Bar)
  if (failureReasonsRef.value) {
    chartInstances.value.failureReasons = new Chart(failureReasonsRef.value, {
      type: 'bar',
      data: {
        labels: ['Invalid PIN', 'Timeout', 'Insufficient Funds', 'Network Error', 'Wrong Recipient', 'Other'],
        datasets: [{
          label: 'Failed Transactions',
          data: [3200, 1800, 1400, 900, 600, 400],
          backgroundColor: 'rgba(239, 68, 68, 0.7)',
          borderColor: 'rgb(239, 68, 68)',
          borderWidth: 1,
          borderRadius: 6
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, title: { display: true, text: 'Count' } } }
      }
    })
  }

  // 5. Loan Funnel (Horizontal Bar)
  if (loanFunnelRef.value) {
    chartInstances.value.loanFunnel = new Chart(loanFunnelRef.value, {
      type: 'bar',
      data: {
        labels: ['View Loan Menu', 'Check Eligibility', 'Start Application', 'Submit Documents', 'Approved'],
        datasets: [{
          label: 'Users',
          data: [18500, 14200, 9800, 5200, 2840],
          backgroundColor: 'rgba(34, 197, 94, 0.7)',
          borderColor: 'rgb(34, 197, 94)',
          borderWidth: 1,
          borderRadius: 6
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const prev = ctx.dataIndex > 0 ? ctx.chart.data.datasets[0].data[ctx.dataIndex - 1] : ctx.raw
                const pct = ctx.dataIndex > 0 ? ((ctx.raw / prev) * 100).toFixed(1) : 100
                return `${ctx.raw.toLocaleString()} users (${pct}%)`
              }
            }
          }
        },
        scales: { x: { beginAtZero: true, title: { display: true, text: 'Users' } } }
      }
    })
  }

  // 6. Agent Cash-In vs Cash-Out (Grouped Bar)
  if (agentCashChartRef.value) {
    chartInstances.value.agentCash = new Chart(agentCashChartRef.value, {
      type: 'bar',
      data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [
          { label: 'Cash-In', data: [420, 380, 510, 470, 620, 580, 310], backgroundColor: 'rgba(16, 185, 129, 0.7)' },
          { label: 'Cash-Out', data: [680, 720, 890, 840, 950, 820, 490], backgroundColor: 'rgba(239, 68, 68, 0.7)' }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: { x: { stacked: false }, y: { beginAtZero: true, title: { display: true, text: 'Transactions' } } }
      }
    })
  }
}

// ── Methods ──────────────────────────────────────────────────────────────
const setQuickRange = (value) => {
  currentRange.value = value
  createCharts()
}

const applyCustomRange = () => {
  if (customStart.value && customEnd.value) {
    currentRange.value = 'custom'
    showDateRangeModal.value = false
    createCharts()
  }
}

// ── Lifecycle ────────────────────────────────────────────────────────────
onMounted(() => {
  createCharts()
})

onUnmounted(() => {
  destroyCharts()
})

// ── Local Components ─────────────────────────────────────────────────────
const KpiCard = {
  props: {
    title: String,
    value: [String, Number],
    trend: String,
    trendPositive: Boolean
  },
  template: `
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden">
      <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-indigo-50/70 to-transparent rounded-bl-full -mr-8 -mt-8"></div>
      <p class="text-sm text-slate-500 font-medium mb-1">{{ title }}</p>
      <p class="text-3xl font-bold text-slate-900">{{ value }}</p>
      <div class="mt-4 flex items-center gap-2">
        <span :class="trendPositive ? 'text-emerald-600' : 'text-rose-600'" class="text-sm font-medium">
          {{ trend }}
        </span>
        <span class="text-xs text-slate-500">vs previous period</span>
      </div>
    </div>
  `
}

const ChartCard = {
  props: { title: String },
  template: `
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-lg font-semibold text-slate-900">{{ title }}</h3>
      </div>
      <div class="p-5">
        <slot />
      </div>
    </div>
  `
}
</script>
