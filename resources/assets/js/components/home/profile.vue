<style>
</style>

<template lang="pug">
div(v-if="profile")
  .row.mb-3
    .col-12.col-md-6
      .diga-container.p-4
        h2 {{ $t('template.My_profile') }}
        fieldset.form-group
          label.control-label {{ $t('template.Interface_language') }}
          select.form-control(v-model="profile.site_language")
            option(value="pt") Português
            option(value="en") English
            option(value="ru") Русский
            option(value="es") Espanol
        fieldset.form-group
          button.btn.btn-diga(v-on:click="check_browser_notification()") {{ $t('template.check_browser_notificaton') }}
        h2 {{ $t('template.Change_password') }}
        fieldset.form-group
          button.btn.btn-diga(v-on:click="send_password_reset_email") {{ $t('template.send_email_to_reset_password') }}
        //- fieldset.form-group(:class="{ 'has-error': errors.has('old_password') }")
        //-     label.control-label {{ $t("template.Old_password") }}
        //-     input.form-control(type="password" name="old_password" v-model="old_password" v-validate="this.new_password != '' ? 'required' : ''" v-bind:data-vv-as="$t('template.Old_password').toLowerCase()")
        //-     span.help-block(v-show="errors.has('old_password')") {{ errors.first('old_password') }}
        //- fieldset.form-group(:class="{ 'has-error': errors.has('new_password') }")
        //-     label.control-label {{ $t("template.New_password") }}
        //-     input.form-control(type="password" name="new_password" v-validate="'min:8'" v-model="new_password" v-bind:data-vv-as="$t('template.New_password').toLowerCase()")
        //-     span.help-block(v-show="errors.has('new_password')") {{ errors.first('new_password') }}
        //- fieldset.form-group(:class="{ 'has-error': errors.has('confirmation') }")
        //-     label.control-label {{ $t("template.Confirmation") }}
        //-     input.form-control(type="password" name="confirmation" v-model="confirmation" v-validate="this.new_password != '' ? 'required|confirmed:new_password' : ''" v-bind:data-vv-as="$t('template.Confirmation').toLowerCase()")
        //-     span.help-block(v-show="errors.has('confirmation')") {{ errors.first('confirmation') }}
        //- h2 {{ $t('estimate.Other_info') }}
        //- fieldset.form-group
        //-   label.control-label {{ $t('client.Phone_number') }}
          //- vue-tel-input(
          //-   v-model="profile.cell_phone",
          //-   @input="phone_input_change"
          //- )
          //- vue-phone-number-input(
          //-     v-model="profile.cell_phone" 
          //-     default-country-code="PT"
          //-     :preferred-countries="['PT', 'ES', 'RU']"
          //-     :only-countries="['PT', 'RU', 'ES', 'BR', 'US']"
          //-     @update="phone_input_change"
          //-     :countries-height="40"
          //-     )
        //- div.form-group(v-if="$root.global_settings.telegram_enabled === true")
        //-     label.control-label {{ $t("template.Telegram_username") }}
        //-     div.input-group
        //-         div.input-group-prepend
        //-             span.input-group-text(id="basic-addon1") @
        //-         input.form-control(type="text" name="tg_username" v-model="profile.tg_username" aria-describedby="basic-addon1")
    .col-12.col-md-6
      .diga-container.p-4
        h2 {{ $t('template.Settings') }}
        fieldset.form-group
          label.control-label {{ $t('template.Email') }}
          input.form-control(type="text", v-model="profile.email", disabled)
        fieldset.form-group
          label.control-label {{ $t('template.Email_password') }}
          input.form-control(type="text", v-model="profile.email_password")
        .row
          .col-6.col-sm-6
            label.control-label {{ $t('template.enable_calendar_on_main_page') }}
          .col-6.col-sm-6
            .float-right(style="width: 120px")
              bootstrap-toggle(
                data-size="mini",
                v-model="profile.show_calendar_on_main_page",
                :options="{ on: $t('template.Yes'), off: $t('template.No') }",
                data-width="120",
                data-height="38",
                data-onstyle="default"
              )
        .row.mt-2(v-if="$root.module_enabled('estimate')")
          .col-6.col-sm-6
            label.control-label {{ $t('template.autosave_estimates') }}
          .col-6.col-sm-6
            .float-right(style="width: 120px")
              bootstrap-toggle(
                data-size="mini",
                v-model="profile.autosave_estimates",
                :options="{ on: $t('template.Yes'), off: $t('template.No') }",
                data-width="120",
                data-height="38",
                data-onstyle="default"
              )
  .row.mb-3
    //- div.col-12.col-md-6
    //-     div.diga-container.p-4
    //-         h2 {{ $t("template.Help") }}
    //-         button.btn.btn-diga(v-on:click="start_tour") {{ $t("template.Start_tour") }}
    .col-12.col-md-6
      .diga-container.p-4
        .clearfix
          h2.float-left {{ $t('template.Google_calendar_integration') }}
          .float-right
            div(v-on:click="google_calendar_toggle", style="width: 120px")
              bootstrap-toggle(
                data-size="mini",
                v-model="profile.gc_enabled",
                :options="{ on: $t('template.On'), off: $t('template.Off') }",
                data-width="120",
                data-height="38",
                data-onstyle="default"
              )
  .row
    .col-12
      button.btn.btn-diga(v-on:click="save_settings()") {{ $t('template.Save') }}
</template>

<script>
export default {
  data() {
    return {
      profile: null,
      old_password: "",
      new_password: "",
      confirmation: "",
    };
  },
  created() {
    this.profile = this.$store.getters.getUser;
  },
  mounted() {
    document.title =
      this.$root.global_settings.site_name +
      " | " +
      this.$root.$t("template.My_profile");
  },
  methods: {
    send_password_reset_email() {
      this.$root.global_loading = true;
      this.$http
        .post("/api/users/change_password", {
          user_id: this.$root.user.id,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("template.email_was_sent"),
                this.$root.$t("template.Success")
              );
            }
            this.$root.global_loading = false;
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
            this.$root.global_loading = false;
          }
        );
    },
    check_browser_notification() {
      if (!Push.Permission.has()) Push.Permission.request();
    },
    google_calendar_toggle() {
      if (!this.profile.gc_enabled) {
        this.$root.global_loading = true;
        let win = window.open("", "_blank"); // create before ajax to prevent window blocking
        this.$http.get("/api/settings/integrations/google_calendar").then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              win.location.href = res.data.url;
              win.focus();
              let timer = setInterval(function () {
                if (win.closed) {
                  clearInterval(timer);
                  window.location.reload(true);
                }
              }, 500);
            }
            this.$root.global_loading = false;
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
            this.$root.global_loading = false;
          }
        );
      }
    },
    start_tour() {
      this.$http.post("/api/start_tour").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            window.localStorage.clear();
            this.$root.start_user_tour();
          }
        },
        (res) => {
          this.$toastr.e(
            this.$root.$t("template.Server_error"),
            this.$root.$t("template.Error")
          );
        }
      );
    },
    save_settings() {
      this.$validator.validateAll().then((result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        }

        this.$root.global_loading = true;
        let payload = Object.assign({}, this.$data);
        this.$http.patch("/api/me", payload).then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("template.Profile_saved"),
                this.$root.$t("template.Success")
              );
            }
            this.$root.global_loading = false;
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
            this.$root.global_loading = false;
          }
        );
      });
    },
    phone_input_change(number, value) {
      this.profile.formatted_cell_phone = value.number.e164;
    },
  },
};
</script>