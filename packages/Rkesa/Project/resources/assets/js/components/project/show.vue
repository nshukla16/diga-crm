<style>
.tabs_container {
  display: grid;
  grid-template-columns: repeat(7, 14.286%);
  margin-top: 25px;
}
.tabs_container > div {
  text-align: center;
  background-color: #24c5c3;
  padding: 10px 0;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.5);
  transition: all 0.1s;
  color: #fff;
  cursor: pointer;
}
.tabs_container > div:first-child {
  border-top-left-radius: 10px;
}
.tabs_container > div:last-child {
  border-top-right-radius: 10px;
}
.tabs_container > div:hover {
  background-color: #25b5c5;
}
.diga-container-dynamic {
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}
div .tab_active {
  background-color: #fff !important;
  color: #2a6668;
}
@media (max-width: 576px) {
  .tabs_container > div {
    font-size: 12px;
    padding: 10px 10px;
  }
}
.input-line {
  line-height: 38px;
  margin-bottom: 0;
}
table .mx-datepicker {
  height: 38px;
}
.diga-container-dynamic .btn-diga {
  font-size: 16px;
}
a.short-link {
  max-width: 150px;
  overflow: hidden;
  display: inline-block;
  white-space: nowrap;
  text-overflow: ellipsis;
  vertical-align: middle;
}
.plus_list_item {
  background-color: #24c5c3;
  padding: 5px 10px;
}
.plus_list_item:hover {
  background-color: #25b5c5;
}
.close-logistics {
  float: right;
  line-height: 24px;
  margin-right: 10px;
}
.close-logistics:hover {
  font-weight: bold;
}
</style>

