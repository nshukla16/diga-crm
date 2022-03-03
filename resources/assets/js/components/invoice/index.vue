<template lang="pug">
div
  #modal-saft.modal.fade(tabindex="-1", aria-hidden="true")
    .modal-dialog.modal-dialog-centered(role="document")
      .modal-content
        .modal-header {{ $t('template.download_saft') }}
        .modal-body
          .row
            .col-6.text-center
              p {{ $t('hr.begin_date') }}
              date-picker(
                v-model="saft_range[0]",
                :first-day-of-week="$root.global_settings.first_day_of_week",
                type="date",
                :lang="$root.locale == 'pt' ? 'pt-br' : $root.locale",
                :disabled-date="disabledFrom"
              ) 
            .col-6.text-center
              p {{ $t('hr.end_date') }}
              date-picker.mx-2(
                v-model="saft_range[1]",
                :first-day-of-week="$root.global_settings.first_day_of_week",
                type="date",
                :lang="$root.locale == 'pt' ? 'pt-br' : $root.locale",
                :disabled-date="disabledTo"
              ) 
          .row.mt-3
            //- div.col-6.text-center
            //-     a(v-on:click="get_saft('light')" style="cursor: pointer;") {{$t('template.light_format')}}
            .col-12.text-center 
              a(v-on:click="get_saft('normal')", style="cursor: pointer") {{ $t('project.Download') }}

  h2 {{ $t('template.invoices') }}
  section.diga-container.p-4
    .float-sm-right.mr-2
      router-link.btn.btn-diga(
        :event="$root.can_do('invoices', 'create') !== 0 ? 'click' : ''",
        style="height: 38px",
        :to="{ name: 'invoice_create' }"
      ) {{ $t('template.Create') }}
      button.btn.btn-diga.ml-2(
        style="height: 38px",
        v-on:click="open_saft_modal"
      )
        i.fa.fa-download
        span.ml-1 SAFT
    datatable.datatable-wrapper.companies-wrapper(v-bind="table") 
      date-picker#dashboard-range(
        v-model="w_range",
        :first-day-of-week="$root.global_settings.first_day_of_week",
        range,
        type="date",
        :lang="$root.locale == 'pt' ? 'pt-br' : $root.locale"
      ) 

      v-select(
        v-if="$root.enable_companies",
        style="width: 200px",
        v-model="selected_client",
        :debounce="250",
        :on-search="lookupClient",
        :options="clients",
        label="name",
        :placeholder="$t('service.Company_name')"
      )
        template(slot="no-options") {{ $t('template.No_matching_options') }}

      v-select(
        style="width: 200px",
        v-model="selected_client_contact",
        :debounce="250",
        :on-search="lookupClientContact",
        :options="client_contacts",
        label="name",
        :placeholder="$t('calendar.Contact')"
      )
        template(slot="no-options") {{ $t('template.No_matching_options') }}

      v-select(
        style="width: 200px",
        v-model="selected_service",
        :debounce="250",
        :on-search="lookupService",
        :options="services",
        label="name",
        :placeholder="$t('calendar.Service')"
      )
        template(slot="no-options") {{ $t('template.No_matching_options') }}

      v-select(
        v-if="$root.module_enabled('estimate')",
        style="width: 200px",
        v-model="selected_estimate",
        :debounce="250",
        :on-search="lookupEstimate",
        :options="estimates",
        label="name",
        :placeholder="$t('estimate.Choose_estimate')"
      )
        template(slot="no-options") {{ $t('template.No_matching_options') }}
</template>

<script>
import td_number from "./custom_columns/td_number.vue";
import td_sum from "./custom_columns/td_sum.vue";
import td_client from "./custom_columns/td_client.vue";
import td_client_contact from "./custom_columns/td_client_contact.vue";
import td_service from "./custom_columns/td_service.vue";
import td_estimate from "./custom_columns/td_estimate.vue";
import td_pay_stage from "./custom_columns/td_pay_stage.vue";
import td_paid from "./custom_columns/td_paid.vue";
import td_edit from "./custom_columns/td_edit.vue";
import td_invoice_document_type from "./custom_columns/td_invoice_document_type.vue";
import td_children from "./custom_columns/td_children.vue";
import { debounce } from "lodash";
import moment from "moment";

const start = new Date();

