<!-- resources/js/pages/analytics/Analytics.vue -->
<template>
  <div class="min-h-screen bg-slate-50 pb-16">
    <!-- Header -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Analytics & Insights</h1>
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
      <!-- KPI Cards - Row 1 -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <KpiCard title="Total Sessions" :value="kpi.totalSessions" trend="+12.4%" :trend-positive="true" />
        <KpiCard title="Success Rate" :value="kpi.successRate + '%'" trend="-1.8%" :trend-positive="false" />
        <KpiCard title="Avg Duration" :value="kpi.avgDuration" trend="+4s" :trend-positive="false" />
        <KpiCard title="Active Users (30d)" :value="kpi.activeUsers" trend="+19%" :trend-positive="true" />
      </div>

      <!-- KPI Cards - Row 2 -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <KpiCard title="New Users (30d)" :value="kpi.newUsers" trend="+28%" :trend-positive="true" />
        <KpiCard title="Retention Rate" :value="kpi.retentionRate + '%'" trend="-2.1%" :trend-positive="false" />
        <KpiCard title="Open Flags" :value="kpi.openFlags" trend="+5" :trend-positive="false" />
        <KpiCard title="Avg Flags/Session" :value="kpi.avgFlagsPerSession" trend="0.04" :trend-positive="false" />
      </div>

      <!-- Main Charts Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">

        <!-- 1. Sessions & Success Rate -->
        <ChartCard title="Sessions & Success Rate" class="lg:col-span-2">
          <div class="h-80"><canvas ref="sessionsChartRef"></canvas></div>
        </ChartCard>

        <!-- 6. New Users Trend -->
        <ChartCard title="New Users Trend">
          <div class="h-72"><canvas ref="newUsersChartRef"></canvas></div>
        </ChartCard>

        <!-- 6. Return Users Trend -->
        <ChartCard title="Return Users Trend">
          <div class="h-72"><canvas ref="returnUsersChartRef"></canvas></div>
        </ChartCard>

        <!-- 4. Sessions by Network & Country -->
       <div class="col-span-2 grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- 2. Sessions by Network -->
            <ChartCard title="Sessions by Network">
            <div class="h-72"><canvas ref="networkChartRef"></canvas></div>
            </ChartCard>

            <!-- 3. Sessions by Country -->
            <ChartCard title="Sessions by Country">
            <div class="h-72"><canvas ref="countryChartRef"></canvas></div>
            </ChartCard>

            <ChartCard title="Sessions by Network & Country">
            <div class="h-72"><canvas ref="networkCountryChartRef"></canvas></div>
            </ChartCard>

        </div>

        <!-- 7. Drop-off Screens -->
        <ChartCard title="Drop-off Screens" class="lg:col-span-2">
          <div class="h-80"><canvas ref="funnelChartRef"></canvas></div>
        </ChartCard>

<!-- Peak Hours / Day-of-Week Heatmap -->
<ChartCard title="Peak Hours / Day-of-Week Heatmap" class="lg:col-span-2">
  <div class="p-6 flex flex-col">
    <!-- Header - Days -->
    <div class="grid grid-cols-[80px_repeat(7,1fr)] border-b border-slate-200 pb-2">
      <div></div> <!-- empty corner -->
      <div
        v-for="day in days"
        :key="day"
        class="text-center font-medium text-slate-700 text-sm"
      >
        {{ day }}
      </div>
    </div>

    <!-- Heatmap body -->
    <div class="flex-1 grid grid-cols-[80px_repeat(7,1fr)] gap-px bg-slate-100 overflow-y-auto rounded-sm">
      <template v-for="(hourRow, hourIndex) in heatmapData" :key="hourIndex">
        <!-- Hour label -->
        <div
          class="flex items-center justify-end py-0.5 pr-3 text-xs text-slate-600 font-medium bg-white"
        >
          {{ formatHour(hourIndex) }}
        </div>

        <!-- Cells for each day -->
        <div
          v-for="(value, dayIndex) in hourRow"
          :key="dayIndex"
          class="relative group transition-all duration-150 hover:z-10 hover:scale-[1.08] hover:shadow-md"
          :style="getCellStyle(value)"
          :title="`${days[dayIndex]} ${formatHour(hourIndex)} → ${value.toLocaleString()} sessions`"
        >
          <!-- Show value on hover (optional) -->
          <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-90 transition-opacity text-white text-[10px] font-bold drop-shadow-md pointer-events-none">
            {{ value }}
          </div>
        </div>
      </template>
    </div>
  </div>
