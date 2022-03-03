<template lang="pug">
    span {{ row.price }} {{ $root.current_currency.symbol }}
</template>

<script>
export default {
    props: ['row'],
}
</script>