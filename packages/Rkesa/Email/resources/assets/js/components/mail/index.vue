<template lang="pug">
    div
        div(v-if="loading", style="text-align: center;padding-top: 15%;")
            div.loader
        div(v-else)
            iframe.diga-container(v-if="error === null" src="/webmail/index.php" style="width: 100%; min-height: 832px; border: 0;")
            div.text-center(v-else)
                div.mb-2 {{ error }}
                button.btn.btn-diga(v-on:click="login") {{ $t('email.Reload') }}
</template>

<script>
export default {
    props: [],
    data() {
        return {
            error: null,
            loading: false,
        }
    },
    created(){
        this.login();
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('email.Email');
    },
    methods: {
        login(){
            if (this.$root.user.email == 'admin@example.com'){
                this.error = this.$root.$t('email.You_need_to_setup_email');
            } else {
                this.loading = true;
                this.$http.post('/api/mail/login').then(res => {
                    if (res.data.errcode == 1) {
                        this.error = res.data.errmess;
                    } else {
                        this.error = null;
                    }
                    this.loading = false;
                }, res => {
                    this.error = this.$root.$t("template.Server_error");
                    this.loading = false;
                });
            }
        },
    },
}
</script>