</ChartCard>

        <!-- Top Failure Reasons -->
        <ChartCard title="Top Failure Reasons (Last 30 days)" class="lg:col-span-2">
          <div class="h-80"><canvas ref="failureReasonsChartRef"></canvas></div>
        </ChartCard>

      <!-- Quality & Flags Section -->
       <div class="col-span-2">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <ChartCard title="Open vs Resolved Flags">
            <div class="h-72"><canvas ref="flagsStatusChartRef"></canvas></div>
            </ChartCard>

            <ChartCard title="Open Flags by Priority">
            <div class="h-72"><canvas ref="flagsPriorityChartRef"></canvas></div>
            </ChartCard>

            <ChartCard title="Open Flags by Category">
            <div class="h-72"><canvas ref="flagsCategoryChartRef"></canvas></div>
            </ChartCard>
    <!-- 5. Mobile vs Simulator Usage -->
    <ChartCard title="Mobile vs Simulator Usage">
        <div class="h-72"><canvas ref="deviceChartRef"></canvas></div>
    </ChartCard>
        </div>
       </div>

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

import { MatrixController, MatrixElement } from 'chartjs-chart-matrix';

Chart.register(MatrixController, MatrixElement);

// ── State ────────────────────────────────────────────────────────────────
const currentRange = ref('30d')
const showDateRangeModal = ref(false)
const customStart = ref('')
const customEnd = ref('')

const chartInstances = ref({
  sessions: null,
  network: null,
  country: null,
  networkCountry: null,
  device: null,
  newUsers: null,
  returnUsers: null,
  funnel: null,
  failureReasons: null,
  flagsStatus: null,
  flagsPriority: null,
  flagsCategory: null
})
// Add new ref
const sessionsChartRef = ref(null)
const networkChartRef = ref(null)
const countryChartRef = ref(null)
const networkCountryChartRef = ref(null)
const deviceChartRef = ref(null)
const newUsersChartRef = ref(null)
const returnUsersChartRef = ref(null)
const funnelChartRef = ref(null)
const failureReasonsChartRef = ref(null)
const flagsStatusChartRef = ref(null)
const flagsPriorityChartRef = ref(null)
const flagsCategoryChartRef = ref(null)

