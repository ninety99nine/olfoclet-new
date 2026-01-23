<!-- resources/js/pages/accounts/Account.vue -->
<template>
  <div class="min-h-screen bg-slate-50 pb-12">

    <!-- Header / Breadcrumb -->
    <div class="bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-6 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
              <ol class="flex items-center space-x-2 text-sm text-slate-500">
                <li>
                  <a href="/accounts" class="hover:text-slate-700">Accounts</a>
                </li>
                <li>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </li>
                <li class="font-medium text-slate-900">{{ account.phone || 'Loading...' }}</li>
              </ol>
            </nav>
            <h1 class="text-2xl font-semibold text-slate-900">
              Account Details
            </h1>
            <p class="mt-1 text-sm text-slate-500">
              {{ account.phone || '—' }} • {{ account.network || '—' }}
            </p>
          </div>

          <div class="flex items-center gap-3 flex-wrap">
            <span
              class="inline-flex px-3.5 py-1.5 text-sm font-medium rounded-full"
              :class="account.status === 'Active'
                ? 'bg-emerald-50 text-emerald-700'
                : 'bg-amber-50 text-amber-700'"
            >
              {{ account.status || 'Unknown' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-8">

      <!-- Quick Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <p class="text-sm text-slate-500 font-medium">Total Sessions</p>
          <p class="text-4xl font-bold text-slate-900 mt-2">{{ account.total_sessions || 0 }}</p>
          <div class="mt-4 flex items-center gap-3">
            <span class="text-xl font-bold text-emerald-600">{{ account.success_rate || '—' }}%</span>
            <span class="text-xs text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full font-medium">
              Success Rate
            </span>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <p class="text-sm text-slate-500 font-medium">First Seen</p>
          <p class="text-3xl font-bold text-slate-900 mt-2">{{ account.first_seen || '—' }}</p>
          <p class="text-sm text-slate-500 mt-2">
            Last Activity: <strong>{{ account.last_activity || '—' }}</strong>
          </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <p class="text-sm text-slate-500 font-medium">Avg Session Duration</p>
          <p class="text-4xl font-bold text-slate-900 mt-2">{{ account.avg_duration || '—' }}</p>
          <p class="text-sm text-slate-500 mt-2">
            Avg Steps: <strong>{{ account.avg_steps || '—' }}</strong>
          </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <p class="text-sm text-slate-500 font-medium">Device Breakdown</p>
          <div class="mt-4 space-y-3">
            <div class="flex justify-between text-sm">
              <span class="text-slate-600">Mobile</span>
              <span class="font-medium">{{ account.mobile_percentage || '—' }}%</span>
            </div>
            <div class="h-2 bg-slate-100 rounded-full">
              <div class="h-full bg-indigo-600 rounded-full" :style="{ width: account.mobile_percentage + '%' }"></div>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-600">Simulator</span>
              <span class="font-medium">{{ account.simulator_percentage || '—' }}%</span>
            </div>
          </div>
        </div>
      </div>

<!-- Replace the existing content after Quick Stats Cards with this -->

<div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

  <!-- Left Column: Last Screen of Most Recent Sessions (main content) -->
  <div class="lg:col-span-8">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-6 py-5 border-b border-slate-100">
        <h2 class="text-lg font-semibold text-slate-900">
          Last 3 Sessions
        </h2>
        <p class="mt-1 text-sm text-slate-500">
          The final output the user saw in their last 3 sessions
        </p>
      </div>

<div class="divide-y divide-slate-100">
  <div v-for="(session, index) in lastScreens" :key="session.id"
       class="p-6 hover:bg-slate-50 transition-colors">
    <div class="flex items-start justify-between gap-6">

      <!-- Left: Session info + last screen -->
      <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-medium text-slate-600 text-lg">
                {{ index + 1 }}
            </div>
            <div>
                <p class="font-medium text-slate-900">
                {{ session.when || '—' }}
                <span class="ml-2 text-sm text-slate-500">({{ session.duration || '—' }})</span>
                </p>
                <p class="text-xs text-slate-500 mt-0.5">
                {{ session.status === 'Success' ? 'Successful' : 'Failed' }} •
                {{ session.device || '—' }} •
                5 steps
                </p>
            </div>
          </div>

      <!-- Right: Three actions – now INLINE (horizontal) -->
      <div class="flex items-center gap-2 flex-wrap mt-1">

        <!-- Flag button – exact style you requested -->
        <button
          @click="showMarkReviewModal = true; selectedSessionId = session.id"
          class="text-sm px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition-colors flex items-center gap-1.5 shrink-0"
          title="Flag this session for review"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Flag
        </button>

        <!-- View full session -->
        <router-link
          :to="`/sessions/${session.id}`"
          class="text-sm px-3 py-1.5 bg-white border border-indigo-300 text-indigo-700 hover:bg-indigo-50 rounded-lg font-medium transition-colors flex items-center gap-1.5"
          title="View complete session details"
        >
          Full session
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </router-link>
      </div>
        </div>

        <!-- The last screen output -->
        <div class="mt-4 bg-slate-900 text-slate-100 font-mono text-sm p-5 rounded-lg whitespace-pre-wrap leading-relaxed border border-slate-700 shadow-inner">
          {{ session.last_screen || 'No final screen recorded (session may have timed out early or was interrupted)' }}
        </div>

        <!-- Error message if failed -->
        <div v-if="session.status !== 'Success' && session.error_message"
             class="w-fit mt-3 text-sm text-rose-700 bg-rose-50 py-2 px-3 rounded border border-rose-200">
          {{ session.error_message }}
        </div>
      </div>

    </div>
  </div>

  <!-- Empty state -->
  <div v-if="!lastScreens.length" class="p-16 text-center text-slate-500">
    <p class="text-lg font-medium">No recent session screens available</p>
    <p class="mt-2">This account may not have completed any sessions yet.</p>
  </div>
</div>
    </div>
  </div>

  <!-- Right Column: Recent Flags + Quick Actions -->
  <div class="lg:col-span-4 space-y-4">
    <!-- Quick Actions -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
      <h3 class="text-lg font-semibold text-slate-900 mb-4">
        Quick Actions
      </h3>

      <div class="space-y-3">
        <button
          @click="copyToClipboard(account.phone, 'Phone number copied!')"
          class="w-full px-4 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          Copy Phone Number
        </button>

        <button
          @click="confirmBlockAccount"
          :disabled="account.status === 'Blocked'"
          class="w-full px-4 py-2.5 bg-white border border-rose-300 text-rose-700 hover:bg-rose-50 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
          </svg>
          Block Account
        </button>

        <!-- You can add more later: Reset PIN, Force end sessions, etc. -->
      </div>
    </div>

    <!-- Recent Flags -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
      <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center justify-between">
        <span>Recent Flags</span>
        <span class="text-sm font-normal text-slate-500">{{ recentFlags.length }}</span>
      </h3>

      <div class="space-y-4">
        <div v-for="flag in recentFlags" :key="flag.id" class="p-4 bg-slate-50 rounded-lg border border-slate-200">
          <div class="flex justify-between items-start gap-2">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold"
                      :class="{
                        'bg-rose-100 text-rose-700': flag.priority === 'critical',
                        'bg-orange-100 text-orange-700': flag.priority === 'high',
                        'bg-amber-100 text-amber-700': flag.priority === 'medium'
                      }">
                  {{ flag.priority[0].toUpperCase() }}
                </span>
                <span class="font-medium text-slate-900 text-sm">{{ flag.category }}</span>
              </div>
              <p class="text-sm text-slate-600 line-clamp-2">{{ flag.comment }}</p>
            </div>
            <span class="text-xs text-slate-500 whitespace-nowrap">
              {{ formattedRelativeDate(flag.timestamp) }}
            </span>
          </div>
        </div>

        <p v-if="!recentFlags.length" class="text-sm text-slate-500 italic text-center py-6">
          No flags yet
        </p>
      </div>

      <div class="mt-4 text-center">
        <router-link
          :to="`/issues?account=${account.phone}`"
          class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
        >
          View all issues →
        </router-link>
      </div>
    </div>

  </div>
</div>

        <!-- Controls now moved here - above the full sessions table -->
        <div class="bg-white rounded-t-xl rounded-x-xl border border-slate-200 shadow-sm overflow-hidden mt-4">

        <!-- Controls Header -->
        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Sessions</h2>

            <!-- Search + Date + Filter + Sort Controls -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Search -->
            <div class="relative flex-1 max-w-md">
                <input
                v-model="searchQuery"
                type="text"
                placeholder="Search sessions (when, status, device...)"
                class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                @keyup.enter="handleSearch"
                />
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-3 flex-wrap">
                <button @click="showDateRangeModal = true" class="px-5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Date Range
                <span v-if="selectedRange.label !== 'All Time'" class="ml-1 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full">
                    {{ selectedRange.label }}
                </span>
                </button>

                <button @click="showFilterModal = true" class="px-5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M3 8h18M3 12h18M3 16h18" />
                </svg>
                Filter
                <span v-if="activeFilters.length" class="ml-1 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full">
                    {{ activeFilters.length }}
                </span>
                </button>

                <button @click="showSortModal = true" class="px-5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9M3 12h6M3 16h4" />
                </svg>
                Sort
                <span v-if="activeSorts.length" class="ml-1 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full">
                    {{ activeSorts.length }}
                </span>
                </button>
            </div>
            </div>

            <!-- Active Tags -->
            <div v-if="activeFilters.length || activeSorts.length || selectedRange.label !== 'All Time'" class="flex flex-wrap gap-2 mt-4">
            <!-- ... your existing active tags code ... -->
            </div>
        </div>
        </div>

      <!-- Recent Sessions -->
      <div class="bg-white rounded-b-xl rounded-x-xl border border-slate-200 overflow-hidden shadow-sm">

        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-slate-50/70">
              <tr>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                  When
                </th>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                  Duration
                </th>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                  Steps
                </th>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                  Device
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="session in recentSessions" :key="session.id" class="hover:bg-slate-50 transition-colors cursor-pointer">
                <td class="px-6 py-4 text-sm text-slate-700">
                  {{ session.when || '—' }}
                </td>
                <td class="px-6 py-4 text-sm text-slate-700">
                  {{ session.duration || '—' }}
                </td>
                <td class="px-6 py-4 text-sm text-slate-700 text-center">
                  {{ session.steps || '—' }}
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="session.status === 'Success'
                      ? 'bg-emerald-50 text-emerald-700'
                      : 'bg-rose-50 text-rose-700'"
                    class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full"
                  >
                    {{ session.status || '—' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="session.device === 'Mobile'
                      ? 'bg-indigo-50 text-indigo-700'
                      : 'bg-amber-50 text-amber-700'"
                    class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full"
                  >
                    {{ session.device || '—' }}
                  </span>
                </td>
              </tr>

              <tr v-if="!recentSessions.length">
                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                  No recent sessions found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 text-center text-sm text-slate-500">
          <router-link
            :to="`/accounts/${account.id}/sessions`"
            class="text-indigo-600 hover:text-indigo-800 font-medium"
          >
            View all sessions →
          </router-link>
        </div>
      </div>

      <!-- More sections can be added later: Timeline, Notes, Risk flags, etc. -->
    </div>
  </div>
</template>

<script>
import { formattedRelativeDate } from '@Utils/dateUtils.js'
export default {
  name: 'AccountDetail',
  data() {
    return {
      searchQuery: '',
      showFilterModal: false,
      showSortModal: false,
      showDateRangeModal: false,
      activeFilters: [],
      activeSorts: [],
      newFilterValue: '',

      // Date Range (First Seen)
      selectedRange: { value: 'all', label: 'All Time' },
      customStart: '',
      customEnd: '',

      filterOptions: [
        { field: 'network', label: 'Network', type: 'select', values: ['MTN', 'Orange'] },
        { field: 'status', label: 'Status', type: 'select', values: ['Active', 'Inactive'] },
      ],

      sortOptions: [
        { value: 'newest', label: 'Newest first', field: 'first_seen', direction: 'desc' },
        { value: 'oldest', label: 'Oldest first', field: 'first_seen', direction: 'asc' },
        { value: 'most_active', label: 'Most active (sessions)', field: 'total_sessions', direction: 'desc' },
        { value: 'least_active', label: 'Least active', field: 'total_sessions', direction: 'asc' },
        { value: 'best_success', label: 'Best success rate', field: 'success_rate', direction: 'desc' },
        { value: 'worst_success', label: 'Worst success rate', field: 'success_rate', direction: 'asc' },
      ],

      datePresets: [
        { value: 'this_week', label: 'This Week' },
        { value: 'this_month', label: 'This Month' },
        { value: 'last_3_months', label: 'Last 3 Months' },
        { value: 'this_year', label: 'This Year' },
        { value: 'all', label: 'All Time' },
      ],
      account: {
        id: null,
        phone: '',
        network: '',
        status: 'Active',
        first_seen: '',
        last_activity: '',
        total_sessions: 0,
        success_rate: '—',
        avg_duration: '—',
        avg_steps: '—',
        mobile_percentage: 0,
        simulator_percentage: 0,
      },
      recentSessions: [],
      loading: true,
    }
  },
  async created() {
    await this.fetchAccount()
    await this.fetchRecentSessions()
    this.loading = false
  },
  computed: {
  recentFlags() {
    // You would normally fetch these, but for now dummy data
    return [
      {
        id: 'f1',
        category: 'Fraud suspicion',
        priority: 'high',
        comment: 'Multiple failed PIN attempts + new device detected',
        timestamp: '2026-01-18T14:22:00Z',
        status: 'open'
      },
      // ...
    ]
  },
lastScreens() {
    // Normally you would fetch or compute this from real data
    // Here using dummy data for illustration
    return [
      {
        id: 'sess-001',
        when: '2 hours ago',
        duration: '1m 42s',
        status: 'Success',
        device: 'Mobile',
        last_screen: 'Your balance is BWP 1,234.56\nAvailable: BWP 1,200.00\n\n1. Transfer\n2. Buy Airtime\n3. Pay Bills\n0. Exit'
      },
      {
        id: 'sess-002',
        when: '1 day ago',
        duration: '48s',
        status: 'Failed',
        device: 'Simulator',
        last_screen: 'Error: Invalid PIN. Please try again.\nYou have 2 attempts remaining.',
        error_message: 'PIN verification failed (3 wrong attempts)'
      },
      {
        id: 'sess-003',
        when: '3 days ago',
        duration: '2m 19s',
        status: 'Success',
        device: 'Mobile',
        last_screen: 'Transaction successful!\nYou sent BWP 150.00 to +267 75 123 456\nNew balance: BWP 1,084.56\n\nThank you for using our service.'
      }
    ]
  }
},
  methods: {
    formattedRelativeDate,
    copyToClipboard(text, message) {
    navigator.clipboard.writeText(text)
    alert(message || 'Copied!')
  },
  forceLogout() {
    if (confirm('Really force logout all sessions for this user?')) {
      alert('All sessions terminated (mock)')
      // Real: api.forceLogout(account.id)
    }
  },
  confirmBlockAccount() {
    if (confirm('This action will BLOCK the account permanently!\nAre you sure?')) {
      alert('Account blocked (mock)')
      this.account.status = 'Blocked'
    }
  },
    async fetchAccount() {
      const id = this.$route.params.id
      // Example API call - replace with your real endpoint
      try {
        // const response = await axios.get(`/api/accounts/${id}`)
        // this.account = response.data

        // Dummy data for now
        this.account = {
          id,
          phone: '+267 71 234 567',
          network: 'Orange',
          status: 'Active',
          first_seen: '14 Oct 2024',
          last_activity: '2 hours ago',
          total_sessions: 87,
          success_rate: '94.3',
          avg_duration: '1m 48s',
          avg_steps: '11.2',
          mobile_percentage: 88,
          simulator_percentage: 12,
        }
      } catch (error) {
        console.error('Failed to load account:', error)
      }
    },

    async fetchRecentSessions() {
      // Replace with real API call
      this.recentSessions = [
        { id: 1, when: '2 hours ago', duration: '2m 14s', steps: 14, status: 'Success', device: 'Mobile' },
        { id: 2, when: '1 day ago', duration: '1m 38s', steps: 9, status: 'Success', device: 'Mobile' },
        { id: 3, when: '3 days ago', duration: '58s', steps: 6, status: 'Failed', device: 'Simulator' },
        // ... more
      ]
    }
  }
}
</script>
