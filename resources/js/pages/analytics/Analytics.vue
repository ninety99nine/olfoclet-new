<template>

    <div class="min-h-screen bg-slate-50 pb-12">

        <!-- Header + Controls -->
        <div class="bg-white border-b border-slate-200">

            <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Analytics & Insights</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ app.name }} • {{ timeRangeLabel }}</p>
                </div>

                <div class="flex items-center gap-3">

                    <!-- Tabs -->
                    <Tabs v-model="currentRange" :tabs="quickRanges" design="1"></Tabs>

                    <!-- Custom Button -->
                    <Button
                        size="md"
                        type="light"
                        mode="outline"
                        buttonClass="rounded-lg"
                        :action="showDateFilterModal">
                        <div class="flex items-center space-x-2">
                        <CalendarMinus2 size="16"></CalendarMinus2>
                        <span>Custom</span>
                        </div>
                    </Button>

                </div>

            </div>

        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-6 py-8">

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <!-- Always show the first 8 -->
                <KpiCard
                    v-for="(card, index) in firstEightKpis"
                    :key="index"
                    :title="card.title"
                    :loading="loadingOverview"
                    :value="card.value"
                    :trend="card.trend"
                    :trend-positive="card.trendPositive"
                />

                <!-- Show remaining cards only when toggled -->
                <template v-if="showAllKpis">
                    <KpiCard
                        v-for="(card, index) in remainingKpis"
                        :key="'extra-' + index"
                        :title="card.title"
                        :loading="loadingOverview"
                        :value="card.value"
                        :trend="card.trend"
                        :trend-positive="card.trendPositive"
                    />
                </template>

            </div>

            <!-- Show More / Show Less Button -->
            <div v-if="allKpiCards.length > 8" class="mb-8 text-center">
                <button
                    @click="showAllKpis = !showAllKpis"
                    class="text-xs font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mx-auto hover:scale-105 transition-all duration-300 cursor-pointer">
                    <span>{{ showAllKpis ? 'Show less' : 'Show more' }}</span>
                    <svg
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': showAllKpis }">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-12 gap-6 mb-12">

                <!-- Sessions & Success Rate -->
                <ChartCard title="Sessions & Success Rate" class="col-span-12">
                    <div class="h-80 relative">
                        <div v-if="loadingSessions" class="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                        </div>

                        <!-- Empty state when no data -->
                        <div v-if="!loadingSessions && !sessionsData?.labels?.length"
                            class="absolute inset-0 flex items-center justify-center text-slate-400 bg-white/80 z-10">
                        <div class="text-center">
                            <p class="text-lg font-medium">No data available</p>
                            <p class="text-sm mt-1">No sessions recorded in this period</p>
                        </div>
                        </div>

                        <canvas ref="sessionsChart"></canvas>
                    </div>
                </ChartCard>

                <!-- New Users Trend -->
                <ChartCard title="New Users Trend" class="col-span-6">
                    <div class="h-72 relative">
                        <div v-if="loadingNewUsers" class="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                        </div>

                        <div v-if="!loadingNewUsers && !newUsersData?.labels?.length"
                            class="absolute inset-0 flex items-center justify-center text-slate-400 bg-white/80 z-10">
                        <div class="text-center">
                            <p class="text-lg font-medium">No new users</p>
                            <p class="text-sm mt-1">No registrations in this period</p>
                        </div>
                        </div>

                        <canvas ref="newUsersChart"></canvas>
                    </div>
                </ChartCard>

                <!-- Return Users Trend -->
                <ChartCard title="Return Users Trend" class="col-span-6">
                    <div class="h-72 relative">
                        <div v-if="loadingReturnUsers" class="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                        </div>

                        <div v-if="!loadingReturnUsers && !returnUsersData?.labels?.length"
                            class="absolute inset-0 flex items-center justify-center text-slate-400 bg-white/80 z-10">
                        <div class="text-center">
                            <p class="text-lg font-medium">No return users</p>
                            <p class="text-sm mt-1">No returning activity in this period</p>
                        </div>
                        </div>

                        <canvas ref="returnUsersChart"></canvas>
                    </div>
                </ChartCard>

                <!-- By Network -->
                <ChartCard title="Sessions by Network" class="col-span-4">
                    <div class="h-72 relative">
                    <div v-if="loadingByNetwork" class="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                    </div>
                    <div v-if="!loadingByNetwork && !byNetworkData.labels?.length"
                        class="absolute inset-0 flex items-center justify-center text-slate-400 bg-white/80 z-10">
                        <p class="text-lg font-medium">No network data</p>
                    </div>
                    <canvas ref="byNetworkChart"></canvas>
                    </div>
                </ChartCard>

                <!-- By Country -->
                <ChartCard title="Sessions by Country" class="col-span-4">
                    <div class="h-72 relative">
                    <div v-if="loadingByCountry" class="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                    </div>
                    <div v-if="!loadingByCountry && !byCountryData.labels?.length"
                        class="absolute inset-0 flex items-center justify-center text-slate-400 bg-white/80 z-10">
                        <p class="text-lg font-medium">No country data</p>
                    </div>
                    <canvas ref="byCountryChart"></canvas>
                    </div>
                </ChartCard>

                <!-- By Network & Country (Grouped Bar) -->
                <ChartCard title="Sessions by Network & Country" class="col-span-4">
                    <div class="h-80 relative">
                    <div v-if="loadingByNetworkCountry" class="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                    </div>
                    <div v-if="!loadingByNetworkCountry && !byNetworkCountryData.length"
                        class="absolute inset-0 flex items-center justify-center text-slate-400 bg-white/80 z-10">
                        <p class="text-lg font-medium">No network-country breakdown</p>
                    </div>
                    <canvas ref="byNetworkCountryChart"></canvas>
                    </div>
                </ChartCard>

                <!-- Peak Hours / Day-of-Week Heatmap -->
                <ChartCard title="Peak Hours / Day-of-Week Heatmap" class="col-span-12">

                    <div class="relative">
                        <div v-if="loadingHeatmap" class="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                        </div>

                        <div v-if="!loadingHeatmap && !heatmapData.matrix?.length"
                            class="absolute inset-0 flex items-center justify-center text-slate-400 bg-white/80 z-10">
                            <div class="text-center">
                                <p class="text-lg font-medium">No activity data</p>
                                <p class="text-sm mt-1">No sessions recorded in this period</p>
                            </div>
                        </div>

                        <!-- Heatmap content -->
                        <div v-if="heatmapData.matrix?.length" class="p-6 flex flex-col h-full">
                        <!-- Header - Days -->
                        <div class="grid grid-cols-[80px_repeat(7,1fr)] border-b border-slate-200 pb-3 mb-2">
                            <div></div> <!-- empty top-left corner -->
                            <div v-for="day in heatmapData.days" :key="day"
                                class="text-center font-medium text-slate-700 text-sm">
                            {{ day }}
                            </div>
                        </div>

                        <!-- Body: 24 rows × (hour label + 7 cells) -->
                        <div class="flex-1 grid grid-cols-[80px_repeat(7,1fr)] gap-px bg-slate-50 overflow-y-auto">
                            <template v-for="(hourRow, hourIndex) in heatmapData.matrix" :key="hourIndex">
                            <!-- Hour label (left column) -->
                            <div class="flex items-center justify-end pr-4 text-xs text-slate-500 font-medium bg-white">
                                {{ hourIndex.toString().padStart(2, '0') }}:00
                            </div>

                            <!-- Cells for each day -->
                            <div
                                v-for="(value, dayIndex) in hourRow"
                                :key="dayIndex"
                                class="relative group "
                                :style="getHeatmapCellStyle(value, heatmapData.max)"
                                :title="`${heatmapData.days[dayIndex]} ${hourIndex.toString().padStart(2, '0')}:00 → ${value.toLocaleString()} sessions`"
                            >
                                <!-- Optional: show value on hover -->
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-white text-xs font-bold drop-shadow-md pointer-events-none">
                                {{ value }}
                                </div>
                            </div>
                            </template>
                        </div>
                        </div>
                    </div>
                </ChartCard>

                <!-- Failed Sessions Trend -->
                <ChartCard title="Failed Sessions Trend" class="col-span-12">
                    <div class="h-72 relative">
                        <div v-if="loadingFailedSessions" class="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
                            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                        </div>

                        <div v-if="!loadingFailedSessions && !failedSessionsData?.labels?.length"
                            class="absolute inset-0 flex items-center justify-center text-slate-400 bg-white/80 z-10">
                            <div class="text-center">
                                <p class="text-lg font-medium">No failed sessions</p>
                                <p class="text-sm mt-1">All sessions successful in this period</p>
                            </div>
                        </div>

                        <canvas ref="failedSessionsChart"></canvas>
                    </div>
                </ChartCard>

                <!-- Open vs Resolved Flags (Doughnut) -->
                <ChartCard title="Open vs Resolved Flags" class="col-span-12 lg:col-span-4">
                    <div class="h-72 relative">
                        <div v-if="loadingFlagsStatus" class="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
                            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                        </div>
                        <div v-if="!loadingFlagsStatus && flagsStatusData.total == 0"
                            class="absolute inset-0 flex items-center justify-center text-slate-400 bg-white/80 z-10">
                            <p class="text-lg font-medium">No flag data</p>
                        </div>
                        <canvas ref="flagsStatusChart"></canvas>
                    </div>
                </ChartCard>

                <!-- Open Flags by Priority (Horizontal Bar) -->
                <ChartCard title="Open Flags by Priority" class="col-span-12 lg:col-span-4">
                    <div class="h-72 relative">
                        <div v-if="loadingFlagsPriority" class="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
                            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                        </div>
                        <div v-if="!loadingFlagsPriority && !flagsPriorityData.length"
                            class="absolute inset-0 flex items-center justify-center text-slate-400 bg-white/80 z-10">
                            <p class="text-lg font-medium">No priority data</p>
                        </div>
                        <canvas ref="flagsPriorityChart"></canvas>
                    </div>
                </ChartCard>

                <!-- Open Flags by Category (Horizontal Bar) -->
                <ChartCard title="Open Flags by Category" class="col-span-12 lg:col-span-4">
                    <div class="h-72 relative">
                        <div v-if="loadingFlagsCategory" class="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
                            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                        </div>
                        <div v-if="!loadingFlagsCategory && !flagsCategoryData.length"
                            class="absolute inset-0 flex items-center justify-center text-slate-400 bg-white/80 z-10">
                            <p class="text-lg font-medium">No category data</p>
                        </div>
                        <canvas ref="flagsCategoryChart"></canvas>
                    </div>
                </ChartCard>

                <!-- By Device -->
                <ChartCard title="Sessions by Device" class="col-span-4">
                    <div class="h-72 relative">
                    <div v-if="loadingByDevice" class="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                    </div>
                    <div v-if="!loadingByDevice && !byDeviceData.labels?.length"
                        class="absolute inset-0 flex items-center justify-center text-slate-400 bg-white/80 z-10">
                        <p class="text-lg font-medium">No device data</p>
                    </div>
                    <canvas ref="byDeviceChart"></canvas>
                    </div>
                </ChartCard>

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

                <!-- Custom Range -->
                <Datepicker
                    :range="true"
                    class="w-full"
                    v-model="dateRange"
                    :showOutline="false"
                    @change="applyDateFilter"
                    placeholder="Select Date Range" />

            </template>

        </Modal>

    </div>

