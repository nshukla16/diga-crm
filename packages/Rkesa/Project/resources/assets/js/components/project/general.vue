<style>
.remove-manufacturer-button {
  height: 34px;
  line-height: 34px;
  padding: 0 10px;
  float: right;
}
</style>

<template lang="pug">
.row
  section.col-12.col-md-4
    fieldset.form-group(:class="{ 'has-error': errors.has('name') }")
      label {{ $t('project.Name') }}
      input.form-control(
        name="name",
        v-validate="'required'",
        type="text",
        v-model="my_project.name",
        v-bind:data-vv-as="$t('project.Name').toLowerCase()"
      )
      h6.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
    fieldset.form-group
      label {{ $t('project.Type_of_project') }}
      select.form-control(
        style="display: inline-block; min-width: 150px; flex: 1",
        v-model="my_project.project_type_id"
      )
        option(
          v-for="type in types",
          v-bind:value="type.id",
          v-text="type.name"
        )
    fieldset.form-group(style="margin-top: 3px")
      label № {{ $t('project.Of_contract') }}:
      input.form-control(v-model="my_project.contract_number")
    fieldset.form-group(style="margin-top: -5px")
      label.mr-2 {{ $t('project.Contract_file') }}:
      file-uploader(
        :file_url="my_project.contract_file",
        :file_name="my_project.contract_filename",
        :editable="true",
        @remove="remove_contract_file",
        @finished="(arr) => { [my_project.contract_file, my_project.contract_filename] = arr; }"
      )
    fieldset.form-group
      label {{ $t('project.Type_of_contract') }}
      select.form-control(v-model="my_project.contract_type")
        option(:value="0") {{ $t('project.With_transition') }}
        option(:value="1") {{ $t('project.Direct') }}
    fieldset.form-group(style="margin-top: -5px")
      label.mr-2 {{ $t('project.calculation_of_direct_costs_file') }}:
      file-uploader(
        :file_url="my_project.calculation_of_direct_costs_file",
        :file_name="my_project.calculation_of_direct_costs_file_name",
        :editable="true",
        @remove="remove_calculation_of_direct_costs",
        @finished="(arr) => { [my_project.calculation_of_direct_costs_file, my_project.calculation_of_direct_costs_file_name] = arr; }"
      )
    fieldset.form-group(style="margin-top: -5px")
      label.mr-2 {{ $t('project.commercial_offer_file') }}:
      file-uploader(
        :file_url="my_project.commercial_offer_file",
        :file_name="my_project.commercial_offer_file_name",
        :editable="true",
        @remove="remove_commercial_offer",
        @finished="(arr) => { [my_project.commercial_offer_file, my_project.commercial_offer_file_name] = arr; }"
      )
    fieldset.form-group(style="margin-top: -5px")
      label.mr-2 {{ $t('project.offer_drawing_file') }}:
      file-uploader(
        :file_url="my_project.offer_drawing_file",
        :file_name="my_project.offer_drawing_file_name",
        :editable="true",
        @remove="remove_offer_drawing",
        @finished="(arr) => { [my_project.offer_drawing_file, my_project.offer_drawing_file_name] = arr; }"
      )
  section.col-12.col-md-4
    fieldset.form-group
      label {{ $t('project.Phased_deliveries') }}
      select.form-control(v-model="my_project.phased_deliveries")
        option(:value="1") {{ $t('project.Yes') }}
        option(:value="0") {{ $t('project.No') }}
    fieldset.form-group(v-if="my_project.phased_deliveries == '1'")
      label {{ $t('project.Number_of_specification') }}:
      input.form-control(v-model="my_project.specification_number")
    fieldset.form-group(v-if="my_project.phased_deliveries == '1'")
      label.mr-2 {{ $t('project.File_of_specification') }}:
      file-uploader(
        :file_url="my_project.specification_file",
        :file_name="my_project.specification_filename",
        :editable="true",
        @remove="remove_specification_file",
        @finished="(arr) => { [my_project.specification_file, my_project.specification_filename] = arr; }"
      )
    fieldset.form-group
      label {{ $t('project.Conditions_of_delivery') }}
      select.form-control(v-model="my_project.conditions_of_delivery")
        option(:value="i", v-for="(v, i) in conditions") {{ v }}
    fieldset.form-group
      label {{ $t('project.Destination_point') }}
      input.form-control(type="text", v-model="my_project.destination")
    fieldset.form-group
      label {{ $t('project.Seller') }}
      select.form-control(
        v-if="my_project.contract_type == 0",
        type="text",
        v-model="my_project.seller_legal_entity_id"
      )
        option(
          v-for="legal_entity in legal_entities",
          :value="legal_entity.id"
        ) {{ legal_entity.name }}
      input.form-control(
        v-else,
        disabled,
        :value="my_project.manufacturers.length > 0 ? my_project.manufacturers.map((e) => (e.manufacturer_select_option_object ? e.manufacturer_select_option_object.label : '')).join(', ') : $t('project.Choose_manufacturer')"
      )
    fieldset.form-group(v-if="my_project.contract_type == 1")
      label {{ $t('project.Commissioner') }}
      .mb-2(v-for="(commissioner, i) in my_project.commissioners")
        button.btn.btn-danger.ml-2.remove-manufacturer-button(
          v-on:click="remove_commissioner(commissioner)"
        )
          i.fa.fa-times
        div(style="margin-right: 43px")
          div(v-if="commissioner.is_new")
            select.form-control(v-model="commissioner.legal_entity_id")
              option(
                v-for="legal_entity in available_legal_entities_options(commissioner)",
                :value="legal_entity.id"
              ) {{ legal_entity.name }}
          div(v-else)
            input.form-control(
              v-model="legal_entities_by_id[commissioner.legal_entity_id].name",
              disabled
            )
      button.btn.btn-diga.w-100(
        v-if="my_project.commissioners.length !== legal_entities.length",
        v-on:click="add_commissioner"
      ) {{ $t('project.Add_commissioner') }}
      fieldset.form-group(
        :class="{ 'has-error': errors.has('no_commissioners') }"
      )
        span.help-block(v-show="errors.has('no_commissioners')") {{ errors.first('no_commissioners') }}
    fieldset.form-group
      label {{ $t('project.Manufacturer') }}
      div(v-for="(manufacturer, i) in my_project.manufacturers")
        .mb-2(:class="{ 'has-error': errors.has('manufacturer_' + i) }")
          button.btn.btn-danger.ml-2.remove-manufacturer-button(
            v-on:click="remove_manufacturer(manufacturer)"
          )
            i.fa.fa-times
          div(v-if="manufacturer.is_new", style="margin-right: 34px")
            v-select(
              :debounce="250",
              v-model="manufacturer.manufacturer_select_option_object",
              v-validate="'required'",
              v-bind:data-vv-as="$t('project.Manufacturer').toLowerCase()",
              :on-search="get_manufacturers_options",
              :on-change="(res) => { manufacturer_selected(res, manufacturer); }",
              :options="tmp_manufacturers",
              :placeholder="$t('project.Start_to_enter_company_name')",
              :name="'manufacturer_' + i"
            )
              template(slot="no-options") {{ $t('template.No_matching_options') }}
          div(v-else, style="margin-right: 43px")
            input.form-control(
              v-model="manufacturer.manufacturer_select_option_object.label",
              disabled
            )
          span.help-block(v-show="errors.has('manufacturer_'+i)") {{ errors.first('manufacturer_' + i) }}
      button.btn.btn-diga.w-100(v-on:click="add_manufacturer") {{ $t('project.Add_manufacturer') }}
      fieldset.form-group(
        :class="{ 'has-error': errors.has('no_manufacturers') }"
      )
        span.help-block(v-show="errors.has('no_manufacturers')") {{ errors.first('no_manufacturers') }}
  section.col-12.col-md-4
    fieldset.form-group(:class="{ 'has-error': errors.has('client_id') }")
      label {{ $t('project.Buyer') }}
      v-select(
        :debounce="250",
        v-model="my_project.selected_company_object",
        v-validate="'required'",
        v-bind:data-vv-as="$t('project.Buyer').toLowerCase()",
        :on-search="get_companies_options",
        :on-change="company_selected",
        :options="tmp_companies",
        :placeholder="$t('project.Start_to_enter_company_name')",
        name="client_id"
      )
        template(slot="no-options") {{ $t('template.No_matching_options') }}
      span.help-block(v-show="errors.has('client_id')") {{ errors.first('client_id') }}
    fieldset.form-group
      label {{ $t('project.Service_number') }}
      v-select(
        :debounce="250",
        v-model="my_project.selected_service_object",
        :on-search="get_services_options",
        :on-change="service_selected",
        :options="tmp_services",
        :placeholder="$t('project.Start_to_enter')"
      ) ---
        template(slot="no-options") {{ $t('template.No_matching_options') }}
    fieldset.form-group
      label {{ $t('project.Responsible') }}
      select.form-control(v-model="my_project.responsible_user_id")
        option(v-for="user in users", :value="user.id") {{ user.name }}
    fieldset.form-group
      label {{ $t('project.Lessee') }}
      v-select(
        :debounce="250",
        v-model="my_project.selected_lessee_company_object",
        :on-search="get_lessee_companies_options",
        :on-change="lessee_company_selected",
        :options="tmp_lessee_companies",
        :placeholder="$t('project.Start_to_enter_company_name')"
      )
        template(slot="no-options") {{ $t('template.No_matching_options') }}
    fieldset.form-group
      label(style="display: block") {{ $t('project.Price_of_the_contract') }}
      vue-numeric.form-control(
        style="display: inline-block; width: 70%",
        separator=",",
        v-bind:precision="2",
        type="text",
        v-model="my_project.contract_price"
      )
      select.form-control(
        style="display: inline-block; width: 30%",
        v-model="my_project.contract_currency"
      )
        option(v-for="currency in $root.currencies", :value="currency.code") {{ currency.name + ' (' + currency.code + ')' }}
    fieldset.form-group(
      v-if="my_project.contract_currency != $root.global_settings.currency"
    )
      label {{ $t('project.Type_of_payment') }}
      select.form-control(
        style="display: block",
        v-model="my_project.contract_currency_type"
      )
        option(:value="0") {{ $t('project.In_currency') }}
        option(:value="1") {{ $t('project.In_main_currency') + ' ' + $root.current_currency.code }}
    //- fieldset.form-group
    //-     label(style="display: block") {{ $t('project.Vat_difference') }}
    //-     vue-numeric.form-control(style="display: inline-block; width: 70%", separator=",", v-bind:precision="2", type="text" v-model="my_project.vat_difference") 
    //-     input.form-control-plaintext(type="text", style="display: inline-block; width: 30%; padding-left: 10px;", readonly, v-model="my_project.contract_currency")
    h6 {{ $t('project.Contract_date') }}
    date-picker(
      format="DD.MM.YYYY",
      v-model="my_project.date_of_sign_contract",
      :value-type="$root.valueType",
      :lang="$root.locale",
      :first-day-of-week="$root.global_settings.first_day_of_week",
      :width="'100%'"
    )
