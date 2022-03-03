/* eslint-disable indent */
<style>
.borderless td,
.borderless th {
  border: none;
}
table.table.table-condensed {
  border: 1px solid black;
}
.table-condensed td,
.table-condensed th {
  border: none;
}
</style>

<template lang="pug">
div
  email(
    v-if="$root.module_enabled('email')",
    :email_link="email_link",
    :redirect_to="redirect_to"
  )
  new-series(:invoice_series="invoice_series_list" :document_type_id="invoice_document_type_id" @get_invoice_series="get_invoice_series" @update_invoice_series_id="update_invoice_series_id")
  .d-flex
    h2 {{ $t('template.invoices') }}
      template(v-if="id === undefined") | {{ $t('template.Create') }}
      template(v-else) | {{ $t('template.Edit') }}
    select.form-control.mx-3(
      style="width: unset !important",
      v-model="invoice_document_type_id"
    )
      option(v-for="idt in invoice_document_types", :value="idt.id") {{ idt.name }}
    select.form-control(
      style="width: unset !important"
      v-model="invoice_series_id"
    )
      option(v-for="serie in invoice_series_list" :value="serie.id") {{ serie.name }}
    button.btn-diga.mx-3.mb-3(@click="show_add_series") {{ $t('template.create_series') }}
  section.diga-container.p-4
    .row.mb-3
      .col-md-5
        img#site_logo.logo-default(:src="$root.logo", alt="logo", height="100")
    .row
      .col-md-6
        p
          b
            span {{ settings.name }}
            span
              router-link.ml-3(
                :to="{ name: 'company_information' }",
                style="font-size: 20px"
              )
                i.fa.fa-pencil
        p CRC: {{ settings.crc }} № {{ settings.crc_number }}
        p {{ $t('template.Company_capital') }} {{ settings.capital }}
        p
          b {{ $t('template.Company_tax_number') }} № : {{ settings.tax_number }}
        p {{ settings.address }}
        p {{ settings.postal_code }} {{ settings.city }}
        p {{ $t('calendar.Phone') }} : {{ settings.phone }}, {{ $t('template.Company_fax') }} : {{ settings.fax }}
        p {{ settings.email }}
        p {{ settings.web_site }}
      .col-md-6
        template(
          v-if="invoice_document_type && ['GT', 'GR'].includes(invoice_document_type.code)"
        )
          fieldset.form-group.mt-4
            label.control-label {{ $t('template.is_valued') }}
            input(
              type="checkbox",
              v-model="is_valued",
              style="vertical-align: middle; margin: 0; margin-left: 10px"
            )
        //- template(
        //-   v-if="invoice_document_type && ['ND', 'NC', 'GT', 'GR', 'NE'].includes(invoice_document_type.code)"
        //- )
        template(v-if="invoice_document_type && ['FT', 'FR', 'ND', 'NC'].includes(invoice_document_type.code)")
          label {{ $t('template.Parent_invoice') }}
          v-select(
            style="max-width: 50%",
            v-model="selected_parent_invoice",
            :debounce="250",
            :on-search="lookupInvoice",
            :options="parent_invoices",
            label="invoice_no",
            :placeholder="$t('template.invoices')",
            :style="highlight_parent_invoice ? 'border: 1px solid #dc3545;' : 'border: none;'"
          )
            template(slot="no-options") {{ $t('template.No_matching_options') }}
          input.form-control.my-2(
            v-if="invoice_document_type && ['ND', 'NC'].includes(invoice_document_type.code)",
            type="text",
            v-model="correction_reason",
            :placeholder="$t('client.Reason')",
            style="max-width: 50%",
            :class="{ 'is-invalid': highlight_parent_invoice === true }"
          )

        div(v-if="$root.enable_companies" class="d-flex")
          v-select.mt-3(            
            style="width: 50%",
            v-model="selected_client",
            :debounce="250",
            :on-search="lookupClient",
            :options="clients",
            label="name",
            :placeholder="$t('service.Company_name')",
            :style="errors.has('client') ? 'border: 1px solid #dc3545;' : 'border: none;'",
            name="client",
            v-validate="{ required: this.selected_client_contact === null }"
          )
            template(slot="no-options") {{ $t('template.No_matching_options') }}
          router-link.ml-3.mt-3(
            v-if="selected_client"
            :to="{ name: 'company_edit', params: {id: selected_client.id} }",
            style="font-size: 20px"
          )
            i.fa.fa-pencil

        div(class="d-flex")
          v-select.mt-3(
            style="width: 50%",
            v-model="selected_client_contact",
            :debounce="250",
            :on-search="lookupClientContact",
            :options="client_contacts",
            label="fullName",
            :placeholder="$t('calendar.Contact')",
            :style="errors.has('client_contact') ? 'border: 1px solid #dc3545;' : 'border: none;'",
            name="client_contact",
            v-validate="{ required: this.selected_client === null }"
          )
            template(slot="no-options") {{ $t('template.No_matching_options') }}
          router-link.ml-3.mt-3(
            v-if="selected_client_contact"
            :to="{ name: 'contact_edit', params: {id: selected_client_contact.id} }",
            style="font-size: 20px"
          )
            i.fa.fa-pencil

        v-select.mt-3(
          style="max-width: 50%",
          v-model="selected_service",
          :debounce="250",
          :on-search="lookupService",
          :options="services",
          label="name",
          :placeholder="$t('calendar.Service')"
        )
          template(slot="no-options") {{ $t('template.No_matching_options') }}

        select.mt-3.form-control(
          v-if="pay_stages && pay_stages.length > 0",
          v-model="selected_pay_stage_id",
          style="max-width: 50%"
        )
          option(value="", selected) {{ $t('template.pay_stage') }}
          option(v-for="pay_stage in pay_stages", :value="pay_stage.id") {{ pay_stage.text }}

        fieldset.form-group.mt-4
          label.control-label {{ $t('template.final_consumer') }}
          input(
            :disabled="selected_parent_invoice !== null"
            type="checkbox",
            v-model="is_final_consumer",
            style="vertical-align: middle; margin: 0; margin-left: 10px"
          )

        template(v-if="is_final_consumer !== true")
          p.mt-3 {{ $t('template.Dear_customer_invoice') }}
          input.form-control.mb-2(
            type="text",
            v-model="name",
            :placeholder="$t('calendar.Name')",
            style="max-width: 50%",
            :class="{ 'is-invalid': errors.has('client_name') }",
            name="client_name",
            v-validate="'required'"
          )
          input.form-control.mb-2(
            type="text",
            v-model="address",
            :placeholder="$t('calendar.Address')",
            style="max-width: 50%",
            :class="{ 'is-invalid': errors.has('address') }",
            name="address",
            v-validate="'required'"
          )
          .input-group(style="max-width: 50%")
            input.form-control(
              type="text",
              v-model="code",
              :placeholder="$t('estimate.Postal_code')",
              :class="{ 'is-invalid': errors.has('postal_code') }",
              name="postal_code",
              v-validate="'required'"
            )
            input.form-control(
              type="text",
              v-model="city",
              :placeholder="$t('dashboard.Region')",
              :class="{ 'is-invalid': errors.has('region') }",
              name="region",
              v-validate="'required'"
            )

    template(v-if="invoice_document_type && invoice_serie")
      h3.mt-3(v-if="invoice_no") {{ invoice_document_type.name }} {{ invoice_no }}
      h3.mt-3(v-else) {{ invoice_document_type.name }} {{ invoice_document_type.code }} {{invoice_serie.name}}{{ new Date().getFullYear() }}/{{ invoice_serie.increment }}
    hr
    .row
      .col-3
        b {{ $t('template.Company_tax_number') }}
        .input-group
          .input-group-prepend
            .input-group-text
              input(
                type="checkbox",
                v-model="is_nif_shown",
                :disabled="is_final_consumer === true"
              )
              label(style="margin-bottom: 0; margin-left: 5px") {{ $t('template.not_specified') }}
          input.form-control(
            name="nif",
            :disabled="true",
            type="text",
            v-model="nif",
            :class="{ 'is-invalid': errors.has('nif') }",
            v-validate="'required'"
          )
      .col-3
        b {{ $t('template.date_of_emission') }}
        br
        date-picker(
          format="YYYY-MM-DD",
          v-model="date",
          :lang="$root.locale",
          :width="'100%'"
          :disabled-date="disabledFuture"
        )
    .row.my-2
      template(v-if="invoice_document_type && !['GT', 'GR'].includes(invoice_document_type.code)")
        .col-3
          b {{ $t('template.Maturity') }}
          br
          date-picker(
            format="YYYY-MM-DD",
            v-model="maturity",
            :lang="$root.locale",
            :width="'100%'"
            :disabled-date="disabledEarlier"
          )
        .col-3
          b {{ $t('template.Conditions_of_payment') }}
          select.form-control(v-model="payment_condition_id")
            option(
              v-for="payment_condition in payment_conditions_filtered",
              :value="payment_condition.id"
            ) {{ payment_condition.name }}
      .col-2
        b {{ $t('template.Currency') }}
        select.form-control(v-model="currency")
          option(value="EUR") EUR
          option(value="GBP") GBP
          option(value="RUB") RUB
      .col-2(v-if="currency !== 'EUR'")
        b {{ $t('template.Exchange') }}
        input.form-control(type="number", v-model="exchange", step="0.01")
      .col-2(v-if="currency !== 'EUR'")
        b {{ $t('template.value_in_other_currency') }}
        div {{ parseFloat(exchange * total).toFixed(2) }}
    .row.my-2(
      v-if="invoice_document_type && invoice_document_type.code === 'FR'"
    )
      .col-3
        b {{ $t('template.movement_type') }}
        select.form-control(v-model="movement_type_id")
          option(
            v-for="movement_type in movement_types",
            :value="movement_type.id"
          ) {{ movement_type.name }} {{ movement_type.description }}
    h3.mt-3 {{ $t('template.articles') }}
    hr

    table.table.borderless
      tr
        th(style="width: 20%")
          b {{ $t('estimate.Artigo') }}
        th(style="width: 10%")
          b {{ $t('template.code') }}
        th(style="width: 20%")
          b {{ $t('template.Description') }}
        th
          b {{ $t('estimate.Quantidade') }}
        th
          b {{ $t('estimate.Units') }}
        th(v-if="is_valued")
          b {{ $t('template.pr_initario') }}
        th(v-if="is_valued")
          b {{ $t('template.desc') }}
        th
          b {{ $t('estimate.IVA') }}
        th(v-if="is_valued")
          b {{ $t('estimate.Valor') }}
        th
      tr(v-for="article in articles", :key="article.id")
        td
          v-select(
            :disabled="invoice_document_type && invoice_document_type.code === 'NC'",
            v-model="article.product",
            @input="product_selected(article)",
            :debounce="250",
            :on-search="lookupProduct",
            :options="products",
            label="name",
            :placeholder="$t('template.Products')"
          )
            template(slot="no-options") {{ $t('template.No_matching_options') }}
        td
          template(v-if="article.product") {{ article.product.code }}
        td
          textarea.form-control(
            type="text",
            v-model="article.description",
            :disabled="invoice_document_type && invoice_document_type.code === 'NC'"
          )
        td
          input.form-control(
            type="number",
            v-model="article.quantity",
            step="0.01"
          )
        td
          input.form-control(
            type="text",
            v-model="article.unit",
            :disabled="invoice_document_type && invoice_document_type.code === 'NC'"
          )
        td(v-if="is_valued")
          input.form-control(
            type="number",
            v-model="article.pr_unitario",
            step="0.01"
          )
        td(v-if="is_valued")
          input.form-control(
            type="number",
            v-model="article.desc",
            step="0.01",
            :disabled="invoice_document_type && invoice_document_type.code === 'NC'"
          )
        td
          select.form-control(
            v-model="article.vat_type_id",
            :disabled="invoice_document_type && invoice_document_type.code === 'NC'"
          )
            option(v-for="vat_type in vat_types", :value="vat_type.id") {{ vat_type.code }} - {{ vat_type.name }} ({{ vat_type.percent }} %)
        td(v-if="is_valued") {{ parseFloat(calculate_value(article)).toFixed(2) }}
        td
          button.btn.btn-danger(
            v-on:click="remove_article(article)",
            :disabled="invoice_document_type && invoice_document_type.code === 'NC'"
          )
            span(aria-hidden="true") &times;
    .text-center
      button.btn.btn-diga(
        v-on:click="add_article",
        :disabled="invoice_document_type && invoice_document_type.code === 'NC'"
      ) {{ $t('calendar.Add') }}
    br

    .row(
      v-if="invoice_document_type && ['GT', 'GR'].includes(invoice_document_type.code)"
    )
      .col-6
        h2 {{ $t('template.loading_location') }}
        div.form-group.form-check
          input.form-check-input(type="checkbox" v-model="use_same_load_address")
          label.form-check-label {{$t('template.use_the_same_address_my')}}
        fieldset.form-group
          label.control-label {{ $t('calendar.Address') }}
          input.form-control(type="text", v-model="loading_address" name="loading_address" :class="{ 'is-invalid': errors.has('loading_address') }", v-validate="'required'")
        fieldset.form-group
          label.control-label {{ $t('dashboard.Region') }}
          input.form-control(type="text", v-model="loading_city" name="loading_city" :class="{ 'is-invalid': errors.has('loading_city') }", v-validate="'required'")
        fieldset.form-group
          label.control-label {{ $t('estimate.Postal_code') }}
          input.form-control(type="text", v-model="loading_postal_code"  name="loading_postal_code" :class="{ 'is-invalid': errors.has('loading_postal_code') }", v-validate="'required'")
        fieldset.form-group
          label.control-label {{ $t('template.Country') }}
          select.form-control(v-model="loading_country")
            option(value="PT") PT {{ $t('template.Portugal') }}
            option(value="ES") ES {{ $t('template.Spain') }}
        fieldset.form-group
          label.control-label {{ $t('estimate.Data') }}
          br
          date-picker(
            format="YYYY-MM-DD HH:mm:ss",
            type="datetime",
            v-model="loading_date",
            :lang="$root.locale",
            :width="'100%'",
            :disabled-date="disabledEarlier"
             name="loading_date" :class="{ 'is-invalid': errors.has('loading_date') }", v-validate="'required'"
          )
      .col-6
        h2 {{ $t('template.dicharge_location') }}
        div.form-group.form-check
          input.form-check-input(type="checkbox" v-model="use_same_discharge_address")
          label.form-check-label {{$t('template.use_the_same_address_client')}}
        fieldset.form-group
          label.control-label {{ $t('calendar.Address') }}
          input.form-control(type="text", v-model="discharge_address" name="discharge_address" :class="{ 'is-invalid': errors.has('discharge_address') }", v-validate="'required'")
        fieldset.form-group
          label.control-label {{ $t('dashboard.Region') }}
          input.form-control(type="text", v-model="discharge_city" name="discharge_city" :class="{ 'is-invalid': errors.has('discharge_city') }", v-validate="'required'")
        fieldset.form-group
          label.control-label {{ $t('estimate.Postal_code') }}
          input.form-control(type="text", v-model="discharge_postal_code" name="discharge_postal_code" :class="{ 'is-invalid': errors.has('discharge_postal_code') }", v-validate="'required'")
        fieldset.form-group
          label.control-label {{ $t('template.Country') }}
          select.form-control(v-model="discharge_country")
            option(value="PT") PT {{ $t('template.Portugal') }}
            option(value="ES") ES {{ $t('template.Spain') }}
        fieldset.form-group
          label.control-label {{ $t('estimate.Data') }}
          br
          date-picker(
            format="YYYY-MM-DD HH:mm:ss",
            type="datetime",
            v-model="discharge_date",
            :lang="$root.locale",
            :width="'100%'",
            :disabled-date="disabledEarlierAndBeforeLoading"
            name="discharge_date" :class="{ 'is-invalid': errors.has('discharge_date') }", v-validate="'required'"
          )
        fieldset.form-group
          label.control-label {{ $t('template.dicharge_registration') }}
          input.form-control(type="text", v-model="discharge_registration")

    template(v-if="is_valued")
      hr
      .row
        .col-md-7
          b {{ $t('template.Summary_table_of_iva') }}
          table.table.borderless
            tr
              td
                b {{ $t('template.tax') }}
              td
                b {{ $t('template.incidence') }}
              td
                b {{ $t('template.total_vat') }}
              td
                b {{ $t('template.reason_for_exemption') }}
            template(v-if="vat_list")
              tr(v-for="vl in vat_list")
                td {{ vl.tax }}
                td {{ parseFloat(vl.value).toFixed(2) }}
                td {{ parseFloat(vl.vat_value).toFixed(2) }}
                td(v-if="vl.vat_value === 0")
                  select.form-control.mt-2(v-model="vat_exemption_reason_id")
                    option(:value="null", selected, disabled) {{ $t('template.Choose') }}
                    option(
                      v-for="ver in vat_exemption_reasons",
                      :value="ver.id"
                    ) {{ ver.name }}

        .col-md-5
          table.table.borderless
            tr
              td {{ $t('template.merchandise') }}/{{ $t('template.ServiceSettings') }}
              td {{ parseFloat(value).toFixed(2) }}
            tr
              td {{ $t('template.Desc_cli') }}
              td {{ parseFloat(commercial_discount).toFixed(2) }}
            tr
              td {{ $t('template.global_discount') }}
              td
                .input-group
                  input.form-control(
                    type="number",
                    v-model="global_discount",
                    step="0.01"
                  )
                  .input-group-append
                    span.input-group-text %
            tr
              td {{ $t('client.vat') }}
              td {{ parseFloat(vat).toFixed(2) }}
          hr
          h2 Total (EUR) {{ parseFloat(total).toFixed(2) }}
          h2(v-if="currency !== 'EUR'") Total ({{ currency }}) {{ parseFloat(exchange * total).toFixed(2) }}

      .row(v-if="$root.module_enabled('email')")
        .col-md-2
          .form-check
            input#send_email.form-check-input(
              type="checkbox",
              v-model="send_email"
            )
            label.form-check-label(for="send_email") {{ $t('client.Send_email') }}
        .col-md-3(v-if="send_email === true")
          select.form-control.mx-2.mb-2(
            v-model="selected_template_id",
            style="flex: 2; min-width: 150px"
          )
            option(value="0") {{ $t('template.choose_email_template') }}
            option(
              v-for="email_template in email_templates",
              v-bind:value="email_template.id",
              v-text="email_template.name"
            )
    .row
      .col-md-3
        button.btn.btn-diga(
          :disabled="articles.length === 0 || total === 0",
          v-on:click="save"
        ) {{ $t('template.Save') }}
