<style>
#services_block .text-center {
  text-align: center;
  height: 50px;
}
.estimates {
  width: 100%;
  margin-bottom: 20px;
  margin-top: 20px;
}
.estimates td {
  padding-bottom: 5px;
}
.estimate-locker {
  margin-right: 15px;
  font-size: 18px;
  cursor: pointer;
}
#services_block .actions {
  float: right;
}
</style>

<template lang="pug">
div
  #services_block.diga-container(style="height: 550px")
    .portlet-title
      .caption
        button.btn(
          v-if="($root.can_do('services', 'create') != 0 && main_contact_id) || is_group === true",
          @click="create_service"
        )
          i.fa.fa-plus
        span.caption-subject.bold.uppercase.ml-2(
          v-if="!$root.module_enabled('project') || !editable || company_id == null"
        ) {{ $t('client.Services') }}
        card-menu(v-else, :current_section="current_section")
        span.float-right(
          @click="maximize_or_minimize",
          style="margin-right: 5px; font-size: 16px; cursor: pointer"
        )
          i.fa.fa-window-maximize(v-if="!maximized")
          i.fa.fa-window-minimize(v-else)
        .actions.mr-2
          bootstrap-toggle(
            data-size="mini",
            v-model="active_services",
            :options="{ on: $t('client.Active'), off: $t('client.All') }",
            data-width="120",
            data-height="30",
            data-onstyle="default",
            ref="services_toggle"
          )
        .actions.mr-2
          a(@click="sort_by_date")
            i.fa.fa-arrow-up(v-if="service_sort === 'asc'")
            i.fa.fa-arrow-down(v-if="service_sort === 'desc'")
    .portlet-body
      div(v-bar="", style="height: 480px")
        #services-scroller
          #services-list(v-if="services.length > 0")
            service(
              v-for="service in services",
              :service="service",
              :key="service.id",
              :editable="editable",
              v-on:set_service="set_current_service",
              v-on:create_additional="create_additional",
              v-on:open_attachments="open_attachments",
              v-on:send_estimates="send_estimates",
              @fetch_services_from_child="fetch_services_from_child",
              @fetch_platform_id="fetch_platform_id"
            )
            infinite-loading(
              @infinite="infiniteHandler",
              :identifier="infiniteId"
            )
          .empty-filler(v-else) {{ $t('client.There_is_no_services') }}
  view_estimates_modal#view_estimate_modal(
    :service="current_service",
    :forks="forks"
  )
  attachments_modal#attachments_modal(:service="current_service")
  send_estimates_modal#send_estimates_modal(:service="current_service")
  fill_checklist_modal#fill_checklist_modal(
    :checklist="checklist",
    :service="current_service"
  )
  #modal-create-service.modal.fade(tabindex="-1", aria-hidden="true")
    .modal-dialog.modal-dialog-centered(role="document")
      .modal-content
        .modal-header {{ $t('template.add_service_to_contractor') }}
        .modal-body
          .row
            .col-12
              .form-group
                label(for="serviceSearch") {{ $t('calendar.Service') }}
                v-select#serviceSearch(
                  :debounce="250",
                  :on-search="get_services_options",
                  :on-change="services_select",
                  :options="search_services",
                  :placeholder="$t('client.Service')"
                )
                  template(slot="no-options") {{ $t('template.No_matching_options') }}
              .form-group(v-if="selected_contract_service_id")
                label(for="estimatesSearch") {{ $t('client.Estimate') }}
                select.form-control(v-model="selected_estimate_id")
                  option(value="0") {{ $t('hr.Without_estimate') }}
                  option(v-for="es in contract_estimates", :value="es.id") 
                    template(
                      v-if="contract_service.master_estimate_id === es.id"
                    ) {{ $t('client.Master') }} {{ estimate_number(es) }}
                    template(v-else) {{ estimate_number(es) }}
          .row.mt-3
            .col-12.text-center
              button.btn.btn-diga(
                :disabled="selected_contract_service_id === null",
                v-on:click="add_service_to_contractor",
                style="cursor: pointer"
              ) {{ $t('template.Add') }}
