<template lang="pug">
    button.btn.btn-danger(v-if="$root.can_do('legal_entities', 'delete') !== 0" v-on:click="delete_legal_entity(row.id)") {{ $t('template.Remove') }}
</template>

<script>
export default {
    props: ['row'],
    methods: {
        delete_legal_entity(id){
            if (confirm(this.$root.$t('project.Are_you_sure_want_to_remove_legal_entity'))) {
                this.$root.global_loading = true;
                this.$http.delete('/api/legal_entities/' + id).then(data => {
                    if (data.errcode == 1) {
                        this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("project.Legal_entity_removed"), this.$root.$t("template.Success"));
                        this.$bus.$emit('get_results');
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                });
            }
        },
    },
}
</script>