const kpi = ref({
  totalSessions: '48,291',
  successRate: 93.8,
  avgDuration: '1m 47s',
  activeUsers: '12,840',
  newUsers: '3,214',
  retentionRate: 67,
  openFlags: 47,
  avgFlagsPerSession: '0.04'
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

// Days of week
const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

// Complete 24-hour data (hour 0 = 00:00 at the top)
const heatmapData = ref([
  [120, 140, 150, 155, 160, 180, 200],   // 00:00
  [ 90, 100, 110, 115, 120, 140, 160],   // 01:00
  [ 60,  70,  75,  80,  85, 100, 120],   // 02:00
  [ 45,  50,  55,  60,  65,  80,  90],   // 03:00
  [ 38,  42,  45,  48,  50,  65,  75],   // 04:00
  [ 55,  65,  70,  75,  80, 110, 130],   // 05:00
  [120, 180, 190, 200, 210, 280, 340],   // 06:00
  [340, 520, 560, 580, 600, 720, 820],   // 07:00
  [680, 950,1020,1080,1120,1280,1420],   // 08:00
  [920,1320,1400,1480,1540,1780,1980],   // 09:00
  [1050,1580,1680,1780,1840,2120,2320],  // 10:00
  [1120,1720,1820,1920,1980,2280,2480],  // 11:00
  [ 980,1540,1620,1700,1760,2020,2180],  // 12:00
  [ 840,1280,1340,1400,1440,1680,1820],  // 13:00
  [ 760,1020,1080,1120,1160,1380,1520],  // 14:00
  [ 890,1140,1220,1280,1340,1580,1780],  // 15:00
  [1240,1680,1780,1880,1980,2320,2580],  // 16:00
  [1580,2140,2280,2420,2520,2980,3280],  // 17:00 ← peak!
  [1420,1980,2080,2180,2280,2680,2920],  // 18:00
  [ 980,1360,1420,1480,1540,1820,1980],  // 19:00
  [ 640, 820, 860, 900, 940,1120,1240],  // 20:00
  [ 420, 540, 560, 580, 600, 720, 820],  // 21:00
  [ 280, 340, 360, 380, 400, 480, 540],  // 22:00
  [ 180, 210, 220, 230, 240, 300, 340]   // 23:00
])

const maxValue = computed(() => Math.max(...heatmapData.value.flat()))

// Format hour labels (show every 4 hours + midnight & last hour)
const formatHour = (index) => {
    return `${index.toString().padStart(2, '0')}:00`
}

// Color calculation (indigo-based, stronger contrast)
const getCellStyle = (value) => {
  if (value === 0) {
    return { backgroundColor: '#f1f5f9' }
  }

  const intensity = Math.pow(value / maxValue.value, 0.75) // slightly softened curve
  const alpha = 0.12 + intensity * 0.88

  return {
    backgroundColor: `rgba(10, 0, 180, ${alpha})`, // indigo-600
    cursor: 'pointer'
  }
}


// ── Chart Management ─────────────────────────────────────────────────────
const destroyCharts = () => {
  Object.values(chartInstances.value).forEach(chart => chart?.destroy())
  Object.keys(chartInstances.value).forEach(key => chartInstances.value[key] = null)
}

const createCharts = () => {
  destroyCharts()

if (sessionsChartRef.value) {
  chartInstances.value.sessions = new Chart(sessionsChartRef.value, {
    type: 'bar',
    data: {
      labels: ['Jan 5','Jan 6','Jan 7','Jan 8','Jan 9','Jan 10','Jan 11','Jan 12','Jan 13','Jan 14','Jan 15','Jan 16','Jan 17','Jan 18'],
      datasets: [
        {
          type: 'bar',
          label: 'Sessions',
          data: [4200,3800,4100,4600,5200,6100,6800,6400,5900,5500,6200,7100,6900,7400],

          // Professional indigo with subtle vertical gradient
          backgroundColor: (ctx) => {
            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(99, 102, 241, 0.75)');   // indigo-500
            gradient.addColorStop(1, 'rgba(99, 102, 241, 0.45)');
            return gradient;
          },
          borderColor: 'rgb(99, 102, 241)',
          borderWidth: 1,
          borderRadius: 5,
          barPercentage: 0.85,
        },
        {
          type: 'line',
          label: 'Success Rate (%)',
          data: [92.1,93.4,94.2,91.8,93.7,95.1,94.6,92.9,93.2,94.8,93.9,92.4,94.1,95.3,],

          borderColor: '#10b981',                    // emerald-500
          backgroundColor: (context) => {
            const chart = context.chart;
            const { ctx, chartArea } = chart;
            if (!chartArea) return null;

            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.32)');
            gradient.addColorStop(0.5, 'rgba(16, 185, 129, 0.16)');
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');
            return gradient;
          },

          borderWidth: 2.5,
          tension: 0.32,
          fill: true,
          pointBackgroundColor: '#ffffff',
          pointBorderColor: '#10b981',
          pointBorderWidth: 2,
          pointRadius: 3,
          pointHoverRadius: 6,
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,

      interaction: {
        mode: 'index',
        intersect: false,
      },

      plugins: {
        legend: {
          position: 'top',
          align: 'start',
          labels: {
            color: '#334155',
            font: { size: 13, weight: 500 },
            boxWidth: 12,
            padding: 16,
            usePointStyle: true,
            pointStyle: 'rectRounded'
          }
        },
        tooltip: {
          backgroundColor: 'rgba(30, 41, 59, 0.94)',
          titleColor: '#f1f5f9',
          bodyColor: '#cbd5e1',
          borderColor: '#475569',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        }
      },

      scales: {
        x: {
          grid: { display: false },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 8
          }
        },
        y: {  // Left axis - Sessions
          type: 'linear',
          position: 'left',
          beginAtZero: true,
          title: {
            display: true,
            text: 'Sessions',
            color: '#334155',
            font: { size: 13, weight: 500 },
            padding: { top: 0, bottom: 10 }
          },
          grid: {
            color: '#e2e8f0',
            drawBorder: false,
          },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 12,
            callback: value => value.toLocaleString()
          }
        },
        y1: {  // Right axis - Success Rate
          type: 'linear',
          position: 'right',
          min: 80,
          max: 100,
          title: {
            display: true,
            text: 'Success Rate',
            color: '#334155',
            font: { size: 13, weight: 500 },
            padding: { top: 0, bottom: 10 }
          },
          grid: {
            drawOnChartArea: false,  // only want the grid lines for the primary axis to show
          },
          ticks: {
            color: '#10b981',
            font: { size: 12 },
            padding: 12,
            callback: value => value + '%'
          }
        }
      }
    }
  });
}
// 2. Sessions by Network (Bar)
if (networkChartRef.value) {
  chartInstances.value.network = new Chart(networkChartRef.value, {
    type: 'bar',
    data: {
      labels: ['Orange', 'MTN', 'Vodacom'],
      datasets: [{
        label: 'Sessions',
        data: [29860, 16870, 1560],
        backgroundColor: (ctx) => {
          const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
          gradient.addColorStop(0, 'rgba(249, 115, 22, 0.85)');   // orange-500 stronger at top
          gradient.addColorStop(1, 'rgba(249, 115, 22, 0.55)');
          return gradient;
        },
        borderColor: 'rgb(249, 115, 22)',
        borderWidth: 1,
        borderRadius: 6,           // rounded corners - modern look
      }]
    },
    options: {
      indexAxis: 'x',
      responsive: true,
      maintainAspectRatio: false,

      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(30, 41, 59, 0.94)',
          titleColor: '#f1f5f9',
          bodyColor: '#cbd5e1',
          borderColor: '#475569',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        }
      },

      scales: {
        x: {
          grid: { display: false },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 10
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: '#e2e8f0',
            drawBorder: false,
          },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 12,
            callback: value => value.toLocaleString()
          }
        }
      }
    }
  });
}

