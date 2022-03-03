<style>
.add-on .input-group-btn > .btn {
  border-left-width: 0;
  left: -2px;
  -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
  box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
}
/* stop the glowing blue shadow */
.add-on .form-control:focus {
  box-shadow: none;
  -webkit-box-shadow: none;
  border-color: #cccccc;
}
.pic-bordered img {
  width: 200px;
  float: left;
}
</style>

<template lang="pug">
.diga-container.p-4
  .row(v-if="currentCompany")
    section.col-12.col-md-6
      h2 {{ isCreating ? $t('client.New_company') : $t('client.Edit_company') }}
      fieldset.form-group(:class="{ 'has-error': errors.has('name') }")
        input.form-control(
          v-bind:placeholder="$t('client.Client_name')",
          type="text",
          name="name",
          v-validate="'required'",
          v-model="currentCompany.name",
          v-bind:data-vv-as="$t('client.Name').toLowerCase()"
        )
        span.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
      fieldset.form-group.form-group(
        :class="{ 'has-error': errors.has('email') }"
      )
        .input-group
          .input-group-prepend
            .input-group-text
              i.fa.fa-envelope
          input.form-control(
            placeholder="Email",
            type="text",
            name="email",
            v-validate="'email'",
            v-model="currentCompany.email"
          )
        span.help-block(v-show="errors.has('email')") {{ errors.first('email') }}
      fieldset.form-group
        .input-group
          .input-group-prepend
            .input-group-text
              i.fa.fa-phone
          input.form-control(
            v-bind:placeholder="$t('client.Phone_number')",
            type="text",
            v-model="currentCompany.phone"
          )
      fieldset.form-group
        .input-group
          .input-group-prepend
            .input-group-text
              i.fa.fa-globe
          input.form-control(
            v-bind:placeholder="$t('client.Site')",
            type="text",
            v-model="currentCompany.site"
          )
      fieldset.form-group
        input.form-control(
          v-bind:placeholder="$t('client.NIF')",
          type="text",
          v-model="currentCompany.nif"
        )
      fieldset.form-group
        input.form-control(
          v-bind:placeholder="$t('client.Address_legal')",
          type="text",
          v-model="currentCompany.address_legal"
        )
      fieldset.form-group
        input.form-control(
          v-bind:placeholder="$t('estimate.Postal_code')",
          type="text",
          v-model="currentCompany.postal_code"
        )
      fieldset.form-group
        input.form-control(
          v-bind:placeholder="$t('dashboard.Region')",
          type="text",
          v-model="currentCompany.city"
        )
      fieldset.form-group
        input.form-control(
          v-bind:placeholder="$t('client.Address_mailing')",
          type="text",
          v-model="currentCompany.address_mailing"
        )
      fieldset.form-group
        input.form-control(
          v-bind:placeholder="$t('client.Checking_account')",
          type="text",
          v-model="currentCompany.checking_account"
        )
      fieldset.form-group
        input.form-control(
          v-bind:placeholder="$t('client.Correspondent_account')",
          type="text",
          v-model="currentCompany.correspondent_account"
        )
      fieldset.form-group
        input.form-control(
          v-bind:placeholder="$t('client.Bic')",
          type="text",
          v-model="currentCompany.bic"
        )
      fieldset.form-group(
        v-for="field in currentCompany.attributes_calculated"
      )
        label {{ field.name }}
        input.form-control(v-if="field.type == 0", v-model="field.value")
        textarea.form-control(
          v-else-if="field.type == 1",
          v-model="field.value"
        )
        select.form-control(v-else-if="field.type == 2", v-model="field.value")
          option(v-for="option in field.options", :value="option.id") {{ option.name }}
      //.row
        .col-6
             fieldset.form-group
                p {{ $t("client.state") }}:
                label.checkbox-inline
                    input(type="checkbox", value="1", v-model="vip")
                    | VIP
      fieldset.form-group
        input.form-control(
          v-bind:placeholder="$t('client.Client_group')",
          type="text",
          v-model="currentCompany.client_group"
        )
      .row
        fieldset.form-group.col-6
          label {{ $t('client.Referrer') }}:
          select.form-control(v-model="currentCompany.client_referrer_id")
            option(
              v-for="option in referrers",
              v-bind:value="option.id",
              v-text="option.title"
            )
        fieldset.form-group.col-6
          label {{ $t('client.Referrer_note') }}:
          input.form-control(
            v-bind:placeholder="$t('client.Referrer_note_placeholder')",
            type="text",
            v-model="currentCompany.referrer_note"
          )
      fieldset.form-group
        label {{ $t('client.Note') }}:
        textarea.form-control(
          v-bind:placeholder="$t('client.Note')",
          v-model="currentCompany.note"
        )
    section.col-12.col-md-6(style="padding-top: 25px; height: 100%")
      section.col-12
        p
          b {{ $t('client.Client_name') }}:
          span.ml-2 {{ currentCompany.name }}
        p
          b {{ $t('client.Email') }}:
          span.ml-2 {{ currentCompany.email }}
        p
          b {{ $t('client.Phones') }}:
          i.ml-2 {{ currentCompany.phone }}
        p
          b {{ $t('client.Site') }}:
          i.ml-2 {{ currentCompany.site }}
        p
          b {{ $t('client.NIF') }}:
          span.ml-2 {{ currentCompany.nif }}
        p
          b {{ $t('client.Address_legal') }}:
          span.ml-2 {{ currentCompany.address_legal }}
        p
          b {{ $t('estimate.Postal_code') }}:
          span.ml-2 {{ currentCompany.postal_code }}
        p
          b {{ $t('dashboard.Region') }}:
          span.ml-2 {{ currentCompany.city }}
        p
          b {{ $t('client.Address_mailing') }}:
          i.ml-2 {{ currentCompany.address_mailing }}
        p
          b {{ $t('client.Checking_account') }}:
          i.ml-2 {{ currentCompany.checking_account }}
        p
          b {{ $t('client.Correspondent_account') }}:
          i.ml-2 {{ currentCompany.correspondent_account }}
        p
          b {{ $t('client.Bic') }}:
          i.ml-2 {{ currentCompany.bic }}
        p(v-for="field in currentCompany.attributes_calculated")
          b {{ field.name }}:
          template(v-if="field.type == 0")
            i.ml-2 {{ field.value }}
          template(v-if="field.type == 1")
            br
            i {{ field.value }}
          template(v-if="field.type == 2")
            i.ml-2 {{ field.options.find((e) => e.id == field.value).name }}
        p
          b {{ $t('client.Client_group') }}:
          i.ml-2 {{ currentCompany.client_group }}
        p
          b {{ $t('client.Referrer') }}:
          i.ml-2(
            v-if="currentCompany.client_referrer_id",
            v-text="referrersById[currentCompany.client_referrer_id].title"
          )
        p
          b {{ $t('client.Referrer_note') }}:
          i.ml-2 {{ currentCompany.referrer_note }}
        //p
          b VIP:
          i.ml-2(v-text="getVip()")
        p
          b {{ $t('client.Note') }}:
          br
          | {{ currentCompany.note }}
  .row
    .col-12
      button.btn.btn-diga(v-on:click="save_client()") {{ $t('template.Save') }}
