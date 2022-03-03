<style>
.timepicker,
.timepicker-hours,
.timepicker-minutes {
  margin-top: 20px;
}
.text_button {
  cursor: pointer;
  margin-left: 20px;
  font-size: 14px;
}
</style>

<template lang="pug">
#eventCreateModal.modal.fade(tabindex="-1", role="dialog", aria-hidden="true")
  .modal-dialog.modal-lg(role="document")
    .modal-content
      .modal-header
        h5.modal-title {{ $t('client.Adicionar_tarefa') }}
        button.close(
          type="button",
          data-dismiss="modal",
          aria-label="Close",
          v-on:click="cancel_event()"
        )
          span(aria-hidden="true") &times;
      .modal-body
        .row
          .col-12.col-lg-6
            fieldset.form-group
              label.control-label(style="text-align: center; width: 100%") {{ $t('dashboard.service_data_inicio') }}
              #datetimepicker
            fieldset.form-group
              label.control-label(style="text-align: center; width: 100%") {{ $t('template.use_range') }}
              bootstrap-toggle(
                v-model="use_range",
                :options="{ on: $t('client.yes'), off: $t('client.no') }",
                data-width="120",
                data-height="30",
                data-onstyle="default"
              )
            fieldset.form-group(
              :style="[use_range === true ? { display: 'block' } : { display: 'none' }]"
            )
              label.control-label(style="text-align: center; width: 100%") {{ $t('hr.end_date') }}
              #datetimepickerfinish
          .col-12.col-lg-6
            fieldset.form-group(
              :class="{ 'has-error': errors.has('task_type') }"
            )
              label.control-label {{ $t('client.Task_type') }}
              select.form-control(
                v-model="event.event_type_id",
                name="task_type",
                v-validate="'not_in:0'",
                v-bind:data-vv-as="$t('client.Task_type').toLowerCase()"
              )
                option(disabled, value="0") {{ $t('client.Choose_event_type') }}
                option(
                  v-for="event_type in event_types",
                  v-text="event_type.title",
                  v-bind:value="event_type.id"
                )
              span.help-block(v-show="errors.has('task_type')") {{ errors.first('task_type') }}
            fieldset.form-group(:class="{ 'has-error': errors.has('user') }")
              label.control-label {{ $t('client.Responsavel_pela_tarefa') }}
              select.form-control(
                v-model="event.user_id",
                v-bind:disabled="!$root.can_do('events', 'create') != 0",
                name="user",
                v-validate="'not_in:0'"
              )
                option(disabled, value="0") {{ $t('client.Choose_user') }}
                template(
                  v-for="user in users.filter((u) => u.active === true)"
                )
                  option(v-text="user.name", v-bind:value="user.id")
              span.help-block(v-show="errors.has('user')") {{ errors.first('user') }}
            fieldset.form-group
              label {{ $t('client.Contact') }}
              .input-group
                v-select(
                  style="width: 88.5%",
                  :debounce="250",
                  :on-search="get_client_contact_options",
                  :on-change="client_contact_select",
                  :options="client_contacts",
                  :placeholder="$t('client.Contact')"
                )
                button.btn.btn-default(
                  style="width: 10%; height: 34px; margin-left: 5px",
                  v-on:click="show_new_client()"
                )
                  i.fa.fa-plus(v-if="!is_new_client")
                  i.fa.fa-times(v-if="is_new_client")
              .input-group(v-if="is_new_client", style="margin-top: 10px")
                input.form-control(
                  v-model="new_client_name",
                  type="text",
                  :placeholder="$t('dashboard.client_name')"
                )
                input.form-control(
                  v-model="new_client_phone",
                  type="tel",
                  :placeholder="$t('calendar.Phone')"
                )
                button.btn.btn-default(v-on:click="create_new_client()")
                  i.fa.fa-check
            fieldset.form-group
              label {{ $t('client.Service') }}
              .input-group
                v-select(
                  style="width: 88.5%",
                  :debounce="250",
                  :on-search="get_services_options",
                  :on-change="services_select",
                  :options="services",
                  :placeholder="$t('client.Service')"
                )
                button.btn.btn-default(
                  style="width: 10%; height: 34px; margin-left: 5px",
                  v-on:click="show_new_service()"
                )
                  i.fa.fa-plus(v-if="!is_new_service")
                  i.fa.fa-times(v-if="is_new_service")
              .form-group(v-if="is_new_service", style="margin-top: 10px")
                .row
                  fieldset.form-group.col-6
                    label.control-label {{ $t('client.Service_type') }}
                    v-select(
                      :options="service_types",
                      multiple,
                      label="name",
                      :on-change="on_service_type_select"
                    )
                    //- select.form-control(v-model="new_service_type_id" @change="on_service_type_select($event)")
                    //-     option(disabled value="0") {{ $t("client.Choose_service_type") }}
                    //-     option(v-for="type in service_types" v-bind:value="type.id") {{ type.name }} 
                  fieldset.form-group.col-6
                    label.control-label {{ $t('client.state') }}
                    select.form-control(v-model="new_service_state_id")
                      option(disabled, value="0") {{ $t('client.Choose_state') }}
                      option(
                        v-for="state in states",
                        v-if="state.type == 0",
                        v-bind:value="state.id"
                      ) {{ state.name }}
                .row
                  fieldset.form-group.col-6
                    label.control-label {{ $t('client.Caption') }}
                    input.form-control(
                      v-bind:placeholder="$t('client.Caption')",
                      type="text",
                      v-model="new_service_name"
                    )
                  fieldset.form-group.col-6
                    label.control-label {{ $t('estimate.Preco') }} {{ $root.current_currency.symbol }}
                    input.form-control(
                      type="number",
                      step="0.01",
                      min="0",
                      v-model="new_service_estimate_sum"
                    )
                .row
                  fieldset.form-group.col-12
                    button.btn.btn-default(v-on:click="create_new_service()")
                      i.fa.fa-check

            fieldset.form-group(v-if="$root.modules.project == 1")
              label.control-label {{ $t('client.Project') }}
              select.form-control(v-model="event.project_id")
                option(value="0") {{ $t('client.No_project') }}
                option(
                  v-for="project in projects",
                  v-text="project.name",
                  v-bind:value="project.id"
                )
            fieldset.form-group
              label.control-label {{ $t('client.Descricao') }}
              textarea.form-control(
                style="height: 115px",
                v-bind:placeholder="$t('client.Descricao')",
                v-model="event.description"
              )
      .modal-footer(style="justify-content: space-between")
        button.btn.grey(v-on:click="cancel_event()") {{ $t('template.Cancel') }}
        button.btn.btn-danger.float-right(
          v-if="event.id && $root.can_with_event('delete', event)",
          v-on:click="remove_event()"
        ) {{ $t('template.Remove') }}
        button.btn.btn-diga.float-right(
          v-show="!loading",
          v-on:click="save_event()"
        ) {{ $t('template.Save') }}
        div(
          v-show="loading",
          style="float: right; margin-top: 10px; margin-right: 10px"
        )
          .loader.sm-loader
