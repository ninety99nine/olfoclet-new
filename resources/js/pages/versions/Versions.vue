<template>
    <div class="min-h-screen bg-slate-50 pb-12">

        <!-- Header -->
        <div class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-6 py-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900">Versions</h1>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ app.name }} • Manage your USSD flow versions
                        </p>
                    </div>

                    <Button
                        v-if="!isLoadingVersions"
                        size="md"
                        type="primary"
                        mode="solid"
                        :leftIcon="Plus"
                        :action="openNewVersionModal"
                        :loading="isCreatingVersion"
                        buttonClass="rounded-lg shadow-sm">
                        New Version
                    </Button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-6 py-8">

            <!-- Loading -->
            <div v-if="isLoadingVersions" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
            </div>

            <!-- Empty State (direct create, no modal) -->
            <div v-else-if="!versions.length && !pagination?.meta?.total" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12 text-center">
                <div class="flex flex-col items-center mx-auto max-w-md">
                    <div class="mb-6">
                        <svg class="mx-auto h-20 w-20 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>

                    <h3 class="text-2xl font-bold text-slate-900 mb-3">No versions yet</h3>

                    <p class="text-slate-600 mb-4">
                        Start building your USSD flow by creating your first version.
                    </p>

                    <p class="text-slate-500 mb-8 italic">
                        Every great service begins with version 1.
                    </p>

                    <Button
                        size="lg"
                        type="primary"
                        mode="solid"
                        :action="createNewVersion"
                        buttonClass="rounded-full shadow-md px-10 py-3 text-base font-medium hover:scale-105 transition-transform">
                        <span class="flex items-center gap-2">
                            <Plus size="18" />
                            Create First Version
                        </span>
                    </Button>

                    <p class="mt-6 text-sm text-slate-500">
                        You can create and switch between versions anytime.
                    </p>
                </div>
            </div>

            <!-- Versions List -->
            <div v-else class="bg-white rounded-2xl border border-slate-200 shadow-sm">

                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-slate-900">All Versions</h2>
                    <span class="text-sm text-slate-500">
                        {{ pagination?.meta?.total?.toLocaleString() || 0 }} version{{ pagination?.meta?.total === 1 ? '' : 's' }}
                    </span>
                </div>

                <!-- Table -->
                <div>
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Version
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Deployments
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Created
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Last Modified
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    By
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="version in versions"
                                :key="version.id"
                                class="group hover:bg-slate-50 transition-colors cursor-pointer"
                                @click="openInBuilder(version)">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                    <div class="flex space-x-2 items-center">
                                        <div>{{ version.number }}</div>
                                        <Popover
                                            trigger="hover"
                                            position="bottom"
                                            v-if="version.description"
                                            class="opacity-0 group-hover:opacity-100 duration-300 transition-all">

                                            <template #content>
                                                <div class="p-3 min-w-[220px]">
                                                    <p class="text-xs font-medium text-slate-800 mb-2">Description</p>
                                                    <p class="text-xs text-slate-500">{{ version.description }}</p>
                                                </div>
                                            </template>
                                        </Popover>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <Popover
                                        v-if="version.deployments?.length"
                                        trigger="hover"
                                        position="bottom">
                                        <template #trigger>
                                            <div class="flex items-center gap-2 cursor-help">
                                                <span :class="[
                                                    'inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full',
                                                    version.deployments_count > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'
                                                ]">
                                                    {{ version.deployments_count || 0 }}
                                                </span>
                                                <span v-if="version.deployments_count > 0" class="text-xs text-emerald-700">
                                                    live
                                                </span>
                                                <span v-else class="text-xs text-slate-500 italic">
                                                    not deployed
                                                </span>
                                            </div>
                                        </template>

                                        <template #content>
                                            <div class="p-3 min-w-[220px]">
                                                <p class="text-xs font-medium text-slate-800 mb-2">Active on:</p>
                                                <ul class="text-xs text-slate-600 space-y-2">
                                                    <li v-for="dep in version.deployments" :key="dep.id" class="flex items-center gap-2">
                                                        <img
                                                            v-if="dep.country"
                                                            :src="`/svgs/country-flags/${dep.country.toLowerCase()}.svg`"
                                                            alt=""
                                                            class="w-5 h-5 rounded-full object-cover border border-slate-200 shadow-sm"
                                                        />
                                                        <span class="font-medium">
                                                            {{ getCountryName(dep.country) || dep.country || 'Unknown' }}
                                                        </span>
                                                        <span class="text-slate-500">•</span>
                                                        <span>{{ dep.network || '—' }}</span>
                                                        <span v-if="!dep.active" class="text-rose-600 text-xs">(inactive)</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </template>
                                    </Popover>

                                    <span v-else class="text-xs text-slate-500 italic">
                                        not deployed
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span :class="[
                                        'inline-flex px-3 py-1 text-xs font-medium rounded-full',
                                        version.status === 'live' ? 'bg-emerald-100 text-emerald-800' :
                                        version.status === 'draft' ? 'bg-amber-100 text-amber-800' :
                                        'bg-slate-100 text-slate-700'
                                    ]">
                                        {{ version.status || 'Draft' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ formattedRelativeDate(version.created_at) }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ formattedRelativeDate(version.updated_at) }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-medium text-indigo-500">
                                            {{ version.created_by?.first_name?.charAt(0)?.toUpperCase() || '?' }}
                                        </div>
                                        <span class="text-sm text-slate-700">
                                            {{ version.created_by?.first_name || 'You' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" @click.stop>
                                    <div class="flex items-center justify-end gap-3">
                                        <Button
                                            size="xs"
                                            type="light"
                                            mode="outline"
                                            loaderType="primary"
                                            :loading="buildingVersionId === version.id"
                                            :action="() => openInBuilder(version)"
                                            buttonClass="rounded-lg">
                                            Build
                                        </Button>

                                        <Button
                                            size="xs"
                                            type="danger"
                                            mode="outline"
                                            :loading="deletingVersions.includes(version.id)"
                                            :action="() => confirmDelete(version)"
                                            buttonClass="rounded-lg">
                                            Delete
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="pagination?.meta?.total >= 10"
                    class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-sm text-slate-600">
                    <div>
                        Showing
                        <strong>{{ pagination?.meta?.from || 0 }}–{{ pagination?.meta?.to || 0 }}</strong>
                        of
                        <strong>{{ pagination?.meta?.total?.toLocaleString() || 0 }}</strong>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap justify-center sm:justify-end">
                        <template v-for="(link, index) in pagination?.meta?.links" :key="index">
                            <button
                                :disabled="!link.url"
                                @click="changePage(link)"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-1.5 rounded-lg border min-w-[40px] text-center transition-colors',
                                    link.active
                                        ? 'border-indigo-600 bg-indigo-600 text-white font-medium'
                                        : 'border-slate-300 text-slate-600 hover:bg-slate-100 hover:border-slate-400',
                                    !link.url && 'opacity-50 cursor-not-allowed'
                                ]">
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Version Modal -->
        <Modal
            size="md"
            ref="newVersionModal"
            :showFooter="false"
            :scrollOnContent="false">
            <template #content>
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-6">
                    Create New Version
                </p>

                <div class="space-y-6">
                    <!-- Version Number -->
                    <Input
                        label="Version Number"
                        v-model="newVersionForm.number"
                        placeholder="e.g. v2, v3.1, beta-1"
                        :errorText="formErrors.number"
                        required
                    />

                    <!-- Description -->
                    <Input
                        label="Description (optional)"
                        type="textarea"
                        rows="3"
                        v-model="newVersionForm.description"
                        placeholder="e.g. Added new payment menu and error handling"
                    />

                    <!-- Clone From -->
                    <Select
                        label="Clone from existing version (optional)"
                        v-model="newVersionForm.clone_from_id"
                        :options="cloneOptions"
                        placeholder="None / Start from blank"
                        clearable
                    />
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <Button
                        size="md"
                        type="light"
                        mode="outline"
                        :action="closeNewVersionModal"
                        buttonClass="rounded-lg">
                        Cancel
                    </Button>
                    <Button
                        size="md"
                        type="primary"
                        mode="solid"
                        :loading="isCreatingVersion"
                        :action="createVersionFromModal"
                        buttonClass="rounded-lg">
                        Create Version
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal
            size="sm"
            ref="deleteModal"
            :showFooter="false"
            :scrollOnContent="false">
            <template #content>
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">
                    Delete Version
                </p>
                <div class="py-2">
                    <p class="text-slate-700 leading-relaxed">
                        Are you sure you want to permanently delete
                        <strong class="text-slate-900">version {{ versionToDelete?.number }}</strong>?
                    </p>
                    <p class="mt-3 text-rose-600 font-medium">
                        This action cannot be undone.
                    </p>
                    <p class="mt-2 text-slate-500 text-sm">
                        Any deployments using this version will stop working.
                    </p>
                </div>
                <div class="mt-8 flex justify-end space-x-3">
                    <Button
                        size="md"
                        type="light"
                        mode="outline"
                        :action="closeDeleteModal"
                        buttonClass="rounded-lg">
                        Cancel
                    </Button>
                    <Button
                        size="md"
                        type="danger"
                        mode="solid"
                        :loading="deletingVersions.includes(versionToDelete?.id)"
                        :action="deleteVersion"
                        buttonClass="rounded-lg">
                        Delete Version
                    </Button>
                </div>
            </template>
        </Modal>
    </div>
</template>

<script>
import { Plus } from 'lucide-vue-next';
import Button from '@Partials/Button.vue';
import Modal from '@Partials/Modal.vue';
import Input from '@Partials/Input.vue';
import Select from '@Partials/Select.vue';
import Popover from '@Partials/Popover.vue';
import countries from '@Json/countries.json';
import { formattedRelativeDate } from '@Utils/dateUtils';

export default {
    name: 'Versions',
    components: { Button, Modal, Plus, Popover, Input, Select },
    inject: ['appState', 'notificationState'],
    data() {
        return {
            versions: [],
            pagination: null,
            isLoadingVersions: false,
            isCreatingVersion: false,
            buildingVersionId: null,
            deletingVersions: [],
            versionToDelete: null,
            currentPage: 1,
            // New version modal
            showNewVersionModal: false,
            newVersionForm: {
                number: '',
                description: '',
                clone_from_id: null,
            },
            formErrors: {},
        };
    },
    computed: {
        app() {
            return this.appState.app;
        },
        cloneOptions() {
            const options = this.versions.map(v => ({
                label: `v${v.number}${v.description ? ' — ' + v.description : ''}`,
                value: v.id,
            }));
            return [
                { label: 'None / Start from blank', value: null },
                ...options,
            ];
        },
    },
    methods: {
        formattedRelativeDate,
        getCountryName(code) {
            if (!code) return null;
            const country = countries.find(c => c.iso === code.toUpperCase());
            return country ? country.name : null;
        },

        openNewVersionModal() {
            const nextNum = (this.pagination?.meta?.total || 0) + 1;
            this.newVersionForm = {
                number: `v${nextNum}`,
                description: '',
                clone_from_id: null,
            };
            this.formErrors = {};
            this.$refs.newVersionModal.showModal();
        },

        closeNewVersionModal() {
            this.$refs.newVersionModal.hideModal();
        },

        async createVersionFromModal() {
            this.isCreatingVersion = true;
            this.formErrors = {};

            if (!this.newVersionForm.number.trim()) {
                this.formErrors.number = 'Version number is required';
                this.isCreatingVersion = false;
                return;
            }

            try {
                const payload = {
                    number: this.newVersionForm.number.trim(),
                    clone_from_id: this.newVersionForm.clone_from_id || null,
                    description: this.newVersionForm.description.trim() || null,
                };

                const response = await axios.post(`/api/apps/${this.app.id}/versions`, payload);
                const newVersion = response.data;

                this.notificationState.showSuccessNotification('New version created!');

                await this.fetchVersions(1);

                this.closeNewVersionModal();
            } catch (error) {
                const message = error?.response?.data?.message || 'Failed to create version';
                this.notificationState.showWarningNotification(message);

                if (error.response?.data?.errors) {
                    this.formErrors = error.response.data.errors;
                }
                console.error('Failed to create version:', error);
            } finally {
                this.isCreatingVersion = false;
            }
        },
        async createNewVersion() {
            this.isCreatingVersion = true;
            try {
                const response = await axios.post(`/api/apps/${this.app.id}/versions`, {
                    number: `v${(this.pagination?.meta?.total || 0) + 1}`,
                });

                this.notificationState.showSuccessNotification('New version created!');
                await this.fetchVersions(1);
            } catch (error) {
                const message = error?.response?.data?.message || 'Failed to create version';
                this.notificationState.showWarningNotification(message);
                console.error(error);
            } finally {
                this.isCreatingVersion = false;
            }
        },

        async fetchVersions(page = 1) {
            try {
                this.isLoadingVersions = true;
                this.currentPage = page;

                const response = await axios.get(`/api/apps/${this.app.id}/versions`, {
                    params: {
                        page,
                        per_page: 15,
                        _relationships: ['createdBy', 'deployments'].join(','),
                    },
                });

                this.pagination = response.data;
                this.versions = this.pagination.data || [];
            } catch (error) {
                const message = error?.response?.data?.message || 'Failed to load versions';
                this.notificationState.showWarningNotification(message);
                console.error('Failed to fetch versions:', error);
            } finally {
                this.isLoadingVersions = false;
            }
        },
        async openInBuilder(version) {
            this.buildingVersionId = version.id;

            this.$router.push({
                name: 'show-app-version',
                params: {
                    app_id: this.app.id,
                    version_id: version.id,
                },
            }).catch(() => {
                // If navigation fails, reset the loader
                this.buildingVersionId = null;
            });
        },
        confirmDelete(version) {
            this.versionToDelete = version;
            this.$refs.deleteModal.showModal();
        },

        closeDeleteModal() {
            this.$refs.deleteModal.hideModal();
            this.versionToDelete = null;
        },

        async deleteVersion() {
            if (!this.versionToDelete) return;

            const versionId = this.versionToDelete.id;
            this.deletingVersions.push(versionId);

            try {
                await axios.delete(`/api/apps/${this.app.id}/versions/${versionId}`);

                this.notificationState.showSuccessNotification('Version deleted');

                await this.fetchVersions(this.currentPage);

                if (!this.versions.length && this.currentPage > 1) {
                    await this.fetchVersions(this.currentPage - 1);
                }
            } catch (error) {
                const message = error?.response?.data?.message || 'Failed to delete version';
                this.notificationState.showWarningNotification(message);
                console.error('Failed to delete version:', error);
            } finally {
                this.deletingVersions = this.deletingVersions.filter(id => id !== versionId);
                this.closeDeleteModal();
            }
        },

        changePage(link) {
            if (link.url) {
                const url = new URL(link.url);
                const page = url.searchParams.get('page');
                if (page) this.fetchVersions(Number(page));
            }
        },
    },
    mounted() {
        this.fetchVersions(1);
    },
};
</script>
