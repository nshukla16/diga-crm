<template lang="pug">
    div
        router-link.btn.btn-secondary(:to="{ name: 'checklist_edit', params: {id: row.id}}") {{ $t("template.Edit") }}
        button.btn.btn-danger.ml-2(v-on:click="remove_checklist(row.id)") {{ $t("template.Remove") }}
</template>

<script>
export default {
    props: ['row'],
    methods: {
        remove_checklist(id){
            if (confirm(this.$root.$t('calendar.Are_you_sure_you_want_to_remove_checklist'))) {
                this.$root.global_loading = true;
                this.$http.delete('/api/checklists/' + id).then(data => {
                    if (data.errcode == 1) {
                        this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("calendar.Checklist_removed"), this.$root.$t("template.Success"));
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