// 3. Sessions by Country (Bar)
if (countryChartRef.value) {
  chartInstances.value.country = new Chart(countryChartRef.value, {
    type: 'bar',
    data: {
      labels: ['Botswana', 'South Africa', 'Namibia', 'Zimbabwe'],
      datasets: [{
        label: 'Sessions',
        data: [28410, 12450, 3850, 2580],
        backgroundColor: (ctx) => {
          const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
          gradient.addColorStop(0, 'rgba(139, 92, 246, 0.85)');   // violet-500
          gradient.addColorStop(1, 'rgba(139, 92, 246, 0.55)');
          return gradient;
        },
        borderColor: 'rgb(139, 92, 246)',
        borderWidth: 1,
        borderRadius: 6,
      }]
    },
    options: {
      indexAxis: 'x',
      responsive: true,
      maintainAspectRatio: false,

      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(30, 41, 59, 0.94)',
          titleColor: '#f1f5f9',
          bodyColor: '#cbd5e1',
          borderColor: '#475569',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        }
      },

      scales: {
        x: {
          grid: { display: false },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 10
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: '#e2e8f0',
            drawBorder: false,
          },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 12,
            callback: value => value.toLocaleString()
          }
        }
      }
    }
  });
}

  // 4. Sessions by Network & Country (Grouped Bar)
