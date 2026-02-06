<template>
  <div class="min-h-screen bg-slate-50 pb-12">

    <!-- Header + Controls -->
    <div class="bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Accounts</h1>
          <p class="mt-1 text-sm text-slate-500">View and manage unique mobile numbers</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">

          <!-- Search Bar -->
          <Input
            type="search"
            :debounced="true"
            class="min-w-87.5"
            :showOutline="false"
            v-model="searchQuery"
            placeholder="Search by phone, network..." />

          <!-- Date Range Button (First Seen) -->
          <Button
            size="md"
            type="light"
            mode="outline"
            buttonClass="rounded-lg"
            :action="showDateFilterModal">
            <div class="flex items-center space-x-2">
              <CalendarMinus2 size="16"></CalendarMinus2>
              <span>First Seen</span>
              <span v-if="selectedDateFilterOption.label !== 'All Time'" class="ml-1 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full">
                {{ selectedDateFilterOption.label }}
              </span>
            </div>
          </Button>

          <!-- Filter Button -->
          <Button
            size="md"
            type="light"
            mode="outline"
            buttonClass="rounded-lg"
            :action="showFilterModal">
            <div class="flex items-center space-x-2">
              <Funnel size="16"></Funnel>
              <span>Filter</span>
              <span
                v-if="selectedFilterOptions.length > 0"
                class="ml-1 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full">
                {{ selectedFilterOptions.length }}
              </span>
            </div>
          </Button>

          <!-- Sort Button -->
          <Button
            size="md"
            type="light"
            mode="outline"
            buttonClass="rounded-lg"
            :action="showSortModal">
            <div class="flex items-center space-x-2">
              <ArrowDownWideNarrow size="16"></ArrowDownWideNarrow>
              <span>Sort</span>
              <span
                v-if="selectedSortOptions.length > 0"
                class="ml-1 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full">
                {{ selectedSortOptions.length }}
              </span>
            </div>
          </Button>

        </div>

      </div>
    </div>

    <!-- High-Level Stats -->
    <div class="max-w-7xl mx-auto px-6 pt-8">

      <div v-if="isLoadingSummary" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12 animate-pulse">
        <div v-for="i in 4" :key="i" class="bg-white rounded-2xl border border-slate-200 p-6 h-40 flex items-center justify-center">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600"></div>
        </div>
      </div>

      <div v-else-if="summary" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">

        <!-- Total Accounts -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50/70 rounded-bl-full -mr-16 -mt-16"></div>
            <p class="text-sm text-slate-500 font-medium">Total Accounts</p>
            <p class="text-4xl font-bold text-slate-900 mt-2">{{ summary.total_accounts.toLocaleString() }}</p>
            <div class="mt-4 flex items-center gap-3">
                <span
                :class="[
                    'text-xl font-bold',
                    (summary.new_this_period >= 0 ? 'text-indigo-600' : 'text-rose-600')
                ]"
                >
                {{ summary.new_this_period_display || '+0' }}
                </span>
                <span class="text-xs text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full font-medium">
                {{ summary.period_label || 'This month' }}
                </span>
            </div>
        </div>

        <!-- Active Accounts -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50/70 rounded-bl-full -mr-16 -mt-16"></div>
          <p class="text-sm text-emerald-700 font-medium">Active Accounts</p>
          <p class="text-4xl font-bold text-emerald-600 mt-2">{{ summary.active_accounts.toLocaleString() }}</p>
          <div class="mt-4 flex items-center gap-3">
            <span class="text-xl font-bold text-emerald-600">{{ summary.active_percentage }}%</span>
            <span class="text-xs text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full font-medium">of total</span>
          </div>
        </div>

        <!-- Usage -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <p class="text-sm text-slate-500 font-medium">Usage</p>
          <div class="mt-4 grid grid-cols-2 gap-4">
            <div>
              <p class="text-2xl font-bold text-slate-900">{{ summary.avg_sessions.toFixed(1) }}</p>
              <p class="text-sm text-slate-500 mt-1">Avg Sessions</p>
            </div>
            <div>
              <p class="text-2xl font-bold text-slate-900">{{ formatDuration(summary.avg_session_duration_ms) }}</p>
              <p class="text-sm text-slate-500 mt-1">Avg Duration</p>
            </div>
          </div>
        </div>

        <!-- Network Breakdown -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <p class="text-sm text-slate-500 font-medium mb-4">Network Breakdown</p>

        <div class="space-y-4">
            <!-- Always show top 2 -->
            <div
                :key="network"
                v-for="(pct, network) in topNetworks">
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="text-slate-600">{{ network }}</span>
                    <span class="font-medium text-indigo-700">{{ pct }}%</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div
                    class="h-full bg-indigo-600 rounded-full transition-all duration-300"
                    :style="{ width: `${pct}%` }"
                    ></div>
                </div>
            </div>

            <!-- Hidden networks (shown when expanded) -->
            <template v-if="showAllNetworks">
                <div
                    :key="network"
                    v-for="(pct, network) in remainingNetworks">
                    <div class="flex justify-between text-sm mb-1.5">
                    <span class="text-slate-600">{{ network }}</span>
                    <span class="font-medium text-indigo-700">{{ pct }}%</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div
                        class="h-full bg-indigo-600 rounded-full transition-all duration-300"
                        :style="{ width: `${pct}%` }"
                    ></div>
                    </div>
                </div>
            </template>

            <!-- Show More / Show Less Button -->
            <div v-if="Object.keys(summary.network_breakdown || {}).length > 2" class="mt-3 text-center">
                <button
                    @click="showAllNetworks = !showAllNetworks"
                    class="text-xs font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mx-auto hover:scale-105 transition-all duration-300 cursor-pointer">
                    <span>{{ showAllNetworks ? 'Show less' : 'Show more' }}</span>
                    <svg
                    class="w-4 h-4 transition-transform"
                    :class="{ 'rotate-180': showAllNetworks }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
        </div>
        </div>

      </div>

      <!-- Active Filters, Sorts & Date Range Tags -->
      <div v-if="selectedDateFilterOption.label !== 'All Time' || selectedFilterOptions.length > 0 || selectedSortOptions.length > 0" class="mb-6 flex flex-wrap gap-2">

            <!-- Date Range Tag -->
            <span
            v-if="selectedDateFilterOption.label !== 'All Time'"
            class="inline-flex items-center px-3 py-1 bg-purple-50 text-purple-700 text-xs font-medium rounded-full"
            >
            <!-- Show preset label or formatted custom range -->
            <template v-if="selectedDateFilterOption.value === 'custom' && dateRange">
                {{ formatCustomDateRange(dateRange) }}
            </template>
            <template v-else>
                {{ selectedDateFilterOption.label }}
            </template>

            <button
                @click="removeDateFilter"
                class="ml-2 text-purple-500 hover:text-purple-700 font-bold hover:scale-125 transition-all duration-300 cursor-pointer"
            >×</button>
            </span>

        <!-- Active Filters -->
        <span
          :key="filter.field"
          v-for="filter in selectedFilterOptions"
          class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full">
          {{ filter.label }}
          <button
            @click="() => removeFilter(filter)"
            class="ml-2 text-indigo-500 hover:text-indigo-700 font-bold hover:scale-125 transition-all duration-300 cursor-pointer">×
          </button>
        </span>

        <!-- Active Sorts -->
        <span
          :key="sort.value"
          v-for="sort in selectedSortOptions"
          class="inline-flex items-center px-3 py-1 bg-amber-50 text-amber-800 text-xs font-medium rounded-full">
          {{ sort.label }}
          <button
            @click="() => removeSort(sort)"
            class="ml-2 text-amber-600 hover:text-amber-800 font-bold hover:scale-125 transition-all duration-300 cursor-pointer">×
          </button>
        </span>

        <!-- Clear All -->
        <button
          @click="clearAll"
          class="text-sm text-slate-500 hover:text-slate-700 underline font-medium ml-2 hover:scale-105 transition-all duration-300 cursor-pointer">
          Clear all
        </button>

      </div>

      <!-- Table -->
      <div class="max-w-7xl mx-auto">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">

          <!-- Table Header with Result Count & Pagination Info -->
          <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-sm">

            <div class="text-slate-600">
              Showing
              <strong>{{ pagination?.meta?.from || 0 }}–{{ pagination?.meta?.to || 0 }}</strong>
              of
              <strong>{{ pagination?.meta?.total?.toLocaleString() || 0 }}</strong>
            </div>

            <!-- Pagination buttons -->
            <div class="flex items-center gap-2">

              <template
                :key="index"
                v-for="(link, index) in pagination?.meta?.links">

                <button
                  :disabled="link.page == null"
                  @click="changePage(link)"
                  :class="[
                    'px-3 py-1.5 rounded-lg border transition-colors',
                    { 'border-slate-300 text-slate-600 hover:bg-slate-100 cursor-pointer' : link.page != null && !link.active },
                    { 'border-indigo-600 bg-indigo-600 text-white font-medium hover:bg-indigo-700 cursor-pointer' : link.page != null && link.active },
                    { 'border-slate-300 text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed' : link.page == null }
                  ]">
                  <template v-if="index == 0">
                    Previous
                  </template>
                  <template v-else-if="(pagination.meta.links.length - 1) == index">
                    Next
                  </template>
                  <template v-else>
                    {{ link.label }}
                  </template>
                </button>

              </template>

            </div>

          </div>

          <!-- Loading State -->
          <div v-if="isLoadingAccounts && !pagination" class="p-12 text-center text-slate-500">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
            <span class="text-sm">Loading accounts...</span>
          </div>

          <!-- Empty State -->
          <div v-else-if="!accounts.length" class="p-12 text-center text-slate-500">
            <Frown size="40" class="mx-auto mb-4"></Frown>
            <p class="text-lg font-medium">No accounts found</p>
            <p class="mt-2">Try adjusting your filters or search term</p>
          </div>

          <!-- Actual Table -->
          <div v-else class="overflow-x-auto">

            <table class="min-w-full divide-y divide-slate-200">

              <thead class="bg-slate-50/70">

                <tr>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    Mobile Number
                  </th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    Network
                  </th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    First Seen
                  </th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    Last Activity
                  </th>
                  <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    Total Sessions
                  </th>
                  <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    Success Rate
                  </th>
                  <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    Status
                  </th>
                </tr>

              </thead>

              <tbody class="divide-y divide-slate-100 bg-white">

                <tr
                  :key="account.id"
                  v-for="account in accounts"
                  @click.stop="() => navigateToAccount(account.id)"
                  class="hover:bg-slate-50 transition-colors cursor-pointer">

                  <!-- MSISDN -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                    <Skeleton v-if="isLoadingAccounts"></Skeleton>
                    <span v-else>{{ account.msisdn || '—' }}</span>
                  </td>

                  <!-- Network + Country Flag -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div v-if="isLoadingAccounts" class="flex items-center gap-3">
                      <Skeleton width="w-6" height="h-6" class="shrink-0"></Skeleton>
                      <Skeleton></Skeleton>
                    </div>
                    <div v-else class="flex items-center gap-3">
                      <img
                        v-if="account.country"
                        :src="`/svgs/country-flags/${account.country.toLowerCase()}.svg`"
                        class="w-6 h-6 rounded-full object-cover border border-slate-200 shadow-sm" />
                      <span class="text-sm font-medium text-slate-900">{{ account.network || '—' }}</span>
                    </div>
                  </td>

                  <!-- First Seen -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                    <Skeleton v-if="isLoadingAccounts"></Skeleton>
                    <span v-else>{{ formattedRelativeDate(account.created_at) || '—' }}</span>
                  </td>

                  <!-- Last Activity -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                    <Skeleton v-if="isLoadingAccounts"></Skeleton>
                    <span v-else>{{ formattedRelativeDate(account.last_activity_at) || '—' }}</span>
                  </td>

                  <!-- Total Sessions -->
                  <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-slate-700">
                    <Skeleton v-if="isLoadingAccounts"></Skeleton>
                    <span v-else>{{ account.total_sessions || 0 }}</span>
                  </td>

                    <!-- Success Rate -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                    <Skeleton v-if="isLoadingAccounts"></Skeleton>

                    <span
                        v-else
                        :class="[
                        account.success_rate >= 90
                            ? 'bg-emerald-50 text-emerald-700'
                            : account.success_rate >= 80
                            ? 'bg-amber-50 text-amber-700'
                            : 'bg-rose-50 text-rose-700',
                        'inline-flex px-2.5 py-1 text-xs font-medium rounded-full'
                        ]">
                        {{ account.success_rate + '%' }}
                    </span>
                    </td>

                  <!-- Status -->
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <Skeleton v-if="isLoadingAccounts"></Skeleton>
                    <span
                        v-else
                        :class="[
                            'inline-flex px-2.5 py-1 text-xs font-medium rounded-full',
                            { 'bg-rose-50 text-rose-700' : account.status == 'Blocked' },
                            { 'bg-amber-50 text-amber-700' : account.status == 'Inactive' },
                            { 'bg-emerald-50 text-emerald-700' : account.status == 'Active' },
                        ]">
                        {{ account.status }}
                    </span>
                  </td>

                </tr>

              </tbody>

            </table>

          </div>

          <!-- Pagination (footer) -->
          <div
                v-if="!isLoadingAccounts && pagination?.meta?.total >= 5"
                class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-sm">

            <div class="text-slate-600">
              Showing
              <strong>{{ pagination?.meta?.from || 0 }}–{{ pagination?.meta?.to || 0 }}</strong>
              of
              <strong>{{ pagination?.meta?.total?.toLocaleString() || 0 }}</strong>
            </div>

            <div class="flex items-center gap-2">

              <template
                :key="index"
                v-for="(link, index) in pagination?.meta?.links">

                <button
                  :disabled="link.page == null"
                  @click="changePage(link)"
                  :class="[
                    'px-3 py-1.5 rounded-lg border transition-colors',
                    { 'border-slate-300 text-slate-600 hover:bg-slate-100 cursor-pointer' : link.page != null && !link.active },
                    { 'border-indigo-600 bg-indigo-600 text-white font-medium hover:bg-indigo-700 cursor-pointer' : link.page != null && link.active },
                    { 'border-slate-300 text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed' : link.page == null }
                  ]">
                  <template v-if="index == 0">
                    Previous
                  </template>
                  <template v-else-if="(pagination.meta.links.length - 1) == index">
                    Next
                  </template>
                  <template v-else>
                    {{ link.label }}
                  </template>
                </button>

              </template>

            </div>

          </div>

        </div>

      </div>

    </div>

    <!-- Date Filter Modal -->
    <Modal
      size="md"
      :showFooter="false"
      ref="dateFilterModal"
      :scrollOnContent="false">

      <template #content>

        <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Select First Seen Range</p>

        <div class="grid grid-cols-2 gap-3 mb-8">

          <div
            :key="option.value"
            v-for="option in dateFilterOptions">

            <Button
              size="md"
              loaderType="primary"
              buttonClass="rounded-lg w-full"
              :action="() => applyDateFilter(option)"
              :type="selectedDateFilterOption.value === option.value ? 'primary' : 'light'"
              :mode="selectedDateFilterOption.value === option.value ? 'solid' : 'outline'">
              {{ option.label }}
            </Button>

          </div>

          <div
            class="col-span-2 space-y-4"
            v-if="selectedDateFilterOption.value === 'custom'">

            <Datepicker
              :range="true"
              class="w-full"
              v-model="dateRange"
              :showOutline="false"
              placeholder="Select Date Range"
              @change="() => applyDateFilter({ value: 'custom', label: 'Custom' })" />

          </div>

        </div>

      </template>

    </Modal>

    <!-- Filter Modal -->
    <Modal
      size="md"
      ref="filterModal"
      :showFooter="false"
      :scrollOnContent="false">

      <template #content>

        <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Add Filters</p>

        <div class="space-y-4 mb-8">

          <div v-for="filter in filterOptions" :key="filter.field">

            <!-- Country -->
            <SelectCountry
              :search="false"
              :clearable="true"
              :label="filter.label"
              placeholder="Any country"
              v-model="tempFilters.country"
              v-if="filter.field === 'country'"
              :allowedCountries="filterCountries" />

            <!-- Network -->
            <Select
              :search="false"
              :clearable="true"
              :label="filter.label"
              :options="filterNetworks"
              placeholder="Any network"
              v-model="tempFilters.network"
              v-if="filter.field === 'network'" />

            <!-- Status -->
            <Select
              :search="false"
              :clearable="true"
              :label="filter.label"
              :options="filter.options"
              placeholder="Any status"
              v-model="tempFilters.status"
              v-if="filter.field === 'status'" />

          </div>

        </div>

        <div
          class="w-full flex items-center space-x-4 px-4"
          v-if="selectedFilterOptions.length">

          <Button
            size="md"
            type="light"
            mode="outline"
            class="w-full"
            :action="clearFilters"
            buttonClass="w-full rounded-lg">
            Clear All Filters
          </Button>

          <Button
            size="md"
            type="primary"
            mode="outline"
            class="w-full"
            :action="hideFilterModal"
            buttonClass="w-full rounded-lg">
            Done
          </Button>

        </div>

      </template>

    </Modal>

    <!-- Sort Modal -->
    <Modal
      size="md"
      ref="sortModal"
      :showFooter="false"
      :scrollOnContent="false">

      <template #content>

        <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Sort By</p>

        <div class="space-y-1 mb-8">

          <div
            :key="option.value"
            @click="toggleSort(option)"
            v-for="option in sortOptions"
            :class="[
              'px-4 py-3 rounded-lg transition-all cursor-pointer flex items-center justify-between',
              isSortActive(option) ? 'bg-indigo-50 font-medium' : 'hover:bg-gray-50'
            ]">
            <span>{{ option.label }}</span>
            <Check v-if="isSortActive(option)" size="20" class="text-indigo-600">✓</Check>
          </div>

        </div>

        <div
          class="w-full flex items-center space-x-4 px-4"
          v-if="selectedSortOptions.length">

          <Button
            size="md"
            type="light"
            mode="outline"
            class="w-full"
            :action="clearSorts"
            buttonClass="w-full rounded-lg">
            Clear Sort
          </Button>

          <Button
            size="md"
            type="primary"
            mode="outline"
            class="w-full"
            :action="hideSortModal"
            buttonClass="w-full rounded-lg">
            Done
          </Button>

        </div>

      </template>

    </Modal>

  </div>
