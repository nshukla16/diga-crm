<template lang="pug">
    select.block-toggler(:value="current_section", @input="$bus.$emit('update_current_section', $event.target.value)")
        option(:value="1") {{ $t("project.Equipment") }}
        option(:value="2") {{ $t("project.Projects") }}
</template>

<script>
    export default {
        props: ['current_section'],
        data: function() {
            return {}
        },
    }
</script>

<style>

</style>