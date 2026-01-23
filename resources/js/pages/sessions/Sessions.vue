<template>

    <div class="min-h-screen bg-slate-50 pb-12">

        <!-- Header + Controls -->
        <div class="bg-white border-b border-slate-200">

            <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Sessions</h1>
                    <p class="mt-1 text-sm text-slate-500">View and analyze all USSD sessions</p>
                </div>


                <div class="flex items-center gap-2 flex-wrap">

                    <!-- Search Bar -->
                    <Input
                        type="search"
                        :debounced="true"
                        class="min-w-87.5"
                        :showOutline="false"
                        v-model="searchQuery"
                        placeholder="Search by phone, network, status..." />

                    <!-- Date Range Button -->
                    <Button
                        size="md"
                        type="light"
                        mode="outline"
                        buttonClass="rounded-lg"
                        :action="showDateFilterModal">
                        <div class="flex items-center space-x-2">
                            <CalendarMinus2 size="16"></CalendarMinus2>
                            <span>Date Range</span>
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

            <div v-if="isLoadingSessionsSummary" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12 animate-pulse">
                <div v-for="i in 4" :key="i" class="bg-white rounded-2xl border border-slate-200 p-6 h-40 flex items-center justify-center">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600"></div>
                </div>
            </div>

            <div v-else-if="summary" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <!-- Total Sessions -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50/70 rounded-bl-full -mr-16 -mt-16"></div>
                <p class="text-sm text-slate-500 font-medium">Total Sessions</p>
                <p class="text-4xl font-bold text-slate-900 mt-2">{{ summary.total_sessions.toLocaleString() }}</p>
                <div class="mt-4 flex items-center gap-3">
                    <span class="text-xl font-bold text-emerald-600">{{ summary.success_rate }}%</span>
                    <span class="text-xs text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full font-medium">Success Rate</span>
                </div>
                </div>

                <!-- Failed Sessions -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-rose-50/70 rounded-bl-full -mr-16 -mt-16"></div>
                <p class="text-sm text-rose-700 font-medium">Failed Sessions</p>
                <p class="text-4xl font-bold text-rose-600 mt-2">{{ summary.failed_sessions.toLocaleString() }}</p>
                <div class="mt-4 flex items-center gap-3">
                    <span class="text-xl font-bold text-rose-600">{{ summary.fail_rate }}%</span>
                    <span class="text-xs text-rose-700 bg-rose-50 px-2.5 py-1 rounded-full font-medium">Fail Rate</span>
                </div>
                </div>

                <!-- Performance -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm text-slate-500 font-medium">Performance</p>
                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div>
                    <p class="text-2xl font-bold text-slate-900">{{ formatDuration(summary.avg_duration_ms) }}</p>
                    <p class="text-sm text-slate-500 mt-1">Avg Duration</p>
                    </div>
                    <div>
                    <p class="text-2xl font-bold text-slate-900">{{ summary.avg_steps.toFixed(1) }}</p>
                    <p class="text-sm text-slate-500 mt-1">Avg Steps</p>
                    </div>
                </div>
                </div>

                <!-- Device Breakdown -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm text-slate-500 font-medium mb-4">Device Breakdown</p>
                <div class="space-y-4">
                    <div>
                    <div class="flex justify-between text-sm mb-1.5">
                        <span class="text-slate-600">Mobile</span>
                        <span class="font-medium text-indigo-700">{{ summary.mobile_percentage }}%</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-600 rounded-full" :style="{ width: `${summary.mobile_percentage}%` }"></div>
                    </div>
                    </div>
                    <div>
                    <div class="flex justify-between text-sm mb-1.5">
                        <span class="text-slate-600">Simulator</span>
                        <span class="font-medium text-amber-700">{{ summary.simulator_percentage }}%</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-amber-500 rounded-full" :style="{ width: `${summary.simulator_percentage}%` }"></div>
                    </div>
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
                    <div v-if="isLoadingSessions && !pagination" class="p-12 text-center text-slate-500">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
                        <span class="text-sm">Loading sessions...</span>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="!sessions.length" class="p-12 text-center text-slate-500">
                        <Frown size="40" class="mx-auto mb-4"></Frown>
                        <p class="text-lg font-medium">No sessions found</p>
                        <p class="mt-2">Try adjusting your filters or search term</p>
                    </div>

                    <!-- Actual Table -->
                    <div v-else class="overflow-x-auto">

                        <table class="min-w-full divide-y divide-slate-200">

                            <thead class="bg-slate-50/70">

                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                        Network
                                    </th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                        Phone (MSISDN)
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
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                        Flags
                                    </th>
                                </tr>

                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">

                                <tr
                                    :key="session.id"
                                    v-for="session in sessions"
                                    @click.stop="() => navigateToSession(session.id)"
                                    class="hover:bg-slate-50 transition-colors cursor-pointer">

                                    <!-- Network + Country Flag -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div v-if="isLoadingSessions" class="flex items-center gap-3">
                                            <Skeleton width="w-6" height="h-6" class="shrink-0"></Skeleton>
                                            <Skeleton></Skeleton>
                                        </div>
                                        <div v-else class="flex items-center gap-3">
                                            <img
                                                v-if="session.country"
                                                :src="`/svgs/country-flags/${session.country.toLowerCase()}.svg`"
                                                class="w-6 h-6 rounded-full object-cover border border-slate-200 shadow-sm" />
                                            <span class="text-sm font-medium text-slate-900">{{ session.network || '—' }}</span>
                                        </div>
                                    </td>

                                    <!-- MSISDN -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                        <Skeleton v-if="isLoadingSessions"></Skeleton>
                                        <span v-else
                                            class="hover:underline hover:text-blue-600 transition-colors cursor-pointer"
                                            @click.stop="() => navigateToAccount(session.ussd_account_id)">{{ session.msisdn || '—' }}</span>
                                    </td>

                                    <!-- Duration -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                        <Skeleton v-if="isLoadingSessions"></Skeleton>
                                        <span v-else>{{ formatDuration(session.total_duration_ms) || '—' }}</span>
                                    </td>

                                    <!-- When -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        <Skeleton v-if="isLoadingSessions"></Skeleton>
                                        <span v-else>{{ formattedRelativeDate(session.created_at) }}</span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <Skeleton v-if="isLoadingSessions"></Skeleton>
                                        <span
                                            v-else
                                            :class="session.successful
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-rose-50 text-rose-700'"
                                            class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full">
                                            {{ session.successful ? 'Success' : 'Failed' }}
                                        </span>
                                    </td>

                                    <!-- Steps -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-slate-700">
                                        <Skeleton v-if="isLoadingSessions"></Skeleton>
                                        <span v-else>{{ session.total_steps || '—' }}</span>
                                    </td>

                                    <!-- Device -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <Skeleton v-if="isLoadingSessions"></Skeleton>
                                        <span
                                            v-else
                                            :class="session.simulated
                                            ? 'bg-amber-50 text-amber-700'
                                            : 'bg-indigo-50 text-indigo-700'"
                                            class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full">
                                            {{ session.simulated ? 'Simulator' : 'Mobile' }}
                                        </span>
                                    </td>

                                    <!-- Open Flags -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <Skeleton v-if="isLoadingSessions"></Skeleton>
                                        <span
                                            v-else
                                            :class="session.open_flags_count > 0
                                            ? 'bg-rose-50 text-rose-700'
                                            : 'text-slate-400'"
                                            class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full">
                                            {{ session.open_flags_count || 0 }}
                                        </span>
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="!isLoadingSessions && pagination?.meta?.total >= 5"
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

        <Modal
            size="md"
            :showFooter="false"
            ref="dateFilterModal"
            :scrollOnContent="false">

            <template #content>

                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Select Date Range</p>

                <!-- Presets -->
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

                        <!-- Start Date -->
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
                            v-model="tempFilters.successful"
                            v-if="filter.field === 'successful'" />

                        <!-- Flags -->
                        <Select
                            :search="false"
                            :clearable="true"
                            :label="filter.label"
                            placeholder="Any flags"
                            :options="filter.options"
                            v-model="tempFilters.has_flags"
                            v-if="filter.field === 'has_flags'" />

                        <!-- Status -->
                        <Select
                            :search="false"
                            :clearable="true"
                            :label="filter.label"
                            placeholder="Any device"
                            :options="filter.options"
                            v-model="tempFilters.simulated"
                            v-if="filter.field === 'simulated'" />

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
    import Switch from '@Partials/Switch.vue';
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
        name: 'Sessions',
        inject: ['appState', 'formState', 'notificationState'],
        components: {
            Frown, Check, Funnel, Input, Modal, Switch, Button, Select, Skeleton, Datepicker, SelectCountry, CalendarMinus2, ArrowDownWideNarrow
        },
        data() {
            return {
                sessions: [],
                summary: null,
                dateRange: null,
                searchQuery: '',
                pagination: null,
                isLoadingSessions: false,
                showDateRangeModal: false,
                isLoadingSessionsSummary: false,
                selectedDateFilterOption: { value: 'all', label: 'All Time' },
                dateFilterOptions: [
                    { value: 'today', label: 'Today' },
                    { value: 'this_week', label: 'This Week' },
                    { value: 'this_month', label: 'This Month' },
                    { value: 'this_year', label: 'This Year' },
                    { value: 'custom', label: 'Custom' },
                    { value: 'all', label: 'All Time' },
                ],
                tempFilters: {},
                filterNetworks: [],
                filterCountries: [],
                selectedFilterOptions: [],
                isLoadingSessionFilters: false,
                filterOptions: [
                    { field: 'country', label: 'Country' },
                    { field: 'network', label: 'Network' },
                    { field: 'successful', label: 'Status', options: [
                        { value: '1', label: 'Successful' },
                        { value: '0', label: 'Failed' },
                    ]  },
                    { field: 'has_flags', label: 'Flags', options: [
                        { value: '1', label: 'Flagged' },
                        { value: '0', label: 'Not Flagged' },
                    ]  },
                    { field: 'simulated', label: 'Device', options: [
                        { value: '0', label: 'Mobile' },
                        { value: '1', label: 'Simulator' },
                    ]  }
                ],
                selectedSortOptions: [],
                sortOptions: [
                    { label: 'Newest first', group: 'date' },
                    { label: 'Oldest first', group: 'date' },
                    { label: 'Longest duration', group: 'duration' },
                    { label: 'Shortest duration', group: 'duration' },
                    { label: 'Most steps', group: 'steps' },
                    { label: 'Fewest steps', group: 'steps' },
                    { label: 'Most Flags', group: 'status' },
                    { label: 'Fewest Flags', group: 'status' },
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
            }
        },
        methods: {
            isNotEmpty,
            formatDuration,
            formattedRelativeDate,
            async navigateToSession(sessionId) {
                await this.$router.push({
                    name: 'show-app-session',
                    params: {
                        app_id: this.app.id,
                        session_id: sessionId
                    }
                });
            },
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

                if(option.value == 'custom') {
                    if(this.dateRange == null) {
                        return;
                    }
                }else {
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
                // Find if any option from the same group is already selected
                const existingInGroupIndex = this.selectedSortOptions.findIndex(s => s.group === option.group);

                if (existingInGroupIndex !== -1) {
                    const existing = this.selectedSortOptions[existingInGroupIndex];

                    if (existing.label === option.label) {
                        // Clicking the same option → remove it
                        this.selectedSortOptions.splice(existingInGroupIndex, 1);
                    } else {
                        // Different option in same group → replace it
                        this.selectedSortOptions.splice(existingInGroupIndex, 1, { ...option });
                    }
                } else {
                    // No conflict → add it
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

                // Loop over every key in tempFilters
                Object.keys(this.tempFilters).forEach(field => {

                    const value = this.tempFilters[field];

                    // Skip if value is empty / meaningless
                    if (value === null || value === undefined || value === '' || value === false) {
                        return;
                    }

                    // Find the filter config for display label
                    const filterConfig = this.filterOptions.find(f => f.field === field);

                    if (!filterConfig) return;

                    // Build the filter entry
                    newFilters.push({
                        field,
                        value,
                        label: this.getFilterLabel(field, value, filterConfig),
                    });

                });

                this.selectedFilterOptions = newFilters;

                this.refreshData();
            },
            getFilterLabel(field, value, config) {

                if (field === 'country') {
                    const country = countries.find(c => c.iso.toUpperCase() === value?.toUpperCase());
                    return `Country ➜ ${country ? country.name : value}`;
                }

                if (field === 'network') {
                    return `Network ➜ ${value}`;
                }

                if (field === 'successful') {
                    return `Status ➜ ${value === '1' ? 'Successful' : 'Failed'}`;
                }

                if (field === 'has_flags') {
                    return `Flags ➜ ${value === '1' ? 'Yes' : 'No'}`;
                }

                if (field === 'simulated') {
                    return `Device ➜ ${value === '1' ? 'Simulator' : 'Mobile'}`;
                }

                return `${config?.label || field}: ${value}`;
            },
            clearFilters() {
                // Clear filters
                this.tempFilters = {};
                this.selectedFilterOptions = [];

                this.refreshData();
            },
            removeDateFilter() {
                // Reset date range
                this.selectedDateFilterOption = { value: 'all', label: 'All Time' };
                this.dateRange = null;

                this.refreshData();
            },
            removeFilter(filter) {
                const field = filter.field;

                // Remove from selectedFilterOptions
                this.selectedFilterOptions = this.selectedFilterOptions.filter(f => f.field !== field);

                // Clear the corresponding value in tempFilters
                delete this.tempFilters[field];

                this.refreshData();
            },
            removeSort(sort) {
                // Remove from selectedSortOptions
                this.selectedSortOptions = this.selectedSortOptions.filter(s => s.label !== sort.label);

                this.refreshData();
            },
            clearAll() {
                // Reset date range
                this.selectedDateFilterOption = { value: 'all', label: 'All Time' };
                this.dateRange = null;

                // Clear filters
                this.tempFilters = {};
                this.selectedFilterOptions = [];

                // Clear sorts
                this.selectedSortOptions = [];

                this.refreshData();
            },
            changePage(link) {
                this.showSessions(link.page);
            },
            refreshData() {
                this.showSessions();
                this.showSessionsSummary();
            },
            async showFilterOptions() {
                try {

                    this.isLoadingSessionFilters = true;

                    const config = {
                        params: {
                            app_id: this.app.id,
                            type: 'ussd sessions'
                        },
                    };

                    const response = await axios.get('/api/filters', config);

                    this.filterCountries = response.data.countries;

                    this.filterNetworks = (response.data.networks || []).map(net => ({
                        label: net,
                        value: net
                    }));

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching filters';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch filters:', error);
                } finally {
                    this.isLoadingSessionFilters = false;
                }
            },
            async showSessionsSummary(page = null) {
                try {

                    this.isLoadingSessionsSummary = true;

                    const response = await axios.get('/api/ussd-sessions/summary', this.getParams(page));

                    this.summary = response.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching summary';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch summary:', error);
                } finally {
                    this.isLoadingSessionsSummary = false;
                }
            },
            async showSessions(page = null) {
                try {

                    this.isLoadingSessions = true;

                    const response = await axios.get('/api/ussd-sessions', this.getParams(page));

                    this.pagination = response.data;
                    this.sessions = this.pagination.data || [];

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching sessions';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch sessions:', error);
                } finally {
                    this.isLoadingSessions = false;
                }
            },
            getParams(page) {

                let params = {
                    app_id: this.app?.id,
                };

                //  Search
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

                for (let index = 0; index < this.selectedFilterOptions.length; index++) {
                    const selectedFilterOption = this.selectedFilterOptions[index];
                    const field = selectedFilterOption.field;
                    const value = selectedFilterOption.value;

                    params[field] = value;
                }

                for (let index = 0; index < this.selectedSortOptions.length; index++) {
                    const selectedSortOption = this.selectedSortOptions[index];

                    const label = selectedSortOption.label;

                    //  Sort
                    if (label == 'Newest first') {
                        params._sort = `created_at:desc`;
                    }else if (label == 'Oldest first') {
                        params._sort = `created_at:asc`;
                    }else if (label == 'Longest duration') {
                        params._sort = `total_duration_ms:desc`;
                    }else if (label == 'Shortest duration') {
                        params._sort = `total_duration_ms:asc`;
                    }else if (label == 'Most steps') {
                        params._sort = `total_steps:desc`;
                    }else if (label == 'Fewest steps') {
                        params._sort = `total_steps:asc`;
                    }else if (label == 'Most Flags') {
                        params._sort = `open_flags_count:desc`;
                    }else if (label == 'Fewest Flags') {
                        params._sort = `open_flags_count:asc`;
                    }

                }

                //  Page
                if (isNotEmpty(page)) {
                    params.page = page;
                }

                return {
                    params: params
                };
            }
        },
        created() {
            this.showSessions();
            this.showSessionsSummary();
            this.showFilterOptions();
        }
    }

</script>
