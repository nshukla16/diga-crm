<template lang="pug">
span 
  template(v-if="row.vat_type") {{ row.vat_type.name }} {{ row.vat_type.percent }} %
</template>

<script>
export default {
  props: ["row"],
};
</script>