<template lang="pug">
.project-wrapper(style="padding-bottom: 30px", v-if="my_project")
  project_changed_window(:project_id="id")
  .d-flex
    h2.d-inline-block.mr-2(style="flex: 1")
      | {{ $t('project.Project') }} : {{ my_project.name }}
      router-link.ml-3(
        :to="{ name: 'project_edit', params: { id: my_project.id } }",
        style="font-size: 22px",
        v-if="$root.can_with_project('update', 5) && !my_project.finished"
      )
        i.fa.fa-pencil
    span.mr-2(v-if="my_project.finished", style="line-height: 38px") {{ $t('project.Project_finished_date') }}: {{ format_finished_at(my_project.finished_at) }}
    router-link.btn.btn-diga(
      :to="{ name: 'project_history', params: { id: my_project.id } }",
      style="margin-right: 10px; height: 38px"
    ) {{ $t('project.History') }}
    select.form-control.mr-2(
      style="width: 200px",
      v-model="my_project.project_status_id"
    )
      option(v-for="status in project_statuses", :value="status.id") {{ status.name }}
  section.diga-container.p-4(v-if="$root.can_with_project('read', 5)")
    general_read_only(:project="my_project")
  .tabs_container
    div(
      v-if="$root.can_with_project('read', 3)",
      v-on:click="setActive('first')",
      :class="{ tab_active: isActive('first') }"
    ) {{ $t('project.Common') }}
    div(
      v-if="($root.can_with_project('read', 0) || $root.can_with_project('read', 1)) && my_project.project_type_id !== 3",
      v-on:click="setActive('second')",
      :class="{ tab_active: isActive('second') }"
    ) {{ $t('project.Dea_and_orders') }}
    div(
      v-if="($root.can_do('shipping_orders', 'read') || $root.can_with_project('read', 2)) && my_project.logistics_enabled && my_project.project_type_id !== 3",
      v-on:click="setActive('third')",
      :class="{ tab_active: isActive('third') }"
    )
      | {{ $t('project.Logistics') }}
      i.fa.fa-times.close-logistics(@click.stop="disable_logistics")
    div(
      v-if="$root.can_with_project('read', 4) && my_project.project_type_id !== 3",
      v-on:click="setActive('fourth')",
      :class="{ tab_active: isActive('fourth') }"
    ) {{ $t('project.Technical_documentation') }}
    div(
      v-if="$root.can_with_project('read', 9)",
      v-on:click="setActive('seventh')",
      :class="{ tab_active: isActive('seventh') }"
    ) {{ $t('project.additional_expenses_block') }}
    div(
      v-if="$root.can_with_project('read', 4)",
      v-on:click="setActive('fifth')",
      :class="{ tab_active: isActive('fifth') }"
    ) {{ $t('project.Installation_and_service') }}
    div(
      v-if="$root.can_with_project('read', 8)",
      v-on:click="setActive('sixth')",
      :class="{ tab_active: isActive('sixth') }"
    ) {{ $t('project.Side_payments_block') }}
    div(
      v-if="($root.can_do('shipping_orders', 'read') || $root.can_with_project('read', 2)) && !my_project.logistics_enabled",
      style="width: 40px",
      v-on:click="show_plus_list()"
    )
      i.fa.fa-plus
      div(
        v-if="plus_list",
        style="position: absolute",
        v-on-clickaway="hide_plus_list"
      )
        .plus_list_item(v-on:click="enable_logistics()") {{ $t('project.Logistics') }}
  section.diga-container.p-4.diga-container-dynamic(
    style="",
    v-if="can_read_anything_except_header"
  )
    div(v-if="isActive('first') && $root.can_with_project('read', 3)")
      common_limits(:project="my_project")
      common_equipment(:project="my_project")
      common_contract_payment_steps(:project="my_project")
      common_documents(:project="my_project")
    div(
      v-if="isActive('second') && ($root.can_with_project('read', 0) || $root.can_with_project('read', 1))"
    )
      manufacturer_relationships(:project="my_project")
    div(v-if="isActive('third')")
      logistic_list(:project="my_project")
    div(v-if="isActive('fourth') && $root.can_with_project('read', 4)")
      technical_documents(:project="my_project")
    div(v-if="isActive('fifth') && $root.can_with_project('read', 4)")
      installation_common(:project="my_project")
      installation_expenses(:project="my_project")
      installation_documents(:project="my_project")
    div(v-if="isActive('sixth') && $root.can_with_project('read', 8)")
      side_payments(:project="my_project")
    div(v-if="isActive('seventh') && $root.can_with_project('read', 9)")
      additional_expenses(:project="my_project")
  .fixed-bottom.panel.panel-default.bottom-fixed-panel(
    style="border-top: 1px solid rgb(199, 199, 199); background-color: rgba(255, 255, 255, 1)"
  )
    .text-center(style="width: 90%; margin: auto")
      .row(v-if="!isActive('third')")
        .col-md-6.text-left
          button.btn.btn-danger(
            style="margin: 5px; padding: 5px 10px",
            v-on:click="update_project()",
            v-if="can_update_anything_except_header"
          ) {{ $t('project.Save') }}
        .col-md-6.text-right
          button.btn.btn-diga(
            :disabled="$root.user.can_finish_projects !== true",
            style="margin: 5px; padding: 5px 10px",
            v-if="!my_project.finished",
            v-on:click="show_finish_project_modal"
          ) {{ $t('project.Finish_project') }}
      portal-target(v-else, name="logistic-save-button-destination")
  #modal-finish-project.modal.fade(tabindex="-1", aria-hidden="true")
    .modal-dialog.modal-dialog-centered(role="document")
      .modal-content
        .modal-header
          h2 {{ $t('project.Finish_project') }}
        .modal-body
          label.w-100 {{ $t('project.Project_finished_date') }}
          date-picker(
            type="month",
            :first-day-of-week="$root.global_settings.first_day_of_week",
            :lang="$root.locale",
            v-model="tmp_finished_at",
            :value-type="$root.valueType",
            format="MM.YYYY"
          )
        .modal-footer(style="justify-content: center")
          button.btn.btn-diga(
            style="padding: 5px 10px",
            v-on:click="finish_project"
          ) {{ $t('project.Finish_project') }}
</template>

<script>
import common_limits from "./tabs/common_limits.vue";
import common_equipment from "./tabs/common_equipment.vue";
import common_documents from "./tabs/common_documents.vue";
import common_contract_payment_steps from "./tabs/common_contract_payment_steps.vue";
import general_read_only from "./general_read_only.vue";
import manufacturer_relationships from "./tabs/manufacturer_relationships.vue";
import logistic_list from "./tabs/logistic_list.vue";
import technical_documents from "./tabs/technical_documents.vue";
import installation_common from "./tabs/installation_common.vue";
import installation_expenses from "./tabs/installation_expenses.vue";
import installation_documents from "./tabs/installation_documents.vue";
import project_changed_window from "./project_changed_window.vue";
import side_payments from "./tabs/side_payments.vue";
import additional_expenses from "./tabs/additional_expenses.vue";
import { mapGetters } from "vuex";
import moment from "moment";

