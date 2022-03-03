<style>
#tasks_block .actions {
  float: right;
}
.item-1 {
  padding: 7px;
  border-radius: 10px;
}
.feeds {
  list-style: none;
  padding-left: 0;
}
.feeds li {
  margin-top: 5px;
}
.actions .toggle .btn {
  font-size: 14px;
}
</style>

<template lang="pug">
div
  #tasks_block.diga-container
    .portlet-title
      .caption
        a.btn(
          v-if="$root.can_do('events', 'create') != 0 && main_contact",
          v-on:click="new_event()"
        )
          i.fa.fa-plus
        span.caption-subject.bold.uppercase.ml-2 {{ $t('client.Tasks') }}
        .actions
          bootstrap-toggle(
            data-size="mini",
            v-model="active_events",
            :options="{ on: $t('client.Active'), off: $t('client.Done') }",
            data-width="120",
            data-height="30",
            data-onstyle="default",
            ref="events_toggle"
          )
    .portlet-body
      div(v-bar="", style="height: 480px")
        div
          ul.feeds(v-if="current_events.length > 0")
            template(v-for="event in current_events")
              my_event(
                :event="event",
                :active="active_events",
                v-on:edit="edit_event",
                v-on:done="done_event"
              )
          .empty-filler(v-else) {{ $t('client.There_is_no_tasks') }}
      edit-form(
        :event="current_event",
        :action="action",
        :services="services",
        :projects="projects",
        v-on:remove_event="remove_current",
        v-on:cancel="cancel_current",
        v-on:save="save_current",
        v-on:update="update_current"
      )
      //reason_form(:event="current_event", v-on:only_finish="only_finish")
</template>

<script>
import my_event from "./_event.vue";
import edit_form from "./_form.vue";
import reason_form from "./reason.vue";
import moment from "moment";

import { mapGetters } from "vuex";

