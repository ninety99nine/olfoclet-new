<template>

    <div class="min-h-screen bg-slate-50 pb-12">

        <!-- Header + Controls -->
        <div class="bg-white border-b border-slate-200">

            <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Issues & Flags</h1>
                    <p class="mt-1 text-sm text-slate-500">View and analyze all flagged issues</p>
                </div>

                <div class="flex items-center gap-2 flex-wrap">

                    <!-- Search Bar -->
                    <Input
                        type="search"
                        :debounced="true"
                        class="min-w-87.5"
                        :showOutline="false"
                        v-model="searchQuery"
                        placeholder="Search by session ID..." />

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
        <div class="max-w-7xl mx-auto px-6 py-4">

            <div v-if="isLoadingSummary" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 animate-pulse">
                <div v-for="i in 4" :key="i" class="bg-white rounded-2xl border border-slate-200 p-6 h-40 flex items-center justify-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600"></div>
                </div>
            </div>

            <div v-else-if="summary" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Critical Open -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-rose-50/70 rounded-bl-full -mr-16 -mt-16"></div>
                    <p class="text-sm text-rose-700 font-medium">Critical Open</p>
                    <p class="text-4xl font-bold text-rose-600 mt-2">{{ summary.total_critical_open }}</p>
                </div>

                <!-- High Open -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-orange-50/70 rounded-bl-full -mr-16 -mt-16"></div>
                    <p class="text-sm text-orange-700 font-medium">High Open</p>
                    <p class="text-4xl font-bold text-orange-600 mt-2">{{ summary.total_high_open }}</p>
                </div>

                <!-- Total Open -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <p class="text-sm text-slate-500 font-medium">Total Open</p>
                    <p class="text-4xl font-bold text-slate-900 mt-2">{{ summary.total_open }}</p>
                    <p class="text-sm text-slate-500 mt-1">
                        from {{ summary.total_unique_sessions_with_open_flags }} session{{ summary.total_unique_sessions_with_open_flags !== 1 ? 's' : '' }}
                    </p>
                </div>

                <!-- Resolved -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <p class="text-sm text-slate-500 font-medium">Resolved</p>
                    <p class="text-4xl font-bold text-slate-900 mt-2">{{ summary.total_resolved }}</p>
                    <p class="text-sm text-slate-500 mt-1">
                        from {{ summary.total_unique_sessions_with_resolved_flags }} session{{ summary.total_unique_sessions_with_resolved_flags !== 1 ? 's' : '' }}
                    </p>
                </div>

            </div>

        </div>

        <!-- Active Filters, Sorts & Date Range Tags -->
        <div v-if="selectedDateFilterOption.label !== 'All Time' || selectedFilterOptions.length > 0 || selectedSortOptions.length > 0" class="max-w-7xl mx-auto px-6 pt-4 pb-8 flex flex-wrap gap-2">

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

        <div class="max-w-7xl mx-auto px-6 grid grid-cols-12 gap-4">

            <div class="col-span-9 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">

                <!-- Table Header, Result Count & Pagination -->
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-sm">

                    <div class="text-slate-600">
                        Showing
                        <strong>{{ pagination?.meta?.from || 0 }}–{{ pagination?.meta?.to || 0 }}</strong>
                        of
                        <strong>{{ pagination?.meta?.total || 0 }}</strong>
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
                <div v-if="isLoadingFlags && !pagination" class="p-12 text-center text-slate-500">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
                    <span class="text-sm">Loading issues...</span>
                </div>

                <!-- Empty State -->
                <div v-else-if="!flags.length" class="p-12 text-center text-slate-500">
                    <Frown size="40" class="mx-auto mb-4"></Frown>
                    <p class="text-lg font-medium">No issues found</p>
                    <p class="mt-2">Try adjusting your filters or search term</p>
                </div>

                <!-- Session List -->
                <div v-else class="divide-y divide-slate-100">

                    <div
                        :key="flag.id"
                        v-for="flag in flags"
                        @click.stop="() => navigateToSession(flag.session.id)"
                        class="px-6 py-5 hover:bg-slate-50/70 transition-colors cursor-pointer group relative">

                        <div class="flex items-start gap-4">

                            <!-- Priority Badge -->
                            <div class="mt-1">
                                <span
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-base font-bold shadow-sm"
                                    :class="{
                                        'bg-rose-100 text-rose-700 ring-1 ring-rose-200': flag.priority === 'critical',
                                        'bg-orange-100 text-orange-700 ring-1 ring-orange-200': flag.priority === 'high',
                                        'bg-amber-100 text-amber-700 ring-1 ring-amber-200': flag.priority === 'medium',
                                        'bg-sky-100 text-sky-700 ring-1 ring-sky-200': flag.priority === 'low'
                                    }">
                                    {{ flag.priority.charAt(0).toUpperCase() }}
                                </span>
                            </div>

                            <div class="grow flex items-start gap-4">

                                <!-- Category, Status, Comment -->
                                <div class="grow">

                                    <div class="grow flex-1 min-w-0">

                                        <!-- Category + Status -->
                                        <div class="flex items-center justify-between mb-1.5">

                                            <!-- Category -->
                                            <h3 class="font-semibold text-slate-900 text-base leading-tight">
                                                {{ getCategoryLabel(flag.category) }}
                                            </h3>

                                            <!-- Status -->
                                            <span
                                                :class="[
                                                    'inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full',
                                                    flag.resolved
                                                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                                        : 'bg-rose-50 text-rose-700 border border-rose-200'
                                                ]">
                                                {{ flag.resolved ? 'Resolved' : 'Open' }}
                                            </span>

                                        </div>

                                        <!-- Comment -->
                                        <p class="text-sm text-slate-700 leading-relaxed line-clamp-2 mb-2">
                                            {{ flag.comment || 'No comment provided' }}
                                        </p>

                                        <!-- Metadata -->
                                        <div class="flex items-center gap-4 text-xs text-slate-500 opacity-75 group-hover:opacity-100 transition-opacity">
                                            <span>Flagged by {{ flag.created_by?.first_name || 'Team' }}</span>
                                            <span>•</span>
                                            <span>{{ formattedRelativeDate(flag.created_at) }}</span>
                                            <span v-if="flag.resolved" class="ml-auto">
                                                Resolved • {{ formattedRelativeDate(flag.resolved_at) }}
                                            </span>
                                        </div>

                                    </div>

                                    <!-- Resolved details – only if resolved, softer styling -->
                                    <div
                                        v-if="flag.resolved && flag.resolution_comment"
                                        class="mt-3 pt-3 border-t border-dashed border-slate-100 text-xs italic text-emerald-600">
                                        "{{ flag.resolution_comment }}"
                                    </div>

                                </div>

                                <!-- Quick actions -->
                                <div class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">

                                    <Dropdown position="left" dropdownClasses="w-44">

                                        <template #trigger="props">
                                            <button
                                                @click="props.toggleDropdown"
                                                class="p-1.5 rounded-lg hover:bg-slate-100">
                                                <EllipsisVertical size="16" class="text-slate-400" />
                                            </button>
                                        </template>

                                        <template #content="props">
                                            <div class="py-1">
                                                <div
                                                    :key="index"
                                                    v-for="(flagMenu, index) in flagMenus(flag)"
                                                    @click="(event) => { flagMenu.action(flag, () => props.toggleDropdown(event)); }"
                                                    class="px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 cursor-pointer">
                                                    {{ flagMenu.name }}
                                                </div>
                                            </div>
                                        </template>

                                    </Dropdown>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- Table Footer, Result Count & Pagination -->
                <div
                    v-if="!isLoadingFlags && pagination?.meta?.total >= 5"
                    class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-sm">

                    <div class="text-slate-600">
                        Showing
                        <strong>{{ pagination?.meta?.from || 0 }}–{{ pagination?.meta?.to || 0 }}</strong>
                        of
                        <strong>{{ pagination?.meta?.total || 0 }}</strong>
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

            </div>

            <div class="col-span-3 space-y-4">

                <!-- Top Priority -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-900 mb-4">Top Priority Now</h3>
                    <div v-if="isLoadingSummary || (summary && summary.most_urgent_open_flag.id)" class="space-y-4">
                        <div class="flex items-center gap-3">
                            <span :class="[
                                isLoadingSummary ? 'bg-slate-100 text-slate-300' : 'bg-rose-100 text-rose-700',
                                'shrink-0 w-8 h-8 rounded-full font-bold flex items-center justify-center text-sm'
                            ]">
                                {{ summary?.most_urgent_open_flag?.priority?.charAt(0)?.toUpperCase() || '?' }}
                            </span>
                            <div>
                                <Skeleton v-if="isLoadingSummary" width="w-20" class="shrink-0 mb-2"></Skeleton>
                                <p v-else class="text-sm font-medium text-slate-900">
                                    <template v-if="summary.most_urgent_open_flag.priority == 'critical'">Critical</template>
                                    <template v-else-if="summary.most_urgent_open_flag.priority == 'high'">High Priority</template>
                                    <template v-else-if="summary.most_urgent_open_flag.priority == 'medium'">Medium Priority</template>
                                    <template v-else-if="summary.most_urgent_open_flag.priority == 'low'">Low Priority</template>
                                </p>
                                <Skeleton v-if="isLoadingSummary" width="w-32" class="shrink-0"></Skeleton>
                                <p v-else class="text-xs text-slate-500">{{ capitalize(summary.most_urgent_open_flag.comment || summary.most_urgent_open_flag.category) }}</p>
                            </div>
                        </div>
                        <Button
                            size="md"
                            type="danger"
                            mode="solid"
                            class="w-full"
                            :skeleton="isLoadingSummary"
                            buttonClass="w-full rounded-lg"
                            :action="isLoadingSummary ? null : () => navigateToSession(summary.most_urgent_open_flag.ussd_session_id)">
                            Jump to this Issue
                        </Button>
                    </div>
                    <p v-else class="text-slate-900 mb-4">No critical issues</p>
                </div>

                <!-- Open Issue Breakdown -->
                <div class="bg-white rounded-2xl border border-slate-200 py-6 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-900 px-6 mb-4">Open Category</h3>
                    <dl
                        class="text-sm"
                        v-if="isLoadingSummary">

                        <div
                            :key="index"
                            v-for="(categoryBreakdown, index) in [1,2,3,4,5]"
                            class="flex items-center justify-between hover:bg-slate-100 transition-colors py-1.5 mx-3 px-3 rounded-lg">
                            <dt class="text-slate-600">
                                <Skeleton width="w-24" class="shrink-0"></Skeleton>
                            </dt>
                            <dd class="font-medium text-slate-900">
                                <Skeleton width="w-4" height="h-4" class="shrink-0"></Skeleton>
                            </dd>
                        </div>

                    </dl>
                    <dl v-else class="text-sm">

                        <div
                            :key="categoryKey"
                            @click="() => tempFilters.category = categoryKey"
                            v-for="(count, categoryKey) in summary.open_category_breakdown || {}"
                            class="flex justify-between hover:bg-slate-100 transition-colors cursor-pointer py-1.5 mx-3 px-3 rounded-lg">
                            <dt class="text-slate-600">
                                {{ capitalize(categoryKey) }}
                            </dt>
                            <dd class="font-medium text-slate-900">{{ count }}</dd>
                        </div>

                    </dl>

                </div>

                <!-- Resolved Issue Breakdown -->
                <div class="bg-white rounded-2xl border border-slate-200 py-6 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-900 px-6 mb-4">Resolved Category</h3>
                    <dl
                        class="text-sm"
                        v-if="isLoadingSummary">

                        <div
                            :key="index"
                            v-for="(categoryBreakdown, index) in [1,2,3,4,5]"
                            class="flex items-center justify-between hover:bg-slate-100 transition-colors py-1.5 mx-3 px-3 rounded-lg">
                            <dt class="text-slate-600">
                                <Skeleton width="w-24" class="shrink-0"></Skeleton>
                            </dt>
                            <dd class="font-medium text-slate-900">
                                <Skeleton width="w-4" height="h-4" class="shrink-0"></Skeleton>
                            </dd>
                        </div>

                    </dl>
                    <dl v-else class="text-sm">

                        <div
                            :key="categoryKey"
                            @click="() => tempFilters.category = categoryKey"
                            v-for="(count, categoryKey) in summary.resolved_category_breakdown || {}"
                            class="flex justify-between hover:bg-slate-100 transition-colors cursor-pointer py-1.5 mx-3 px-3 rounded-lg">
                            <dt class="text-slate-600">
                                {{ capitalize(categoryKey) }}
                            </dt>
                            <dd class="font-medium text-slate-900">{{ count }}</dd>
                        </div>

                    </dl>

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

                        <!-- Custom Range -->
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

                        <Select
                            :search="false"
                            :clearable="true"
                            placeholder="Any"
                            :label="filter.label"
                            :options="filter.options"
                            v-model="tempFilters[filter.field]"
                            v-if="filter.options" />

                    </div>

                    </div>

                    <div
                        v-if="selectedFilterOptions.length"
                        class="w-full flex items-center space-x-4 px-4">

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
                    v-if="selectedSortOptions.length"
                    class="w-full flex items-center space-x-4 px-4">

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

        <Modal
            size="md"
            :showFooter="false"
            ref="editableFlagModal"
            :scrollOnContent="false"
            :approveAction="updateFlag">

            <template #content>

                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">
                    Edit Flag
                </p>

                <!-- Original flag info (read-only) -->
                <div class="border-b border-gray-300 border-dashed pb-4 mb-6">
                <div class="flex items-center justify-between">
                    <p class="font-medium text-slate-900 truncate max-w-40">
                    {{ selectedFlag.created_by?.first_name || 'Team' }}
                    </p>
                    <span class="text-xs text-slate-500 whitespace-nowrap self-start sm:self-auto">
                    {{ formattedRelativeDate(selectedFlag.created_at) }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 whitespace-nowrap">
                    {{ selectedFlag.category }}
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap"
                        :class="{
                            'bg-sky-100 text-sky-800': selectedFlag.priority === 'low',
                            'bg-amber-100 text-amber-800': selectedFlag.priority === 'medium',
                            'bg-orange-100 text-orange-800': selectedFlag.priority === 'high',
                            'bg-rose-100 text-rose-800': selectedFlag.priority === 'critical'
                        }">
                    {{ selectedFlag.priority }}
                    </span>
                </div>

                <p v-if="selectedFlag.comment" class="italic text-sm text-slate-700 mt-4">
                    "{{ selectedFlag.comment }}"
                </p>
                </div>

                <!-- Editable fields -->
                <div class="space-y-6">

                <!-- Step (optional) -->
                <Select
                    label="Step"
                    :search="true"
                    :clearable="true"
                    secondaryLabel="(optional)"
                    :options="stepSelectOptions"
                    v-model="editForm.ussd_session_step_id"
                    placeholder="Select step this flag refers to"
                    :errorText="formState.getFormError('ussd_session_step_id')"
                />

                <!-- Category -->
                <Select
                    label="Category"
                    :clearable="false"
                    :showAsterisk="true"
                    placeholder="Select category"
                    :options="categoryOptions"
                    v-model="editForm.category"
                    :errorText="formState.getFormError('category')"
                />

                <!-- Priority -->
                <div class="mb-2">
                    <label class="flex items-center text-sm font-medium text-gray-900 space-x-1">
                    <span>Priority</span>
                    <span class="text-red-500">*</span>
                    </label>
                </div>
                <div class="grid grid-cols-4 gap-3 mb-6">
                    <div v-for="option in priorityOptions" :key="option.value">
                    <Button
                        size="md"
                        loaderType="primary"
                        buttonClass="rounded-lg w-full"
                        :action="() => editForm.priority = option.value"
                        :type="editForm.priority === option.value ? 'primary' : 'light'"
                        :mode="editForm.priority === option.value ? 'solid' : 'outline'">
                        {{ option.label }}
                    </Button>
                    </div>
                </div>

                <!-- Comment -->
                <Input
                    rows="4"
                    type="textarea"
                    label="Comment / Explanation"
                    secondaryLabel="(optional)"
                    v-model="editForm.comment"
                    :errorText="formState.getFormError('comment')"
                    placeholder="Update your explanation if needed..."
                />

                </div>

                <Button
                    size="md"
                    mode="solid"
                    type="primary"
                    class="mt-8 mx-4"
                    :action="updateFlag"
                    buttonClass="rounded-lg w-full"
                    :loading="updatingFlags.includes(selectedFlag.id)">
                    Save Changes
                </Button>

            </template>

        </Modal>

        <Modal
            size="md"
            :showFooter="false"
            :scrollOnContent="false"
            ref="resolvableFlagModal"
            :approveAction="resolveFlag">

            <template #content>

                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">
                    Resolve Flag
                </p>

                <div class="border-b border-gray-300 border-dashed pb-4">

                    <div>

                        <div class="flex items-center justify-between">

                            <p class="font-medium text-slate-900 truncate max-w-40">
                                {{ selectedFlag.created_by?.first_name || 'Team' }}
                            </p>

                            <span class="text-xs text-slate-500 whitespace-nowrap self-start sm:self-auto">
                                {{ formattedRelativeDate(selectedFlag.created_at) }}
                            </span>

                        </div>

                        <div class="flex flex-wrap items-center gap-1.5 mt-2">

                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 whitespace-nowrap">
                                {{ selectedFlag.category }}
                            </span>

                            <span class="text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap"
                                :class="{
                                    'bg-sky-100 text-sky-800': selectedFlag.priority === 'low',
                                    'bg-amber-100 text-amber-800': selectedFlag.priority === 'medium',
                                    'bg-orange-100 text-orange-800': selectedFlag.priority === 'high',
                                    'bg-rose-100 text-rose-800': selectedFlag.priority === 'critical'
                                }">
                                {{ selectedFlag.priority }}
                            </span>

                            <span v-if="selectedFlag.resolved" class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap">
                                Resolved
                            </span>

                        </div>

                    </div>

                    <!-- Comment -->
                    <p v-if="selectedFlag.comment" class="italic text-sm text-slate-700 leading-relaxed wrap-break-word mt-4">
                        {{ selectedFlag.comment }}
                    </p>

                </div>

                <Input
                    rows="4"
                    type="textarea"
                    label="Comment"
                    class="mt-8 mb-8"
                    secondaryLabel="(optional)"
                    v-model="resolutionForm.comment"
                    placeholder="Your resolution feedback..."
                    :errorText="formState.getFormError('comment')" />

                <Button
                    size="md"
                    mode="solid"
                    class="mx-4"
                    type="success"
                    :action="resolveFlag"
                    buttonClass="rounded-lg w-full"
                    :loading="resolvingFlags.includes(selectedFlag.id)">
                    Resolve
                </Button>

            </template>

        </Modal>

        <Modal
            size="md"
            :showFooter="false"
            :scrollOnContent="false"
            ref="unresolvableFlagModal"
            :approveAction="unresolveFlag">

            <template #content>

                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">
                    Unresolve Flag
                </p>

                <div class="border-b border-gray-300 border-dashed pb-4 mb-8">

                    <div>

                        <div class="flex items-center justify-between">

                            <p class="font-medium text-slate-900 truncate max-w-40">
                                {{ selectedFlag.created_by?.first_name || 'Team' }}
                            </p>

                            <span class="text-xs text-slate-500 whitespace-nowrap self-start sm:self-auto">
                                {{ formattedRelativeDate(selectedFlag.created_at) }}
                            </span>

                        </div>

                        <div class="flex flex-wrap items-center gap-1.5 mt-2">

                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 whitespace-nowrap">
                                {{ selectedFlag.category }}
                            </span>

                            <span class="text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap"
                                :class="{
                                    'bg-sky-100 text-sky-800': selectedFlag.priority === 'low',
                                    'bg-amber-100 text-amber-800': selectedFlag.priority === 'medium',
                                    'bg-orange-100 text-orange-800': selectedFlag.priority === 'high',
                                    'bg-rose-100 text-rose-800': selectedFlag.priority === 'critical'
                                }">
                                {{ selectedFlag.priority }}
                            </span>

                            <span v-if="selectedFlag.resolved" class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap">
                                Resolved
                            </span>

                        </div>

                    </div>

                    <!-- Comment -->
                    <p v-if="selectedFlag.comment" class="italic text-sm text-slate-700 leading-relaxed wrap-break-word mt-4">
                        {{ selectedFlag.comment }}
                    </p>

                </div>

                <Button
                    size="md"
                    mode="solid"
                    class="mx-4"
                    type="warning"
                    :action="unresolveFlag"
                    buttonClass="rounded-lg w-full"
                    :loading="unresolvingFlags.includes(selectedFlag.id)">
                    Unresolve
                </Button>

            </template>

        </Modal>

        <Modal
            size="md"
            :showFooter="false"
            :scrollOnContent="false"
            ref="deletableFlagModal"
            :approveAction="unresolveFlag">

            <template #content>

                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">
                    Delete Flag
                </p>

                <div class="border-b border-gray-300 border-dashed pb-4 mb-8">

                    <div>

                        <div class="flex items-center justify-between">

                            <p class="font-medium text-slate-900 truncate max-w-40">
                                {{ selectedFlag.created_by?.first_name || 'Team' }}
                            </p>

                            <span class="text-xs text-slate-500 whitespace-nowrap self-start sm:self-auto">
                                {{ formattedRelativeDate(selectedFlag.created_at) }}
                            </span>

                        </div>

                        <div class="flex flex-wrap items-center gap-1.5 mt-2">

                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 whitespace-nowrap">
                                {{ selectedFlag.category }}
                            </span>

                            <span class="text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap"
                                :class="{
                                    'bg-sky-100 text-sky-800': selectedFlag.priority === 'low',
                                    'bg-amber-100 text-amber-800': selectedFlag.priority === 'medium',
                                    'bg-orange-100 text-orange-800': selectedFlag.priority === 'high',
                                    'bg-rose-100 text-rose-800': selectedFlag.priority === 'critical'
                                }">
                                {{ selectedFlag.priority }}
                            </span>

                            <span v-if="selectedFlag.resolved" class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap">
                                Resolved
                            </span>

                        </div>

                    </div>

                    <!-- Comment -->
                    <p v-if="selectedFlag.comment" class="italic text-sm text-slate-700 leading-relaxed wrap-break-word mt-4">
                        {{ selectedFlag.comment }}
                    </p>

                </div>

                <Button
                    size="md"
                    mode="solid"
                    class="mx-4"
                    type="danger"
                    :action="deleteFlag"
                    buttonClass="rounded-lg w-full"
                    :loading="deletingFlags.includes(selectedFlag.id)">
                    Delete
                </Button>

            </template>

        </Modal>

    </div>

