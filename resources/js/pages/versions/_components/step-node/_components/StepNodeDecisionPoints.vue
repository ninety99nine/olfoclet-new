<template>
    <div class="select-none">

        <div class="space-y-2 mb-4">
            <div
                v-for="(rule, index) in (step.rules || [])"
                :key="index"
                @click.stop="openEditor"
                :class="[
                    'relative flex items-center justify-between px-3 py-2.5 rounded-lg group/rule transition-all duration-300 cursor-pointer border',
                    // HIGHLIGHT LOGIC: Compare rule label with simulator state
                    (matchedLabel === (rule.label || `Rule ${index + 1}`))
                        ? 'bg-orange-50 border-orange-200'
                        : 'bg-white border-slate-200 hover:bg-slate-50'
                ]">

                <div class="flex items-center min-w-0 flex-1 pr-6 py-1">
                    <div class="flex flex-col min-w-0">
                        <span
                            :class="[
                                'text-[10px] font-black tracking-wider mb-0.5',
                                (matchedLabel === (rule.label || `Rule ${index + 1}`)) ? 'text-orange-700' : 'text-orange-600'
                            ]">
                            {{ rule.label || `RULE #${index + 1}` }}
                        </span>

                        <div class="flex items-center gap-1 text-[11px] text-slate-600 font-medium truncate">
                            <span class="text-indigo-600 font-bold">@{{ rule.checks[0]?.variable || '?' }}</span>
                            <span class="text-slate-400 italic">{{ getOperatorLabel(rule.checks[0]?.operator) }}</span>
                            <span class="truncate">{{ rule.checks[0]?.value || '?' }}</span>

                            <span v-if="rule.checks.length > 1"
                                class="ml-1 px-1 bg-indigo-50 text-indigo-600 text-[9px] rounded border border-indigo-100 shrink-0">
                                + {{ rule.checks.length - 1 }} more
                            </span>
                        </div>
                    </div>
                </div>

                <Handle
                    type="source"
                    position="right"
                    :id="`decision-rule#${id}#${index}`"
                    class="decision-handle"
                />
            </div>

            <div
                @click.stop="openEditor"
                :class="[
                    'relative flex items-center justify-between px-3 py-2 rounded-lg group/else transition-colors cursor-pointer border',
                    // HIGHLIGHT LOGIC: Check if matched label is 'Else'
                    matchedLabel === 'Else'
                        ? 'bg-slate-100 border-slate-500 shadow-md ring-2 ring-slate-200 z-10'
                        : 'bg-slate-50 border-slate-200 hover:border-slate-300'
                ]">
                <div class="flex items-center gap-2 min-w-0">
                    <span :class="['shrink-0 text-[10px] font-black uppercase tracking-wider', matchedLabel === 'Else' ? 'text-slate-700' : 'text-slate-500']">ELSE</span>
                    <span class="text-xs text-slate-400 italic font-medium truncate text-right">Fallback path</span>
                </div>

                <div v-if="matchedLabel === 'Else'" class="absolute right-3 top-1/2 -translate-y-1/2">
                    <div class="h-2 w-2 rounded-full bg-slate-400 animate-pulse"></div>
                </div>

                <Handle
                    :id="`decision-default#${id}`"
                    type="source"
                    position="right"
                    class="decision-handle default-handle"
                />
            </div>
        </div>

        <Button
            size="sm"
            mode="solid"
            type="light"
            :leftIcon="Plus"
            leftIconSize="14"
            :action="openEditor"
            buttonClass="rounded-lg shadow-sm w-full font-bold">
            <span class="text-xs">Configure Rules</span>
        </Button>
    </div>
</template>

<script>
import { Handle } from '@vue-flow/core';
import Button from '@Partials/Button.vue';
import Dropdown from '@Partials/Dropdown.vue';
import { GitBranch, Settings2, Plus, EllipsisVertical, SquarePen, Trash } from 'lucide-vue-next';

export default {
    name: 'ConditionNode',
    inject: ['versionState', 'notificationState'],
    components: { Handle, Button, Dropdown, GitBranch, Settings2, EllipsisVertical, SquarePen, Trash, Plus },
    props: {
        id: { required: true }
    },
    data() {
        return {
            Plus
        }
    },
    computed: {
        step() {
            return this.versionState.builder?.steps[this.id] || {};
        },
        // Retrieves the matched rule label for this specific step from the Simulator Store
        matchedLabel() {
            return this.versionState.simulatorPathLogic[this.id];
        }
    },
    methods: {
        openEditor() {
            this.versionState.setCurrentStepId(this.id);
            this.versionState.decisionPointModal?.showModal();
        },
        deleteCondition(close) {
            this.versionState.removeStep(this.id);
            if (this.notificationState) {
                this.notificationState.showSuccessNotification('Logic gate deleted');
            }
            if (close) close();
        },
        getOperatorLabel(op) {
            const labels = {
                '==': 'is',
                '!=': 'is not',
                '>': 'is >',
                '<': 'is <',
                'contains': 'contains',
                'starts_with': 'starts with'
            };
            return labels[op] || op;
        }
    }
}
</script>

<style scoped>
/* Explicitly position handles on the right edge of their relative parent (the row). */
:deep(.vue-flow__handle.decision-handle) {
    width: 10px;
    height: 10px;
    background-color: #f97316; /* Orange-500 */
    border: 2px solid white;
    border-radius: 50%;
    position: absolute;
    right: -5px !important; /* Push slightly outside the box */
    top: 50% !important;
    transform: translateY(-50%) !important;
    z-index: 20; /* Increased Z-index to sit above the active border */
    transition: transform 0.2s;
}

:deep(.vue-flow__handle.decision-handle:hover) {
    transform: translateY(-50%) scale(1.3) !important;
    background-color: #ea580c; /* Orange-600 */
}

/* Different color for the Else/Default handle to distinguish it */
:deep(.vue-flow__handle.default-handle) {
    background-color: #94a3b8; /* Slate-400 */
}
:deep(.vue-flow__handle.default-handle:hover) {
    background-color: #64748b; /* Slate-500 */
}
</style>