if (networkCountryChartRef.value) {
  chartInstances.value.networkCountry = new Chart(networkCountryChartRef.value, {
    type: 'bar',
    data: {
      labels: ['Botswana', 'South Africa', 'Namibia'],
      datasets: [
        {
          label: 'Orange',
          data: [17800, 4200, 1900],
          backgroundColor: (ctx) => {
            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(249, 115, 22, 0.85)');   // orange-500
            gradient.addColorStop(1, 'rgba(249, 115, 22, 0.55)');
            return gradient;
          },
          borderColor: 'rgb(249, 115, 22)',
          borderWidth: 1,
          borderRadius: 6,
        },
        {
          label: 'MTN',
          data: [9800, 7650, 1650],
          backgroundColor: (ctx) => {
            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.85)');    // blue-500
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.55)');
            return gradient;
          },
          borderColor: 'rgb(59, 130, 246)',
          borderWidth: 1,
          borderRadius: 6,
        },
        {
          label: 'Vodacom',
          data: [810, 600, 300],
          backgroundColor: (ctx) => {
            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(34, 197, 94, 0.85)');     // green-500
            gradient.addColorStop(1, 'rgba(34, 197, 94, 0.55)');
            return gradient;
          },
          borderColor: 'rgb(34, 197, 94)',
          borderWidth: 1,
          borderRadius: 6,
        }
      ]
    },
    options: {
      indexAxis: 'x',
      responsive: true,
      maintainAspectRatio: false,

      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: '#334155',                // slate-700
            font: { size: 13, weight: 500 },
            padding: 16,
            boxWidth: 14,
            usePointStyle: true,
            pointStyle: 'rectRounded'
          }
        },
        tooltip: {
          backgroundColor: 'rgba(30, 41, 59, 0.94)',
          titleColor: '#f1f5f9',
          bodyColor: '#cbd5e1',
          borderColor: '#475569',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        }
      },

      scales: {
        x: {
          grid: { display: false },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 10
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: '#e2e8f0',
            drawBorder: false,
          },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 12,
            callback: value => value.toLocaleString()
          }
        }
      }
    }
  });
}

  // 6. New Users Trend (Line)
