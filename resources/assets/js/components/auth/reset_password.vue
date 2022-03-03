<template lang="pug">
    div.container
        div.row
            div.col-12.col-sm-10.offset-sm-1.col-md-8.offset-md-2.col-lg-6.offset-lg-3
                div.panel.panel-default
                    div.panel-header(style="text-align: center;margin-top:40px;margin-bottom:40px;")
                        router-link(:to="{ name: 'login' }")
                            img(v-if="$root.logo", :src="$root.logo", style="max-height: 150px;max-width: 100%;")
                    div.panel-body
                        form(method='POST', v-on:submit.prevent='sendPasswordResetEmail', v-if='seconds>4')
                            label.sr-only(for='email') Email
                            input#email.form-control(type='email', placeholder='Email', required='', autofocus='', v-model='email')
                            button.btn.btn-diga.btn-block.mt-2.mb-2(type='submit', :disabled='disableSubmit') {{ $t('template.Send_password_reset_link') }}
                            | {{ $t('auth.back_to_form') }}
                            router-link(:to="{ name: 'login' }") {{ $t('auth.of_auth') }}
                        .container(v-else='')
                            h2.text-success.mt-5 {{ $t('auth.password_recovery_email_sent') }}
                            .progress.mt-3.mb-3.ml-5.mr-5
                                .progress-bar.bg-success(role='progressbar', :style='progress', aria-valuenow='25', aria-valuemin='0', aria-valuemax='100')
                            h3 {{ $t('auth.redirect_after', {seconds: seconds}) }}
</template>

<script>
export default {
    data(){
        return {
            'disableSubmit': false,
            'email': '',
            'seconds': 5,
        }
    },
    computed: {
        progress: function(){
            return 'width: ' + (20 * Math.abs(5 - this.seconds)) + '%';
        },
    },
    methods: {
        sendPasswordResetEmail: function () {
            let $this = this;
            $this.disableSubmit = true;
            axios.post('/password/email', {'email': $this.email})
                .then((resp) => {
                    $this.countdownRedirect();
                })
                .catch(() => {
                    $this.disableSubmit = false;
                    $this.seconds = 5;
                })
        },
        countdownRedirect: function () {
            let $this = this;
            setInterval(() => {
                $this.seconds -= 1;
                if ($this.seconds === 0) {
                    $this.$router.push('/');
                }
            }, 1000);
        },
    },
}
</script>