<style>
#services-list .btn-group {
  width: 100%;
}
.modal-mail .modal-dialog {
  max-width: 1026px;
}
/**/
.fullwidth {
  width: 100%;
}
/**/
.service {
  border-radius: 7px;
  margin-top: 5px;
  margin-left: 5px;
}
.panel-heading {
  padding: 7px;
}
.panel-collapse {
  padding: 20px;
}
button.btn-1 {
  border: 1px solid transparent;
  border-radius: 0.25rem;
  float: right;
  margin-left: 3px;
  cursor: pointer;
}
.service-selected {
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
}
</style>

<template lang="pug">
.service.with-gradient
  .panel-heading
    .panel-title
      a.accordion-toggle.collapsed(
        aria-expanded="false",
        data-toggle="collapse",
        data-parent="#services-list",
        v-bind:href="'#service_' + service.id",
        v-on:click="status_to_center",
        style="font-size: 13pt"
      )
        div(style="width: 80%; display: inline-block; vertical-align: middle")
          .status-icon(
            v-bind:style="{ 'background-color': current_state ? current_state.color : '#f5f5f5' }"
          )
            i(v-bind:class="['fa', current_state ? current_state.icon : null]")
          | {{ $t('client.Service') }} №&nbsp;
          b.estimate_number {{ $root.service_number(service) }}
          span(
            v-if="$root.global_settings.company_type == 2",
            v-html="' (' + get_pay_info() + ')'"
          )
          | :&nbsp {{ service.name }}
        .date(
          style="width: 20%; display: inline-block; vertical-align: middle"
        ) {{ service.created_at }}
  .panel-collapse.collapse(
    area-expanded="false",
    style="height: 0px",
    v-bind:id="'service_' + service.id"
  )
    .panel-body
      div(v-if="service.groups.length")
        b.mr-2 {{ $t('template.contractors_and_teams') }}:
        span(v-for="g in service.groups") {{ g.group.name + ' ' }}
      template(v-else)
        div(v-if="contractors_with_estimates")
          b.mr-2 {{ $t('template.contractors_and_teams') }}:
          | {{ contractors_with_estimates }}
      div
        b.mr-2 {{ $t('client.Contact') }}:
        | {{ $root.fullName(service.client_contact) }}
      div(v-if="service.service_type")
        b.mr-2 {{ $t('client.Service_type') }}:
        | {{ service.service_type.name }}
      div
        b.mr-2 {{ $t('client.Morada') }}:
        | {{ service.address }}
      div(v-if="service.responsible_user_id")
        b.mr-2 {{ $t('client.Responsavel') }}:
        | {{ users_by_id[service.responsible_user_id].name }}
      div(v-if="$root.user.can_see_prices")
        div(v-if="service.estimate_summ")
          b.mr-2 {{ $t('client.Estimate_summ') }}:
          | {{ service.estimate_summ }} {{ $root.current_currency.symbol }}
        div(v-if="service.paid_summ")
          b.mr-2 {{ $t('client.Paid_summ') }}:
          | {{ service.paid_summ }} {{ $root.current_currency.symbol }}
      div(v-if="service.service_priority")
        b.mr-2 {{ $t('client.Prioridade') }}:
        | {{ service.service_priority.name }}
      div(v-if="service.note")
        b.mr-2 {{ $t('client.Note') }}:
        | {{ service.note }}
      template
        div(v-if="!current_state_in_scope")
          b.mr-2 {{ $t('client.state') }}:
          span(v-if="current_state") {{ current_state.name }}
        process(
          v-else,
          :ref="'process-' + _uid",
          :scopes="null",
          :mystates="mystates",
          :current_order="current_state ? current_state.order : null",
          :current_id="current_state ? current_state.id : null",
          :p_editable="!editable || !$root.can_with_service('update', service)",
          v-on:activate_state="activate_state"
        )
      div(style="height: 24px")
        router-link.btn.btn-diga.btn-sm(
          v-if="$root.can_with_service('update', service)",
          :to="{ name: 'service_edit', params: { id: service.id }, query: { contact_id: service.client_contact_id } }"
        )
          i.fa.fa-pencil
        button.btn-diga.btn-sm.float-right.ml-2(
          v-if="$root.module_enabled('project') && service.project_id",
          v-on:click="open_project"
        )
          i.fa.fa-briefcase
        button.btn-diga.btn-sm.float-right.ml-2(
          v-if="$root.module_enabled('estimate') && $root.can_do('estimates', 'read') != 0",
          v-on:click="open_estimates"
        )
          i.fa.fa-list
        button.btn-diga.btn-sm.float-right.ml-2(v-on:click="open_attachments")
          i.fa.fa-files-o
        button.btn-diga.btn-sm.float-right.ml-2(
          v-if="$root.can_with_service('update', service)",
          v-on:click="additional_service"
        )
          i.fa.fa-plus
        button.btn-diga.btn-sm.float-right.ml-2(v-on:click="open_extra")
          i.fa.fa-ellipsis-h 
        //- button.btn-diga.btn-sm.float-right.ml-2(v-on:click="enable_chat")
        //-     i.fa.fa-comment
        //- button.btn-diga.btn-sm.float-right.ml-2(v-on:click="open_finances" v-if="$root.module_enabled('estimate') && $root.can_do('estimates', 'read') != 0" style="width: 35px;")
        //-     i.fa.fa-dollar 
        //- button.btn-diga.btn-sm.float-right.ml-2(v-on:click="open_subcontractors" v-if="$root.module_enabled('estimate') && $root.can_do('estimates', 'read') != 0")
        //-     i.fa.fa-users
        //- button.btn-diga.btn-sm.float-right.ml-2(v-on:click="open_expences" v-if="$root.module_enabled('expences') && $root.can_do('expences', 'read') != 0" )
        //-     i.fa.fa-truck
        //- button.btn-diga.btn-sm.float-right.ml-2(v-on:click="open_estimate_workers" v-if="$root.module_enabled('expences') && $root.can_do('expences', 'read') != 0" )
        //-     i.fa.fa-clock-o
        //- button.btn-diga.btn-sm.float-right.ml-2(v-on:click="open_estimate_materials" v-if="$root.module_enabled('expences') && $root.can_do('expences', 'read') != 0" )
        //-     i.fa.fa-paint-brush

  // MAKE IT AS COMPONENT!
  .modal.fade.modal-mail(
    :id="'modal-file-' + service.id",
    tabindex="-1",
    aria-hidden="true"
  )
    .modal-dialog.modal-dialog-centered(role="document")
      .modal-content
        .modal-body(style="text-align: center")
          .mb-2 {{ $t('client.Lets_upload_file_to_gd') }}
          vue-core-image-upload(
            :class="['btn', 'btn-diga']",
            @imageuploading="imageuploading",
            @imageuploaded="imageuploaded",
            @errorhandle="imageerror",
            :headers="{ Authorization: $root.access_token }",
            :extensions="'*'",
            :inputAccept="'*'",
            :max-file-size="$root.max_file_size",
            :text="$t('estimate.Upload_document')",
            url="/api/file_upload"
          )
          div(v-show="loading")
            .loader.sm-loader
  // MAKE IT AS COMPONENT!
  .modal.fade(
    :id="'modal-reason-' + service.id",
    tabindex="-1",
    aria-hidden="true"
  )
    .modal-dialog.modal-dialog-centered(role="document")
      .modal-content
        .modal-header(v-if="reason_state") {{ $t('client.state') + ' "' + reason_state.name + '"' }}
        .modal-body
          div {{ $t('client.Status_Description') }}:
          textarea(v-model="reason_text", style="width: 100%")
          div(style="text-align: center")
            button.btn.green(v-on:click="reason_ok") OK
  // MAKE IT AS COMPONENT!
  .modal.fade.modal-mail(
    :id="'modal-mail-' + service.id",
    tabindex="-1",
    aria-hidden="true"
  )
    .modal-dialog.modal-dialog-centered(role="document")
      .modal-content
        div
          iframe#email_iframe(
            v-if="email_link",
            :src="email_link",
            name="email_iframe",
            @load="email_service_iframe_loaded",
            style="width: 100%; min-height: 736px; border: 0px"
          )
  // MAKE IT AS COMPONENT!
  #modal-open-file.modal.fade.modal-mail(tabindex="-1", aria-hidden="true")
    .modal-dialog.modal-dialog-centered(role="document")
      .modal-content
        .modal-body(style="text-align: center")
          div {{ $t('client.Open_file_description') }}
          button.btn.btn-diga(v-on:click="open_url_clicked") {{ $t('template.Open') }}

  //- chat_access(:service="service")
  finance(
    ref="finance",
    v-if="$root.can_do('estimates', 'read') != 0",
    :service="service",
    @update_pay_stage="update_pay_stage",
    @update_pay_stage_valor="update_pay_stage_valor",
    @update_pay_stage_vat="update_pay_stage_vat",
    @update_pay_stage_total="update_pay_stage_total"
  )
  //- subcontractors(v-if="$root.can_do('estimates', 'read') != 0" :service="service")
  //- invoice(v-if="$root.can_do('estimates', 'read') != 0" :service="service" :pay_stage="pay_stage" :pay_stage_valor="pay_stage_valor" :pay_stage_vat="pay_stage_vat" :pay_stage_total="pay_stage_total" :client_contact="service.client_contact" @update_email_link="update_email_link" @update_emailResolve="update_emailResolve" @update_invoice="update_invoice")
  //- estimate_group_workers(v-if="$root.can_do('estimates', 'read') != 0" :service="service")
  //- estimate_group_materials(v-if="$root.can_do('estimates', 'read') != 0" :service="service")

  .modal.fade(
    :id="'modal-additional_features_' + service.id",
    tabindex="-1",
    aria-hidden="true"
  )
    .modal-dialog.modal-lg(role="document")
      .modal-content
        .modal-header
          h5.modal-title {{ $t('client.Extra') }}
        .modal-body
          .card
            .card-body
              router-link(
                target="_blank",
                v-if="$root.can_do('estimates', 'read') != 0",
                :to="{ name: 'estimate_group_materials', params: { id: service.id } }"
              ) 
                i.fa.fa-paint-brush(style="font-size: 30px")
                span(style="font-size: 30px; margin-left: 20px") {{ $t('template.Material_consumption') }}
          .card
            .card-body 
              router-link(
                target="_blank",
                v-if="$root.can_do('estimates', 'read') != 0",
                :to="{ name: 'estimate_group_workers', params: { id: service.id } }"
              ) 
                i.fa.fa-clock-o(style="font-size: 30px")
                span(style="font-size: 30px; margin-left: 20px") {{ $t('estimate.Mao_de_obra') }}
          .card(
            v-if="$root.user.can_see_financial_calendar === true && has_contractors === true"
          )
            .card-body 
              router-link(
                target="_blank",
                v-if="$root.can_do('estimates', 'read') != 0",
                :to="{ name: 'subcontractors', params: { id: service.id } }"
              ) 
                i.fa.fa-users(style="font-size: 30px")
                span(style="font-size: 30px; margin-left: 20px") {{ $t('template.contractors') }}
          .card(v-if="service.source_id > 0")
            .card-body 
              a(@click="open_general_contractor_modal") 
                i.fa.fa-suitcase(style="font-size: 30px")
                span(style="font-size: 30px; margin-left: 20px") {{ $t('template.general_contractor') }}
          .card(
            v-if="$root.user.can_see_financial_calendar === true && has_contractors === false"
          )
            .card-body 
              a(
                target="_blank",
                v-if="$root.can_do('estimates', 'read') != 0",
                @click="showContractorsModal"
              ) 
                i.fa.fa-users(style="font-size: 30px")
                span(style="font-size: 30px; margin-left: 20px") {{ $t('template.add_service_to_contractor') }}
          .card(v-if="$root.global_settings.telegram_enabled === true")
            .card-body 
              router-link(
                target="_blank",
                :to="{ name: 'chat_access', params: { id: service.id } }"
              ) 
                i.fa.fa-comment(style="font-size: 30px")
                span(style="font-size: 30px; margin-left: 20px") {{ $t('client.Access') }} (Telegram)
          .card(v-if="$root.user.can_see_financial_calendar === true")
            .card-body 
              a(v-on:click="open_finances") 
                i.fa.fa-dollar(style="font-size: 30px")
                span(style="font-size: 30px; margin-left: 20px") {{ $t('client.Finances') }}
          .card(
            v-if="$root.module_enabled('expences') && $root.can_do('expences', 'read') != 0"
          )
            .card-body 
              a(v-on:click="open_expences") 
                i.fa.fa-truck(style="font-size: 30px")
                span(style="font-size: 30px; margin-left: 20px") {{ $t('expences.expences') }}
          .card
            .card-body 
              router-link(
                target="_blank",
                :to="{ name: 'summary', params: { id: service.id } }"
              ) 
                i.fa.fa-bar-chart(style="font-size: 30px")
                span(style="font-size: 30px; margin-left: 20px") {{ $t('client.summary') }}
          .card(v-if="$root.can_do('services', 'update') != 0")
            .card-body 
              a(v-on:click="send_to_platform") 
                i.fa.fa-exchange(style="font-size: 30px")
                span(
                  v-if="service.platform_id > 0",
                  style="font-size: 30px; margin-left: 20px"
                ) {{ $t('template.view_on_the_platform') }}
                span(v-else, style="font-size: 30px; margin-left: 20px") {{ $t('template.send_to_platform') }}

  general_contractor(:service="service")
  add_service_to_subcontractor(
    :service="service",
    v-if="service",
    @fetch_services_from_child="fetch_services_from_child"
  )