</template>

<script>
import { mapGetters } from "vuex";
import moment from "moment";

export default {
  props: ["date_start", "date_finish", "refresh_form"],
  data: function () {
    return {
      event: {
        event_type_id: 0,
        service_id: null,
        client_contact_id: null,
        start: moment().format("YYYY-MM-DDTHH:mm:00Z"),
        finish: moment().format("YYYY-MM-DDTHH:mm:00Z"),
        user_id: this.$cookie.get("calendar-user") || this.$root.user.id,
        done: 0,
        description: "",
      },
      services: [],
      client_contacts: [],
      loading: false,
      reason_why_not_new_text: null,
      reason_loading: false,
      use_range: false,
      is_new_client: false,
      new_client_name: "",
      new_client_phone: "",
      is_new_service: false,
      new_service_name: "",
      new_service_type_id: 0,
      new_service_state_id: 0,
      new_service_estimate_sum: 0,
      projects: [],
    };
  },
  created() {
    let $this = this;
    jQuery(document).keyup(function (e) {
      if (e.keyCode == 27) {
        // escape key maps to keycode `27`
        $this.cancel_event();
      }
    });

    this.event.event_type_id = this.event_types[0].id;
  },
  mounted() {},
  methods: {
    create_new_service() {
      if (!this.event.client_contact_id || this.event.client_contact_id === 0) {
        this.$toastr.w(
          this.$root.$t("client.Choose_contact"),
          this.$root.$t("template.Warning")
        );
        return;
      }

      if (
        !this.new_service_name ||
        !this.new_service_type_id ||
        !this.new_service_state_id ||
        !this.new_service_estimate_sum ||
        this.new_service_name === "" ||
        this.new_service_type_id === 0 ||
        this.new_service_state_id === 0 ||
        this.new_service_estimate_sum === 0
      ) {
        this.$toastr.w(
          this.$root.$t("template.Need_to_fill"),
          this.$root.$t("template.Warning")
        );
        return;
      }

      this.$root.global_loading = true;

      var payload = {
        service_state_id: this.new_service_state_id,
        service_type_id: this.new_service_type_id,
        name: this.new_service_name,
        estimate_summ: this.new_service_estimate_sum,
        responsible_user_id: this.$root.user.id,
        client_contact_id: this.event.client_contact_id,
      };

      this.$http.post("/api/services", payload).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            this.$root.global_loading = false;
          } else {
            this.$toastr.s(
              this.$root.$t("client.Service_saved"),
              this.$root.$t("template.Success")
            );
            this.event.service_id = res.data.service_id;
            this.services = [];
            this.services.push({
              label: this.new_service_name,
              value: res.data.service_id,
            });
            this.new_service_name = "";
            this.new_service_type_id = 0;
            this.new_service_state_id = 0;
            this.new_service_estimate_sum = 0;
            this.is_new_service = false;
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
    on_service_type_select(res) {
      // if(res === null){
      //     this.event.service_id = null;
      // }
      if (typeof res === "object" && res !== null && res.length > 0) {
        this.new_service_name = "";
        this.new_service_estimate_sum = 0.0;
        res.forEach((r) => {
          var service_type = this.service_types.find(
            (s) => s.id === parseInt(r.id)
          );
          if (service_type != null) {
            this.new_service_name += service_type.name + ", ";
          }
          if (service_type != null) {
            this.new_service_estimate_sum += service_type.price;
          }
          this.new_service_type_id = r.id;
        });
      }
    },
    show_new_service() {
      this.is_new_service = !this.is_new_service;
      this.new_service_state_id = this.states[0].id;
    },
    create_new_client() {
      if (
        !this.new_client_name ||
        !this.new_client_phone ||
        this.new_client_name === "" ||
        this.new_client_phone === ""
      ) {
        this.$toastr.w(
          this.$root.$t("template.Need_to_fill"),
          this.$root.$t("template.Warning")
        );
        return;
      }
      this.$root.global_loading = true;
      var phones = [];
      phones.push({ phone_number: this.new_client_phone });
      var payload = {
        name: this.new_client_name,
        client_contact_phones: phones,
        responsible_user_id: this.$root.user.id,
      };
      this.$http
        .post("/api" + this.$root.contact_or_client_store(), payload)
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
              this.$root.global_loading = false;
            } else {
              this.$toastr.s(
                this.$root.$t("client.Contact_saved"),
                this.$root.$t("template.Success")
              );
              this.event.client_contact_id = res.data.contact_id;
              this.client_contacts = [];
              this.client_contacts.push({
                label: this.new_client_name,
                value: res.data.contact_id,
              });
              this.new_client_name = "";
              this.new_client_phone = "";
              this.is_new_client = false;
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
    show_new_client() {
      this.is_new_client = !this.is_new_client;
    },
    get_client_contact_options(search, loading) {
      loading(true);
      this.$http.get("/api/contacts?query=" + search).then(
        (res) => {
          var processedData = [];
          let $this = this;
          res.data.rows.forEach(function (i) {
            processedData.push({ label: $this.$root.fullName(i), value: i.id });
          });
          this.client_contacts = processedData;
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
    client_contact_select(res) {
      if (res === null) {
        this.event.client_contact_id = null;
      }
      if (typeof res === "object" && res !== null) {
        this.event.client_contact_id = res.value;
      }
    },
    get_services_options(search, loading) {
      loading(true);
      var url = "/api/services?query=" + search;
      if (this.event.client_contact_id > 0) {
        url += "&client_contact_id=" + this.event.client_contact_id;
      }
      this.$http.get(url).then(
        (res) => {
          var processedData = [];
          let $this = this;
          res.data.rows.forEach(function (i) {
            processedData.push({
              label:
                (i.name === null ? "" : i.name.substr(0, 60) + "... ") +
                $this.$root.service_number(i),
              value: i.id,
            });
          });
          this.services = processedData;
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
    services_select(res) {
      if (res === null) {
        this.event.service_id = null;
      }
      if (typeof res === "object" && res !== null) {
        this.event.service_id = res.value;
      }
    },
    cancel_event: function () {
      this.$emit("cancel");
    },
    date_changed(payload) {
      this.$refs.datepicker.$emit("input", payload);
    },
    save_event: function () {
      this.$validator.validateAll().then((result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        }
        this.loading = true;
        let payload = Object.assign({}, this.event);
        payload.start = jQuery("#datetimepicker")
          .data("DateTimePicker")
          .date()
          .format("YYYY-MM-DD HH:mm:00");

        if (this.use_range === true) {
          payload.finish = jQuery("#datetimepickerfinish")
            .data("DateTimePicker")
            .date()
            .format("YYYY-MM-DD HH:mm:00");

          if (payload.start > payload.finish) {
            this.$toastr.e(
              this.$root.$t("template.Date_start_bigger_date_end"),
              this.$root.$t("template.Error")
            );
            this.loading = false;
            return;
          }
        } else {
          delete payload.finish;
        }

        if (this.event.id == null) {
          this.$http.post("/api/events", payload).then(
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
                this.event.id = res.data.id;
                // SET USER
                this.event.user = this.users_by_id[this.event.user_id];
                this.$emit("update");
              }
              this.loading = false;
            },
            (res) => {
              this.$toastr.e(
                this.$root.$t("template.Server_error"),
                this.$root.$t("template.Error")
              );
              this.loading = false;
            }
          );
        } else {
          this.$http.patch("/api/events/" + this.event.id, payload).then(
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
                // SET USER
                this.event.user = this.users_by_id[this.event.user_id];
                this.$emit("update");
              }
              this.loading = false;
            },
            (res) => {
              this.$toastr.e(
                this.$root.$t("template.Server_error"),
                this.$root.$t("template.Error")
              );
              this.loading = false;
            }
          );
        }
      });
    },
  },
  computed: {
    ...mapGetters({
      event_types: "getEventTypes",
      event_groups: "getEventGroups",
      users: "getNotRemovedUsers",
      users_by_id: "getUsersById",
      priorities: "getServicePriorities",
      service_types: "getServiceTypes",
      states: "getNotRemovedServiceStates",
    }),
  },
  watch: {
    date_start(newval, oldVal) {
      this.event.start = moment(newval); //.format('YYYY-MM-DDTHH:mm:00');
      let $this = this;
      Vue.nextTick(function () {
        let $dtp = jQuery("#datetimepicker").data("DateTimePicker");
        if (typeof $dtp !== "undefined") {
          $dtp.date($this.event.start).locale($this.$root.locale);
        } else {
          jQuery("#datetimepicker").datetimepicker({
            inline: true,
            sideBySide: true,
            defaultDate: $this.event.start,
            locale: $this.$root.locale,
            // first day of week defines according to locale
            format: "YYYY-MM-DD HH:mm",
            // disabledHours: [0, 1, 2, 3, 4, 5, 6, 21, 22, 23],
          });
        }
        jQuery(
          ".day, .timepicker-picker a, .timepicker-hour, .timepicker-minute, .datepicker-days th"
        ).click(function () {
          if ($this.action && $this.action.event_date_type != 0) {
            event.preventDefault();
            event.stopPropagation();
          }
        });
      });
    },
    date_finish(newval, oldVal) {
      this.event.finish = moment(newval); //.format('YYYY-MM-DDTHH:mm:00');
      let $this = this;
      Vue.nextTick(function () {
        let $dtpfinish = jQuery("#datetimepickerfinish").data("DateTimePicker");
        if (typeof $dtpfinish !== "undefined") {
          $dtpfinish.date($this.event.finish).locale($this.$root.locale);
        } else {
          jQuery("#datetimepickerfinish").datetimepicker({
            inline: true,
            sideBySide: true,
            defaultDate: $this.event.finish,
            locale: $this.$root.locale,
            // first day of week defines according to locale
            format: "YYYY-MM-DD HH:mm",
            // disabledHours: [0, 1, 2, 3, 4, 5, 6, 21, 22, 23],
          });
        }
        jQuery(
          ".day, .timepicker-picker a, .timepicker-hour, .timepicker-minute, .datepicker-days th"
        ).click(function () {
          if ($this.action && $this.action.event_date_type != 0) {
            event.preventDefault();
            event.stopPropagation();
          }
        });
      });
    },
    refresh_form: {
      immediate: true,
      handler(newval, oldVal) {
        this.event = {
          event_type_id: this.event_types[0].id,
          service_id: null,
          client_contact_id: null,
          start: moment(this.date_start) /*.format('YYYY-MM-DDTHH:mm:00Z')*/,
          finish: moment(this.date_finish) /*.format('YYYY-MM-DDTHH:mm:00Z')*/,
          user_id: this.$cookie.get("calendar-user") || this.$root.user.id,
          done: 0,
          description: "",
        };

        this.services = [];
        this.client_contacts = [];
        this.use_range = false;
        this.is_new_client = false;
        this.new_client_name = "";
        this.new_client_phone = "";
        this.is_new_service = false;
        this.new_service_name = "";
        this.new_service_type_id = 0;
        this.new_service_state_id = 0;
        this.new_service_estimate_sum = 0;

        let $this = this;

        Vue.nextTick(function () {
          let $dtp = jQuery("#datetimepicker").data("DateTimePicker");
          let $dtpfinish = jQuery("#datetimepickerfinish").data(
            "DateTimePicker"
          );
          if (typeof $dtp !== "undefined") {
            $dtp.date($this.event.start).locale($this.$root.locale);
          } else {
            jQuery("#datetimepicker").datetimepicker({
              inline: true,
              sideBySide: true,
              defaultDate: $this.event.start || moment(),
              locale: $this.$root.locale,
              // first day of week defines according to locale
              format: "YYYY-MM-DD HH:mm",
              // disabledHours: [0, 1, 2, 3, 4, 5, 6, 21, 22, 23],
            });
          }
          if (typeof $dtpfinish !== "undefined") {
            $dtpfinish.date($this.event.finish).locale($this.$root.locale);
          } else {
            jQuery("#datetimepickerfinish").datetimepicker({
              inline: true,
              sideBySide: true,
              defaultDate: $this.event.finish || moment(),
              locale: $this.$root.locale,
              // first day of week defines according to locale
              format: "YYYY-MM-DD HH:mm",
              // disabledHours: [0, 1, 2, 3, 4, 5, 6, 21, 22, 23],
            });
          }
          jQuery(
            ".day, .timepicker-picker a, .timepicker-hour, .timepicker-minute, .datepicker-days th"
          ).click(function () {
            if ($this.action && $this.action.event_date_type != 0) {
              event.preventDefault();
              event.stopPropagation();
            }
          });
        });
      },
    },
  },
};
</script>