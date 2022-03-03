<style>
.icon {
  border: 1px solid #c7c7c7;
  border-right: none;
  line-height: 34px;
  text-align: center;
}
.colors-table td {
  padding: 5px;
  vertical-align: top;
}
.colors-table td.num {
  padding-top: 15px;
}
.picker-container {
  position: relative;
}
.service-types-table td {
  padding: 5px;
}
.hints {
  color: #949494;
}
.preview canvas {
  max-width: 100%;
  max-height: 100%;
  width: auto !important;
}
</style>

<template lang="pug">
div(v-if="global_settings")
  .row
    .col-12.col-md-6.mb-3
      .diga-container.p-4
        h2 {{ $t('service.Service_types') }}
        table.service-types-table.mb-2
          tr(v-for="(service_type, i) in types", style="margin-bottom: 5px")
            td
              | {{ i + 1 }}.
            td
              input.form-control(v-model="service_type.name")
            td
              input.form-control(
                :placeholder="$t('estimate.Preco')",
                type="number",
                min="0",
                step="0.01",
                v-model="service_type.price"
              )
            td
              button.btn(v-on:click="remove_service_type(service_type)") {{ $t('template.Remove') }}
        button.btn(v-on:click="add_service_type()") {{ $t('template.Add') }}
    .col-12.col-md-6.mb-3
      .diga-container.p-4
        h2 {{ $t('service.Service_settings') }}
        fieldset.form-group
          label.control-label.col-xs-3 {{ $t('service.Additional_service_state') }}
          .col-xs-9
            select.form-control(v-model="global_settings.add_service_state_id")
              option(
                v-for="state in states",
                v-bind:value="state.id",
                v-if="state.type == 0"
              ) {{ state.name }}
  .row.mb-3
    .col-12
      .diga-container.p-4
        h2 {{ $t('service.Service_scopes') }}
        process(
          :mystates="states",
          :current_order="null",
          :current_id="null",
          :p_editable="true",
          :scopes="my_scopes"
        )
        table.table.mb-2
          thead
            tr
              th(style="width: 70px") {{ $t('template.Color') }}
              th {{ $t('template.Name') }}
              th {{ $t('template.Start_service_state') }}
              th {{ $t('template.End_service_state') }}
              th {{ $t('template.Actions') }}
          tbody
            tr(v-for="scope in my_scopes")
              td(:style="{ 'background-color': scope.color }")
              td
                input.form-control(v-model="scope.name")
              td
                span(v-if="scope.start_service_state_id > 0") {{ current_service_states_by_id[scope.start_service_state_id].name }}
              td
                span(v-if="scope.end_service_state_id > 0") {{ current_service_states_by_id[scope.end_service_state_id].name }}
              td
                button.btn.btn-danger(@click="remove_scope(scope)") {{ $t('template.Remove') }}
        button.btn.btn-diga(@click="add_scope") {{ $t('template.Add') }}
  .row.mb-3
    .col-12
      .diga-container.p-4
        h2 {{ $t('service.Service_process') }}
        div
          i.fa.fa-question-circle &nbsp;
          | {{ $t('service.Global_variables') }}:
          br
          br
          strong {{ $t('service.Estimates') }}:
          br
          | {sent_estimate_numbers} - {{ $t('service.Sent_estimate_numbers') }}
          br
          br
          strong {{ $t('service.Tasks') }}:
          br
          | {event_start} - {{ $t('service.Event_start_datetime') }}
          br
          | {task_description} - {{ $t('service.Task_description') }}
          br
          br
          strong Google Drive:
          br
          | {uploaded_link} - {{ $t('service.Uploaded_link') }}
          br
          br
        .table-responsive
          table.table.table-striped.table-hover.colors-table(
            style="width: 100%"
          )
            thead
              tr
                th(style="width: 1px") №
                th(style="width: 1px") {{ $t('service.Order') }}
                th(style="width: 200px") {{ $t('service.Title') }}
                th(style="width: 120px") {{ $t('service.Type') }}
                th(style="width: 140px") {{ $t('service.Position') }}
                th {{ $t('service.Options') }}
                th(style="width: 150px; min-width: 150px") {{ $t('service.Color') }}
                th(style="width: 200px; min-width: 200px") {{ $t('service.Icon') }}
                th(style="width: 1px") {{ $t('template.Remove') }}
            tbody
              template(
                v-for="(service_state, i) in states",
                style="margin-bottom: 5px"
              )
                tr
                  td.num {{ service_state.order }}.
                  td
                    i.fa.fa-chevron-up(
                      style="cursor: pointer",
                      v-on:click="to_up(states, service_state)"
                    )
                    i.fa.fa-chevron-down(
                      style="cursor: pointer",
                      v-on:click="to_down(states, service_state)"
                    )
                  td
                    fieldset.form-group(
                      :class="{ 'has-error': errors.has('name' + service_state.order) }"
                    )
                      input.form-control(
                        v-model="service_state.name",
                        @change="update_scopes",
                        v-bind:name="'name' + service_state.order",
                        v-bind:placeholder="$t('service.Title')",
                        v-validate="'required'"
                      )
                  td
                    select.form-control(v-model="service_state.type")
                      option(value="0") {{ $t('service.State') }}
                      option(value="1") {{ $t('service.Button') }}
                  td
                    select.form-control(v-model="service_state.horizontal")
                      option(:value="1") {{ $t('service.Horizontal') }}
                      option(:value="0") {{ $t('service.Vertical') }}
                  td.form-horizontal
                    template(v-if="service_state.type == 0")
                      .col-xs-3
                        .checkbox
                          label.control-label
                            input(
                              type="checkbox",
                              v-model="service_state.can_click"
                            )
                            | {{ $t('service.Clickable') }}
                      .col-xs-3
                        .checkbox
                          label.control-label
                            input(
                              type="checkbox",
                              v-model="service_state.with_reason"
                            )
                            | {{ $t('service.With_reason') }}
                    template(v-if="service_state.type == 1")
                      fieldset.form-group
                        label.control-label.col-xs-3 {{ $t('service.Destination_state') }}
                        .col-xs-9
                          select.form-control(
                            v-model="service_state.destination_state_id"
                          )
                            option(value="0") {{ $t('service.Not_specified') }}
                            option(
                              v-for="state in states",
                              v-bind:value="state.id"
                            ) {{ state.name }}
                  td.picker-container
                    sketch-picker(
                      v-if="selected[service_state.order - 1]",
                      v-model="service_state.color",
                      v-on-clickaway="hide_picker"
                    )
                    .color-icon.color(
                      v-bind:style="{ 'background-color': service_state.color.hex }",
                      v-on:click="show_picker(service_state.order - 1)"
                    )
                    input.form-control.settings-inputs(
                      style="width: 100px",
                      v-model="service_state.color.hex"
                    )
                  td
                    .color-icon.icon
                      i(v-bind:class="['fa', service_state.icon]")
                    input.form-control.settings-inputs(
                      style="width: 150px",
                      v-model="service_state.icon"
                    )
                  td
                    button.btn(
                      v-on:click="remove_service_state(service_state)"
                    ) {{ $t('template.Remove') }}
                tr
                  td(colspan="9", style="border-top: none")
                    table.table(style="width: 100%")
                      thead
                        tr
                          th(style="width: 1px") №
                          th(style="width: 1px") {{ $t('service.Order') }}
                          th(style="width: 150px") {{ $t('service.Action_type') }}
                          th {{ $t('service.Options') }}
                          th(style="width: 1px") {{ $t('template.Remove') }}
                      tbody
                        tr(
                          v-for="(action, j) in service_state.service_state_actions"
                        )
                          td.num {{ action.order }}.
                          td
                            i.fa.fa-chevron-up(
                              style="cursor: pointer",
                              v-on:click="to_up(service_state.service_state_actions, action)"
                            )
                            i.fa.fa-chevron-down(
                              style="cursor: pointer",
                              v-on:click="to_down(service_state.service_state_actions, action)"
                            )
                          td
                            select.form-control(v-model="action.type")
                              option(value="1") Email
                              option(value="2") SMS
                              option(value="3") {{ $t('service.Task') }}
                              option(value="4") {{ $t('service.Fill_checklist') }}
                              option(value="5") {{ $t('service.Upload_google_drive') }}
                              option(value="6") {{ $t('service.Open_url') }}
                          td.form-horizontal
                            .row(v-if="action.type == 1")
                              .col-8
                                .form-group.row
                                  label.col-form-label.col-2 {{ $t('service.Email_recipient') }}
                                  .col-7
                                    select.form-control(
                                      v-model="action.email_type"
                                    )
                                      option(:value="1") {{ $t('service.Client') }}
                                      option(:value="2") {{ $t('service.Service_responsible') }}
                                      option(:value="3") {{ $t('service.Fix_address') }}
                                      option(:value="4") {{ $t('service.Task_responsible') }}
                                  .col-3
                                    .form-check
                                      input.form-check-input.position-relative.mr-2(
                                        type="checkbox",
                                        v-model="action.email_cc",
                                        :id="'email-cc-' + service_state.order + '-' + action.order"
                                      )
                                      label.col-form-label(
                                        :for="'email-cc-' + service_state.order + '-' + action.order"
                                      ) {{ $t('service.Copy_to_sender') }}
                                .form-group.row(v-if="action.email_type == 3")
                                  label.col-form-label.col-2 {{ $t('service.Email_address') }}
                                  .col-10
                                    input.form-control(
                                      v-model="action.email_address"
                                    )
                                .form-group.row
                                  label.col-form-label.col-2 {{ $t('service.Email_subject') }}<sup>*</sup>
                                  .col-10
                                    input.form-control(
                                      v-model="action.email_subject"
                                    )
                                .form-group.row
                                  label.col-form-label.col-2 {{ $t('service.Email_text') }}<sup>*</sup>
                                  .col-10
                                    textarea.form-control(
                                      v-model="action.email_text"
                                    )
                                .form-group.row
                                  .col-10.offset-2
                                    .form-check
                                      input.form-check-input(
                                        type="checkbox",
                                        v-model="action.email_show",
                                        :id="'email-show-' + service_state.order + '-' + action.order"
                                      )
                                      label.form-check-label(
                                        :for="'email-show-' + service_state.order + '-' + action.order"
                                      ) {{ $t('service.Show_email_before_sending') }}
                                div(
                                  style="text-align: center; font-size: 16px; margin-bottom: 10px"
                                )
                                  | {{ $t('service.Attachments') }}
                                .form-group.row
                                  label.col-form-label.col-2 {{ $t('service.Attach_estimate') }}
                                  .col-4
                                    select.form-control(
                                      v-model="action.email_include_estimate_type"
                                    )
                                      option(value="0") {{ $t('service.Not_attach') }}
                                      option(value="1") {{ $t('service.Master_estimate') }}
                                      option(value="2") {{ $t('service.Selected_estimates') }}
                                  label.col-form-label.col-2 {{ $t('service.Attach_checklist') }}
                                  .col-4
                                    select.form-control(
                                      v-model="action.email_include_checklist_id"
                                    )
                                      option(value="0") {{ $t('service.Not_attach') }}
                                      option(
                                        v-for="checklist in checklists",
                                        v-bind:value="checklist.id"
                                      ) {{ checklist.name }}
                                .form-group.row
                                  .col-5.offset-2
                                    vue-core-image-upload(
                                      style="max-width: 300px",
                                      :class="['btn', 'green', 'text-center']",
                                      @imageuploading="imageuploading",
                                      @imageuploaded="(res) => { imageuploaded(res, action); }",
                                      @errorhandle="imageerror",
                                      :headers="{ Authorization: $root.access_token }",
                                      :extensions="'png,gif,jpeg,jpg,pdf'",
                                      :max-file-size="$root.max_file_size",
                                      :text="$t('service.Attach_file')",
                                      url="/api/file_upload"
                                    )
                                    div(
                                      v-if="action.email_file != null",
                                      style="margin-top: 10px"
                                    )
                                      a(v-bind:href="action.email_file") {{ action.email_filename + extension(action.email_file) }}
                                      .preview(
                                        style="width: 200px; height: 200px; margin-top: 10px"
                                      )
                                        template(
                                          v-if="extension(action.email_file) == '.jpg' || extension(action.email_file) == '.jpeg' || extension(action.email_file) == '.png'"
                                        )
                                          img(
                                            v-bind:src="action.email_file",
                                            style="max-width: 100%; max-height: 100%"
                                          )
                                        template(
                                          v-if="extension(action.email_file) == '.pdf'"
                                        )
                                          pdf(
                                            v-bind:src="action.email_file",
                                            style="max-width: 100%; height: 200px"
                                          )
                                  .col-5
                                    .form-check
                                      input.form-check-input.position-relative.mr-2(
                                        type="checkbox",
                                        v-model="action.email_include_resource_attachments",
                                        :id="'email-include-' + service_state.order + '-' + action.order"
                                      )
                                      label.col-form-label(
                                        :for="'email-include-' + service_state.order + '-' + action.order"
                                      ) {{ $t('service.Attach_resource_attachments') }}
                              .col-4.hints
                                i.fa.fa-question-circle &nbsp;
                                | {{ $t('service.Local_variables') }}:
                                br
                                br
                                strong {{ $t('service.Selected_estimates') }}:
                                br
                                | {selected_estimate_numbers} - {{ $t('service.Selected_estimate_numbers') }}
                                br
                                strong {{ $t('service.Master_estimate') }}:
                                br
                                | {master_estimate_number} - {{ $t('service.Master_estimate_number') }}
                                br
                                | {first_pay_step_percent} - {{ $t('service.First_pay_step_percent') }}
                                br
                                strong {{ $t('service.Service') }}:
                                br
                                | {service_number} - {{ $t('service.Service_number') }}
                                br
                                | {service_address} - {{ $t('service.Service_address') }}
                                br
                                | {service_priority} - {{ $t('service.Service_priority') }}
                                br
                                | {service_url} - {{ $t('service.Service_url') }}
                                br
                                strong {{ $t('service.Client') }}:
                                br
                                | {client_fullname} - {{ $t('service.Main_contact_fullname') }}
                                br
                                | {client_email} - {{ $t('service.Main_contact_email') }}
                                br
                                | {client_phones} - {{ $t('service.Main_contact_phones') }}
                                br
                                br
                                i.fa.fa-info-circle &nbsp;
                                | {{ $t('service.Overwrites_global_variable') }} {sent_estimate_numbers}
                                br
                                i.fa.fa-info-circle &nbsp;
                                | {{ $t('service.Not_saved_in_log') }}
                            .row(v-if="action.type == 2")
                              .col-8
                                .form-group.row
                                  label.col-form-label.col-2 {{ $t('service.SMS_recipient') }}
                                  .col-10
                                    select.form-control(
                                      v-model="action.sms_type"
                                    )
                                      option(value="1") {{ $t('service.Client') }}
                                      option(value="2") {{ $t('service.Fix_phone') }}
                                .form-group.row(v-if="action.sms_type == 2")
                                  label.col-form-label.col-2 {{ $t('service.SMS_phone') }}
                                  .col-10
                                    input.form-control(v-model="action.sms_to")
                                .form-group.row(
                                  :class="{ 'has-error': errors.has('text-' + i.toString() + '-' + j.toString()) }"
                                )
                                  label.col-form-label.col-2 {{ $t('service.SMS_text') }}<sup>*</sup>
                                  .col-10
                                    textarea.form-control(
                                      v-validate="{ required: true, regex: /^[a-zA-Z0-9!@#$%^*_| ,{}-]{0,160}$/ }",
                                      :name="'text-' + i.toString() + '-' + j.toString()",
                                      v-model="action.sms_text",
                                      v-bind:data-vv-as="$t('service.SMS_text').toLowerCase()"
                                    )
                                    span.help-block(
                                      v-show="errors.has('text-'+i.toString()+'-'+j.toString())"
                                    ) {{ errors.first('text-' + i.toString() + '-' + j.toString()) }}
                              .col-4.hints
                                i.fa.fa-question-circle &nbsp;
                                | {{ $t('service.Local_variables') }}:
                                br
                                br
                                strong {{ $t('service.Client') }}:
                                br
                                | {client_email} - {{ $t('service.Main_contact_email') }}
                                br
                                strong {{ $t('service.Service') }}:
                                br
                                | {estimate_summ} - {{ $t('service.Master_estimate_sum') }}
                                br
                                | {paid_summ} - {{ $t('service.Paid_sum') }}
                                br
                                | {left_summ} - {{ $t('service.Remaining_sum') }}
                            .row(v-if="action.type == 3")
                              .col-8
                                .form-group.row
                                  label.col-form-label.col-2 {{ $t('service.Date') }}
                                  .col-10
                                    select.form-control(
                                      v-model="action.event_date_type"
                                    )
                                      option(:value="0") {{ $t('service.Selected_date') }}
                                      option(:value="1") +1 {{ tr($t('template.day'), 1) }}
                                      option(:value="2") +2 {{ tr($t('template.day'), 2) }}
                                      option(:value="3") +3 {{ tr($t('template.day'), 3) }}
                                      option(:value="4") +4 {{ tr($t('template.day'), 4) }}
                                      option(:value="5") +5 {{ tr($t('template.day'), 5) }}
                                      option(:value="6") +6 {{ tr($t('template.day'), 6) }}
                                      option(:value="7") +1 {{ tr($t('template.week'), 1) }}
                                      option(:value="12") +2 {{ tr($t('template.week'), 2) }}
                                      option(:value="13") +3 {{ tr($t('template.week'), 3) }}
                                      option(:value="8") +1 {{ tr($t('template.month'), 1) }}
                                      option(:value="9") +2 {{ tr($t('template.month'), 2) }}
                                      option(:value="10") +6 {{ tr($t('template.month'), 6) }}
                                      option(:value="11") +1 {{ tr($t('template.year'), 1) }}
                                .form-group.row
                                  label.col-form-label.col-2 {{ $t('service.Task_type') }}
                                  .col-10
                                    select.form-control(
                                      v-model="action.event_type_id"
                                    )
                                      option(value="0") {{ $t('service.Selected_type') }}
                                      option(
                                        v-for="event_type in event_types",
                                        :value="event_type.id"
                                      ) {{ event_type.title }}
                                .form-group.row
                                  label.col-form-label.col-2 {{ $t('service.Responsible') }}
                                  .col-10
                                    select.form-control(
                                      v-model="action.event_user_id"
                                    )
                                      option(value="0") {{ $t('service.Selected_user') }}
                                      option(
                                        v-for="user in users",
                                        :value="user.id"
                                      ) {{ user.name }}
                                .form-group.row
                                  label.col-form-label.col-2 {{ $t('service.Description') }}<sup>*</sup>
                                  .col-10
                                    textarea.form-control(
                                      v-model="action.event_description"
                                    )
                                .form-group.row
                                  .col-10.offset-2
                                    label.col-form-label
                                      input(
                                        type="checkbox",
                                        v-model="action.event_description_not_editable"
                                      )
                                      | &nbsp;{{ $t('service.Not_editable_description') }}
                              .col-4.hints
                                i.fa.fa-info-circle &nbsp;
                                | {{ $t('service.If_all_fields_not_editable') }}
                                br
                                br
                                strong {{ $t('service.Service') }}:
                                br
                                | {service_note} - {{ $t('service.Service_note') }}
                                br
                                br
                                i.fa.fa-info-circle &nbsp;
                                | {{ $t('service.Overwrites_global_variable') }} {event_start}
                                br
                                i.fa.fa-info-circle &nbsp;
                                | {{ $t('service.Overwrites_global_variable') }} {task_description}
                            .row(v-if="action.type == 4")
                              .col-8
                                .form-group.row
                                  label.col-form-label.col-3 {{ $t('template.Checklist') }}
                                  .col-9
                                    select.form-control(
                                      v-model="action.checklist_id"
                                    )
                                      option(
                                        v-for="checklist in checklists",
                                        v-bind:value="checklist.id"
                                      ) {{ checklist.name }}
                              .col-4
                            .row(v-if="action.type == 5")
                              .col-8
                                .form-group.row
                                  label.col-form-label.col-3 {{ $t('template.Folder') }}
                                  .col-9
                                    input.form-control(v-model="action.path")
                              .col-4.hints
                                i.fa.fa-info-circle &nbsp;
                                | {{ $t('service.Overwrites_global_variable') }} {uploaded_link}
                            .row(v-if="action.type == 6")
                              .col-8
                                .form-group.row
                                  label.col-form-label.col-3 {{ $t('service.URL') }}*
                                  .col-9
                                    input.form-control(v-model="action.url")
                              .col-4
                          td
                            button.btn.yellow-gold(
                              v-on:click="remove_service_state_action(service_state, action)"
                            ) {{ $t('template.Remove') }}
                      tfoot
                        td(colspan="5")
                          | <sup>*</sup> - {{ $t('service.Fields_can_use_variables') }}
                    button.btn.green(
                      v-on:click="add_service_state_action(service_state)"
                    ) {{ $t('service.Add_action') }}
        button.btn(v-on:click="add_service_state()") {{ $t('service.Add_state') }}
  .row
    .col-12
      button.btn.btn-diga(v-on:click="save_settings()") {{ $t('template.Save') }}
