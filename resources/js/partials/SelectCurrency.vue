<template>
  <Select
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    v-bind="$attrs"
    :clearable="clearable"
    :options="currencyOptions"
    noResultsText="No currencies found"
    :searchableFields="['label', 'value']"
  >
    <template #selectedOption="{ selectedOption }">
      <div v-if="selectedOption" class="flex items-center space-x-2">
        <span class="text-slate-600 text-sm font-medium">{{ selectedOption.symbol }}</span>
        <span class="text-slate-700 text-sm truncate">{{ selectedOption.label }}</span>
      </div>
      <span v-else class="text-slate-700 text-sm">{{ placeholder }}</span>
    </template>

    <template #option="{ option }">
      <div class="flex items-center space-x-2">
        <span class="text-slate-600 text-sm font-medium w-8">{{ option.symbol }}</span>
        <span class="text-slate-700 text-sm">{{ option.label }}</span>
      </div>
    </template>
  </Select>
</template>

<script>
import Select from '@Partials/Select.vue';
import currencies from '@Json/currencies.json';

export default {
  components: { Select },

  props: {
    modelValue: {
      type: [String, null],
      default: null,
    },
    clearable: {
      type: Boolean,
      default: false,
    },
    placeholder: {
      type: [String, null],
      default: 'Select currency',
    },
  },

  emits: ['update:modelValue'],

  data() {
    return {
      currencyOptions: [],
    };
  },

  created() {
    this.currencyOptions = Object.keys(currencies).map((code) => {
      const c = currencies[code];
      return {
        value: code,
        label: c.name + ' (' + code + ')',
        symbol: c.symbol_native || c.symbol || code,
      };
    }).sort((a, b) => a.label.localeCompare(b.label));
  },
};
</script>
