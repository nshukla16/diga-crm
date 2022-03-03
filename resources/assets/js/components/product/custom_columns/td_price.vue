<template lang="pug">
span {{ parseFloat(row.price).toFixed(2) }} {{ $root.current_currency.symbol }}
</template>

<script>
export default {
  props: ["row"],
};
</script>