if (newUsersChartRef.value) {
  chartInstances.value.newUsers = new Chart(newUsersChartRef.value, {
    type: 'line',
    data: {
      labels: ['Jan 5','Jan 6','Jan 7','Jan 8','Jan 9','Jan 10','Jan 11','Jan 12','Jan 13','Jan 14','Jan 15','Jan 16','Jan 17','Jan 18'],
      datasets: [{
        label: 'New Users',
        data: [320, 280, 410, 520, 680, 920, 850, 720, 680, 610, 780, 950, 880, 1020],

        // Professional indigo/violet tone
        borderColor: '#6366f1',           // indigo-500
        backgroundColor: (context) => {
          const chart = context.chart;
          const { ctx, chartArea } = chart;
          if (!chartArea) return null;

          // Create vertical gradient – stronger at top, fades to almost transparent
          const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
          gradient.addColorStop(0, 'rgba(99, 102, 241, 0.35)');    // indigo-500 at 35% opacity
          gradient.addColorStop(0.5, 'rgba(99, 102, 241, 0.18)');
          gradient.addColorStop(1, 'rgba(99, 102, 241, 0.02)');    // very subtle near bottom
          return gradient;
        },

        borderWidth: 2.5,
        tension: 0.32,                    // slightly smoother curve
        fill: true,

        // Nice point styling for enterprise feel
        pointBackgroundColor: '#ffffff',
        pointBorderColor: '#6366f1',
        pointBorderWidth: 2,
        pointRadius: 3.5,
        pointHoverRadius: 6,
        pointHoverBorderWidth: 2,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,

      interaction: {
        mode: 'index',
        intersect: false,
      },

      plugins: {
        legend: {
          display: true,
          position: 'top',
          align: 'start',
          labels: {
            color: '#334155',           // slate-700
            font: { size: 13, weight: 500 },
            boxWidth: 12,
            padding: 16,
          }
        },
        tooltip: {
          backgroundColor: 'rgba(30, 41, 59, 0.94)', // slate-900 with opacity
          titleColor: '#f1f5f9',
          bodyColor: '#cbd5e1',
          borderColor: '#475569',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        }
      },

      scales: {
        x: {
          grid: {
            display: false,
          },
          ticks: {
            color: '#64748b',           // slate-500
            font: { size: 12 },
            padding: 8,
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: '#e2e8f0',           // very light slate
            drawBorder: false,
          },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 12,
            callback: value => value.toLocaleString()
          }
        }
      }
    }
  });
}

  // 6. Return Users Trend (Line)
if (returnUsersChartRef.value) {
  chartInstances.value.returnUsers = new Chart(returnUsersChartRef.value, {
    type: 'line',
    data: {
      labels: ['Jan 5','Jan 6','Jan 7','Jan 8','Jan 9','Jan 10','Jan 11','Jan 12','Jan 13','Jan 14','Jan 15','Jan 16','Jan 17','Jan 18'],
      datasets: [{
        label: 'Return Users',
        data: [320, 280, 410, 520, 680, 920, 850, 720, 680, 610, 780, 950, 880, 1020],

        // Professional violet/purple tone – distinct from indigo
        borderColor: '#7c3aed',                 // violet-600
        backgroundColor: (context) => {
          const chart = context.chart;
          const { ctx, chartArea } = chart;
          if (!chartArea) return null;

          const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
          gradient.addColorStop(0, 'rgba(124, 58, 237, 0.35)');   // violet at ~35% opacity
          gradient.addColorStop(0.5, 'rgba(124, 58, 237, 0.18)');
          gradient.addColorStop(1, 'rgba(124, 58, 237, 0.02)');   // very subtle fade
          return gradient;
        },

        borderWidth: 2.5,
        tension: 0.32,
        fill: true,

        // Consistent professional point styling
        pointBackgroundColor: '#ffffff',
        pointBorderColor: '#7c3aed',
        pointBorderWidth: 2,
        pointRadius: 3.5,
        pointHoverRadius: 6,
        pointHoverBorderWidth: 2,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,

      interaction: {
        mode: 'index',
        intersect: false,
      },

      plugins: {
        legend: {
          display: true,
          position: 'top',
          align: 'start',
          labels: {
            color: '#334155',
            font: { size: 13, weight: 500 },
            boxWidth: 12,
            padding: 16,
          }
        },
        tooltip: {
          backgroundColor: 'rgba(30, 41, 59, 0.94)',
          titleColor: '#f1f5f9',
          bodyColor: '#cbd5e1',
          borderColor: '#475569',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        }
      },

      scales: {
        x: {
          grid: { display: false },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 8,
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: '#e2e8f0',
            drawBorder: false,
          },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 12,
            callback: value => value.toLocaleString()
          }
        }
      }
    }
  });
}

// 7. Drop-off Screens (Horizontal Bar – consistent enterprise style)
if (funnelChartRef.value) {
  chartInstances.value.funnel = new Chart(funnelChartRef.value, {
    type: 'bar',
    data: {
      labels: ['Main Menu', 'Send Money', 'Cash Out', 'Cash In', 'Pay For Goods'],
      datasets: [{
        label: 'Users',
        data: [10000, 9200, 7800, 6200, 5100],
        backgroundColor: (ctx) => {
          const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300); // vertical gradient
          gradient.addColorStop(0, 'rgba(59, 130, 246, 0.85)');   // blue-500 strong at top
          gradient.addColorStop(1, 'rgba(59, 130, 246, 0.55)');   // softer toward bottom
          return gradient;
        },
        borderColor: 'rgb(59, 130, 246)',
        borderWidth: 1,
        borderRadius: 6,
        barThickness: 32,              // consistent with other horizontal bars
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,

      plugins: {
        legend: { display: false },
        title: {
          display: true,
          color: '#1e293b',
          font: { size: 15, weight: '600' },
          padding: { bottom: 16 },
          align: 'start'
        },
        tooltip: {
          backgroundColor: 'rgba(30, 41, 59, 0.94)',
          titleColor: '#f1f5f9',
          bodyColor: '#cbd5e1',
          borderColor: '#475569',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
          callbacks: {
            label: (context) => {
              const value = context.raw;
              const prev = context.dataIndex > 0
                ? context.chart.data.datasets[0].data[context.dataIndex - 1]
                : value;
              const retention = context.dataIndex > 0
                ? `${((value / prev) * 100).toFixed(1)}% retention`
                : 'Entry point';
              return `${value.toLocaleString()} users (${retention})`;
            }
          }
        }
      },

      scales: {
        x: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Users',
            color: '#334155',
            font: { size: 13, weight: 500 },
            padding: { top: 12 }
          },
          grid: {
            color: '#e2e8f0',
            drawBorder: false,
          },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 12,
            callback: value => value.toLocaleString()
          }
        },
        y: {
          grid: { display: false },
          ticks: {
            color: '#334155',
            font: { size: 13, weight: 500 },
            padding: 12
          }
        }
      }
    }
  });
}

