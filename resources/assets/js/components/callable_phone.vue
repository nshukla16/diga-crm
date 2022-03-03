<style>
    .common-phone-code {
        cursor: pointer;
        color: #24C5C3;
        text-decoration: underline;
    }

    .common-phone-code-separator {
        margin-left: 1px;
        margin-right: 1px;
    }

</style>

<template lang="pug">
    span
        span.common-phone-code(v-if="enabled && $root.user.zadarma_internal_phonecode" v-text="number", v-on:click="phone_table")
        span(v-else, v-text="number")
        span.common-phone-code-separator(v-if="!isLast", v-text="','")
</template>

<script>
import { mapGetters } from 'vuex';
export default {
    props: {
        number: {
            default: '',
            type: String,
        },
        isLast: {
            default: false,
            type: Boolean,
        },
    },
    methods: {
        phone_table() {
            this.$store.dispatch('call/initWindow', this.number);
        },
    },
    computed: {
        ...mapGetters({
            enabled: 'getZadarmaEnabled',
        }),
    },
}
</script>