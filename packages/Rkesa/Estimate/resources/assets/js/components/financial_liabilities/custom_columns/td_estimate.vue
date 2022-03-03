<template lang="pug">
    ul
        li(v-for="eg in row.estimate_groups") 
            a(:href="'/contacts/' + eg.estimate.service.client_contact_id" target="_blank") {{eg.estimate.service.estimate_number}}
</template>

<script>
export default {
    props: ['row'],
}
</script>