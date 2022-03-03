<style>
</style>

<template lang="pug">
section.diga-container.p-4
  .float-sm-right.mr-2
    button.btn.btn-diga.float-right(@click="showModal") {{ $t('template.Add') }}
  datatable.datatable-wrapper(v-bind="table")
    h2 {{ $t('template.contractors_and_teams') }}
  #modal-create-group.modal.fade(tabindex="-1", aria-hidden="true")
    .modal-dialog.modal-dialog-centered(role="document")
      .modal-content
        .modal-header {{ $t('template.Add') }}
        .modal-body
          .row
            .col-12
              .form-group
                label(for="groupName") {{ $t('dashboard.name') }}
                input#groupName.form-control(
                  :placeholder="$t('dashboard.name')",
                  v-model="group.name"
                )
          .row.mt-3
            .col-12.text-center
              button.btn.btn-diga(v-on:click="create", style="cursor: pointer") {{ $t('template.Add') }}
</template>

<script>
import client_column from "./custom_columns/td_company.vue";
import email_column from "./custom_columns/td_email.vue";
import phone_column from "./custom_columns/td_phone.vue";
import type_column from "./custom_columns/td_type.vue";

export default {
  data: function () {
    return {
      group: {
        name: "",
        users_ids: [],
      },
      table: {
        columns: [
          {
            title: this.$root.$t("hr.Name"),
            field: "name",
            tdStyle: "width: 200px;",
            sortable: true,
          },
          { title: this.$root.$t("client.Perfil"), tdComp: client_column },
          { title: "Email", tdComp: email_column },
          { title: this.$root.$t("calendar.Phone"), tdComp: phone_column },
          {
            title: this.$root.$t("template.Department_or_company"),
            tdComp: type_column,
          },
        ],
        data: [],
        total: 0,
        query: {
          offset: this.offset || 0,
        },
      },
    };
  },
  props: ["offset"],
  methods: {
    showModal() {
      jQuery("#modal-create-group").modal("show");
    },
    create() {
      this.$root.global_loading = true;
      this.$http.post("/api/hr/groups", this.group).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            this.$root.global_loading = false;
          } else {
            this.$toastr.s(
              this.$root.$t("client.Client_saved"),
              this.$root.$t("template.Success")
            );
            this.getResults();
            jQuery("#modal-create-group").modal("hide");
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
    getResults() {
      this.$http
        .get("/api/hr/groups/?" + this.$root.params(this.table.query))
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.table.data = data.rows;
              this.table.total = data.total;
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
  watch: {
    "table.query": {
      handler(query) {
        this.getResults();
      },
      deep: true,
    },
  },
  mounted() {
    document.title =
      this.$root.global_settings.site_name +
      " | " +
      this.$root.$t("template.contractors_and_teams");
    this.$bus.$on("get_results", this.getResults);
  },
  beforeDestroy: function () {
    this.getResults && this.$bus.$off("get_results", this.getResults);
  },
};
</script>