export default {
  props: ["offset"],
  data() {
    return {
      invoice_document_types: [],

      saft_range: [moment().subtract(1, "months"), moment()],
      w_range: null,
      selected_client: null,
      clients: [],

      selected_client_contact: null,
      client_contacts: [],

      selected_service: null,
      services: [],

      selected_estimate: null,
      estimates: [],

      table: {
        columns: [
          { title: "#", field: "id", sortable: true },
          {
            title: this.$root.$t("template.Type"),
            field: "document_type_id",
            tdComp: td_invoice_document_type,
            sortable: true,
          },
          {
            title: this.$root.$t("client.Date"),
            field: "invoice_date",
            sortable: true,
          },
          {
            title: this.$root.$t("template.number") + " №",
            field: "invoice_no",
            tdComp: td_number,
            sortable: true,
          },
          {
            title: this.$root.$t("template.incidence"),
            field: "gross_total",
            tdComp: td_sum,
            sortable: true,
          },
          {
            title: this.$root.$t("estimate.Documents"),
            tdComp: td_children,
            sortable: false,
          },
          {
            title: this.$root.$t("service.Company_name"),
            field: "client_id",
            tdComp: td_client,
            sortable: true,
          },
          {
            title: this.$root.$t("calendar.Contact"),
            field: "client_contact_id",
            tdComp: td_client_contact,
            sortable: true,
          },
          {
            title: this.$root.$t("calendar.Service"),
            field: "service_id",
            tdComp: td_service,
            sortable: true,
          },
          {
            title: this.$root.$t("client.Estimate"),
            field: "estimate_id",
            tdComp: td_estimate,
            sortable: true,
          },
          {
            title: this.$root.$t("template.pay_stage"),
            field: "pay_stage_id",
            tdComp: td_pay_stage,
            sortable: true,
          },
          {
            title: this.$root.$t("template.payment_event_paid"),
            field: "is_paid",
            tdComp: td_paid,
            sortable: true,
          },
          { tdComp: td_edit, sortable: false },
        ],
        data: [],
        total: 0,
        query: {
          offset: this.offset || 0,
        },
      },
      filters: {
        client_id: 0,
        client_contact_id: 0,
        service_id: 0,
        estimate_id: 0,
      },
    };
  },
  mounted() {
    document.title =
      this.$root.global_settings.site_name +
      " | " +
      this.$root.$t("template.invoices");
    this.$bus.$on("get_results", this.getResults);
  },
  beforeDestroy() {
    this.getResults && this.$bus.$off("get_results", this.getResults);
  },
  methods: {
    disabledFrom(date) {
      return date > this.saft_range[1];
    },
    disabledTo(date) {
      return date > start || date < this.saft_range[0];
    },
    get_saft(saft_format) {
      if (
        this.saft_range == null ||
        this.saft_range[0] == null ||
        this.saft_range[1] == null
      ) {
        this.$toastr.w(
          this.$root.$t("template.Need_to_fill"),
          this.$root.$t("template.Warning")
        );
        return;
      }
      this.$root.global_loading = true;
      this.$http
        .get(
          "/api/invoices/saft?saft_format=" +
            saft_format +
            "&date_from=" +
            moment(this.saft_range[0]).format("YYYY-MM-DD") +
            "&" +
            "date_to=" +
            moment(this.saft_range[1]).format("YYYY-MM-DD"),
          { responseType: "blob" }
        )
        .then(
          (response) => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute(
              "download",
              "saft " +
                moment(this.saft_range[0]).format("YYYY-MM-DD") +
                " - " +
                moment(this.saft_range[1]).format("YYYY-MM-DD") +
                ".xml"
            );
            document.body.appendChild(link);
            link.click();
            this.$root.global_loading = false;
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.saft_no_invoices_selected"),
              this.$root.$t("template.Warning")
            );
            this.$root.global_loading = false;
          }
        );
    },
    open_saft_modal() {
      jQuery("#modal-saft").modal("show");
    },
    lookupEstimate: debounce(function (estimate_query) {
      this.$http
        .get(`/api/user_plannings?search=${estimate_query}`)
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              data.forEach((i) => {
                i.name = this.$root.estimate_number(i);
              });
              this.estimates = data;
            }
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
          }
        );
    }, 500),
    lookupService: debounce(function (service_query) {
      this.$http
        .get(`/api/services?query=${service_query}`)
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              data.rows.forEach((i) => {
                i.name =
                  (i.name === null ? "" : i.name.substr(0, 60) + "... ") +
                  this.$root.service_number(i);
              });
              this.services = data.rows;
            }
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
          }
        );
    }, 500),
    lookupClientContact: debounce(function (client_contact_query) {
      this.$http
        .get(`/api/contacts?query=${client_contact_query}`)
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.client_contacts = data.rows;
            }
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
          }
        );
    }, 500),
    lookupClient: debounce(function (client_query) {
      this.$http
        .get(`/api/companies?query=${client_query}`)
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.clients = data.rows;
            }
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
          }
        );
    }, 500),
    getResults() {
      let url =
        "/api/invoices?" +
        "client_id=" +
        this.filters.client_id +
        "&" +
        "client_contact_id=" +
        this.filters.client_contact_id +
        "&" +
        "service_id=" +
        this.filters.service_id +
        "&" +
        "estimate_id=" +
        this.filters.estimate_id +
        "&";
      if (
        this.w_range != null &&
        this.w_range[0] != null &&
        this.w_range[1] != null
      ) {
        url +=
          "date_from=" +
          moment(this.w_range[0]).format("YYYY-MM-DD") +
          "&" +
          "date_to=" +
          moment(this.w_range[1]).format("YYYY-MM-DD") +
          "&";
      }
      this.$http
        .get(url + this.$root.params(this.table.query))
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
    selected_client: function (val) {
      if (val == null) {
        this.filters.client_id = 0;
      } else {
        this.filters.client_id = val.id;
      }
    },
    selected_client_contact: function (val) {
      if (val == null) {
        this.filters.client_contact_id = 0;
      } else {
        this.filters.client_contact_id = val.id;
      }
    },
    selected_service: function (val) {
      if (val == null) {
        this.filters.service_id = 0;
      } else {
        this.filters.service_id = val.id;
      }
    },
    selected_estimate: function (val) {
      if (val == null) {
        this.filters.estimate_id = 0;
      } else {
        this.filters.estimate_id = val.id;
      }
    },
    "table.query": {
      handler(query) {
        this.getResults();
      },
      deep: true,
    },
    "filters.client_id": function () {
      this.table.query.offset = 0;
      this.getResults();
    },
    "filters.client_contact_id": function () {
      this.table.query.offset = 0;
      this.getResults();
    },
    "filters.service_id": function () {
      this.table.query.offset = 0;
      this.getResults();
    },
    "filters.estimate_id": function () {
      this.table.query.offset = 0;
      this.getResults();
    },
    w_range: function () {
      this.table.query.offset = 0;
      this.getResults();
    },
  },
};
</script>