<template>

    <div class="min-h-screen bg-slate-50 pb-80">

        <!-- Header -->
        <div class="bg-white border-b border-slate-200">

            <div class="max-w-7xl mx-auto px-6 py-6">

                <div class="flex items-center justify-between">

                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
                        <p class="mt-1 text-sm text-slate-500">
                        {{ app.name }} • Overview
                        </p>
                    </div>

                    <Button
                        size="md"
                        type="light"
                        mode="outline"
                        v-if="summary"
                        :leftIcon="RefreshCcw"
                        buttonClass="rounded-lg"
                        :action="refreshDashboard"
                        :loading="isLoadingSessions">
                        <span class="ml-1">Refresh</span>
                    </Button>

                </div>

            </div>

        </div>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-6 py-4">

            <!-- Quick Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-4">

                <!-- Total Sessions -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-50/50 rounded-full blur-2xl"></div>
                    <p class="text-sm text-slate-500 font-medium">Total Sessions</p>
                    <p v-if="isLoadingSummary" class="text-5xl font-bold text-slate-200 mt-2 animate-pulse">---</p>
                    <p v-else class="text-5xl font-bold text-slate-900 mt-1 tracking-tight">
                        {{ summary?.total_sessions?.toLocaleString() || '—' }}
                    </p>
                    <p class="text-sm text-emerald-600 mt-2 font-medium">
                        {{ summary?.recent_sessions_trend || '—' }}
                    </p>
                </div>

                <!-- Success Rate -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-emerald-50/50 rounded-full blur-2xl"></div>
                    <p class="text-sm text-slate-500 font-medium">Success Rate</p>
                    <p v-if="isLoadingSummary" class="text-5xl font-bold text-slate-200 mt-2 animate-pulse">---</p>
                    <p v-else class="text-5xl font-bold mt-1 tracking-tight"
                        :class="{
                        'text-emerald-600': summary?.success_rate >= 95,
                        'text-amber-600': summary?.success_rate >= 85 && summary?.success_rate < 95,
                        'text-rose-600': summary?.success_rate < 85
                        }">
                        {{ summary?.success_rate || '—' }}%
                    </p>
                </div>

                <!-- Avg Duration -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-amber-50/30 rounded-full blur-2xl"></div>
                    <p class="text-sm text-slate-500 font-medium">Avg. Duration</p>
                    <p v-if="isLoadingSummary" class="text-5xl font-bold text-slate-200 mt-2 animate-pulse">---</p>
                    <template v-else>
                        <p class="text-5xl font-bold text-slate-900 mt-1 tracking-tight">
                            {{ formatDuration(summary?.avg_duration_ms) || '—' }}
                        </p>
                        <p class="text-sm text-slate-500 mt-2">
                            <template v-if="summary?.avg_steps">
                                {{ summary?.avg_steps || '—' }} {{ summary?.avg_steps == 1 ? 'step' : 'steps' }}
                            </template>
                            <template v-else>-</template>
                        </p>
                    </template>
                </div>

                <!-- 4. Open Flags -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-rose-50/40 rounded-full blur-2xl"></div>
                    <p class="text-sm text-slate-500 font-medium">Open Flags</p>
                    <p v-if="isLoadingSummary" class="text-5xl font-bold text-slate-200 mt-2 animate-pulse">---</p>
                    <p v-else class="text-5xl font-bold mt-1 tracking-tight"
                        :class="{
                        'text-rose-600': summary?.total_open_flags > 10,
                        'text-amber-600': summary?.total_open_flags > 3 && summary?.total_open_flags <= 10,
                        'text-emerald-600': summary?.total_open_flags <= 3
                        }">
                        {{ summary?.total_open_flags?.toLocaleString() || '0' }}
                    </p>
                    <p class="text-sm mt-2"
                        :class="{
                        'text-rose-600': summary?.total_open_flags > 10,
                        'text-amber-600': summary?.total_open_flags > 3 && summary?.total_open_flags <= 10,
                        'text-emerald-600': summary?.total_open_flags <= 3
                        }">
                        {{ summary?.total_open_flags > 0 ? 'Needs attention' : 'All clear' }}
                    </p>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                <!-- Left / Main column -->
                <div class="lg:col-span-9 space-y-4">

                    <!-- Recent Sessions -->
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">

                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-slate-900">Recent Sessions</h2>
                            <router-link
                                :to="{ name: 'show-app-sessions', params: { app_id: app.id } }"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-all hover:scale-105 duration-300">
                                View All
                                <ArrowRight size="14" />
                            </router-link>
                        </div>

                        <div v-if="isLoadingSessions" class="p-16 flex justify-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                        </div>

                        <div v-else-if="!recentSessions?.length" class="p-16 text-center text-slate-500">
                            <Frown size="48" class="mx-auto mb-4 opacity-40" />
                            <p class="text-lg font-medium">No recent sessions</p>
                            <p class="mt-2">New activity will appear here</p>
                        </div>

                        <div v-else class="overflow-x-auto">

                            <table class="min-w-full divide-y divide-slate-100">

                                <thead class="bg-slate-50/70">

                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Network</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Phone</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Duration</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">When</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Steps</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Device</th>
                                    </tr>

                                </thead>

                                <tbody class="divide-y divide-slate-100">

                                    <tr
                                        v-for="session in recentSessions"
                                        :key="session.id"
                                        @click="navigateToSession(session.id)"
                                        class="hover:bg-slate-50 transition-colors cursor-pointer group">

                                        <td class="px-6 py-5 whitespace-nowrap">
                                            <div class="flex items-center gap-2.5">
                                                <img
                                                v-if="session.country"
                                                :src="`/svgs/country-flags/${session.country.toLowerCase()}.svg`"
                                                class="w-6 h-6 rounded-full object-cover border border-slate-200 shadow-sm"
                                                />
                                                <span class="text-sm font-medium text-slate-900">{{ session.network || '—' }}</span>
                                            </div>
                                        </td>

                                        <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-slate-900 hover:text-indigo-600 transition-colors">
                                            {{ session.msisdn || '—' }}
                                        </td>

                                        <td class="px-6 py-5 whitespace-nowrap text-sm text-slate-600">
                                            {{ formatDuration(session.total_duration_ms) || '—' }}
                                        </td>

                                        <td class="px-6 py-5 whitespace-nowrap text-sm text-slate-500">
                                            {{ formattedRelativeDate(session.created_at) }}
                                        </td>

                                        <td class="px-6 py-5 text-center">
                                            <span
                                                :class="session.successful
                                                ? 'bg-emerald-100 text-emerald-800'
                                                : 'bg-rose-100 text-rose-800'"
                                                class="inline-flex px-3 py-1 text-xs font-semibold rounded-full">
                                                {{ session.successful ? 'Success' : 'Failed' }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-5 text-center text-sm font-semibold text-slate-700">
                                            {{ session.total_steps || '—' }}
                                        </td>

                                        <td class="px-6 py-5 text-center">
                                            <span
                                                :class="session.simulated
                                                ? 'bg-amber-100 text-amber-800'
                                                : 'bg-indigo-100 text-indigo-800'"
                                                class="inline-flex px-3 py-1 text-xs font-semibold rounded-full">
                                                {{ session.simulated ? 'Simulator' : 'Mobile' }}
                                            </span>
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

                <!-- Right Sidebar -->
                <div class="lg:col-span-3 space-y-4">

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

                    <!-- Quick Links -->
                    <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100 overflow-hidden shadow-sm">

                        <router-link
                            :to="{ name: 'show-app-sessions', params: { app_id: app.id } }"
                            class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3 text-sm text-slate-700 font-medium">
                                <List size="18" />
                                Sessions
                            </div>
                            <ArrowRight size="16" class="text-slate-400" />
                        </router-link>

                        <router-link
                            :to="{ name: 'show-app-accounts', params: { app_id: app.id } }"
                            class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3 text-sm text-slate-700 font-medium">
                                <Users size="18" />
                                Accounts
                            </div>
                            <ArrowRight size="16" class="text-slate-400" />
                        </router-link>

                        <router-link
                            :to="{ name: 'show-app-analytics', params: { app_id: app.id } }"
                            class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3 text-sm text-slate-700 font-medium">
                                <ChartArea size="18" />
                                Analytics
                            </div>
                            <ArrowRight size="16" class="text-slate-400" />
                        </router-link>

                        <router-link
                            :to="{ name: 'show-app-issues', params: { app_id: app.id } }"
                            class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3 text-sm text-slate-700 font-medium">
                                <BadgeAlert size="18" />
                                Flags & Issues
                            </div>
                            <ArrowRight size="16" class="text-slate-400" />
                        </router-link>

                    </div>

                </div>

            </div>

        </main>

    </div>

</template>

<script>
    import axios from 'axios';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { formattedRelativeDate } from '@Utils/dateUtils';
    import { formatDuration, capitalize } from '@Utils/stringUtils';
    import { BadgeAlert, RefreshCcw, ArrowRight, ChartArea, List, Users, Frown } from 'lucide-vue-next';

    export default {
        name: 'Home',
        components: { Button, Skeleton, BadgeAlert, ArrowRight, ChartArea, List, Users, Frown },
        inject: ['appState', 'notificationState'],
        data() {
            return {
                RefreshCcw,
                summary: null,
                recentSessions: [],
                isLoadingSummary: false,
                isLoadingSessions: false,
            };
        },
        computed: {
            app() {
                return this.appState.app;
            },
        },
        methods: {
            capitalize,
            formatDuration,
            formattedRelativeDate,
            async navigateToSession(sessionId) {
                await this.$router.push({
                    name: 'show-app-session',
                    params: { app_id: this.app.id, session_id: sessionId },
                });
            },
            priorityColorClasses(p) {
                const map = {
                    critical: 'bg-rose-100 text-rose-800 ring-1 ring-rose-300',
                    high:     'bg-orange-100 text-orange-800 ring-1 ring-orange-300',
                    medium:   'bg-amber-100 text-amber-800 ring-1 ring-amber-300',
                    low:      'bg-sky-100 text-sky-800 ring-1 ring-sky-300',
                };
                return map[p?.toLowerCase()] || 'bg-slate-100 text-slate-700';
            },
            getCategoryLabel(cat) {
                const map = {
                    bug:              'Bug / Technical Error',
                    ux:               'UX / Flow Confusion',
                    content:          'Menu Text / Wording Issue',
                    performance:      'Performance / Slow Response',
                    security:         'Security / Compliance Concern',
                    'feature-request': 'Feature Suggestion',
                    other:            'Other',
                };
                return map[cat] || cat.charAt(0).toUpperCase() + cat.slice(1);
            },
            async refreshDashboard() {
                this.fetchSummary();
                this.fetchSessions();
            },
            async fetchSummary() {
                this.isLoadingSummary = true;
                try {
                    const res = await axios.get('/api/home/summary', {
                        params: { app_id: this.app.id },
                    });
                    this.summary = res.data;
                } catch (err) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching dashboard summary';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch dashboard summary:', error);
                } finally {
                    this.isLoadingSummary = false;
                }
            },
            async fetchSessions() {
                this.isLoadingSessions = true;
                try {
                    const res = await axios.get(`/api/apps/${this.app.id}/ussd-sessions`, {
                        params: {
                            per_page: 5,
                        },
                    });
                    this.recentSessions = res.data.data || [];
                } catch (err) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching recent sessions';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch recent sessions:', error);
                } finally {
                    this.isLoadingSessions = false;
                }
            },
        },
        mounted() {
            this.fetchSummary();
            this.fetchSessions();
        },
    };
</script>
