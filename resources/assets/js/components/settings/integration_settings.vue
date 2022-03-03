<style>
.integrations-table td {
  padding: 5px;
}
.table-users-wrapper {
  max-height: 300px;
  overflow: auto;
  display: inline-block;
}
</style>

<template lang="pug">
div(v-if="settings")
  #modal-client-id.modal.fade(tabindex="-1", aria-hidden="true")
    .modal-dialog.modal-dialog-centered(role="document")
      .modal-content
        .modal-header Google Ads
        .modal-body
          .row
            .col-12.text-center
              a(
                :href="'https://accounts.google.com/o/oauth2/v2/auth?response_type=code&access_type=offline&client_id=' + settings.google_ads_client_id + '&redirect_uri=urn%3Aietf%3Awg%3Aoauth%3A2.0%3Aoob&state&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fadwords'",
                target="_blank"
              ) {{ $t('template.enable_access_and_get_code') }}
          .row.mt-3
            .col-12.text-center
              fieldset.form-group
                label.control-label.col-xs-4 {{ $t('template.tg_code') }}
                input.form-control(
                  v-model="google_ads_code",
                  :placeholder="$t('template.tg_code')"
                )
            .col-12.text-center
              button.btn.btn-diga(
                :disabled="google_ads_code === ''",
                @click="get_refresh_token_g_ads"
              ) {{ $t('template.get_refresh_token') }}
  .row
    .col-12
      h1 {{ $t('template.IntegrationSettings') }}
    .col-12.col-md-6.mb-3
      .diga-container.p-4
        .clearfix
          h2.float-left {{ $t('template.Fb_integration') }}
          .float-right
            div(v-on:click="facebook_toggle()", style="width: 120px")
              bootstrap-toggle(
                data-size="mini",
                v-model="settings.fb_enabled",
                :options="{ on: $t('template.On'), off: $t('template.Off') }",
                data-width="120",
                data-height="38",
                data-onstyle="default",
                ref="fb_toggle"
              )
        div(v-if="settings.fb_enabled")
          .mb-2 {{ $t('template.Fb_attention') }}
          table.w-100
            tr(v-for="page in settings.fb_pages")
              td(style="width: 50px")
                img(style="width: 50px; height: 50px", :src="page.logo")
              td.pl-2
                a(:href="page.url") {{ page.name }}
              td(style="width: 120px")
                bootstrap-toggle(
                  data-size="mini",
                  v-model="page.enabled",
                  :options="{ on: $t('template.On'), off: $t('template.Off') }",
                  data-width="120",
                  data-height="38",
                  data-onstyle="default"
                )
        div(v-else) {{ $t('template.Facebook_info') }}
    .col-12.col-md-6.mb-3
      .diga-container.p-4
        .clearfix
          h2.float-left {{ $t('template.Checkfront_integration') }}
          .float-right
            bootstrap-toggle(
              data-size="mini",
              v-model="settings.checkfront_enabled",
              :options="{ on: $t('template.On'), off: $t('template.Off') }",
              data-width="120",
              data-height="38",
              data-onstyle="default",
              ref="cf_toggle"
            )
        div(v-if="settings.checkfront_enabled")
          fieldset.form-group
            label.control-label.col-xs-4 {{ $t('template.Checkfront_host') }}
            .col-xs-8
              input.form-control(v-model="settings.checkfront_host")
          fieldset.form-group
            label.control-label.col-xs-4 {{ $t('template.Checkfront_api_key') }}
            .col-xs-8
              input.form-control(v-model="settings.checkfront_api_key")
          fieldset.form-group
            label.control-label.col-xs-4 {{ $t('template.Checkfront_api_secret') }}
            .col-xs-8
              input.form-control(v-model="settings.checkfront_api_secret")
          h3 {{ $t('template.Fields') }}
          table.integrations-table.mb-2(style="width: 100%")
            tr
              td #
              td(style="width: 18px")
              td {{ $t('template.Field') }}
              td {{ $t('template.Note') }}
              td {{ $t('template.Destination') }}
              td {{ $t('template.Type') }}
            tr(
              v-for="(field, i) in checkfront_fields_ordered",
              style="margin-bottom: 5px"
            )
              td
                | {{ i + 1 }}.
              td
                i.fa.fa-chevron-up(
                  style="cursor: pointer",
                  v-on:click="field_up(field)"
                )
                i.fa.fa-chevron-down(
                  style="cursor: pointer",
                  v-on:click="field_down(field)"
                )
              td
                input.form-control(v-model="field.field_name")
              td
                input.form-control(v-model="field.note")
              td
                select.form-control(v-model="field.destination")
                  option(value="1") {{ $t('template.Service_note') }}
                  option(value="2") {{ $t('template.Service_address') }}
              td
                select.form-control(v-model="field.type")
                  option(value="1") {{ $t('template.Overwrite') }}
                  option(value="2") {{ $t('template.Append') }}
              td
                button.btn(v-on:click="remove_field(field)") {{ $t('template.Remove') }}
          button.btn(v-on:click="add_field()") {{ $t('template.Add') }}
        div(v-else) {{ $t('template.Checkfront_info') }}
    .col-12.col-md-6.mb-3
      .diga-container.p-4
        .clearfix
          h2.float-left {{ $t('template.Google_drive_integration') }}
          .float-right
            div(v-on:click="google_drive_toggle()", style="width: 120px")
              bootstrap-toggle(
                data-size="mini",
                v-model="settings.gd_enabled",
                :options="{ on: $t('template.On'), off: $t('template.Off') }",
                data-width="120",
                data-height="38",
                data-onstyle="default"
              )
        div {{ $t('template.Google_drive_info') }}
    .col-12.col-md-6.mb-3
      .diga-container.p-4
        .clearfix
          h2.float-left {{ $t('zadarma.integration_title') }}
          .float-right
            bootstrap-toggle(
              data-size="mini",
              v-model="settings.zadarma_enabled",
              :options="{ on: $t('template.On'), off: $t('template.Off') }",
              data-width="120",
              data-height="38",
              data-onstyle="default",
              ref="cf_toggle"
            )
        div(v-if="settings.zadarma_enabled")
          fieldset.form-group
            label.control-label.col-xs-4 {{ $t('zadarma.client_secret') }}
            .col-xs-8
              input.form-control(v-model="settings.zadarma_secret")
          fieldset.form-group
            label.control-label.col-xs-4 {{ $t('zadarma.client_key') }}
            .col-xs-8
              input.form-control(v-model="settings.zadarma_key")
          fieldset.form-group
            label.control-label.col-xs-4 {{ $t('zadarma.redirect_to_responsible') }}
            input.ml-2(
              type="checkbox",
              v-model="settings.zadarma_redirect_to_responsible"
            )
          hr
          fieldset.form-group
            label.control-label.col-xs-4 {{ $t('zadarma.make_task_if_no_answer') }}
            input.ml-2(
              type="checkbox",
              v-model="settings.zadarma_new_task_if_no_answer"
            )
          fieldset.form-group
            label.control-label.col-xs-4 {{ $t('zadarma.task_type') }}
            .col-xs-8
              select.form-control(
                v-model="settings.zadarma_task_type_id",
                :disabled="settings.zadarma_new_task_if_no_answer == 0"
              )
                option(
                  v-for="(evt, i) in event_types",
                  :value="evt.id",
                  v-text="evt.title"
                )
          fieldset.form-group
            label.control-label.col-xs-4 {{ $t('zadarma.responsible_for_unanswered_calls') }}
            .col-xs-8
              select.form-control(
                v-model="settings.zadarma_missed_call_responsible_id",
                :disabled="settings.zadarma_new_task_if_no_answer == 0"
              )
                option(
                  v-for="ruser in responsible_users",
                  :value="ruser.id",
                  v-text="ruser.name"
                )
          hr
          .table-users-wrapper(style="width: 100%")
            table.integrations-table.mb-2(style="width: 100%")
              thead
                tr
                  td {{ $t('zadarma.usermanager') }}
                  td {{ $t('zadarma.internal_number') }}
                  //td {{ $t('zadarma.responsible_by_default') }}
              tbody
                tr(
                  v-for="ruser in responsible_users",
                  style="margin-bottom: 5px"
                )
                  td {{ ruser.name }}
                  td
                    input.form-control(
                      type="text",
                      v-model="ruser.zadarma_internal_phonecode"
                    )
                  //td
                    input.form-control(type="radio",  :value="ruser.id", v-model="zadarma_responsible_by_default")
        div(v-else) {{ $t('template.Zadarma_info') }}
    .col-12.col-md-6.mb-3
      .diga-container.p-4
        .clearfix
          h2.float-left {{ $t('template.MailchimpIntegration') }}
          .float-right
            div(v-on:click="", style="width: 120px")
              bootstrap-toggle(
                data-size="mini",
                v-model="settings.mailchimp_integration_enabled",
                :options="{ on: $t('template.On'), off: $t('template.Off') }",
                data-width="120",
                data-height="38",
                data-onstyle="default"
              )
        div(v-if="settings.mailchimp_integration_enabled")
          fieldset.form-group
            label.control-label.col-xs-4 {{ $t('template.Checkfront_api_key') }}
            .col-xs-8
              input.form-control(v-model="settings.mailchimp_api_key")
          h3 {{ $t('template.Mailchimp_audiences') }}
          .table-users-wrapper(style="width: 100%")
            table.integrations-table.mb-2(style="width: 100%")
              thead
                tr
                  td {{ $t('template.Mailchimp_listname') }}
                  td {{ $t('template.Mailchimp_numberofemails') }}
                  td {{ $t('template.Mailchimp_numberofunsubscribers') }}
              tbody
                tr(v-for="l in mailchimp_lists", style="margin-bottom: 5px")
                  td
                    a(
                      target="_blank",
                      :href="'https://' + getSecondPart(settings.mailchimp_api_key) + '.admin.mailchimp.com/audience/'"
                    ) {{ l.name }}
                  td {{ l.stats.member_count }}
                  td {{ l.stats.unsubscribe_count }}
          h3 {{ $t('template.Mailchimp_campaigns') }}
          .table-users-wrapper(style="width: 100%")
            table.integrations-table.mb-2(style="width: 100%")
              thead
                tr
                  td {{ $t('template.Mailchimp_campaign_title') }}
                  td {{ $t('template.Mailchimp_campaign_type') }}
                  td {{ $t('template.Mailchimp_campaign_emailsent') }}
                  td {{ $t('template.Mailchimp_campaign_recipients') }}
              tbody
                tr(
                  v-for="c in mailchimp_campaigns",
                  style="margin-bottom: 5px"
                )
                  td 
                    a(
                      target="_blank",
                      :href="'https://' + getSecondPart(settings.mailchimp_api_key) + '.admin.mailchimp.com/campaigns/edit?id=' + c.web_id"
                    ) {{ c.settings.title }}
                  td {{ c.type }}
                  td {{ c.emails_sent }}
                  td {{ c.recipients.list_name }}
          h3 {{ $t('template.Mailchimp_audiences_export') }}
          div {{ $t('template.Mailchimp_export_contacts_with_status') }}
            select.form-control(
              v-model="selected_service_state_id",
              style="flex: 2; min-width: 150px"
            )
              option(value="0") {{ $t('template.Mailchimp_all') }}
              option(
                v-for="service_state in service_states",
                v-bind:value="service_state.id",
                v-text="service_state.name"
              )
            div {{ $t('template.Mailchimp_to_new_list') }}
              input.big-checkbox(
                type="checkbox",
                v-model="is_new_list",
                style="margin-left: 5px"
              )
            div(v-if="is_new_list")
              input.form-control(
                type="text",
                v-validate="'required'",
                v-bind:data-vv-as="$t('template.Mailchimp_enter_audience_name').toLowerCase()",
                name="new_list_name",
                style="margin-bottom: 5px",
                v-model="new_list_name",
                :placeholder="$t('template.Mailchimp_enter_audience_name')"
              )
              h6.help-block(v-show="errors.has('new_list_name')") {{ errors.first('new_list_name') }}
              input.form-control(
                type="text",
                v-validate="'required'",
                v-bind:data-vv-as="$t('template.Mailchimp_company').toLowerCase()",
                name="company",
                style="margin-bottom: 5px",
                v-model="company",
                :placeholder="$t('template.Mailchimp_company')"
              )
              h6.help-block(v-show="errors.has('company')") {{ errors.first('company') }}
              input.form-control(
                type="text",
                v-validate="'required'",
                v-bind:data-vv-as="$t('template.Mailchimp_address1').toLowerCase()",
                name="address1",
                style="margin-bottom: 5px",
                v-model="address1",
                :placeholder="$t('template.Mailchimp_address1')"
              )
              h6.help-block(v-show="errors.has('address1')") {{ errors.first('address1') }}
              input.form-control(
                type="text",
                style="margin-bottom: 5px",
                v-model="address2",
                :placeholder="$t('template.Mailchimp_address2')"
              )
              input.form-control(
                type="text",
                v-validate="'required'",
                v-bind:data-vv-as="$t('template.Mailchimp_city').toLowerCase()",
                name="city",
                style="margin-bottom: 5px",
                v-model="city",
                :placeholder="$t('template.Mailchimp_city')"
              )
              h6.help-block(v-show="errors.has('city')") {{ errors.first('city') }}
              input.form-control(
                type="text",
                v-validate="'required'",
                v-bind:data-vv-as="$t('template.Mailchimp_state').toLowerCase()",
                name="state",
                style="margin-bottom: 5px",
                v-model="state",
                :placeholder="$t('template.Mailchimp_state')"
              )
              h6.help-block(v-show="errors.has('state')") {{ errors.first('state') }}
              input.form-control(
                type="text",
                v-validate="'required'",
                v-bind:data-vv-as="$t('template.Mailchimp_zip').toLowerCase()",
                name="zip",
                style="margin-bottom: 5px",
                v-model="zip",
                :placeholder="$t('template.Mailchimp_zip')"
              )
              h6.help-block(v-show="errors.has('zip')") {{ errors.first('zip') }}
              input.form-control(
                type="text",
                v-validate="'required'",
                v-bind:data-vv-as="$t('template.Mailchimp_phone').toLowerCase()",
                name="phone",
                style="margin-bottom: 5px",
                v-model="phone",
                :placeholder="$t('template.Mailchimp_phone')"
              )
              h6.help-block(v-show="errors.has('phone')") {{ errors.first('phone') }}
              textarea.form-control(
                v-validate="'required'",
                v-bind:data-vv-as="$t('template.Mailchimp_permission_reminder').toLowerCase()",
                name="permission_reminder",
                style="margin-bottom: 5px",
                rows="4",
                cols="50",
                v-model="permission_reminder",
                :placeholder="$t('template.Mailchimp_permission_reminder')"
              )
              h6.help-block(v-show="errors.has('permission_reminder')") {{ errors.first('permission_reminder') }}
              input.form-control(
                type="text",
                v-validate="'required'",
                v-bind:data-vv-as="$t('template.Mailchimp_from_name').toLowerCase()",
                name="from_name",
                style="margin-bottom: 5px",
                v-model="from_name",
                :placeholder="$t('template.Mailchimp_from_name')"
              )
              h6.help-block(v-show="errors.has('from_name')") {{ errors.first('from_name') }}
              input.form-control(
                type="text",
                v-validate="'required'",
                v-bind:data-vv-as="$t('template.Mailchimp_from_email').toLowerCase()",
                name="from_email",
                style="margin-bottom: 5px",
                v-model="from_email",
                :placeholder="$t('template.Mailchimp_from_email')"
              )
              h6.help-block(v-show="errors.has('from_email')") {{ errors.first('from_email') }}
            select.form-control(
              v-model="selected_audience_id",
              style="flex: 2; min-width: 150px",
              v-if="!is_new_list"
            )
              option(value="0", disabled) {{ $t('template.Mailchimp_select_audience') }}
              option(
                v-for="l in mailchimp_lists",
                v-bind:value="l.id",
                v-text="l.name"
              )
            button.btn.btn-diga(
              v-on:click="export_audience_to_mailchimp",
              style="margin-top: 5px"
            ) {{ $t('template.Export') }}
        div(v-else) {{ $t('template.MailchimpIntegration_info') }}
    .col-12.col-md-6.mb-3
      .diga-container.p-4
        .clearfix
          h2.float-left Telegram
          .float-right
            bootstrap-toggle(
              data-size="mini",
              v-model="settings.telegram_enabled",
              :options="{ on: $t('template.On'), off: $t('template.Off') }",
              data-width="120",
              data-height="38",
              data-onstyle="default",
              ref="cf_toggle"
            )
        div(v-if="settings.telegram_enabled")
          div(v-if="tg_show_code === false")
            fieldset.form-group
              label.control-label.col-xs-4 {{ $t('template.tg_api_id') }}
              .col-xs-8
                input.form-control(v-model="settings.tg_api_id")
            fieldset.form-group
              label.control-label.col-xs-4 {{ $t('template.tg_api_hash') }}
              .col-xs-8
                input.form-control(v-model="settings.tg_api_hash")
            fieldset.form-group
              label.control-label.col-xs-4 {{ $t('template.tg_phone') }}
              .col-xs-8
                input.form-control(v-model="settings.tg_phone")
            button.btn.btn-diga(
              v-on:click="tg_send_code",
              style="margin-top: 5px"
            ) {{ $t('template.Send_code') }}
          div(v-if="tg_show_code === true")
            fieldset.form-group
              label.control-label.col-xs-4 {{ $t('template.tg_code') }}
              .col-xs-8
                input.form-control(v-model="tg_code")
            button.btn.btn-diga(
              v-on:click="tg_enter_code",
              style="margin-top: 5px"
            ) {{ $t('template.Check') }}
        div(v-else) {{ $t('template.Telegram_info') }}
    .col-12.col-md-6.mb-3
      .diga-container.p-4
        .clearfix
          h2.float-left Google ads / Google Adwards
          .float-right
            bootstrap-toggle(
              data-size="mini",
              v-model="settings.google_ads_enabled",
              :options="{ on: $t('template.On'), off: $t('template.Off') }",
              data-width="120",
              data-height="38",
              data-onstyle="default",
              ref="cf_toggle"
            )
        div(v-if="settings.google_ads_enabled")
          a(
            href="https://developers.google.com/adwords/api/docs/guides/signup",
            target="_blank"
          ) {{ $t('template.ga_how_to') }}
          fieldset.form-group
            label.control-label.col-xs-4 Developer token
            .col-xs-8
              input.form-control(
                v-model="settings.google_ads_developer_token",
                name="google_ads_developer_token",
                v-validate="'required'",
                data-vv-as="Developer token"
              )
              h6.help-block(v-show="errors.has('google_ads_developer_token')") {{ errors.first('google_ads_developer_token') }}
          fieldset.form-group
            label.control-label.col-xs-4 Client customer id
            .col-xs-8
              input.form-control(
                v-model="settings.google_ads_client_customer_id",
                name="google_ads_client_customer_id",
                v-validate="'required'",
                data-vv-as="Client customer id"
              )
              h6.help-block(
                v-show="errors.has('google_ads_client_customer_id')"
              ) {{ errors.first('google_ads_client_customer_id') }}
          fieldset.form-group
            label.control-label.col-xs-4 User agent
            .col-xs-8
              input.form-control(
                v-model="settings.google_ads_user_agent",
                name="google_ads_user_agent",
                v-validate="'required'",
                data-vv-as="User agent"
              )
              h6.help-block(v-show="errors.has('google_ads_user_agent')") {{ errors.first('google_ads_user_agent') }}
          fieldset.form-group
            label.control-label.col-xs-4 Client id
            .col-xs-8
              input.form-control(
                v-model="settings.google_ads_client_id",
                name="google_ads_client_id",
                v-validate="'required'",
                data-vv-as="Client id"
              )
              h6.help-block(v-show="errors.has('google_ads_client_id')") {{ errors.first('google_ads_client_id') }}
            button.btn.btn-diga.my-2(
              @click="open_client_modal_g_ads",
              v-if="settings.google_ads_client_id"
            ) {{ $t('template.enable_access_and_get_code') }}
          fieldset.form-group
            label.control-label.col-xs-4 Client secret
            .col-xs-8
              input.form-control(
                v-model="settings.google_ads_client_secret",
                name="google_ads_client_secret",
                v-validate="'required'",
                data-vv-as="Client secret"
              )
              h6.help-block(v-show="errors.has('google_ads_client_secret')") {{ errors.first('google_ads_client_secret') }}
          fieldset.form-group
            label.control-label.col-xs-4 Refresh token
            .col-xs-8
              input.form-control(
                disabled="true",
                v-model="settings.google_ads_refresh_token",
                name="google_ads_refresh_token",
                v-validate="'required'",
                data-vv-as="Refresh token"
              )
              h6.help-block(v-show="errors.has('google_ads_refresh_token')") {{ errors.first('google_ads_refresh_token') }}
        div(v-else) {{ $t('template.Google_ads_info') }}
    .col-12.col-md-6.mb-3
      .diga-container.p-4
        .clearfix
          h2.float-left {{ $t('template.ip_3cx') }}
          .float-right
            bootstrap-toggle(
              data-size="mini",
              v-model="settings.cx_enabled",
              :options="{ on: $t('template.On'), off: $t('template.Off') }",
              data-width="120",
              data-height="38",
              data-onstyle="default",
              ref="cf_toggle"
            )
        div(v-if="settings.cx_enabled")
          a(href="/api/3cx/config", target="_blank") {{ $t('template.download_configuration') }}
        div(v-else) {{ $t('template.ip_3cx_desc') }}
  .row
    .col-12
      button.btn.btn-diga(v-on:click="save_settings") {{ $t('template.Save') }}
