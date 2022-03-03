<style>
.cov-vue-date {
  width: 100%;
}
.g-core-image-corp-container {
  z-index: 9999 !important;
}
span.input-group-addon {
  font-size: 20px;
  margin-right: 10px;
  line-height: 38px;
}
.my-date {
  width: 1%;
  flex: 1 1 auto;
}
.my-date input {
  border-top-left-radius: 0 !important;
  border-bottom-left-radius: 0 !important;
  line-height: 24px !important;
}
.no-border-radius-left {
  border-top-left-radius: 0 !important;
  border-bottom-left-radius: 0 !important;
}
</style>

<template lang="pug">
div(v-if="currentUser")
  .row.mb-1(style="text-align: right")
    .col-12
      router-link.btn.btn-light(
        :to="{ name: 'user_blank' }",
        style="margin: 0 10px 10px 0",
        target="_blank"
      ) {{ $t('hr.Print_blank') }}
      router-link.btn.btn-light(
        :to="{ name: 'user_card', params: { id: currentUser.id } }",
        v-if="!isCreating",
        style="margin: 0 10px 10px 0",
        target="_blank"
      ) {{ $t('hr.Print_card') }}
      button.btn.btn-diga(
        type="button",
        v-on:click="save",
        style="margin-bottom: 10px"
      ) {{ $t('template.Save') }}
  .row
    section.col-12.col-md-6.col-lg-3
      .row.mb-3
        .col-12
          .diga-container.p-4
            fieldset.form-group.text-center(style="padding-top: 20px")
              img(:src="src", style="max-width: 300px; width: 100%")
              br
              vue-core-image-upload(
                style="max-width: 300px; width: 100%",
                :class="['btn', 'green', 'text-center']",
                :crop="'server'",
                @imageuploading="imageuploading",
                @imageuploaded="imageuploaded",
                @errorhandle="imageerror",
                :headers="{ Authorization: $root.access_token }",
                :extensions="'png,gif,jpeg,jpg'",
                :max-file-size="$root.max_file_size",
                :text="$t('hr.Upload_image')",
                url="/api/photo_upload"
              )
              div(v-show="loading")
                .loader.sm-loader
      .row.mb-3
        .col-12
          .diga-container.p-4
            h2 {{ $t('hr.System_information') }}
            fieldset.form-group.col-xs-12(
              :class="{ 'has-error': errors.has('email') }"
            )
              label.control-label {{ $t('hr.Email') }}
              input.form-control(
                placeholder="Email",
                name="email",
                v-validate="'required|email'",
                type="text",
                v-model="currentUser.email"
              )
              span.help-block(v-show="errors.has('email')") {{ errors.first('email') }}
              button.btn.btn-diga(
                v-if="!isCreating && $root.user.is_admin",
                v-on:click="send_password_reset_email",
                style="margin: 10px 10px 10px 0"
              ) {{ $t('template.send_email_to_reset_password') }}
              button.btn.btn-diga(
                v-if="!isCreating && $root.user.is_admin",
                v-on:click="show_email = !show_email",
                style="margin: 0 10px 0 0"
              ) {{ $t('hr.Edit_email_pass') }}
            fieldset.form-group.col-xs-12(
              v-if="currentUser.id == null || show_email"
            )
              label.control-label {{ $t('hr.Email_password') }}
              input.form-control(
                v-bind:placeholder="$t('hr.Email_password')",
                type="password",
                v-model="currentUser.email_password"
              )
            fieldset.form-group.col-xs-12(
              :class="{ 'has-error': errors.has('password') }",
              v-if="currentUser.id == null || show_user"
            )
              label.control-label {{ $t('hr.Account_password') }}
              .input-group
                .input-group-prepend
                  .input-group-text
                    i.fa.fa-lock.fa-fw
                input.form-control(
                  v-bind:placeholder="$t('hr.Password')",
                  name="password",
                  v-validate="'required|min:8'",
                  type="text",
                  v-model="currentUser.password",
                  v-bind:data-vv-as="$t('hr.Account_password').toLowerCase()"
                )
                span.input-group-btn
                  button.btn.btn-diga.m-0.no-border-radius-left(
                    type="button",
                    v-on:click="currentUser.password = generate_password()"
                  )
                    i.fa.fa-arrow-left.fa-fw
                    | {{ $t('hr.Random') }}
              span.help-block(v-show="errors.has('password')") {{ errors.first('password') }}
            fieldset.form-group.col-xs-12
              label.control-label PIN
              input.form-control(
                placeholder="PIN",
                type="text",
                disabled,
                v-model="currentUser.pin"
              )
      .row.mb-3
        .col-12
          .diga-container.p-4
            h2 {{ $t('hr.Work_information') }}
            .row
              fieldset.form-group.col-4
                label.control-label(style="width: 100%") {{ $t('hr.Salary_type') }}
                bootstrap-toggle(
                  v-model="currentUser.salary_type",
                  :options="{ on: $t('hr.Por_hora'), off: $t('hr.Mensal') }",
                  style="width: 100%",
                  ref="salary_type"
                )
              fieldset.form-group.col-8(
                :class="{ 'has-error': errors.has('salary') }"
              )
                label.control-label {{ $t('hr.Salary') }}
                input.form-control(
                  placeholder="Salary",
                  type="number",
                  name="salary",
                  v-model="currentUser.salary",
                  min="0",
                  v-validate="'min_value:0'"
                )
                span.help-block(v-show="errors.has('salary')") {{ errors.first('salary') }}
              fieldset.form-group.col-4
                label.control-label(style="width: 100%") {{ $t('hr.Status') }}
                bootstrap-toggle(
                  v-model="currentUser.active",
                  :options="{ on: $t('hr.Active'), off: $t('hr.Inactive') }",
                  style="width: 100%",
                  ref="active"
                )
              fieldset.form-group.col-8(
                :class="{ 'has-error': errors.has('position') }"
              )
                label.control-label {{ $t('hr.Position') }}
                input.form-control(
                  :placeholder="$t('hr.Position')",
                  type="text",
                  name="position",
                  v-model="currentUser.position",
                  v-validate="'required'"
                )
                span.help-block(v-show="errors.has('position')") {{ errors.first('position') }}
              fieldset.form-group.col-4
                label.control-label(style="width: 100%") {{ $t('template.vacation_days_left') }}
                input.form-control(
                  type="number",
                  name="vacation_days_left",
                  v-model="currentUser.vacation_days_left",
                  min="0",
                  step="1"
                )
      .row.mb-3
        .col-12
          .diga-container.p-4
            h2 {{ $t('hr.Emergency_contact') }}
            fieldset.form-group
              label.control-label {{ $t('hr.Name') }}
              input.form-control(
                v-bind:placeholder="$t('hr.Name')",
                type="text",
                v-model="currentUser.emergency_name"
              )
            fieldset.form-group
              label.control-label {{ $t('hr.Contact') }}
              input.form-control(
                v-bind:placeholder="$t('hr.Contact')",
                type="text",
                v-model="currentUser.emergency_contact"
              )
            fieldset.form-group
              label.control-label {{ $t('hr.Relation') }}
              input.form-control(
                v-bind:placeholder="$t('hr.Relation')",
                type="text",
                v-model="currentUser.emergency_relation"
              )
    section.col-12.col-md-6.col-lg-9
      .row.mb-3
        .col-12
          .diga-container.p-4
            h2 {{ $t('hr.Personal_information') }}
            .row
              fieldset.form-group.col-12(
                :class="{ 'has-error': errors.has('name') }"
              )
                label.control-label {{ $t('hr.Full_Name') }}
                input.form-control(
                  v-bind:placeholder="$t('hr.Full_Name')",
                  name="name",
                  v-validate="'required'",
                  type="text",
                  v-model="currentUser.name",
                  v-bind:data-vv-as="$t('hr.Full_Name').toLowerCase()"
                )
                span.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
            .row
              fieldset.form-group.col-12.col-lg-4
                label.control-label {{ $t('hr.Birthday_date') }}
                date-picker(
                  format="DD.MM.YYYY",
                  v-model="currentUser.birthday",
                  :first-day-of-week="$root.global_settings.first_day_of_week",
                  :value-type="$root.valueType",
                  :lang="$root.locale",
                  :width="'100%'"
                )
              fieldset.form-group.col-12.col-lg-4(
                :class="{ 'has-error': errors.has('additional_email') }"
              )
                label.control-label {{ $t('hr.User_email') }}
                .input-group
                  .input-group-prepend
                    .input-group-text
                      i.fa.fa-envelope
                  input.form-control(
                    v-bind:placeholder="$t('hr.User_email')",
                    name="additional_email",
                    v-validate="'email'",
                    data-vv-as="User Email",
                    type="text",
                    v-model="currentUser.additional_email"
                  )
                  span.help-block(v-show="errors.has('additional_email')") {{ errors.first('additional_email') }}
              fieldset.form-group.col-12.col-lg-4
                label.control-label {{ $t('hr.marital_status') }}
                select.form-control(v-model="currentUser.marital_status_id")
                  option(value="1") {{ $t('hr.married') }}
                  option(value="2") {{ $t('hr.single') }}
                  option(value="3") {{ $t('hr.divorsed') }}
            .row
              fieldset.form-group.col-12.col-lg-6
                label.control-label {{ $t('hr.Cell_Phone_Number') }}
                //- vue-tel-input(
                //-   v-model="currentUser.cell_phone",
                //-   @input="phone_input_change"
                //- )
                //- vue-phone-number-input(
                //-     v-model="currentUser.cell_phone" 
                //-     default-country-code="PT"
                //-     :preferred-countries="['PT', 'ES', 'RU']"
                //-     :only-countries="['PT', 'RU', 'ES', 'BR', 'US']"
                //-     @update="phone_input_change"
                //-     )
                .input-group
                  .input-group-prepend
                    .input-group-text
                      i.fa.fa-mobile
                  input.form-control(
                    v-bind:placeholder="$t('hr.Cell_Phone_Number')",
                    type="text",
                    v-model="currentUser.cell_phone"
                  )

              fieldset.form-group.col-12.col-lg-6
                label.control-label {{ $t('hr.Home_Phone_Number') }}
                .input-group
                  .input-group-prepend
                    .input-group-text
                      i.fa.fa-phone
                  input.form-control(
                    v-bind:placeholder="$t('hr.Home_Phone_Number')",
                    type="text",
                    v-model="currentUser.home_phone"
                  )
            .row
              fieldset.form-group.col-12.col-lg-8
                label.control-label {{ $t('hr.Address') }}
                .input-group
                  .input-group-prepend
                    .input-group-text
                      i.fa.fa-map-marker
                  input.form-control(
                    v-bind:placeholder="$t('hr.Address')",
                    type="text",
                    v-model="currentUser.address"
                  )
              fieldset.form-group.col-12.col-lg-4
                label.control-label {{ $t('hr.Postal_Code') }}
                input.form-control(
                  v-bind:placeholder="$t('hr.Postal_Code')",
                  name="postal",
                  type="text",
                  v-model="currentUser.postal"
                )
            .row
              fieldset.form-group.col-12.col-lg-3
                label.control-label {{ $t('hr.Education') }}
                input.form-control(
                  v-bind:placeholder="$t('hr.Education')",
                  type="text",
                  v-model="currentUser.education"
                )
              fieldset.form-group.col-12.col-lg-3
                label.control-label {{ $t('hr.Nation') }}
                input.form-control(
                  v-bind:placeholder="$t('hr.Nation')",
                  type="text",
                  v-model="currentUser.nation"
                )
              fieldset.form-group.col-12.col-lg-3
                label.control-label {{ $t('hr.Languages') }}
                input.form-control(
                  v-bind:placeholder="$t('hr.Languages')",
                  type="text",
                  v-model="currentUser.languages"
                )
              fieldset.form-group.col-12.col-lg-3
                label.control-label {{ $t('hr.Dependencies') }}
                input.form-control(
                  v-bind:placeholder="$t('hr.Dependencies')",
                  type="text",
                  v-model="currentUser.dependencies"
                )
      .row.mb-3
        .col-12
          .diga-container.p-4
            h2 {{ $t('hr.Law_information') }}
            .row
              fieldset.form-group.col-6
                label.control-label {{ $t('hr.identical_type') }}
                select.form-control(v-model="currentUser.identical_type_id")
                  option(value="1") {{ $t('hr.passport') }}
                  option(value="2") {{ $t('hr.citizen_card') }}
              fieldset.form-group.col-6
                label.control-label {{ $t('hr.Identical_Number') }}
                input.form-control(
                  v-bind:placeholder="$t('hr.Identical_Number')",
                  type="text",
                  v-model="currentUser.identical_number"
                )
              fieldset.form-group.col-6
                label.control-label {{ $t('hr.valid_to') }}
                date-picker(
                  format="YYYY-MM-DD",
                  v-model="currentUser.identical_valid_to",
                  :lang="$root.locale",
                  :width="'100%'"
                ) 
              fieldset.form-group.col-6
                label.control-label {{ $t('hr.Tax_Number') }}
                input.form-control(
                  v-bind:placeholder="$t('hr.Tax_Number')",
                  type="text",
                  v-model="currentUser.tax_number"
                )
            .row
              fieldset.form-group.col-6
                label.control-label {{ $t('hr.Bank_Number') }}
                input.form-control(
                  v-bind:placeholder="$t('hr.Bank_Number')",
                  type="text",
                  v-model="currentUser.bank_number"
                )
              fieldset.form-group.col-6
                label.control-label {{ $t('hr.Driver_Number') }}
                input.form-control(
                  v-bind:placeholder="$t('hr.Driver_Number')",
                  type="text",
                  v-model="currentUser.driver_number"
                )
            .row
              fieldset.form-group.col-6
                label.control-label {{ $t('hr.Insurance_Number') }}
                input.form-control(
                  v-bind:placeholder="$t('hr.Insurance_Number')",
                  type="text",
                  v-model="currentUser.insurance_number"
                )
              fieldset.form-group.col-6
                label.control-label {{ $t('hr.Social_Security_Number') }}
                input.form-control(
                  v-bind:placeholder="$t('hr.Social_Security_Number')",
                  type="text",
                  v-model="currentUser.social_security_number"
                )
            .row
              fieldset.form-group.col-6
                label.control-label {{ $t('hr.Date_of_the_last_medical_examination') }}
                date-picker(
                  format="DD.MM.YYYY",
                  v-model="currentUser.medical_date",
                  :first-day-of-week="$root.global_settings.first_day_of_week",
                  :value-type="$root.valueType",
                  :lang="$root.locale",
                  :width="'100%'"
                )
      .row.mb-3
        .col-12
          .diga-container.p-4
            h2 {{ $t('hr.contracts') }}
            .table-responsive
              table.table.table-hover
                thead
                  tr
                    td(style="") {{ $t('hr.contract_number') }}
                    td(style="") {{ $t('hr.begin_date') }}
                    td(style="") {{ $t('hr.end_date') }}
                    td(style="") {{ $t('hr.first_renovation_begin_date') }}
                    td(style="") {{ $t('hr.first_renovation_end_date') }}
                    td(style="") {{ $t('hr.second_renovation_begin_date') }}
                    td(style="") {{ $t('hr.second_renovation_end_date') }}
                    td(style="") {{ $t('hr.is_effective') }}
                    td(style="") {{ $t('hr.contract_file') }}
                tbody
                  tr(v-for="contract in currentUser.user_contracts")
                    td
                      input.form-control(
                        v-model="contract.number",
                        type="text"
                      )
                    td
                      date-picker(
                        format="YYYY-MM-DD",
                        v-model="contract.begin",
                        :lang="$root.locale",
                        :width="'100%'"
                      )
                    td
                      date-picker(
                        format="YYYY-MM-DD",
                        v-model="contract.end",
                        :lang="$root.locale",
                        :width="'100%'"
                      )
                    td
                      date-picker(
                        format="YYYY-MM-DD",
                        v-model="contract.first_renovation_begin",
                        :lang="$root.locale",
                        :width="'100%'"
                      )
                    td
                      date-picker(
                        format="YYYY-MM-DD",
                        v-model="contract.first_renovation_end",
                        :lang="$root.locale",
                        :width="'100%'"
                      )
                    td
                      date-picker(
                        format="YYYY-MM-DD",
                        v-model="contract.second_renovation_begin",
                        :lang="$root.locale",
                        :width="'100%'"
                      )
                    td
                      date-picker(
                        format="YYYY-MM-DD",
                        v-model="contract.second_renovation_end",
                        :lang="$root.locale",
                        :width="'100%'"
                      ) 
                    td
                      bootstrap-toggle(
                        v-model="contract.effective",
                        :options="{ on: $t('template.Yes'), off: $t('template.No') }",
                        data-width="80",
                        data-height="36",
                        data-onstyle="default"
                      )
                    td
                      file-uploader(
                        :file_url="contract.contract_file",
                        :file_name="contract.contract_file_name",
                        :editable="true",
                        @remove="remove_contract_file(contract)",
                        @finished="(arr) => { [contract.contract_file, contract.contract_file_name] = arr; }"
                      ) 
                    td
                      button.btn.btn-sm.red(
                        v-on:click="remove_contract(contract)"
                      )
                        i.fa.fa-times
            button.btn.green(type="button", v-on:click="add_contract") {{ $t('calendar.Add') }}
      .row.mb-3
        .col-12
          .diga-container.p-4
            h2 {{ $t('hr.timesheet') }}
            .d-flex.justify-content-between
              .div
                p {{ $t('gantt.Start_date') }}
                date-picker(
                  format="YYYY-MM-DD",
                  v-model="timesheet_date_start",
                  :lang="$root.locale",
                  :width="'100%'"
                )
              .div
                p {{ $t('gantt.End_date') }}
                date-picker(
                  format="YYYY-MM-DD",
                  v-model="timesheet_date_end",
                  :lang="$root.locale",
                  :width="'100%'"
                )
            br
            .text-center.table-responsive
              table.table.table-hover
                thead
                  tr
                    th(style="width: 20%") {{ $t('service.Date') }}
                    //- th(v-if="$root.module_enabled('estimate')") {{ $t("client.Estimate") }}
                    th(style="width: 10%") {{ $t('template.start_time_before_lunch') }}
                    th(style="width: 10%") {{ $t('template.end_time_before_lunch') }}
                    th(style="width: 10%") {{ $t('template.start_time_after_lunch') }}
                    th(style="width: 10%") {{ $t('template.end_time_after_lunch') }}
                    th(style="width: 10%") {{ $t('hr.worked_hours') }}
                    th(style="width: 10%") {{ $t('hr.earned_money') }} {{ $root.current_currency.symbol }}
                    th(style="width: 20%") {{ $t('client.Actions') }}
                tbody
                  tr(v-for="gw in filtered_estimate_group_workers")
                    td 
                      date-picker(
                        format="YYYY-MM-DD",
                        v-model="gw.date",
                        :lang="$root.locale",
                        style="min-width: 100px"
                      )
                    //- td(v-if="$root.module_enabled('estimate')")
                    //-     template(v-if="gw.estimate_group")  {{gw.estimate_group.estimate.subject}} {{gw.estimate_group.estimate.service.estimate_number}} 
                    //-     template(v-else)
                    //-         template(v-if="gw.estimate_id > 0") {{gw.estimate.subject}} {{gw.estimate.service.estimate_number}} 
                    //-         template(v-else)
                    //-             v-select(style="width: 100%;",
                    //-                 :debounce='250',
                    //-                 :on-search='get_base_options',
                    //-                 v-model="gw.estimate"
                    //-                 :on-change='base_select(gw)'
                    //-                 :options='bases',
                    //-                 :placeholder="$t('estimate.Choose_estimate')")                                                                   
                    td
                      TimePicker(
                        placeholder="",
                        v-model="gw.date_start_before_lunch",
                        :picker-options="{ start: '00:00', step: '00:15', end: '24:00' }",
                        size="mini",
                        style="width: 100px !important"
                      )
                    td
                      TimePicker(
                        placeholder="",
                        v-model="gw.date_end_before_lunch",
                        :picker-options="{ start: '00:00', step: '00:15', end: '24:00' }",
                        size="mini",
                        style="width: 100px !important"
                      )
                    td
                      TimePicker(
                        placeholder="",
                        v-model="gw.date_start_after_lunch",
                        :picker-options="{ start: '00:00', step: '00:15', end: '24:00' }",
                        size="mini",
                        style="width: 100px !important"
                      )
                    td
                      TimePicker(
                        placeholder="",
                        v-model="gw.date_end_after_lunch",
                        :picker-options="{ start: '00:00', step: '00:15', end: '24:00' }",
                        size="mini",
                        style="width: 100px !important"
                      )
                    td {{ Math.round(hours_count(gw), 2) }}
                    td 
                      template(v-if="currentUser.salary_type === true") {{ Math.round(hours_count(gw) * currentUser.salary, 2) }}
                      template(v-else) {{ Math.round(currentUser.salary, 2) }}
                    td
                      button.btn.btn-danger(
                        v-on:click="remove_estimate_worker(gw)"
                      ) {{ $t('estimate.Delete') }}
                  tr
                    td(colspan="8")
                      button.btn.btn-diga(
                        v-on:click="add_estimate_worker()",
                        style="margin-left: 10px"
                      ) {{ $t('client.Add') }}
            .row
              .col-6.text-left
                button.btn.btn-diga(v-on:click="save_estimate_workers()") {{ $t('template.Save') }}
              .col-6.text-right
                p {{ $t('dashboard.Total') }}:
                p {{ Math.round(total_hours) }} {{ $t('estimate.Hours') }}
                p {{ Math.round(total_money) }} {{ $root.current_currency.symbol }}

      .row.mb-3
        .col-12
          .diga-container.p-4
            h2 {{ $t('hr.Work_experience') }}
            .table-responsive
              table.table.table-hover
                thead
                  tr
                    td(style="width: 18%") {{ $t('hr.begin') }}
                    td(style="width: 18%") {{ $t('hr.End') }}
                    td(style="width: 27%") {{ $t('hr.Specialty') }}
                    td(style="width: 21%") {{ $t('hr.Company') }}
                    td(style="width: 40px")
                tbody
                  tr(v-for="exp in currentUser.work_before")
                    td
                      input.form-control(v-model="exp.begin", type="text")
                    td
                      input.form-control(v-model="exp.end", type="text")
                    td
                      input.form-control(
                        v-model="exp.description",
                        type="text"
                      )
                    td
                      input.form-control(v-model="exp.place", type="text")
                    td
                      button.btn.btn-sm.red(
                        v-on:click="remove_work_before(exp)"
                      )
                        i.fa.fa-times
            button.btn.green(type="button", v-on:click="add_work_before") {{ $t('hr.Add_work') }}
      .row.mb-3
        .col-12
          .diga-container.p-4
            h2 {{ $t('hr.Education_experience') }}
            .table-responsive
              table.table.table-hover
                thead
                  tr
                    td(style="width: 21%") {{ $t('hr.begin') }}
                    td(style="width: 21%") {{ $t('hr.End') }}
                    td {{ $t('hr.Tema') }}
                    td(style="width: 21%") {{ $t('hr.Place') }}
                    td(style="width: 40px")
                tbody
                  tr(v-for="exp in currentUser.educ_before")
                    td
                      input.form-control(v-model="exp.begin", type="text")
                    td
                      input.form-control(v-model="exp.end", type="text")
                    td
                      input.form-control(
                        v-model="exp.description",
                        type="text"
                      )
                    td
                      input.form-control(v-model="exp.place", type="text")
                    td
                      button.btn.btn-sm.red(
                        v-on:click="remove_educ_before(exp)"
                      )
                        i.fa.fa-times
            button.btn.green(type="button", v-on:click="add_educ_before") {{ $t('hr.Add_education') }}
      .row.mb-3
        .col-12
          .diga-container.p-4
            h2 {{ $t('hr.documents') }}
            .table-responsive
              table.table.table-hover
                thead
                  tr
                    td {{ $t('hr.document_type') }}
                    td {{ $t('hr.document_file') }}
                tbody
                  tr(v-for="doc in currentUser.user_documents")
                    td
                      input.form-control(v-model="doc.type", type="text")
                    td
                      file-uploader(
                        :file_url="doc.file",
                        :file_name="doc.file_name",
                        :editable="true",
                        @remove="remove_document_file(doc)",
                        @finished="(arr) => { [doc.file, doc.file_name] = arr; }"
                      ) 
                    td
                      button.btn.btn-sm.red(
                        v-on:click="remove_user_document(doc)"
                      )
                        i.fa.fa-times
            button.btn.green(type="button", v-on:click="add_user_document") {{ $t('calendar.Add') }}

      .row.mb-1(style="text-align: right")
        .col-12
          button.btn.btn-diga(
            type="button",
            v-on:click="save",
            style="margin-bottom: 10px"
          ) {{ $t('template.Save') }}