</template>

<script>
import { mapGetters } from "vuex";
import { project_conditions_of_delivery } from "../../helper";

export default {
  inject: ["$validator"],
  props: ["project"],
  data() {
    return {
      my_project: this.project,
      tmp_companies: [],
      tmp_lessee_companies: [],
      tmp_manufacturers: [],
      tmp_services: [],
    };
  },
  computed: {
    ...mapGetters({
      types: "getProjectTypes",
      legal_entities: "getLegalEntities",
      users: "getNotRemovedUsers",
      legal_entities_by_id: "getLegalEntitiesById",
    }),
    available_legal_entities_ids() {
      let all_disabled_ids = this.my_project.commissioners.map(
        (r) => r.legal_entity_id
      );
      return this.legal_entities.filter(
        (le) => all_disabled_ids.indexOf(le.id) === -1
      );
    },
    conditions() {
      return project_conditions_of_delivery;
    },
  },
  created() {
    // Vue.set(this.my_project, 'removed_manufacturers', []);
    if (this.my_project.id === 0) {
      Vue.set(this.my_project, "commissioners", []);
    } else {
      Vue.set(
        this.my_project,
        "commissioners",
        JSON.parse(
          JSON.stringify(this.my_project.manufacturers[0].commission_relations)
        )
      );
    }
  },
  methods: {
    available_legal_entities_options(commissioner) {
      let all_disabled_ids = this.my_project.commissioners.map(
        (r) => r.legal_entity_id
      );
      return this.legal_entities
        .filter((le) => all_disabled_ids.indexOf(le.id) === -1)
        .concat(this.legal_entities_by_id[commissioner.legal_entity_id]);
    },
    add_commissioner() {
      let commissioner = {
        id: null,
        legal_entity_id: this.available_legal_entities_ids[0].id,
        is_new: true,
      };
      this.my_project.commissioners.push(commissioner);
    },
    remove_commissioner(commissioner) {
      if (
        confirm(
          this.$root.$t("project.Are_you_sure_you_want_to_delete_commissioner")
        )
      ) {
        if (!("removed_commissioners" in this.my_project)) {
          this.my_project.removed_commissioners = [];
        }
        if (!commissioner.is_new) {
          this.my_project.removed_commissioners.push(
            commissioner.legal_entity_id
          );
        }
        let index = this.my_project.commissioners.indexOf(commissioner);
        this.my_project.commissioners.splice(index, 1);

        this.my_project.manufacturers.forEach((m) => {
          if (m.commission_relations) {
            let i = m.commission_relations.find(
              (r) => r.legal_entity_id == commissioner.legal_entity_id
            );
            m.commission_relations.splice(i, 1);
          }
        });
      }
    },
    get_manufacturers_options(search, loading) {
      loading(true);
      this.$http
        .get("/api/manufacturers?query=" + search + "&limit=100&fields=id,name")
        .then(
          (res) => {
            var processedData = [];
            res.data.rows.forEach(function (i) {
              processedData.push({ label: i.name, value: i.id });
            });
            this.tmp_manufacturers = processedData;
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
    manufacturer_selected(val, manufacturer) {
      if (val !== null) {
        manufacturer.manufacturer_select_option_object = val;
        manufacturer.id = val.value;
      } else {
        manufacturer.manufacturer_select_option_object = null;
        manufacturer.id = null;
      }
    },
    add_manufacturer() {
      let manufacturer = {
        id: null,
        is_new: true,
        manufacturer_select_option_object: null,
      };
      this.my_project.manufacturers.push(manufacturer);
    },
    remove_manufacturer(manufacturer) {
      if (
        confirm(
          this.$root.$t("project.Are_you_sure_you_want_to_delete_manufacturer")
        )
      ) {
        if (!("removed_manufacturers" in this.my_project)) {
          this.my_project.removed_manufacturers = [];
        }
        if (!manufacturer.is_new) {
          this.my_project.removed_manufacturers.push(manufacturer.id);
        }
        let index = this.my_project.manufacturers.indexOf(manufacturer);
        this.my_project.manufacturers.splice(index, 1);
      }
    },
    get_companies_options(search, loading) {
      loading(true);
      this.$http
        .get("/api/companies?query=" + search + "&limit=100&fields=id,name")
        .then(
          (res) => {
            var processedData = [];
            res.data.rows.forEach(function (i) {
              processedData.push({ label: i.name, value: i.id });
            });
            this.tmp_companies = processedData;
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
    company_selected(val) {
      if (val !== null) {
        this.my_project.selected_company_object = val;
        this.my_project.client_id = val.value;
      } else {
        this.my_project.selected_company_object = null;
        this.my_project.client_id = null;
      }
    },
    get_lessee_companies_options(search, loading) {
      loading(true);
      this.$http
        .get("/api/companies?query=" + search + "&limit=100&fields=id,name")
        .then(
          (res) => {
            var processedData = [];
            res.data.rows.forEach(function (i) {
              processedData.push({ label: i.name, value: i.id });
            });
            this.tmp_lessee_companies = processedData;
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
    lessee_company_selected(val) {
      if (val !== null) {
        this.my_project.selected_lessee_company_object = val;
        this.my_project.lessee_client_id = val.value;
      } else {
        this.my_project.selected_lessee_company_object = null;
        this.my_project.lessee_client_id = null;
      }
    },
    remove_contract_file() {
      if (
        confirm(
          this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document")
        )
      ) {
        this.my_project.contract_file = null;
        this.my_project.contract_filename = null;
      }
    },
    remove_specification_file() {
      if (
        confirm(
          this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document")
        )
      ) {
        this.my_project.specification_file = null;
        this.my_project.specification_filename = null;
      }
    },
    remove_calculation_of_direct_costs() {
      if (
        confirm(
          this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document")
        )
      ) {
        this.my_project.calculation_of_direct_costs_file = null;
        this.my_project.calculation_of_direct_costs_file_name = null;
      }
    },
    remove_commercial_offer() {
      if (
        confirm(
          this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document")
        )
      ) {
        this.my_project.commercial_offer_file = null;
        this.my_project.commercial_offer_file_name = null;
      }
    },
    remove_offer_drawing() {
      if (
        confirm(
          this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document")
        )
      ) {
        this.my_project.offer_drawing_file = null;
        this.my_project.offer_drawing_file_name = null;
      }
    },
    // service
    get_services_options(search, loading) {
      loading(true);
      let $this = this;
      this.$http.get("/api/services?query=" + search + "&limit=100").then(
        (res) => {
          var processedData = [];
          res.data.rows.forEach(function (i) {
            processedData.push({
              label: $this.$root.service_number(i),
              value: i.id,
            });
          });
          this.tmp_services = processedData;
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
    service_selected(val) {
      if (val !== null) {
        this.my_project.selected_service_object = val;
        this.my_project.service_id = val.value;
      } else {
        this.my_project.selected_service_object = null;
        this.my_project.service_id = null;
      }
    },
  },
};
</script>