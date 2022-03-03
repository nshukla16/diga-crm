<template lang="pug">
    span
        router-link(v-if="$root.can_do('users', 'update') == 1", :to="{ name: 'user_edit', params: { id: row.id } }") {{ row.name }}
        span(v-else) {{ row.name }}
</template>

<script>
export default {
    props: ['row'],
}
</script>