<style>
</style>

<template lang="pug">
div
  h2 {{ $t('template.All_companies') }}
  section.diga-container.p-4
    datatable.datatable-wrapper(v-bind="table")
      input.form-control(
        v-model="query",
        type="text",
        :placeholder="$t('client.Search')",
        style="height: 38px; min-width: 150px; display: inline-block; flex: 1; margin: 0 10px 0"
      )
      select.form-control(
        v-model="filters.referrer_id",
        style="display: inline-block; min-width: 150px; flex: 1; margin: 0 10px 0"
      )
        option(value="0") {{ $t('client.All') }}
        option(v-for="referrer in referrers", :value="referrer.id") {{ referrer.title }}
      router-link.btn.btn-diga(
        style="height: 38px; margin: 0 10px 0",
        v-if="$root.can_do('clients', 'create') == 1",
        :to="{ name: 'company_create' }"
      ) {{ $t('client.New_company') }}
</template>

<script>
import created_at_column from "./custom_columns/td_created_at.vue";
import company_column from "./custom_columns/td_company.vue";
import contacts_column from "./custom_columns/td_contacts.vue";
import services_column from "./custom_columns/td_services.vue";
import actions_column from "./custom_columns/td_actions.vue";
import referrer_column from "./custom_columns/td_referrer.vue";

import { mapGetters } from "vuex";

export default {
  data: function () {
    return {
      query: "",
      table: {
        columns: [
          { title: "#", field: "id", sortable: true },
          {
            title: this.$root.$t("client.Date"),
            field: "created_at",
            tdComp: created_at_column,
            sortable: true,
          },
          {
            title: this.$root.$t("client.Client_name"),
            field: "name",
            tdComp: company_column,
            sortable: true,
          },
          {
            title: this.$root.$t("client.Client_group"),
            field: "client_group",
            sortable: true,
          },
          { title: this.$root.$t("client.Contacts"), tdComp: contacts_column },
          {
            title: this.$root.$t("client.Service"),
            tdComp: services_column,
            tdClass: "service_states",
          },
          {
            title: this.$root.$t("client.Email"),
            field: "email",
            sortable: true,
          },
          {
            title: this.$root.$t("client.Phones"),
            field: "phone",
            sortable: true,
          },
          {
            title: this.$root.$t("client.Site"),
            field: "site",
            sortable: true,
            visible: false,
          },
          { title: this.$root.$t("client.Referrer"), tdComp: referrer_column },
          {
            title: this.$root.$t("template.Actions"),
            tdClass: "column_autosize",
            tdComp: actions_column,
            visible: this.$root.can_do("clients", "delete") != 0,
          },
        ],
        data: [],
        total: 0,
        query: {
          offset: this.offset || 0,
        },
      },
      filters: {
        referrer_id: this.referrer || 0,
      },
      // timer_id: null,
      // current_url: ''
    };
  },
  props: ["offset", "referrer"],
  methods: {
    isEmpty(str) {
      return !str || str.length === 0;
    },
    getResults() {
      // if(this.timer_id != null){
      //     clearTimeout(this.timer_id);
      //     this.timer_id = null;
      // }
      // this.current_url = ;
      this.$http
        .get(
          "/api/companies?" +
            this.$root.params(this.table.query) +
            "&referrer_id=" +
            this.filters.referrer_id +
            (this.query != "" ? "&query=" + this.query : "")
        )
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
              // save service link to estimates
              this.table.data.forEach(function (client) {
                client.client_contacts.forEach(function (contact) {
                  contact.services.forEach(function (service) {
                    service.estimates.forEach(function (
                      estimate,
                      k,
                      estimates
                    ) {
                      estimates[k].service = service;
                    });
                  });
                });
              });
              // this.timer_id = setTimeout(this.save_url, 600);
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
    // save_url(){
    //                window.history.pushState("string", null, this.current_url);
    //             },
    //            get_estimate_numbers(client){
    //                // array of arrays to array - [].concat.apply([], array)
    //                let $this = this;
    //                return ([].concat.apply([], client.services.map(function(service){
    //                    return service.estimates;
    //                }))).map(function(estimate){
    //                    return $this.$root.estimate_number(estimate);
    //                }).concat(client.services.filter(function(service){
    //                    return service.estimates.length === 0;
    //                }).map(function(service){
    //                    return $this.$root.service_number(service);
    //                })).join(', ');
    //            },
    //            get_service_states(client){
    //                let $this = this;
    //                return client.services.map(function (service) {
    //                    return $this.get_visual_state(service);
    //                }).join(', ');
    //            },

    get_services_pay_info(client) {
      let $this = this;
      return client.services
        .map(function (service) {
          return $this.get_service_pay_info(service);
        })
        .join(", ");
    },
    get_services_desc(client) {
      let $this = this;
      return client.services
        .filter(function (service) {
          return service.note != null && service.note.trim != "";
        })
        .map(function (service) {
          return (
            '<span style="background-color: #4080c6;">' +
            service.note +
            "</span>"
          );
        })
        .join(", ");
    },
    get_service_pay_info(service) {
      if (
        service.paid_summ != null &&
        service.paid_summ == service.estimate_summ
      ) {
        return (
          '<span style="background-color: #60c657;">' +
          this.$root.$t("client.Paid_full") +
          "</span>"
        );
      } else if (
        service.paid_summ != null &&
        service.paid_summ != 0 &&
        service.paid_summ != service.estimate_summ
      ) {
        return (
          '<span style="background-color: #eeb240;">' +
          this.$root.$t("client.Paid_not_full") +
          "</span>"
        );
      } else {
        return (
          '<span style="background-color: #ee1334;">' +
          this.$root.$t("client.Not_paid") +
          "</span>"
        );
      }
    },
  },
  watch: {
    "table.query": {
      handler(query) {
        this.getResults();
      },
      deep: true,
    },
    query: function () {
      this.table.query.offset = 0;
      this.getResults();
    },
    "filters.referrer_id": function () {
      this.table.query.offset = 0;
      this.getResults();
    },
  },
  computed: {
    ...mapGetters({
      referrers: "getClientReferrers",
    }),
  },
  mounted() {
    document.title =
      this.$root.global_settings.site_name +
      " | " +
      this.$root.$t("template.All_companies");
    this.$bus.$on("get_results", this.getResults);
  },
  beforeDestroy: function () {
    this.getResults && this.$bus.$off("get_results", this.getResults);
  },
};
</script>