<template>

    <div class="min-h-screen bg-slate-50 pb-80">

        <!-- Header -->
        <div class="bg-white border-b border-slate-200">

            <div class="max-w-7xl mx-auto px-6 py-4">

                <div class="flex items-center justify-between gap-6">

                    <!-- Left: Back + Title -->
                    <div class="flex items-center gap-4">

                        <Button
                            size="xs"
                            type="basic"
                            rightIconSize="24"
                            :rightIcon="ArrowLeft"
                            buttonClass="rounded-lg"
                            :action="goBackToVersions">
                        </Button>

                        <div>
                            <h1 class="text-2xl font-semibold text-slate-900">Builder</h1>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ app.name }} • version {{ version?.number || '...' }}
                            </p>
                        </div>

                    </div>

                    <!-- Middle: Tabs -->
                    <div class="flex-1 flex justify-center">
                        <Tabs v-model="tab" :tabs="tabs"></Tabs>
                    </div>

                    <!-- Right: Add to canvas + Save + Actions Dropdown -->
                    <div class="flex items-center gap-3">

                        <Button
                            size="md"
                            mode="solid"
                            type="primary"
                            :leftIcon="Plus"
                            buttonClass="rounded-lg"
                            :action="() => versionState.canvasActionsModal?.showModal()">
                            <span class="ml-1">Add</span>
                        </Button>

                        <Button
                            size="md"
                            type="light"
                            mode="outline"
                            :leftIcon="Save"
                            :action="updateVersion"
                            buttonClass="rounded-lg"
                            :loading="isSavingChanges">
                            <span class="ml-1">Save</span>
                        </Button>

                        <Dropdown position="left" dropdownClasses="w-44">
                            <template #trigger="props">
                                <button
                                    type="button"
                                    @click="props.toggleDropdown"
                                    class="inline-flex items-center justify-center w-10 h-12 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:scale-105 active:scale-100 transition-all duration-300">
                                    <EllipsisVertical size="16" />
                                </button>
                            </template>

                            <template #content="props">
                                <div class="py-1">
                                    <div
                                        @click="(event) => { openVersionDetailsModal(); props.toggleDropdown(event); }"
                                        class="px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 cursor-pointer">
                                        Edit version
                                    </div>
                                    <div
                                        @click="(event) => { openDeleteVersionModal(); props.toggleDropdown(event); }"
                                        class="px-4 py-2.5 text-sm text-rose-700 hover:bg-rose-50 cursor-pointer">
                                        Delete version
                                    </div>
                                </div>
                            </template>
                        </Dropdown>

                    </div>

                </div>

            </div>

        </div>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-6 py-4">

            <!-- Loading State -->
            <div v-if="isLoadingVersion" class="flex justify-center items-center h-96">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
            </div>

            <!-- Content (only show when version is loaded) -->
            <template v-else>

                <!-- Classic Mode -->
                <div v-if="tab == 'classic'">

                </div>

                <!-- Visual Flow Mode -->
                <div v-else class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" style="height: 70vh;">

                        <VueFlow
                            :nodes="nodes"
                            :edges="edges"
                            @pane-ready="onPaneReady"
                            @connect="(p) => versionState.onConnect(p)"
                            @nodes-change="(c) => versionState.onNodesChange(c)"
                            @edges-change="(c) => versionState.onEdgesChange(c)">

                        <template #node-special="specialNodeProps">
                            <StepNode v-bind="specialNodeProps"></StepNode>
                        </template>

                        <template #edge-special="specialEdgeProps">
                            <StepEdge v-bind="specialEdgeProps"></StepEdge>
                        </template>

                        <MiniMap />
                        <Controls />
                        <Background pattern-color="#e2e8f0" gap="20" />

                    </VueFlow>

                </div>

                {{versionForm}}

            </template>

        </main>

        <FeaturesModal ref="featuresModal" />
        <StepEditModal ref="stepEditModal" />
        <InputFeatureModal ref="inputFeatureModal" />
        <SelectFeatureModal ref="selectFeatureModal" />
        <DeleteVersionModal ref="deleteVersionModal" />
        <CanvasActionsModal ref="canvasActionsModal" />
        <VersionDetailsModal ref="versionDetailsModal" />
        <CodeContentEditorModal ref="codeContentEditorModal" />
        <BasicContentEditorModal ref="basicContentEditorModal" />
    </div>

</template>

