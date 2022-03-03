<template lang="pug">
div(v-if="currentContact")
  client(
    v-if="$root.enable_companies && currentContact.client",
    :client="currentContact.client"
  )
  .diga-container.p-4
    h2 {{ isCreating ? $t('client.Contact_new') : $t('client.Contact_Edit') }}
    .row
      section.col-12.col-md-6
        fieldset.form-group(:class="{ 'has-error': errors.has('name') }")
          input.form-control(
            v-bind:placeholder="$t('client.Name')",
            name="name",
            v-validate="'required'",
            type="text",
            v-model="currentContact.name",
            v-bind:data-vv-as="$t('client.Name').toLowerCase()"
          )
          span.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
        fieldset.form-group
          input.form-control(
            v-bind:placeholder="$t('client.Surname')",
            name="surname",
            type="text",
            value="",
            v-model="currentContact.surname"
          )
        fieldset.form-group
          input.form-control(
            v-bind:placeholder="$t('client.Morada')",
            name="address",
            type="text",
            value="",
            v-model="currentContact.address"
          )
        fieldset.form-group
          input.form-control(
            v-bind:placeholder="$t('estimate.Postal_code')",
            name="postal_code",
            type="text",
            value="",
            v-model="currentContact.postal_code"
          )
        fieldset.form-group
          input.form-control(
            v-bind:placeholder="$t('dashboard.Region')",
            name="region",
            type="text",
            value="",
            v-model="currentContact.city"
          )
        fieldset.form-group(v-if="$root.enable_companies")
          v-select(
            :debounce="250",
            :on-search="get_companies_options",
            :on-change="company_select",
            v-model="selected",
            :options="companies",
            :placeholder="$t('client.Choose_company')"
          )
            template(slot="no-options") {{ $t('template.No_matching_options') }}
        fieldset.form-group
          label.radio-inline.mr-2
            input(
              name="sex",
              type="radio",
              value="1",
              v-model="currentContact.sex"
            )
            span.ml-1 {{ $t('client.Man') }}
          label.radio-inline
            input(
              name="sex",
              type="radio",
              value="0",
              v-model="currentContact.sex"
            )
            span.ml-1 {{ $t('client.Woman') }}
        fieldset.form-group
          .input-group.form-group(
            v-for="(k, v) in currentContact.client_contact_emails",
            :class="{ 'has-error': errors.has('email' + v) }"
          )
            .input-group-prepend
              .input-group-text
                i.fa.fa-envelope
            input.form-control(
              placeholder="Email",
              :name="'email' + v",
              type="text",
              v-validate="'email'",
              v-model="k.email"
            )
            .input-group-append
              button.btn.btn-outline-secondary(v-on:click="removeEmail(v)")
                i.fa.fa-close
          button.btn.btn-diga(v-on:click="newEmail()") {{ $t('client.Add_new_email') }}
        fieldset.form-group
          .input-group.form-group(
            v-for="(k, v) in currentContact.client_contact_phones"
          )
            .input-group-prepend
              .input-group-text
                i.fa.fa-phone
            input.form-control(
              v-bind:placeholder="$t('client.Phone_number')",
              type="text",
              v-model="k.phone_number"
            )
            .input-group-append
              button.btn.btn-outline-secondary(v-on:click="removePhone(v)")
                i.fa.fa-close
          button.btn.btn-diga(v-on:click="newPhone()") {{ $t('client.Add_new_phone') }}
        fieldset.form-group
          input.form-control(
            v-bind:placeholder="$t('client.NIF')",
            name="nif",
            type="text",
            value="",
            v-model="currentContact.nif"
          )
        fieldset.form-group
          input.form-control(
            v-bind:placeholder="$t('client.Profession')",
            type="text",
            value="",
            v-model="currentContact.profession"
          )
        fieldset.form-group(
          v-for="field in currentContact.attributes_calculated"
        )
          label {{ field.name }}
          input.form-control(v-if="field.type == 0", v-model="field.value")
          textarea.form-control(
            v-else-if="field.type == 1",
            v-model="field.value"
          )
          select.form-control(
            v-else-if="field.type == 2",
            v-model="field.value"
          )
            option(v-for="option in field.options", :value="option.id") {{ option.name }}
        fieldset.form-group
          p {{ $t('client.Person_type') }}:
          label.radio-inline.mr-2
            input(
              name="type",
              type="radio",
              :value="1",
              v-model="currentContact.contact_type"
            )
            span.ml-1 {{ $t('client.Legal_entity') }}
          label.radio-inline
            input(
              name="type",
              type="radio",
              :value="2",
              v-model="currentContact.contact_type"
            )
            span.ml-1 {{ $t('client.Individual') }}
        .row
          fieldset.form-group.col-6
            label {{ $t('client.Referrer') }}:
            select.form-control(v-model="currentContact.client_referrer_id")
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
              v-model="currentContact.referrer_note"
            )
        .row
          fieldset.form-group.col-6
            label {{ $t('client.Responsible') }}:
            select.form-control(v-model="currentContact.responsible_user_id")
              option(
                v-for="option in responsible_users",
                v-bind:value="option.id",
                v-text="option.name"
              )
          fieldset.form-group.col-6
        fieldset.form-group
          label {{ $t('client.Note') }}:
          textarea.form-control(name="note", v-model="currentContact.note")
      section.col-12.col-md-6
        p
          b {{ $t('client.Name') }}:
          span.ml-2 {{ currentContact.name }}
        p
          b {{ $t('client.Surname') }}:
          span.ml-2 {{ currentContact.surname }}
        p
          b {{ $t('client.Morada') }}:
          span.ml-2 {{ currentContact.address }}
        p
          b {{ $t('estimate.Postal_code') }}:
          span.ml-2 {{ currentContact.postal_code }}
        p
          b {{ $t('dashboard.Region') }}:
          span.ml-2 {{ currentContact.city }}
        p(v-if="$root.enable_companies")
          b {{ $t('client.Client_name') }}:
          span.ml-2(v-if="selected") {{ selected.label }}
        p
          b {{ $t('client.Sex') }}:
          span.ml-2(v-text="getSex()")
        p
          b {{ $t('client.Email') }}:
          span.ml-2(v-text="getEmails()")
        p
          b {{ $t('client.Phones') }}:
          span.ml-2(v-text="getPhones()")
        p
          b {{ $t('client.NIF') }}:
          span.ml-2 {{ currentContact.nif }}
        p
          b {{ $t('client.Profession') }}:
          span.ml-2 {{ currentContact.profession }}
        p(v-for="field in currentContact.attributes_calculated")
          b {{ field.name }}:
          template(v-if="field.type == 0")
            i.ml-2 {{ field.value }}
          template(v-if="field.type == 1")
            br
            i {{ field.value }}
          template(v-if="field.type == 2")
            i.ml-2 {{ field.options.find((e) => e.id == field.value).name }}
        p
          b {{ $t('client.Person_type') }}:
          span.ml-2(v-text="getPersonType()")
        p
          b {{ $t('client.Referrer') }}:
          i.ml-2(
            v-if="currentContact.client_referrer_id",
            v-text="referrersById[currentContact.client_referrer_id].title"
          )
        p
          b {{ $t('client.Referrer_note') }}:
          i.ml-2 {{ currentContact.referrer_note }}
        p
          b {{ $t('client.Responsible') }}
          i.ml-2(v-text="getResponsibleUserName()")
        p
          b {{ $t('client.Note') }}:
          br
          | {{ currentContact.note }}
    .row
      .col-12
        button.btn.btn-diga(v-on:click="save_contact()") {{ $t('template.Save') }}
