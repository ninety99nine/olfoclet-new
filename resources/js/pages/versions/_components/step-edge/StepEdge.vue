<template>
  <BaseEdge :path="edgePath" />
  <EdgeLabelRenderer>
    <div
      :style="{
        pointerEvents: 'all',
        position: 'absolute',
        transform: `translate(-50%, -50%) translate(${labelX}px, ${labelY}px)`,
      }"
      class="nodrag nopan flex items-center gap-1.5 bg-white/90 pl-2 pr-1 py-1 rounded shadow-sm text-sm font-medium text-slate-800 border border-slate-200"
    >
      <span>{{ edgeLabel }}</span>
      <button
        type="button"
        @click.stop="onRemoveClick"
        class="shrink-0 w-5 h-5 flex items-center justify-center rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
        aria-label="Remove connection"
      >
        <X size="12" />
      </button>
    </div>
  </EdgeLabelRenderer>
</template>

<script>
import { BaseEdge, EdgeLabelRenderer, getBezierPath } from '@vue-flow/core'
import { X } from 'lucide-vue-next'

export default {
  name: 'StepEdge',
  inheritAttrs: false,
  components: { BaseEdge, EdgeLabelRenderer, X },
  inject: ['versionState'],
  props: {
    id: { type: String, default: '' },
    source: { type: String, default: '' },
    target: { type: String, default: '' },
    sourceHandleId: { type: String, default: '' },
    targetHandleId: { type: String, default: '' },
    sourceX: { type: Number, required: true },
    sourceY: { type: Number, required: true },
    targetX: { type: Number, required: true },
    targetY: { type: Number, required: true },
    sourcePosition: { type: String, required: true },
    targetPosition: { type: String, required: true },
    data: { type: Object, default: () => ({}) },
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
      if (f.type === 'select' && this.data?.optionIndex != null && f.options?.[this.data.optionIndex]) {
        const opt = f.options[this.data.optionIndex];
        return opt?.label ? `→ ${opt.label}` : 'Edge';
      }
      return 'Edge';
    },
    pathData() {
      return getBezierPath({
        sourceX: this.sourceX,
        sourceY: this.sourceY,
        sourcePosition: this.sourcePosition,
        targetX: this.targetX,
        targetY: this.targetY,
        targetPosition: this.targetPosition,
      })
    },
    edgePath() {
      return this.pathData[0]
    },
    labelX() {
      return this.pathData[1]
    },
    labelY() {
      return this.pathData[2]
    },
  },
  methods: {
    onRemoveClick() {
      this.versionState.removeInputEdge(this.id)
    },
  },
}
</script>

<style scoped>
.nodrag.nopan {
  transform-origin: center center;
  white-space: nowrap;
}
</style>
