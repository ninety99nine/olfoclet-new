<template>
  <div class="min-h-screen bg-slate-50">

    <!-- Sticky Header -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-20 shadow-sm">
      <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold text-slate-900">USSD Flow Builder</h1>
            <div class="flex items-center gap-2 bg-slate-100 rounded-lg p-1 text-sm">
              <button
                @click="mode = 'classic'"
                :class="[
                  'px-4 py-1.5 rounded-md font-medium transition-all',
                  mode === 'classic' ? 'bg-white shadow text-indigo-700' : 'text-slate-600 hover:text-indigo-600'
                ]"
              >
                Classic
              </button>
              <button
                @click="mode = 'flow'"
                :class="[
                  'px-4 py-1.5 rounded-md font-medium transition-all',
                  mode === 'flow' ? 'bg-white shadow text-indigo-700' : 'text-slate-600 hover:text-indigo-600'
                ]"
              >
                Visual Flow
              </button>
            </div>
          </div>

          <div class="flex items-center gap-4">
            <Button
              size="md"
              type="outline"
              :action="previewFlow"
              buttonClass="rounded-lg"
            >
              <Eye class="w-4 h-4 mr-2" />
              Preview
            </Button>
            <Button
              size="md"
              type="primary"
              :action="saveFlow"
              :loading="saving"
              buttonClass="rounded-lg shadow-md"
            >
              <Save class="w-4 h-4 mr-2" />
              Save & Deploy
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-8">

      <!-- Classic Mode -->
      <div v-if="mode === 'classic'" class="space-y-6">

        <!-- Step Cards -->
        <div
          v-for="(step, index) in steps"
          :key="step.id"
          class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hover:shadow transition-shadow"
        >
          <div class="flex items-center justify-between px-6 py-4 bg-slate-50 border-b border-slate-200">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                {{ index + 1 }}
              </div>
              <h3 class="font-semibold text-slate-900">
                {{ step.title || `Step ${index + 1}` }}
              </h3>
            </div>
            <div class="flex gap-2">
              <button
                @click="editStep(index)"
                class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                title="Edit step"
              >
                <Pencil class="w-5 h-5" />
              </button>
              <button
                @click="deleteStep(index)"
                class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                title="Delete step"
              >
                <Trash2 class="w-5 h-5" />
              </button>
            </div>
          </div>

          <div class="p-6 space-y-5">
            <div>
              <p class="text-sm font-medium text-slate-600 mb-1.5">Displayed Text</p>
              <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-sm whitespace-pre-line min-h-[80px]">
                {{ step.instructions || 'No message set for this step' }}
              </div>
            </div>

            <div>
              <p class="text-sm font-medium text-slate-600 mb-1.5">User Action</p>
              <div class="flex items-center gap-3">
                <div class="px-3 py-1.5 bg-slate-100 rounded-full text-xs font-medium">
                  {{ step.actionType === 'input' ? 'Free Text Input' : 'Choose from Options' }}
                </div>
                <div v-if="step.actionType === 'list'" class="text-sm text-slate-600">
                  {{ step.options.length }} option{{ step.options.length !== 1 ? 's' : '' }}
                </div>
              </div>
            </div>

            <div v-if="step.actionType === 'list'" class="space-y-2">
              <p class="text-sm font-medium text-slate-600">Options & Next Steps</p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div
                  v-for="(opt, i) in step.options"
                  :key="i"
                  class="bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm flex justify-between items-center"
                >
                  <span class="font-medium">{{ opt.text || 'Unnamed option' }}</span>
                  <span class="text-slate-500 text-xs">
                    → Step {{ opt.nextStep || 'End' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Add Step Button -->
        <button
          @click="addStep"
          class="w-full py-8 border-2 border-dashed border-indigo-300 rounded-xl text-indigo-600 hover:border-indigo-500 hover:bg-indigo-50/30 transition-all flex flex-col items-center gap-2"
        >
          <PlusCircle class="w-8 h-8" />
          <span class="font-medium">Add New Step</span>
        </button>
      </div>

      <!-- Visual Flow Mode - Horizontal Layout -->
      <div v-else class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" style="height: 70vh;">
<VueFlow
  v-model="elements"
  @node-click="onNodeClick"
  @connect="onConnect"
  :default-viewport="{ zoom: 1.0, x: -80, y: 120 }"
  :connection-line-type="'bezier'"
  :connection-line-style="{ stroke: '#6366f1', strokeWidth: 2 }"
  :default-edge-options="{
    type: 'bezier',
    style: { stroke: '#6366f1', strokeWidth: 2.5 },
    labelBgStyle: { fill: 'white', fillOpacity: 0.9 },
    labelStyle: { fill: '#1e293b', fontSize: '12px', fontWeight: 500 }
  }"
>
  <Background pattern-color="#e2e8f0" gap="20" />
  <MiniMap />
  <Controls />
</VueFlow>
      </div>

    </main>

    <!-- Step Editor Modal -->
    <Modal v-if="showStepModal" @close="closeModal" title="Configure Step">
      <!-- ... (unchanged modal content) ... -->
    </Modal>
  </div>
</template>

<script>
import Input from '@Partials/Input.vue'
import Modal from '@Partials/Modal.vue'
import Button from '@Partials/Button.vue'
import { VueFlow } from '@vue-flow/core';
import '@vue-flow/controls/dist/style.css';
import { MiniMap } from '@vue-flow/minimap';
import { Controls } from '@vue-flow/controls';
import { Background } from '@vue-flow/background';
import { Eye, Save, Pencil, Trash2, PlusCircle, Plus, LayoutDashboard } from 'lucide-vue-next'

export default {
    components: {
        Button, Input, Modal,
        VueFlow, MiniMap, Controls, Background,
        Eye, Save, Pencil, Trash2, PlusCircle, Plus, LayoutDashboard
    },

  data() {
    return {
      mode: 'classic',
      steps: [],                    // Classic mode
      showStepModal: false,
      editingIndex: -1,
      currentStep: {
        title: '',
        instructions: '',
        actionType: 'input',
        options: []
      },
      saving: false,

      // Vue Flow - Horizontal layout
      elements: [
        { id: 'start', type: 'input', label: 'Start', position: { x: 50, y: 180 } },
        { id: 'welcome', label: 'Welcome Message', position: { x: 300, y: 180 } },
        { id: 'menu', label: 'Main Menu', position: { x: 550, y: 180 } },
        { id: 'balance', label: 'Check Balance', position: { x: 800, y: 100 } },
        { id: 'transfer', label: 'Transfer', position: { x: 800, y: 260 } },
        { id: 'end', type: 'output', label: 'End', position: { x: 1050, y: 180 } },

        // Edges (horizontal connections)
        { id: 'e-start-welcome', source: 'start', target: 'welcome', animated: true },
        { id: 'e-welcome-menu', source: 'welcome', target: 'menu', animated: true },
        { id: 'e-menu-balance', source: 'menu', target: 'balance', label: '1' },
        { id: 'e-menu-transfer', source: 'menu', target: 'transfer', label: '2' },
        { id: 'e-balance-end', source: 'balance', target: 'end' },
        { id: 'e-transfer-end', source: 'transfer', target: 'end' }
      ]
    }
  },

  mounted() {
    // Optional: auto-layout on first load (horizontal spacing)
    this.layoutHorizontal();
  },

  methods: {
    // ── Classic Mode Methods ────────────────────────────────────────

    addStep() {
      this.editingIndex = -1
      this.currentStep = {
        title: '',
        instructions: '',
        actionType: 'input',
        options: []
      }
      this.showStepModal = true
    },

    editStep(index) {
      this.editingIndex = index
      this.currentStep = JSON.parse(JSON.stringify(this.steps[index]))
      this.showStepModal = true
    },

    saveStep() {
      if (!this.currentStep.instructions?.trim()) {
        alert('Please enter the message shown to the user')
        return
      }

      if (this.editingIndex === -1) {
        this.steps.push({
          id: Date.now(),
          ...this.currentStep
        })
      } else {
        this.$set(this.steps, this.editingIndex, { ...this.currentStep })
      }

      this.showStepModal = false
      this.resetCurrentStep()
    },
layoutHorizontal() {
    const horizontalSpacing = 280
    const verticalOffset = 80  // small vertical stagger to prevent overlap

    this.elements = this.elements.map((el, index) => {
      if (el.position) {  // only move nodes, not edges
        return {
          ...el,
          position: {
            x: 80 + index * horizontalSpacing,
            y: 140 + (index % 2 === 0 ? 0 : verticalOffset)  // slight zigzag for readability
          }
        }
      }
      return el
    })
  },
    closeModal() {
      this.showStepModal = false
      this.resetCurrentStep()
    },

    resetCurrentStep() {
      this.currentStep = {
        title: '',
        instructions: '',
        actionType: 'input',
        options: []
      }
      this.editingIndex = -1
    },

    deleteStep(index) {
      if (confirm('Delete this step?')) {
        this.steps.splice(index, 1)
      }
    },

    addOption() {
      this.currentStep.options.push({ text: '', nextStep: null })
    },

    removeOption(index) {
      this.currentStep.options.splice(index, 1)
    },

    // ── Shared / Header Methods ─────────────────────────────────────

    async saveFlow() {
      this.saving = true
      await new Promise(r => setTimeout(r, 1400))
      alert('Flow saved successfully!')
      this.saving = false
    },

    previewFlow() {
      alert('Preview not implemented yet')
    },

    // ── Vue Flow Helpers ────────────────────────────────────────────

    onNodeClick({ node }) {
      console.log('Clicked node:', node)
      // You can open the step editor modal here for the clicked node
      // Example: find matching step in this.steps and edit
    },

    onConnect(params) {
      // Add new edge
      this.elements.push({
        id: `e${params.source}-${params.target}`,
        source: params.source,
        target: params.target,
        animated: true
      })
    },

    autoLayoutHorizontal() {
      // Simple horizontal auto-layout (can be improved later)
      const spacing = 250
      this.elements = this.elements.map((el, idx) => {
        if (el.position) {
          return {
            ...el,
            position: { x: 50 + idx * spacing, y: 180 }
          }
        }
        return el
      })
    }
  }
}
</script>

<style>
/* Make nodes look nicer */
.vue-flow__node {
  background: white;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  padding: 16px 20px;
  min-width: 140px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  font-weight: 500;
  color: #1e293b;
}

.vue-flow__node-input {
  background: #f0fdfa;
  border-color: #14b8a6;
}

.vue-flow__node-output {
  background: #fef2f2;
  border-color: #ef4444;
}

.vue-flow__handle {
  background: #6366f1;
  width: 12px;
  height: 12px;
  border: 2px solid white;
  box-shadow: 0 0 6px rgba(99, 102, 241, 0.6);
}
</style>
