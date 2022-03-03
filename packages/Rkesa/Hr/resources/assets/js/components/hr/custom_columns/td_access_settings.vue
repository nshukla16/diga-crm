<template lang="pug">
    span 
        router-link(v-if="$root.user.is_admin", :to="{ name: 'user_permissions', params: { id: row.id } }") {{$t("hr.Permission_settings")}}
</template>

<script>
export default {
    props: ['row'],
}
</script>