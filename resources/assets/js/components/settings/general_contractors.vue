<style>
</style>

<template lang="pug">
div
  h2 {{ $t('template.general_contractors') }}
  p {{ $t('template.general_contractors_desc') }}
  .row
    .col-12.col-md-6.mb-3(v-if="connections")
      .diga-container.p-4
        table.referrers-table
          tr
            th #
            th Url
            th {{ $t('calendar.Responsible_user') }}
            th 
              | {{ $t('client.Referrer') }}
              router-link.btn.btn-light.mx-2(:to="{ name: 'client_settings' }") +
          tr(
            v-for="(connection, i) in connections",
            style="margin-bottom: 5px"
          )
            td
              | {{ i + 1 }}.
            td
              input.form-control(disabled="true", v-model="connection.url")
            td
              select.form-control(v-model="connection.responsible_id")
                option(
                  v-for="user in users.filter((u) => u.active === true)",
                  :value="user.id"
                ) {{ user.name }}
            td
              select.form-control(v-model="connection.client_referrer_id")
                option(
                  v-for="option in referrers",
                  v-bind:value="option.id",
                  v-text="option.title"
                )
            td
              button.btn.btn-success(
                v-if="connection.is_approved === false",
                v-on:click="confirm(connection)"
              ) {{ $t('template.Check') }}
              button.btn.btn-success(v-else, :disabled="true") {{ $t('project.Confirmed') }}

    .col-12.col-md-6.mb-3(v-if="connections")
      .diga-container.p-4
        fieldset.form-group
          label.control-label.col-xs-4 
            | {{ $t('template.status_of_contractor_service') }}
            router-link.btn.btn-light.mx-2(:to="{ name: 'service_settings' }") +
          .col-xs-8
            select.form-control(v-model="new_service_state_id")
              option(
                v-for="state in states",
                v-bind:value="state.id",
                v-if="state.type == 0"
              ) {{ state.name }}
  .row
    .col-12
      button.btn.btn-diga(
        v-on:click="save_settings()",
        style="margin-right: 20px"
      ) {{ $t('template.Save') }}
</template>

<script>
import { mapGetters } from "vuex";

export default {
  data() {
    return {
      connections: [],
      new_service_state_id: this.$root.global_settings
        .contractor_service_state_id,
    };
  },
  created() {
    this.get_connections();
  },
  mounted() {
    document.title =
      this.$root.global_settings.site_name +
      " | " +
      this.$root.$t("client.vat");
  },
  computed: {
    ...mapGetters({
      users: "getUsers",
      users_by_id: "getUsersById",
      referrers: "getClientReferrers",
      referrersById: "getClientReferrersById",
      states: "getNotRemovedServiceStates",
    }),
  },
  methods: {
    save_settings() {
      this.$root.global_loading = true;
      this.$http
        .patch("/api/connection", {
          connections: this.connections,
          new_service_state_id: this.new_service_state_id,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(
                this.$root.$t("template.connection_error"),
                this.$root.$t("template.Error")
              );
              this.$root.global_loading = false;
            } else {
              this.$toastr.s(
                this.$root.$t("template.Success"),
                this.$root.$t("template.Success")
              );
              this.get_connections();
              this.$root.global_settings.contractor_service_state_id = this.new_service_state_id;
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
    get_connections() {
      this.$root.global_loading = true;
      this.$http.get("/api/connection").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.connections = res.data.rows;
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
    confirm(connection) {
      this.$root.global_loading = true;
      this.$http
        .post("/api/connection/confirm", {
          id: connection.id,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(
                this.$root.$t("template.connection_error"),
                this.$root.$t("template.Error")
              );
              this.$root.global_loading = false;
            } else {
              this.$toastr.s(
                this.$root.$t("template.Success"),
                this.$root.$t("template.Success")
              );
              connection.is_approved = true;
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
  },
};
</script>