<template>
  <div class="min-h-screen bg-slate-50 pb-12">

    <!-- Header -->
    <div class="bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-6 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h1 class="text-2xl font-semibold text-slate-900">Issues & Flags</h1>
            <p class="mt-1 text-sm text-slate-500">
              Feedback from QA team — {{ filteredStats.totalOpen }} open issues across sessions
            </p>
          </div>

          <div class="flex items-center gap-3 flex-wrap">
            <button class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                Resources
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
</svg>

            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Overview (now always based on filtered results) -->
    <div class="max-w-7xl mx-auto px-6 pt-8 mb-8">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl border border-rose-200 p-6 shadow-sm relative overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-rose-50 rounded-bl-full -mr-16 -mt-16"></div>
          <p class="text-sm text-rose-700 font-medium">Critical Open</p>
          <p class="text-4xl font-bold text-rose-700 mt-2">{{ filteredStats.criticalOpen }}</p>
          <p v-if="isFiltered" class="text-xs text-rose-600 mt-1 italic">filtered</p>
        </div>

        <div class="bg-white rounded-2xl border border-orange-200 p-6 shadow-sm relative overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-orange-50 rounded-bl-full -mr-16 -mt-16"></div>
          <p class="text-sm text-orange-700 font-medium">High Open</p>
          <p class="text-4xl font-bold text-orange-700 mt-2">{{ filteredStats.highOpen }}</p>
          <p v-if="isFiltered" class="text-xs text-orange-600 mt-1 italic">filtered</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <p class="text-sm text-slate-600 font-medium">Total Open (filtered)</p>
          <p class="text-4xl font-bold text-slate-900 mt-2">{{ filteredStats.totalOpen }}</p>
          <p class="text-sm text-slate-500 mt-1">
            from {{ filteredStats.uniqueSessions }} session{{ filteredStats.uniqueSessions !== 1 ? 's' : '' }}
          </p>
        </div>

        <div class="bg-white rounded-2xl border border-emerald-200 p-6 shadow-sm">
          <p class="text-sm text-emerald-700 font-medium">Resolved (filtered)</p>
          <p class="text-4xl font-bold text-emerald-600 mt-2">{{ filteredStats.resolvedCount }}</p>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 grid grid-cols-12 gap-4">

        <div class="col-span-9">

            <!-- Controls: Search + Date + Filter + Sort -->
            <div>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
                <!-- Search -->
                <div class="relative flex-1 max-w-md">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search by session number..."
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
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
            <div v-if="activeFilters.length || activeSorts.length || selectedRange.label !== 'All Time'" class="flex flex-wrap gap-2 mb-6">
                <span v-if="selectedRange.label !== 'All Time'" class="inline-flex items-center px-3 py-1 bg-purple-50 text-purple-700 text-xs font-medium rounded-full">
                {{ selectedRange.label }}
                <button @click="clearDateRange" class="ml-2 text-purple-500 hover:text-purple-700">×</button>
                </span>

                <span v-for="(f, i) in activeFilters" :key="i" class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full">
                {{ f.label }}
                <button @click="removeFilter(i)" class="ml-2 text-indigo-500 hover:text-indigo-700">×</button>
                </span>

                <span v-for="(s, i) in activeSorts" :key="i" class="inline-flex items-center px-3 py-1 bg-amber-50 text-amber-800 text-xs font-medium rounded-full">
                {{ s.label }}
                <button @click="removeSort(i)" class="ml-2 text-amber-600 hover:text-amber-800">×</button>
                </span>

                <button @click="clearAll" class="text-sm text-slate-500 hover:text-slate-700 underline">
                Clear all
                </button>
            </div>
            </div>

            <!-- Issues List -->
            <div>
            <div class="space-y-5">
                <div v-for="issue in paginatedIssues" :key="issue.id"
                    class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full font-bold text-lg"
                            :class="{
                            'bg-rose-100 text-rose-700': issue.priority === 'critical',
                            'bg-orange-100 text-orange-700': issue.priority === 'high',
                            'bg-amber-100 text-amber-700': issue.priority === 'medium',
                            'bg-emerald-100 text-emerald-700': issue.priority === 'low'
                            }">
                        {{ issue.priority[0].toUpperCase() }}
                    </span>

                    <div>
                        <p class="font-medium text-slate-900">{{ issue.category }} — {{ issue.sessionId }}</p>
                        <p class="text-sm text-slate-500">
                        Flagged by {{ issue.user }} • {{ formattedRelativeDate(issue.timestamp) }}
                        </p>
                    </div>
                    </div>

                    <div class="flex items-center gap-3">
                    <span class="text-xs px-3 py-1 rounded-full font-medium whitespace-nowrap"
                            :class="issue.status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
                        {{ issue.status === 'resolved' ? 'Resolved' : 'Open' }}
                    </span>

                    <router-link :to="`/sessions/${issue.sessionId}`"
                                class="px-5 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2">
                        View Session →
                    </router-link>
                    </div>
                </div>

                <div class="px-6 py-4 text-sm text-slate-700 border-b border-slate-100">
                    {{ issue.comment }}
                </div>

                <div v-if="issue.status === 'resolved'" class="px-6 py-4 bg-emerald-50/50 text-sm text-slate-600">
                    <p>
                    Resolved by <span class="font-medium text-slate-800">{{ issue.resolvedBy || 'Team' }}</span>
                    <span class="ml-2 text-xs text-slate-500">
                        {{ formattedRelativeDate(issue.resolvedAt) }}
                    </span>
                    </p>
                    <p v-if="issue.resolvedComment" class="mt-2 italic">
                    "{{ issue.resolvedComment }}"
                    </p>
                </div>

                <div v-if="issue.status === 'open'" class="px-6 py-4 bg-slate-50 flex justify-end">
                    <button @click="resolveIssue(issue.id)"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors">
                    Mark Resolved
                    </button>
                </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="mt-8 flex items-center justify-between text-sm">
                <button @click="currentPage--" :disabled="currentPage === 1"
                        class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                Previous
                </button>

                <div class="flex gap-2">
                <button v-for="page in pageNumbers" :key="page"
                        @click="currentPage = page"
                        :class="currentPage === page
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white border border-slate-300 text-slate-600 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-lg font-medium transition-colors">
                    {{ page }}
                </button>
                </div>

                <button @click="currentPage++" :disabled="currentPage === totalPages"
                        class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                Next
                </button>
            </div>

            <!-- Empty State -->
            <div v-if="!filteredIssues.length" class="text-center py-16 text-slate-500">
                <p class="text-lg font-medium">No issues found</p>
                <p class="mt-2">Try adjusting your filters, search, or date range</p>
            </div>
            </div>

        </div>

        <div class="col-span-3 space-y-6">

            <!-- Current Focus / Top Issue -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Top Priority Now
                </h3>
                <div v-if="filteredStats.criticalOpen > 0" class="space-y-4">
                <div class="flex items-start gap-3">
                    <span class="flex-shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-full bg-rose-100 text-rose-700 font-bold text-lg">
                    C
                    </span>
                    <div>
                    <p class="font-medium text-slate-900 text-sm">Critical Open Issues</p>
                    <p class="text-sm text-slate-600 mt-0.5">{{ filteredStats.criticalOpen }} pending</p>
                    </div>
                </div>

                <button class="w-full px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm rounded-lg transition-colors">
                    Jump to First Critical →
                </button>
                </div>

                <p v-else class="text-sm text-emerald-700 italic">
                No critical issues right now — nice work!
                </p>
            </div>


            <!-- Quick Stats Breakdown -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Quick Breakdown</h3>
                <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-600">Bugs</dt>
                    <dd class="font-medium">{{ filteredIssues.filter(i => i.category === 'bug').length }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Performance</dt>
                    <dd class="font-medium">{{ filteredIssues.filter(i => i.category === 'performance').length }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-600">Security</dt>
                    <dd class="font-medium">{{ filteredIssues.filter(i => i.category === 'security').length }}</dd>
                </div>
                <div class="flex justify-between pt-2 border-t border-slate-100">
                    <dt class="text-slate-600 font-medium">Total in View</dt>
                    <dd class="font-bold text-slate-900">{{ filteredIssues.length }}</dd>
                </div>
                </dl>
            </div>

        </div>

    </div>

    <!-- Date Range Modal -->
    <div v-if="showDateRangeModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-slate-900">Select Date Range</h3>
          <button @click="showDateRangeModal = false" class="text-slate-500 hover:text-slate-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6 space-y-6">
          <div class="grid grid-cols-2 gap-3">
            <button v-for="preset in datePresets" :key="preset.value" @click="applyPreset(preset)"
                    class="px-4 py-3 text-sm font-medium border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors text-center"
                    :class="{ 'bg-indigo-600 text-white border-indigo-600': selectedRange.value === preset.value }">
              {{ preset.label }}
            </button>
          </div>

          <div class="space-y-4 pt-4 border-t border-slate-100">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Start Date</label>
              <input v-model="customStart" type="date" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">End Date</label>
              <input v-model="customEnd" type="date" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm" />
            </div>
            <button @click="applyCustomRange" :disabled="!customStart || !customEnd"
                    class="w-full px-5 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:bg-slate-300 transition-colors">
              Apply Custom Range
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Modal (now with category & priority checkboxes) -->
    <div v-if="showFilterModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-slate-900">Filter Issues</h3>
          <button @click="showFilterModal = false" class="text-slate-500 hover:text-slate-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6 space-y-8">
          <!-- Status -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="checkbox" v-model="filters.statusOpen" class="w-4 h-4 text-indigo-600 border-slate-300 rounded" />
                <span class="ml-2 text-sm text-slate-700">Open</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" v-model="filters.statusResolved" class="w-4 h-4 text-indigo-600 border-slate-300 rounded" />
                <span class="ml-2 text-sm text-slate-700">Resolved</span>
              </label>
            </div>
          </div>

          <!-- Priority -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Priority</label>
            <div class="grid grid-cols-2 gap-2">
              <label class="flex items-center">
                <input type="checkbox" v-model="filters.priorityCritical" class="w-4 h-4 text-rose-600 border-slate-300 rounded" />
                <span class="ml-2 text-sm text-rose-700">Critical</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" v-model="filters.priorityHigh" class="w-4 h-4 text-orange-600 border-slate-300 rounded" />
                <span class="ml-2 text-sm text-orange-700">High</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" v-model="filters.priorityMedium" class="w-4 h-4 text-amber-600 border-slate-300 rounded" />
                <span class="ml-2 text-sm text-amber-700">Medium</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" v-model="filters.priorityLow" class="w-4 h-4 text-emerald-600 border-slate-300 rounded" />
                <span class="ml-2 text-sm text-emerald-700">Low</span>
              </label>
            </div>
          </div>

          <!-- Category -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Category</label>
            <div class="grid grid-cols-2 gap-2">
              <label class="flex items-center">
                <input type="checkbox" v-model="filters.categoryBug" class="w-4 h-4 text-indigo-600 border-slate-300 rounded" />
                <span class="ml-2 text-sm text-slate-700">Bug</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" v-model="filters.categoryUX" class="w-4 h-4 text-indigo-600 border-slate-300 rounded" />
                <span class="ml-2 text-sm text-slate-700">UX</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" v-model="filters.categoryPerformance" class="w-4 h-4 text-indigo-600 border-slate-300 rounded" />
                <span class="ml-2 text-sm text-slate-700">Performance</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" v-model="filters.categorySecurity" class="w-4 h-4 text-indigo-600 border-slate-300 rounded" />
                <span class="ml-2 text-sm text-slate-700">Security</span>
              </label>
            </div>
          </div>
        </div>

        <div class="px-6 py-5 border-t border-slate-100 flex items-center justify-between">
          <button @click="clearFilters" class="text-sm text-slate-600 hover:text-slate-800">
            Clear Filters
          </button>
          <div class="flex gap-3">
            <button @click="showFilterModal = false" class="px-5 py-2.5 text-slate-600 hover:bg-slate-100 rounded-lg">
              Cancel
            </button>
            <button @click="applyFilters" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
              Apply
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { formattedRelativeDate } from '@Utils/dateUtils.js'

export default {
  name: 'Issues',

  data() {
    return {
      showFilterModal: false,
      showDateRangeModal: false,
      showSortModal: false,
      searchQuery: '',
      activeFilters: [],
      activeSorts: [],
      selectedRange: { value: 'all', label: 'All Time' },
      customStart: '',
      customEnd: '',
      currentPage: 1,
      itemsPerPage: 5,

      filters: {
        statusOpen: true,
        statusResolved: false,
        priorityCritical: false,
        priorityHigh: false,
        priorityMedium: false,
        priorityLow: false,
        categoryBug: false,
        categoryUX: false,
        categoryPerformance: false,
        categorySecurity: false
      },

      datePresets: [
        { value: 'today', label: 'Today' },
        { value: 'this_week', label: 'This Week' },
        { value: 'this_month', label: 'This Month' },
        { value: 'this_year', label: 'This Year' },
        { value: 'all', label: 'All Time' }
      ],

      sortOptions: [
        { value: 'newest', label: 'Newest first', field: 'timestamp', direction: 'desc' },
        { value: 'oldest', label: 'Oldest first', field: 'timestamp', direction: 'asc' },
        { value: 'priority_desc', label: 'Priority (high to low)', field: 'priority', direction: 'desc' }
      ],
issues: [
  {
    id: 'rev-001',
    sessionId: '145012',
    user: 'Neo (QA)',
    category: 'bug',
    priority: 'critical',
    comment: 'Payment fails with "Internal Server Error 500" when selecting Orange network on step 4',
    timestamp: '2026-01-15T15:47:33Z',
    status: 'open',
    resolvedBy: null,
    resolvedAt: null,
    resolvedComment: null
  },
  {
    id: 'rev-002',
    sessionId: '145019',
    user: 'Thabo (QA)',
    category: 'security',
    priority: 'high',
    comment: 'Session token visible in plain text in USSD response headers (Orange network)',
    timestamp: '2026-01-16T08:22:10Z',
    status: 'open',
    resolvedBy: null,
    resolvedAt: null,
    resolvedComment: null
  },
  {
    id: 'rev-003',
    sessionId: '144998',
    user: 'Sarah (QA)',
    category: 'performance',
    priority: 'high',
    comment: 'Main menu takes average 17.8s to load on MTN (Botswana) – tested on 15 sessions',
    timestamp: '2026-01-14T11:05:00Z',
    status: 'open',
    resolvedBy: null,
    resolvedAt: null,
    resolvedComment: null
  },
  {
    id: 'rev-004',
    sessionId: '145007',
    user: 'Lerato (QA)',
    category: 'ux',
    priority: 'medium',
    comment: 'Confusing wording on "Mini Statement" option – users think it shows last 5 txns only (it shows all)',
    timestamp: '2026-01-13T14:30:22Z',
    status: 'resolved',
    resolvedBy: 'Neo (Dev)',
    resolvedAt: '2026-01-15T09:12:45Z',
    resolvedComment: 'Updated menu text to "Full Transaction History" – awaiting deployment'
  },
  {
    id: 'rev-005',
    sessionId: '145021',
    user: 'Kagiso (QA)',
    category: 'content',
    priority: 'low',
    comment: 'Spelling mistake in airtime purchase confirmation: "Airtme" instead of "Airtime"',
    timestamp: '2026-01-16T10:15:00Z',
    status: 'open',
    resolvedBy: null,
    resolvedAt: null,
    resolvedComment: null
  }
]
    }
  },

  computed: {
    hotSessions() {
  const countBySession = {}
  this.filteredIssues.forEach(i => {
    if (i.status === 'open') {
      countBySession[i.sessionId] = (countBySession[i.sessionId] || 0) + 1
    }
  })
  return Object.entries(countBySession)
    .map(([id, openCount]) => ({ id, openCount }))
    .sort((a, b) => b.openCount - a.openCount)
},
    filteredIssues() {
      let result = [...this.issues]

      // Search
      if (this.searchQuery.trim()) {
        const q = this.searchQuery.toLowerCase()
        result = result.filter(i => i.sessionId.toLowerCase().includes(q))
      }

      // Date Range (simplified - add real date logic when you have dates)
      // ...

      // Status filter (multi)
      if (!this.filters.statusOpen && this.filters.statusResolved) {
        result = result.filter(i => i.status === 'resolved')
      } else if (this.filters.statusOpen && !this.filters.statusResolved) {
        result = result.filter(i => i.status === 'open')
      }

      // Priority filter (multi)
      const selectedPriorities = []
      if (this.filters.priorityCritical) selectedPriorities.push('critical')
      if (this.filters.priorityHigh) selectedPriorities.push('high')
      if (this.filters.priorityMedium) selectedPriorities.push('medium')
      if (this.filters.priorityLow) selectedPriorities.push('low')
      if (selectedPriorities.length) {
        result = result.filter(i => selectedPriorities.includes(i.priority))
      }

      // Category filter (multi)
      const selectedCategories = []
      if (this.filters.categoryBug) selectedCategories.push('bug')
      if (this.filters.categoryUX) selectedCategories.push('ux')
      if (this.filters.categoryPerformance) selectedCategories.push('performance')
      if (this.filters.categorySecurity) selectedCategories.push('security')
      if (selectedCategories.length) {
        result = result.filter(i => selectedCategories.includes(i.category))
      }

      // Sort (last sort wins)
      if (this.activeSorts.length) {
        const sort = this.activeSorts[this.activeSorts.length - 1]
        result.sort((a, b) => {
          if (sort.field === 'timestamp') {
            return sort.direction === 'desc'
              ? new Date(b.timestamp) - new Date(a.timestamp)
              : new Date(a.timestamp) - new Date(b.timestamp)
          }
          if (sort.field === 'priority') {
            const order = { critical: 4, high: 3, medium: 2, low: 1 }
            return sort.direction === 'desc'
              ? (order[b.priority] || 0) - (order[a.priority] || 0)
              : (order[a.priority] || 0) - (order[b.priority] || 0)
          }
          return 0
        })
      }

      return result
    },

    paginatedIssues() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      return this.filteredIssues.slice(start, start + this.itemsPerPage)
    },

    totalPages() {
      return Math.ceil(this.filteredIssues.length / this.itemsPerPage) || 1
    },

    pageNumbers() {
      const pages = []
      const max = 5
      let start = Math.max(1, this.currentPage - Math.floor(max / 2))
      let end = Math.min(this.totalPages, start + max - 1)
      if (end - start + 1 < max) start = Math.max(1, end - max + 1)
      for (let i = start; i <= end; i++) pages.push(i)
      return pages
    },

    filteredStats() {
      const f = this.filteredIssues
      const open = f.filter(i => i.status === 'open')
      const resolved = f.filter(i => i.status === 'resolved')

      return {
        criticalOpen: open.filter(i => i.priority === 'critical').length,
        highOpen: open.filter(i => i.priority === 'high').length,
        totalOpen: open.length,
        uniqueSessions: new Set(f.map(i => i.sessionId)).size,
        resolvedCount: resolved.length
      }
    },

    isFiltered() {
      return this.searchQuery.trim() ||
             this.activeFilters.length > 0 ||
             this.activeSorts.length > 0 ||
             this.selectedRange.label !== 'All Time'
    },

    openIssueCount() {
      return this.issues.filter(i => i.status === 'open').length
    }
  },

  methods: {
    formattedRelativeDate,
    async resolveIssue(reviewId) {
      const issue = this.issues.find(i => i.id === reviewId)
      if (!issue) return

      const comment = prompt('Resolution comment (optional):')

      issue.status = 'resolved'
      issue.resolvedBy = 'Current Dev'
      issue.resolvedAt = new Date().toISOString()
      issue.resolvedComment = comment || null

      alert('Issue resolved!')
    },

    // Date Range
    applyPreset(preset) {
      this.selectedRange = { value: preset.value, label: preset.label }
      this.customStart = ''
      this.customEnd = ''
      this.showDateRangeModal = false
      this.currentPage = 1
    },

    applyCustomRange() {
      if (!this.customStart || !this.customEnd) return
      this.selectedRange = {
        value: 'custom',
        label: `${this.formatDate(this.customStart)} → ${this.formatDate(this.customEnd)}`
      }
      this.showDateRangeModal = false
      this.currentPage = 1
    },

    clearDateRange() {
      this.selectedRange = { value: 'all', label: 'All Time' }
      this.customStart = ''
      this.customEnd = ''
      this.currentPage = 1
    },

    formatDate(dateStr) {
      return new Date(dateStr).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
    },

    applyFilters() {
      this.activeFilters = []

      if (this.filters.statusOpen && !this.filters.statusResolved) {
        this.activeFilters.push({ label: 'Status: Open' })
      } else if (!this.filters.statusOpen && this.filters.statusResolved) {
        this.activeFilters.push({ label: 'Status: Resolved' })
      }

      if (this.filters.priorityCritical) this.activeFilters.push({ label: 'Priority: Critical' })
      if (this.filters.priorityHigh) this.activeFilters.push({ label: 'Priority: High' })
      if (this.filters.priorityMedium) this.activeFilters.push({ label: 'Priority: Medium' })
      if (this.filters.priorityLow) this.activeFilters.push({ label: 'Priority: Low' })

      if (this.filters.categoryBug) this.activeFilters.push({ label: 'Category: Bug' })
      if (this.filters.categoryUX) this.activeFilters.push({ label: 'Category: UX' })
      if (this.filters.categoryPerformance) this.activeFilters.push({ label: 'Category: Performance' })
      if (this.filters.categorySecurity) this.activeFilters.push({ label: 'Category: Security' })

      this.showFilterModal = false
      this.currentPage = 1
    },

    removeFilter(index) {
      const removed = this.activeFilters.splice(index, 1)[0]
      const label = removed.label.toLowerCase()

      if (label.includes('status: open')) this.filters.statusOpen = false
      if (label.includes('status: resolved')) this.filters.statusResolved = false
      if (label.includes('priority: critical')) this.filters.priorityCritical = false
      if (label.includes('priority: high')) this.filters.priorityHigh = false
      if (label.includes('priority: medium')) this.filters.priorityMedium = false
      if (label.includes('priority: low')) this.filters.priorityLow = false
      if (label.includes('category: bug')) this.filters.categoryBug = false
      if (label.includes('category: ux')) this.filters.categoryUX = false
      if (label.includes('category: performance')) this.filters.categoryPerformance = false
      if (label.includes('category: security')) this.filters.categorySecurity = false

      this.currentPage = 1
    },

    clearFilters() {
      this.filters = {
        statusOpen: true,
        statusResolved: false,
        priorityCritical: false,
        priorityHigh: false,
        priorityMedium: false,
        priorityLow: false,
        categoryBug: false,
        categoryUX: false,
        categoryPerformance: false,
        categorySecurity: false
      }
      this.activeFilters = []
      this.currentPage = 1
    },

    // Sort
    addSort(option) {
      this.activeSorts = [option] // single sort for simplicity
      this.showSortModal = false
      this.currentPage = 1
    },

    removeSort() {
      this.activeSorts = []
      this.currentPage = 1
    },

    clearAll() {
      this.clearFilters()
      this.clearDateRange()
      this.activeSorts = []
      this.currentPage = 1
    },


  }
}
</script>
