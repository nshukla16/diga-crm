<style>
</style>

<template lang="pug">
.portlet.light(v-if="currentProduct")
  .portlet-body
    .row
      section.col-12.col-md-6.mb-3
        .diga-container.p-4
          h2 {{ $t('estimate.Main_information') }}
          fieldset.form-group(:class="{ 'has-error': errors.has('name') }")
            label.control-label {{ $t('estimate.Name') }}
            input.form-control(
              name="name",
              v-validate="'required'",
              type="text",
              v-model="currentProduct.name",
              v-bind:data-vv-as="$t('estimate.Name').toLowerCase()"
            )
            span.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
          fieldset.form-group(:class="{ 'has-error': errors.has('code') }")
            label.control-label {{ $t('template.product_code') }}
            input.form-control(
              name="code",
              v-validate="'required'",
              type="text",
              v-model="currentProduct.code",
              v-bind:data-vv-as="$t('template.product_code').toLowerCase()"
            )
            span.help-block(v-show="errors.has('code')") {{ errors.first('code') }}
          fieldset.form-group
            label.control-label {{ $t('estimate_fork.Category') }}
            select.form-control(v-model="currentProduct.category")
              option(value="Serviço") Serviço
              option(value="Produto") Produto
              option(value="Outros") Outros
          .row
            .col-12.col-md-6
              fieldset.form-group(
                :class="{ 'has-error': errors.has('price') }"
              )
                label.control-label {{ $t('estimate.Price_for_1_unit') + ' ' + (currentProduct.estimate_unit_id != null ? units_by_id[currentProduct.estimate_unit_id].measure : '') }}
                input.form-control(
                  name="price",
                  v-validate="{'min_value':0, required:true}",
                  type="number",
                  v-model="currentProduct.price",
                  min="0"
                )
                span.help-block(v-show="errors.has('price')") {{ errors.first('price') }}
            .col-12.col-md-6
              fieldset.form-group
                label.control-label {{ $t('estimate.Unit') }}
                select.form-control(v-model="currentProduct.estimate_unit_id")
                  option(v-for="unit in units", :value="unit.id") {{ unit.measure }}
          .row
            .col-12.col-md-6
              fieldset.form-group(
                :class="{ 'has-error': errors.has('quantity') }"
              )
                label.control-label {{ $t('estimate.Quantity') }}
                input.form-control(
                  name="quantity",
                  v-validate="{'min_value':0, required:true}",
                  type="number",
                  v-model="currentProduct.quantity",
                  min="0"
                )
                span.help-block(v-show="errors.has('quantity')") {{ errors.first('quantity') }}
            .col-12.col-md-6(v-if="vat_types.length > 0")
              fieldset.form-group(
                :class="{ 'has-error': errors.has('vat_type') }"
              )
                label.control-label {{ $t('client.type_of_the_vat') }}
                select.form-control(
                  v-model="currentProduct.vat_type_id",
                  v-bind:data-vv-as="$t('client.type_of_the_vat').toLowerCase()",
                  name="vat_type",
                  v-validate="'required'"
                )
                  option(v-for="vat_type in vat_types", :value="vat_type.id") {{ vat_type.name }} {{ vat_type.percent }}
                span.help-block(v-show="errors.has('vat_type')") {{ errors.first('vat_type') }}

    .row
      .col-12
        button.btn.btn-diga(v-on:click="save") {{ $t('template.Save') }}
</template>

<script>
import { mapGetters } from "vuex";

export default {
  data: function () {
    return {
      currentProduct: null,
      isCreating: true,
      vat_types: [],
    };
  },
  props: ["id"],
  computed: {
    ...mapGetters({
      units: "getEstimateUnits",
      units_by_id: "getEstimateUnitsById",
    }),
  },
  methods: {
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
    load_resource() {
      this.$root.global_loading = true;
      this.$http.get("/api/products/" + this.id).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.currentProduct = res.data;
            this.isCreating = false;
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
    save: function () {
      this.$validator.validateAll().then((result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        }
        let payload = Object.assign({}, this.currentProduct);
        if (this.isCreating) {
          this.$http.post("/api/products", payload).then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
              } else {
                this.$toastr.s(
                  this.$root.$t("estimate.Resource_saved"),
                  this.$root.$t("template.Success")
                );
                this.$router.push({ name: "products_index" });
              }
            },
            (res) => {
              this.$toastr.e(
                this.$root.$t("template.Server_error"),
                this.$root.$t("template.Error")
              );
            }
          );
        } else if (
          (this.currentProduct.update_fichas &&
            confirm(
              this.$root.$t("estimate.Are_you_sure_want_to_update_resource")
            )) ||
          !this.currentProduct.update_fichas
        ) {
          this.$http
            .patch("/api/products/" + this.currentProduct.id, payload)
            .then(
              (res) => {
                if (res.data.errcode == 1) {
                  this.$toastr.e(
                    res.data.errmess,
                    this.$root.$t("template.Error")
                  );
                } else {
                  this.$toastr.s(
                    this.$root.$t("estimate.Resource_saved"),
                    this.$root.$t("template.Success")
                  );
                  this.$router.push({ name: "products_index" });
                }
              },
              (res) => {
                this.$toastr.e(
                  this.$root.$t("template.Server_error"),
                  this.$root.$t("template.Error")
                );
              }
            );
        }
      });
    },
  },
  mounted() {
    this.get_vat_types();
    if (this.id) {
      document.title =
        this.$root.global_settings.site_name +
        " | " +
        this.$root.$t("template.edit_product");
      this.load_resource();
    } else {
      document.title =
        this.$root.global_settings.site_name +
        " | " +
        this.$root.$t("template.create_product");
      let newResource = {
        name: "",
        quantity: 0,
        estimate_unit_id: this.units[0].id,
        price: 0,
        category: "Serviços gerais",
      };
      this.isCreating = true;
      this.currentProduct = Object.assign({}, newResource);
    }
  },
};
</script>