<script>

    import axios from 'axios';
    import Tabs from '@Partials/Tabs.vue';
    import { VueFlow } from '@vue-flow/core';
    import Button from '@Partials/Button.vue';
    import '@vue-flow/controls/dist/style.css';
    import { MiniMap } from '@vue-flow/minimap';
    import { Controls } from '@vue-flow/controls';
    import Dropdown from '@Partials/Dropdown.vue';
    import { Background } from '@vue-flow/background';
    import { Save, Plus, ArrowLeft, EllipsisVertical } from 'lucide-vue-next';
    import StepNode from '@Pages/versions/_components/step-node/StepNode.vue';
    import StepEdge from '@Pages/versions/_components/step-edge/StepEdge.vue';
    import FeaturesModal from '@Pages/versions/_components/modals/FeaturesModal.vue';
    import StepEditModal from '@Pages/versions/_components/modals/StepEditModal.vue';
    import InputFeatureModal from '@Pages/versions/_components/modals/InputFeatureModal.vue';
    import SelectFeatureModal from '@Pages/versions/_components/modals/SelectFeatureModal.vue';
    import DeleteVersionModal from '@Pages/versions/_components/modals/DeleteVersionModal.vue';
    import CanvasActionsModal from '@Pages/versions/_components/modals/CanvasActionsModal.vue';
    import VersionDetailsModal from '@Pages/versions/_components/modals/VersionDetailsModal.vue';
    import CodeContentEditorModal from '@Pages/versions/_components/modals/CodeContentEditorModal.vue';
    import BasicContentEditorModal from '@Pages/versions/_components/modals/BasicContentEditorModal.vue';

    export default {
        name: 'Version',
        components: {
            Plus, ArrowLeft, EllipsisVertical,
            VueFlow, MiniMap, Controls, Background,
            Tabs, Button, Dropdown, StepNode, StepEdge,
            FeaturesModal, CanvasActionsModal, InputFeatureModal, SelectFeatureModal, BasicContentEditorModal,
            CodeContentEditorModal, VersionDetailsModal, DeleteVersionModal, StepEditModal,
        },
        inject: ['appState', 'versionState', 'formState', 'notificationState'],
        data() {
            return {
                Plus,
                Save,
                ArrowLeft,
                tab: 'visual',
                tabs: [
                    { label: 'Classic', value: 'classic' },
                    { label: 'Visual Flow', value: 'visual' }
                ],
                isSavingChanges: false
            };
        },
        computed: {
            app() {
                return this.appState.app;
            },
            nodes() {
                return this.versionState.nodes;
            },
            edges() {
                return this.versionState.edges;
            },
            version() {
                return this.versionState.version;
            },
            versionId() {
                return this.$route.params.version_id;
            },
            versionForm() {
                return this.versionState.versionForm || {};
            },
            isLoadingVersion() {
                return this.versionState.isLoadingVersion;
            },
            isUpdatingVersion() {
                return this.versionState.isUpdatingVersion;
            },
        },
        methods: {
            async goBackToVersions() {
                await this.$router.push({
                    name: 'show-app-versions',
                    params: {
                        app_id: this.app.id,
                    },
                });
            },
            openVersionDetailsModal() {
                this.versionState.versionDetailsModal?.showModal();
            },
            openDeleteVersionModal() {
                this.versionState.deleteVersionModal?.showModal();
            },
            onPaneReady(instance) {
                this.versionState.vueFlowInstance = instance;
            },
            async showVersion() {
                try {
                    this.versionState.isLoadingVersion = true;

                    const config = {
                        params: {
                            _relationships: ['app'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/apps/${this.app.id}/versions/${this.versionId}`, config);
                    const version = response.data;

                    this.versionState.setVersion(version);
                    this.versionState.setVersionForm(version);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching version';
                    this.notificationState.showWarningNotification(message);
                    console.error('Failed to fetch version:', error);

                    if (error.response?.status === 404) {
                        await this.$router.replace({
                            name: 'show-app-versions',
                            params: {
                                app_id: this.app.id
                            }
                        });
                    }

                } finally {
                    this.versionState.isLoadingVersion = false;
                }
            },
            async updateVersion() {
                if (this.versionState.isUpdatingVersion) return;

                try {
                    this.versionState.isUpdatingVersion = true;

                    const payload = { ...this.versionState.versionForm };

                    const response = await axios.put(`/api/apps/${this.app.id}/versions/${this.versionId}`, payload);
                    const updatedVersion = response.data?.version;

                    this.versionState.setVersion(updatedVersion);
                    this.versionState.setVersionForm(updatedVersion);

                    this.notificationState.showSuccessNotification('Changes saved!');
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Failed to save changes';
                    this.notificationState.showWarningNotification(message);
                    console.error('Failed to save changes:', error);
                } finally {
                    this.versionState.isUpdatingVersion = false;
                }
            }
        },
        mounted() {
            this.$nextTick(() => {
                this.versionState.featuresModal = this.$refs.featuresModal.$refs.modal;
                this.versionState.stepEditModal = this.$refs.stepEditModal.$refs.modal;
                this.versionState.stepEditModal = this.$refs.stepEditModal.$refs.modal;
                this.versionState.inputFeatureModal = this.$refs.inputFeatureModal.$refs.modal;
                this.versionState.selectFeatureModal = this.$refs.selectFeatureModal.$refs.modal;
                this.versionState.deleteVersionModal = this.$refs.deleteVersionModal.$refs.modal;
                this.versionState.canvasActionsModal = this.$refs.canvasActionsModal.$refs.modal;
                this.versionState.versionDetailsModal = this.$refs.versionDetailsModal.$refs.modal;
                this.versionState.codeContentEditorModal = this.$refs.codeContentEditorModal.$refs.modal;
                this.versionState.basicContentEditorModal = this.$refs.basicContentEditorModal.$refs.modal;
            });
        },
        created() {
            this.showVersion();
        },
    };
</script>

<style scoped>
.collapsible-enter-active,
.collapsible-leave-active {
    transition: all 0.18s ease-out;
}
.collapsible-enter-from,
.collapsible-leave-to {
    opacity: 0;
    max-height: 0;
    transform: translateY(-4px);
}
.collapsible-enter-to,
.collapsible-leave-from {
    opacity: 1;
    max-height: 320px;
    transform: translateY(0);
}
</style>
