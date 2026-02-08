<template>
    <div class="min-h-screen bg-slate-50 flex overflow-hidden relative">

        <div class="flex-1 flex flex-col min-w-0 pb-80 transition-all duration-300">
            <div class="bg-white border-b border-slate-200">
                <div class="max-w-7xl mx-auto px-6 py-4">
                    <div class="flex items-center justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <Button size="xs" type="basic" rightIconSize="24" :rightIcon="ArrowLeft" buttonClass="rounded-lg" :action="goBackToVersions" />
                            <div>
                                <h1 class="text-2xl font-semibold text-slate-900">Builder</h1>
                                <p class="mt-1 text-sm text-slate-500">{{ app.name }} • version {{ version?.number || '...' }}</p>
                            </div>
                        </div>

                        <div class="flex-1 flex justify-center">
                            <Tabs v-model="tab" :tabs="tabs"></Tabs>
                        </div>

                        <div class="flex items-center gap-3">
                            <Button size="md" mode="solid" type="primary" :leftIcon="Plus" buttonClass="rounded-lg" :action="() => versionState.canvasActionsModal?.showModal()">
                                <span class="ml-1">Add</span>
                            </Button>

                            <Button size="md" type="basic" :leftIcon="Wand2" :action="() => versionState.autoLayoutNodes()" buttonClass="rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 shadow-sm">
                                <span class="ml-1">Arrange</span>
                            </Button>

                            <Button size="md" type="light" mode="outline" :leftIcon="Save" :action="updateVersion" buttonClass="rounded-lg" :loading="isSavingChanges">
                                <span class="ml-1">Save</span>
                            </Button>

                            <Dropdown position="left" dropdownClasses="w-44">
                                <template #trigger="props">
                                    <button type="button" @click="props.toggleDropdown" class="inline-flex items-center justify-center w-10 h-12 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:scale-105 active:scale-100 transition-all duration-300">
                                        <EllipsisVertical size="16" />
                                    </button>
                                </template>
                                <template #content="props">
                                    <div class="py-1">
                                        <div @click="(event) => { openVersionDetailsModal(); props.toggleDropdown(event); }" class="px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 cursor-pointer">Edit version</div>
                                        <div @click="(event) => { openDeleteVersionModal(); props.toggleDropdown(event); }" class="px-4 py-2.5 text-sm text-rose-700 hover:bg-rose-50 cursor-pointer">Delete version</div>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>

            <main class="max-w-7xl mx-auto px-6 py-4 w-full">
                <div v-if="isLoadingVersion" class="flex justify-center items-center h-96">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                </div>

                <template v-else>
                    <div v-if="tab == 'classic'"></div>
                    <div v-else class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" style="height: 70vh;">
                        <VueFlow :nodes="nodes" :edges="edges" :max-zoom="1.5" :min-zoom="0.05" :fit-view-on-init="true" @pane-ready="onPaneReady" @connect="(p) => versionState.onConnect(p)" @nodes-change="(c) => versionState.onNodesChange(c)" @edges-change="(c) => versionState.onEdgesChange(c)">
                            <template #node-special="specialNodeProps"><StepNode v-bind="specialNodeProps" /></template>
                            <template #edge-special="specialEdgeProps"><StepEdge v-bind="specialEdgeProps" /></template>
                            <MiniMap /><Controls /><Background pattern-color="#e2e8f0" gap="20" />
                        </VueFlow>
                    </div>
                </template>
            </main>
        </div>

        <div
            class="fixed bottom-0 right-8 z-50 flex flex-col items-end transition-all duration-500 ease-in-out"
            :class="[showSimulator ? 'translate-y-0' : 'translate-y-full']"
        >
            <button
                v-if="!showSimulator"
                @click="showSimulator = true"
                class="flex items-center gap-3 px-6 py-3 bg-linear-to-br from-indigo-600 to-violet-600 text-white rounded-t-2xl hover:-translate-y-[43px] transition-all duration-300 group cursor-pointer -translate-y-[40px] border-t border-x border-white/20">
                <div class="rounded-lg group-hover:scale-110 transition-transform duration-300">
                    <Smartphone size="18" class="text-white" />
                </div>
                <span class="text-xs font-medium tracking-widest uppercase">Test Flow</span>
                <ChevronUp size="18" class="-mb-1 ml-1 animate-bounce" />
            </button>

            <div class="w-[420px] h-[720px] border-x border-t border-slate-300 shadow-[0_-20px_50px_-12px_rgba(0,0,0,0.3)] rounded-t-3xl flex flex-col overflow-hidden bg-slate-50/80 backdrop-blur-xl relative">

                <button
                    @click="showSimulator = false"
                    class="absolute top-5 right-4 z-60 p-2.5 bg-white/40 hover:bg-rose-500 hover:text-white rounded-full text-slate-500 transition-all duration-300 border border-slate-100 hover:border-transparent backdrop-blur-md shadow-sm group cursor-pointer"
                >
                    <X size="18" class="group-hover:rotate-90 transition-transform duration-300" />
                </button>

                <div class="flex-1 p-6 overflow-y-auto custom-scrollbar">
                    <SimulatorPhone
                        v-if="version?.id"
                        :versionId="version.id"
                        phoneNumber="26771234567"
                        @close="showSimulator = false"
                    />
                </div>
            </div>
        </div>

        <FeaturesModal ref="featuresModal" />
        <StepEditModal ref="stepEditModal" />
        <EventPickerModal ref="eventPickerModal" />
        <RestApiEventModal ref="restApiEventModal" />
        <SoapApiEventModal ref="soapApiEventModal" />
        <InputFeatureModal ref="inputFeatureModal" />
        <SelectFeatureModal ref="selectFeatureModal" />
        <DeleteVersionModal ref="deleteVersionModal" />
        <CanvasActionsModal ref="canvasActionsModal" />
        <DecisionPointModal ref="decisionPointModal" />
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
    import SimulatorPhone from '@Components/SimulatorPhone.vue';
    import {
        Save, Plus, ArrowLeft, EllipsisVertical, Wand2,
        Smartphone, ChevronUp, ChevronDown, X
    } from 'lucide-vue-next';

    import StepNode from '@Pages/versions/_components/step-node/StepNode.vue';
    import StepEdge from '@Pages/versions/_components/step-edge/StepEdge.vue';
    import FeaturesModal from '@Pages/versions/_components/modals/FeaturesModal.vue';
    import StepEditModal from '@Pages/versions/_components/modals/StepEditModal.vue';
    import EventPickerModal from '@Pages/versions/_components/modals/EventPickerModal.vue';
    import InputFeatureModal from '@Pages/versions/_components/modals/InputFeatureModal.vue';
    import DecisionPointModal from '@Pages/versions/_components/modals/DecisionPointModal.vue';
    import SelectFeatureModal from '@Pages/versions/_components/modals/SelectFeatureModal.vue';
    import DeleteVersionModal from '@Pages/versions/_components/modals/DeleteVersionModal.vue';
    import CanvasActionsModal from '@Pages/versions/_components/modals/CanvasActionsModal.vue';
    import VersionDetailsModal from '@Pages/versions/_components/modals/VersionDetailsModal.vue';
    import CodeContentEditorModal from '@Pages/versions/_components/modals/CodeContentEditorModal.vue';
    import BasicContentEditorModal from '@Pages/versions/_components/modals/BasicContentEditorModal.vue';
    import RestApiEventModal from '@Pages/versions/_components/modals/api-event-modal/RestApiEventModal.vue';
    import SoapApiEventModal from '@Pages/versions/_components/modals/api-event-modal/SoapApiEventModal.vue';

    export default {
        name: 'Version',
        components: {
            Plus, ArrowLeft, EllipsisVertical, Wand2, Smartphone, ChevronUp, ChevronDown, X,
            VueFlow, MiniMap, Controls, Background,
            Tabs, Button, Dropdown, StepNode, StepEdge, SimulatorPhone,
            FeaturesModal, RestApiEventModal, SoapApiEventModal, EventPickerModal, CanvasActionsModal, InputFeatureModal,
            SelectFeatureModal, BasicContentEditorModal, CodeContentEditorModal, VersionDetailsModal, DeleteVersionModal,
            StepEditModal, DecisionPointModal
        },
        inject: ['appState', 'versionState', 'formState', 'notificationState'],
        data() {
            return {
                Plus, Save, ArrowLeft, Wand2, Smartphone, ChevronUp, ChevronDown, X,
                tab: 'visual',
                tabs: [
                    { label: 'Classic', value: 'classic' },
                    { label: 'Visual Flow', value: 'visual' }
                ],
                isSavingChanges: false,
                showSimulator: false
            };
        },
        watch: {
            showSimulator(isOpen) {
                if (!isOpen) {
                    // 1. Clear the red/green/blue path highlights
                    this.versionState.clearSimulatorPath();

                    // 2. Wait for the drawer animation (500ms) to finish, then re-organize
                    setTimeout(() => {
                        this.versionState.autoLayoutNodes({
                            zoom: true,       // Re-center the camera
                            autoLayout: true  // Snap nodes back to clean columns
                        });
                    }, 500);
                }
            }
        },
        computed: {
            app() { return this.appState.app; },
            nodes() { return this.versionState.nodes; },
            edges() { return this.versionState.edges; },
            version() { return this.versionState.version; },
            versionId() { return this.$route.params.version_id; },
            isLoadingVersion() { return this.versionState.isLoadingVersion; },
            isUpdatingVersion() { return this.versionState.isUpdatingVersion; },
        },
        methods: {
            async goBackToVersions() {
                await this.$router.push({
                    name: 'show-app-versions',
                    params: { app_id: this.app.id },
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
                    const config = { params: { _relationships: ['app'].join(',') } };
                    const response = await axios.get(`/api/apps/${this.app.id}/versions/${this.versionId}`, config);
                    const version = response.data;
                    this.versionState.setVersion(version);
                    this.versionState.setVersionForm(version);
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong';
                    this.notificationState.showWarningNotification(message);
                    if (error.response?.status === 404) {
                        await this.$router.replace({ name: 'show-app-versions', params: { app_id: this.app.id } });
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
                } finally {
                    this.versionState.isUpdatingVersion = false;
                }
            }
        },
        mounted() {
            this.$nextTick(() => {
                this.versionState.featuresModal = this.$refs.featuresModal.$refs.modal;
                this.versionState.stepEditModal = this.$refs.stepEditModal.$refs.modal;
                this.versionState.eventPickerModal = this.$refs.eventPickerModal.$refs.modal;
                this.versionState.restApiEventModal = this.$refs.restApiEventModal.$refs.modal;
                this.versionState.soapApiEventModal = this.$refs.soapApiEventModal.$refs.modal;
                this.versionState.inputFeatureModal = this.$refs.inputFeatureModal.$refs.modal;
                this.versionState.decisionPointModal = this.$refs.decisionPointModal.$refs.modal;
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
/* Keep your existing scrollbar styles */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

/* Target the Vue Flow internal node wrapper */
:deep(.vue-flow__node) {
    /* Smoothly animate the transform property (x/y position) */
    transition: transform 1s cubic-bezier(0.25, 0.8, 0.25, 1);
}

/* CRITICAL: Disable transition while the user is manually dragging a node.
   If you don't do this, the node will float slowly behind the mouse cursor. */
:deep(.vue-flow__node.dragging) {
    transition: none !important;
}
</style>
