<style>
</style>

<template lang="pug">
#company_info.mb-3
  .diga-container.p-3
    .info-top
      .caption.float-left
        span \#{{ my_client.id }}
        router-link#current_client_name.contacts-list.light-link.active.ml-2(
          :to="{ name: 'company_show', params: { id: my_client.id } }"
        ) {{ my_client.name }}
        span(v-if="my_client.is_group === true") {{ ' (' + $t('template.contractor') }})
      .float-right(style="font-size: 18pt")
        router-link.btn.btn-circle.btn-default-1(
          :to="{ name: 'company_show_pdf', params: { id: my_client.id } }",
          target="_blank"
        )
          i.fa.fa-print
        router-link.btn.btn-circle.btn-default-1(
          v-if="my_client.can_be_updated",
          :to="{ name: 'company_edit', params: { id: my_client.id } }"
        )
          i.fa.fa-pencil
        button.btn.btn-default.my-2(
          v-if="my_client.is_group === true && $root.user.is_admin && !my_client.connection_id > 0",
          @click="showModal"
        ) {{ $t('template.connect_with_erp_of_subcontractor') }}
        button.btn.btn-default.my-2(
          v-if="my_client.connection_id > 0 && my_client.connection"
        ) 
          i.fa.fa-check(
            aria-hidden="true",
            v-if="my_client.connection.is_approved === true"
          ) {{ my_client.connection.url }}
          i.fa.fa-clock-o(aria-hidden="true", v-else) {{ my_client.connection.url }}
      .clearfix
    .info-bottom
      .row
        .col-12.col-lg-3(v-for="i in 3")
          .d-flex(
            v-for="attr in all_attributes.slice((i - 1) * attr_chunk_count, i * attr_chunk_count)"
          )
            span.text-muted {{ attr.name }}:
            span.dotter
            span.text-right(v-if="getAttrType(attr) == 'phone'")
              common_phone_number(:number="attr.value", :isLast="true")
            span.text-right(v-else) {{ attr.value }}
        .col-12.col-lg-3
          .d-flex
            textarea.form-control.with-gradient(
              :disabled="!my_client.can_be_updated",
              @change="autosave_note()",
              v-model="my_client.note",
              v-bind:placeholder="$t('client.Note')",
              style="height: 130px"
            ) {{ my_client.note }}
  #modal-create-connection.modal.fade(tabindex="-1", aria-hidden="true")
    .modal-dialog.modal-dialog-centered(role="document")
      .modal-content
        .modal-header {{ $t('template.connection') }}
        .modal-body
          .row
            .col-12
              .form-group
                label(for="groupName") {{ $t('template.connection_desc') }}
                #groupName.input-group
                  input.form-control(placeholder="contractor", v-model="url")
                  .input-group-prepend
                    .input-group-text .diga.pt
          .row.mt-3
            .col-12.text-center
              button.btn.btn-diga(
                v-on:click="createConnection",
                style="cursor: pointer"
              ) {{ $t('template.Add') }}
</template>

<script>
import common_phone_number from "@/components/callable_phone";
export default {
  props: ["client"],
  components: {
    common_phone_number,
  },
  data() {
    return {
      my_client: this.client,
      url: "",
      //                fb_message: '',
      //                message_fb_loading: false,
      //                vCard: '',
      //                client_email_link: '',
    };
  },
  methods: {
    createConnection() {
      this.$root.global_loading = true;
      this.$http
        .post("/api/connection", {
          url: this.url,
          client_id: this.my_client.id,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(
                this.$root.$t("template.connection_error"),
                this.$root.$t("template.Error")
              );
              this.$root.global_loading = false;
            } else {
              this.$toastr.s(
                this.$root.$t("client.Client_saved"),
                this.$root.$t("template.Success")
              );
              jQuery("#modal-create-connection").modal("hide");
              this.$emit("load_company");
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
    showModal() {
      jQuery("#modal-create-connection").modal("show");
    },
    autosave_note: function () {
      this.saveNote();
    },
    saveNote: function () {
      this.$http
        .post("/api/companies/" + this.my_client.id + "/save_note", {
          note: this.my_client.note,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error")); // this.$root.$t because of https://github.com/kazupon/vue-i18n/issues/184
            } else {
              this.$toastr.s(
                this.$root.$t("client.Note_saved"),
                this.$root.$t("template.Success")
              );
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
    getAttrType(attribute) {
      let type = "all";
      if (typeof attribute.type != "undefined" && attribute.type == "phone")
        type = "phone";
      return type;
    },
  },
  computed: {
    attr_chunk_count() {
      return Math.ceil(this.all_attributes.length / 3);
    },
    all_attributes() {
      let attrs = [];
      attrs.push({
        name: this.$root.$t("client.Client_name"),
        value: this.my_client.name,
      });
      attrs.push({
        name: this.$root.$t("client.Address_legal"),
        value: this.my_client.address_legal,
      });
      attrs.push({
        name: this.$root.$t("estimate.Postal_code"),
        value: this.my_client.postal_code,
      });
      attrs.push({
        name: this.$root.$t("dashboard.Region"),
        value: this.my_client.city,
      });
      attrs.push({
        name: this.$root.$t("client.Address_mailing"),
        value: this.my_client.address_mailing,
      });
      if (this.$root.settings.companies_show_nif == "1") {
        attrs.push({
          name: this.$root.$t("client.NIF"),
          value: this.my_client.nif,
        });
      }
      if (this.$root.settings.companies_show_checking_account == "1") {
        attrs.push({
          name: this.$root.$t("client.Checking_account"),
          value: this.my_client.checking_account,
        });
      }
      if (this.$root.settings.companies_show_correspondent_account == "1") {
        attrs.push({
          name: this.$root.$t("client.Correspondent_account"),
          value: this.my_client.correspondent_account,
        });
      }
      if (this.$root.settings.companies_show_bic == "1") {
        attrs.push({
          name: this.$root.$t("client.Bic"),
          value: this.my_client.bic,
        });
      }
      attrs.push({
        name: this.$root.$t("client.Phones"),
        value: this.my_client.phone,
        type: "phone",
      });
      attrs.push({
        name: this.$root.$t("client.Site"),
        value: this.my_client.site,
      });
      attrs.push({
        name: this.$root.$t("client.Email"),
        value: this.my_client.email,
      });
      attrs.push({
        name: this.$root.$t("client.Client_group"),
        value: this.my_client.client_group,
      });
      if (this.my_client.client_referrer) {
        attrs.push({
          name: this.$root.$t("client.Referrer"),
          value: this.my_client.client_referrer.title,
        });
      }
      if (this.my_client.referrer_note != "") {
        attrs.push({
          name: this.$root.$t("client.Referrer_note"),
          value: this.my_client.referrer_note,
        });
      }
      for (var pr in this.my_client.attributes_calculated) {
        var field = this.my_client.attributes_calculated[pr];
        if (field.show_in_card) {
          attrs.push({ name: field.name, value: field.value_calculated });
        }
      }
      return attrs;
    },
  },
};
</script>