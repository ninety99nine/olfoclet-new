<template>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden min-h-[140px]">

        <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/60 z-10">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-indigo-50/70 to-transparent rounded-bl-full -mr-8 -mt-8"></div>

        <p class="text-sm text-slate-500 font-medium mb-1">{{ title }}</p>

        <p class="text-3xl font-bold text-slate-900">{{ value }}</p>

        <div class="mt-4 flex items-center gap-2">

            <span :class="trendPositive ? 'text-emerald-600' : 'text-rose-600'" class="text-sm font-medium">
                {{ trend }}
            </span>

            <span class="text-xs text-slate-500">vs previous period</span>

        </div>

    </div>

</template>

<script>
export default {
    name: 'KpiCard',
    props: {
        title: {
            type: String,
            required: true
        },
        value: {
            type: [String, Number],
            required: true
        },
        trend: {
            type: String,
            default: '—'
        },
        trendPositive: {
            type: Boolean,
            default: true
        },
        loading: {
            type: Boolean,
            default: false
        }
    }
}
</script>
