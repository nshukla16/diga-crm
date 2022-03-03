<style>
</style>

<template lang="pug">
.row
  section.col-12.col-md-4
    .d-flex
      span.text-muted {{ $t('project.Name') }}
      span.dotter
      span.text-right {{ project.name }}
    .d-flex
      span.text-muted {{ $t('project.Type_of_project') }}
      span.dotter
      span.text-right {{ typesById[project.project_type_id].name }}
    .d-flex
      span.text-muted № {{ $t('project.Of_contract') }}
      span.dotter
      span.text-right {{ project.contract_number }}
    .d-flex
      span.text-muted {{ $t('project.Contract_file') }}
      span.dotter
      a.text-right(:href="project.contract_file", target="_blank") {{ project.contract_filename }}
    .d-flex
      span.text-muted {{ $t('project.calculation_of_direct_costs_file') }}
      span.dotter
      a.text-right(
        :href="project.calculation_of_direct_costs_file",
        target="_blank"
      ) {{ project.calculation_of_direct_costs_file_name }}
    .d-flex
      span.text-muted {{ $t('project.commercial_offer_file') }}
      span.dotter
      a.text-right(:href="project.commercial_offer_file", target="_blank") {{ project.commercial_offer_file_name }}
    .d-flex
      span.text-muted {{ $t('project.offer_drawing_file') }}
      span.dotter
      a.text-right(:href="project.offer_drawing_file", target="_blank") {{ project.offer_drawing_file_name }}
    .d-flex
      span.text-muted {{ $t('project.Type_of_contract') }}
      span.dotter
      span.text-right {{ get_contract_type(project.contract_type) }}
    .d-flex
      span.text-muted {{ $t('project.Phased_deliveries') }}
      span.dotter
      span.text-right {{ get_phased_deliveries(project.phased_deliveries) }}
    .d-flex(v-if="project.phased_deliveries == '1'")
      span.text-muted {{ $t('project.Number_of_specification') }}
      span.dotter
      span.text-right {{ project.specification_number }}
    .d-flex(v-if="project.phased_deliveries == '1'")
      span.text-muted {{ $t('project.File_of_specification') }}
      span.dotter
      a.text-right(:href="project.specification_file", target="_blank") {{ project.specification_filename }}
    .d-flex
      span.text-muted {{ $t('project.Conditions_of_delivery') }}
      span.dotter
      span.text-right {{ get_conditions_of_delivery(project.conditions_of_delivery) }}
    .d-flex
      span.text-muted {{ $t('project.Destination_point') }}
      span.dotter
      span.text-right {{ project.destination }}
  section.col-12.col-md-4
    .d-flex
      span.text-muted {{ $t('project.Seller') }}
      span.dotter
      span.text-right(v-if="project.contract_type == 0")
        template
          router-link(
            :to="{ name: 'legal_entities', params: { id: legal_entities_by_id[project.seller_legal_entity_id].id } }"
          ) {{ legal_entities_by_id[project.seller_legal_entity_id].name }}
      span.text-right(v-else) {{ project.manufacturers.map((e) => e.manufacturer_select_option_object.label).join(', ') }}
    .d-flex(v-if="project.contract_type == 1")
      span.text-muted {{ $t('project.Commissioner') }}
      span.dotter
      span.text-right
        router-link(
          :to="{ name: 'legal_entities', params: { id: legal_entities_by_id[project.seller_legal_entity_id].id } }"
        ) {{ project.manufacturers[0].commission_relations.map((r) => legal_entities_by_id[r.legal_entity_id].name).join(', ') }}
    .d-flex
      span.text-muted {{ $t('project.Manufacturer') }}
      span.dotter
      span.text-right
        template(v-for="(manufacturer, i) in project.manufacturers")
          router-link(
            :to="{ name: 'manufacturer_show', params: { id: manufacturer.manufacturer.id } }"
          ) {{ manufacturer.manufacturer_select_option_object.label }}
          | {{ project.manufacturers.length != i + 1 ? ', ' : '' }}
    .d-flex
      span.text-muted {{ $t('project.Buyer') }}
      span.dotter
      span.text-right
        router-link(
          :to="{ name: 'company_show', params: { id: project.client_id } }"
        ) {{ project.selected_company_object.label }}
    .d-flex
      span.text-muted {{ $t('project.Service_number') }}
      span.dotter
      span.text-right(v-if="project.service_id")
        router-link(
          :to="{ name: 'contact_show', query: { service_id: project.service_id }, params: { id: project.selected_service_object.client_contact_id } }"
        ) {{ project.selected_service_object.label }}
    .d-flex
      span.text-muted {{ $t('project.Responsible') }}
      span.dotter
      span.text-right {{ usersById[project.responsible_user_id].name }}
    .d-flex
      span.text-muted {{ $t('project.Lessee') }}
      span.dotter
      span.text-right(v-if="project.lessee_client_id")
        router-link(
          :to="{ name: 'company_show', params: { id: project.lessee_client_id } }"
        ) {{ project.selected_lessee_company_object.label }}
    .d-flex
      span.text-muted {{ $t('project.Price_of_the_contract') }}
      span.dotter
      span.text-right {{ $root.formatFinanceValue(project.contract_price) + ' ' + get_currency_format($root.currencies[project.contract_currency]) }}
    .d-flex(v-if="project.contract_currency != $root.global_settings.currency")
      span.text-muted {{ $t('project.Type_of_payment') }}
      span.dotter
      span.text-right {{ get_contract_currency_type(project.contract_currency_type) }}
    .d-flex
      span.text-muted {{ $t('project.Contract_date') }}
      span.dotter
      span.text-right {{ project.date_of_sign_contract }}
  section.col-12.col-md-4
    .d-flex
      span.text-muted {{ $t('project.Total') }}
      span.dotter
      span.text-right {{ total_format }}
    .d-flex
      span.text-muted {{ $t('project.Vat_20_percent') }}
      span.dotter
      span.text-right {{ vat_from_total }}
    .d-flex
      span.text-muted {{ $t('project.Manufacturer_expenses') }}
      span.dotter
      span.text-right {{ manufacturer_payments }}
    .d-flex
      span.text-muted {{ $t('project.Expenses') }}
      popper.ml-1(:append-to-body="true")
        .popper {{ $t('project.Transportation_expenses_plus_installation_and_service') }}
        i.fa.fa-question-circle-o(slot="reference")
      span.dotter
      span.text-right {{ expenses_format }}
    .d-flex
      span.text-muted {{ $t('project.Transportation_vat_expenses') }}
      span.dotter
      span.text-right {{ vat_expenses }}
    .d-flex
      span.text-muted {{ $t('project.Gross_profit') }}
      span.dotter
      span.text-right {{ gross_profit }}
    .d-flex
      span.text-muted {{ $t('project.Vat_difference') }}
      span.dotter
      //- span.text-right {{ $root.formatFinanceValue(project.vat_difference) +' '+ get_currency_format($root.currencies[project.contract_currency]) }}    
      span.text-right {{ vat_difference }}
    .d-flex
      span.text-muted {{ $t('project.Gross_profit_without_VAT') }}
      span.dotter
      span.text-right {{ gross_profit_without_VAT }}
    .d-flex
      span.text-muted {{ $t('project.Expenses_on_guarantee') }}
      span.dotter
      span.text-right {{ guarantee_expenses }}
    //- div.d-flex
    //-     span.text-muted {{ $t('project.Markup') }}
    //-     span.dotter
    //-     span.text-right {{ markup }}
    //- div.d-flex
    //-     span.text-muted {{ $t('project.Profitability') }}
    //-     span.dotter
    //-     span.text-right {{ profitability }}
    .d-flex
      span.text-muted {{ $t('project.Shipping_date') }}
      span.dotter
      span.text-right {{ shipping_date_format }}
    .d-flex
      span.text-muted {{ $t('project.Shipping_date') }} ({{ $t('project.fact') }})
      span.dotter
      span.text-right {{ fact_shipping_date_format }}
    // from "common" tab, block "limit"
    //- div.d-flex
    //-     span.text-muted {{ $t('project.Delivery_date') }}
    //-     popper.ml-1(:append-to-body="true")
    //-         div.popper {{ $t('project.From_limit_block') }}
    //-         i.fa.fa-question-circle-o(slot="reference")
    //-     span.dotter
    //-     span.text-right {{ delivery_date_format }}
    // direct contract type
    template(
      v-for="fact_entity in project.fact_delivery_entities",
      v-if="project.contract_type == 1"
    )
      .d-flex
        span.text-muted {{ $t('project.Delivery_date') }} ({{ $t('project.fact') }})
        popper.ml-1(:append-to-body="true")
          .popper {{ $t('project.From_limit_block') }}
          i.fa.fa-question-circle-o(slot="reference")
        span.dotter
        span.text-right {{ fact_entity.date ? format_date(fact_entity.date) : $t('template.Unknown') }}
    //  with transition (if has a logistics block)
    template(
      v-for="man in project.manufacturers",
      v-if="project.logistics_enabled"
    )
      template(v-for="order in man.orders")
        .d-flex
          span.text-muted {{ $t('project.Delivery_date') }} ({{ $t('project.fact') }})
          popper.ml-1(:append-to-body="true")
            .popper {{ $t('project.Manufacturer_order') }}: {{ order.name }}
            i.fa.fa-question-circle-o(slot="reference")
          span.dotter
          span.text-right {{ order.fact_delivery && order.fact_delivery_date ? format_date(order.fact_delivery_date) : $t('template.Unknown') }}
    // from "common" tab, block "documents"
    .d-flex
      span.text-muted {{ $t('project.Delivery_date') }} ({{ $t('project.fact') }})
      popper.ml-1(:append-to-body="true")
        .popper {{ $t('project.Acceptance_certificate_date') }}
        i.fa.fa-question-circle-o(slot="reference")
      span.dotter
      span.text-right {{ fact_delivery_date_format }}
    .d-flex
      span.text-muted {{ $t('project.Entry_date') }} ({{ $t('project.fact') }})
      span.dotter
      span.text-right {{ entry_date_format }}
