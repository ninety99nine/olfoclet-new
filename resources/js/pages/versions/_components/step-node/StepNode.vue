<template>

    <div
        :class="[
            'relative group w-80 px-5 py-4 bg-white border rounded-xl shadow-md transition-all duration-300 cursor-grab active:cursor-grabbing',
            selected ? 'border-indigo-600 shadow-lg' : 'border-slate-300 hover:border-indigo-400 hover:shadow',
            dragging ? 'opacity-90 scale-[1.02] shadow-2xl' : ''
        ]">

        <Handle v-if="!isFirstStep" type="target" position="left" class="block nodrag nopan!" />

        <StepNodeHeader
            :stepId="id"
            :isFirstStep="isFirstStep"
            :canDeleteStep="canDeleteStep"
        />

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

        <div v-if="showAddFeatureAlways" class="max-w-52 mb-6 mt-4 mx-auto text-slate-500 text-sm">
            Lets add a feature to our first step to make it interactive
        </div>

        <div class="flex justify-center my-4">
            <Button v-if="showAddFeatureAlways" size="md" mode="solid" type="primary" :leftIcon="Plus" buttonClass="rounded-full" :action="showFeaturesModal">
                Add Feature
            </Button>
            <button v-else-if="connectable" @click="showFeaturesModal" class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-all z-20 cursor-pointer">
                <Plus class="w-4 h-4" />
            </button>
        </div>
    </div>
</template>

<script>
import { Handle } from '@vue-flow/core';
import { Plus } from 'lucide-vue-next';
import Button from '@Partials/Button.vue';
import StepNodeInput from './_components/StepNodeInput.vue';
import StepNodeSelect from './_components/StepNodeSelect.vue';
import StepNodeHeader from './_components/StepNodeHeader.vue';
import StepNodeCodeContent from './_components/StepNodeCodeContent.vue';
import StepNodeBasicContent from './_components/StepNodeBasicContent.vue';

export default {
    name: 'StepNode',
    inject: ['versionState'],
    components: { Handle, Button, Plus, StepNodeHeader, StepNodeCodeContent, StepNodeBasicContent, StepNodeSelect, StepNodeInput },
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
        isValidTarget: { type: Function },
        isValidSource: { type: Function },
        parent: { type: String },
        zIndex: { type: Number, default: 0 },
    },
    data() { return { Plus }; },
    computed: {
        step() { return this.builder.steps[this.id]; },
        builder() { return this.versionState.versionForm.builder; },
        isFirstStep() { return this.builder.initial_step_id === this.id; },
        canDeleteStep() { return Object.keys(this.builder.steps).length > 1; },
        hasAnyFeature() { return this.featuresList.length > 0; },
        featuresList() {
            const feats = this.builder.features;
            return (this.step.feature_ids || []).map(fid => ({ ...feats[fid], id: fid })).filter(feature => feature.type);
        },
        showAddFeatureAlways() { return this.isFirstStep && !this.hasAnyFeature; },
        otherFeatures() { return this.featuresList.filter(feature => ['input', 'select'].includes(feature.type)); },
        contentFeature() { return this.featuresList.find(feature => ['basic content', 'code content'].includes(feature.type)); },
    },
    methods: {
        showFeaturesModal() {
            this.versionState.setCurrentStepId(this.id);
            this.versionState.featuresModal?.showModal();
        }
    }
}
</script>
