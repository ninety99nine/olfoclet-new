<template>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 pt-6">
            <h3 class="text-lg font-semibold text-slate-900">{{ title }}</h3>
        </div>
        <div class="p-4">
            <slot />
        </div>
    </div>
</template>

<script>
    export default {
    name: 'ChartCard',
    props: {
        title: {
                type: String,
                required: true
            }
        }
    }
</script>
