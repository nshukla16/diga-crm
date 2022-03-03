<template lang="pug">
    div
        div(v-for="kv in Object.entries(row.new_values)") {{ render_entity(kv) }}
</template>

<script>
import {customValue} from "@/audit-translations.js";

export default {
    props: ['row'],
    computed: {
    },
    methods: {
        render_entity(kv){
            let key = kv[0];
            let val = kv[1];
            return customValue(this, key, val, this.row.auditable_type);
        },
    },
}
</script>