// 9. Top Failure Reasons (Horizontal Bar – matches Open Flags by Category style)
if (failureReasonsChartRef.value) {
  chartInstances.value.failureReasons = new Chart(failureReasonsChartRef.value, {
    type: 'bar',
    data: {
      labels: ['Invalid PIN', 'Timeout', 'Network Error', 'Insufficient Funds', 'System Error', 'Other'],
      datasets: [{
        label: 'Failures',
        data: [42, 28, 15, 12, 8, 5],
        backgroundColor: (ctx) => {
          const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300); // vertical gradient
          gradient.addColorStop(0, 'rgba(239, 68, 68, 0.85)');   // red-500 strong at top
          gradient.addColorStop(1, 'rgba(239, 68, 68, 0.55)');   // softer toward bottom
          return gradient;
        },
        borderColor: 'rgb(239, 68, 68)',
        borderWidth: 1,
        borderRadius: 6,
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,

      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(30, 41, 59, 0.94)',
          titleColor: '#f1f5f9',
          bodyColor: '#cbd5e1',
          borderColor: '#475569',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        }
      },

      scales: {
        x: {
          beginAtZero: true,
          grid: { color: '#e2e8f0', drawBorder: false },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 12,
            callback: value => value.toLocaleString()
          }
        },
        y: {
          grid: { display: false },
          ticks: {
            color: '#334155',
            font: { size: 13, weight: 500 },
            padding: 12
          }
        }
      }
    }
  });
}
// Open vs Resolved Flags (Doughnut)
if (flagsStatusChartRef.value) {
  chartInstances.value.flagsStatus = new Chart(flagsStatusChartRef.value, {
    type: 'doughnut',
    data: {
      labels: ['Open', 'Resolved'],
      datasets: [{
        data: [47, 132],
        backgroundColor: [
          '#ef4444',  // red-500 (Open)
          '#10b981'   // emerald-500 (Resolved)
        ],
        borderColor: '#ffffff',
        borderWidth: 2,
        hoverOffset: 12,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',  // slightly larger cutout for elegance
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: '#334155',
            font: { size: 13, weight: 500 },
            padding: 20,
            usePointStyle: true,
            pointStyle: 'circle',
          }
        },
        tooltip: {
          backgroundColor: 'rgba(30, 41, 59, 0.94)',
          titleColor: '#f1f5f9',
          bodyColor: '#cbd5e1',
          borderColor: '#475569',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        }
      }
    }
  });
}

// Open Flags by Priority (Horizontal Bar)
if (flagsPriorityChartRef.value) {
  chartInstances.value.flagsPriority = new Chart(flagsPriorityChartRef.value, {
    type: 'bar',
    data: {
      labels: ['Critical', 'High', 'Medium', 'Low'],
      datasets: [{
        label: 'Open Flags',
        data: [8, 19, 34, 12],
        backgroundColor: (ctx) => {
          const gradients = [
            ctx.chart.ctx.createLinearGradient(0, 0, 0, 300), // Critical - red
            ctx.chart.ctx.createLinearGradient(0, 0, 0, 300), // High - orange
            ctx.chart.ctx.createLinearGradient(0, 0, 0, 300), // Medium - yellow
            ctx.chart.ctx.createLinearGradient(0, 0, 0, 300)  // Low - green
          ];
          gradients[0].addColorStop(0, 'rgba(239, 68, 68, 0.85)');
          gradients[0].addColorStop(1, 'rgba(239, 68, 68, 0.55)');
          gradients[1].addColorStop(0, 'rgba(249, 115, 22, 0.85)');
          gradients[1].addColorStop(1, 'rgba(249, 115, 22, 0.55)');
          gradients[2].addColorStop(0, 'rgba(234, 179, 8, 0.85)');
          gradients[2].addColorStop(1, 'rgba(234, 179, 8, 0.55)');
          gradients[3].addColorStop(0, 'rgba(34, 197, 94, 0.85)');
          gradients[3].addColorStop(1, 'rgba(34, 197, 94, 0.55)');
          return gradients[ctx.dataIndex];
        },
        borderWidth: 0,
        borderRadius: 6,
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,

      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(30, 41, 59, 0.94)',
          titleColor: '#f1f5f9',
          bodyColor: '#cbd5e1',
          borderColor: '#475569',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        }
      },

      scales: {
        x: {
          beginAtZero: true,
          grid: { color: '#e2e8f0', drawBorder: false },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 12,
            callback: value => value.toLocaleString()
          }
        },
        y: {
          grid: { display: false },
          ticks: {
            color: '#334155',
            font: { size: 13, weight: 500 },
            padding: 12
          }
        }
      }
    }
  });
}

