<!-- resources/js/pages/deployments/Deployments.vue -->

<template>
  <div class="min-h-screen bg-slate-50 pb-12">

    <!-- Header + Controls -->
    <div class="bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Deployments</h1>
          <p class="mt-1 text-sm text-slate-500">Manage network connections and integrations</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
          <!-- Search Bar -->
          <div class="relative min-w-[280px] sm:min-w-[340px]">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by network, country..."
              class="block w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-colors bg-white shadow-sm"
              @keyup.enter="handleSearch"
            />
          </div>

          <!-- Date Range Button -->
          <button
            @click="showDateRangeModal = true"
            class="px-5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition-colors flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Created Date
            <span v-if="selectedRange.label !== 'All Time'" class="ml-1 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full">
              {{ selectedRange.label }}
            </span>
          </button>

          <!-- Filter Button -->
          <button
            @click="showFilterModal = true"
            class="px-5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition-colors flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M3 8h18M3 12h18M3 16h18" />
            </svg>
            Filter
            <span v-if="activeFilters.length > 0" class="ml-1 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full">
              {{ activeFilters.length }}
            </span>
          </button>

          <!-- Sort Button -->
          <button
            @click="showSortModal = true"
            class="px-5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition-colors flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9M3 12h6M3 16h4" />
            </svg>
            Sort
            <span v-if="activeSorts.length > 0" class="ml-1 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full">
              {{ activeSorts.length }}
            </span>
          </button>

          <!-- New Deployment Button -->
          <router-link
            to="/deployments/new"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Deployment
          </router-link>
        </div>
      </div>

      <!-- Active Filters, Sorts & Date Range Tags -->
      <div v-if="activeFilters.length > 0 || activeSorts.length > 0 || selectedRange.label !== 'All Time'" class="px-6 py-4 flex flex-wrap gap-2 border-b border-slate-100">
        <!-- Date Range Tag -->
        <span v-if="selectedRange.label !== 'All Time'" class="inline-flex items-center px-3 py-1 bg-purple-50 text-purple-700 text-xs font-medium rounded-full">
          {{ selectedRange.label }}
          <button @click="clearDateRange" class="ml-2 text-purple-500 hover:text-purple-700">
            ×
          </button>
        </span>

        <!-- Active Filters -->
        <template v-for="(filter, index) in activeFilters" :key="'f-'+index">
          <span class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full">
            {{ filter.label }}
            <button @click="removeFilter(index)" class="ml-2 text-indigo-500 hover:text-indigo-700">
              ×
            </button>
          </span>
        </template>

        <!-- Active Sorts -->
        <template v-for="(sort, index) in activeSorts" :key="'s-'+index">
          <span class="inline-flex items-center px-3 py-1 bg-amber-50 text-amber-800 text-xs font-medium rounded-full">
            {{ sort.label }}
            <button @click="removeSort(index)" class="ml-2 text-amber-600 hover:text-amber-800">
              ×
            </button>
          </span>
        </template>

        <button @click="clearAll" class="text-sm text-slate-500 hover:text-slate-700 underline">
          Clear all
        </button>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
          <thead class="bg-slate-50/70">
            <tr>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                Network
              </th>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                Country
              </th>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                Callback URL
              </th>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                Format
              </th>
              <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                Created
              </th>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                Last Updated
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(deployment, index) in filteredDeployments" :key="index" class="hover:bg-slate-50 transition-colors cursor-pointer" @click="$router.push(`/deployments/${deployment.id}`)">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                {{ deployment.network }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2 text-sm text-slate-600">
                  <img
                    :src="`/svgs/country-flags/${deployment.country.toLowerCase()}.svg`"
                    alt=""
                    class="w-5 h-5 rounded-full object-cover border border-slate-200/70 shadow-sm"
                  />
                  <span>{{ deployment.country }}</span>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-slate-700 truncate max-w-xs">
                {{ deployment.callback_url }}
              </td>
              <td class="px-6 py-4 text-sm text-slate-700">
                {{ deployment.request_format }} / {{ deployment.response_format }}
              </td>
              <td class="px-6 py-4 text-center">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" class="sr-only peer" :checked="deployment.active" @click.stop="toggleDeployment(deployment)" />
                  <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                </label>
              </td>
              <td class="px-6 py-4 text-sm text-slate-500">
                {{ deployment.created_at }}
              </td>
              <td class="px-6 py-4 text-sm text-slate-500">
                {{ deployment.updated_at }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-sm">
        <div class="text-slate-600">
          Showing <strong>{{ (currentPage - 1) * itemsPerPage + 1 }}–{{ Math.min(currentPage * itemsPerPage, filteredDeployments.length) }}</strong> of <strong>{{ filteredDeployments.length }}</strong>
        </div>

        <div class="flex items-center gap-2">
          <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1.5 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
            Previous
          </button>
          <button v-for="page in pageNumbers" :key="page"
                  @click="currentPage = page"
                  :class="currentPage === page ? 'bg-indigo-600 text-white' : 'border border-slate-300 hover:bg-slate-50'"
                  class="px-4 py-1.5 rounded-lg font-medium transition-colors">
            {{ page }}
          </button>
          <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-3 py-1.5 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
            Next
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Date Range Modal -->
  <div v-if="showDateRangeModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
      <!-- ... similar to other modals ... -->
    </div>
  </div>

  <!-- Filter Modal -->
  <div v-if="showFilterModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
      <!-- ... similar ... -->
    </div>
  </div>

  <!-- Sort Modal -->
  <div v-if="showSortModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
      <!-- ... similar ... -->
    </div>
  </div>
</template>

<script>
export default {
  name: 'Deployments',
  data() {
    return {
      searchQuery: '',
      showDateRangeModal: false,
      showFilterModal: false,
      showSortModal: false,
      activeFilters: [],
      activeSorts: [],
      selectedRange: { value: 'all', label: 'All Time' },
      customStart: '',
      customEnd: '',
      currentPage: 1,
      itemsPerPage: 10,
      deployments: [ // Dummy data
        {
          id: 1,
          network: 'Orange',
          country: 'BW',
          callback_url: 'https://api.orange.bw/ussd/callback',
          request_format: 'XML',
          response_format: 'XML',
          active: true,
          created_at: '2024-01-01',
          updated_at: '2024-01-10'
        },
        // Add more dummy entries
      ]
    }
  },
  computed: {
    filteredDeployments() {
      // Apply filters, search, sort, date range
      return this.deployments // Placeholder
    },
    totalPages() {
      return Math.ceil(this.filteredDeployments.length / this.itemsPerPage)
    },
    pageNumbers() {
      // Simple pagination logic
      return Array.from({ length: this.totalPages }, (_, i) => i + 1)
    }
  },
  methods: {
    toggleDeployment(deployment) {
      deployment.active = !deployment.active
      // API call to update
    },
    // Other methods similar to Sessions.vue: handleSearch, applyPreset, etc.
  }
}
</script>