</template>

<script>
import { mapGetters } from "vuex";

export default {
  data: function () {
    return {
      isCreating: true,
      currentCompany: null, // newCompany or existed company loaded from api
    };
  },
  props: ["id", "group_id"],
  methods: {
    load_company: function () {
      this.$root.global_loading = true;
      this.$http.get("/api/companies/" + this.id).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.currentCompany = res.data;
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
    save_client: function () {
      this.$validator.validateAll().then((result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        }
        let payload = Object.assign({}, this.currentCompany);
        this.$root.global_loading = true;
        if (this.isCreating) {
          this.$http.post("/api/companies/", payload).then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
                this.$root.global_loading = false;
              } else {
                this.$toastr.s(
                  this.$root.$t("client.Client_saved"),
                  this.$root.$t("template.Success")
                );
                this.$router.push({
                  name: "company_show",
                  params: { id: res.data.id },
                });
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
        } else {
          this.$http
            .patch("/api/companies/" + this.currentCompany.id, payload)
            .then(
              (res) => {
                if (res.data.errcode == 1) {
                  this.$toastr.e(
                    res.data.errmess,
                    this.$root.$t("template.Error")
                  );
                  this.$root.global_loading = false;
                } else {
                  this.$toastr.s(
                    this.$root.$t("client.Client_saved"),
                    this.$root.$t("template.Success")
                  );
                  this.$router.push({
                    name: "company_show",
                    params: { id: this.currentCompany.id },
                  });
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
        }
      });
    },
    getVip() {
      return this.vip
        ? this.$root.$t("client.yes")
        : this.$root.$t("client.no");
    },
    getPersonType() {
      return [
        "",
        this.$root.$t("client.Legal_entity"),
        this.$root.$t("client.Individual"),
      ][this.contact_type];
    },
    //            getPhones(){
    //                return this.client_contact_phones.map(function(p){ return p.phone_number }).join(', ');
    //            },
    //            newPhone() {
    //                this.client_contact_phones.push({phone_number: ''});
    //            },
    //            removePhone(i) {
    //                if(this.client_contact_phones.length > 1)
    //                    this.client_contact_phones.splice(i, 1);
    //            }
    client_attributes_calculated() {
      let ca = this.$store.getters.getGlobalSettings.client_attributes;
      ca.forEach(function (currentValue, index, array) {
        if (currentValue.type == 2) {
          currentValue.value = currentValue.options[0].id;
          currentValue.value_calculated = currentValue.options[0].name;
        } else {
          currentValue.value = "";
          currentValue.value_calculated = "";
        }
      });
      return ca;
    },
  },
  computed: {
    ...mapGetters({
      referrers: "getClientReferrers",
      referrersById: "getClientReferrersById",
      groups: "getGroups",
      groupsById: "getGroupsById",
    }),
  },
  mounted() {
    if (this.id) {
      document.title =
        this.$root.global_settings.site_name +
        " | " +
        this.$root.$t("client.Edit_company");
      this.load_company();
    } else {
      document.title =
        this.$root.global_settings.site_name +
        " | " +
        this.$root.$t("client.New_company");

      let newCompany = {
        name: "",
        email: "",
        phone: "",
        site: "",
        nif: "",
        address_legal: "",
        address_mailing: "",
        checking_account: "",
        correspondent_account: "",
        bic: "",
        attributes_calculated: this.client_attributes_calculated(),
        client_group: "",
        client_referrer_id: this.referrers[0].id,
        referrer_note: "",
        note: "",
        is_group: false,
      };

      if (this.group_id > 0) {
        let group = this.groupsById[this.group_id];
        if (group !== null) {
          newCompany.name = group.name;
          newCompany.group_id = this.group_id;
        }
        newCompany.is_group = true;
      }
      this.currentCompany = Object.assign({}, newCompany);
    }
  },
};
</script>