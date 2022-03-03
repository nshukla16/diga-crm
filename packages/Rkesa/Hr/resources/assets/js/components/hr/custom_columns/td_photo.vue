<template lang="pug">
    img(:src='row.photo', style='width: 60px;')
</template>

<script>
export default {
    props: ['row'],
}
</script>