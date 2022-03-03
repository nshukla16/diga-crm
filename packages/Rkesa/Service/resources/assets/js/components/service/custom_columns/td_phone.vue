<template lang="pug">
    span(v-if="row.client_contact && row.client_contact.client_contact_phones.length > 0")
        common_phone_number(v-for="(phone, index) in row.client_contact.client_contact_phones", :key="phone.id", :number="phone.phone_number", :isLast="index === (row.client_contact.client_contact_phones.length-1)")
</template>
<script>
import common_phone_number from '@/components/callable_phone'
export default {
    props: ['row'],
    components: {
        common_phone_number,
    },
}
</script>