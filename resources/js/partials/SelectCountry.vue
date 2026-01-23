<template>

  <Select
        v-bind="$props"
        :clearable="clearable"
        :options="filteredCountryOptions"
        noResultsText="No countries found"
        :searchableFields="['label', 'value']">

        <!-- Custom Styled Selected Option -->
        <template #selectedOption="{ selectedOption }">
            <div v-if="selectedOption" class="flex items-center space-x-2">
                <img :src="`/svgs/country-flags/${selectedOption.value.toLowerCase()}.svg`" class="w-4 h-4" />
                <span class="text-gray-700 text-sm truncate">{{ selectedOption.label }}</span>
            </div>
            <span v-else class="text-gray-700 text-sm">{{ placeholder }}</span>
        </template>

        <!-- Custom Styled Dropdown Option -->
        <template #option="{ option }">
            <div class="flex items-center space-x-2">
                <img :src="`/svgs/country-flags/${option.value.toLowerCase()}.svg`" class="w-4 h-4" />
                <span class="text-gray-700 text-sm">{{ option.label }}</span>
            </div>
        </template>

  </Select>

</template>

<script>
    import Select from '@Partials/Select.vue';
    import countries from '@Json/countries.json';

    export default {
        components: { Select },

        props: {
            clearable: {
                type: Boolean,
                default: false,
            },
            placeholder: {
                type: [String, null],
                default: 'Select a country',
            },
            allowedCountries: {
                type: [Array, null], // e.g. ['BW', 'ZA', 'KE']
                default: null,
            },
        },

        data() {
            return {
                countryOptions: [],
            };
        },

        computed: {
            filteredCountryOptions() {
                let options = this.countryOptions;

                if (this.allowedCountries && Array.isArray(this.allowedCountries) && this.allowedCountries.length > 0) {
                    const allowed = new Set(this.allowedCountries.map(code => code.toUpperCase()));
                    options = options.filter(opt => allowed.has(opt.value));
                }

                return options;
            },
        },

        created() {
            this.countryOptions = countries.map((country) => ({
                label: country.name,
                value: country.iso.toUpperCase(), // ensure uppercase consistency
            }));
        },
    };
</script>
