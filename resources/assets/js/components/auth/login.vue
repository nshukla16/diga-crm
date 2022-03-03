<template lang="pug">
.container
  .row
    .col-12.col-sm-10.offset-sm-1.col-md-8.offset-md-2.col-lg-6.offset-lg-3
      .panel.panel-default
        .panel-header(
          style="text-align: center; margin-top: 40px; margin-bottom: 40px"
        )
          img(:src="$root.logo", style="max-height: 150px; max-width: 100%")
        .panel-body
          h3.text-center {{ $t('template.you_will_be_redirected') }}
          button.btn.btn-diga.btn-block.mb-2(@click="goToAuth0") {{ $t('template.Login') }}
          //- form(method="POST", v-on:submit.prevent="login")
          //-   p.text-danger(
          //-     v-if="authErrors.has('invalid_credentials')",
          //-     v-text="$t('auth.failed')"
          //-   )

          //-   input#email.form-control.mb-2(
          //-     type="email",
          //-     placeholder="Email",
          //-     required="",
          //-     autofocus="",
          //-     v-model="email"
          //-   )
          //-   .d-flex
          //-     input#password.form-control(
          //-       :type="passwordFieldType",
          //-       :placeholder="$t('template.Password')",
          //-       required="",
          //-       v-model="password"
          //-     )
          //-     span.clickable.ml-2(
          //-       style="line-height: 38px",
          //-       @click="switchVisibility",
          //-       :title="$t('template.Show_hide_password')"
          //-     )
          //-       i(
          //-         :class="['fa', passwordFieldType === 'password' ? 'fa-eye' : 'fa-eye-slash']"
          //-       )
          //-   .checkbox.mt-2
          //-     label
          //-       input.mr-2(
          //-         type="checkbox",
          //-         value="remember",
          //-         v-model="remember"
          //-       )
          //-       | {{ $t('template.Remember_me') }}
          //-   button.btn.btn-diga.btn-block.mb-2(type="submit") {{ $t('template.Login') }}
          //-   router-link(:to="{ name: 'password-email' }") {{ $t('template.Forgot_password') }}
</template>

<script>
export default {
  mounted() {},
  data() {
    return {
      // email: "",
      // password: "",
      // remember: false,
      // passwordFieldType: "password",
    };
  },
  computed: {
    authErrors() {
      return this.$store.getters.authErrors;
    },
  },
  methods: {
    goToAuth0() {
      const url = new URL("https://diga.eu.auth0.com/authorize");

      url.searchParams.append("response_type", "code");
      url.searchParams.append("client_id", "n2hhgGIZIjwoMxI8BOZeaU2bA2DJR4jM");
      url.searchParams.append(
        "redirect_uri",
        window.location.origin + "/auth0"
      );
      url.searchParams.append("audience", "https://diga.pt");
      url.searchParams.append("scope", "openid profile email");

      location.href = url.href;
    },
    // switchVisibility() {
    //   this.passwordFieldType =
    //     this.passwordFieldType === "password" ? "text" : "password";
    // },
    // login: function () {
    //   const { email, password, remember } = this;
    //   this.$store
    //     .dispatch("authRequest", { email, password, remember })
    //     .then((bearer) => {
    //       initialize_bearer(bearer);
    //       if (!Push.Permission.has()) Push.Permission.request();
    //       if (localStorage.getItem("returnUrl") === null) {
    //         this.$router.push("/dashboard");
    //       } else {
    //         this.$router.push(localStorage.getItem("returnUrl"));
    //       }
    //     });
    // },
  },
};
</script>