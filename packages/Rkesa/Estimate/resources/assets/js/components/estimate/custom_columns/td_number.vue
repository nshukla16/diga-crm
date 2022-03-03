<template lang="pug">
    div
        router-link(v-if="!row.blocked && $root.can_with_estimate('update', row)", :to="{name: 'estimate_edit', params: {id: row.id}}") {{ $root.estimate_number(row) }}
        span(v-else) {{ $root.estimate_number(row) }}
        i.fa.fa-lock.ml-2(v-if="row.blocked")
</template>

<script>
export default {
    props: ['row'],
}
</script>