</template>

<script>

import dayjs from 'dayjs';
import Input from '@Partials/Input.vue';
import Modal from '@Partials/Modal.vue';
import Button from '@Partials/Button.vue';
import Select from '@Partials/Select.vue';
import countries from '@Json/countries.json';
import Skeleton from '@Partials/Skeleton.vue';
import Datepicker from '@Partials/Datepicker.vue';
import SelectCountry from '@Partials/SelectCountry.vue';
import { formattedRelativeDate } from '@Utils/dateUtils';
import { isNotEmpty, formatDuration } from '@Utils/stringUtils';
import { Frown, Check, Funnel, CalendarMinus2, ArrowDownWideNarrow } from 'lucide-vue-next';

export default {
  name: 'Accounts',
  inject: ['appState', 'formState', 'notificationState'],
  components: {
    Frown, Check, Funnel, Input, Modal, Button, Select, Skeleton, Datepicker, SelectCountry, CalendarMinus2, ArrowDownWideNarrow
  },
  data() {
    return {
      accounts: [],
      summary: null,
      dateRange: null,
      searchQuery: '',
      pagination: null,
      showAllNetworks: false,
      isLoadingSummary: false,
      isLoadingAccounts: false,
      selectedDateFilterOption: { value: 'all', label: 'All Time' },
      dateFilterOptions: [
        { value: 'today',     label: 'Today' },
        { value: 'this_week', label: 'This Week' },
        { value: 'this_month',label: 'This Month' },
        { value: 'this_year', label: 'This Year' },
        { value: 'custom',    label: 'Custom' },
        { value: 'all',       label: 'All Time' },
      ],
      tempFilters: {},
      filterNetworks: [],
      filterCountries: [],
      selectedFilterOptions: [],
      selectedSortOptions: [],
      filterOptions: [
        { field: 'country', label: 'Country' },
        { field: 'network', label: 'Network' },
        { field: 'status',  label: 'Status', options: [
          { value: 'active',   label: 'Active' },
          { value: 'inactive', label: 'Inactive' },
          { value: 'blocked', label: 'Blocked' },
        ]},
      ],
      sortOptions: [
        { label: 'Newest first',      group: 'date' },
        { label: 'Oldest first',      group: 'date' },
        { label: 'Most recent activity',   group: 'activity' },
        { label: 'Least recent activity',  group: 'activity' },
        { label: 'Most sessions',     group: 'sessions' },
        { label: 'Fewest sessions',   group: 'sessions' },
        { label: 'Best success rate', group: 'success' },
        { label: 'Worst success rate',group: 'success' },
      ],
    }
  },
  watch: {
    searchQuery() {
      this.refreshData();
    },
    tempFilters: {
      handler() {
        this.applyFilter();
      },
      deep: true,
    },
  },
  computed: {
    app() {
      return this.appState.app;
    },
    topNetworks() {
        if (!this.summary?.network_breakdown) return {};

        return Object.fromEntries(
        Object.entries(this.summary.network_breakdown)
            .sort(([, a], [, b]) => b - a) // descending order
            .slice(0, 2)
        );
    },
    remainingNetworks() {
        if (!this.summary?.network_breakdown) return {};

        const topKeys = Object.keys(this.topNetworks);
        return Object.fromEntries(
            Object.entries(this.summary.network_breakdown)
                .filter(([key]) => !topKeys.includes(key))
        );
    },
  },
  methods: {
    isNotEmpty,
    formatDuration,
    formattedRelativeDate,
    async navigateToAccount(accountId) {
        await this.$router.push({
            name: 'show-app-account',
            params: {
                app_id: this.app.id,
                account_id: accountId
            }
        });
    },
    showDateFilterModal() {
      this.$refs.dateFilterModal.showModal();
    },
    hideDateFilterModal() {
      this.$refs.dateFilterModal.hideModal();
    },
    applyDateFilter(option) {
      this.selectedDateFilterOption = { ...option };

      if (option.value === 'custom') {
        if (!this.dateRange) return;
      } else {
        this.dateRange = null;
      }

      this.hideDateFilterModal();
      this.refreshData();
    },
    formatCustomDateRange(range) {
        if (!range || range.length !== 2) return 'Custom';

        const [start, end] = range;

        // Use dayjs for clean formatting
        const startFormatted = dayjs(start).format('MMM D, YYYY');
        const endFormatted   = dayjs(end).format('MMM D, YYYY');

        // If same day, show just one date
        if (dayjs(start).isSame(end, 'day')) {
        return startFormatted;
        }

        // If same month/year, show "Jan 10 – 15, 2026"
        if (dayjs(start).isSame(end, 'month') && dayjs(start).isSame(end, 'year')) {
        return `${dayjs(start).format('MMM D')} – ${dayjs(end).format('D, YYYY')}`;
        }

        // Full range: "Jan 10 – Feb 5, 2026"
        return `${startFormatted} – ${endFormatted}`;
    },
    showSortModal() {
      this.$refs.sortModal.showModal();
    },
    hideSortModal() {
      this.$refs.sortModal.hideModal();
    },
    isSortActive(option) {
      return this.selectedSortOptions.some(s => s.label === option.label);
    },
    toggleSort(option) {
      const existingInGroupIndex = this.selectedSortOptions.findIndex(s => s.group === option.group);

      if (existingInGroupIndex !== -1) {
        const existing = this.selectedSortOptions[existingInGroupIndex];
        if (existing.label === option.label) {
          this.selectedSortOptions.splice(existingInGroupIndex, 1);
        } else {
          this.selectedSortOptions.splice(existingInGroupIndex, 1, { ...option });
        }
      } else {
        this.selectedSortOptions.push({ ...option });
      }

      this.refreshData();
    },
    clearSorts() {
      this.selectedSortOptions = [];
      this.hideSortModal();
      this.refreshData();
    },
    showFilterModal() {
      if (this.filterCountries.length === 0 || this.filterNetworks.length === 0) {
        this.showFilterOptions();
      }
      this.$refs.filterModal.showModal();
    },
    hideFilterModal() {
      this.$refs.filterModal.hideModal();
    },
    applyFilter() {
      const newFilters = [];

      Object.keys(this.tempFilters).forEach(field => {
        const value = this.tempFilters[field];

        if (value === null || value === undefined || value === '' || value === false) return;

        const filterConfig = this.filterOptions.find(f => f.field === field);
        if (!filterConfig) return;

        newFilters.push({
          field,
          value,
          label: this.getFilterLabel(field, value, filterConfig),
        });
      });

      this.selectedFilterOptions = newFilters;
      this.refreshData();
    },
    getFilterLabel(field, value, filterConfig) {
      if (field === 'country') {
        const country = countries.find(c => c.iso.toUpperCase() === value?.toUpperCase());
        return `Country ➜ ${country ? country.name : value}`;
      }
      if (field === 'network') {
        return `Network ➜ ${value}`;
      }
      if (field === 'status') {
        const valueLabel = filterConfig.options.find(o => o.value === value).label;
        return `Status ➜ ${valueLabel}`;
      }
      return `${filterConfig?.label || field}: ${value}`;
    },
    clearFilters() {
      this.tempFilters = {};
      this.selectedFilterOptions = [];
      this.refreshData();
    },
    removeDateFilter() {
      this.selectedDateFilterOption = { value: 'all', label: 'All Time' };
      this.dateRange = null;
      this.refreshData();
    },
    removeFilter(filter) {
      const field = filter.field;
      this.selectedFilterOptions = this.selectedFilterOptions.filter(f => f.field !== field);
      delete this.tempFilters[field];
      this.refreshData();
    },
    removeSort(sort) {
        this.selectedSortOptions = this.selectedSortOptions.filter(s => s.label !== sort.label);
        this.refreshData();
    },
    clearAll() {
      this.selectedDateFilterOption = { value: 'all', label: 'All Time' };
      this.dateRange = null;
      this.tempFilters = {};
      this.selectedFilterOptions = [];
      this.selectedSortOptions = [];
      this.refreshData();
    },
    changePage(link) {
      this.showAccounts(link.page);
    },
    refreshData() {
      this.showAccounts();
      this.showAccountsSummary();
    },
    async showFilterOptions() {
      try {
        const config = {
          params: {
            app_id: this.app.id,
            type: 'ussd accounts'
          },
        };

        const response = await axios.get('/api/filters', config);

        this.filterCountries = response.data.countries;
        this.filterNetworks = (response.data.networks || []).map(net => ({
          label: net,
          value: net
        }));

      } catch (error) {
        const message = error?.response?.data?.message || 'Failed to load filter options';
        this.notificationState.showWarningNotification(message);
        console.error('Failed to fetch filter options:', error);
      }
    },
    async showAccountsSummary() {
      try {
        this.isLoadingSummary = true;

        const response = await axios.get(`/api/apps/${this.app?.id}/ussd-accounts/summary`, this.getParams());

        this.summary = response.data;

      } catch (error) {
        const message = error?.response?.data?.message || 'Failed to load summary';
        this.notificationState.showWarningNotification(message);
        console.error('Summary fetch error:', error);
      } finally {
        this.isLoadingSummary = false;
      }
    },
    async showAccounts(page = null) {
      try {
        this.isLoadingAccounts = true;

        const response = await axios.get(`/api/apps/${this.app?.id}/ussd-accounts`, this.getParams(page));

        this.pagination = response.data;
        this.accounts = this.pagination.data || [];

      } catch (error) {
        const message = error?.response?.data?.message || 'Failed to load accounts';
        this.notificationState.showWarningNotification(message);
        console.error('Accounts fetch error:', error);
      } finally {
        this.isLoadingAccounts = false;
      }
    },
    getParams(page = null) {
      let params = {};

      if (this.isNotEmpty(this.searchQuery)) {
        params.search = this.searchQuery.trim();
      }

      // Date filtering
      if (this.selectedDateFilterOption.value !== 'all') {

        params.date_range = this.selectedDateFilterOption.value;

        if(this.selectedDateFilterOption.value == 'custom' && this.dateRange) {

            if(this.dateRange) {
                params.date_range_end = this.dateRange[1];
                params.date_range_start = this.dateRange[0];
            }

        }

      }

      // Regular filters
      this.selectedFilterOptions.forEach(f => {
        params[f.field] = f.value;
      });

      // Sorting
      this.selectedSortOptions.forEach(s => {
        if (s.label === 'Newest first')        params._sort = 'created_at:desc';
        else if (s.label === 'Oldest first')   params._sort = 'created_at:asc';
        else if (s.label === 'Most recent activity')  params._sort = 'updated_at:desc';
        else if (s.label === 'Least recent activity')params._sort = 'updated_at:asc';
        else if (s.label === 'Most sessions')  params._sort = 'total_sessions:desc';
        else if (s.label === 'Fewest sessions')params._sort = 'total_sessions:asc';
        else if (s.label === 'Best success rate')  params._sort = 'success_rate:desc';
        else if (s.label === 'Worst success rate') params._sort = 'success_rate:asc';
      });

      if (page) params.page = page;

      return { params };
    }
  },
  created() {
    this.showAccounts();
    this.showAccountsSummary();
    this.showFilterOptions();
  }
}
</script>