</template>

<script>
import { mapGetters } from "vuex";
export default {
  data() {
    return {
      settings: null,
      // zadarma_responsible_by_default: null,
      checkfront_removed_fields: [],
      mailchimp_lists: [],
      mailchimp_campaigns: [],
      service_states: [],
      selected_service_state_id: 0,
      is_new_list: false,
      new_list_name: "",
      selected_audience_id: 0,
      company: "",
      address1: "",
      address2: "",
      city: "",
      state: "",
      zip: "",
      country: "",
      phone: "",
      permission_reminder: "",
      from_name: "",
      from_email: "",
      tg_show_code: false,
      tg_code: "",
      google_ads_code: "",
    };
  },
  created() {
    this.settings = this.$store.getters.getGlobalSettings;

    // for(var ruser in this.responsible_users) {
    //     let usr = this.responsible_users[ruser];
    //     if(usr.zadarma_default_responsible == 1)
    //         this.zadarma_responsible_by_default = usr.id;
    // }
  },
  mounted() {
    document.title =
      this.$root.global_settings.site_name +
      " | " +
      this.$root.$t("template.IntegrationSettings");
    this.load_mailchimp_audiences();
    this.load_mailchimp_campaigns();
    this.load_service_states();
  },
  methods: {
    get_refresh_token_g_ads() {
      let url = "/api/google_ads/get_refresh_token";
      this.$root.global_loading = true;
      this.$http.post(url, { code: this.google_ads_code }).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.$toastr.s(
              this.$root.$t("template.Success"),
              this.$root.$t("template.Success")
            );
            this.settings.google_ads_refresh_token =
              res.data.refresh_token.refresh_token;
            jQuery("#modal-client-id").modal("hide");
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
    open_client_modal_g_ads() {
      jQuery("#modal-client-id").modal("show");
    },
    export_audience_to_mailchimp() {
      var payload = {};
      payload.selected_service_state_id = this.selected_service_state_id;
      payload.new_list_name = this.new_list_name;
      payload.selected_audience_id = this.selected_audience_id;
      payload.company = this.company;
      payload.address1 = this.address1;
      payload.address2 = this.address2;
      payload.city = this.city;
      payload.state = this.state;
      payload.zip = this.zip;
      payload.country = this.country;
      payload.phone = this.phone;
      payload.permission_reminder = this.permission_reminder;
      payload.from_name = this.from_name;
      payload.from_email = this.from_email;
      payload.language = this.$root.user.site_language;

      var url = "";
      if (this.is_new_list === true) {
        url = "/api/settings/integrations/mailchimp/audiences/create";
        this.$validator.validateAll().then((result) => {
          if (!result) {
            this.$toastr.w(
              this.$root.$t("template.Need_to_fill"),
              this.$root.$t("template.Warning")
            );
            return;
          }

          this.$root.global_loading = true;
          this.$http.post(url, payload).then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
              } else {
                this.$toastr.s(
                  this.$root.$t("template.Success"),
                  this.$root.$t("template.Success")
                );
                this.load_mailchimp_audiences();
                this.load_mailchimp_campaigns();
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
        });
      } else {
        url = "/api/settings/integrations/mailchimp/audiences/add";
        this.$root.global_loading = true;
        this.$http.post(url, payload).then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("template.Success"),
                this.$root.$t("template.Success")
              );
              this.load_mailchimp_audiences();
              this.load_mailchimp_campaigns();
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
    },
    load_service_states() {
      this.$root.global_loading = true;
      this.$http.get("/api/service_states").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.service_states = res.data;
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
    load_mailchimp_audiences() {
      if (
        this.settings.mailchimp_integration_enabled &&
        this.settings.mailchimp_api_key !== ""
      ) {
        this.$root.global_loading = true;
        this.$http.get("/api/settings/integrations/mailchimp/audiences").then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.mailchimp_lists = res.data.lists;
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
    },
    load_mailchimp_campaigns() {
      if (
        this.settings.mailchimp_integration_enabled &&
        this.settings.mailchimp_api_key !== ""
      ) {
        this.$root.global_loading = true;
        this.$http.get("/api/settings/integrations/mailchimp/campaigns").then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.mailchimp_campaigns = res.data.campaigns;
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
    },
    facebook_toggle() {
      if (!this.settings.fb_enabled) {
        this.$root.global_loading = true;
        let win = window.open("", "_blank");
        this.$http.get("/api/settings/integrations/facebook").then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              win.location.href = res.data.url;
              win.focus();
              let timer = setInterval(function () {
                if (win.closed) {
                  clearInterval(timer);
                  window.location.reload(true);
                }
              }, 500);
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
    },
    google_drive_toggle() {
      if (!this.settings.gd_enabled) {
        this.$root.global_loading = true;
        let win = window.open("", "_blank");
        this.$http.get("/api/settings/integrations/google_drive").then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              win.location.href = res.data.url;
              win.focus();
              let timer = setInterval(function () {
                if (win.closed) {
                  clearInterval(timer);
                  window.location.reload(true);
                }
              }, 500);
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
    },
    save_settings() {
      this.$validator.validateAll().then((result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        }
        let payload = JSON.parse(JSON.stringify(this.$data));

        if (!Push.Permission.has()) Push.Permission.request();

        // for(var ruser in this.responsible_users) {
        //     let usr = this.responsible_users[ruser];
        //     if(this.zadarma_responsible_by_default == usr.id)
        //          this.responsible_users[ruser].zadarma_default_responsible = 1;
        //     else this.responsible_users[ruser].zadarma_default_responsible = 0;
        // }
        this.settings.responsible_users = this.responsible_users;
        payload.global_settings = this.settings;
        payload.global_settings.checkfront_removed_fields = this.checkfront_removed_fields;
        this.$root.global_loading = true;
        this.$http.post("/api/settings/integrations", payload).then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("template.Settings_saved"),
                this.$root.$t("template.Success")
              );
            }
            this.$root.global_loading = false;
          },
          (res) => {
            this.$root.global_loading = false;
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
          }
        );
      });
    },
    add_field() {
      let field = {
        id: 0,
        field_name: "",
        destination: 1,
        type: 1,
        note: "",
        order: this.settings.checkfront_fields.length + 1,
      };
      this.settings.checkfront_fields.push(field);
    },
    remove_field(field) {
      if (confirm(this.$root.$t("template.Sure_remove_checkfront_field"))) {
        this.checkfront_removed_fields.push(field.id);
        let index = this.settings.checkfront_fields.indexOf(field);
        this.settings.checkfront_fields.splice(index, 1);
        for (let i = index; i < this.settings.checkfront_fields.length; i++) {
          this.settings.checkfront_fields[i]["order"]--;
        }
      }
    },
    field_up(field) {
      if (field.order > 1) {
        field.order--;
        let index = this.settings.checkfront_fields.indexOf(field);
        this.settings.checkfront_fields[index - 1].order++;
      }
    },
    field_down(field) {
      if (field.order < this.settings.checkfront_fields.length) {
        field.order++;
        let index = this.settings.checkfront_fields.indexOf(field);
        this.settings.checkfront_fields[index + 1].order--;
      }
    },
    //            fb_get_pages_list(){
    //                this.$root.global_loading = true;
    //                this.$http.get('/settings/integrations/facebook/get_pages_list').then(res => {
    //                    if (res.data.errcode == 1) {
    //                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
    //                    } else {
    //                        this.pages = res.data;
    //                    }
    //                    this.$root.global_loading = false;
    //                }, res => {
    //                    this.$root.global_loading = false;
    //                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
    //                });
    //            }
    getSecondPart(str) {
      return str.split("-")[1];
    },
    tg_send_code() {
      this.$root.global_loading = true;
      let payload = {
        tg_api_id: this.settings.tg_api_id,
        tg_api_hash: this.settings.tg_api_hash,
        tg_phone: this.settings.tg_phone,
      };
      this.$http.post("/api/telegram/send_code", payload).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            if (res.data.result === "ok") {
              this.tg_show_code = true;
            } else {
              this.$toastr.e(
                this.$root.$t("template.Check_entered_info"),
                this.$root.$t("template.Error")
              );
            }
          }
          this.$root.global_loading = false;
        },
        (res) => {
          this.$root.global_loading = false;
          this.$toastr.e(
            this.$root.$t("template.Server_error"),
            this.$root.$t("template.Error")
          );
        }
      );
    },
    tg_enter_code() {
      this.$root.global_loading = true;
      let payload = {
        tg_api_id: this.settings.tg_api_id,
        tg_api_hash: this.settings.tg_api_hash,
        tg_phone: this.settings.tg_phone,
        tg_code: this.tg_code,
      };
      this.$http.post("/api/telegram/enter_code", payload).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            if (res.data.result === "ok") {
              this.$toastr.s(
                this.$root.$t("template.tg_Integration_saved"),
                this.$root.$t("template.Success")
              );
            } else if (res.data.result === "need_password") {
              this.$toastr.e(
                this.$root.$t("template.need_password"),
                this.$root.$t("template.Error")
              );
            } else {
              this.$toastr.e(
                this.$root.$t("template.need_registration"),
                this.$root.$t("template.Error")
              );
            }
          }
          this.$root.global_loading = false;
          this.tg_show_code = false;
        },
        (res) => {
          this.$root.global_loading = false;
          this.tg_show_code = false;
          this.$toastr.e(
            this.$root.$t("template.Server_error"),
            this.$root.$t("template.Error")
          );
        }
      );
    },
  },
  computed: {
    ...mapGetters({
      event_types: "getEventTypes",
      responsible_users: "getNotRemovedUsers",
    }),
    checkfront_fields_ordered() {
      return this.settings.checkfront_fields.sort(function (a, b) {
        if (a.order > b.order) {
          return 1;
        } else if (b.order > a.order) {
          return -1;
        } else {
          return 0;
        }
      });
    },
  },
};
</script>