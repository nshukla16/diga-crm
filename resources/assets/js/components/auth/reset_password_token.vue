<template lang="pug">
    div.container
        div.row
            div.col-12.col-sm-10.offset-sm-1.col-md-8.offset-md-2.col-lg-6.offset-lg-3
                div.panel.panel-default
                    div.panel-header(style="text-align: center;margin-top:40px;margin-bottom:40px;")
                        router-link(:to="{ name: 'login' }")
                            img(v-if="$root.logo", :src="$root.logo", style="max-height: 150px;max-width: 100%;")
                    div(v-if="error")
                        div.alert.alert-danger(role="alert") {{ $t('template.Reset_error') }}
                    div.panel-body
                        form(method='POST', v-on:submit.prevent='resetPassword')
                            label.sr-only(for='email') Email
                            input#email.form-control(type='email', placeholder='Email', required='', autofocus='', v-model='email')
                            input(type='hidden', name='token', required='', v-model='token')
                            label.sr-only(for='password') {{ $t('template.Password') }}
                            input#password.form-control(type='password', :placeholder="$t('template.Password')", required='', v-model='password')
                            label.sr-only(for='password_confirmation') {{ $t('template.Password_confirm') }}
                            input#password_confirmation.form-control(type='password', :placeholder="$t('template.Password_confirm')", required='', v-model='password_confirmation')
                            .alert.alert-danger.mt-3(role='alert', v-if='authErrors.any()')
                                div(v-text="authErrors.get('email')")
                                div(v-text="authErrors.get('password')")
                            button.btn.btn-diga.btn-block.mt-2.mb-2(type='submit') {{ $t('template.Reset_password') }}
</template>

<script>
export default {
    data(){
        return {
            email: '',
            password: '',
            password_confirmation: '',
            token: '',
            error: false,
        }
    },
    computed: {
        authErrors(){
            return this.$store.getters.authErrors;
        },
    },
    methods: {
        resetPassword: function () {
            this.reset = false;
            const { email, password, password_confirmation, token } = this;
            this.$store.dispatch('resetRequest', { email, password, password_confirmation, token })
                .then(() => {
                    this.$router.push({ name: 'login' });
                }, () => {
                    this.error = true;
                })
        },
    },
    mounted(){
        let token = this.$route.params.token;
        if (!token){
            this.$router.push('/');
        }
        this.token = token;
    },
}
</script>