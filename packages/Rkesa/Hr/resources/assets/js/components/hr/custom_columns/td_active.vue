<template lang="pug">
    span
        template(v-if="row.active") {{ $t("hr.Active") }}
        template(v-else) {{ $t("hr.Inactive") }}
</template>

<script>
export default {
    props: ['row'],
}
</script>