</template>

<script>
import moment from "moment";
import { mapGetters } from "vuex";
import TimePicker from "element-ui/lib/time-select";

require("element-ui/lib/theme-chalk/index.css");

export default {
  props: ["id"],
  components: { TimePicker },
  data: function () {
    return {
      isCreating: true,
      currentUser: null,
      show_user: false,
      show_email: false,
      src: "/img/no_profile_picture.png",
      loading: false,
      timesheet_date_start: moment().startOf("week"),
      timesheet_date_end: moment().endOf("week"),
      bases: [],
      deleted_estimate_workers: [],
    };
  },
  methods: {
    send_password_reset_email() {
      this.$root.global_loading = true;
      this.$http
        .post("/api/users/change_password", {
          user_id: this.id,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("template.email_was_sent"),
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
    base_select(val) {
      if (typeof val.estimate === "object" && val.estimate !== null) {
        val.estimate_id = val.estimate.value;
      }
    },
    save_estimate_workers() {
      this.$root.global_loading = true;

      var payload = JSON.parse(
        JSON.stringify(this.currentUser.estimate_group_workers)
      );
      payload.forEach((p) => {
        p.date = moment(p.date).format("YYYY-MM-DD");
        p.date_start_before_lunch =
          moment(p.date).format("YYYY-MM-DD") + " " + p.date_start_before_lunch;
        p.date_end_before_lunch =
          moment(p.date).format("YYYY-MM-DD") + " " + p.date_end_before_lunch;
        p.date_start_after_lunch =
          moment(p.date).format("YYYY-MM-DD") + " " + p.date_start_after_lunch;
        p.date_end_after_lunch =
          moment(p.date).format("YYYY-MM-DD") + " " + p.date_end_after_lunch;
      });

      this.$http
        .post("/api/estimate_group_workers", {
          group_id: 0,
          estimate_id: 0,
          estimate_group_workers: payload,
          deleted_estimate_workers: this.deleted_estimate_workers,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("client.State_saved"),
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
    get_base_options(search, loading) {
      loading(true);
      this.$http.get("/api/user_plannings?search=" + search).then(
        (res) => {
          var processedData = [];
          let $this = this;
          res.data.forEach(function (i) {
            processedData.push({
              label: $this.$root.estimate_number(i),
              value: i.id,
            });
          });
          this.bases = processedData;
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
    add_estimate_worker() {
      this.currentUser.estimate_group_workers.push({
        id: 0,
        user_id: this.id,
        date: moment().format("YYYY-MM-DD"),
        date_start_before_lunch: moment()
          .set("hour", 8)
          .set("minute", 0)
          .format("HH:mm"),
        date_end_before_lunch: moment()
          .set("hour", 13)
          .set("minute", 0)
          .format("HH:mm"),
        date_start_after_lunch: moment()
          .set("hour", 14)
          .set("minute", 0)
          .format("HH:mm"),
        date_end_after_lunch: moment()
          .set("hour", 18)
          .set("minute", 0)
          .format("HH:mm"),
        estimate_line_category_id: null,
        estimate_group_id: null,
        estimate_unit_id: null,
        quantity: null,
        estimate_id: null,
      });
    },
    remove_estimate_worker(estimate_worker) {
      if (
        confirm(this.$root.$t("estimate_fork.Are_you_sure_want_to_delete_this"))
      ) {
        let index =
          this.currentUser.estimate_group_workers.indexOf(estimate_worker);
        this.currentUser.estimate_group_workers.splice(index, 1);
        if (estimate_worker.id > 0) {
          this.deleted_estimate_workers.push(estimate_worker.id);
        }
      }
    },
    hours_count(gw) {
      let result = 0;
      if (
        gw.date_end_before_lunch !== null &&
        gw.date_start_before_lunch !== null
      ) {
        result += moment
          .duration(
            moment(
              moment(gw.date).format("YYYY-MM-DD") +
                " " +
                gw.date_end_before_lunch
            ).diff(
              moment(
                moment(gw.date).format("YYYY-MM-DD") +
                  " " +
                  gw.date_start_before_lunch
              )
            )
          )
          .asHours();
      }
      if (
        gw.date_end_after_lunch !== null &&
        gw.date_start_after_lunch !== null
      ) {
        result += moment
          .duration(
            moment(
              moment(gw.date).format("YYYY-MM-DD") +
                " " +
                gw.date_end_after_lunch
            ).diff(
              moment(
                moment(gw.date).format("YYYY-MM-DD") +
                  " " +
                  gw.date_start_after_lunch
              )
            )
          )
          .asHours();
      }

      if (result < 0) {
        return 0;
      }
      return result;
    },
    load_user: function () {
      this.$root.global_loading = true;
      this.$http.get("/api/users/" + this.id).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.currentUser = res.data;
            this.isCreating = false;
            //
            this.src = this.currentUser.photo;

            if (
              this.currentUser.estimate_group_workers !== null &&
              this.currentUser.estimate_group_workers.length > 0
            ) {
              this.currentUser.estimate_group_workers.forEach((cw) => {
                cw.date_start_before_lunch = moment(cw.date_start_before_lunch);
                cw.date_end_before_lunch = moment(cw.date_end_before_lunch);
                cw.date_start_after_lunch = moment(cw.date_start_after_lunch);
                cw.date_end_after_lunch = moment(cw.date_end_after_lunch);

                if (!cw.date_start_before_lunch.isValid()) {
                  cw.date_start_before_lunch = "00:00";
                } else {
                  cw.date_start_before_lunch =
                    cw.date_start_before_lunch.format("HH:mm");
                }
                if (!cw.date_end_before_lunch.isValid()) {
                  cw.date_end_before_lunch = "00:00";
                } else {
                  cw.date_end_before_lunch =
                    cw.date_end_before_lunch.format("HH:mm");
                }
                if (!cw.date_start_after_lunch.isValid()) {
                  cw.date_start_after_lunch = "00:00";
                } else {
                  cw.date_start_after_lunch =
                    cw.date_start_after_lunch.format("HH:mm");
                }
                if (!cw.date_end_after_lunch.isValid()) {
                  cw.date_end_after_lunch = "00:00";
                } else {
                  cw.date_end_after_lunch =
                    cw.date_end_after_lunch.format("HH:mm");
                }
              });
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
    save: function () {
      this.$validator.validateAll().then((result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        }
        this.$root.global_loading = true;
        this.currentUser.photo = this.src;
        if (this.isCreating) {
          this.$http.post("/api/users", this.currentUser).then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
              } else if (res.data.errcode == 2) {
                this.$toastr.w(
                  this.$root.$t("hr.Email_already_exists"),
                  this.$root.$t("template.Warning")
                );
              } else {
                this.$toastr.s(
                  this.$root.$t("hr.User_saved"),
                  this.$root.$t("template.Success")
                );
                this.$router.push({ name: "users_index" });
                this.$store.dispatch("usersRequest");
              }
              this.$root.global_loading = false;
            },
            (res) => {
              this.$toastr.e(
                this.$root.$t("template.Something_bad_happened"),
                this.$root.$t("template.Error")
              );
              this.$root.global_loading = false;
            }
          );
        } else {
          this.$http
            .patch("/api/users/" + this.currentUser.id, this.currentUser)
            .then(
              (res) => {
                if (res.data.errcode == 1) {
                  this.$toastr.e(
                    res.data.errmess,
                    this.$root.$t("template.Error")
                  );
                } else if (res.data.errcode == 2) {
                  this.$toastr.w(
                    this.$root.$t("hr.Email_already_exists"),
                    this.$root.$t("template.Warning")
                  );
                } else {
                  this.$toastr.s(
                    this.$root.$t("hr.User_saved"),
                    this.$root.$t("template.Success")
                  );
                  this.$router.push({ name: "users_index" });
                  this.$store.dispatch("usersRequest");
                }
                this.$root.global_loading = false;
              },
              (res) => {
                this.$toastr.e(
                  this.$root.$t("template.Something_bad_happened"),
                  this.$root.$t("template.Error")
                );
                this.$root.global_loading = false;
              }
            );
        }
      });
    },
    generate_password: function () {
      var length = 8;
      var charset =
        "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
      var retVal = "";
      for (var i = 0, n = charset.length; i < length; ++i) {
        retVal += charset.charAt(Math.floor(Math.random() * n));
      }
      return retVal;
    },
    add_work_before: function () {
      this.currentUser.work_before.push({
        begin: null,
        end: null,
        description: null,
        place: null,
        type: 1,
      });
    },
    add_educ_before: function () {
      this.currentUser.educ_before.push({
        begin: null,
        end: null,
        description: null,
        place: null,
        type: 2,
      });
    },
    remove_work_before: function (el) {
      let i = this.currentUser.work_before.indexOf(el);
      this.currentUser.work_before.splice(i, 1);
    },
    remove_educ_before: function (el) {
      let i = this.currentUser.educ_before.indexOf(el);
      this.currentUser.educ_before.splice(i, 1);
    },
    imageuploading() {
      this.loading = true;
    },
    imageuploaded(res) {
      this.loading = false;
      if (res.errcode == 0) {
        this.src = res.url;
      } else {
        this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
      }
    },
    imageerror(e) {
      this.$toastr.e(e, this.$root.$t("template.Error"));
    },
    remove_contract_file(contract) {
      if (
        confirm(
          this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document")
        )
      ) {
        contract.contract_file = null;
        contract.contract_file_name = null;
      }
    },
    add_contract() {
      this.currentUser.user_contracts.push({
        user_id: this.id,
        begin: null,
        end: null,
        first_renovation_begin: null,
        first_renovation_end: null,
        second_renovation_begin: null,
        second_renovation_end: null,
        number: "",
        contract_file: null,
        contract_file_name: null,
        effective: true,
      });
    },
    remove_contract(el) {
      let i = this.currentUser.user_contracts.indexOf(el);
      this.currentUser.user_contracts.splice(i, 1);
    },
    remove_document_file(doc) {
      if (
        confirm(
          this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document")
        )
      ) {
        doc.file = null;
        doc.file_name = null;
      }
    },
    add_user_document() {
      this.currentUser.user_documents.push({
        user_id: this.id,
        type: "",
        file: null,
        file_name: null,
      });
    },
    remove_user_document(el) {
      let i = this.currentUser.user_documents.indexOf(el);
      this.currentUser.user_documents.splice(i, 1);
    },
    phone_input_change(number, value) {
      this.currentUser.formatted_cell_phone = value.number.e164;
    },
  },
  computed: {
    filtered_estimate_group_workers() {
      var result = [];
      if (
        this.currentUser.estimate_group_workers != null &&
        this.currentUser.estimate_group_workers.length > 0
      ) {
        this.currentUser.estimate_group_workers.forEach((gw) => {
          if (
            moment(gw.date).isBetween(
              this.timesheet_date_start,
              this.timesheet_date_end,
              null,
              "[]"
            )
          ) {
            result.push(gw);
          }
        });
      }

      return result;
    },
    total_hours() {
      var result = 0;
      this.filtered_estimate_group_workers.forEach((gw) => {
        result += this.hours_count(gw);
      });

      return result;
    },
    total_money() {
      var result = 0;

      if (this.total_hours > 0) {
        if (this.currentUser.salary_type === true) {
          result = this.total_hours * this.currentUser.salary;
        } else {
          result = this.currentUser.salary;
        }
      }

      return result;
    },
  },
  created() {},
  mounted() {
    if (this.id) {
      document.title =
        this.$root.global_settings.site_name +
        " | " +
        this.$root.$t("hr.Edit_user");
      this.load_user();
    } else {
      document.title =
        this.$root.global_settings.site_name +
        " | " +
        this.$root.$t("hr.New_worker");
      let newUser = {
        // system
        id: null,
        email: null,
        password: null,
        // personal
        photo: null,
        name: null,
        birthday: null,
        additional_email: null,
        cell_phone: null,
        formatted_cell_phone: null,
        home_phone: null,
        address: null,
        postal: null,
        education: null,
        nation: null,
        languages: null,
        dependencies: null,
        // work
        salary_type: false,
        salary: 0.0,
        active: true,
        // law
        identical_number: null,
        tax_number: null,
        bank_number: null,
        driver_number: null,
        medical_date: null,
        insurance_number: null,
        social_security_number: null,
        // emergency
        emergency_name: null,
        emergency_contact: null,
        emergency_relation: null,
        // experience
        educ_before: [],
        work_before: [],
        user_contracts: [],
        user_documents: [],
      };
      this.currentUser = Object.assign({}, newUser);
    }
  },
};
</script>