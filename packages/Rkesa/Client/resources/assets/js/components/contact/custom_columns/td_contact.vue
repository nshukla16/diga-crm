<template lang="pug">
    router-link(:id="row.id == 1 ? 'test_client' : ''", :to="{ name: $root.contact_or_client_show_route(), params: {id: row.id }}") {{ $root.fullName(row) }}
</template>

<script>
export default {
    props: ['row'],
}
</script>