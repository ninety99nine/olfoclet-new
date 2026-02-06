<template>

    <div class="space-y-4">

        <Select
            :clearable="clearable"
            :options="stepOptions"
            :model-value="modelValue"
            :placeholder="placeholder"
            :label="showLabel ? label : null"
            @update:model-value="handleSelection"
        />

        <div v-if="isPending" class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-4 space-y-2 animate-in fade-in slide-in-from-top-2 duration-200">

            <Input
                v-model="pendingName"
                type="text"
                label="New step name"
                placeholder="e.g. Success Screen"
                @input="emitUpdate"
                showAsterisk
            />

            <p class="text-[10px] text-indigo-600 font-medium">
                {{ hint }}
            </p>

        </div>

    </div>

</template>

<script>
import Select from '@Partials/Select.vue';
import Input from '@Partials/Input.vue';

export default {
    name: 'StepSelector',
    inject: ['versionState'],
    components: { Select, Input },
    props: {
        modelValue: [String, Number],
        showLabel: { type: Boolean, default: true },
        clearable: { type: Boolean, default: true },
        label: { type: String, default: 'Go to next step' },
        placeholder: { type: String, default: 'Select next step' },
        hint: { type: String, default: 'This step will be created when you apply.' },
    },
    emits: ['update:modelValue', 'update:pendingStep'],
    data() {
        return {
            isPending: false,
            pendingName: 'New Step'
        };
    },
    computed: {
        stepOptions() {
            // 1. Get steps from store
            const steps = this.versionState.builder?.steps || {};

            // 2. Map existing steps to labels/values
            const existing = Object.entries(steps).map(([sid, step]) => ({
                label: step.name || 'Unnamed Step',
                value: sid
            }));

            // 3. Prepare special options
            const createOption = this.isPending
                ? []
                : [{ label: '➕ Create new step…', value: '__create__' }];

            const pendingOption = this.isPending
                ? [{ label: this.pendingName || 'New step', value: '__pending_new__' }]
                : [];

            return [...createOption, ...pendingOption, ...existing];
        }
    },
    methods: {
        handleSelection(value) {
            if (value === '__create__') {
                this.isPending = true;
                this.$emit('update:modelValue', '__pending_new__');
            } else {
                this.isPending = false;
                this.$emit('update:modelValue', value);
            }
            this.emitUpdate();
        },
        emitUpdate() {
            this.$emit('update:pendingStep', {
                isPending: this.isPending,
                name: this.pendingName
            });
        }
    }
};
</script>
