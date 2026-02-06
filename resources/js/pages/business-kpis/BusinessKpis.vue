<template>
  <div class="min-h-screen bg-slate-50 pb-12">

    <!-- Header -->
    <div class="bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Business KPIs</h1>
          <p class="mt-1 text-sm text-slate-500">
            {{ app ? app.name : '' }} • Define custom metrics and trackers for your USSD service
          </p>
        </div>

        <Modal
          ref="createMetricModal"
          size="md"
          header="Create metric"
          subheader="Define a metric to track performance."
          :showFooter="true"
          :showDelineButton="true"
          declineText="Cancel"
          :showApproveButton="true"
          approveText="Create metric"
          :approveAction="submitCreateMetric"
          :approveLoading="creating"
          :approveDisabled="!canSubmitMetric"
          :scrollOnContent="true"
          :onShow="onCreateModalShow"
          :onHide="onCreateModalHide"
        >
          <template #trigger="{ showModal }">
            <Button
              size="md"
              type="primary"
              buttonClass="rounded-lg"
              :action="() => { resetForm(); showModal(); }"
            >
              <Plus class="w-4 h-4" />
              <span>Create metric</span>
            </Button>
          </template>

          <template #content>
            <form @submit.prevent="submitCreateMetric" class="space-y-4">
              <Input
                v-model="form.name"
                type="text"
                label="Name"
                placeholder="e.g. Registrations completed"
                showAsterisk
              />
              <Input
                v-model="form.description"
                type="textarea"
                label="Description (optional)"
                placeholder="What it measures"
                rows="2"
              />
              <div class="grid grid-cols-2 gap-4">

              <Select
                v-model="form.metricType"
                label="Track"
                :options="metricTypeOptions"
                placeholder="Count, money, %…"
                showAsterisk
              />
              <Select
                v-model="form.chartType"
                label="Chart type"
                :options="chartTypeOptionsForForm"
                placeholder="Line, bar, doughnut"
                showAsterisk
              />
            </div>

              <!-- Distribution options (when metric type is distribution) -->
              <div v-if="form.metricType === 'distribution'" class="pt-2 border-t border-slate-100 space-y-2">
                <p class="text-sm font-medium text-slate-700">Categories</p>
                <div class="space-y-2">
                  <div
                    v-for="(opt, index) in form.distributionOptions"
                    :key="'dist-' + index"
                    class="flex items-center gap-2"
                  >
                    <input
                      :value="opt"
                      @input="updateFormDistributionOption(index, $event.target.value)"
                      type="text"
                      placeholder="e.g. Male"
                      class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <button
                      type="button"
                      @click="removeFormDistributionOption(index)"
                      class="shrink-0 p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50"
                      aria-label="Remove"
                    >
                      <Trash class="w-4 h-4" />
                    </button>
                  </div>
                  <button
                    type="button"
                    @click="addFormDistributionOption"
                    class="w-full py-2 border border-dashed border-slate-300 rounded-lg text-sm text-slate-500 hover:border-indigo-400 hover:text-indigo-600 transition-colors flex items-center justify-center gap-2"
                  >
                    <Plus class="w-4 h-4" />
                    Add option
                  </button>
                </div>
              </div>

              <!-- Advanced (collapsible, collapsed by default when creating) -->
              <div class="pt-4 border-t border-slate-100">
                <div class="rounded-lg border border-slate-200 bg-slate-50/80 overflow-hidden">
                  <button
                    type="button"
                    @click="form.advancedOpen = !form.advancedOpen"
                    class="w-full flex items-center justify-between px-4 py-3 text-left text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100/80 transition-colors"
                  >
                    <span>Advanced</span>
                    <ChevronDown
                      class="w-5 h-5 text-slate-500 shrink-0 transition-transform duration-200"
                      :class="{ 'rotate-180': form.advancedOpen }"
                    />
                  </button>
                  <div v-show="form.advancedOpen" class="px-4 pb-4 pt-1 space-y-4 border-t border-slate-200/80 bg-white/50">
                  <SelectCurrency
                    v-if="form.metricType === 'money'"
                    v-model="form.currency"
                    label="Currency"
                    placeholder="Select currency"
                  />
                  <div>
                    <p class="text-sm font-medium text-slate-700 my-2">Break down by</p>
                    <div class="flex flex-wrap gap-4">
                      <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input
                          :checked="form.breakDownByNetwork"
                          type="checkbox"
                          class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                          @change="setFormBreakdownBy('network')"
                        />
                        <span class="text-sm text-slate-700">By network</span>
                      </label>
                      <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input
                          :checked="form.breakDownByCountry"
                          type="checkbox"
                          class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                          @change="setFormBreakdownBy('country')"
                        />
                        <span class="text-sm text-slate-700">By country</span>
                      </label>
                    </div>
                    <div v-if="form.breakDownByNetwork || form.breakDownByCountry" class="mt-4">
                      <Select
                        v-model="form.breakdownDisplay"
                        label="Display"
                        :options="breakdownDisplayOptions"
                        placeholder="Time series or distribution"
                      />
                    </div>
                  </div>
                  <template v-if="form.chartType !== 'doughnut'">
                    <Input
                      v-model="form.xAxisName"
                      type="text"
                      label="X-axis"
                      placeholder="e.g. Date"
                    />
                    <Input
                      v-model="form.yAxisName"
                      type="text"
                      label="Y-axis"
                      placeholder="e.g. Count"
                    />
                  </template>
                  </div>
                </div>
              </div>

              <!-- Preview -->
              <div class="pt-4 border-t border-slate-100">
                <p class="text-sm font-medium text-slate-700 mb-2">Preview</p>
                <div class="relative bg-slate-50 rounded-lg border border-slate-200 overflow-hidden">
                  <div class="h-56 p-3">
                    <canvas ref="createPreviewCanvas"></canvas>
                  </div>
                </div>
              </div>
            </form>
          </template>
        </Modal>
      </div>
    </div>

    <!-- Edit Metric Modal -->
    <Modal
      ref="editMetricModal"
      size="md"
      header="Edit metric"
      subheader="Update name, type, and chart settings."
      :onHide="onEditModalHide"
      :showFooter="true"
      :showDelineButton="true"
      declineText="Cancel"
      :showApproveButton="true"
      approveText="Save changes"
      :approveAction="saveEditMetric"
      :approveLoading="saving"
      :approveDisabled="!canSaveEditMetric"
      :scrollOnContent="true"
    >
      <template #content>
        <form @submit.prevent="saveEditMetric" class="space-y-4">
          <Input
            v-model="editForm.name"
            type="text"
            label="Name"
            placeholder="e.g. Registrations completed"
            showAsterisk
          />
          <Input
            v-model="editForm.description"
            type="textarea"
            label="Description (optional)"
            placeholder="What it measures"
            rows="2"
          />
          <div class="grid grid-cols-2 gap-4">
          <Select
            v-model="editForm.metricType"
            label="Track"
            :options="metricTypeOptions"
            placeholder="Count, money, %…"
            showAsterisk
          />
          <Select
            v-model="editForm.chartType"
            label="Chart type"
            :options="chartTypeOptionsForEdit"
            placeholder="Line, bar, doughnut"
            showAsterisk
          />
        </div>
          <!-- Distribution options (when metric type is distribution) -->
          <div v-if="editForm.metricType === 'distribution'" class="pt-2 border-t border-slate-100 space-y-3">
            <p class="text-sm font-medium text-slate-700">Distribution options (categories)</p>
            <p class="text-xs text-slate-500">Add the options you want to track. The preview below updates when you change these.</p>
            <div class="space-y-2">
              <div
                v-for="(opt, index) in editForm.distributionOptions"
                :key="'edit-dist-' + index"
                class="flex items-center gap-2"
              >
                <input
                  :value="opt"
                  @input="updateEditDistributionOption(index, $event.target.value)"
                  type="text"
                  placeholder="e.g. Male"
                  class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
                <button
                  type="button"
                  @click="removeEditDistributionOption(index)"
                  class="shrink-0 p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50"
                  aria-label="Remove"
                >
                  <Trash class="w-4 h-4" />
                </button>
              </div>
              <button
                type="button"
                @click="addEditDistributionOption"
                class="w-full py-2 border border-dashed border-slate-300 rounded-lg text-sm text-slate-500 hover:border-indigo-400 hover:text-indigo-600 transition-colors flex items-center justify-center gap-2"
              >
                <Plus class="w-4 h-4" />
                Add option
              </button>
            </div>
          </div>

          <!-- Advanced (collapsible, expanded by default when editing) -->
          <div class="pt-4 border-t border-slate-100">
            <div class="rounded-lg border border-slate-200 bg-slate-50/80 overflow-hidden">
              <button
                type="button"
                @click="editForm.advancedOpen = !editForm.advancedOpen"
                class="w-full flex items-center justify-between px-4 py-3 text-left text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100/80 transition-colors"
              >
                <span>Advanced</span>
                <ChevronDown
                  class="w-5 h-5 text-slate-500 shrink-0 transition-transform duration-200"
                  :class="{ 'rotate-180': editForm.advancedOpen }"
                />
              </button>
              <div v-show="editForm.advancedOpen" class="px-4 pb-4 pt-1 space-y-4 border-t border-slate-200/80 bg-white/50">
              <SelectCurrency
                v-if="editForm.metricType === 'money'"
                v-model="editForm.currency"
                label="Currency"
                placeholder="Select currency"
              />
              <div>
                <p class="text-sm font-medium text-slate-700 my-2">Break down by</p>
                <div class="flex flex-wrap gap-4">
                  <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input
                      :checked="editForm.breakDownByNetwork"
                      type="checkbox"
                      class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                      @change="setEditBreakdownBy('network')"
                    />
                    <span class="text-sm text-slate-700">By network</span>
                  </label>
                  <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input
                      :checked="editForm.breakDownByCountry"
                      type="checkbox"
                      class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                      @change="setEditBreakdownBy('country')"
                    />
                    <span class="text-sm text-slate-700">By country</span>
                  </label>
                </div>
                <div v-if="editForm.breakDownByNetwork || editForm.breakDownByCountry" class="mt-4">
                  <Select
                    v-model="editForm.breakdownDisplay"
                    label="Display"
                    :options="breakdownDisplayOptions"
                    placeholder="Time series or distribution"
                  />
                </div>
              </div>
              <template v-if="editForm.chartType !== 'doughnut'">
                <Input
                  v-model="editForm.xAxisName"
                  type="text"
                  label="X-axis"
                  placeholder="e.g. Date"
                />
                <Input
                  v-model="editForm.yAxisName"
                  type="text"
                  label="Y-axis"
                  placeholder="e.g. Count"
                />
              </template>
              </div>
            </div>
          </div>

          <!-- Preview -->
          <div class="pt-4 border-t border-slate-100">
            <p class="text-sm font-medium text-slate-700 mb-2">Preview</p>
            <div class="relative bg-slate-50 rounded-lg border border-slate-200 overflow-hidden">
              <div class="h-56 p-3">
                <canvas ref="editPreviewCanvas"></canvas>
              </div>
            </div>
          </div>
        </form>
      </template>
    </Modal>

    <!-- Delete Metric Modal -->
    <Modal
      ref="deleteMetricModal"
      size="sm"
      header="Delete metric?"
      subheader="This removes the metric definition and its preview from this page."
      :showFooter="true"
      :showDelineButton="true"
      declineText="Cancel"
      :showApproveButton="true"
      approveText="Delete"
      approveType="danger"
      :approveAction="confirmDeleteMetric"
      :approveLoading="deleting"
      :scrollOnContent="false"
    >
      <template #content>
        <p class="text-sm text-slate-600">
          You are about to delete <span class="font-medium text-slate-900">{{ metricBeingDeleted?.name || 'this metric' }}</span>.
        </p>
      </template>
    </Modal>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-6 py-8">

      <!-- Loading -->
      <div
        v-if="loadingMetrics"
        class="flex justify-center items-center py-24"
      >
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Fetch error -->
      <div
        v-else-if="fetchError"
        class="bg-rose-50 border border-rose-200 rounded-xl p-6 text-center"
      >
        <p class="text-rose-700">{{ fetchError }}</p>
        <Button
          size="md"
          type="light"
          mode="outline"
          buttonClass="mt-4 rounded-lg"
          :action="fetchMetrics"
        >
          Retry
        </Button>
      </div>

      <!-- Empty state -->
      <div
        v-else-if="metrics.length === 0"
        class="flex flex-col items-center justify-center bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center"
      >
        <div class="w-14 h-14 mx-auto rounded-full bg-indigo-50 flex items-center justify-center mb-4">
          <ChartCandlestick class="w-7 h-7 text-indigo-600" />
        </div>
        <h2 class="text-lg font-semibold text-slate-900 mb-2">No custom metrics yet</h2>
        <p class="text-sm text-slate-500 max-w-md mx-auto mb-6">
          Create your first metric to track things like registrations, transaction volume, or completion rates. These trackers can later be linked to steps in your USSD flow.
        </p>
        <Button
          size="md"
          type="primary"
          buttonClass="rounded-lg"
          :action="() => { resetForm(); $refs.createMetricModal?.showModal(); }"
        >
          <Plus class="w-4 h-4" />
          <span>Create metric</span>
        </Button>
      </div>

      <!-- Metric cards -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="metric in metrics"
          :key="metric.id"
          class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hover:border-slate-300 transition-colors"
        >
          <div class="p-5">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h3 class="font-semibold text-slate-900 truncate">{{ metric.name }}</h3>
                <p class="text-sm text-slate-500 line-clamp-2 mt-1">{{ metric.description || '—' }}</p>
              </div>

              <!-- Actions -->
              <Dropdown position="left" dropdownClasses="w-44">
                <template #trigger="props">
                  <button
                    type="button"
                    @click="props.toggleDropdown"
                    class="w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-500 hover:text-slate-700 hover:bg-slate-50 flex items-center justify-center shrink-0"
                  >
                    <EllipsisVertical size="16" />
                  </button>
                </template>
                <template #content="props">
                  <div class="py-1">
                    <div
                      @click="(event) => { goToMetricRecords(metric); props.toggleDropdown(event); }"
                      class="px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 cursor-pointer flex items-center gap-2"
                    >
                      <List size="14" />
                      View records
                    </div>
                    <div
                      @click="(event) => { openEditMetric(metric); props.toggleDropdown(event); }"
                      class="px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 cursor-pointer flex items-center gap-2"
                    >
                      <SquarePen size="14" />
                      Edit
                    </div>
                    <div
                      @click="(event) => { openDeleteMetric(metric); props.toggleDropdown(event); }"
                      class="px-4 py-2.5 text-sm text-rose-700 hover:bg-rose-50 cursor-pointer flex items-center gap-2"
                    >
                      <Trash size="14" />
                      Delete
                    </div>
                  </div>
                </template>
              </Dropdown>
            </div>

            <!-- Chart only – clickable to view underlying records -->
            <div
              class="relative bg-slate-50 rounded-lg border border-slate-200 overflow-hidden mt-4 cursor-pointer hover:border-indigo-300 transition-colors group"
              role="button"
              tabindex="0"
              @click="goToMetricRecords(metric)"
              @keydown.enter="goToMetricRecords(metric)"
              @keydown.space.prevent="goToMetricRecords(metric)"
            >
              <div class="h-56 p-3">
                <canvas :ref="(el) => registerCanvas(metric.id, el)"></canvas>
              </div>
              <div class="absolute inset-0 flex items-center justify-center bg-slate-900/0 group-hover:bg-slate-900/5 transition-colors pointer-events-none rounded-lg">
                <span class="text-sm font-medium text-slate-600 opacity-0 group-hover:opacity-100 transition-opacity bg-white/90 px-3 py-1.5 rounded-lg shadow-sm">
                  View records
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Modal from '@Partials/Modal.vue';
import Button from '@Partials/Button.vue';
import Input from '@Partials/Input.vue';
import Select from '@Partials/Select.vue';
import SelectCurrency from '@Partials/SelectCurrency.vue';
import Dropdown from '@Partials/Dropdown.vue';
import { Plus, ChartCandlestick, EllipsisVertical, SquarePen, Trash, List, ChevronDown } from 'lucide-vue-next';
import { Chart, registerables } from 'chart.js';
import currencies from '@Json/currencies.json';

