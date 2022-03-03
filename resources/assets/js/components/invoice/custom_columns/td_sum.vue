<template lang="pug">
    div {{ $root.formatFinanceValue(row.gross_total) + ' ' + $root.current_currency.symbol }}
</template>

<script>
export default {
    props: ['row'],
}
</script>