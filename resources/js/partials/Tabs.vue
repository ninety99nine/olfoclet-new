<template>

    <div
        :class="[
            'rounded-lg p-1 items-center gap-1',
            { 'flex bg-gray-100' : design == '1' },
            { 'inline-flex  flex-wrap' : design == '2' },
        ]">

        <template v-for="tab in tabs" :key="tab.value">

            <button
                type="button"
                @click.prevent.stop="selectTab(tab.value)"
                :class="design == 1 ? [
                    'w-full flex items-center justify-center gap-x-2 rounded-md transition cursor-pointer font-medium',
                    size === 'md'
                        ? 'px-4 py-2 text-sm'
                        : 'px-4 py-1 text-sm',
                    tab.value === modelValue
                        ? 'bg-white shadow text-gray-900'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-white'
                ] : [
                    { 'px-4 py-1 text-sm' : size === 'sm' },
                    { 'px-4 py-2 text-sm' : size === 'md' },
                    { 'px-6 py-2.5 text-sm' : size === 'lg' },
                    'rounded-full font-medium transition-all cursor-pointer',
                    size === 'md'
                        ? 'px-4 py-2 text-sm'
                        : 'px-4 py-1 text-sm',
                    tab.value === modelValue
                        ? 'bg-indigo-600 text-white shadow-lg'
                        : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'
                ]">

                <!-- Left Icon -->
                <component v-if="tab.leftIcon" :is="tab.leftIcon" :size="tab.leftIconSize ?? 16" />

                <!-- Label -->
                <span class="whitespace-nowrap">{{ tab.label }}</span>

                <!-- Right Icon -->
                <component v-if="tab.rightIcon" :is="tab.rightIcon" :size="tab.rightIconSize ?? 16" />

            </button>

        </template>

    </div>

</template>

<script>
    export default {
        props: {
            modelValue: {
                type: String,
                required: true
            },
            tabs: {
                type: Array,
                required: true,
                default: () => [],
            },
            size: {
                type: String,
                default: 'md',
                validator: value => ['sm', 'md', 'lg'].includes(value)
            },
            design: {
                type: String,
                default: '2',
                validator: value => ['1', '2'].includes(value)
            },
        },
        methods: {
            selectTab(value) {
                this.$emit('update:modelValue', value)
                this.$emit('change', value)
            },
        },
    }
</script>
