<template lang="pug">
    div
        router-link(:to="{name: 'estimate_show', params: {id: row.id}}" target="_blank")
            | {{ $t("estimate.Estimate") }}
        router-link.ml-2(v-if="$root.can_do('plannings', 'read') != 0", :to="{name: 'planning_show', params: {id: row.id}}" target="_blank")
            | {{ $t("estimate.Planning") }}
</template>

<script>
export default {
    props: ['row'],
}
</script>