// Open Flags by Category (Horizontal Bar)
if (flagsCategoryChartRef.value) {
  chartInstances.value.flagsCategory = new Chart(flagsCategoryChartRef.value, {
    type: 'bar',
    data: {
      labels: ['Bug', 'Performance', 'Security', 'UX', 'Content'],
      datasets: [{
        label: 'Open Flags',
        data: [18, 14, 9, 4, 2],
        backgroundColor: (ctx) => {
          const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
          gradient.addColorStop(0, 'rgba(168, 85, 247, 0.85)');   // purple-500
          gradient.addColorStop(1, 'rgba(168, 85, 247, 0.55)');
          return gradient;
        },
        borderColor: 'rgb(168, 85, 247)',
        borderWidth: 1,
        borderRadius: 6,
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,

      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(30, 41, 59, 0.94)',
          titleColor: '#f1f5f9',
          bodyColor: '#cbd5e1',
          borderColor: '#475569',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        }
      },

      scales: {
        x: {
          beginAtZero: true,
          grid: { color: '#e2e8f0', drawBorder: false },
          ticks: {
            color: '#64748b',
            font: { size: 12 },
            padding: 12,
            callback: value => value.toLocaleString()
          }
        },
        y: {
          grid: { display: false },
          ticks: {
            color: '#334155',
            font: { size: 13, weight: 500 },
            padding: 12
          }
        }
      }
    }
  });
}
// 5. Mobile vs Simulator Usage (Donut)
if (deviceChartRef.value) {
  chartInstances.value.device = new Chart(deviceChartRef.value, {
    type: 'doughnut',
    data: {
      labels: ['Mobile', 'Simulator'],
      datasets: [{
        data: [89, 11],
        backgroundColor: [
          '#6366f1',  // indigo-500 – Mobile (primary, trusted device)
          '#f59e0b'   // amber-500 – Simulator (secondary, caution/less common)
        ],
        borderColor: '#ffffff',
        borderWidth: 3,            // clean white border between segments
        hoverOffset: 12,           // nice hover expansion effect
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',               // slightly larger cutout = more elegant
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: '#334155',      // slate-700
            font: {
              size: 13,
              weight: 500
            },
            padding: 20,
            usePointStyle: true,
            pointStyle: 'circle',
            generateLabels: (chart) => {
              // Custom legend labels with percentages
              const data = chart.data.datasets[0].data;
              const total = data.reduce((sum, val) => sum + val, 0);
              return chart.data.labels.map((label, i) => ({
                text: `${label} (${((data[i] / total) * 100).toFixed(0)}%)`,
                fillStyle: chart.data.datasets[0].backgroundColor[i],
                strokeStyle: '#ffffff',
                lineWidth: 2,
                hidden: false,
                index: i
              }));
            }
          }
        },
        tooltip: {
          backgroundColor: 'rgba(30, 41, 59, 0.94)', // dark slate tooltip
          titleColor: '#f1f5f9',
          bodyColor: '#cbd5e1',
          borderColor: '#475569',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
          callbacks: {
            label: (context) => {
              const value = context.raw;
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = ((value / total) * 100).toFixed(1);
              return `${context.label}: ${value}% (${percentage}%)`;
            }
          }
        },
        datalabels: {  // Optional: show % directly on chart (uncomment if wanted)
          // display: true,
          // color: '#ffffff',
          // font: { weight: 'bold', size: 13 },
          // formatter: (value, ctx) => {
          //   const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
          //   return Math.round((value / total) * 100) + '%';
          // }
        }
      }
    }
  });
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
      <div class="p-4">
        <slot />
        </div>
    </div>
  `
}
</script>
