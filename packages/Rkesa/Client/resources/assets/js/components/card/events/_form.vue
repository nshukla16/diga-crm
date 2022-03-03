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
#eventModal.modal.fade(tabindex="-1", role="dialog", aria-hidden="true")
  .modal-dialog.modal-lg(role="document")
    .modal-content(v-if="event != null")
      .modal-header
        h5.modal-title {{ event.id ? $t('client.Edit_task') : $t('client.Adicionar_tarefa') }}
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
              label.control-label(style="text-align: center; width: 100%") {{ $t('client.Date_and_time') }}
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
            fieldset.form-group(v-if="event.client_contact")
              label.control-label {{ $t('client.Contact') }}
              input.form-control(
                :value="$root.fullName(event.client_contact)",
                disabled
              )
          .col-12.col-lg-6
            fieldset.form-group(
              :class="{ 'has-error': errors.has('task_type') }"
            )
              label.control-label {{ $t('client.Task_type') }}
              select.form-control(
                v-model="event.event_type_id",
                v-bind:disabled="action && action.event_type_id != 0",
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
                v-bind:disabled="action && action.event_user_id != 0",
                name="user",
                v-validate="'not_in:0'"
              )
                option(disabled, value="0") {{ $t('client.Choose_user') }}
                template(v-for="user in users.filter((u) => u.active === true)")
                  //- HARDCODE Commerce, Tech
                  //option(v-if="[2,3].indexOf(user.role_id) != -1 || user.id == event.user_id" v-text="user.name" v-bind:value="user.id")
                  option(v-text="user.name", v-bind:value="user.id")
              span.help-block(v-show="errors.has('user')") {{ errors.first('user') }}
            fieldset.form-group
              label.control-label {{ $t('client.Service') }}
              select.form-control(v-model="event.service_id")
                option(value="0") {{ $t('client.No_service') }}
                option(
                  v-for="service in services",
                  v-text="(service.name === null ? '' : service.name.substr(0, 60) + '... ') + $root.service_number(service)",
                  v-bind:value="service.id"
                )
            //- fieldset.form-group(v-if="$root.modules.calendar_extended == 1")
            //-     label.control-label {{ $t("calendar_extended.Event_group") }}
            //-     select.form-control(v-model="event.event_group_id")
            //-         option(v-for="event_group in event_groups" v-text="event_group.name" v-bind:value="event_group.id")
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
                v-bind:disabled="action && action.event_description_not_editable == 1",
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

export default {
  props: ["event", "action", "services", "projects"],
  data: function () {
    return {
      loading: false,
      reason_why_not_new_text: null,
      reason_loading: false,
      use_range: false,
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
  },
  mounted() {},
  methods: {
    remove_event() {
      if (
        confirm(this.$root.$t("client.Are_you_sure_you_want_to_delete_event"))
      ) {
        this.$http.delete("/api/events/" + this.event.id).then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("client.Event_removed"),
                this.$root.$t("template.Success")
              );
              this.$emit("remove_event");
            }
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Something_bad_happened"),
              this.$root.$t("template.Error")
            );
          }
        );
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
        this.event.start = jQuery("#datetimepicker")
          .data("DateTimePicker")
          .date()
          .format("YYYY-MM-DD HH:mm:00");
        if (this.use_range === true) {
          this.event.finish = jQuery("#datetimepickerfinish")
            .data("DateTimePicker")
            .date()
            .format("YYYY-MM-DD HH:mm:00");

          if (this.event.start > this.event.finish) {
            this.$toastr.e(
              this.$root.$t("template.Date_start_bigger_date_end"),
              this.$root.$t("template.Error")
            );
            this.loading = false;
            return;
          }
        }
        let payload = Object.assign({}, this.event);
        delete payload.client_contact;
        delete payload.source;
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
                this.$emit("save");
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
    //            finish_only: function(){
    //                Vue.nextTick(function () {
    //                    jQuery('#modal-event-reason').modal('show');
    //                });
    //            },
  },
  computed: {
    ...mapGetters({
      event_types: "getEventTypes",
      event_groups: "getEventGroups",
      users: "getNotRemovedUsers",
      users_by_id: "getUsersById",
    }),
  },
  watch: {
    event(val) {
      if (val) {
        if (val.end !== null) {
          this.use_range = true;
        }
        let $this = this;
        Vue.nextTick(function () {
          let $dtp = jQuery("#datetimepicker").data("DateTimePicker");
          let $dtpfinish = jQuery("#datetimepickerfinish").data(
            "DateTimePicker"
          );
          if (typeof $dtp !== "undefined") {
            $dtp.date(val.start).locale($this.$root.locale);
          } else {
            jQuery("#datetimepicker").datetimepicker({
              inline: true,
              sideBySide: true,
              defaultDate: val.start,
              locale: $this.$root.locale,
              // first day of week defines according to locale
              format: "YYYY-MM-DD HH:mm",
              disabledHours: [0, 1, 2, 3, 4, 5, 6, 21, 22, 23],
            });
          }
          if (typeof $dtpfinish !== "undefined") {
            $dtpfinish.date(val.end).locale($this.$root.locale);
          } else {
            jQuery("#datetimepickerfinish").datetimepicker({
              inline: true,
              sideBySide: true,
              defaultDate: val.end,
              locale: $this.$root.locale,
              // first day of week defines according to locale
              format: "YYYY-MM-DD HH:mm",
              disabledHours: [0, 1, 2, 3, 4, 5, 6, 21, 22, 23],
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
      }
    },
  },
};
</script>