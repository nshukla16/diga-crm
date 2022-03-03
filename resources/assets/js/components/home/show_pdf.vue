<style>
    .file-notification{
        text-align: center;
        font-size: 30px;
    }
</style>

<template lang="pug">
    div.file-notification
        div(v-if="granted") {{ $t('template.The_requested_file_is_opening') }}
        div.red(v-else) {{ $t('template.Access_denied') }}
</template>

<script>
export default {
    props: ['src'],
    data: function(){
        return {
            granted: true,
        }
    },
    created(){
        this.$root.global_loading = true;

        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.The_requested_file_is_opening');

        this.$http.get(this.src).then(res => {
            window.location = res.data.link;
            this.$root.global_loading = false;
        }, res => {
            this.granted = false;
            this.$root.global_loading = false;
        });
    },
}
</script>