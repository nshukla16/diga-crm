<style>
#qrcode img {
  width: 150px;
}
.pic-bordered img {
  width: 200px;
  float: left;
}
.light-link {
  color: #bebebe;
}
.modal-send-email .modal-dialog {
  max-width: 1026px;
}
</style>

<template lang="pug">
#contact_info.mb-3
  .diga-container
    .float-sm-left.mr-2
      #qrcode.d-none.d-sm-block
      button.btn.green(
        v-on:click="download_contact",
        style="margin-top: 5px; width: 100%; height: 30px; padding: 0; font-size: 12px"
      ) {{ $t('client.Download_contact') }}
    div(style="overflow: hidden")
      .info-top
        .caption.float-left
          router-link#current_client_name.contacts-list.light-link.active(
            :to="{ name: 'contact_show', params: { id: contact.id } }"
          )
            | {{ $root.fullName(contact) }}
            template(v-if="contact.source_id > 0") {{ '(' + $t('template.from_general_contractor') + ')' }}
        .float-right(style="font-size: 16px")
          router-link.color2-text.color2-border.envelope-button(
            v-if="$root.enable_companies && contact.client_id",
            :to="{ name: 'company_show', params: { id: contact.client_id } }",
            style="padding: 3px 10px"
          ) {{ $t('client.All_contacts') }}
          router-link.btn.btn-circle.btn-default-1(
            v-if="contact.can_be_updated",
            :to="{ name: 'contact_edit', params: { id: contact.id } }"
          )
            i.fa.fa-pencil
        .clearfix
      .info-bottom
        .row
          .col-12.col-lg-4(v-for="i in 2")
            .d-flex(
              v-for="attr in all_attributes.slice((i - 1) * attr_chunk_count, i * attr_chunk_count)"
            )
              span.text-muted {{ attr.name }}:
              span.dotter
              span.text-right(v-if="getAttrType(attr) == 'phone'")
                common_phone_number(
                  v-for="(phone, index) in attr.value",
                  :key="phone.id",
                  :number="phone.phone_number",
                  :isLast="index === attr.value.length - 1"
                )
              span.text-right(v-else) {{ attr.value }}
            .d-flex(v-if="i == 2")
              .w-50.pr-1(v-if="contact.client_contact_emails.length > 0")
                button.color2-text.color2-border.envelope-button.mt-2.w-100(
                  v-on:click="send_email"
                )
                  i.fa.fa-envelope.mr-2
                  | {{ $t('client.Send_email') }}
              .w-50.pr-1(
                v-if="$store.getters['getZadarmaEnabled'] && getPhones().length > 0 && $root.user.zadarma_internal_phonecode"
              )
                button.color2-text.color2-border.envelope-button.mt-2.w-100(
                  v-on:click="open_phones_window"
                )
                  i.fa.fa-phone.mr-2
                  | {{ $t('zadarma.Call') }}
              .w-50.pl-1(v-if="contact.fb_psid")
                button.color2-text.color2-border.envelope-button.mt-2.w-100(
                  v-on:click="open_send_fb"
                )
                  i.fa.fa-facebook-square.mr-2
                  | {{ $t('client.Send_fb') }}

            //div.d-flex(v-if="card.client.client_referrer")
              span.text-muted {{ $t("client.Referrer") }}:
              span.dotter
              span {{ card.client.client_referrer.title }}
            //div.d-flex(v-if="card.client.referrer_note != ''")
              span.text-muted {{ $t("client.Referrer_note") }}:
              span.dotter
              span {{ card.client.referrer_note }}

          .col-12.col-lg-4
            textarea.form-control.with-gradient(
              :disabled="!contact.can_be_updated",
              @change="autosave_note()",
              v-model="my_contact.note",
              v-bind:placeholder="$t('client.Note')",
              style="height: 130px"
            )
  #modal-send-fb.modal.fade(tabindex="-1", aria-hidden="true")
    .modal-dialog(role="document")
      .modal-content
        .modal-header
          h5.modal-title {{ $t('client.Send_fb') }}
          button.close(type="button", data-dismiss="modal", aria-label="Close")
            span(aria-hidden="true") &times;
        .modal-body
          fieldset.form-group(
            :class="{ 'has-error': errors.has('fb_message') }"
          )
            textarea.form-control(
              type="text",
              name="fb_message",
              v-validate="'required'",
              v-model="fb_message",
              v-bind:data-vv-as="$t('client.Message').toLowerCase()"
            )
            span.help-block(v-show="errors.has('fb_message')") {{ errors.first('fb_message') }}
        .modal-footer(style="justify-content: center")
          button.btn.green(v-on:click="send_fb")
            span(v-show="!message_fb_loading") OK
            div(v-show="message_fb_loading")
              .loader.sm-loader
  #modal-send-email.modal.fade.modal-send-email(
    tabindex="-1",
    aria-hidden="true"
  )
    .modal-dialog.modal-dialog-centered(role="document")
      .modal-content
        div
          iframe#client_email_iframe(
            :src="'/webmail/#single-compose/to/mailto:' + (contact.client_contact_emails.length > 0 ? contact.client_contact_emails[0].email : '')",
            name="client_email_iframe",
            @load="email_iframe_loaded",
            style="width: 100%; min-height: 736px; border: 0px"
          )
  #modal-phones-window.modal.fade(tabindex="-1", aria-hidden="true")
    .modal-dialog(role="document")
      .modal-content
        .modal-header
          h5.modal-title {{ $t('zadarma.Choose_phone') }}
          button.close(type="button", data-dismiss="modal", aria-label="Close")
            span(aria-hidden="true") &times;
        .modal-body
          fieldset.form-group
            select.form-control(v-model="phone_to_call")
              option(
                v-for="(option, ind) in contact.client_contact_phones",
                v-bind:value="ind",
                v-text="option.phone_number"
              )
        .modal-footer(style="justify-content: center")
          button.btn.green(v-on:click="make_call")
            span(v-show="!calling") {{ $t('zadarma.Call') }}
            div(v-show="calling")
              .loader.sm-loader