</template>

<script>

    import axios from 'axios';
    import 'chartjs-adapter-date-fns';
    import Tabs from '@Partials/Tabs.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import countries from '@Json/countries.json';
    import { Chart, registerables } from 'chart.js';
    import { CalendarMinus2 } from 'lucide-vue-next';
    import Datepicker from '@Partials/Datepicker.vue';
    import { formatDuration } from '@Utils/stringUtils';
    import KpiCard from '@Pages/analytics/_components/KpiCard.vue';
    import ChartCard from '@Pages/analytics/_components/ChartCard.vue';

    Chart.register(...registerables);

    export default {
        name: 'Analytics',
        inject: ['appState', 'notificationState'],
        components: { Tabs, Modal, Button, Datepicker, KpiCard, ChartCard, CalendarMinus2 },

        data() {
            return {
                dateRange: null,
                currentRange: '30d',
                showAllKpis: false,

                loadingHeatmap: false,
                loadingOverview: false,
                loadingSessions: false,
                loadingNewUsers: false,
                loadingByDevice: false,
                loadingByNetwork: false,
                loadingByCountry: false,
                loadingReturnUsers: false,
                loadingFlagsStatus: false,
                loadingFlagsPriority: false,
                loadingFlagsCategory: false,
                loadingFailedSessions: false,
                loadingByNetworkCountry: false,


                overview: [],
                flagsPriorityData: [],
                flagsCategoryData: [],
                byNetworkCountryData: [],
                newUsersData: { labels: [], values: [] },
                byDeviceData: { labels: [], values: [] },
                byNetworkData: { labels: [], values: [] },
                byCountryData: { labels: [], values: [] },
                returnUsersData: { labels: [], values: [] },
                heatmapData: { matrix: [], max: 1, days: [] },
                failedSessionsData: { labels: [], values: [] },
                flagsStatusData: { open: 0, resolved: 0, total: 0 },
                sessionsData: { labels: [], sessions: [], successRate: [] },

                heatmapChart: null,
                sessionsChart: null,
                newUsersChart: null,
                byDeviceChart: null,
                byNetworkChart: null,
                byCountryChart: null,
                returnUsersChart: null,
                flagsStatusChart: null,
                flagsPriorityChart: null,
                flagsCategoryChart: null,
                failedSessionsChart: null,
                byNetworkCountryChart: null,

                quickRanges: [
                    { value: 'today', label: 'Today' },
                    { value: '7d',    label: '7 days' },
                    { value: '30d',   label: '30 days' },
                    { value: '90d',   label: '90 days' },
                    { value: 'ytd',   label: 'YTD' }
                ],
            }
        },
        watch: {
            currentRange() {
                if(this.currentRange != 'custom') {
                    this.dateRange = null;
                }
                this.loadAllData();
            }
        },
        computed: {
            app() {
                return this.appState.app;
            },
            timeRangeLabel() {
                if (this.currentRange === 'custom') return this.customRangeLabel;
                const found = this.quickRanges.find(r => r.value === this.currentRange);
                return found ? found.label : 'Custom';
            },
            customRangeLabel() {
                if (!this.dateRange[0] || !this.dateRange[1]) return ''
                const s = new Date(this.dateRange[0]).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
                const e = new Date(this.dateRange[1]).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
                return `${s} – ${e}`
            },
            allKpiCards() {
                if(this.overview.length == 0) return [];
                console.log('this.overview');
                console.log(this.overview);
                console.log('this.overview.total_sessions');
                console.log(this.overview.total_sessions);
                return [
                    {
                        title: 'Total Sessions',
                        value: this.overview.total_sessions.toString() || '—',
                        trend: '-',
                        trendPositive: true
                    },
                    {
                        title: 'Success Rate',
                        value: this.overview.success_rate ? this.overview.success_rate.toString() + '%' : '—',
                        trend: '-',
                        trendPositive: true
                    },
                    {
                        title: 'Avg Session Duration',
                        value: this.formatDuration(this.overview.avg_duration_ms) || '—',
                        trend: '-',
                        trendPositive: true
                    },
                    {
                        title: 'Total Users (All time)',
                        value: this.overview.total_users.toString() || '—',
                        trend: '-',
                        trendPositive: true
                    },
                    {
                        title: 'New Users (this period)',
                        value: this.overview.new_users.toString() || '—',
                        trend: '-',
                        trendPositive: true
                    },
                    {
                        title: 'Return Users (this period)',
                        value: this.overview.return_users.toString() || '—',
                        trend: '-',
                        trendPositive: true
                    },
                    {
                        title: 'Lapsed Users (this period)',
                        value: this.overview.lapsed_users.toString() || '—',
                        trend: '-',
                        trendPositive: true
                    },
                    {
                        title: 'Open Flags',
                        value: this.overview.open_flags.toString(),
                        trend: '-',
                        trendPositive: true
                    },
                    {
                        title: 'Resolved Flags',
                        value: this.overview.resolved_flags.toString(),
                        trend: '-',
                        trendPositive: true
                    }
                ];
            },
            firstEightKpis() {
                return this.allKpiCards.slice(0, 8);
            },
            remainingKpis() {
                return this.allKpiCards.slice(8);
            }
        },
        methods: {
            formatDuration,
            getCountryName(iso) {
                if (!iso) return 'Unknown';
                const country = countries.find(c => c.iso.toUpperCase() === iso.toUpperCase());
                return country ? country.name : iso;
            },
            showDateFilterModal() {
                this.$refs.dateFilterModal.showModal();
            },
            hideDateFilterModal() {
                this.$refs.dateFilterModal.hideModal();
            },
            applyDateFilter() {
                if(!this.dateRange) {
                    this.currentRange = '30d';
                    return;
                }

                this.currentRange = 'custom';
                this.hideDateFilterModal();
            },
            async loadAllData() {
                this.fetchOverview();
                this.fetchSessionsOverTime();
                this.fetchNewUsersOverTime();
                this.fetchReturnUsersOverTime();
                this.fetchByNetwork();
                this.fetchByCountry();
                this.fetchByNetworkAndCountry();
                this.fetchByDevice();
                this.fetchHeatmap();
                this.fetchFailedSessionsOverTime();
                this.fetchFlagsStatus();
                this.fetchFlagsByPriority();
                this.fetchFlagsByCategory();
            },
            getDateParams() {
                const params = {
                    range: this.currentRange,
                    app_id: this.app.id
                }

                if (this.currentRange === 'custom') {
                    params.start = this.dateRange[0];
                    params.end   = this.dateRange[1]
                }

                return params;
            },

            async fetchOverview() {
                try {
                    this.loadingOverview = true;
                    const response = await axios.get('/api/analytics/overview', { params: this.getDateParams() });
                    this.overview = response.data;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load analytics overview';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to load analytics overview:', error);
                } finally {
                    this.loadingOverview = false;
                }
            },
            async fetchSessionsOverTime() {
                try {
                    this.loadingSessions = true;
                    const response = await axios.get('/api/analytics/sessions-over-time', { params: this.getDateParams() });
                    this.sessionsData = response.data;
                    this.renderSessionsChart();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load sessions chart';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to load sessions chart:', error);
                } finally {
                    this.loadingSessions = false;
                }
            },
            async fetchNewUsersOverTime() {
                try {
                    this.loadingNewUsers = true;
                    const response = await axios.get('/api/analytics/new-users-over-time', { params: this.getDateParams() });
                    this.newUsersData = response.data;
                    this.renderNewUsersChart();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load new users chart';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to load new users chart:', error);
                } finally {
                    this.loadingNewUsers = false;
                }
            },
            async fetchReturnUsersOverTime() {
                try {
                    this.loadingReturnUsers = true;
                    const response = await axios.get('/api/analytics/return-users-over-time', { params: this.getDateParams() });
                    this.returnUsersData = response.data;
                    this.renderReturnUsersChart();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load return users chart';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to load return users chart:', error);
                } finally {
                    this.loadingReturnUsers = false;
                }
            },
            async fetchByNetwork() {
                try {
                    this.loadingByNetwork = true;
                    const response = await axios.get('/api/analytics/by-network', { params: this.getDateParams() });
                    this.byNetworkData = {
                        labels: response.data.map(item => item.network),
                        values: response.data.map(item => item.sessions)
                    };
                    this.renderByNetworkChart();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load network breakdown';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to load network breakdown:', error);
                } finally {
                    this.loadingByNetwork = false;
                }
            },
            async fetchByCountry() {
                try {
                    this.loadingByCountry = true;
                    const response = await axios.get('/api/analytics/by-country', { params: this.getDateParams() });
                    this.byCountryData = {
                        labels: response.data.map(item => this.getCountryName(item.country) || item.country || 'Unknown'),
                        values: response.data.map(item => item.sessions)
                    };
                    this.renderByCountryChart();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load country breakdown';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to load country breakdown:', error);
                } finally {
                    this.loadingByCountry = false;
                }
            },
            async fetchByNetworkAndCountry() {
                try {
                    this.loadingByNetworkCountry = true;
                    const response = await axios.get('/api/analytics/by-network-and-country', { params: this.getDateParams() });
                    this.byNetworkCountryData = response.data.map(networkGroup => ({
                        network: networkGroup.network,
                        countries: networkGroup.countries.map(c => ({
                            country: this.getCountryName(c.country) || c.country || 'Unknown',
                            sessions: c.sessions
                        }))
                    }));

                    this.renderByNetworkCountryChart();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load network-country breakdown';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to load network-country breakdown:', error);
                } finally {
                    this.loadingByNetworkCountry = false;
                }
            },
            async fetchByDevice() {
                try {
                    this.loadingByDevice = true;
                    const response = await axios.get('/api/analytics/by-device', { params: this.getDateParams() });
                    this.byDeviceData = {
                        labels: response.data.map(item => item.device),
                        values: response.data.map(item => item.sessions)
                    };
                    this.renderByDeviceChart();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load device breakdown';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to load device breakdown:', error);
                } finally {
                    this.loadingByDevice = false;
                }
            },
            async fetchHeatmap() {
                try {
                    this.loadingHeatmap = true;
                    const response = await axios.get('/api/analytics/heatmap', { params: this.getDateParams() });
                    this.heatmapData = response.data;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load heatmap';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to load heatmap:', error);
                } finally {
                    this.loadingHeatmap = false;
                }
            },
            async fetchFlagsStatus() {
                try {
                    this.loadingFlagsStatus = true;
                    const response = await axios.get('/api/analytics/flags-status', { params: this.getDateParams() });
                    this.flagsStatusData = response.data;
                    this.renderFlagsStatusChart();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load flags status';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to load flags status:', error);
                } finally {
                    this.loadingFlagsStatus = false;
                }
            },
            async fetchFlagsByPriority() {
                try {
                    this.loadingFlagsPriority = true;
                    const response = await axios.get('/api/analytics/flags-by-priority', { params: this.getDateParams() });
                    this.flagsPriorityData = response.data;
                    this.renderFlagsPriorityChart();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load flags by priority';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to load flags by priority:', error);
                } finally {
                    this.loadingFlagsPriority = false;
                }
            },
            async fetchFlagsByCategory() {
                try {
                    this.loadingFlagsCategory = true;
                    const response = await axios.get('/api/analytics/flags-by-category', { params: this.getDateParams() });
                    this.flagsCategoryData = response.data;
                    this.renderFlagsCategoryChart();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load flags by category';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to load flags by category:', error);
                } finally {
                    this.loadingFlagsCategory = false;
                }
            },
            async fetchFailedSessionsOverTime() {
                try {
                    this.loadingFailedSessions = true;
                    const response = await axios.get('/api/analytics/failed-sessions-over-time', { params: this.getDateParams() });
                    this.failedSessionsData = response.data;
                    this.renderFailedSessionsChart();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to load failed sessions chart';
                    this.notificationState.showWarningNotification(message);
                    console.error('Failed to load failed sessions chart:', error);
                } finally {
                    this.loadingFailedSessions = false;
                }
            },

            renderSessionsChart() {
                if (this.sessionsChart) this.sessionsChart.destroy();

                const canvas = this.$refs.sessionsChart;
                if (!canvas || !this.sessionsData.labels?.length) return;

                this.sessionsChart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: this.sessionsData.labels,
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Sessions',
                                data: this.sessionsData.sessions,
                                backgroundColor: (ctx) => {
                                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300)
                                    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.75)')
                                    gradient.addColorStop(1, 'rgba(99, 102, 241, 0.45)')
                                    return gradient
                                },
                                borderColor: 'rgb(99, 102, 241)',
                                borderWidth: 1,
                                borderRadius: 5,
                                barPercentage: 0.85
                            },
                            {
                                type: 'line',
                                label: 'Success Rate (%)',
                                data: this.sessionsData.success_rate,
                                borderColor: '#10b981',
                                backgroundColor: (context) => {
                                    const { ctx, chartArea } = context.chart
                                    if (!chartArea) return null
                                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom)
                                    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.32)')
                                    gradient.addColorStop(0.5, 'rgba(16, 185, 129, 0.16)')
                                    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)')
                                    return gradient
                            },
                                borderWidth: 2.5,
                                tension: 0,
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
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', align: 'start' },
                            tooltip: { backgroundColor: 'rgba(30, 41, 59, 0.94)' }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                min: 0,
                                beginAtZero: true,
                                title: { display: true, text: 'Sessions' },
                                ticks: {
                                    callback: v => Math.round(v).toLocaleString(),  // ← remove decimals + add commas
                                    stepSize: 1  // optional: force integer steps if needed
                                }
                            },
                            y1: {
                                position: 'right',
                                min: 0,
                                title: { display: true, text: 'Success Rate' },
                                grid: { drawOnChartArea: false },
                                ticks: {
                                    callback: v => Math.round(v) + '%',  // success rate already integer-ish, but round anyway
                                    stepSize: 10  // optional: nicer steps (0, 10, 20, ...)
                                }
                            }
                        }
                    }
                });
            },
            renderNewUsersChart() {
                if (this.newUsersChart) this.newUsersChart.destroy();

                const canvas = this.$refs.newUsersChart;
                if (!canvas || !this.newUsersData.labels?.length) return;

                this.newUsersChart = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: this.newUsersData.labels,
                        datasets: [{
                            label: 'New Users',
                            data: this.newUsersData.values,
                            borderColor: '#6366f1',
                            backgroundColor: (ctx) => {
                                const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300)
                                gradient.addColorStop(0, 'rgba(99, 102, 241, 0.35)')
                                gradient.addColorStop(1, 'rgba(99, 102, 241, 0.02)')
                                return gradient
                            },
                            borderWidth: 2.5,
                            tension: 0,
                            fill: true,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#6366f1',
                            pointBorderWidth: 2,
                            pointRadius: 3.5,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', align: 'start' } },
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => Math.round(v).toLocaleString(),  // ← whole numbers + commas
                                    stepSize: 1  // optional
                                }
                            }
                        }
                    }
                });
            },
            renderReturnUsersChart() {
                if (this.returnUsersChart) this.returnUsersChart.destroy();

                const canvas = this.$refs.returnUsersChart;
                if (!canvas || !this.returnUsersData.labels?.length) return;

                this.returnUsersChart = new Chart(canvas, {
                    type: 'line',
                    data: {
                    labels: this.returnUsersData.labels,
                    datasets: [{
                        label: 'Return Users',
                        data: this.returnUsersData.values,
                        borderColor: '#6366f1',
                        backgroundColor: (ctx) => {
                            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300)
                            gradient.addColorStop(0, 'rgba(99, 102, 241, 0.35)')
                            gradient.addColorStop(1, 'rgba(99, 102, 241, 0.02)')
                            return gradient
                        },
                        borderWidth: 2.5,
                        tension: 0,
                        fill: true,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#6366f1',
                        pointBorderWidth: 2,
                        pointRadius: 3.5,
                        pointHoverRadius: 6
                    }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', align: 'start' } },
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => Math.round(v).toLocaleString(),
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            },
            renderByNetworkChart() {
                if (this.byNetworkChart) this.byNetworkChart.destroy();

                const canvas = this.$refs.byNetworkChart;
                if (!canvas || !this.byNetworkData.labels?.length) return;

                this.byNetworkChart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: this.byNetworkData.labels,
                        datasets: [{
                            label: 'Sessions',
                            data: this.byNetworkData.values,
                            backgroundColor: (ctx) => {
                                const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                                gradient.addColorStop(0, 'rgba(99, 102, 241, 0.75)');
                                gradient.addColorStop(1, 'rgba(99, 102, 241, 0.45)');
                                return gradient;
                            },
                            borderColor: 'rgb(99, 102, 241)',
                            borderWidth: 1,
                            borderRadius: 5,
                            barPercentage: 0.85
                        }]
                    },
                    options: {
                        indexAxis: 'x',
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', align: 'start' },
                            tooltip: { backgroundColor: 'rgba(30, 41, 59, 0.94)' }
                        },
                        x: {
                            beginAtZero: true,
                            grid: { color: '#e2e8f0' }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { display: false },
                            ticks: {
                                callback: v => Math.round(v).toLocaleString()   // ← no decimals + thousand separators
                            }
                        }
                    }
                });
            },
            renderByCountryChart() {
                if (this.byCountryChart) this.byCountryChart.destroy();

                const canvas = this.$refs.byCountryChart;
                if (!canvas || !this.byCountryData.labels?.length) return;

                this.byCountryChart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: this.byCountryData.labels,
                        datasets: [{
                            label: 'Sessions',
                            data: this.byCountryData.values,
                            backgroundColor: (ctx) => {
                                const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                                gradient.addColorStop(0, 'rgba(99, 102, 241, 0.75)');
                                gradient.addColorStop(1, 'rgba(99, 102, 241, 0.45)');
                                return gradient;
                            },
                            borderColor: 'rgb(99, 102, 241)',
                            borderWidth: 1,
                            borderRadius: 5,
                            barPercentage: 0.85
                        }]
                    },
                    options: {
                        indexAxis: 'x',
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', align: 'start' },
                            tooltip: { backgroundColor: 'rgba(30, 41, 59, 0.94)' }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: '#e2e8f0' }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { display: false },
                                ticks: {
                                    callback: v => Math.round(v).toLocaleString()   // ← no decimals + commas
                                }
                            }
                        }
                    }
                });
            },
            renderByNetworkCountryChart() {
                if (this.byNetworkCountryChart) this.byNetworkCountryChart.destroy();

                const canvas = this.$refs.byNetworkCountryChart;
                if (!canvas || !this.byNetworkCountryData.length) return;

                // 1. Get all unique countries across all networks
                const allCountries = [...new Set(
                    this.byNetworkCountryData.flatMap(group =>
                        group.countries.map(c => c.country)  // ← now full names
                    )
                )].sort();

                // 2. Get all unique networks (for legend & colors)
                const networks = this.byNetworkCountryData.map(g => g.network);

                // 3. Build datasets: one dataset per network
                const datasets = this.byNetworkCountryData.map((networkGroup, index) => {
                    // For each network, create data array matching country order
                    const data = allCountries.map(country => {
                        const match = networkGroup.countries.find(c => c.country === country);
                        return match ? match.sessions : 0;
                    });

                    return {
                        label: networkGroup.network,
                        data: data,
                        backgroundColor: [
                            'rgba(99, 102, 241, 0.85)',     // 1. Indigo family (classic primary)
                            'rgba(139, 92, 246, 0.85)',     // 3. Violet family (purple accent)
                            'rgba(59, 130, 246, 0.85)',     // 4. Sky blue family (bright cool blue)
                            'rgba(16, 185, 129, 0.85)',     // 2. Emerald/teal family (fresh green)
                            'rgba(6, 182, 212, 0.85)',      // 5. Cyan family (vibrant cyan)
                            'rgba(71, 85, 105, 0.80)',      // 6. Deep slate/blue-gray (neutral cool tone)
                        ][index % 6],
                        borderColor: 'transparent',
                        borderWidth: 0,
                        borderRadius: 6,
                        barPercentage: 0.8,
                        categoryPercentage: 0.9
                    };
                });

                this.byNetworkCountryChart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: allCountries,  // ← countries on x-axis (BW, ZA, etc.)
                        datasets               // ← one dataset per network
                    },
                    options: {
                        indexAxis: 'x',
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                align: 'start',
                                labels: {
                                    padding: 20,
                                    font: { size: 13 }
                                }
                            },
                            tooltip: { backgroundColor: 'rgba(30, 41, 59, 0.94)' }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                title: {
                                    display: true,
                                    text: 'Country',
                                    padding: { top: 10 }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Sessions',
                                    padding: { bottom: 10 }
                                },
                                ticks: {
                                    callback: v => Math.round(v).toLocaleString()   // ← no decimals + thousand separators
                                },
                                grid: { color: '#e2e8f0' }
                            }
                        }
                    }
                });
            },
            renderByDeviceChart() {
                if (this.byDeviceChart) this.byDeviceChart.destroy();

                const canvas = this.$refs.byDeviceChart;
                if (!canvas || !this.byDeviceData.labels?.length) return;

                this.byDeviceChart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: this.byDeviceData.labels,
                        datasets: [{
                            label: 'Sessions',
                            data: this.byDeviceData.values,
                            backgroundColor: (ctx) => {
                                const device = this.byDeviceData.labels[ctx.dataIndex]?.toLowerCase() || '';

                                let startColor, endColor;

                                if (device.includes('mobile')) {
                                    // Emerald / teal-green for Mobile
                                    startColor = 'rgba(16, 185, 129, 0.85)';   // emerald-500
                                    endColor   = 'rgba(16, 185, 129, 0.45)';
                                } else if (device.includes('simulator')) {
                                    // Amber / yellow-orange for Simulator
                                    startColor = 'rgba(245, 158, 11, 0.85)';   // amber-500
                                    endColor   = 'rgba(245, 158, 11, 0.45)';
                                } else {
                                    // Fallback (e.g. unknown)
                                    startColor = 'rgba(148, 163, 184, 0.85)';  // slate-400
                                    endColor   = 'rgba(148, 163, 184, 0.45)';
                                }

                                const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                                gradient.addColorStop(0, startColor);
                                gradient.addColorStop(1, endColor);
                                return gradient;
                            },
                            borderColor: (ctx) => {
                                const device = this.byDeviceData.labels[ctx.dataIndex]?.toLowerCase() || '';
                                if (device.includes('mobile')) return 'rgb(16, 185, 129)';
                                if (device.includes('simulator')) return 'rgb(245, 158, 11)';
                                return 'rgb(148, 163, 184)';
                            },
                            borderWidth: 1,
                            borderRadius: 5,
                            barPercentage: 0.85
                        }]
                    },
                    options: {
                        indexAxis: 'x',
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', align: 'start' },
                            tooltip: { backgroundColor: 'rgba(30, 41, 59, 0.94)' }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: '#e2e8f0' }
                            },
                            y: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            },
            getHeatmapCellStyle(value, maxValue) {
                if (value === 0) return { backgroundColor: '#ffffff' };

                const intensity = Math.min(value / maxValue, 1);
                const alpha = 0.25 + intensity * 0.75;

                // White-yellow → orange → deep red
                const r = 255;
                const g = Math.round(255 * (1 - intensity));
                const b = Math.round(100 * (1 - intensity));

                return {
                    backgroundColor: `rgba(${r}, ${g}, ${b}, ${alpha})`,
                    cursor: 'pointer'
                };
            },
            renderFlagsStatusChart() {
                if (this.flagsStatusChart) this.flagsStatusChart.destroy();

                const canvas = this.$refs.flagsStatusChart;
                if (!canvas || this.flagsStatusData.total == 0) return;

                this.flagsStatusChart = new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Open', 'Resolved'],
                        datasets: [{
                            data: [this.flagsStatusData.open, this.flagsStatusData.resolved],
                            backgroundColor: [
                                'rgba(239, 68, 68, 0.85)',   // red for Open (warning feel intentional)
                                'rgba(16, 185, 129, 0.85)'   // emerald for Resolved
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverOffset: 12
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                align: 'start',
                                labels: {
                                    padding: 20,
                                    font: { size: 13 }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(30, 41, 59, 0.94)',
                                callbacks: {
                                    label: ctx => `${ctx.label}: ${ctx.raw} (${((ctx.raw / this.flagsStatusData.total) * 100).toFixed(1)}%)`
                                }
                            }
                        }
                    }
                });
            },
            renderFlagsPriorityChart() {
                if (this.flagsPriorityChart) this.flagsPriorityChart.destroy();

                const canvas = this.$refs.flagsPriorityChart;
                if (!canvas || !this.flagsPriorityData.length) return;

                // Map priority levels to colors (case-insensitive match)
                const priorityColors = {
                    'critical': { start: 'rgba(239, 68, 68, 0.90)', end: 'rgba(239, 68, 68, 0.50)' },   // red-500
                    'high':     { start: 'rgba(249, 115, 22, 0.85)', end: 'rgba(249, 115, 22, 0.45)' },   // orange-500
                    'medium':   { start: 'rgba(234, 179, 8, 0.85)', end: 'rgba(234, 179, 8, 0.45)' },    // yellow-500
                    'low':      { start: 'rgba(34, 197, 94, 0.85)', end: 'rgba(34, 197, 94, 0.45)' },     // green-500
                    'unknown':  { start: 'rgba(148, 163, 184, 0.85)', end: 'rgba(148, 163, 184, 0.45)' }  // slate-400 fallback
                };

                this.flagsPriorityChart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: this.flagsPriorityData.map(item => item.priority),
                        datasets: [{
                            label: 'Open Flags',
                            data: this.flagsPriorityData.map(item => item.count),
                            backgroundColor: (ctx) => {
                                const priority = (this.flagsPriorityData[ctx.dataIndex]?.priority || 'unknown').toLowerCase();
                                const colors = priorityColors[priority] || priorityColors['unknown'];
                                const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                                gradient.addColorStop(0, colors.start);
                                gradient.addColorStop(1, colors.end);
                                return gradient;
                            },
                            borderColor: (ctx) => {
                                const priority = (this.flagsPriorityData[ctx.dataIndex]?.priority || 'unknown').toLowerCase();
                                return priorityColors[priority]?.start.replace('0.85', '1') || 'rgb(148, 163, 184)';
                            },
                            borderWidth: 1,
                            borderRadius: 5,
                            barPercentage: 0.85
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { backgroundColor: 'rgba(30, 41, 59, 0.94)' }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: '#e2e8f0' },
                                ticks: { callback: v => v.toLocaleString() }
                            },
                            y: { grid: { display: false } }
                        }
                    }
                });
            },
            renderFlagsCategoryChart() {
                if (this.flagsCategoryChart) this.flagsCategoryChart.destroy();

                const canvas = this.$refs.flagsCategoryChart;
                if (!canvas || !this.flagsCategoryData.length) return;

                this.flagsCategoryChart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: this.flagsCategoryData.map(item => item.category),
                        datasets: [{
                            label: 'Open Flags',
                            data: this.flagsCategoryData.map(item => item.count),
                            backgroundColor: (ctx) => {
                                const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                                gradient.addColorStop(0, 'rgba(234, 179, 8, 0.85)');
                                gradient.addColorStop(1, 'rgba(234, 179, 8, 0.45)');
                                return gradient;
                            },
                            borderColor: 'rgb(234, 179, 8)',
                            borderWidth: 1,
                            borderRadius: 5,
                            barPercentage: 0.85
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { backgroundColor: 'rgba(30, 41, 59, 0.94)' }
                        },
                        scales: {
                            x: { beginAtZero: true, grid: { color: '#e2e8f0' } },
                            y: { grid: { display: false } }
                        }
                    }
                });
            },
            renderFailedSessionsChart() {
                if (this.failedSessionsChart) this.failedSessionsChart.destroy();

                const canvas = this.$refs.failedSessionsChart;
                if (!canvas || !this.failedSessionsData.labels?.length) return;

                this.failedSessionsChart = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: this.failedSessionsData.labels,
                        datasets: [{
                            label: 'Failed Sessions',
                            data: this.failedSessionsData.values,
                            borderColor: '#ef4444',  // red for failures
                            backgroundColor: (ctx) => {
                                const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                                gradient.addColorStop(0, 'rgba(239, 68, 68, 0.35)');
                                gradient.addColorStop(1, 'rgba(239, 68, 68, 0.02)');
                                return gradient;
                            },
                            borderWidth: 2.5,
                            tension: 0,
                            fill: true,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#ef4444',
                            pointBorderWidth: 2,
                            pointRadius: 3.5,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', align: 'start' } },
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => Math.round(v).toLocaleString(),
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            },
        },
        mounted() {
            this.loadAllData();
        },
        beforeUnmount() {
            if (this.heatmapChart) this.heatmapChart.destroy();
            if (this.byDeviceChart) this.byDeviceChart.destroy();
            if (this.sessionsChart) this.sessionsChart.destroy();
            if (this.newUsersChart) this.newUsersChart.destroy();
            if (this.byCountryChart) this.byCountryChart.destroy();
            if (this.byNetworkChart) this.byNetworkChart.destroy();
            if (this.returnUsersChart) this.returnUsersChart.destroy();
            if (this.flagsStatusChart) this.flagsStatusChart.destroy();
            if (this.flagsPriorityChart) this.flagsPriorityChart.destroy();
            if (this.flagsCategoryChart) this.flagsCategoryChart.destroy();
            if (this.failedSessionsChart) this.failedSessionsChart.destroy();
            if (this.byNetworkCountryChart) this.byNetworkCountryChart.destroy();
        },
    }
</script>
