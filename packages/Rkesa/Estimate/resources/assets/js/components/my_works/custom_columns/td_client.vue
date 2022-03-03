<template lang="pug">
    div(v-if='row.estimate.service && row.estimate.service.client_contact') {{ $root.fullName(row.estimate.service.client_contact) }}
</template>

<script>
export default {
    props: ['row'],
}
</script>