</template>

<script>
// this module is used in contact/index, contact/_form, card/services/_form, client/show
// index, create, edit, show in ContactController
// create, edit in ServiceController
// show in ClientController
import moment from "moment";
import QRCode from "./../../qrcode";

import common_phone_number from "@/components/callable_phone";
import { mapGetters } from "vuex";

export default {
  props: ["contact"],
  components: {
    common_phone_number,
  },
  data() {
    return {
      my_contact: this.contact,
      fb_message: "",
      message_fb_loading: false,
      phone_to_call: 0,
      calling: false,
      vCard: "",
      //                client_email_link: '',
      qrcode: null,
    };
  },
  mounted() {
    this.gen_vcard();
    this.qrcode = new QRCode(document.getElementById("qrcode"), {
      text: this.vCard,
      width: 150,
      height: 150,
      colorDark: "#000000",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.L,
    });
  },
  methods: {
    send_email() {
      this.$root.global_loading = true;
      this.$http.post("/api/mail/login").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            jQuery("#modal-send-email").modal("show");
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
    open_send_fb() {
      jQuery("#modal-send-fb").modal("show");
    },
    send_fb() {
      this.$validator.validateAll().then((result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        }
        this.message_fb_loading = true;
        this.$http
          .post("/api/contacts/" + this.contact.id + "/fb_message", {
            message: this.fb_message,
          })
          .then(
            (res) => {
              this.message_fb_loading = false;
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
              } else {
                let comment = {
                  type_id: 20,
                  user_id: this.$root.user.id,
                  created_at: moment(new Date()).format("YYYY-MM-DD HH:mm:ss"),
                  message: this.fb_message,
                };
                this.fb_message = "";
                this.$bus.$emit("system_message", comment);
                this.$toastr.s(
                  this.$root.$t("client.Message_sent"),
                  this.$root.$t("template.Success")
                );
                jQuery("#modal-send-fb").modal("hide");
              }
            },
            (res) => {
              this.message_fb_loading = false;
              this.$toastr.e(
                this.$root.$t("template.Server_error"),
                this.$root.$t("template.Error")
              );
            }
          );
      });
    },
    email_iframe_loaded: function () {
      let $this = this;
      jQuery("#client_email_iframe").ready(function () {
        setTimeout(function () {
          // Knockout.js observable subscribe
          window.frames[
            "client_email_iframe"
          ].App.Screens.oScreens.information.Model.reportVisible.subscribe(
            function (newValue) {
              if (newValue) {
                // need some time to send ajax to save mail to history
                $this.timerId = setInterval(function () {
                  // if outcoming email history done
                  if (!window.frames["client_email_iframe"].erp_email_sends) {
                    clearInterval($this.timerId);
                    // Message sent
                    jQuery("#modal-send-email").modal("hide");
                    $this.$toastr.s(
                      $this.$root.$t("client.Message_sent"),
                      $this.$root.$t("template.Success")
                    );
                  }
                }, 100);
              }
            }
          );
        }, 500);
      });
    },
    getPersonType() {
      return [
        "",
        this.$root.$t("client.Legal_entity"),
        this.$root.$t("client.Individual"),
      ][this.card.contact_type];
    },
    //            edit_contact_url() {
    //                return this.$root.contact_or_client_edit(this.contact.id);
    //            },
    getPhones() {
      return this.contact.client_contact_phones;
    },
    getSex() {
      return this.my_contact.sex == 1
        ? this.$root.$t("client.Man")
        : this.$root.$t("client.Woman");
    },
    created() {
      return moment(new Date(this.created_at)).format("DD.MM.YYYY HH:mm");
    },
    totalServices() {
      return typeof this.client.services.length === "number"
        ? this.client.services.length
        : 0;
    },
    totalEvents() {
      return this.contact.client.events.length;
    },
    // QR
    download_contact: function () {
      var link = document.createElement("a");
      link.download = "contact.vcf";
      link.href =
        "data:text/vcard;base64," +
        btoa(unescape(encodeURIComponent(this.vCard)));
      link.click();
      console.log(this.vCard);
    },
    gen_vcard: function () {
      this.vCard = [
        "BEGIN:VCARD",
        "VERSION:4.0",
        "N:" +
          (this.contact.surname ? this.contact.surname : "") +
          ";" +
          (this.contact.name ? this.contact.name : ""),
        "FN:" +
          (this.contact.name ? this.contact.name : "") +
          ";" +
          (this.contact.surname ? this.contact.surname : ""),
        "client_contact_phones" in this.contact
          ? this.contact.client_contact_phones
              .map((p) => "TEL;TYPE=WORK:" + p.phone_number)
              .join("\r\n")
          : "TEL;TYPE=WORK:",
        "EMAIL;PREF;INTERNET:" +
          (this.contact.client_contact_emails
            ? this.contact.client_contact_emails.map((e) => e.email).join(", ")
            : ""),
        "ORG:" +
          (this.contact.services[0]
            ? this.$root.service_number(this.contact.services[0])
            : ""),
        "NOTE:" +
          ("services" in this.contact
            ? this.contact.services.map(this.$root.service_number).join(", ")
            : ""),
        "END:VCARD",
      ].join("\r\n");
    },
    autosave_note: function () {
      this.saveNote();
    },
    saveNote: function () {
      this.$http
        .post(
          "/api" +
            this.$root.contact_or_client_show(this.contact.id) +
            "/save_note",
          { note: this.my_contact.note }
        )
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
    open_phones_window() {
      if (this.contact.client_contact_phones.length > 1)
        jQuery("#modal-phones-window").modal("show");
      else {
        let phone_number =
          this.contact.client_contact_phones[this.phone_to_call].phone_number;
        if (confirm(phone_number + " - " + this.$root.$t("zadarma.Call")))
          this.make_call();
      }
    },

    make_call() {
      let phone_number =
        this.contact.client_contact_phones[this.phone_to_call].phone_number;

      this.calling = true;
      let $this = this;

      this.$http
        .post("/api/zadarma/callback", { phone_number: phone_number })
        .then((res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            $this.calling = false;
          } else {
            setTimeout(function () {
              $this.calling = false;
            }, 5000);
          }
        });
    },
    getAttrType(attribute) {
      let type = "all";
      if (typeof attribute.type != "undefined" && attribute.type == "phone")
        type = "phone";
      return type;
    },
  },
  computed: {
    ...mapGetters({
      usersById: "getUsersById",
    }),
    not_main_contacts: function () {
      return this.client.client_contacts.filter(function (contact) {
        return !contact.is_main_contact;
      });
    },
    main_contact: function () {
      return this.client.client_contacts.filter(function (contact) {
        return contact.is_main_contact;
      })[0];
    },
    attr_chunk_count() {
      return Math.ceil(this.all_attributes.length / 2);
    },
    all_attributes() {
      let attrs = [];
      attrs.push({
        name: this.$root.$t("client.Full_Name"),
        value: this.$root.fullName(this.my_contact),
      });
      if (this.my_contact.surname != "" && this.my_contact.surname != null) {
        attrs.push({
          name: this.$root.$t("client.Surname"),
          value: this.my_contact.surname,
        });
      }
      if (this.my_contact.address != "" && this.my_contact.address != null) {
        attrs.push({
          name: this.$root.$t("client.Morada"),
          value: this.my_contact.address,
        });
      }
      if (
        this.my_contact.postal_code != "" &&
        this.my_contact.postal_code != null
      ) {
        attrs.push({
          name: this.$root.$t("estimate.Postal_code"),
          value: this.my_contact.postal_code,
        });
      }
      if (this.my_contact.city != "" && this.my_contact.city != null) {
        attrs.push({
          name: this.$root.$t("dashboard.Region"),
          value: this.my_contact.city,
        });
      }
      if (this.my_contact.sex) {
        attrs.push({ name: this.$root.$t("client.Sex"), value: this.getSex() });
      }
      if (this.getPhones() != "") {
        attrs.push({
          name: this.$root.$t("client.Phones"),
          value: this.getPhones(),
          type: "phone",
        });
      }
      if (this.my_contact.client_contact_emails.length > 0) {
        attrs.push({
          name: "E-mail",
          value: this.my_contact.client_contact_emails
            .map((e) => e.email)
            .join(", "),
        });
      }
      if (this.my_contact.nif) {
        attrs.push({
          name: this.$root.$t("client.NIF"),
          value: this.my_contact.nif,
        });
      }
      if (this.my_contact.profession != "") {
        attrs.push({
          name: this.$root.$t("client.Profession"),
          value: this.my_contact.profession,
        });
      }
      attrs.push({
        name: this.$root.$t("client.DATA_DO_PRIMEIRO_CONTACTO"),
        value: this.my_contact.created_at,
      });
      if (this.my_contact.client_referrer) {
        attrs.push({
          name: this.$root.$t("client.Referrer"),
          value: this.my_contact.client_referrer.title,
        });
      }
      if (this.my_contact.referrer_note != "") {
        attrs.push({
          name: this.$root.$t("client.Referrer_note"),
          value: this.my_contact.referrer_note,
        });
      }
      if (this.my_contact.responsible_user_id != null) {
        attrs.push({
          name: this.$root.$t("client.Responsible"),
          value: this.usersById[this.my_contact.responsible_user_id].name,
        });
      }
      for (var pr in this.my_contact.attributes_calculated) {
        var field = this.my_contact.attributes_calculated[pr];
        if (field.show_in_card) {
          attrs.push({ name: field.name, value: field.value_calculated });
        }
      }
      return attrs;
    },
  },
};
</script>