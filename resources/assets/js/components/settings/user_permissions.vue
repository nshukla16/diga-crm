<style>
.permissions tbody td {
  padding: 10px;
}
</style>

<template lang="pug">
div(v-if="edit_user")
  .row
    .col-12.mb-3
      .diga-container.p-4
        h2 {{ $t('template.Permissions_for') + ' ' + edit_user.name }}
        .table-responsive
          table.table.table-striped.permissions.w-100
            thead
              tr
                td {{ $t('template.Type') }}
                td {{ $t('template.Creating') }}
                td {{ $t('template.Reading') }}
                td {{ $t('template.Updating') }}
                td {{ $t('template.Deleting') }}
                td {{ $t('template.Execution') }}
                //td Export
                td(style="width: 18%") {{ $t('template.Description') }}
            tbody
              tr(
                v-for="role in roles",
                v-if="role.action != 'projects' && $root.module_enabled(role.module)"
              )
                td {{ $t('template.' + capitalize(role.action)) }}
                td
                  permission(
                    :value="find_user_role(role.action).create",
                    :values="create_options(find_user_role(role.action))",
                    :action="role.action",
                    :emit_action="'create'",
                    @change="change_value"
                  )
                td
                  permission(
                    v-bind:value="find_user_role(role.action).read",
                    v-bind:values="read_options(find_user_role(role.action))",
                    :action="role.action",
                    :emit_action="'read'",
                    @change="change_value"
                  )
                td
                  permission(
                    v-bind:value="find_user_role(role.action).update",
                    v-bind:values="update_options(find_user_role(role.action))",
                    :action="role.action",
                    :emit_action="'update'",
                    @change="change_value"
                  )
                td
                  permission(
                    v-bind:value="find_user_role(role.action).delete",
                    v-bind:values="delete_options(find_user_role(role.action))",
                    :action="role.action",
                    :emit_action="'delete'",
                    @change="change_value"
                  )
                td
                  permission(
                    v-bind:value="find_user_role(role.action).addit",
                    v-bind:values="addit_options(find_user_role(role.action))",
                    :action="role.action",
                    :emit_action="'addit'",
                    @change="change_value"
                  )
                //td
                  permission
                td {{ $t('template.' + role.action + '_permission_description') }}
        p_perm(v-if="$root.module_enabled('project')", :user="edit_user")
        div
          div
            label.control-label {{ $t('hr.Dashboard') }}
            select.form-control.d-inline.ml-2(
              style="width: 200px",
              v-model="edit_user.dashboard_id"
            )
              option(value="0") {{ $t('hr.No_dashboard') }}
              option(
                v-for="dashboard in dashboards",
                v-bind:value="dashboard.id"
              ) {{ dashboard.name }}
          div
            input(type="checkbox", v-model="edit_user.can_see_prices")
            span.ml-2 {{ $t('template.Can_see_prices') }}
          div(v-if="$root.module_enabled('time_tracker')")
            input(type="checkbox", v-model="edit_user.can_use_timetracker")
            span.ml-2 {{ $t('template.Can_use_timetracker') }}
          div(v-if="$root.module_enabled('time_tracker')")
            input(
              type="checkbox",
              v-model="edit_user.can_view_results_of_timetracker"
            )
            span.ml-2 {{ $t('template.Can_view_results_of_timetracker') }}
          div(v-if="$root.module_enabled('kpi')")
            input(type="checkbox", v-model="edit_user.can_see_kpi")
            span.ml-2 {{ $t('template.Can_see_kpi') }}
          div
            input(
              type="checkbox",
              v-model="edit_user.new_client_notifications"
            )
            span.ml-2 {{ $t('template.Show_new_client_notifications') }}
          div
            input(
              type="checkbox",
              v-model="edit_user.new_fb_messages_notifications"
            )
            span.ml-2 {{ $t('template.Show_new_fb_messages_notifications') }}
          div(v-if="settings.zadarma_enabled")
            input(
              type="checkbox",
              v-model="edit_user.missed_calls_notifications"
            )
            span.ml-2 {{ $t('template.Show_missed_calls_notifications') }}
          div
            input(type="checkbox", v-model="edit_user.email_suggestions")
            span.ml-2 {{ $t('template.Hide_email_suggestions') }}
          div
            input(type="checkbox", v-model="edit_user.is_admin")
            span.ml-2 {{ $t('template.Admin') }}
          div(v-if="settings.zadarma_enabled")
            input(type="checkbox", v-model="edit_user.can_view_calls")
            span.ml-2 {{ $t('template.Can_view_calls') }}
          div(v-if="$root.module_enabled('financial_calendar')")
            input(
              type="checkbox",
              v-model="edit_user.can_see_financial_calendar"
            )
            span.ml-2 {{ $t('template.Can_view_financial_calendar') }}
          div
            input(type="checkbox", v-model="edit_user.can_export")
            span.ml-2 {{ $t('template.Can_export') }}
          div(v-if="$root.module_enabled('estimates')")
            input(type="checkbox", v-model="edit_user.can_give_discount")
            span.ml-2 {{ $t('template.can_give_discount') }}
          div(v-if="$root.module_enabled('estimates')")
            input(
              type="checkbox",
              v-model="edit_user.can_enter_timesheet_and_consumption"
            )
            span.ml-2 {{ $t('template.can_enter_timesheet_and_consumption') }}
          div
            input(type="checkbox", v-model="edit_user.can_approve_vacations")
            span.ml-2 {{ $t('template.can_approve_vacations') }}
          div(v-if="$root.module_enabled('project')")
            input(type="checkbox", v-model="edit_user.can_finish_projects")
            span.ml-2 {{ $t('project.can_finish_projects') }}
  .row
    .col-12
      button.btn.btn-diga(v-on:click="save_settings") {{ $t('template.Save') }}
