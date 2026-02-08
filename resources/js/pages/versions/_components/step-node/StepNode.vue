<template>
    <div class="flex flex-col items-center">
        <div
            :class="[
                'relative group w-80 px-5 py-4 bg-white border rounded-xl shadow-md transition-all duration-500 cursor-grab active:cursor-grabbing',
                selected ? 'border-indigo-600 shadow-lg' : 'border-slate-300 hover:border-indigo-400 hover:shadow',
                dragging ? 'opacity-90 scale-[1.02] shadow-2xl' : '',
                step?.is_terminal ? 'ring-2 ring-amber-100/50 border-amber-200 shadow-amber-50/50' : '',
                // Simulator Highlighting Logic
                isTerminalSimulatorPath ? 'ring-4 ring-amber-500 border-amber-500 shadow-amber-200 z-30' :
                isInSimulatorPath ? 'ring-4 ring-indigo-500 border-indigo-500 shadow-indigo-200 z-30' : ''
            ]">

            <Handle v-if="!isFirstStep" type="target" position="left" class="block nodrag nopan!" />

            <StepNodeHeader
                :stepId="id"
                :isFirstStep="isFirstStep"
                :canDeleteStep="canDeleteStep"
            />

            <template v-if="isDecisionPoint">
                <ConditionNode :id="id" :selected="selected" />
            </template>

            <template v-else>

                <div v-if="beforeEvents.length" class="mb-4">
                    <template v-for="eventId in beforeEvents" :key="eventId">
                        <component
                            v-if="builder.events && builder.events[eventId]"
                            :is="builder.events[eventId].type === 'soap api' ? 'StepNodeSoapEvent' : 'StepNodeRestEvent'"
                            :step-id="id"
                            :event-id="eventId"
                            :timing="builder.events[eventId].timing"
                        />
                    </template>
                </div>

                <template v-if="contentFeature">
                    <StepNodeBasicContent
                        v-if="contentFeature.type === 'basic content'"
                        :stepId="id"
                        :feature="contentFeature"
                    />
                    <StepNodeCodeContent
                        v-else-if="contentFeature.type === 'code content'"
                        :stepId="id"
                        :feature="contentFeature"
                    />
                </template>

                <template v-for="feature in otherFeatures" :key="feature.id">
                    <StepNodeInput
                        :stepId="id"
                        :feature="feature"
                        v-if="feature.type === 'input'"
                    />

                    <StepNodeSelect
                        :stepId="id"
                        :feature="feature"
                        v-else-if="feature.type === 'select'"
                    />
                </template>

                <div v-if="hasBeforeEvents && hasAfterEvents && showAddFeatureAlways" class="h-px bg-slate-100 my-4"></div>

                <div v-if="afterEvents.length" class="mt-2">
                    <template v-for="eventId in afterEvents" :key="eventId">
                        <component
                            v-if="builder.events && builder.events[eventId]"
                            :is="builder.events[eventId].type === 'soap api' ? 'StepNodeSoapEvent' : 'StepNodeRestEvent'"
                            :step-id="id"
                            :event-id="eventId"
                            :timing="builder.events[eventId].timing"
                        />
                    </template>
                </div>

                <div v-if="step?.is_terminal" :class="['flex flex-col items-center mt-4', { 'border-t border-slate-200 pt-4' : step.is_terminal && (hasBeforeEvents || hasAfterEvents || hasAnyFeature) }]">
                    <Tooltip v-if="step?.is_terminal" position="top" width="w-48" content="The session will close after this step is reached.">
                        <template #trigger>
                            <div class="flex items-center gap-2 px-4 py-2 bg-amber-100 rounded-full border border-amber-100">
                                <LogOut size="14" class="text-amber-700" />
                                <span class="text-[10px] font-bold text-amber-700 uppercase tracking-widest">End of Session</span>
                            </div>
                        </template>
                    </Tooltip>
                </div>

                <div v-if="showAddFeatureAlways" class="max-w-52 mb-6 mt-4 mx-auto text-slate-400 text-[11px] text-center leading-relaxed">
                    Add content or logic to define the behavior of your first step.
                </div>

                <div class="flex justify-center my-4">
                    <div v-if="!hasAnyFeature" class="flex items-center gap-2">
                        <button
                            @click="showEventPicker"
                            type="button"
                            title="Add Event (API)"
                            class="w-10 h-10 rounded-full bg-amber-500 text-white flex items-center justify-center shadow-lg hover:scale-110 active:scale-95 transition-all cursor-pointer">
                            <Zap class="w-4 h-4" />
                        </button>

                        <Button :size="showAddFeatureAlways ? 'md' : 'sm'" mode="solid" type="primary" :leftIcon="Plus" buttonClass="rounded-full shadow-md hover:shadow-lg" :action="showFeaturesModal">
                            <span class="ml-0.5 font-bold">Add Feature</span>
                        </Button>
                    </div>

                    <div v-else-if="connectable" class="flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-all z-20">
                        <button
                            @click="showEventPicker"
                            type="button"
                            title="Add Event (API)"
                            class="w-10 h-10 rounded-full bg-amber-500 text-white flex items-center justify-center shadow-lg hover:scale-110 active:scale-95 transition-all cursor-pointer">
                            <Zap class="w-4 h-4" />
                        </button>

                        <button
                            @click="showFeaturesModal"
                            type="button"
                            title="Add Feature"
                            class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center shadow-lg hover:scale-110 active:scale-95 transition-all cursor-pointer">
                            <Plus class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </template>
        </div>
        <Transition name="slide-up">
    <div
        v-if="isInSimulatorPath && (versionState.simulatorStepResults[id] || versionState.simulatorPathLogic[id])"
        class="w-72 mt-5 flex flex-col gap-2 relative z-50 animate-in fade-in slide-in-from-top-2 duration-300">

        <div v-if="versionState.simulatorStepResults[id]"
             class="bg-white/95 backdrop-blur-sm border border-indigo-100 p-4 rounded-2xl shadow-sm relative z-10">
            <div class="flex items-center gap-2 mb-1.5">
                <div class="p-1 bg-indigo-50 rounded-md">
                    <Smartphone size="12" class="text-indigo-600" />
                </div>
                <span class="text-[9px] font-bold uppercase tracking-widest text-indigo-500">Live Preview</span>
            </div>
            <p class="text-xs font-medium leading-relaxed whitespace-pre-wrap text-slate-700">
                {{ versionState.simulatorStepResults[id] }}
            </p>
        </div>

        <div v-if="versionState.simulatorPathLogic[id]"
             :class="[
                'border px-3 py-2 rounded-2xl shadow-sm relative z-10 max-w-[80%]',
                isDecisionPoint
                    ? 'self-center bg-orange-50 border-orange-200'
                    : 'self-end bg-slate-50 border-slate-200 rounded-tr-sm'
             ]">
            <p :class="['text-[10px] font-medium leading-tight px-1', isDecisionPoint ? 'text-orange-900' : 'text-slate-600']">
                <span :class="['text-xs mr-2 font-bold select-none', isDecisionPoint ? 'text-orange-500' : 'text-slate-400']">
                    {{ isDecisionPoint ? '⚡' : '↳' }}
                </span>
                <span>{{ versionState.simulatorPathLogic[id] }}</span>
            </p>
        </div>

        <div :class="[
            'absolute z-10 -top-1.5 left-1/2 -translate-x-1/2 w-3 h-3 rotate-45 transform border-t border-l',
            versionState.simulatorStepResults[id]
                ? 'bg-white border-indigo-100'
                : (isDecisionPoint
                    ? 'bg-orange-50 border-orange-200'
                    : 'bg-slate-50 border-slate-200')
        ]"></div>
    </div>