export default {
  components: {
    general_read_only,
    common_limits,
    common_equipment,
    common_documents,
    common_contract_payment_steps,
    manufacturer_relationships,
    logistic_list,
    technical_documents,
    installation_common,
    installation_expenses,
    installation_documents,
    project_changed_window,
    side_payments,
    additional_expenses,
  },
  props: ["id", "tab"],
  data() {
    return {
      my_project: null,
      activeItem: "first",
      plus_list: false,
      selected_order: null,
      tmp_finished_at: null,
    };
  },
  watch: {
    activeItem: function () {
      if (this.activeItem !== "third") {
        this.$route.params.orderId = null;
      }
    },
  },
  mounted() {
    this.$bus.$on("create_order", this.create_manufacturer_order);
  },
  beforeDestroy() {
    this.create_manufacturer_order &&
      this.$bus.$off("create_order", this.create_manufacturer_order);
  },
  created() {
    if (this.$route.params.orderId) {
      this.selected_order = this.$route.params.orderId;
      this.activeItem = "third";
    }
    if (this.$route.params.tab) {
      this.activeItem = this.$route.params.tab;
    }
    document.title =
      this.$root.global_settings.site_name +
      " | " +
      this.$root.$t("project.Project");
    this.load_project();
    if (!this.$root.can_with_project("read", 3)) {
      if (
        this.$root.can_with_project("read", 0) ||
        this.$root.can_with_project("read", 1)
      ) {
        this.activeItem = "second";
      } else if (
        this.$root.can_do("shipping_orders", "read") ||
        this.$root.can_with_project("read", 2)
      ) {
        this.activeItem = "third";
      }
    }
  },
  computed: {
    ...mapGetters({
      project_statuses: "getProjectStatuses",
    }),
    can_read_anything_except_header() {
      return (
        this.$root.can_with_project("read", 0) ||
        this.$root.can_with_project("read", 1) ||
        this.$root.can_do("shipping_orders", "read") ||
        this.$root.can_with_project("read", 2) ||
        this.$root.can_with_project("read", 3) ||
        this.$root.can_with_project("read", 4)
      );
    },
    can_update_anything_except_header() {
      return (
        this.$root.can_with_project("update", 0) ||
        this.$root.can_with_project("update", 1) ||
        this.$root.can_do("shipping_orders", "update") ||
        this.$root.can_with_project("update", 2) ||
        this.$root.can_with_project("update", 3) ||
        this.$root.can_with_project("update", 4)
      );
    },
  },
  methods: {
    format_finished_at(datetime) {
      return moment(datetime).format("MM.YYYY");
    },
    show_plus_list() {
      this.plus_list = true;
    },
    hide_plus_list() {
      this.plus_list = false;
    },
    disable_logistics() {
      this.my_project.logistics_enabled = false;
    },
    enable_logistics() {
      this.plus_list = false;
      this.my_project.logistics_enabled = true;
    },
    show_finish_project_modal() {
      this.tmp_finished_at = moment();
      $("#modal-finish-project").modal("show");
    },
    finish_project() {
      if (
        confirm(this.$root.$t("project.Are_you_sure_want_to_finish_project"))
      ) {
        this.my_project.finished_at = this.tmp_finished_at;
        this.my_project.finished = true;
        this.update_project();
      }
    },
    load_project() {
      this.$root.global_loading = true;
      this.$http.get("/api/projects/" + this.id).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.my_project = res.data;
            this.my_project.specifications.forEach((item, i, a) => {
              Vue.set(a[i], "from_bd", false);
            });

            if (
              this.my_project.transportation_vat_total === null ||
              this.my_project.transportation_vat_total === 0
            ) {
              if (this.my_project.manufacturers) {
                this.my_project.transportation_vat_total = this.my_project.manufacturers
                  .map((m) =>
                    m.orders
                      .map((o) => o.transportation_vat_total)
                      .reduce((a, b) => parseFloat(a) + parseFloat(b), 0)
                  )
                  .reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
              }
            }
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
    create_manufacturer_order(manufacturer) {
      this.setActive("third");
      let $this = this;
      Vue.nextTick(function () {
        $this.$bus.$emit("create_new_order", manufacturer);
      });
    },
    isActive: function (menuItem) {
      return this.activeItem === menuItem;
    },
    setActive: function (menuItem) {
      this.activeItem = menuItem;
      this.$router.replace("/projects/" + this.id + "/" + menuItem);
    },
    update_project() {
      this.$validator.validateAll().then((result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        }
        this.$root.global_loading = true;
        this.$http
          .patch("/api/projects/" + this.my_project.id, this.my_project)
          .then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
              } else {
                this.$toastr.s(
                  this.$root.$t("project.Project_saved"),
                  this.$root.$t("template.Success")
                );
                $("#modal-finish-project").modal("hide");
                this.load_project();
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
  },
};
</script>