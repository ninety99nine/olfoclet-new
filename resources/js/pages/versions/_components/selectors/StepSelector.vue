<template>
    <div class="space-y-3">
        <div class="flex items-center gap-1">
            <Select
                class="flex-1"
                :clearable="clearable"
                :options="filteredOptions"
                :model-value="modelValue"
                :placeholder="placeholder"
                :label="showLabel ? label : null"
                @update:model-value="handleSelection"
            >
                <template #header>
                    <div class="p-2 border-b border-gray-100 bg-gray-50/50">
                        <Tabs
                            v-model="activeTab"
                            design="1"
                            size="sm"
                            :tabs="[
                                { label: 'Next Step', value: 'steps', leftIcon: Layers },
                                { label: 'Markers', value: 'markers', leftIcon: Bookmark },
                                { label: 'Navigation', value: 'other', leftIcon: Navigation2 }
                            ]"
                        />
                    </div>
                </template>

                <template #option="{ option }">
                    <div class="flex items-center gap-2 py-0.5">
                        <div
                            :class="[
                                'w-7 h-7 rounded flex items-center justify-center shrink-0',
                                activeTab === 'other' ? 'bg-amber-50 text-amber-600' : 'bg-indigo-50 text-indigo-600'
                            ]"
                        >
                            <component
                                v-if="option.icon"
                                :is="option.icon"
                                size="14"
                            />
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-sm font-medium truncate">{{ option.label }}</span>
                            <span v-if="option.description" class="text-[10px] text-gray-400 truncate">{{ option.description }}</span>
                        </div>
                    </div>
                </template>
            </Select>

            <Input
                v-if="isPending"
                placeholder="e.g. Success Screen"
                v-model="pendingName"
                @input="emitUpdate"
                type="text"
                class="flex-1"
            >
                <template #prefix>
                    <div class="whitespace-nowrap text-[10px] p-2.5 border-r text-slate-500 font-bold uppercase tracking-tight border-slate-300 bg-slate-100 h-full rounded-l-md">
                        Step Name
                    </div>
                </template>
            </Input>
        </div>

        <p v-if="isPending" class="text-[10px] text-indigo-500 font-medium ml-1 italic">
            * This new step will be automatically created on the canvas.
        </p>
    </div>
</template>

<script>
import Select from '@Partials/Select.vue';
import Input from '@Partials/Input.vue';
import Tabs from '@Partials/Tabs.vue';
import {
    Layers,
    Bookmark,
    Navigation2,
    ChevronLeft,
    Home,
    Plus,
    RotateCcw,
    ChevronFirst,
    History
} from 'lucide-vue-next';

export default {
    name: 'StepSelector',
    inject: ['versionState'],
    components: { Select, Input, Tabs },
    props: {
        modelValue: [String, Number],
        showLabel: { type: Boolean, default: true },
        clearable: { type: Boolean, default: true },
        label: { type: String, default: 'Destination' },
        placeholder: { type: String, default: 'Where should this go?' },
    },
    emits: ['update:modelValue', 'update:pendingStep'],
    data() {
        return {
            activeTab: 'steps',
            isPending: false,
            pendingName: 'New Step',
            // Component references for dynamic rendering
            Layers,
            Bookmark,
            Navigation2,
            ChevronLeft,
            Home,
            Plus,
            RotateCcw,
            ChevronFirst,
            History
        };
    },
    computed: {
        allSteps() {
            return this.versionState.builder?.steps || {};
        },
        filteredOptions() {
            if (this.activeTab === 'steps') {
                const steps = Object.entries(this.allSteps).map(([id, step]) => ({
                    label: step.name || 'Unnamed Step',
                    value: id,
                    icon: this.Layers
                }));

                const createOption = this.isPending
                    ? [{ label: 'Creating New Step...', value: '__pending_new__', icon: this.Plus }]
                    : [{ label: 'Create new step', value: '__create__', icon: this.Plus }];

                return [...createOption, ...steps];
            }

            if (this.activeTab === 'markers') {
                return Object.entries(this.allSteps)
                    .filter(([id, step]) => !!step.marker)
                    .map(([id, step]) => ({
                        label: step.marker,
                        description: `Points to: ${step.name}`,
                        value: id,
                        icon: this.Bookmark
                    }));
            }

            if (this.activeTab === 'other') {
                return [
                    {
                        label: 'Refresh Current Step',
                        value: '__refresh__',
                        icon: this.RotateCcw,
                        description: 'Show the current screen again'
                    },
                    {
                        label: 'Go Back (1 Step)',
                        value: '__back_1__',
                        icon: this.ChevronLeft,
                        description: 'Return to the previous screen'
                    },
                    {
                        label: 'Go Back (2 Steps)',
                        value: '__back_2__',
                        icon: this.ChevronLeft,
                        description: 'Jump back two screens'
                    },
                    {
                        label: 'Go Back (3 Steps)',
                        value: '__back_3__',
                        icon: this.ChevronLeft,
                        description: 'Jump back three screens'
                    },
                    {
                        label: 'Main Menu',
                        value: '__main_menu__',
                        icon: this.Home,
                        description: 'Jump back to the initial step'
                    }
                ];
            }

            return [];
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