</template>

<script>

    import Modal from '@Partials/Modal.vue';
    import Input from '@Partials/Input.vue';
    import Select from '@Partials/Select.vue';
    import Button from '@Partials/Button.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import Datepicker from '@Partials/Datepicker.vue';
    import { formattedRelativeDate } from '@Utils/dateUtils';
    import { isEmpty, isNotEmpty, capitalize } from '@Utils/stringUtils';
    import { Frown, Check, Funnel, EllipsisVertical, CalendarMinus2, ArrowDownWideNarrow } from 'lucide-vue-next';

    export default {
        name: 'Issues',
        inject: ['appState', 'authState', 'formState', 'notificationState'],
        components: { Frown, Check, Modal, Input, Select, Button, Dropdown, Skeleton, Datepicker, Funnel, EllipsisVertical, CalendarMinus2, ArrowDownWideNarrow },
        data() {
            return {
                flags: [],
                summary: null,
                dateRange: null,
                searchQuery: '',
                pagination: null,
                updatingFlags: [],
                deletingFlags: [],
                resolvingFlags: [],
                unresolvingFlags: [],
                stepSelectOptions: [],
                isLoadingFlags: false,
                isLoadingSummary: false,
                selectedSortOptions: [],
                editForm: {
                    ussd_session_step_id: null,
                    category: null,
                    priority: 'low',
                    comment: '',
                },
                resolutionForm: {
                    comment: ''
                },
                sortOptions: [
                    { label: 'Newest first', group: 'date' },
                    { label: 'Oldest first', group: 'date' },
                    { label: 'Highest priority', group: 'priority' },
                    { label: 'Lowest priority', group: 'priority' },
                ],
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
                selectedFilterOptions: [],
                filterOptions: [
                    { field: 'status', label: 'Status', options: [
                        { value: 'open', label: 'Open' },
                        { value: 'resolved', label: 'Resolved' },
                    ]},
                    { field: 'priority', label: 'Priority', options: [
                        { value: 'critical', label: 'Critical' },
                        { value: 'high', label: 'High' },
                        { value: 'medium', label: 'Medium' },
                        { value: 'low', label: 'Low' },
                    ]},
                    { field: 'category', label: 'Category', options: [
                        { value: 'bug', label: 'Bug / Technical Error' },
                        { value: 'ux', label: 'UX / Flow Confusion' },
                        { value: 'content', label: 'Menu Text / Wording Issue' },
                        { value: 'performance', label: 'Performance / Slow Response' },
                        { value: 'security', label: 'Security / Compliance Concern' },
                        { value: 'feature-request', label: 'Feature Suggestion / Improvement' },
                        { value: 'other', label: 'Other' },
                    ]},
                ],
                priorityOptions: [
                    { value: 'low', label: 'Low' },
                    { value: 'medium', label: 'Medium' },
                    { value: 'high', label: 'High' },
                    { value: 'critical', label: 'Critical' },
                ],
                categoryOptions: [
                    { value: 'bug', label: 'Bug / Technical Error' },
                    { value: 'ux', label: 'UX / Flow Confusion' },
                    { value: 'content', label: 'Menu Text / Wording Issue' },
                    { value: 'performance', label: 'Performance / Slow Response' },
                    { value: 'security', label: 'Security / Compliance Concern' },
                    { value: 'feature-request', label: 'Feature Suggestion / Improvement' },
                    { value: 'other', label: 'Other' },
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
            }
        },
        computed: {
            app() {
                return this.appState.app;
            },
            authUser() {
                return this.authState.user;
            },
        },
        methods: {
            isEmpty,
            isNotEmpty,
            capitalize,
            formattedRelativeDate,
            async navigateToSession(sessionId) {
                console.log(sessionId);
                await this.$router.push({
                    name: 'show-app-session',
                    params: {
                        app_id: this.app.id,
                        session_id: sessionId
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
            showFilterModal() {
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
            getFilterLabel(field, value, config) {
                const option = config.options?.find(o => o.value === value);
                return `${config.label} ➜ ${option?.label || value.charAt(0).toUpperCase() + value.slice(1)}`;
            },
            clearFilters() {
                this.tempFilters = {};
                this.selectedFilterOptions = [];
                this.refreshData();
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
            getCategoryLabel(category) {
                const opt = this.categoryOptions.find(o => o.value === category);
                return opt ? opt.label : category.charAt(0).toUpperCase() + category.slice(1);
            },
            flagMenus(flag) {

                let menus =  [];

                if (flag.created_by?.id === this.authUser.id) {
                    menus.push({
                        name: 'Edit',
                        action: this.showEditableFlagModal
                    });
                }

                if(flag.resolved) {

                    menus.push({
                        name: 'Unresolve',
                        action: this.showUnresolvableFlagModal
                    });

                }else{

                    menus.push({
                        name: 'Resolve',
                        action: this.showResolvableFlagModal
                    });

                }

                menus.push({
                    name: 'Delete',
                    action: this.showDeletableFlagModal
                });

                return menus;

            },
            setStepSelectOptions(session) {
                if (!session?.steps?.length) return [];

                this.stepSelectOptions = session.steps.map((step, index) => ({
                    value: step.id,
                    label: `Step ${index + 1} — ${step.step_title || 'Unnamed'}`,
                }));
            },
            showEditableFlagModal(flag, toggleDropdown = null) {
                this.selectedFlag = flag;
                this.setStepSelectOptions(flag.session);

                this.editForm = {
                    category: flag.category,
                    priority: flag.priority,
                    comment: flag.comment || '',
                    ussd_session_step_id: flag.ussd_session_step_id || null,
                };

                if (toggleDropdown) toggleDropdown();
                this.$refs.editableFlagModal.showModal();
            },
            hideEditableFlagModal() {
                this.$refs.editableFlagModal.hideModal();
            },
            showResolvableFlagModal(flag, toggleDropdown = null) {
                this.selectedFlag = flag;
                if(toggleDropdown) toggleDropdown();
                this.$refs.resolvableFlagModal.showModal();
            },
            hideResolvableFlagModal() {
                this.$refs.resolvableFlagModal.hideModal();
            },
            showUnresolvableFlagModal(flag, toggleDropdown = null) {
                this.selectedFlag = flag;
                if(toggleDropdown) toggleDropdown();
                this.$refs.unresolvableFlagModal.showModal();
            },
            hideUnresolvableFlagModal() {
                this.$refs.unresolvableFlagModal.hideModal();
            },
            showDeletableFlagModal(flag, toggleDropdown = null) {
                this.selectedFlag = flag;
                if(toggleDropdown) toggleDropdown();
                this.$refs.deletableFlagModal.showModal();
            },
            hideDeletableFlagModal() {
                this.$refs.deletableFlagModal.hideModal();
            },
            flagThisSession(session) {
                this.setStepSelectOptions(session);
                this.form.ussd_session_step_id = session.steps[session.steps.length - 1].id;
                this.showFlagModal();
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


            changePage(link) {
                this.showFlags(link.page);
            },
            refreshData() {
                this.showFlags();
                this.showFlagsSummary();
            },
            async updateFlag() {

                const flagId = this.selectedFlag.id;

                if (this.updatingFlags.includes(flagId)) return;

                this.updatingFlags.push(flagId);

                try {

                    this.formState.hideFormErrors();

                    if (this.isEmpty(this.editForm.category)) {
                        this.formState.setFormError('category', 'Category is required');
                    }

                    if (this.isEmpty(this.editForm.priority)) {
                        this.formState.setFormError('priority', 'Priority is required');
                    }

                    if (this.formState.hasErrors) return;

                    const data = {
                        category: this.editForm.category,
                        priority: this.editForm.priority,
                        comment: this.editForm.comment.trim() || null,
                        ussd_session_step_id: this.editForm.ussd_session_step_id,
                    };

                    const response = await axios.put(`/api/apps/${this.app.id}/ussd-session-flags/${flagId}`, data);

                    const updatedFlag = response.data.ussd_session_flag;

                    this.notificationState.showSuccessNotification('Flag updated!');

                    const index = this.flags.findIndex(f => f.id === flagId);

                    if (index !== -1) {
                        this.flags[index] = {
                            ...this.flags[index],
                            ...updatedFlag
                        };
                    }

                    this.$refs.editableFlagModal.hideModal();
                    this.refreshData();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating flag';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update flag:', error);
                } finally {
                    const index = this.updatingFlags.indexOf(flagId);
                    if (index !== -1) {
                        this.updatingFlags.splice(index, 1);
                    }
                }
            },
            async resolveFlag() {

                const flagId = this.selectedFlag.id;

                if (this.resolvingFlags.includes(flagId)) return;

                this.resolvingFlags.push(flagId);

                try {

                    const data = {};

                    if(isNotEmpty(this.resolutionForm.comment)) {
                        data.resolution_comment = this.resolutionForm.comment;
                    }

                    const response = await axios.post(`/api/apps/${this.app.id}/ussd-session-flags/${flagId}/resolve`, data);

                    const resolvedFlag = response.data.ussd_session_flag;

                    this.notificationState.showSuccessNotification('Flag resolved!');

                    const index = this.flags.findIndex(f => f.id === flagId);

                    if (index !== -1) {
                        this.flags[index] = {
                            ...this.flags[index],
                            ...resolvedFlag,
                            resolved_by: this.authUser
                        };
                    }

                    this.resolutionForm.comment = '';
                    this.hideResolvableFlagModal();
                    this.refreshData();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while resolving flag';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to resolve flag:', error);
                } finally {
                    const index = this.resolvingFlags.indexOf(flagId);

                    if (index !== -1) {
                        this.resolvingFlags.splice(index, 1);
                    }
                }
            },
            async unresolveFlag() {

                const flagId = this.selectedFlag.id;

                if (this.unresolvingFlags.includes(flagId)) return;

                this.unresolvingFlags.push(flagId);

                try {

                    const data = {};

                    const response = await axios.post(`/api/apps/${this.app.id}/ussd-session-flags/${flagId}/unresolve`, data);

                    const unresolvedFlag = response.data.ussd_session_flag;

                    this.notificationState.showSuccessNotification('Flag unresolved!');

                    const index = this.flags.findIndex(f => f.id === flagId);

                    if (index !== -1) {
                        this.flags[index] = {
                            ...this.flags[index],
                            ...unresolvedFlag
                        };
                    }

                    this.hideUnresolvableFlagModal();
                    this.refreshData();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while unresolving flag';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to unresolve flag:', error);
                } finally {
                    const index = this.unresolvingFlags.indexOf(flagId);

                    if (index !== -1) {
                        this.unresolvingFlags.splice(index, 1);
                    }
                }
            },
            async deleteFlag() {

                const flagId = this.selectedFlag.id;

                if (this.deletingFlags.includes(flagId)) return;

                this.deletingFlags.push(flagId);

                try {

                    const config = {};

                    await axios.delete(`/api/apps/${this.app.id}/ussd-session-flags/${flagId}`, config);

                    const index = this.flags.findIndex(f => f.id === flagId);

                    this.flags.splice(index, 1)

                    this.notificationState.showSuccessNotification('Flag deleted!');

                    this.hideDeletableFlagModal();
                    this.refreshData();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting flag';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete flag:', error);
                } finally {
                    const index = this.deletingFlags.indexOf(flagId);

                    if (index !== -1) {
                        this.deletingFlags.splice(index, 1);
                    }
                }
            },
            async showFlags(page = null) {
                try {
                    this.isLoadingFlags = true;
                    const response = await axios.get(`/api/apps/${this.app.id}/ussd-session-flags`, this.getParams(page));
                    this.pagination = response.data;
                    this.flags = this.pagination.data || [];
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to fetch issues';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch issues:', error);
                } finally {
                    this.isLoadingFlags = false;
                }
            },
            async showFlagsSummary() {
                try {
                    this.isLoadingSummary = true;
                    const response = await axios.get(`/api/apps/${this.app.id}/ussd-session-flags/summary`, this.getParams());
                    this.summary = response.data;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to fetch summary';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch summary:', error);
                } finally {
                    this.isLoadingSummary = false;
                }
            },
            getParams(page = null) {
                let params = {
                    _relationships: ['session.steps', 'createdBy', 'resolvedBy'].join(',')
                };

                if (this.searchQuery.trim()) params.search = this.searchQuery.trim();

                if (this.selectedDateFilterOption.value !== 'all') {
                    params.date_range = this.selectedDateFilterOption.value;
                    if (this.selectedDateFilterOption.value === 'custom' && this.dateRange) {
                        params.date_range_end = this.dateRange[1];
                        params.date_range_start = this.dateRange[0];
                    }
                }

                this.selectedFilterOptions.forEach(f => {
                    params[f.field] = f.value;
                });

                this.selectedSortOptions.forEach(s => {
                    if (s.label === 'Newest first') params._sort = 'created_at:desc';
                    else if (s.label === 'Oldest first') params._sort = 'created_at:asc';
                    else if (s.label === 'Highest priority') params._sort = 'priority:desc';
                    else if (s.label === 'Lowest priority') params._sort = 'priority:asc';
                });

                if (page) params.page = page;

                return { params };
            }
        },
        created() {
            this.refreshData();
        }
    }

</script>