</template>

<script>
import service from "./_service.vue";
import view_estimates_modal from "./view_estimates_modal.vue";
import attachments_modal from "./attachments_modal.vue";
import send_estimates_modal from "./send_estimates_modal.vue";
import fill_checklist_modal from "./fill_checklist_modal.vue";
import CardMenu from "../card_menu.vue";
import InfiniteLoading from "vue-infinite-loading";

export default {
  props: [
    "main_contact_id",
    "editable",
    "maximized",
    "selected_service",
    "current_section",
    "company_id",
    "is_group",
  ],
  components: {
    service,
    view_estimates_modal,
    attachments_modal,
    send_estimates_modal,
    fill_checklist_modal,
    CardMenu,
    InfiniteLoading,
  },
  data: function () {
    return {
      services: [],
      current_service: null,
      checklist: null,
      loading: false,
      forks: [],
      active_services: true,
      service_sort: "asc",
      page: 1,
      offset: 0,
      limit: 10,
      total: 0,
      infiniteId: +new Date(),
      active_states: [],
      search_services: [],
      selected_contract_service_id: null,
      selected_estimate_id: 0,
    };
  },
  methods: {
    fetch_platform_id(data) {
      let service = this.services.find((s) => s.id === data.id);
      if (service) {
        service.platform_id = data.platform_id;
      }
    },
    fetch_services_from_child() {
      this.services = [];
      this.page = 1;
      this.offset = 0;
      this.infiniteId += 1;
      this.fetch_services();
    },
    estimate_number(estimate) {
      return (
        this.$root.service_number(this.contract_service) +
        (estimate.option != null
          ? " " + this.$t("template.option") + estimate.option
          : "") +
        (estimate.revision != null
          ? " " + this.$t("template.revision") + estimate.revision
          : "") +
        (estimate.fork_id != null
          ? " " + this.$root.get_estimate_fork(estimate)
          : "")
      );
    },
    services_select(res) {
      if (res === null) {
        this.selected_contract_service_id = null;
        this.selected_estimate_id = 0;
      }
      if (typeof res === "object" && res !== null) {
        this.selected_contract_service_id = res.value;
        this.selected_estimate_id = 0;
      }
    },
    get_services_options(search, loading) {
      loading(true);
      var url = "/api/services?query=" + search;
      this.$http.get(url).then(
        (res) => {
          var processedData = [];
          let $this = this;
          res.data.rows.forEach(function (i) {
            processedData.push({
              label:
                (i.name === null ? "" : i.name.substr(0, 60) + "... ") +
                $this.$root.service_number(i),
              value: i.id,
              estimates: i.estimates,
              estimate_number: i.estimate_number,
              additional: i.additional,
              master_estimate_id: i.master_estimate_id,
            });
          });
          this.search_services = processedData;
          loading(false);
        },
        (res) => {
          this.$toastr.e(
            this.$root.$t("template.Something_bad_happened"),
            this.$root.$t("template.Error")
          );
        }
      );
    },
    create_service() {
      if (this.is_group !== true) {
        if (this.main_contact_id) {
          this.$router.push({
            name: "service_create",
            query: { contact_id: this.main_contact_id },
          });
        }
      } else {
        jQuery("#modal-create-service").modal("show");
      }
    },
    add_service_to_contractor() {
      this.$root.global_loading = true;
      this.$http
        .post("/api/estimate_groups", {
          client_id: this.company_id,
          estimate_id: this.selected_estimate_id,
          service_id: this.selected_contract_service_id,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              jQuery("#modal-create-service").modal("hide");
              this.services = [];
              this.page = 1;
              this.offset = 0;
              this.infiniteId += 1;
              this.fetch_services();
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
    fetch_services() {
      this.$root.global_loading = true;
      this.get_services()
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.page += 1;
              this.total = data.total;
              this.services = data.rows;
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
    get_services() {
      let url =
        "/api/services/by_client_contact?client_contact_id=" +
        this.main_contact_id;

      if (this.company_id > 0) {
        url = "/api/services/by_company?client_id=" + this.company_id;
      }
      if (this.is_group === true) {
        url = "/api/services/by_company_is_group?client_id=" + this.company_id;
      }
      this.offset = (this.page - 1) * this.limit;
      return this.$http.get(
        url +
          "&offset=" +
          this.offset +
          "&limit=" +
          this.limit +
          "&order=" +
          this.service_sort
      );
    },
    infiniteHandler($state) {
      this.get_services()
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              if (data.rows.length) {
                this.page += 1;
                this.services.push(...data.rows);
                $state.loaded();
              } else {
                $state.complete();
              }
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
    sort_by_date() {
      var prev = window.localStorage.getItem("service_sort");
      if (prev === null || prev === "") {
        prev = this.service_sort;
      }

      if (prev === "asc") {
        localStorage.setItem("service_sort", "desc");
        this.service_sort = "desc";
      }
      if (prev === "desc") {
        localStorage.setItem("service_sort", "asc");
        this.service_sort = "asc";
      }

      this.services = [];
      this.page = 1;
      this.offset = 0;
      this.infiniteId += 1;
      this.fetch_services();
    },
    maximize_or_minimize: function () {
      this.$emit("maximize_minimize_click");
      this.$bus.$emit("maximize_minimize_click_for_service");
    },
    // estimates
    set_current_service: function (service, forks) {
      this.forks = forks;
      this.current_service = service;
      this.current_service.estimates.forEach(function (i) {
        i.selected = false;
      });
      jQuery("#view_estimate_modal").modal("show");
    },
    create_additional: function (service) {
      this.services.push(service);
      Vue.nextTick(function () {
        $("a[href='#service_" + service.id + "']").trigger("click");
        setTimeout(function () {
          let element = document.getElementById("services-scroller");
          element.scrollTop = element.scrollHeight - element.clientHeight;
        }, 250);
      });
    },
    open_attachments: function (service) {
      this.current_service = service;
      jQuery("#attachments_modal").modal("show");
    },
    send_estimates: function (service) {
      this.current_service = service;
      jQuery("#send_estimates_modal").modal("show");
    },
    fill_checklist(checklist, service, global_data) {
      this.checklist = checklist;
      this.current_service = service;
      jQuery("#fill_checklist_modal").modal("show");
    },
  },
  watch: {
    active_services(newVal, oldVal) {
      if (newVal === true) {
        if (this.$root.user.is_admin) {
          this.active_states = [];
        } else {
          this.active_states = $this.$root.department_states;
        }
      } else {
        this.active_states = [];
      }
      this.services = [];
      this.page = 1;
      this.offset = 0;
      this.infiniteId += 1;
      this.fetch_services();
    },
  },
  computed: {
    contract_service() {
      if (this.selected_contract_service_id > 0) {
        let s = this.search_services.find(
          (x) => x.value === this.selected_contract_service_id
        );
        return s;
      } else {
        return null;
      }
    },
    contract_estimates() {
      if (this.contract_service) {
        return this.contract_service.estimates;
      } else {
        return [];
      }
    },
  },
  mounted() {
    let $this = this;
    this.$bus.$on("fill_checklist_form", this.fill_checklist);
    if (this.selected_service != null) {
      setTimeout(function () {
        $("#service_" + $this.selected_service)
          .collapse()
          .parent(".service")
          .addClass("service-selected");
        Vue.nextTick(function () {
          $this.$bus.$emit("maximize_minimize_click_for_service");
        });
      }, 1000);
    }

    if (this.$root.user.is_admin) {
      this.active_states = [];
    } else {
      this.active_states = $this.$root.department_states;
    }

    var prev = window.localStorage.getItem("service_sort");

    if (prev !== null && prev !== "") {
      this.service_sort = prev;
    }
    this.fetch_services();
  },
  beforeDestroy: function () {
    this.fill_checklist &&
      this.$bus.$off("fill_checklist_form", this.fill_checklist);
  },
};
</script>