</template>

<script>
import moment from "moment";
import { mapGetters } from "vuex";
import { debounce } from "lodash";
import email from "./../../../../../packages/Rkesa/Email/resources/assets/js/components/mail/popup.vue";
import new_series from "./new_series.vue";

export default {
  components: { email: email, "new-series": new_series },
  data() {
    return {
      invoice_series_id: 0,
      invoice_series: [],
      use_same_load_address: false,
      use_same_discharge_address: false,
      is_valued: true,
      parent_invoice_id: 0,
      invoice_document_type_id: 0,
      selected_client: null,
      clients: [],

      selected_client_contact: null,
      client_contacts: [],

      selected_service: null,
      services: [],

      selected_estimate: null,

      pay_stages: [],
      selected_pay_stage: null,
      selected_pay_stage_id: "",

      payment_conditions: [],
      movement_types: [],
      vat_types: [],

      settings: {
        name: "",
        address: "",
        postal_code: "",
        city: "",
        phone: "",
        fax: "",
        email: "",
        web_site: "",
        capital: "",
        tax_number: "",
        bank: "",
        iban: "",
        swift: "",
      },
      vat_exemption_reason_id:
        this.$root.global_settings.vat_exemption_reason_id,
      invoice_no: null,
      name: "",
      address: "",
      city: "",
      code: "",
      date: new Date(),
      nif: "",
      request: "",
      currency: "EUR",
      exchange: 1.0,
      desc_cli: 0.0,
      desc_fin: 0.0,
      maturity: new Date(),
      payment_condition_id: null,
      movement_type_id: null,
      estimate_number: "",
      reason_for_exemption: "",
      postage: 0.0,
      other_services: 0.0,
      advances: 0.0,
      settlement: 0.0,
      service_id: 0,
      pay_stage_percent: 0.0,
      send_email: false,
      selected_template_id: 0,

      loading_address: "",
      loading_city: "",
      loading_postal_code: "",
      loading_country: "PT",
      loading_date: new Date(),
      discharge_date: new Date(),

      discharge_address: "",
      discharge_city: "",
      discharge_postal_code: "",
      discharge_country: "PT",
      discharge_registration: "",

      email_link: "",
      redirect_to: "invoice_index",

      articles: [],
      removed: [],
      vat_exemption_reasons: [],
      is_final_consumer: false,
      is_nif_shown: false,

      invoice_document_types: [],
      parent_invoices: [],
      selected_parent_invoice: null,
      highlight_parent_invoice: false,

      products: [],
      correction_reason: "",
      global_discount: 0.0,
    };
  },
  props: ["id", "pay_stage_id"],
  mounted() {
    this.get_settings();
    this.currency = "EUR";
    this.get_movement_types();
    this.get_vat_types();
    this.get_templates();
    this.get_vat_exemption_reasons();
    this.get_invoice_document_types();

    if (this.id !== undefined) {
      this.get_invoice();
    }
    if (this.pay_stage_id !== undefined) {
      this.get_pay_stage();
    }
  },
  methods: {
    update_invoice_series_id(id) {
      this.invoice_series_id = id;
    },
    show_add_series() {
      jQuery("#modal-add-series").modal("show");
    },
    get_invoice_series() {
      this.$root.global_loading = true;
      this.$http.get("/api/invoice_series").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.invoice_series = res.data.rows;
            if (
              this.invoice_series.length > 0 &&
              this.invoice_series_id === 0
            ) {
              let series = this.invoice_series.find(
                (s) => s.document_type_id === this.invoice_document_type_id
              );
              if (series) {
                this.invoice_series_id = series.id;
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
    disabledEarlierAndBeforeLoading(date) {
      if (this.loading_date !== null) {
        return date < this.loading_date;
      }
      return date < this.date;
    },
    disabledEarlier(date) {
      return date < this.date;
    },
    disabledFuture(date) {
      if (this.invoice_serie && this.invoice_serie.last_invoice_date !== null) {
        return (
          date > new Date() ||
          date < new Date(this.invoice_serie.last_invoice_date)
        );
      } else {
        return date > new Date();
      }
    },
    product_selected(article) {
      if (article.product != null && article.product !== null) {
        article.product_id = article.product.id;
        article.product = article.product;
        article.vat_type_id = article.product.vat_type_id;
        if (article.product.estimate_unit != null) {
          article.unit = article.product.estimate_unit.measure;
        }
        article.pr_unitario = article.product.price;
        if (article.description === "") {
          article.description = article.product.name;
        }
      } else {
        article.product_id = null;
        article.product = null;
      }
    },
    lookupProduct: debounce(function (client_query) {
      let url = `/api/products?query=${client_query}`;
      if (
        this.invoice_document_type.code === "GT" ||
        this.invoice_document_type.code === "GR"
      ) {
        url += "&category=Produto";
      }
      this.$http
        .get(url)
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.products = data.rows;
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
    lookupInvoice: debounce(function (client_query) {
      this.highlight_parent_invoice = false;
      this.$http
        .get(
          `/api/invoices?query=${client_query}&source=${this.invoice_document_type.code}`
        )
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.parent_invoices = data.rows;
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
    get_invoice_document_types() {
      this.$root.global_loading = true;
      this.$http.get("/api/invoice_document_types").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.invoice_document_types = res.data.rows;
            if (this.invoice_document_type_id === 0) {
              var f = this.invoice_document_types.find(
                (i) => i.name === "Fatura"
              );
              if (f) {
                this.invoice_document_type_id = f.id;
              }
            }
            this.get_invoice_series();
          }
          this.$root.global_loading = false;
          this.get_payment_conditions();
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
    get_vat_exemption_reasons() {
      this.$root.global_loading = true;
      this.$http.get("/api/vat_exemption_reasons").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.vat_exemption_reasons = res.data.rows;
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
    get_pay_stage() {
      this.$root.global_loading = true;
      this.$http.get("/api/estimate_pay_stages/" + this.pay_stage_id).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            // this.selected_client = res.data.client;
            this.selected_pay_stage = res.data;
            this.selected_client_contact =
              res.data.estimate.service.client_contact;
            this.selected_service = res.data.estimate.service;
            this.selected_estimate = res.data.estimate;
            this.selected_pay_stage_id = this.pay_stage_id;
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
    get_invoice() {
      this.$root.global_loading = true;
      this.$http.get("/api/invoices/" + this.id).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.date = res.data.invoice_date;
            this.invoice_no = res.data.invoice_no;

            this.selected_client = res.data.client;
            this.selected_client_contact = res.data.client_contact;
            this.selected_service = res.data.service;
            this.selected_estimate = res.data.estimate;
            this.selected_pay_stage_id = res.data.pay_stage_id;
            this.selected_pay_stage = res.data.pay_stage;
            if (res.data.pay_stage_id !== null) {
              if (res.data.estimate && res.data.estimate.estimate_pay_stages) {
                this.pay_stages = res.data.estimate.estimate_pay_stages;
              }
            }

            this.payment_condition_id = res.data.payment_condition_id;
            this.movement_type_id = res.data.movement_type_id;

            this.name = res.data.name;
            this.address = res.data.address;
            this.city = res.data.city;
            this.code = res.data.code;
            this.nif = res.data.nif;
            this.request = res.data.request;
            this.currency = res.data.currency;
            this.exchange = res.data.exchange;
            this.desc_cli = res.data.desc_cli;
            this.desc_fin = res.data.desc_fin;
            this.maturity = res.data.maturity;
            this.postage = res.data.postage;
            this.other_services = res.data.other_services;
            this.advances = res.data.advances;
            this.settlement = res.data.settlement;
            this.vat_exemption_reason_id = res.data.vat_exemption_reason_id;
            this.is_final_consumer = res.data.is_final_consumer;
            this.invoice_document_type_id = res.data.document_type_id;
            this.is_valued = res.data.is_valued;
            this.global_discount = res.data.global_discount;

            this.articles = res.data.invoice_items.map((a) => ({
              id: a.id,
              description: a.description,
              quantity: a.quantity,
              unit: a.unit,
              pr_unitario: a.unit_price,
              desc: a.discount,
              vat_type_id: a.vat_type_id,
              product_id: a.product.id,
            }));
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
    remove_article(article) {
      if (confirm(this.$root.$t("calendar.AreYouSure"))) {
        if (article.id > 0) {
          this.removed.push(article.id);
        }
        let index = this.articles.indexOf(article);
        this.articles.splice(index, 1);
      }
    },
    calculate_value(a) {
      var tot = 0.0;
      if (a.quantity > 0 && a.pr_unitario > 0) {
        tot = a.pr_unitario;
        if (a.desc > 0) {
          tot -= (tot * a.desc) / 100;
        }
        if (this.global_discount > 0) {
          tot -= (tot * this.global_discount) / 100;
        }
        tot *= a.quantity;
      }
      return tot;
    },
    calculate_discount(a) {
      var tot = 0.0;
      if (a.quantity > 0 && a.pr_unitario > 0) {
        let sum = a.quantity * a.pr_unitario;
        if (a.desc > 0) {
          tot = (sum * a.desc) / 100;
          sum -= tot;
        }
        if (this.global_discount > 0) {
          tot = +(sum * this.global_discount) / 100;
        }
      }
      return tot;
    },
    calculate_value_with_finance_discount(a) {
      var tot = 0.0;
      if (a.quantity > 0 && a.pr_unitario > 0) {
        tot = a.quantity * a.pr_unitario;
        if (a.desc > 0) {
          tot -= (tot * a.desc) / 100;
        }
        if (this.desc_fin > 0) {
          tot -= (tot * this.desc_fin) / 100;
        }
        if (this.global_discount > 0) {
          tot -= (tot * this.global_discount) / 100;
        }
      }
      return tot;
    },
    get_payment_conditions() {
      this.$root.global_loading = true;
      this.$http.get("/api/payment_conditions").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.payment_conditions = res.data.rows;
            if (this.payment_conditions_filtered.length > 0) {
              this.payment_condition_id =
                this.payment_conditions_filtered[0].id;
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
    get_movement_types() {
      this.$root.global_loading = true;
      this.$http.get("/api/movement_types").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.movement_types = res.data.rows;
            if (this.movement_types.length > 0) {
              this.movement_type_id = this.movement_types[0].id;
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
    get_vat_types() {
      this.$root.global_loading = true;
      this.$http.get("/api/vat_types").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.vat_types = res.data.rows;
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
    get_estimate(estimate_id) {
      this.$root.global_loading = true;
      this.$http.get("/api/estimates/" + estimate_id).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.selected_estimate = res.data;
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
    add_article() {
      var article = {
        description: "",
        quantity: 1,
        unit: "UN",
        pr_unitario: 0.0,
        desc: 0.0,
        vat_type_id: this.vat_types[0].id,
        product_id: null,
      };
      if (this.$root.global_settings.vat_exemption_reason_id > 0) {
        article.vat_type_id = this.vat_types[0].id;
      }
      this.articles.push(article);
    },
    lookupService: debounce(function (service_query) {
      var url = `/api/services?query=${service_query}`;
      if (this.selected_client_contact != null) {
        url += "&client_contact_id=" + this.selected_client_contact.id;
      }
      this.$http
        .get(url)
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
                  (i.name === null ? "" : i.name.substr(0, 40) + "... ") +
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
      let url = `/api/contacts?query=${client_contact_query}`;
      if (this.selected_client != null) {
        url += "&client_id=" + this.selected_client.id;
      }
      this.$http
        .get(url)
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              data.rows.forEach((r) => {
                r.fullName = r.name + " " + r.surname;
              });
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
    get_templates() {
      this.$http.get("/api/email_templates").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.email_templates = res.data;
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
    get_settings() {
      this.$http.get("/api/company_information").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.settings = res.data;
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
    async save() {
      this.$validator.validateAll().then(async (result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        }

        if (this.invoice_document_type.code === "NC") {
          if (
            this.selected_parent_invoice == null ||
            this.correction_reason === ""
          ) {
            this.$toastr.w(
              this.$root.$t("template.for_credit_note_choose_invoice"),
              this.$root.$t("template.Warning")
            );
            this.highlight_parent_invoice = true;
            return;
          }
          if (this.total > this.selected_parent_invoice.gross_total) {
            this.$toastr.w(
              this.$root.$t("template.for_credit_note_sum_exceeded"),
              this.$root.$t("template.Warning")
            );
            return;
          }
        }

        let $this = this;
        this.$root.global_loading = true;

        if (this.send_email === true) {
          let res = await this.$http.post("/api/mail/login").then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
                return "error";
              }
            },
            (res) => {
              this.$toastr.e(
                this.$root.$t("template.Something_bad_happened"),
                this.$root.$t("template.Error")
              );
              return "error";
            }
          );

          if (res === "error") {
            this.$root.global_loading = false;
            return;
          }
        }

        let payload = {
          invoice_date: this.date,
          invoice_no: `${this.invoice_document_type.code} ${
            this.invoice_serie.name
          }${new Date().getFullYear()}/${this.invoice_serie.increment}`,
          gross_total: this.total,
          gross_total_without_vat: this.value,
          client_id:
            this.selected_client !== null ? this.selected_client.id : null,
          client_contact_id:
            this.selected_client_contact !== null
              ? this.selected_client_contact.id
              : null,
          service_id:
            this.selected_service !== null ? this.selected_service.id : null,
          estimate_id:
            this.selected_estimate !== null ? this.selected_estimate.id : null,
          pay_stage_id:
            this.selected_pay_stage !== null
              ? this.selected_pay_stage.id
              : null,
          payment_condition_id: this.payment_condition_id,
          movement_type_id: this.movement_type_id,

          name: this.name,
          address: this.address,
          city: this.city,
          code: this.code,
          nif: this.nif,
          request: this.request,
          currency: this.currency,
          exchange: this.exchange,
          desc_cli: this.desc_cli,
          desc_fin: this.desc_fin,
          maturity: this.maturity,
          postage: this.postage,
          other_services: this.other_services,
          advances: this.advances,
          settlement: this.settlement,
          vat_exemption_reason_id: this.vat_exemption_reason_id,
          is_final_consumer: this.is_final_consumer,
          document_type_id: this.invoice_document_type_id,
          parent_invoice_id:
            this.parent_invoice_id === 0 ? null : this.parent_invoice_id,
          correction_reason: this.correction_reason,
          loading_address: this.loading_address,
          loading_city: this.loading_city,
          loading_postal_code: this.loading_postal_code,
          loading_country: this.loading_country,
          loading_date: this.loading_date,
          discharge_date: this.discharge_date,

          discharge_address: this.discharge_address,
          discharge_city: this.discharge_city,
          discharge_postal_code: this.discharge_postal_code,
          discharge_country: this.discharge_country,
          discharge_registration: this.discharge_registration,

          invoice_items: this.articles.map((a) => ({
            id: a.id || null,
            description: a.description,
            quantity: a.quantity,
            unit: a.unit,
            unit_price: a.pr_unitario,
            discount: a.desc,
            vat_type_id: a.vat_type_id,
            product_id: a.product_id,
          })),

          removed_invoice_items: this.removed,
          selected_template_id: this.selected_template_id,
          send_email: this.send_email,
          is_valued: this.is_valued,
          global_discount: this.global_discount,
          series_id: this.invoice_series_id,
        };

        if (
          payload.service_id != null &&
          (payload.client_id == null || payload.client_contact_id == null)
        ) {
          payload.client_contact_id = this.selected_service.client_contact_id;
          if (this.selected_service.client_contact) {
            payload.client_id = this.selected_service.client_contact.client_id;
          }
        }

        if (this.id !== undefined) {
          this.$http.patch("/api/invoices/" + this.id, payload).then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
              } else {
                if (this.send_email === true) {
                  this.email_link =
                    "/webmail/index.php#single-compose/drafts/" +
                    encodeURIComponent(res.data.draft.NewFolder) +
                    "/" +
                    res.data.draft.NewUid;
                  jQuery("#modal-mail-general").modal("show");
                  this.$root.global_loading = false;
                } else {
                  this.$router.push({ name: "invoice_index" });
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
        } else {
          this.$http.post("/api/invoices", payload).then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
              } else {
                if (this.send_email === true) {
                  this.email_link =
                    "/webmail/index.php#single-compose/drafts/" +
                    encodeURIComponent(res.data.draft.NewFolder) +
                    "/" +
                    res.data.draft.NewUid;
                  jQuery("#modal-mail-general").modal("show");
                  this.$root.global_loading = false;
                } else {
                  this.$router.push({ name: "invoice_index" });
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
        }
      });
    },
    addDays(date, days) {
      var result = new Date(date);
      result.setDate(result.getDate() + days);
      return result;
    },
  },
  computed: {
    invoice_document_type() {
      if (this.invoice_document_type_id > 0) {
        return this.invoice_document_types.find(
          (d) => d.id === this.invoice_document_type_id
        );
      } else {
        return null;
      }
    },
    invoice_series_list() {
      if (this.invoice_document_type_id > 0) {
        return this.invoice_series.filter(
          (s) => s.document_type_id === this.invoice_document_type_id
        );
      } else {
        return [];
      }
    },
    invoice_serie() {
      if (this.invoice_series_id > 0) {
        return this.invoice_series.find((s) => s.id === this.invoice_series_id);
      } else {
        return null;
      }
    },
    payment_conditions_filtered() {
      if (this.payment_conditions.length > 0) {
        if (
          this.invoice_document_type &&
          ["FR"].includes(this.invoice_document_type.code)
        ) {
          return this.payment_conditions.filter(
            (p) => p.name === "Pronto pagamento"
          );
        } else {
          return this.payment_conditions.filter(
            (p) => p.name !== "Pronto pagamento"
          );
        }
      }
      return [];
    },
    commercial_discount() {
      let result = 0.0;
      this.articles.forEach((a) => {
        result += this.calculate_discount(a);
      });
      return result;
    },
    total() {
      let sum = this.value;
      // if (this.desc_fin > 0) {
      //   sum = sum - (sum * this.desc_fin) / 100;
      // }
      return (
        sum + this.vat
        // - this.desc_cli -
        // this.desc_fin +
        // this.postage +
        // this.other_services +
        // this.advances
      );
    },
    vat() {
      let result = 0.0;
      if (this.vat_list === null) {
        return parseFloat(result).toFixed(2);
      }
      this.vat_list.forEach((vl) => {
        result += vl.vat_value;
      });

      return result;
    },
    value() {
      let result = 0.0;
      this.articles.forEach((a) => {
        result += this.calculate_value_with_finance_discount(a);
      });
      return result;
    },
    vat_list() {
      if (this.articles.length === 0) {
        return null;
      }
      let list = [];
      this.vat_types.forEach((vt) => {
        let value = 0.0;
        let vat_value = 0.0;
        this.articles.forEach((a) => {
          if (parseInt(a.vat_type_id) === parseInt(vt.id)) {
            value += this.calculate_value_with_finance_discount(a);
            vat_value +=
              (this.calculate_value_with_finance_discount(a) * vt.percent) /
              100;
          }
        });

        if (value > 0) {
          list.push({
            tax: vt.percent + " %",
            value: value,
            vat_value: vat_value,
          });
        }
      });

      return list;
    },
  },
  watch: {
    use_same_discharge_address() {
      if (this.use_same_discharge_address === true) {
        this.discharge_address = this.address;
        this.discharge_city = this.city;
        this.discharge_postal_code = this.code;
      } else {
        this.discharge_address = "";
        this.discharge_city = "";
        this.discharge_postal_code = "";
      }
    },
    use_same_load_address() {
      if (this.use_same_load_address === true) {
        this.loading_address = this.settings.address;
        this.loading_city = this.settings.city;
        this.loading_postal_code = this.settings.postal_code;
      } else {
        this.loading_address = "";
        this.loading_city = "";
        this.loading_postal_code = "";
      }
    },
    selected_parent_invoice: function () {
      if (this.selected_parent_invoice !== null) {
        this.parent_invoice_id = this.selected_parent_invoice.id;
        this.payment_condition_id =
          this.selected_parent_invoice.payment_condition_id;
        this.movement_type_id = this.selected_parent_invoice.movement_type_id;

        this.client_id = this.selected_parent_invoice.client_id;
        this.selected_client = this.selected_parent_invoice.client;
        this.client_contact_id = this.selected_parent_invoice.client_contact_id;
        if (this.selected_parent_invoice.client_contact) {
          this.selected_parent_invoice.client_contact.fullName =
            this.selected_parent_invoice.client_contact.name +
            " " +
            this.selected_parent_invoice.client_contact.surname;
          this.selected_client_contact =
            this.selected_parent_invoice.client_contact;
        }

        this.service_id = this.selected_parent_invoice.service_id;
        this.selected_service = this.selected_parent_invoice.service;
        this.estimate_id = this.selected_parent_invoice.estimate_id;
        this.selected_estimate = this.selected_parent_invoice.estimate;

        this.name = this.selected_parent_invoice.name;
        this.address = this.selected_parent_invoice.address;
        this.city = this.selected_parent_invoice.city;
        this.code = this.selected_parent_invoice.code;
        this.nif = this.selected_parent_invoice.nif;
        this.request = this.selected_parent_invoice.request;
        this.currency = this.selected_parent_invoice.currency;
        this.exchange = this.selected_parent_invoice.exchange;
        this.maturity = this.selected_parent_invoice.maturity;
        this.postage = this.selected_parent_invoice.postage;
        this.other_services = this.selected_parent_invoice.other_services;
        this.advances = this.selected_parent_invoice.advances;
        this.settlement = this.selected_parent_invoice.settlement;
        this.vat_exemption_reason_id =
          this.selected_parent_invoice.vat_exemption_reason_id;
        this.is_final_consumer = this.selected_parent_invoice.is_final_consumer;
        this.global_discount = this.selected_parent_invoice.global_discount;

        this.articles = this.selected_parent_invoice.invoice_items.map((a) => ({
          id: a.id,
          description: a.description,
          quantity: a.quantity,
          unit: a.unit,
          pr_unitario: a.unit_price,
          desc: a.discount,
          vat_type_id: a.vat_type_id,
          product_id: a.product_id,
          product: a.product,
        }));

        if (this.invoice_document_type.code === "NC") {
          this.articles.forEach((a) => {
            if (this.global_discount > 0) {
              a.pr_unitario -= (a.pr_unitario * this.global_discount) / 100;
            }
            if (a.desc > 0) {
              a.pr_unitario -= (a.pr_unitario * a.desc) / 100;
            }
            a.desc = 0;
          });
          this.global_discount = 0;
        }
      }
    },
    invoice_document_type_id: function () {
      if (this.invoice_series.length > 0) {
        this.invoice_series_id = this.invoice_series.find(
          (s) => s.document_type_id === this.invoice_document_type_id
        ).id;
      }
      if (this.payment_conditions_filtered.length > 0) {
        this.payment_condition_id = this.payment_conditions_filtered[0].id;
      }
      this.is_valued = true;
    },
    is_final_consumer: function (newVal) {
      if (newVal === true) {
        this.nif = "999999990";
        this.name = "";
        this.address = "";
        this.code = "";
        this.city = "";

        this.selected_client = null;
        this.selected_client_contact = null;
      } else {
        this.nif = "";
      }
    },
    is_nif_shown: function (newVal) {
      if (newVal === true) {
        this.nif = "999999990";
      } else {
        this.nif = "";
      }
    },
    date: function (newVal) {
      if (this.payment_condition_id !== null) {
        let payment_condition = this.payment_conditions.find(
          (pc) => parseInt(pc.id) === parseInt(this.payment_condition_id)
        );
        this.maturity = moment(newVal).add(
          parseInt(payment_condition.days),
          "days"
        );
      } else {
        this.maturity = newVal;
      }
    },
    payment_condition_id: function (newVal) {
      if (newVal !== null && this.id === undefined) {
        let payment_condition = this.payment_conditions.find(
          (pc) => parseInt(pc.id) === parseInt(newVal)
        );
        this.maturity = this.addDays(
          this.date,
          parseInt(payment_condition.days)
        );
      }
    },
    selected_pay_stage_id: function (newVal) {
      if (this.id === undefined && this.pay_stage_id === undefined) {
        if (newVal !== "") {
          this.selected_pay_stage = this.pay_stages.find(
            (ps) => parseInt(ps.id) === parseInt(newVal)
          );
        } else {
          this.selected_pay_stage = null;
        }
      }
    },
    selected_pay_stage: function (newVal) {
      if (newVal !== null && this.id === undefined) {
        this.articles = [];
        var article = {
          description: this.selected_estimate.subject + " " + newVal.text,
          quantity: 1,
          unit: "UN",
          pr_unitario: (
            (this.selected_estimate.price * (newVal.percent || 0)) /
            100
          ).toFixed(2),
          desc: 0.0,
          vat_type_id: this.vat_types[0].id,
          product_id: null,
        };
        if (this.$root.global_settings.vat_exemption_reason_id > 0) {
          article.vat_type_id = this.vat_types[0].id;
        }
        if (this.articles.length === 0) {
          this.articles.push(article);
        }
      }
    },
    selected_estimate: function (newVal) {
      if (this.id === undefined && this.pay_stage_id === undefined) {
        var article = {
          description: newVal.subject,
          quantity: 1,
          unit: "UN",
          pr_unitario: newVal.price,
          desc: 0.0,
          vat_type_id: this.vat_types[0].id,
          product_id: null,
        };
        if (this.$root.global_settings.vat_exemption_reason_id > 0) {
          article.vat_type_id = this.vat_types[0].id;
        }
        if (this.articles.length === 0) {
          this.articles.push(article);
        }
      }
      if (
        newVal.estimate_pay_stages != null &&
        newVal.estimate_pay_stages.length > 0
      ) {
        this.pay_stages = newVal.estimate_pay_stages;
        // this.selected_pay_stage_id = this.pay_stages[0].id;
      }
    },
    selected_client: function (newVal) {
      if (newVal != null && this.id === undefined) {
        this.is_final_consumer = false;
        this.name = newVal.name;
        this.address = newVal.address_legal;
        this.code = newVal.postal_code;
        this.city = newVal.city;
        this.nif = newVal.nif;
        if (this.nif === "" || this.nif === null) {
          this.$toastr.w(
            this.$root.$t("template.selected_client_has_no_nif"),
            this.$root.$t("template.Warning")
          );
          this.is_nif_shown = true;
        }
      }
    },
    selected_client_contact: function (newVal) {
      if (
        newVal != null &&
        this.selected_client === null &&
        this.id === undefined
      ) {
        this.is_final_consumer = false;
        this.name = newVal.name + " " + newVal.surname;
        this.address = newVal.address;
        this.code = newVal.postal_code;
        this.city = newVal.city;
        this.nif = newVal.nif;
        if (this.nif === "" || this.nif === null) {
          this.$toastr.w(
            this.$root.$t("template.selected_client_has_no_nif"),
            this.$root.$t("template.Warning")
          );
          this.is_nif_shown = true;
        }
      }
    },
    selected_service: function (newVal) {
      if (this.id === undefined) {
        if (newVal.master_estimate_id != null) {
          this.get_estimate(newVal.master_estimate_id);
        } else {
          var article = {
            description: newVal.name,
            quantity: 1,
            unit: "UN",
            pr_unitario: newVal.estimate_summ,
            desc: 0.0,
            vat_type_id: this.vat_types[0].id,
            product_id: null,
          };
          if (this.$root.global_settings.vat_exemption_reason_id > 0) {
            article.vat_type_id = this.vat_types[0].id;
          }
          if (this.articles.length === 0) {
            this.articles.push(article);
          }
        }
        if (
          this.selected_client_contact == null &&
          this.selected_client == null
        ) {
          if (newVal.client_contact.client != null) {
            this.name = newVal.client_contact.client.name;
            this.address = newVal.client_contact.client.address_legal;
            if (this.nif === "") {
              this.nif = newVal.client_contact.client.nif;
            }
          } else {
            this.name =
              newVal.client_contact.name + " " + newVal.client_contact.surname;
            this.address = newVal.client_contact.address;
            if (this.nif === "") {
              this.nif = newVal.client_contact.nif;
            }
          }
        }
      }
    },
  },
};
</script>