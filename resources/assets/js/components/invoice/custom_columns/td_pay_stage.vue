<template lang="pug">
    div(v-if="row.pay_stage") {{ row.pay_stage.text }}
</template>

<script>
export default {
    props: ['row'],
}
</script>