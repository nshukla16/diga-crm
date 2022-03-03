<style>
.js-updated-wrapper {
  text-align: center;
}
</style>

<template lang="pug">
.wrapper
  .js-updated-wrapper(v-if="$root.js_updated_showing")
    | {{ $t('template.JSUpdated_first') }}
    button.button-like-link.mx-1(v-on:click="reload_page") {{ $t('template.JSUpdated_link') }}
    | {{ $t('template.JSUpdated_last') }}

  sidebar_component
  //- rightpanel-component
  #content
    div(v-if="is_expired", style="background-color: #ff7b7b; color: white")
      .container-fluid
        .row.text-center
          p(style="margin: auto") {{ $t('template.some_modules_expire') }} {{ expiracy_text }}
    topbar-component

    .page-container
      .page-content-wrapper
        div(:class="$root.page_content_class")
          router-view
      .clearfix
  common-phone-modal
  portal-target(name="uploader-destination", multiple)
</template>

<script>
import topbar_component from "./topbar.vue";
import navbar_component from "./navbar.vue";
import sidebar_component from "./sidebar.vue";
// import rightpanel_component from './rightpanel.vue'
import common_phone_modal from "@/components/phone_modal";
import moment from "moment";

export default {
  data() {
    return {
      expiracy_text: "",
      modules: [],
    };
  },
  components: {
    "topbar-component": topbar_component,
    "navbar-component": navbar_component,
    // 'rightpanel-component': rightpanel_component,
    "common-phone-modal": common_phone_modal,
    sidebar_component,
  },
  methods: {
    reload_page() {
      window.location.reload(true);
    },
    getModules() {
      this.$http.get("/api/modules").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.modules = res.data;
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
  },
  created() {
    this.$root.LoadNotificationData();
    this.getModules();
  },
  computed: {
    is_expired() {
      if (this.modules.length > 0) {
        var modules_str = "";
        var is_trial = false;

        this.modules.forEach((m) => {
          if (m.current_subscription_date_end) {
            var diff = moment(m.current_subscription_date_end).diff(
              moment(),
              "days"
            );
            if (diff > 14) {
              return false;
            }
            if (diff < 0) {
              modules_str +=
                this.$root.$t("template.module-" + m.name) +
                ": " +
                this.$root.$t("template.expired") +
                "; ";
            } else {
              modules_str +=
                this.$root.$t("template.module-" + m.name) +
                ": " +
                diff +
                " " +
                this.$root.$t("estimate.dias") +
                "; ";
            }
          } else {
            if (m.trial_date_end) {
              is_trial = true;
              var diff = moment(m.trial_date_end).diff(moment(), "days");
              if (diff > 14) {
                return false;
              }
              if (diff < 0) {
                modules_str +=
                  this.$root.$t("template.module-" + m.name) +
                  ": " +
                  this.$root.$t("template.expired") +
                  "; ";
              } else {
                modules_str +=
                  this.$root.$t("template.module-" + m.name) +
                  ": " +
                  diff +
                  " " +
                  this.$root.$t("estimate.dias") +
                  "; ";
              }
            }
          }
        });

        if (modules_str.length > 0) {
          this.expiracy_text = modules_str;
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    },
  },
};
</script>