</template>

<script>
import moment from "moment";
import process from "./process.vue";
import chat_access from "./chat_access.vue";
import finance from "./finance.vue";
import subcontractors from "./subcontractors.vue";
import estimate_group_materials from "./estimate_group_materials.vue";
import estimate_group_workers from "./estimate_group_workers.vue";
import general_contractor from "./general_contractor_modal.vue";
import add_service_to_subcontractor from "./add_service_to_subcontractor.vue";

import { mapGetters } from "vuex";

export default {
  props: ["service", "editable"],
  data() {
    return {
      current_state: null,
      mystates: [],
      current_state_in_scope: false,
      loading: false,
      // Event
      eventResolve: null,
      eventReject: null,
      // Estimate select
      estimatesResolve: null,
      estimatesReject: null,
      // Fill checklist
      checklistResolve: null,
      checklistReject: null,
      // Upload to google drive
      fileResolve: null,
      fileReject: null,
      // Open link
      openFileResolve: null,
      openFileReject: null,
      open_file_url: "",
      // Reason
      reason_state: null,
      reason_text: null,
      reasonResolve: null,
      reasonReject: null,
      // Email
      email_link: "",
      timerId: null,
      emailResolve: null,
      emailReject: null,
      tmp_global_data: null,
      invoice_email_resolve: null,

      pay_stage: null,
      pay_stage_valor: 0.0,
      pay_stage_vat: 0.0,
      pay_stage_total: 0.0,
      invoice: null,
    };
  },
  components: {
    process,
    chat_access,
    finance,
    subcontractors,
    estimate_group_materials,
    estimate_group_workers,
    general_contractor,
    add_service_to_subcontractor,
  },
  methods: {
    fetch_services_from_child() {
      this.$emit("fetch_services_from_child");
    },
    send_to_platform() {
      if (this.service.platform_id > 0) {
        window
          .open(
            "https://pec.pt/Platform#/contracts/" +
              this.service.platform_id +
              "/edit",
            "_blank"
          )
          .focus();
      } else {
        if (this.service.master_estimate_id > 0) {
          this.publish_to_platform();
        } else {
          if (confirm(this.$root.$t("template.are_you_sure_want_to_publish"))) {
            this.publish_to_platform();
          }
        }
      }
    },
    publish_to_platform() {
      this.$root.global_loading = true;
      this.$http
        .post("/api/services/" + this.service.id + "/send_to_platform")
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$emit("fetch_platform_id", {
                id: this.service.id,
                platform_id: res.data.platform_id,
              });
              this.$toastr.s(
                this.$root.$t("template.service_is_successfully_published"),
                this.$root.$t("template.Success")
              );
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
    open_general_contractor_modal() {
      jQuery("#modal-additional_features_" + this.service.id).modal("hide");
      jQuery("#modal-general-contractor_" + this.service.id).modal("show");
    },
    estimate_number(estimate) {
      return (
        this.$root.service_number(this.service) +
        (estimate.option != null
          ? " " + this.$t("template.option") + estimate.option
          : "") +
        (estimate.revision != null
          ? " " + this.$t("template.revision") + estimate.revision
          : "") +
        (estimate.fork_id != null
          ? " " + this.$root.get_estimate_fork(estimate)
          : "")
      );
    },

    showContractorsModal() {
      jQuery("#modal-additional_features_" + this.service.id).modal("hide");
      jQuery("#modal-contractors_" + this.service.id).modal("show");
    },
    update_invoice(value) {
      this.invoice = value;
    },
    update_emailResolve(value) {
      this.invoice_email_resolve = value;
    },
    update_email_link(value) {
      this.email_link = value;
    },
    update_pay_stage(value) {
      this.pay_stage = value;
    },
    update_pay_stage_valor(value) {
      this.pay_stage_valor = value;
    },
    update_pay_stage_vat(value) {
      this.pay_stage_vat = value;
    },
    update_pay_stage_total(value) {
      this.pay_stage_total = value;
    },
    status_to_center() {
      if (this.current_state_in_scope) {
        this.$refs["process-" + this._uid].status_to_center();
      }
    },
    additional_service() {
      if (
        confirm(
          this.$root.$t("client.Are_you_sure_to_create_additional_service")
        )
      ) {
        this.$http
          .post("/api/services/additional", { service: this.service.id })
          .then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
              } else {
                this.$emit("create_additional", res.data.service);
                this.$toastr.s(
                  this.$root.$t("client.Additional_service_created"),
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
      }
    },
    open_project() {
      this.$router.push({
        name: "project_show",
        params: { id: this.service.project_id },
      });
    },
    open_estimates: function () {
      this.$http
        .get("/api/estimates?search=" + this.service.id + "&by_service=true")
        .then(
          (res) => {
            Vue.set(this.service, "estimates", []);
            for (let i = 0; i < res.data.estimates.length; i++) {
              res.data.estimates[i].service = this.service;
              this.service.estimates.push(res.data.estimates[i]);
            }
            // заполнить forks
            this.$emit("set_service", this.service, res.data.forks);
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
          }
        );
    },
    // Specify reason action
    show_reason: function (service_state) {
      let $this = this;
      return new Promise(function (resolve, reject) {
        $this.reasonResolve = resolve;
        $this.reasonReject = reject;
        $this.reason_state = service_state;
        jQuery("#modal-reason-" + $this.service.id).modal("show");
      });
    },
    reason_ok: function () {
      this.reason_state = null;
      this.reasonResolve(this.reason_text);
      this.reason_text = null;
      jQuery("#modal-reason-" + this.service.id).modal("hide");
    },
    // Send email action
    show_email: async function (
      service_state_action,
      global_data,
      estimates = null
    ) {
      let $this = this;
      this.$root.global_loading = true;

      let res = await this.$http.post("/api/mail/login").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
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

      return this.$http
        .post("/api/mail/action_email", {
          service: this.service.id,
          action: service_state_action.id,
          estimates: estimates,
          global_data: global_data,
        })
        .then(
          (res) => {
            this.$root.global_loading = false;
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
              return new Promise.reject();
            } else if (service_state_action.email_show) {
              $this.tmp_global_data = global_data;
              if ("sent_estimate_numbers" in res.data) {
                $this.tmp_global_data.sent_estimate_numbers =
                  res.data.sent_estimate_numbers;
              }
              return new Promise(function (resolve, reject) {
                $this.emailResolve = resolve;
                $this.emailReject = reject;
                $this.email_link =
                  "/webmail/index.php#single-compose/drafts/" +
                  encodeURIComponent(res.data.draft.NewFolder) +
                  "/" +
                  res.data.draft.NewUid;
                //                                $this.show_email_modal = true;
                jQuery("#modal-mail-" + $this.service.id).modal("show");
              });
            } else if (res.data.info) {
              this.$toastr.s(
                this.$root.$t("client.Message_sent"),
                this.$root.$t("template.Success")
              );
            } else {
              this.$toastr.e(
                this.$root.$t("client.Email not sent"),
                this.$root.$t("template.Error")
              );
              return new Promise.reject();
            }
          },
          (res) => {
            this.$root.global_loading = false;
            this.$toastr.e(
              this.$root.$t("template.Something_bad_happened"),
              this.$root.$t("template.Error")
            );
          }
        );
    },
    //            email_click_at_back: function(){
    //                this.show_email_modal = false;
    //                this.emailReject();
    //            },
    email_service_iframe_loaded: function () {
      let $this = this;
      jQuery("#email_iframe").ready(function () {
        // Knockout.js observable subscribe
        window.frames[
          "email_iframe"
        ].App.Screens.oScreens.information.Model.reportVisible.subscribe(
          function (newValue) {
            if (newValue) {
              // need some time to send ajax to save mail to history
              $this.timerId = setInterval(function () {
                // if outcoming email history done
                if (!window.frames["email_iframe"].erp_email_sends) {
                  clearInterval($this.timerId);
                  // Message sent
                  //                                    $this.show_email_modal = false;
                  jQuery("#modal-mail-" + $this.service.id).modal("hide");
                  if ($this.invoice !== null) {
                    $this.invoice_email_resolve($this.invoice);
                    $this.invoice = null;
                  } else {
                    $this.emailResolve($this.tmp_global_data);
                  }

                  $this.$toastr.s(
                    $this.$root.$t("client.Message_sent"),
                    $this.$root.$t("template.Success")
                  );
                }
              }, 100);
            }
          }
        );
      });
    },
    // Select estimates action
    select_estimates: function () {
      let $this = this;
      return this.$http
        .get("/api/estimates?search=" + this.service.id + "&by_service=true")
        .then(
          (res) => {
            if (res.data.estimates.length > 0) {
              Vue.set($this.service, "estimates", []);
              for (let i = 0; i < res.data.estimates.length; i++) {
                res.data.estimates[i].service = $this.service;
                res.data.estimates[i].selected = false;
                $this.service.estimates.push(res.data.estimates[i]);
              }
              return new Promise(function (resolve, reject) {
                $this.estimatesResolve = resolve;
                $this.estimatesReject = reject;
                $this.$emit("send_estimates", $this.service);
              });
            } else {
              this.$toastr.w(
                this.$root.$t("client.No_estimates_yet"),
                this.$root.$t("template.Warning")
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
    resolve_send_estimates: function (estimates, service_id) {
      if (this.service.id == service_id) {
        this.estimatesResolve(estimates);
        jQuery("#send_estimates_modal").modal("hide");
      }
    },
    // Task action
    show_event: function (service_state_action, global_data) {
      let $this = this;
      this.tmp_global_data = global_data;
      if (
        service_state_action.event_type_id != 0 &&
        service_state_action.event_user_id != 0 &&
        service_state_action.event_date_type != 0 &&
        service_state_action.event_description_not_editable
      ) {
        return this.$http
          .post("/api/calendar/action_event", {
            action_id: service_state_action.id,
            service_id: this.service.id,
            global_data: global_data,
          })
          .then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
              } else {
                this.$toastr.s(
                  this.$root.$t("client.Task_saved"),
                  this.$root.$t("template.Success")
                );
                $this.tmp_global_data.event_start = res.data.event.start;
                $this.tmp_global_data.task_description =
                  res.data.event.description;
                $this.tmp_global_data.event_id = res.data.event.id;
                this.$bus.$emit("add_event_to_list", res.data.event);
                return $this.tmp_global_data;
              }
            },
            (res) => {
              this.$toastr.e(
                this.$root.$t("template.Server_error"),
                this.$root.$t("template.Error")
              );
            }
          );
      } else {
        // Show event form
        return new Promise(function (resolve, reject) {
          $this.eventResolve = resolve;
          $this.eventReject = reject;
          $this.$bus.$emit(
            "action_event",
            service_state_action,
            $this.service,
            global_data
          );
        });
      }
    },
    action_event_ok: function (event_id, event_start, event_description) {
      if (this.tmp_global_data != null) {
        // HARDCODE! FIRES ALL INSTANCES
        this.tmp_global_data.event_id = event_id;
        this.tmp_global_data.event_start = event_start;
        this.tmp_global_data.task_description = event_description;
        this.eventResolve(this.tmp_global_data);
      }
    },
    action_event_cancel: function () {
      this.eventReject();
    },
    //
    action_sms: function (service_state_action, global_data) {
      return this.$http
        .post("/api/action_sms/", {
          action_id: service_state_action.id,
          service_id: this.service.id,
          global_data: global_data,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              let $this = this;
              res.data.info.forEach(function (el) {
                if (el.errcode == 1) {
                  $this.$toastr.e(el.errmess, $this.$root.$t("template.Error")); // $this.$root.$t because of https://github.com/kazupon/vue-i18n/issues/184
                } else {
                  $this.$toastr.s(
                    $this.$root.$t("client.sms_sent"),
                    $this.$root.$t("template.Success")
                  );
                }
              });
              return global_data;
            }
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Something_bad_happened"),
              this.$root.$t("template.Error")
            );
          }
        );
    },
    save_new_state: function (service_id, state, message = null) {
      this.$http
        .post("/api/services/" + service_id + "/set_new_state", {
          state_id: state.id,
          message: message,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              let system = {
                type_id: 2, // System
                user_id: this.$root.user.id,
                service_state_id: state.id,
                service_state: {
                  name: state.name,
                },
                service_id: service_id,
                service: {
                  estimate_number: this.service.estimate_number,
                  additional: this.service.additional,
                },
                created_at: moment(new Date()).format("YYYY-MM-DD HH:mm:ss"),
                message: message,
              };
              this.$bus.$emit("system_message", system);
              this.$toastr.s(
                this.$root.$t("client.State_saved"),
                this.$root.$t("template.Success")
              ); // $this.$root.$t because of https://github.com/kazupon/vue-i18n/issues/184
            }
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Something_bad_happened"),
              this.$root.$t("template.Error")
            );
          }
        );
    },
    new_state: function (service_state, message = null) {
      this.save_new_state(this.service.id, service_state, message);
      this.current_state.selected = false;
      this.current_state = this.mystates[this.mystates.indexOf(service_state)];
      this.current_state.selected = true;
      this.status_to_center();
    },
    // google drive
    upload_file_to_gd(service_state_action, global_data) {
      let $this = this;
      this.tmp_global_data = global_data;
      return new Promise(function (resolve, reject) {
        $this.fileResolve = resolve;
        $this.fileReject = reject;
        jQuery("#modal-file-" + $this.service.id).modal("show");
      });
    },
    upload_file(url, name) {
      this.$root.global_loading = true;
      return this.$http
        .post("/api/settings/integrations/google_drive/upload", {
          src: url,
          filename: name,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
              this.$root.global_loading = false;
            } else {
              this.tmp_global_data.uploaded_link = res.data.link;
              this.$root.global_loading = false;
              jQuery("#modal-file-" + this.service.id).modal("hide");
              this.fileResolve(this.tmp_global_data);
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
    imageuploading() {
      this.loading = true;
    },
    imageuploaded(res) {
      this.loading = false;
      if (res.errcode == 0) {
        this.upload_file(res.url, res.name);
      } else {
        this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
      }
    },
    imageerror(e) {
      this.loading = false;
      this.$toastr.e(e, this.$root.$t("template.Error"));
    },
    // open url
    open_url(service_state_action, global_data) {
      let formatted_url = service_state_action.url.replace(
        new RegExp("{uploaded_link}", "g"),
        global_data.uploaded_link
      );

      let $this = this;
      return new Promise(function (resolve, reject) {
        $this.openFileResolve = resolve;
        $this.openFileReject = reject;
        $this.open_file_url = formatted_url;
        $this.tmp_global_data = global_data;
        jQuery("#modal-open-file").modal("show");
      });
    },
    open_url_clicked() {
      let h = window.open(this.open_file_url, "_blank");
      h.blur();
      window.focus();
      this.openFileResolve(this.tmp_global_data);
      jQuery("#modal-open-file").modal("hide");
    },
    // checklist
    fill_checklist(service_state_action, global_data) {
      let $this = this;
      this.tmp_global_data = global_data;
      this.$root.global_loading = true;
      return this.$http
        .get(
          "/api/checklists/" +
            service_state_action.checklist_id +
            "?format=json"
        )
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
              this.$root.global_loading = false;
            } else {
              this.$root.global_loading = false;
              return new Promise(function (resolve, reject) {
                $this.checklistResolve = resolve;
                $this.checklistReject = reject;
                $this.$bus.$emit(
                  "fill_checklist_form",
                  res.data,
                  $this.service,
                  global_data
                );
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
    },
    resolve_fill_checklist: function () {
      //                if (this.tmp_global_data != null) { // HARDCODE! FIRES ALL INSTANCES
      //                    this.tmp_global_data.event_id = event_id;
      //                    this.tmp_global_data.event_start = event_start;
      //                    this.tmp_global_data.task_description = event_description;

      this.checklistResolve(this.tmp_global_data);
      //                }
    },
    activate_state: function (service_state) {
      let $this = this;
      if (
        (service_state.type == 0 &&
          service_state.can_click &&
          this.current_state.id != service_state.id) ||
        service_state.type == 1
      ) {
        // Global variables
        let actions_pipe = Promise.resolve({
          sent_estimate_numbers: "",
          event_start: "",
          task_description: "",
          event_id: "",
          uploaded_link: "",
        });
        service_state.service_state_actions.forEach(function (
          service_state_action
        ) {
          actions_pipe = actions_pipe.then((global_data) => {
            switch (service_state_action.type) {
              case 1:
                if (service_state_action.email_include_estimate_type == 2) {
                  // Select estimates
                  return $this.select_estimates().then((selected_estimates) => {
                    return $this.show_email(
                      service_state_action,
                      global_data,
                      selected_estimates
                    );
                  });
                } else {
                  // Master estimate
                  return $this.show_email(service_state_action, global_data);
                }
              case 2:
                if (
                  $this.service.client_contact.client_contact_phones.length ==
                    0 &&
                  service_state_action.sms_type == 1
                ) {
                  // sms to client
                  $this.$toastr.w(
                    $this.$root.$t("client.There_are_no_phones"),
                    $this.$root.$t("template.Warning")
                  );
                } else {
                  return $this.action_sms(service_state_action, global_data);
                }
                break;
              case 3:
                return $this.show_event(service_state_action, global_data);
              case 4:
                return $this.fill_checklist(service_state_action, global_data);
              case 5:
                return $this.upload_file_to_gd(
                  service_state_action,
                  global_data
                );
              case 6:
                return $this.open_url(service_state_action, global_data);
            }
          });
        });
        if (service_state.type == 0 && service_state.with_reason) {
          actions_pipe = actions_pipe.then((res) => {
            return $this.show_reason(service_state);
          });
        }
        actions_pipe = actions_pipe.then((res) => {
          $this.new_state(
            service_state.type == 0
              ? service_state
              : service_state.destination_state,
            service_state.type == 0 && service_state.with_reason ? res : null
          );
        });
      }
    },
    //
    open_attachments() {
      this.$emit("open_attachments", this.service);
    },
    get_pay_info() {
      if (
        this.service.paid_summ != null &&
        this.service.paid_summ == this.service.estimate_summ
      ) {
        return this.$root.$t("client.Paid_full");
      } else if (
        this.service.paid_summ != null &&
        this.service.paid_summ != 0 &&
        this.service.paid_summ != this.service.estimate_summ
      ) {
        return this.$root.$t("client.Paid_not_full");
      } else {
        return this.$root.$t("client.Not_paid");
      }
    },
    maximize_minimize_click_for_service() {
      this.status_to_center();
    },
    // enable_chat(){
    //     jQuery('#modal-access_' + this.service.id).modal('show');
    // },
    open_finances() {
      jQuery("#modal-additional_features_" + this.service.id).modal("hide");
      jQuery("#modal-finance_" + this.service.id).modal({
        backdrop: "static",
        keyboard: false,
      });
      this.$refs.finance.load_estimate(this.service.master_estimate_id);
    },
    // open_subcontractors(){
    //     jQuery('#modal-subcontractors_' + this.service.id).modal('show');
    // },
    open_expences() {
      let routeData = null;
      if (this.service.master_estimate_id) {
        routeData = this.$router.resolve({
          name: "expences_index_estimate",
          params: { estimate_id: this.service.master_estimate_id },
        });
      } else {
        routeData = this.$router.resolve({ name: "expences_index" });
      }
      window.open(routeData.href, "_blank");
    },
    // open_estimate_workers(){
    //     jQuery('#modal-estimate_group_workers_' + this.service.id).modal('show');
    // },
    // open_estimate_materials(){
    //     jQuery('#modal-estimate_group_materials_' + this.service.id).modal('show');
    // },
    open_extra() {
      jQuery("#modal-additional_features_" + this.service.id).modal("show");
    },
    estimate_number(estimate) {
      return (
        this.$root.service_number(this.service) +
        (estimate.option != null
          ? " " + this.$t("template.option") + estimate.option
          : "") +
        (estimate.revision != null
          ? " " + this.$t("template.revision") + estimate.revision
          : "") +
        (estimate.fork_id != null
          ? " " + this.$root.get_estimate_fork(estimate)
          : "")
      );
    },
  },
  computed: {
    ...mapGetters({
      states: "getNotRemovedServiceStates",
      users_by_id: "getUsersById",
      groups: "getGroups",
      groupsById: "getGroupsById",
    }),
    contractors_with_estimates() {
      let res = "";
      this.service.estimates.forEach((e) => {
        if (e.groups && e.groups.length > 0) {
          res += this.estimate_number(e) + " (";
          e.groups.forEach((g) => {
            res += g.group.name + ", ";
          });
          res += ") ";
        }
      });
      return res;
    },
    has_contractors() {
      let res = false;
      if (this.service.groups.length > 0) {
        res = true;
      }
      this.service.estimates.forEach((e) => {
        if (e.groups && e.groups.length > 0) {
          res = true;
        }
      });
      return res;
    },
  },
  mounted() {
    this.$bus.$on("refetch_estimates", (data) => {
      this.open_estimates();
    });
    this.$bus.$on(
      "maximize_minimize_click_for_service",
      this.maximize_minimize_click_for_service
    );
    this.$bus.$on("action_event_ok", this.action_event_ok);
    this.$bus.$on("resolve_send_estimates", this.resolve_send_estimates);
    this.$bus.$on("resolve_fill_checklist", this.resolve_fill_checklist);
    let $this = this;
    this.states.forEach(function (state) {
      let new_state = Object.assign({}, state);
      new_state.selected = false;
      $this.mystates.push(new_state);
    });
    this.mystates.forEach(function (part, index, theArray) {
      if (theArray[index].destination_state_id != null) {
        theArray[index].destination_state = $this.mystates.filter(function (
          fstate
        ) {
          return fstate.id == theArray[index].destination_state_id;
        })[0];
      }
    });
    let tmp_state = this.mystates.filter(function (state) {
      return state.id == $this.service.service_state_id;
    });
    if (tmp_state.length > 0) {
      this.current_state = tmp_state[0];
      this.current_state.selected = true;
    } else {
      this.$toastr.e(
        this.$root.$t("client.State_not_found"),
        this.$root.$t("template.Error")
      );
    }

    // filter states according to scope
    if (!this.$root.user.is_admin) {
      let tmp_states = [];
      let started = false;
      this.mystates.forEach(function (state) {
        if (
          state.id ==
          $this.$root.current_user_service_scope.start_service_state_id
        ) {
          started = true;
        }
        if (started) {
          tmp_states.push(state);
          if (state.id == $this.current_state.id) {
            $this.current_state_in_scope = true;
          }
        }
        if (
          state.id ==
          $this.$root.current_user_service_scope.end_service_state_id
        ) {
          started = false;
        }
      });
      this.mystates = tmp_states;
    } else {
      this.current_state_in_scope = true;
    }
  },
  beforeDestroy: function () {
    this.maximize_minimize_click_for_service &&
      this.$bus.$off(
        "maximize_minimize_click_for_service",
        this.maximize_minimize_click_for_service
      );
    this.action_event_ok &&
      this.$bus.$off("action_event_ok", this.action_event_ok);
    this.resolve_send_estimates &&
      this.$bus.$off("resolve_send_estimates", this.resolve_send_estimates);
    this.resolve_fill_checklist &&
      this.$bus.$off("resolve_fill_checklist", this.resolve_fill_checklist);
    this.$bus.$off("refetch_estimates", this.open_estimates);
  },
};
</script>