<template lang="pug">
span 
  template(v-if="row.estimate_unit") {{ row.estimate_unit.measure }}
</template>

<script>
export default {
  props: ["row"],
};
</script>