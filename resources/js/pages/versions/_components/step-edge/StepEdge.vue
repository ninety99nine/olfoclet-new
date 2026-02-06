<template>
    <BaseEdge
        :path="edgePath"
        :class="['step-edge-path', { 'is-dynamic': data?.is_dynamic }]"
    />
    <EdgeLabelRenderer>
        <div
            @click="copyLabel"
            :style="{
                pointerEvents: 'all',
                position: 'absolute',
                left: `${labelX}px`,
                top: `${labelY}px`,
                transform: `translate(-50%, -50%) rotate(${labelRotation}deg)`,
                transition: 'none', /* Ensures the label snaps instantly to coordinates */
            }"
            :class="[
                'nodrag nopan flex items-center backdrop-blur-sm rounded-full shadow-md border cursor-pointer select-none active:scale-95 transition-transform duration-200',
                isRecentlyCopied
                    ? 'bg-indigo-600 border-indigo-600 shadow-indigo-200 px-3 py-1 gap-1.5'
                    : 'bg-white/95 border-slate-200 hover:border-indigo-400 hover:shadow-indigo-100/50 group/edge-label gap-0 py-1 px-2'
            ]"
        >
            <div class="relative w-3.5 h-3.5 flex items-center justify-center shrink-0">
                <transition name="fade-scale" mode="out-in">
                    <Check v-if="isRecentlyCopied" key="check" size="10" stroke-width="3" class="text-white" />
                    <div v-else key="icons" class="flex items-center justify-center">
                        <AtSign
                            size="12"
                            class="text-slate-400 group-hover/edge-label:scale-0 group-hover/edge-label:opacity-0 transition-all duration-200"
                        />
                        <Copy
                            size="10"
                            stroke-width="3"
                            class="absolute scale-0 opacity-0 group-hover/edge-label:scale-100 group-hover/edge-label:opacity-100 text-indigo-500 transition-all duration-200 delay-75"
                        />
                    </div>
                </transition>
            </div>

            <div
                :class="[
                    'grid transition-all duration-300 ease-in-out',
                    isRecentlyCopied ? 'grid-cols-[1fr] opacity-100' : 'grid-cols-[0fr] opacity-0 group-hover/edge-label:grid-cols-[1fr] group-hover/edge-label:opacity-100'
                ]"
            >
                <div class="overflow-hidden">
                    <span
                        :class="[
                            'block text-[10px] tracking-widest font-extrabold whitespace-nowrap px-0.5',
                            isRecentlyCopied ? 'text-white' : 'text-slate-600'
                        ]">
                        {{ isRecentlyCopied ? 'COPIED!' : edgeLabel }}
                    </span>
                </div>
            </div>

            <div
                v-if="!data?.is_dynamic"
                :class="[
                    'flex items-center justify-center transition-all duration-300 ease-in-out overflow-hidden',
                    !isRecentlyCopied ? 'w-0 opacity-0 group-hover/edge-label:w-6 group-hover/edge-label:opacity-100' : 'w-0 opacity-0'
                ]"
            >
                <button
                    type="button"
                    @click.stop="onRemoveClick"
                    class="w-5 h-5 flex items-center justify-center rounded-full text-slate-400 hover:text-white hover:bg-rose-500 bg-slate-50 transition-colors duration-200 cursor-pointer"
                >
                    <X size="10" stroke-width="3" />
                </button>
            </div>
        </div>
    </EdgeLabelRenderer>
</template>

<script>
import { BaseEdge, EdgeLabelRenderer, getBezierPath } from '@vue-flow/core'
import { X, Copy, Check, AtSign } from 'lucide-vue-next'

export default {
    name: 'StepEdge',
    inheritAttrs: false,
    components: { BaseEdge, EdgeLabelRenderer, X, Copy, Check, AtSign },
    inject: ['versionState', 'notificationState'],
    props: {
        id: { type: String, required: true },
        source: { type: String, required: true },
        target: { type: String, required: true },
        sourceX: { type: Number, required: true },
        sourceY: { type: Number, required: true },
        targetX: { type: Number, required: true },
        targetY: { type: Number, required: true },
        sourcePosition: { type: String, required: true },
        targetPosition: { type: String, required: true },
        data: { type: Object, default: () => ({}) },
    },
    data() {
        return { isRecentlyCopied: false }
    },
    computed: {
        feature() {
            const fid = this.data?.feature_id;
            return fid && this.versionState ? this.versionState.versionForm?.builder?.features?.[fid] : null;
        },
        edgeLabel() {
            const f = this.feature;
            if (!f) return 'Edge';

            if (f.type === 'input') return '@' + (f.variable || 'value');

            if (f.type === 'select') {
                if (f.source === 'list' && this.data?.option_index != null) {
                    const opt = f.options?.[this.data.option_index];
                    return opt?.label || `Option ${this.data.option_index + 1}`;
                }
                if (f.source === 'code') {
                    // Specific label for dynamic links
                    return '@' + (f.variable || 'value');
                }
            }
            return 'Edge';
        },
        edgeVariable() {
            return this.feature ? `@${this.feature.variable}` : null;
        },
        pathData() {
            return getBezierPath({
                sourceX: this.sourceX, sourceY: this.sourceY, sourcePosition: this.sourcePosition,
                targetX: this.targetX, targetY: this.targetY, targetPosition: this.targetPosition,
            })
        },
        edgePath() { return this.pathData[0] },
        labelX() { return this.pathData[1] },
        labelY() { return this.pathData[2] },
        labelRotation() {
            const angle = Math.atan2(this.targetY - this.sourceY, this.targetX - this.sourceX) * (180 / Math.PI);
            return (angle > 90 || angle < -90) ? angle + 180 : angle;
        }
    },
    methods: {
        copyLabel() {
            if (this.isRecentlyCopied) return;
            navigator.clipboard.writeText(this.edgeVariable);
            this.isRecentlyCopied = true;
            if (this.notificationState) this.notificationState.showSuccessNotification(`Copied: ${this.edgeVariable}`);
            setTimeout(() => { this.isRecentlyCopied = false; }, 1500);
        },
        onRemoveClick() {
            this.versionState.removeInputEdge(this.id);
        }
    }
}
</script>

<style scoped>
    /* Status icon transitions */
    .fade-scale-enter-active, .fade-scale-leave-active {
        transition: all 0.2s ease;
    }
    .fade-scale-enter-from, .fade-scale-leave-to {
        opacity: 0;
        transform: scale(0.5);
    }
</style>
