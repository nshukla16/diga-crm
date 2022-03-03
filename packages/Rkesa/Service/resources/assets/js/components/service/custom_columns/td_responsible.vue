<template lang="pug">
    span(v-if="row.responsible_user") {{ row.responsible_user.name }}
</template>

<script>
export default {
    props: ['row'],
}
</script>