</template>

<script>
import client from "../shared/client.vue";

import { mapGetters } from "vuex";

export default {
  props: ["id", "company_id"],
  components: {
    client,
  },
  data: function () {
    return {
      selected: null,
      companies: [],
      isCreating: true,
      currentContact: null, // newContact or existed company loaded from api
    };
  },
  methods: {
    load_company: function () {
      this.$root.global_loading = true;
      return new Promise((resolve, reject) => {
        this.$http.get("/api/companies/" + this.company_id).then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
              this.$root.global_loading = false;
              reject();
            } else {
              this.selected = { label: res.data.name, value: res.data.id };
              this.$root.global_loading = false;
              resolve(res.data);
            }
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
            this.$root.global_loading = false;
            reject();
          }
        );
      });
    },
    load_contact: function () {
      this.$root.global_loading = true;
      this.$http.get("/api/contacts/" + this.id).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            this.$root.global_loading = false;
          } else {
            this.currentContact = res.data;
            if (this.currentContact.client) {
              this.selected = {
                label: this.currentContact.client.name,
                value: this.currentContact.client.id,
              };
            }
            this.isCreating = false;
            this.$root.global_loading = false;
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
    },
    get_companies_options(search, loading) {
      loading(true);
      this.$http
        .get("/api/companies?format=json&limit=20&query=" + search)
        .then(
          (res) => {
            var processedData = [];
            let $this = this;
            res.data.rows.forEach(function (i) {
              processedData.push({ label: i.name, value: i.id });
            });
            this.companies = processedData;
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
    company_select(res) {
      this.currentContact.client_id = res == null ? null : res.value;
      this.selected = res;
    },
    save_contact() {
      this.$validator.validateAll().then((result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        }
        this.$root.global_loading = true;
        if (this.isCreating) {
          this.$http
            .post(
              "/api" + this.$root.contact_or_client_store(),
              this.currentContact
            )
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
                    this.$root.$t("client.Contact_saved"),
                    this.$root.$t("template.Success")
                  );
                  this.$router.push({
                    name: this.$root.contact_or_client_show_route(),
                    params: { id: res.data.contact_id },
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
            .patch(
              "/api" +
                this.$root.contact_or_client_show(this.currentContact.id),
              this.currentContact
            )
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
                    this.$root.$t("client.Contact_saved"),
                    this.$root.$t("template.Success")
                  );
                  this.$router.push({
                    name: this.$root.contact_or_client_show_route(),
                    params: { id: this.currentContact.id },
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
    getSex() {
      return [this.$root.$t("client.Woman"), this.$root.$t("client.Man")][
        this.currentContact.sex
      ];
    },
    getPersonType() {
      return [
        "",
        this.$root.$t("client.Legal_entity"),
        this.$root.$t("client.Individual"),
      ][this.currentContact.contact_type];
    },
    getPhones() {
      return this.currentContact.client_contact_phones
        .map(function (p) {
          return p.phone_number;
        })
        .join(", ");
    },
    newPhone(phone) {
      var the_phone = "";
      if (typeof phone != "undefined") the_phone = phone;
      this.currentContact.client_contact_phones.push({
        phone_number: the_phone,
      });
    },
    removePhone(i) {
      if (this.currentContact.client_contact_phones.length > 1)
        this.currentContact.client_contact_phones.splice(i, 1);
    },
    getEmails() {
      return this.currentContact.client_contact_emails
        .map(function (p) {
          return p.email;
        })
        .join(", ");
    },
    newEmail() {
      this.currentContact.client_contact_emails.push({ email: "" });
    },
    removeEmail(i) {
      if (this.currentContact.client_contact_emails.length > 1)
        this.currentContact.client_contact_emails.splice(i, 1);
    },
    contact_attributes_calculated() {
      let ca = this.$store.getters.getGlobalSettings.contact_attributes;
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
    getResponsibleUserName() {
      let usr = this.usersById[this.currentContact.responsible_user_id];
      return typeof usr != "undefined"
        ? usr.name
        : this.$root.$t("template.User_not_exists");
    },
  },
  computed: {
    ...mapGetters({
      responsible_users: "getNotRemovedUsers",
      usersById: "getUsersById",
      referrers: "getClientReferrers",
      referrersById: "getClientReferrersById",
    }),
  },
  async mounted() {
    if (this.id) {
      document.title =
        this.$root.global_settings.site_name +
        " | " +
        this.$root.$t("client.Contact_Edit");
      this.load_contact();
    } else {
      document.title =
        this.$root.global_settings.site_name +
        " | " +
        this.$root.$t("client.Contact_new");
      let company = null;
      if (this.company_id) {
        await this.load_company().then((c) => {
          company = c;
        });
      }
      let newContact = {
        name: "",
        surname: "",
        client_contact_emails: [],
        client_contact_phones: [],
        nif: "",
        sex: 0,
        profession: "",
        contact_type: 0,
        client_id: null,
        attributes_calculated: this.contact_attributes_calculated(),
        client_referrer_id: this.referrers[0].id,
        referrer_note: "",
        responsible_user_id: this.$root.user.id,
        note: "",
        client: company,
      };
      this.currentContact = Object.assign({}, newContact);

      if (typeof this.$route.query.caller_id != "undefined")
        this.newPhone(this.$route.query.caller_id);

      if (typeof this.$route.query.referrer_id != "undefined")
        this.currentContact.client_referrer_id = this.$route.query.referrer_id;
    }
  },
};
</script>