</template>

<script>
import smartPlurals from "smart-plurals";
import process from "../../../../../../Client/resources/assets/js/components/card/services/process.vue";
import { mapGetters } from "vuex";

export default {
  data() {
    return {
      states: [],
      types: [],
      types_removed: [],
      states_removed: [],
      actions_removed: [],
      selected: [],
      my_scopes: [],
      scopes_removed: [],
      global_settings: null,
      checklists: [],
    };
  },
  components: {
    process,
  },
  mounted() {
    document.title =
      this.$root.global_settings.site_name +
      " | " +
      this.$root.$t("service.Service_settings");
    this.load_checklists();
    //
    this.types = JSON.parse(JSON.stringify(this.c_service_types));
    //
    this.global_settings = JSON.parse(JSON.stringify(this.c_global_settings));
    //
    this.states = JSON.parse(JSON.stringify(this.c_service_states));
    this.update_state_colors();
    //
    this.my_scopes = JSON.parse(JSON.stringify(this.c_service_scopes));
    this.update_scopes_colors();
  },
  watch: {
    c_service_types() {
      this.types = JSON.parse(JSON.stringify(this.c_service_types));
    },
    c_global_settings() {
      this.global_settings = JSON.parse(JSON.stringify(this.c_global_settings));
    },
    c_service_states() {
      this.states = JSON.parse(JSON.stringify(this.c_service_states));
      this.update_state_colors();
    },
    c_service_scopes() {
      this.my_scopes = JSON.parse(JSON.stringify(this.c_service_scopes));
      this.update_scopes_colors();
    },
  },
  methods: {
    update_state_colors() {
      this.selected = Array(this.states.length).fill(false);
      for (let i = 0; i < this.states.length; i++) {
        this.states[i].color = {
          hex: this.states[i].color,
        };
      }
    },
    load_checklists() {
      this.$root.global_loading = true;
      this.$http.get("/api/checklists?limit=9999").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.checklists = res.data.rows;
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
    update_scopes() {
      this.$bus.$emit("rerender_scopes");
    },
    update_scopes_colors() {
      for (let i = 0; i < this.my_scopes.length; i++) {
        this.my_scopes[i].color = this.get_color_from_int(i + 1);
      }
    },
    add_scope() {
      let scope = {
        id: 0,
        start_service_state_id: this.states[0].id,
        end_service_state_id: this.states[0].id,
        name:
          this.$root.$t("service.Scope") + " " + (this.my_scopes.length + 1),
        color: this.get_color_from_int(this.my_scopes.length + 1),
      };
      this.my_scopes.push(scope);
    },
    remove_scope(scope) {
      if (confirm(this.$root.$t("service.Are_you_sure_want_to_delete_scope"))) {
        if (this.my_scopes.length == 1) {
          this.$toastr.w(
            this.$root.$t("service.Need_to_exist_at_least_one_scope"),
            this.$root.$t("template.Warning")
          );
        } else {
          this.scopes_removed.push(scope.id);
          let index = this.my_scopes.indexOf(scope);
          this.my_scopes.splice(index, 1);
          this.update_scopes_colors();
        }
      }
    },
    get_color_from_int(i) {
      switch (i) {
        case 1:
          return "#ff0000";
        case 2:
          return "#15ba02";
        case 3:
          return "#3e59ff";
        default:
          return "#535353";
      }
    },
    tr(str, amount) {
      let lang = this.$root.locale;
      return smartPlurals.Plurals.getRule(lang)(amount, str.split(" | "));
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
        this.$root.global_loading = true;
        let payload = JSON.parse(JSON.stringify(this.$data));
        for (let i = 0; i < payload.states.length; i++) {
          payload.states[i].color = payload.states[i].color.hex;
        }
        payload.selected = null;
        payload.global_settings = this.global_settings;
        this.$http.post("/api/services/settings", payload).then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("template.Settings_saved"),
                this.$root.$t("template.Success")
              );
              this.types_removed = [];
              this.states_removed = [];
              this.actions_removed = [];
              this.scopes_removed = [];
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
    },
    show_picker(i) {
      Vue.set(this.selected, i, true);
    },
    hide_picker() {
      for (let i = 0; i < this.states.length; i++) {
        Vue.set(this.selected, i, false);
      }
    },
    remove_service_type(service_type) {
      if (
        confirm(
          this.$root.$t("service.Are_you_sure_want_to_delete_service_type")
        )
      ) {
        this.types_removed.push(service_type.id);
        let index = this.types.indexOf(service_type);
        this.types.splice(index, 1);
      }
    },
    add_service_type() {
      let service_type = {
        id: 0,
        name: "",
      };
      this.types.push(service_type);
    },
    remove_service_state(service_state) {
      if (
        confirm(
          this.$root.$t("service.Are_you_sure_want_to_delete_service_state")
        )
      ) {
        this.states_removed.push(service_state.id);
        let index = this.states.indexOf(service_state);
        this.states.splice(index, 1);
        this.selected.splice(index, 1);
        for (var i = index; i < this.states.length; i++) {
          this.states[i].order--;
        }
        let $this = this;
        this.my_scopes.forEach((scope) => {
          // if removing status is the beginning of some scope
          if (scope.start_service_state_id == service_state.id) {
            if ($this.states.length == index) {
              // if removing status is the end of process
              scope.start_service_state_id = $this.states[index - 1].id;
            } else {
              scope.start_service_state_id = $this.states[index].id;
            }
          }
          // if removing status is the end of some scope
          if (scope.end_service_state_id == service_state.id) {
            if ($this.states.length == index) {
              // if removing status is the end of process
              scope.end_service_state_id = $this.states[index - 1].id;
            } else {
              scope.end_service_state_id = $this.states[index].id;
            }
          }
        });
        // change additional service default status
        if (this.global_settings.add_service_state_id == service_state.id) {
          this.global_settings.add_service_state_id = this.states[0].id;
          this.$toastr.w(
            this.$root.$t("service.Additional_service_status_changed"),
            this.$root.$t("template.Warning")
          );
        }
      }
    },
    add_service_state() {
      let service_state = {
        id: 0,
        name: "",
        horizontal: 1,
        type: 0,
        color: { hex: "#e7505a" },
        icon: "fa-circle",
        can_click: true,
        with_reason: false,
        destination_state_id: 0,
        service_state_actions: [],
        order: this.states.length == 0 ? 1 : this.states.length + 1,
      };
      this.states.push(service_state);
      this.selected.push(false);
    },
    remove_service_state_action(service_state, service_state_action) {
      if (
        confirm(this.$root.$t("service.Are_you_sure_want_to_delete_action"))
      ) {
        this.actions_removed.push(service_state_action.id);
        let index = service_state.service_state_actions.indexOf(
          service_state_action
        );
        service_state.service_state_actions.splice(index, 1);
        for (
          var i = index;
          i < service_state.service_state_actions.length;
          i++
        ) {
          service_state.service_state_actions[i].order--;
        }
      }
    },
    add_service_state_action(service_state) {
      let service_state_action = {
        id: 0,
        type: 1,
        email_type: 1,
        email_subject: null,
        email_address: null,
        email_file: null,
        email_filename: null,
        email_text: null,
        email_include_estimate_type: 0,
        email_include_checklist_id: 0,
        email_include_resource_attachments: false,
        email_show: true,
        sms_text: null,
        //                    sms_from: null,
        sms_type: 1,
        sms_to: null,
        event_type_id: 0,
        event_user_id: 0,
        event_date_type: 0,
        checklist_id: null,
        order:
          service_state.service_state_actions.length == 0
            ? 1
            : service_state.service_state_actions.length + 1,
      };
      service_state.service_state_actions.push(service_state_action);
    },
    to_up(states, service_state) {
      if (service_state.order > 1) {
        states.splice(service_state.order - 1, 1);
        states.splice(service_state.order - 2, 0, service_state);
        states[service_state.order - 1].order++;
        service_state.order--;
        this.update_scopes();
      }
    },
    to_down(states, service_state) {
      if (service_state.order < states.length) {
        states.splice(service_state.order - 1, 1);
        states.splice(service_state.order, 0, service_state);
        states[service_state.order - 1].order--;
        service_state.order++;
        this.update_scopes();
      }
    },
    imageuploading() {
      this.$root.global_loading = true;
    },
    imageuploaded(res, action) {
      this.$root.global_loading = false;
      if (res.errcode == 0) {
        action.email_file = res.url;
        action.email_filename = res.name;
      } else {
        this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
      }
    },
    imageerror(e) {
      this.$root.global_loading = false;
      this.$toastr.e(e, this.$root.$t("template.Error"));
    },
    extension: function (str) {
      return str ? str.substring(str.lastIndexOf(".")) : "";
    },
  },
  computed: {
    ...mapGetters({
      event_types: "getEventTypes",
      users: "getUsers",
      c_service_types: "getServiceTypes",
      c_global_settings: "getGlobalSettings",
      c_service_states: "getNotRemovedServiceStates",
      c_service_scopes: "getServiceScopes",
    }),
    current_service_states_by_id() {
      return this.states.reduce(function (map, obj) {
        map[obj.id] = obj;
        return map;
      }, {});
    },
  },
};
</script>