</template>

<script>
import permission from "./permission.vue";
import p_perm from "./project_permissions.vue";

export default {
  data() {
    return {
      edit_user: null,
      dashboards: [],
      settings: null,
      roles: [
        { action: "services", module: "crm" },
        { action: "events", module: "crm" },
        { action: "clients", module: "crm" },
        { action: "estimates", module: "estimate" },
        { action: "fichas", module: "estimate" },
        { action: "resources", module: "estimate" },
        { action: "forks", module: "estimate" },
        { action: "plannings", module: "estimate" },
        { action: "users", module: "crm" },
        { action: "projects", module: "project" },
        { action: "legal_entities", module: "project" },
        { action: "shipping_orders", module: "project" },
        { action: "expences", module: "expences" },
        { action: "invoices", module: "invoices" },
        { action: "products", module: "invoices" },
      ],
    };
  },
  components: {
    permission,
    p_perm,
  },
  props: ["id"],
  created() {
    this.load_user();
    this.load_dashboards();
    this.settings = this.$store.getters.getGlobalSettings;
  },
  mounted() {
    // title is filling inside load_user()
  },
  methods: {
    change_value(value) {
      let userRole = this.edit_user.roles.find(
        (r) => r.action === value.action
      );
      if (userRole != null) {
        switch (value.emit_action) {
          case "create":
            userRole.create = value.value;
            break;
          case "read":
            userRole.read = value.value;
            break;
          case "update":
            userRole.update = value.value;
            break;
          case "delete":
            userRole.delete = value.value;
            break;

          case "export":
            userRole.export = value.value;
            break;

          case "addit":
            userRole.addit = value.value;
            break;
        }
      }
    },
    find_user_role(action) {
      let userRole = this.edit_user.roles.find((r) => r.action === action);
      if (userRole) {
        return userRole;
      } else {
        userRole = {
          action: action,
          create: 0,
          read: 0,
          update: 0,
          delete: 0,
          export: 0,
          addit: 0,
        };
        this.edit_user.roles.push(userRole);
        return userRole;
      }
    },
    load_user() {
      this.$root.global_loading = true;
      this.$http.get("/api/users/" + this.id).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            this.$root.global_loading = false;
          } else {
            this.edit_user = res.data;
            document.title =
              this.$root.global_settings.site_name +
              " | " +
              this.$root.$t("template.Permissions_for") +
              " " +
              this.edit_user.name;
            if (this.edit_user.dashboard_id == null) {
              this.edit_user.dashboard_id = 0;
            }
            this.$root.global_loading = false;
          }
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
    load_dashboards() {
      this.$root.global_loading = true;
      this.$http.get("/api/dashboards/").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.dashboards = res.data.rows;
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
    capitalize(str) {
      return str.charAt(0).toUpperCase() + str.substr(1);
    },
    create_options(role) {
      switch (role.action) {
        case "plannings":
          return [];
        default:
          return [
            {
              value: 0,
              title: this.$root.$t("template.Forbidden"),
              enabled: true,
            },
            {
              value: 1,
              title: this.$root.$t("template.Allowed"),
              enabled: true,
            },
          ];
      }
    },
    read_options(role) {
      switch (role.action) {
        case "services":
        case "events":
        case "clients":
        case "estimates":
          return [
            {
              value: 0,
              title: this.$root.$t("template.Forbidden"),
              enabled: true,
            },
            {
              value: 1,
              title: this.$root.$t("template.If_responsible"),
              enabled: true,
            },
            {
              value: 2,
              title: this.$root.$t("template.For_group"),
              enabled: true,
            },
            {
              value: 3,
              title: this.$root.$t("template.Allowed"),
              enabled: true,
            },
          ];
        case "users":
          return [
            {
              value: 0,
              title: this.$root.$t("template.Forbidden"),
              enabled: true,
            },
            {
              value: 1,
              title: this.$root.$t("template.For_group"),
              enabled: true,
            },
            {
              value: 2,
              title: this.$root.$t("template.Allowed"),
              enabled: true,
            },
          ];
        case "forks":
          return [];
        default:
          return [
            {
              value: 0,
              title: this.$root.$t("template.Forbidden"),
              enabled: true,
            },
            {
              value: 1,
              title: this.$root.$t("template.Allowed"),
              enabled: true,
            },
          ];
      }
    },
    update_options(role) {
      if (role.action != "users") {
        role.update = Math.min(role.read, role.update);
      }
      switch (role.action) {
        case "services":
        case "events":
        case "clients":
        case "estimates":
          return [
            {
              value: 0,
              title: this.$root.$t("template.Forbidden"),
              enabled: true,
            },
            {
              value: 1,
              title: this.$root.$t("template.If_responsible"),
              enabled: [1, 2, 3].includes(role.read),
            },
            {
              value: 2,
              title: this.$root.$t("template.For_group"),
              enabled: [2, 3].includes(role.read),
            },
            {
              value: 3,
              title: this.$root.$t("template.Allowed"),
              enabled: [3].includes(role.read),
            },
          ];
        case "users":
          return [
            {
              value: 0,
              title: this.$root.$t("template.Forbidden"),
              enabled: true,
            },
            {
              value: 1,
              title: this.$root.$t("template.Allowed"),
              enabled: true,
            },
          ];
        case "forks":
          return [];
        default:
          return [
            {
              value: 0,
              title: this.$root.$t("template.Forbidden"),
              enabled: true,
            },
            {
              value: 1,
              title: this.$root.$t("template.Allowed"),
              enabled: [1].includes(role.read),
            },
          ];
      }
    },
    delete_options(role) {
      role.delete = Math.min(role.update, role.delete);
      switch (role.action) {
        case "services":
        case "events":
        case "clients":
        case "estimates":
          return [
            {
              value: 0,
              title: this.$root.$t("template.Forbidden"),
              enabled: true,
            },
            {
              value: 1,
              title: this.$root.$t("template.If_responsible"),
              enabled: [1, 2, 3].includes(role.update),
            },
            {
              value: 2,
              title: this.$root.$t("template.For_group"),
              enabled: [2, 3].includes(role.update),
            },
            {
              value: 3,
              title: this.$root.$t("template.Allowed"),
              enabled: [3].includes(role.update),
            },
          ];
        case "users":
          return [
            {
              value: 0,
              title: this.$root.$t("template.Forbidden"),
              enabled: true,
            },
            {
              value: 1,
              title: this.$root.$t("template.Allowed"),
              enabled: [1].includes(role.update),
            },
          ];
        case "plannings":
        case "forks":
          return [];
        default:
          return [
            {
              value: 0,
              title: this.$root.$t("template.Forbidden"),
              enabled: true,
            },
            {
              value: 1,
              title: this.$root.$t("template.Allowed"),
              enabled: [1].includes(role.update),
            },
          ];
      }
    },
    addit_options(role) {
      switch (role.action) {
        case "events":
          return [
            {
              value: 0,
              title: this.$root.$t("template.Forbidden"),
              enabled: true,
            },
            {
              value: 1,
              title: this.$root.$t("template.If_responsible"),
              enabled: [1, 2, 3].includes(role.read),
            },
            {
              value: 2,
              title: this.$root.$t("template.For_group"),
              enabled: [2, 3].includes(role.read),
            },
            {
              value: 3,
              title: this.$root.$t("template.Allowed"),
              enabled: [3].includes(role.read),
            },
          ];
        default:
          return [];
      }
    },
    save_settings() {
      this.$root.global_loading = true;
      this.$http
        .post("/api/hr/" + this.id + "/permissions", {
          edit_user: this.edit_user,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("template.Settings_saved"),
                this.$root.$t("template.Success")
              );
            }
            this.$root.global_loading = false;
          },
          (res) => {
            this.$root.global_loading = false;
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
          }
        );
    },
  },
};
</script>