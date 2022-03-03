<style>

</style>

<template lang="pug">
    div.modal.fade.modal-medium.modal-estimate-select(tabindex="-1" aria-hidden="true")
        div.modal-dialog.modal-dialog-centered(role="document")
            div.modal-content
                div.modal-body(v-if="service")
                    table.estimates.table.table-striped.table-hover(v-if="'estimates' in service && service.estimates.length > 0")
                        thead
                            tr
                                td.text-center(style="width: 10%;font-size: 20px;") {{ $t('client.Master') }}
                                td.text-center(style="width: 30%;font-size: 20px;") {{ $t('client.Number') }}
                                td.text-center(style="width: 10%;font-size: 20px;") {{ $t('client.Send') }}
                                td.text-center(style="width: 50%;font-size: 20px;") {{ $t('client.Estimates') }}
                        tbody
                            tr(v-for="estimate in service.estimates")
                                td.text-center
                                    input(type="radio" v-model="service.master_estimate_id" v-bind:value="estimate.id" disabled)
                                td.text-center {{ $root.estimate_number(estimate) }}
                                td.text-center
                                    input(type="checkbox" v-model="estimate.selected")
                                td(style="text-align:center;")
                                    router-link.btn.default(:to="{name: 'estimate_show', params: {id: estimate.id}}" target="_blank" style="margin-right: 10px;") PDF
                    div(style="text-align:center;")
                        button.btn.default.green(v-on:click="estimate_select_ok" style="margin-right: 10px;") {{ $t("client.Enviar") }}
</template>

<script>

export default {
    props: ['service'],
    data() {
        return {

        }
    },
    methods: {
        estimate_select_ok: function(){
            let estimates = this.service.estimates.filter(function(i){ return i.selected }).map(function(i){ return i.id; });
            this.$bus.$emit('resolve_send_estimates', estimates, this.service.id);
        },
    },
}
</script>