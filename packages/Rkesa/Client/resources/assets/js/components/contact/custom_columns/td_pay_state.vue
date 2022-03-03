<template lang="pug">
    span(v-html="get_services_pay_info(row)")
</template>

<script>
export default {
    props: ['row'],
    methods: {
        get_services_pay_info(client){
            let $this = this;
            return client.services.map(function (service) {
                return $this.get_service_pay_info(service);
            }).join(', ');
        },
        get_service_pay_info(service){
            if (service.paid_summ != null && service.paid_summ == service.estimate_summ){
                return '<span style="background-color: #60c657;">' + this.$root.$t('client.Paid_full') + '</span>';
            } else if (service.paid_summ != null && service.paid_summ != 0 && service.paid_summ != service.estimate_summ){
                return '<span style="background-color: #eeb240;">' + this.$root.$t('client.Paid_not_full') + '</span>';
            } else {
                return '<span style="background-color: #ee1334;">' + this.$root.$t('client.Not_paid') + '</span>';
            }
        },
    },
}
</script>