</Transition>
    </div>
</template>

<script>
import { Handle } from '@vue-flow/core';
import Button from '@Partials/Button.vue';
import Tooltip from '@Partials/Tooltip.vue';
import { Plus, Zap, LogOut, Smartphone } from 'lucide-vue-next';

// Node Components
import StepNodeHeader from './_components/StepNodeHeader.vue';
import ConditionNode from './_components/StepNodeDecisionPoints.vue';

// Event Components
import StepNodeRestEvent from './_components/events/StepNodeRestEvent.vue';
import StepNodeSoapEvent from './_components/events/StepNodeSoapEvent.vue';

// Feature Components
import StepNodeSelect from './_components/StepNodeSelect.vue';
import StepNodeInput from './_components/features/StepNodeInput.vue';
import StepNodeCodeContent from './_components/features/StepNodeCodeContent.vue';
import StepNodeBasicContent from './_components/features/StepNodeBasicContent.vue';

export default {
    name: 'StepNode',
    inject: ['versionState'],
    components: {
        Handle,
        Button,
        Plus,
        Zap,
        LogOut,
        Smartphone,
        Tooltip,
        StepNodeRestEvent,
        StepNodeSoapEvent,
        StepNodeHeader,
        StepNodeCodeContent,
        StepNodeBasicContent,
        StepNodeSelect,
        StepNodeInput,
        ConditionNode
    },
    props: {
        id: { type: [String, Number], required: true },
        type: { type: String, default: 'default' },
        data: { type: Object, default: () => ({}) },
        selected: { type: Boolean, default: false },
        dragging: { type: Boolean, default: false },
        resizing: { type: Boolean, default: false },
        connectable: { type: Boolean, default: true },
        position: { type: Object, default: () => ({ x: 0, y: 0 }) },
        dimensions: { type: Object, default: () => ({ width: 0, height: 0 }) },
        zIndex: { type: Number, default: 0 },
    },
    data() { return { Plus }; },
    computed: {
        step() { return this.builder.steps[this.id]; },
        builder() { return this.versionState.versionForm.builder; },
        /* Detect if the node should render as a Logic Gate */
        isDecisionPoint() { return this.step?.type === 'decision_point'; },
        isFirstStep() { return this.builder.initial_step_id === this.id; },
        canDeleteStep() { return Object.keys(this.builder.steps).length > 1; },
        hasAnyFeature() { return this.featuresList.length > 0; },
        featuresList() {
            const feats = this.builder.features || {};
            return (this.step.feature_ids || []).map(fid => ({ ...feats[fid], id: fid })).filter(feature => feature.type);
        },
        showAddFeatureAlways() { return this.isFirstStep && !this.hasAnyFeature; },
        otherFeatures() { return this.featuresList.filter(feature => ['input', 'select'].includes(feature.type)); },
        contentFeature() { return this.featuresList.find(feature => ['basic content', 'code content'].includes(feature.type)); },
        eventIds() { return this.step.event_ids || []; },
        hasBeforeEvents() { return this.beforeEvents.length > 0; },
        hasAfterEvents() { return this.afterEvents.length > 0; },
        beforeEvents() {
            return this.eventIds.filter(id => this.builder.events?.[id]?.timing === 'before');
        },
        afterEvents() {
            return this.eventIds.filter(id => this.builder.events?.[id]?.timing === 'after');
        },
        // Simulator Highlighting Computeds
        isInSimulatorPath() {
            return this.versionState.simulatorPathNodes.includes(this.id);
        },
        isTerminalSimulatorPath() {
            return this.versionState.simulatorTerminalStepId === this.id;
        }
    },
    methods: {
        showFeaturesModal() {
            this.versionState.setCurrentStepId(this.id);
            this.versionState.featuresModal?.showModal();
        },
        showEventPicker() {
            this.versionState.setCurrentStepId(this.id);
            this.versionState.eventPickerModal?.showModal();
        }
    }
}
</script>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.slide-up-enter-from, .slide-up-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
}
</style>