const metricTypeOptions = [
  { value: 'count', label: 'Count' },
  { value: 'money', label: 'Money' },
  { value: 'percentage', label: 'Percentage' },
  { value: 'distribution', label: 'Distribution' },
];

const CHART_TYPE_OPTIONS = [
  { value: 'line', label: 'Line chart' },
  { value: 'bar', label: 'Bar chart' },
  { value: 'doughnut', label: 'Doughnut chart' },
];

const BREAKDOWN_DISPLAY_OPTIONS = [
  { value: 'time', label: 'Time series' },
  { value: 'distribution', label: 'Distribution' },
];

Chart.register(...registerables);

export default {
  name: 'BusinessKpis',
  components: { Modal, Button, Input, Select, SelectCurrency, Dropdown, Plus, ChartCandlestick, EllipsisVertical, SquarePen, Trash, List, ChevronDown },
  inject: ['appState'],
  data() {
    return {
      metrics: [],
      loadingMetrics: false,
      fetchError: null,
      creating: false,
      saving: false,
      deleting: false,
      chartInstances: {}, // metricId -> Chart instance
      chartCanvases: {}, // metricId -> canvas element
      editPreviewChart: null,
      createPreviewChart: null,
      form: {
        name: '',
        description: '',
        metricType: 'count',
        chartType: 'line',
        breakDownByNetwork: false,
        breakDownByCountry: false,
        breakdownDisplay: 'time',
        currency: 'USD',
        distributionOptions: [],
        xAxisName: '',
        yAxisName: '',
        advancedOpen: false,
      },
      editMetricId: null,
      editForm: {
        name: '',
        description: '',
        metricType: '',
        chartType: '',
        breakDownByNetwork: false,
        breakDownByCountry: false,
        breakdownDisplay: 'time',
        currency: 'USD',
        distributionOptions: [],
        xAxisName: '',
        yAxisName: '',
        advancedOpen: true,
      },
      deleteMetricId: null,
      metricTypeOptions,
      breakdownDisplayOptions: BREAKDOWN_DISPLAY_OPTIONS,
    };
  },
  computed: {
    app() {
      return this.appState?.app ?? null;
    },
    canSubmitMetric() {
      const name = (this.form.name || '').trim();
      return name.length > 0 && !!this.form.metricType && !!this.form.chartType;
    },
    chartTypeOptionsForForm() {
      return this.chartTypeOptionsForMetricType(this.form.metricType);
    },
    chartTypeOptionsForEdit() {
      return this.chartTypeOptionsForMetricType(this.editForm.metricType);
    },
    canSaveEditMetric() {
      const name = (this.editForm.name || '').trim();
      return name.length > 0 && !!this.editForm.metricType && !!this.editForm.chartType;
    },
    metricBeingDeleted() {
      if (!this.deleteMetricId) return null;
      return this.metrics.find(m => m.id === this.deleteMetricId) || null;
    },
  },
  watch: {
    'form.metricType': {
      handler(newType) {
        // Set a sensible default chart type for the selected metric type.
        if (!newType) {
          this.form.chartType = '';
          this.$nextTick(() => this.syncCreatePreviewChart());
          return;
        }

        const defaultByMetricType = {
          count: 'line',
          money: 'bar',
          percentage: 'line',
          distribution: 'doughnut',
        };

        const desired = defaultByMetricType[newType] || 'line';
        const allowed = this.chartTypeOptionsForMetricType(newType).map(o => o.value);
        this.form.chartType = allowed.includes(desired) ? desired : allowed[0] || '';
        if (newType === 'distribution' && !this.form.distributionOptions?.length) {
          this.form.distributionOptions = ['Option A', 'Option B'];
        }
        if (newType !== 'distribution') {
          this.form.distributionOptions = [];
        }
        this.$nextTick(() => this.syncCreatePreviewChart());
      },
      immediate: true,
    },
    'form.chartType': function () {
      this.$nextTick(() => this.syncCreatePreviewChart());
    },
    'form.breakDownByNetwork': function () {
      this.$nextTick(() => this.syncCreatePreviewChart());
    },
    'form.breakDownByCountry': function () {
      this.$nextTick(() => this.syncCreatePreviewChart());
    },
    'form.breakdownDisplay': function () {
      this.$nextTick(() => this.syncCreatePreviewChart());
    },
    'form.distributionOptions': {
      handler() {
        this.$nextTick(() => this.syncCreatePreviewChart());
      },
      deep: true,
    },
    'form.xAxisName': function () {
      this.$nextTick(() => this.syncCreatePreviewChart());
    },
    'form.yAxisName': function () {
      this.$nextTick(() => this.syncCreatePreviewChart());
    },
    'form.currency': function () {
      this.$nextTick(() => this.syncCreatePreviewChart());
    },
    'editForm.metricType': {
      handler(newType) {
        if (!newType) {
          this.editForm.chartType = '';
          return;
        }

        const defaultByMetricType = {
          count: 'line',
          money: 'bar',
          percentage: 'line',
          distribution: 'doughnut',
        };

        const desired = defaultByMetricType[newType] || 'line';
        const allowed = this.chartTypeOptionsForMetricType(newType).map(o => o.value);
        this.editForm.chartType = allowed.includes(this.editForm.chartType) ? this.editForm.chartType : (allowed.includes(desired) ? desired : allowed[0] || '');
        if (newType === 'distribution' && (!this.editForm.distributionOptions || !this.editForm.distributionOptions.length)) {
          this.editForm.distributionOptions = ['Option A', 'Option B'];
        }
        if (newType !== 'distribution') {
          this.editForm.distributionOptions = [];
        }

        this.$nextTick(() => this.syncEditPreviewChart());
      },
      immediate: false,
    },
    'editForm.chartType': function () {
      this.$nextTick(() => this.syncEditPreviewChart());
    },
    'editForm.breakDownByNetwork': function () {
      this.$nextTick(() => this.syncEditPreviewChart());
    },
    'editForm.breakDownByCountry': function () {
      this.$nextTick(() => this.syncEditPreviewChart());
    },
    'editForm.breakdownDisplay': function () {
      this.$nextTick(() => this.syncEditPreviewChart());
    },
    'editForm.distributionOptions': {
      handler() {
        this.$nextTick(() => this.syncEditPreviewChart());
      },
      deep: true,
    },
    'editForm.xAxisName': function () {
      this.$nextTick(() => this.syncEditPreviewChart());
    },
    'editForm.yAxisName': function () {
      this.$nextTick(() => this.syncEditPreviewChart());
    },
    'editForm.currency': function () {
      this.$nextTick(() => this.syncEditPreviewChart());
    },
    metrics: {
      handler() {
        this.$nextTick(() => this.syncCharts());
      },
      deep: true,
    },
  },
  methods: {
    destroyEditPreviewChart() {
      if (!this.editPreviewChart) return;
      const chart = this.editPreviewChart;
      this.editPreviewChart = null;
      try {
        chart.destroy();
      } catch (_) {}
    },
    onEditModalHide() {
      this.destroyEditPreviewChart();
      this.editMetricId = null;
    },
    destroyCreatePreviewChart() {
      if (!this.createPreviewChart) return;
      const chart = this.createPreviewChart;
      this.createPreviewChart = null;
      try {
        chart.destroy();
      } catch (_) {}
    },
    onCreateModalShow() {
      this.$nextTick(() => {
        const trySync = (attempt = 0) => {
          const canvas = this.$refs.createPreviewCanvas;
          if (canvas && document.body.contains(canvas)) {
            this.syncCreatePreviewChart();
            return;
          }
          if (attempt < 5) {
            setTimeout(() => trySync(attempt + 1), 80);
          }
        };
        setTimeout(() => trySync(), 100);
      });
    },
    onCreateModalHide() {
      this.destroyCreatePreviewChart();
    },
    syncCreatePreviewChart() {
      const canvas = this.$refs.createPreviewCanvas;
      if (!canvas || !document.body.contains(canvas)) return;

      const metricType = this.form.metricType;
      const chartType = this.form.chartType;
      if (!metricType || !chartType) return;

      const breakDownByNetwork = !!this.form.breakDownByNetwork;
      const breakDownByCountry = !!this.form.breakDownByCountry;
      const breakdownDisplay = this.form.breakdownDisplay || 'time';

      this.destroyCreatePreviewChart();
      requestAnimationFrame(() => {
        if (!this.$refs.createPreviewCanvas || !document.body.contains(this.$refs.createPreviewCanvas)) return;
        this.destroyCreatePreviewChart();
        this.createPreviewChart = this.createSampleChart(this.$refs.createPreviewCanvas, {
          metricType,
          chartType,
          breakDownByNetwork,
          breakDownByCountry,
          breakdownDisplay,
          distributionOptions: this.form.distributionOptions || [],
          xAxisName: (this.form.xAxisName || '').trim(),
          yAxisName: (this.form.yAxisName || '').trim(),
          currency: metricType === 'money' ? (this.form.currency || 'USD') : null,
        });
      });
    },
    syncEditPreviewChart() {
      const canvas = this.$refs.editPreviewCanvas;
      if (!canvas || !document.body.contains(canvas)) return;

      const metricType = this.editForm.metricType;
      const chartType = this.editForm.chartType;
      if (!metricType || !chartType) return;

      const breakDownByNetwork = !!this.editForm.breakDownByNetwork;
      const breakDownByCountry = !!this.editForm.breakDownByCountry;
      const breakdownDisplay = this.editForm.breakdownDisplay || 'time';

      this.destroyEditPreviewChart();
      requestAnimationFrame(() => {
        const c = this.$refs.editPreviewCanvas;
        if (!c || !document.body.contains(c)) return;
        this.destroyEditPreviewChart();
        this.editPreviewChart = this.createSampleChart(c, {
          metricType,
          chartType,
          breakDownByNetwork,
          breakDownByCountry,
          breakdownDisplay,
          distributionOptions: this.editForm.distributionOptions || [],
          xAxisName: (this.editForm.xAxisName || '').trim(),
          yAxisName: (this.editForm.yAxisName || '').trim(),
          currency: metricType === 'money' ? (this.editForm.currency || 'USD') : null,
        });
      });
    },
    getSampleChartConfig({ metricType, chartType, breakDownByNetwork = false, breakDownByCountry = false, breakdownDisplay = 'time', distributionOptions = [], xAxisName = '', yAxisName = '' }) {
      const labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

      const sampleSeriesByType = {
        count: [12, 18, 22, 16, 29, 35, 31],
        money: [1200, 950, 1800, 1420, 2100, 2650, 1980],
        percentage: [92.1, 93.4, 91.8, 94.2, 95.0, 94.6, 95.3],
      };

      const applyAxisTitles = (op) => {
        if (!op) return;
        op.scales = op.scales || {};
        if (xAxisName) op.scales.x = { ...op.scales.x, title: { display: true, text: xAxisName } };
        if (yAxisName) op.scales.y = { ...op.scales.y, title: { display: true, text: yAxisName } };
      };

      // Distribution: categories as labels (e.g. Male, Female)
      if (metricType === 'distribution') {
        const distLabels = (distributionOptions && distributionOptions.length) ? distributionOptions.map(s => (s || '').trim() || 'Option').filter(Boolean) : ['Option A', 'Option B', 'Option C'];
        const distData = distLabels.map((_, i) => [40, 55, 30, 62, 45, 38, 50][i % 7] || 40);
        if (chartType === 'doughnut') {
          const res = {
            labels: distLabels,
            datasets: [{
              data: distData,
              backgroundColor: distLabels.map((_, i) => ['rgba(99, 102, 241, 0.75)', 'rgba(16, 185, 129, 0.75)', 'rgba(245, 158, 11, 0.75)', 'rgba(239, 68, 68, 0.75)', 'rgba(139, 92, 246, 0.75)'][i % 5]),
              borderColor: '#ffffff',
              borderWidth: 2,
            }],
            optionsPatch: { cutout: '68%' },
          };
          applyAxisTitles(res.optionsPatch);
          return res;
        }
        const res = {
          labels: distLabels,
          datasets: [{
            label: 'Count',
            data: distData,
            borderWidth: chartType === 'bar' ? 0 : 2,
            backgroundColor: 'rgba(99, 102, 241, 0.35)',
            borderRadius: 8,
          }],
          optionsPatch: { scales: { y: { beginAtZero: true } } },
        };
        applyAxisTitles(res.optionsPatch);
        return res;
      }

      const breakdownColors = [
        { border: 'rgb(99, 102, 241)', fill: 'rgba(99, 102, 241, 0.35)' },
        { border: 'rgb(16, 185, 129)', fill: 'rgba(16, 185, 129, 0.35)' },
        { border: 'rgb(245, 158, 11)', fill: 'rgba(245, 158, 11, 0.35)' },
      ];

      const byNetworkLabels = ['MTN', 'Airtel', 'Vodacom'];
      const byCountryLabels = ['Kenya', 'Uganda', 'Tanzania'];
      const byNetworkSample = [
        [420, 380, 510, 470, 620, 580, 310],
        [380, 410, 390, 440, 520, 490, 350],
        [400, 360, 500, 510, 560, 510, 340],
      ];
      const byCountrySample = [
        [520, 480, 610, 570, 720, 680, 410],
        [350, 390, 370, 420, 500, 470, 330],
        [330, 280, 420, 430, 480, 430, 260],
      ];
      const byNetworkPercentageSample = [
        [92, 94, 88, 91, 93, 90, 95],
        [88, 90, 92, 89, 91, 93, 90],
        [90, 91, 89, 94, 92, 91, 93],
      ];
      const byCountryPercentageSample = [
        [94, 91, 93, 90, 95, 92, 94],
        [89, 92, 88, 91, 90, 93, 89],
        [91, 90, 94, 88, 92, 91, 93],
      ];

      if (chartType === 'doughnut') {
        const doughnutLabels = breakDownByNetwork
          ? byNetworkLabels
          : breakDownByCountry
            ? byCountryLabels
            : ['Completed', 'Dropped', 'Timed out', 'Failed'];
        const doughnutData = metricType === 'percentage'
          ? (breakDownByNetwork ? [92, 88, 90] : breakDownByCountry ? [94, 89, 91] : [95, 92, 94, 93])
          : (breakDownByNetwork ? [42, 35, 23] : breakDownByCountry ? [48, 32, 20] : [58, 22, 14, 6]);

        const res = {
          labels: doughnutLabels,
          datasets: [
            {
              data: doughnutData,
              backgroundColor: doughnutData.length === 3
                ? ['rgba(99, 102, 241, 0.75)', 'rgba(16, 185, 129, 0.75)', 'rgba(245, 158, 11, 0.75)']
                : ['rgba(16, 185, 129, 0.75)', 'rgba(245, 158, 11, 0.75)', 'rgba(99, 102, 241, 0.75)', 'rgba(239, 68, 68, 0.75)'],
              borderColor: '#ffffff',
              borderWidth: 2,
            },
          ],
          optionsPatch: { cutout: '68%' },
        };
        return res;
      }

      const useBreakdown = breakDownByNetwork || breakDownByCountry;
      const breakdownLabels = breakDownByNetwork ? byNetworkLabels : byCountryLabels;
      const breakdownSeries = breakDownByNetwork
        ? (metricType === 'percentage' ? byNetworkPercentageSample : byNetworkSample)
        : (metricType === 'percentage' ? byCountryPercentageSample : byCountrySample);

      const yScale = (() => {
        if (metricType === 'percentage') return { min: 0, max: 100 };
        return { beginAtZero: true };
      })();

      // Breakdown (network OR country) + display as distribution: grouping without time (one value per category)
      if (useBreakdown && breakdownDisplay === 'distribution') {
        const distValues = metricType === 'percentage'
          ? breakdownLabels.map((_, i) => [92, 88, 90, 91, 89, 93][i % 6] || 90)
          : breakdownLabels.map((_, i) => [420, 380, 400, 350, 410, 390][i % 6] || 400);
        if (chartType === 'doughnut') {
          const res = {
            labels: breakdownLabels,
            datasets: [{
              data: distValues,
              backgroundColor: breakdownLabels.map((_, i) => ['rgba(99, 102, 241, 0.75)', 'rgba(16, 185, 129, 0.75)', 'rgba(245, 158, 11, 0.75)'][i % 3]),
              borderColor: '#ffffff',
              borderWidth: 2,
            }],
            optionsPatch: { cutout: '68%' },
          };
          return res;
        }
        const datasetLabelByType = { count: 'Count', money: 'Amount', percentage: 'Percent' };
        const res = {
          labels: breakdownLabels,
          datasets: [{
            label: datasetLabelByType[metricType] || 'Value',
            data: distValues,
            backgroundColor: 'rgba(99, 102, 241, 0.35)',
            borderWidth: 0,
            borderRadius: 6,
          }],
          optionsPatch: {
            scales: { y: yScale },
            plugins: { legend: { display: false } },
          },
        };
        applyAxisTitles(res.optionsPatch);
        return res;
      }

      // Breakdown + display as time series: values over time (one series per network/country, or both+time = by network over time)
      if (useBreakdown && breakdownSeries.length > 0) {
        const datasets = breakdownSeries.map((data, i) => {
          const c = breakdownColors[i % breakdownColors.length];
          const base = {
            label: breakdownLabels[i],
            data,
            borderColor: c.border,
            backgroundColor: chartType === 'bar' ? c.fill : c.border.replace('rgb', 'rgba').replace(')', ', 0.18)'),
            borderWidth: 2,
            pointRadius: chartType === 'line' ? 3 : 0,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: c.border,
            pointBorderWidth: 2,
          };
          if (chartType === 'bar') {
            return { ...base, borderWidth: 0, borderRadius: 6 };
          }
          return { ...base, fill: true, tension: 0.35 };
        });

        const res = {
          labels,
          datasets,
          optionsPatch: {
            scales: { y: yScale },
            plugins: { legend: { display: true, position: 'bottom' } },
          },
        };
        applyAxisTitles(res.optionsPatch);
        return res;
      }

      const raw = sampleSeriesByType[metricType] || sampleSeriesByType.count;
      const datasetLabelByType = {
        count: 'Count',
        money: 'Amount',
        percentage: 'Percent',
      };

      const baseDataset = {
        label: datasetLabelByType[metricType] || 'Value',
        data: raw,
        borderColor: 'rgb(99, 102, 241)',
        backgroundColor: 'rgba(99, 102, 241, 0.18)',
        borderWidth: 2,
        pointRadius: chartType === 'line' ? 3 : 0,
        pointBackgroundColor: '#ffffff',
        pointBorderColor: 'rgb(99, 102, 241)',
        pointBorderWidth: 2,
      };

      if (chartType === 'bar') {
        const res = {
          labels,
          datasets: [
            {
              ...baseDataset,
              borderWidth: 0,
              backgroundColor: 'rgba(99, 102, 241, 0.35)',
              borderRadius: 8,
            },
          ],
          optionsPatch: { scales: { y: yScale } },
        };
        applyAxisTitles(res.optionsPatch);
        return res;
      }

      const lineRes = {
        labels,
        datasets: [{ ...baseDataset, fill: true, tension: 0.35 }],
        optionsPatch: { scales: { y: yScale } },
      };
      applyAxisTitles(lineRes.optionsPatch);
      return lineRes;
    },
    formatMoneyForChart(value, currencyCode) {
      if (value == null || currencyCode == null) return String(value ?? '');
      const num = Number(value);
      if (Number.isNaN(num)) return String(value);
      const decimals = currencies[currencyCode]?.decimal_digits ?? 2;
      const formatted = num.toLocaleString('en-US', {
        minimumFractionDigits: Math.min(decimals, 2),
        maximumFractionDigits: 2,
      });
      const symbol = currencies[currencyCode]?.symbol_native ?? currencies[currencyCode]?.symbol ?? currencyCode;
      const symbolAfter = ['EUR', 'CAD', 'AUD', 'BRL', 'CZK', 'DKK', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'THB', 'TRY'];
      if (symbolAfter.includes(currencyCode)) {
        return `${formatted} ${symbol}`;
      }
      return `${symbol}${formatted}`;
    },
    createSampleChart(canvas, { metricType, chartType, breakDownByNetwork = false, breakDownByCountry = false, breakdownDisplay = 'time', distributionOptions = [], xAxisName = '', yAxisName = '', currency = null }) {
      const { labels, datasets, optionsPatch } = this.getSampleChartConfig({
        metricType,
        chartType,
        breakDownByNetwork,
        breakDownByCountry,
        breakdownDisplay,
        distributionOptions,
        xAxisName,
        yAxisName,
      });
      const useBreakdown = breakDownByNetwork || breakDownByCountry;
      const isDistributionView = breakdownDisplay === 'distribution';
      const effectiveChartType = useBreakdown && isDistributionView && chartType === 'line' ? 'bar' : chartType;
      const showLegend = datasets.length > 1;
      const isMoney = metricType === 'money' && currency;

      const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
        plugins: {
          legend: { display: showLegend, position: 'bottom' },
          tooltip: {
            enabled: true,
            ...(isMoney ? {
              callbacks: {
                label: (context) => {
                  const label = context.dataset.label ? `${context.dataset.label}: ` : '';
                  return label + this.formatMoneyForChart(context.raw, currency);
                },
              },
            } : {}),
          },
        },
      };

      if (effectiveChartType === 'doughnut') {
        const doughnutOptions = {
          ...commonOptions,
          ...(optionsPatch || {}),
        };
        if (isMoney && doughnutOptions.plugins?.tooltip) {
          doughnutOptions.plugins.tooltip.callbacks = {
            ...doughnutOptions.plugins.tooltip.callbacks,
            label: (context) => {
              const label = context.label ? `${context.label}: ` : '';
              return label + this.formatMoneyForChart(context.raw, currency);
            },
          };
        }
        return new Chart(canvas, {
          type: 'doughnut',
          data: { labels, datasets },
          options: doughnutOptions,
        });
      }

      const yScale = {
        beginAtZero: true,
        grid: { color: 'rgba(148, 163, 184, 0.35)' },
        ...(isMoney ? {
          ticks: {
            callback: (value) => this.formatMoneyForChart(value, currency),
          },
        } : {}),
      };

      const barLineOptions = {
        ...commonOptions,
        ...(optionsPatch || {}),
        scales: {
          ...(optionsPatch?.scales || {}),
          x: { grid: { display: false } },
          y: { ...(optionsPatch?.scales?.y || {}), ...yScale },
        },
      };
      return new Chart(canvas, {
        type: effectiveChartType === 'bar' ? 'bar' : 'line',
        data: { labels, datasets },
        options: barLineOptions,
      });
    },
    chartTypeOptionsForMetricType(metricType) {
      if (!metricType) return CHART_TYPE_OPTIONS.filter(o => o.value !== 'doughnut');
      if (metricType === 'percentage' || metricType === 'distribution') return CHART_TYPE_OPTIONS;
      return CHART_TYPE_OPTIONS.filter(o => o.value !== 'doughnut');
    },
    normalizeMetric(metric) {
      const metricType = metric?.metricType;
      const defaultByMetricType = {
        count: 'line',
        money: 'bar',
        percentage: 'line',
        distribution: 'doughnut',
      };

      const allowed = this.chartTypeOptionsForMetricType(metricType).map(o => o.value);
      const desiredDefault = defaultByMetricType[metricType] || 'line';
      const chartType = allowed.includes(metric?.chartType) ? metric.chartType : (allowed.includes(desiredDefault) ? desiredDefault : allowed[0]);

      const out = { ...metric, chartType };
      if (metricType === 'money' && out.currency == null) out.currency = 'USD';
      if (metricType === 'distribution' && !Array.isArray(out.distributionOptions)) out.distributionOptions = [];
      return out;
    },
    fetchMetrics() {
      if (!this.app?.id) {
        this.metrics = [];
        return;
      }
      this.loadingMetrics = true;
      this.fetchError = null;
      axios
        .get(`/api/apps/${this.app.id}/business-kpis`)
        .then((response) => {
          const data = response?.data?.data ?? response?.data ?? [];
          this.metrics = Array.isArray(data) ? data.map(this.normalizeMetric) : [];
        })
        .catch((err) => {
          this.fetchError = err?.response?.data?.message ?? err?.message ?? 'Failed to load metrics';
          this.metrics = [];
        })
        .finally(() => {
          this.loadingMetrics = false;
          this.$nextTick(() => this.syncCharts());
        });
    },
    resetForm() {
      this.form = {
        name: '',
        description: '',
        metricType: 'count',
        chartType: 'line',
        breakDownByNetwork: false,
        breakDownByCountry: false,
        breakdownDisplay: 'time',
        currency: 'USD',
        distributionOptions: [],
        xAxisName: '',
        yAxisName: '',
        advancedOpen: false,
      };
    },
    setFormBreakdownBy(which) {
      if (which === 'network') {
        if (this.form.breakDownByNetwork) {
          this.form.breakDownByNetwork = false;
        } else {
          this.form.breakDownByNetwork = true;
          this.form.breakDownByCountry = false;
        }
      } else {
        if (this.form.breakDownByCountry) {
          this.form.breakDownByCountry = false;
        } else {
          this.form.breakDownByCountry = true;
          this.form.breakDownByNetwork = false;
        }
      }
      this.$nextTick(() => this.syncCreatePreviewChart());
    },
    setEditBreakdownBy(which) {
      if (which === 'network') {
        if (this.editForm.breakDownByNetwork) {
          this.editForm.breakDownByNetwork = false;
        } else {
          this.editForm.breakDownByNetwork = true;
          this.editForm.breakDownByCountry = false;
        }
      } else {
        if (this.editForm.breakDownByCountry) {
          this.editForm.breakDownByCountry = false;
        } else {
          this.editForm.breakDownByCountry = true;
          this.editForm.breakDownByNetwork = false;
        }
      }
      this.$nextTick(() => this.syncEditPreviewChart());
    },
    addFormDistributionOption() {
      this.form.distributionOptions = [...(this.form.distributionOptions || []), ''];
    },
    removeFormDistributionOption(index) {
      this.form.distributionOptions = this.form.distributionOptions.filter((_, i) => i !== index);
    },
    updateFormDistributionOption(index, value) {
      const next = [...(this.form.distributionOptions || [])];
      next[index] = value;
      this.form.distributionOptions = next;
    },
    addEditDistributionOption() {
      this.editForm.distributionOptions = [...(this.editForm.distributionOptions || []), ''];
    },
    removeEditDistributionOption(index) {
      this.editForm.distributionOptions = this.editForm.distributionOptions.filter((_, i) => i !== index);
    },
    updateEditDistributionOption(index, value) {
      const next = [...(this.editForm.distributionOptions || [])];
      next[index] = value;
      this.editForm.distributionOptions = next;
    },
    submitCreateMetric(hideModal) {
      const name = (this.form.name || '').trim();
      const metricType = this.form.metricType;
      const chartType = this.form.chartType;
      if (!name || !metricType || !chartType || !this.app?.id) return;

      this.creating = true;
      const payload = {
        name,
        description: (this.form.description || '').trim() || null,
        metric_type: metricType,
        chart_type: chartType,
        break_down_by_network: !!this.form.breakDownByNetwork,
        break_down_by_country: !!this.form.breakDownByCountry,
        breakdown_display: this.form.breakdownDisplay || 'time',
        currency: metricType === 'money' ? (this.form.currency || 'USD') : null,
        distribution_options: metricType === 'distribution' ? (this.form.distributionOptions || []).map(s => (s || '').trim()).filter(Boolean) : null,
        x_axis_name: (this.form.xAxisName || '').trim() || null,
        y_axis_name: (this.form.yAxisName || '').trim() || null,
      };
      axios
        .post(`/api/apps/${this.app.id}/business-kpis`, payload)
        .then((response) => {
          const created = response?.data?.business_kpi;
          if (created) {
            this.metrics = [...this.metrics, this.normalizeMetric(created)];
            this.$nextTick(() => this.syncCharts());
          }
          this.resetForm();
          const close = typeof hideModal === 'function' ? hideModal : () => this.$refs.createMetricModal?.hideModal?.();
          close();
        })
        .catch((err) => {
          const msg = err?.response?.data?.message ?? err?.response?.data?.errors ?? err?.message ?? 'Failed to create metric';
          this.$emit?.('toast', { type: 'error', message: typeof msg === 'string' ? msg : Object.values(msg).flat().join(', ') });
        })
        .finally(() => {
          this.creating = false;
        });
    },
    goToMetricRecords(metric) {
      if (!this.app?.id || !metric?.id) return;
      this.$router.push({
        name: 'show-app-metric-records',
        params: { app_id: this.app.id, metric_id: metric.id },
      });
    },
    openEditMetric(metric) {
      this.editMetricId = metric.id;
      this.editForm = {
        name: metric.name,
        description: metric.description,
        metricType: metric.metricType,
        chartType: metric.chartType,
        breakDownByNetwork: !!metric.breakDownByNetwork,
        breakDownByCountry: !!metric.breakDownByCountry,
        breakdownDisplay: metric.breakdownDisplay || 'time',
        currency: metric.metricType === 'money' ? (metric.currency || 'USD') : 'USD',
        distributionOptions: Array.isArray(metric.distributionOptions) && metric.distributionOptions.length
          ? [...metric.distributionOptions]
          : (metric.metricType === 'distribution' ? ['Option A', 'Option B'] : []),
        xAxisName: metric.xAxisName || '',
        yAxisName: metric.yAxisName || '',
        advancedOpen: true,
      };
      this.$refs.editMetricModal?.showModal?.();
      // Wait for modal content and canvas to be in the DOM before drawing the preview chart.
      this.$nextTick(() => {
        setTimeout(() => this.syncEditPreviewChart(), 150);
      });
    },
    saveEditMetric(hideModal) {
      if (!this.editMetricId || !this.app?.id) return;
      const name = (this.editForm.name || '').trim();
      if (!name || !this.editForm.metricType || !this.editForm.chartType) return;

      this.saving = true;
      const payload = {
        name,
        description: (this.editForm.description || '').trim() || null,
        metric_type: this.editForm.metricType,
        chart_type: this.editForm.chartType,
        break_down_by_network: !!this.editForm.breakDownByNetwork,
        break_down_by_country: !!this.editForm.breakDownByCountry,
        breakdown_display: this.editForm.breakdownDisplay || 'time',
        currency: this.editForm.metricType === 'money' ? (this.editForm.currency || 'USD') : null,
        distribution_options: this.editForm.metricType === 'distribution' ? (this.editForm.distributionOptions || []).map(s => (s || '').trim()).filter(Boolean) : null,
        x_axis_name: (this.editForm.xAxisName || '').trim() || null,
        y_axis_name: (this.editForm.yAxisName || '').trim() || null,
      };
      const editedMetricId = this.editMetricId;
      axios
        .put(`/api/apps/${this.app.id}/business-kpis/${editedMetricId}`, payload)
        .then((response) => {
          const updated = response?.data?.business_kpi;
          if (updated) {
            this.metrics = this.metrics.map(m => (m.id === editedMetricId ? this.normalizeMetric(updated) : m));
            this.destroyChart(editedMetricId);
            this.$nextTick(() => this.syncCharts());
          }
          const close = typeof hideModal === 'function' ? hideModal : () => this.$refs.editMetricModal?.hideModal?.();
          close();
        })
        .catch((err) => {
          const msg = err?.response?.data?.message ?? err?.response?.data?.errors ?? err?.message ?? 'Failed to update metric';
          this.$emit?.('toast', { type: 'error', message: typeof msg === 'string' ? msg : Object.values(msg).flat().join(', ') });
        })
        .finally(() => {
          this.saving = false;
        });
    },
    openDeleteMetric(metric) {
      this.deleteMetricId = metric.id;
      this.$refs.deleteMetricModal?.showModal?.();
    },
    confirmDeleteMetric(hideModal) {
      if (!this.deleteMetricId || !this.app?.id) return;
      this.deleting = true;
      const deletingId = this.deleteMetricId;
      axios
        .delete(`/api/apps/${this.app.id}/business-kpis/${deletingId}`)
        .then(() => {
          this.metrics = this.metrics.filter(m => m.id !== deletingId);
          this.destroyChart(deletingId);
          delete this.chartCanvases[deletingId];
          this.deleteMetricId = null;
          this.$nextTick(() => this.syncCharts());
          const close = typeof hideModal === 'function' ? hideModal : () => this.$refs.deleteMetricModal?.hideModal?.();
          close();
        })
        .catch((err) => {
          const msg = err?.response?.data?.message ?? err?.message ?? 'Failed to delete metric';
          this.$emit?.('toast', { type: 'error', message: msg });
        })
        .finally(() => {
          this.deleting = false;
        });
    },
    metricTypeLabel(value) {
      const o = this.metricTypeOptions.find(opt => opt.value === value);
      return o ? o.label : value;
    },
    metricTypeBadgeClass(value) {
      const classes = {
        count: 'bg-indigo-100 text-indigo-700',
        money: 'bg-emerald-100 text-emerald-700',
        percentage: 'bg-amber-100 text-amber-700',
        distribution: 'bg-sky-100 text-sky-700',
      };
      return classes[value] || 'bg-slate-100 text-slate-700';
    },
    chartTypeLabel(value) {
      if (!value) return 'Line chart';
      return CHART_TYPE_OPTIONS.find(o => o.value === value)?.label ?? value;
    },
    registerCanvas(metricId, el) {
      if (!metricId) return;
      if (el) {
        this.chartCanvases[metricId] = el;
      } else {
        // Vue is clearing the ref (e.g. on unmount or re-render); destroy chart and drop canvas ref
        this.safeDestroyChart(metricId);
        delete this.chartCanvases[metricId];
      }
    },
    /**
     * Destroy a single chart, swallowing any error (e.g. canvas already removed from DOM).
     */
    safeDestroyChart(metricId) {
      const chart = this.chartInstances[metricId];
      if (!chart) return;
      try {
        chart.destroy();
      } catch (_) {
        // Chart.js may throw if canvas is already detached (e.g. after navigation)
      }
      delete this.chartInstances[metricId];
    },
    destroyCharts() {
      Object.keys(this.chartInstances).forEach(metricId => this.safeDestroyChart(metricId));
      this.chartInstances = {};
      this.chartCanvases = {};
    },
    destroyChart(metricId) {
      this.safeDestroyChart(metricId);
      delete this.chartCanvases[metricId];
    },
    syncCharts() {
      const metricIds = new Set(this.metrics.map(m => m.id));

      // Clean up charts for removed metrics
      Object.keys(this.chartInstances).forEach(metricId => {
        if (!metricIds.has(metricId)) this.destroyChart(metricId);
      });

      // Create (or recreate) charts for current metrics (only if canvas is still in DOM)
      this.metrics.forEach(metric => {
        const canvas = this.chartCanvases[metric.id];
        if (!canvas || !document.body.contains(canvas)) return;

        const existing = this.chartInstances[metric.id];
        const existingType = existing?.config?.type;
        const breakDownByNetwork = !!metric.breakDownByNetwork;
        const breakDownByCountry = !!metric.breakDownByCountry;
        const breakdownDisplay = metric.breakdownDisplay || 'time';
        const useBreakdown = breakDownByNetwork || breakDownByCountry;
        const isDistributionView = breakdownDisplay === 'distribution';
        const effectiveChartType = useBreakdown && isDistributionView && metric.chartType === 'line' ? 'bar' : metric.chartType;

        if (!existing || existingType !== effectiveChartType) {
          if (existing) this.safeDestroyChart(metric.id);
          this.chartInstances[metric.id] = this.createPlaceholderChart(canvas, metric);
        }
      });
    },
    createPlaceholderChart(canvas, metric) {
      const breakDownByNetwork = !!metric.breakDownByNetwork;
      const breakDownByCountry = !!metric.breakDownByCountry;
      const breakdownDisplay = metric.breakdownDisplay || 'time';
      const { labels, datasets, optionsPatch } = this.getSampleChartConfig({
        metricType: metric.metricType,
        chartType: metric.chartType,
        breakDownByNetwork,
        breakDownByCountry,
        breakdownDisplay,
        distributionOptions: metric.distributionOptions || [],
        xAxisName: (metric.xAxisName || '').trim(),
        yAxisName: (metric.yAxisName || '').trim(),
      });

      const useBreakdown = breakDownByNetwork || breakDownByCountry;
      const isDistributionView = breakdownDisplay === 'distribution';
      const effectiveChartType = useBreakdown && isDistributionView && metric.chartType === 'line' ? 'bar' : metric.chartType;

      const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 450 },
        plugins: {
          legend: { display: datasets.length > 1, position: 'bottom' },
          tooltip: { enabled: false },
        },
      };

      if (effectiveChartType === 'doughnut') {
        return new Chart(canvas, {
          type: 'doughnut',
          data: { labels, datasets },
          options: {
            ...commonOptions,
            plugins: {
              ...commonOptions.plugins,
              legend: { display: true, position: 'bottom' },
            },
            ...(optionsPatch || {}),
          },
        });
      }

      const isMoney = metric.metricType === 'money' && metric.currency;
      const yScale = {
        grid: { color: 'rgba(148, 163, 184, 0.35)' },
        beginAtZero: true,
        ...(isMoney ? {
          ticks: {
            callback: (value) => this.formatMoneyForChart(value, metric.currency),
          },
        } : {}),
      };

      const barLineOptions = {
        ...commonOptions,
        ...(optionsPatch || {}),
        scales: {
          ...(optionsPatch?.scales || {}),
          x: { grid: { display: false }, ticks: { maxTicksLimit: 5 } },
          y: { ...(optionsPatch?.scales?.y || {}), ...yScale },
        },
      };
      return new Chart(canvas, {
        type: effectiveChartType === 'bar' ? 'bar' : 'line',
        data: { labels, datasets },
        options: barLineOptions,
      });
    },
  },
  mounted() {
    this.fetchMetrics();
  },
  beforeUnmount() {
    this.destroyCharts();
    this.destroyEditPreviewChart();
    this.destroyCreatePreviewChart();
  },
};
</script>