export default {
  props: ["events", "main_contact", "projects", "company_id"],
  components: {
    my_event,
    "edit-form": edit_form,
    reason_form: reason_form,
  },
  data() {
    return {
      current_event: null,
      old_event: null,
      my_events: [],
      action: null,
      active_events: true,
      services: [],
      // instead_event: null,
    };
  },
  created() {
    let $this = this;
    // make events reactive
    this.events.forEach(function (e) {
      $this.my_events.push(e);
    });
  },
  mounted() {
    this.$bus.$on("action_event", this.action_event);
    this.$bus.$on("add_event_to_list", this.add_event_to_list);
    if (this.main_contact || this.company_id > 0) {
      this.get_services();
    }
  },
  beforeDestroy: function () {
    this.action_event && this.$bus.$off("action_event", this.action_event);
    this.add_event_to_list &&
      this.$bus.$off("add_event_to_list", this.add_event_to_list);
  },
  methods: {
    get_services() {
      let url = "";
      if (this.main_contact) {
        url = "/api/services/short?client_contact_id=" + this.main_contact.id;
      }

      if (this.company_id > 0) {
        url = "/api/services/short?client_id=" + this.company_id;
      }

      this.$http
        .get(url)
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              if (data.rows.length) {
                this.services.push(...data.rows);
              }
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
    add_event_to_list: function (event) {
      this.my_events.push(event);
    },
    action_event: function (service_state_action, service, global_data) {
      this.action = service_state_action;
      let date = this.get_date_from_type(service_state_action.event_date_type);
      let formatted_description = service_state_action.event_description;
      if (formatted_description == null) {
        formatted_description = "";
      }
      formatted_description = formatted_description.replace(
        new RegExp("{sent_estimate_numbers}", "g"),
        global_data.sent_estimate_numbers
      );
      formatted_description = formatted_description.replace(
        new RegExp("{event_start}", "g"),
        global_data.event_start
      );
      formatted_description = formatted_description.replace(
        new RegExp("{service_note}", "g"),
        service.note
      );
      this.new_event(
        date,
        service_state_action.event_type_id,
        service_state_action.event_user_id,
        formatted_description,
        service.id
      );
    },
    get_date_from_type: function (type) {
      switch (type) {
        case 0:
          return null;
        case 1:
          return moment().add(1, "days").format("YYYY-MM-DD HH:mm");
        case 2:
          return moment().add(2, "days").format("YYYY-MM-DD HH:mm");
        case 3:
          return moment().add(3, "days").format("YYYY-MM-DD HH:mm");
        case 4:
          return moment().add(4, "days").format("YYYY-MM-DD HH:mm");
        case 5:
          return moment().add(5, "days").format("YYYY-MM-DD HH:mm");
        case 6:
          return moment().add(6, "days").format("YYYY-MM-DD HH:mm");
        case 7:
          return moment().add(1, "weeks").format("YYYY-MM-DD HH:mm");
        case 8:
          return moment().add(1, "months").format("YYYY-MM-DD HH:mm");
        case 9:
          return moment().add(2, "months").format("YYYY-MM-DD HH:mm");
        case 10:
          return moment().add(6, "months").format("YYYY-MM-DD HH:mm");
        case 11:
          return moment().add(1, "years").format("YYYY-MM-DD HH:mm");
        case 12:
          return moment().add(2, "weeks").format("YYYY-MM-DD HH:mm");
        case 13:
          return moment().add(3, "weeks").format("YYYY-MM-DD HH:mm");
      }
    },
    new_event: function (
      event_date = null,
      event_type_id = 0,
      event_user_id = 0,
      event_description = null,
      service_id = 0,
      instead_done = false,
      project_id = 0
    ) {
      let event = {
        id: null,
        start: event_date,
        end: null,
        event_type_id:
          event_type_id == 0 ? this.event_types[0].id : event_type_id,
        user_id: event_user_id == 0 ? this.$root.user.id : event_user_id,
        description: event_description,
        service_id: service_id,
        project_id: project_id,
        // CALENDAR-EXTENDED
        event_group_id: 1,
        done: false,
        instead_done: instead_done,
        client_contact: this.main_contact,
        client_contact_id: this.main_contact.id,
      };
      this.current_event = event;
      jQuery("#eventModal").modal("show");
    },
    // Children events
    // my_event
    edit_event: function (event) {
      if (!("instead_done" in event)) {
        event.instead_done = null;
      }
      this.old_event = Object.assign({}, event);
      this.current_event = event;
      jQuery("#eventModal").modal("show");
    },
    done_event: function (event) {
      if (confirm(this.$root.$t("client.Are_you_sure_finish_task"))) {
        this.$root.global_loading = true;
        this.$http.post("/api/calendar/" + event.id + "/finish").then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("client.Task_finished"),
                this.$root.$t("template.Success")
              );
              // add to history
              this.$bus.$emit("system_message", res.data.history);
              event.done = true;
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
    // edit-form
    remove_current: function () {
      let index = this.my_events.indexOf(this.current_event);
      this.my_events.splice(index, 1);
      this.current_event = null;
      jQuery("#eventModal").modal("hide");
    },
    cancel_current: function () {
      if (this.current_event) {
        Object.assign(this.current_event, this.old_event);
        if (this.action) {
          this.$bus.$emit("action_event_cancel", this.current_event.service_id);
        }
        this.current_event = null;
        this.old_event = null;
        this.action = null;
        jQuery("#eventModal").modal("hide");
      }
    },
    save_current: function () {
      this.my_events.push(this.current_event);
      let $this = this;
      jQuery("#eventModal")
        .modal("hide")
        .on("hidden.bs.modal", function () {
          if ($this.action) {
            $this.$bus.$emit(
              "action_event_ok",
              $this.current_event.id,
              $this.current_event.start,
              $this.current_event.description
            );
          }
          $this.current_event = null;
          $this.old_event = null;
          $this.action = null;
        });
    },
    update_current: function () {
      this.current_event = null;
      this.old_event = null;
      jQuery("#eventModal").modal("hide");
    },
    //            only_finish: function(){
    //                this.instead_event.done = !this.instead_event.done;
    //                this.current_event = null;
    //                this.old_event = null;
    //            }
  },
  computed: {
    current_events() {
      let $this = this;
      return this.my_events.filter(function (e) {
        return e.done != $this.active_events;
      });
    },
    ...mapGetters({
      event_types: "getEventTypes",
    }),
  },
};
</script>