</template>

<script>
import { mapGetters } from "vuex";
import moment from "moment";
import { project_conditions_of_delivery } from "../../helper";

export default {
  inject: ["$validator"],
  props: {
    project: Object,
  },
  data() {
    return {
      contract_file_loading: false,
      specification_file_loading: false,
      tmp_companies: [],
      tmp_lessee_companies: [],
      tmp_manufacturers: [],
    };
  },
  computed: {
    ...mapGetters({
      types: "getProjectTypes",
      typesById: "getProjectTypesById",
      legal_entities: "getLegalEntities",
      legal_entities_by_id: "getLegalEntitiesById",
      usersById: "getUsersById",
    }),
    shipping_date_format() {
      return this.project.manufacturers
        .map((m) => {
          if (m.limit_forming_type == 0) {
            if (m.limit_forming_date == 0) {
              if (m.payments[0] && m.payments[0].payment_date) {
                return moment(m.payments[0].payment_date)
                  .add(m.limit_forming_days, "days")
                  .format("DD.MM.YYYY");
              } else {
                return this.$root.$t("template.Unknown");
              }
            } else {
              return moment(m.order_confirmed_at)
                .add(m.limit_forming_days, "days")
                .format("DD.MM.YYYY");
            }
          } else if (m.limit_before_date) {
            return moment(m.limit_before_date).format("DD.MM.YYYY");
          } else {
            return this.$root.$t("template.Unknown");
          }
        })
        .join(", ");
    },
    fact_shipping_date_format() {
      return this.project.manufacturers
        .map((m) => {
          if (m.fact_shipping_date != null) {
            return moment(m.fact_shipping_date).format("DD.MM.YYYY");
          } else {
            return this.$root.$t("template.Unknown");
          }
        })
        .join(", ");
    },
    delivery_date_format() {
      if (this.project.limit_type == 0) {
        if (this.project.limit_forming_type == 0) {
          if (this.project.limit_forming_date == 0) {
            if (
              this.project.contract_payments[0] &&
              this.project.contract_payments[0].payment_date
            ) {
              return moment(this.project.contract_payments[0].payment_date)
                .add(this.project.limit_forming_days, "days")
                .format("DD.MM.YYYY");
            } else {
              return this.$root.$t("template.Unknown");
            }
          } else if (this.project.date_of_sign_contract) {
            return moment(this.project.date_of_sign_contract)
              .add(this.project.limit_forming_days, "days")
              .format("DD.MM.YYYY");
          } else {
            return this.$root.$t("template.Unknown");
          }
        } else if (this.project.limit_before_date) {
          return moment(this.project.limit_before_date).format("DD.MM.YYYY");
        } else {
          return this.$root.$t("template.Unknown");
        }
      } else {
        return this.$root.$t("template.Unknown");
      }
    },
    fact_delivery_date_format() {
      if (this.project.acceptance_certificate_date != null) {
        return moment(this.project.acceptance_certificate_date).format(
          "DD.MM.YYYY"
        );
      } else {
        return this.$root.$t("template.Unknown");
      }
    },
    entry_date_format() {
      if (this.project.equipment_certificate_date != null) {
        return moment(this.project.equipment_certificate_date).format(
          "DD.MM.YYYY"
        );
      } else {
        return this.$root.$t("template.Unknown");
      }
    },
    manufacturer_payments_tmp() {
      return this.project.manufacturers
        .map((m) =>
          m.payments_total_price != null
            ? parseFloat(m.payments_total_price)
            : 0
        )
        .reduce((a, b) => a + b, 0);
    },
    manufacturer_payments() {
      return (
        this.$root.formatFinanceValue(this.manufacturer_payments_tmp) +
        " " +
        this.get_currency_format(
          this.$root.currencies[this.$root.global_settings.currency]
        )
      );
    },
    expenses_format_tmp() {
      return (
        parseFloat(this.project.transportation_total) +
        parseFloat(this.project.installation_total_price) +
        parseFloat(this.additional_expenses_tmp)
      );
      /*+ this.project.side_payments.map(p => parseFloat(p.in_main_currency)).reduce((a, b) => a + b, 0)*/
    },
    expenses_format() {
      return (
        this.$root.formatFinanceValue(this.expenses_format_tmp) +
        " " +
        this.get_currency_format(
          this.$root.currencies[this.$root.global_settings.currency]
        )
      );
    },
    total_format_tmp() {
      return parseFloat(this.project.contract_total_price);
    },
    total_format() {
      return (
        this.$root.formatFinanceValue(this.total_format_tmp) +
        " " +
        this.get_currency_format(
          this.$root.currencies[this.$root.global_settings.currency]
        )
      );
    },
    gross_profit_tmp() {
      // return this.total_format_tmp - this.expenses_format_tmp - this.manufacturer_payments_tmp;
      return (
        this.total_format_tmp -
        this.expenses_format_tmp -
        this.manufacturer_payments_tmp -
        this.project.transportation_vat_total
      );
    },
    gross_profit() {
      return (
        this.$root.formatFinanceValue(this.gross_profit_tmp) +
        " " +
        this.get_currency_format(
          this.$root.currencies[this.$root.global_settings.currency]
        )
      );
    },
    markup_tmp() {
      return this.gross_profit_tmp / this.manufacturer_payments_tmp;
    },
    markup() {
      if (this.manufacturer_payments_tmp != 0) {
        return (
          this.$root.formatFinanceValue(this.markup_tmp) +
          " " +
          this.get_currency_format(
            this.$root.currencies[this.$root.global_settings.currency]
          )
        );
      } else {
        return this.$root.$t("template.Unknown");
      }
    },
    profitability_tmp() {
      return this.gross_profit_tmp / this.total_format_tmp;
    },
    profitability() {
      if (this.total_format_tmp != 0) {
        return (
          this.$root.formatFinanceValue(this.profitability_tmp) +
          " " +
          this.get_currency_format(
            this.$root.currencies[this.$root.global_settings.currency]
          )
        );
      } else {
        return this.$root.$t("template.Unknown");
      }
    },
    gross_profit_without_VAT() {
      // return this.$root.formatFinanceValue(this.gross_profit_tmp - (this.total_format_tmp / 6 - this.project.transportation_vat_total)) + ' ' + this.get_currency_format(this.$root.currencies[this.$root.global_settings.currency]);
      return (
        this.$root.formatFinanceValue(
          this.total_format_tmp -
            this.expenses_format_tmp -
            this.manufacturer_payments_tmp -
            this.total_format_tmp / 6
        ) +
        " " +
        this.get_currency_format(
          this.$root.currencies[this.$root.global_settings.currency]
        )
      );
    },
    vat_from_total() {
      return (
        this.$root.formatFinanceValue(this.total_format_tmp / 6) +
        " " +
        this.get_currency_format(
          this.$root.currencies[this.$root.global_settings.currency]
        )
      );
    },
    guarantee_expenses() {
      return (
        this.$root.formatFinanceValue(
          this.project.side_payments
            .map((p) => parseFloat(p.in_main_currency))
            .reduce((a, b) => a + b, 0)
        ) +
        " " +
        this.get_currency_format(
          this.$root.currencies[this.$root.global_settings.currency]
        )
      );
    },
    additional_expenses_tmp() {
      return this.project.additional_expenses
        .map((p) => parseFloat(p.in_main_currency))
        .reduce((a, b) => a + b, 0);
    },
    vat_expenses() {
      return (
        this.$root.formatFinanceValue(this.project.transportation_vat_total) +
        " " +
        this.get_currency_format(
          this.$root.currencies[this.$root.global_settings.currency]
        )
      );
    },
    vat_difference() {
      return (
        this.$root.formatFinanceValue(
          this.total_format_tmp / 6 - this.project.transportation_vat_total
        ) +
        " " +
        this.get_currency_format(
          this.$root.currencies[this.$root.global_settings.currency]
        )
      );
    },
  },
  methods: {
    get_contract_type(val) {
      if (val == 0) return this.$root.$t("project.With_transition");
      if (val == 1) return this.$root.$t("project.Direct");
    },
    get_phased_deliveries(val) {
      if (val == 0) return this.$root.$t("project.No");
      if (val == 1) return this.$root.$t("project.Yes");
    },
    get_conditions_of_delivery(val) {
      return project_conditions_of_delivery[val];
    },
    get_contract_currency_type(val) {
      if (val == 0) return this.$root.$t("project.In_currency");
      if (val == 1)
        return (
          this.$root.$t("project.In_main_currency") +
          " " +
          this.$root.current_currency.code
        );
    },
    get_currency_format(currency) {
      //                return currency.name + ' (' + currency.code + ')';
      return currency.code;
    },
    format_date(date) {
      return moment(date).format("DD.MM.YYYY");
    },
  },
};
</script>