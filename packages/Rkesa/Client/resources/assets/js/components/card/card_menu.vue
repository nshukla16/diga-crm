<template lang="pug">
    select.block-toggler(:value="current_section", @input="$bus.$emit('update_current_section', $event.target.value)")
        option(:value="1") {{ $t("client.Services") }}
        option(:value="2") {{ $t("template.Projects") }}
        option(:value="3") {{ $t("template.All_equipments") }}
        option(:value="4") {{ $t("client.Delivery_calculations") }}
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