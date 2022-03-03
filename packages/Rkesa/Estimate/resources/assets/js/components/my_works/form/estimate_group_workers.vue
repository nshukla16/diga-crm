<style>
</style>

<template lang="pug">
section.diga-container.p-4
  .portlet-title
    .caption
      span.caption-subject.bold.uppercase.ml-2 {{ $t('estimate.Mao_de_obra') }} {{ groupsById[group_id].name }}
  .portlet-body
    .vb.vb-invisible(
      style="height: 730px; position: relative; overflow: hidden"
    )
      .vb-content(
        style="display: block; overflow: hidden scroll; height: 100%; width: calc(100% + 26px)"
      )
        .text-center
          fieldset.form-group(style="margin-top: 10px")
            label(style="margin-right: 10px") {{ $t('project.Date') }}
            date-picker(
              format="YYYY-MM-DD",
              v-model="date",
              :lang="$root.locale",
              :width="'30%'"
            )
            button.btn.btn-diga(
              :disabled="$root.user.can_enter_timesheet_and_consumption !== true",
              v-on:click="save_day()",
              style="margin-left: 10px"
            ) {{ $t('template.save_day') }}
          .table-responsive
            table.table
              thead
                tr.d-flex
                  th.col-2(rowspan="2") {{ $t('calendar.Name') }}
                  th.col-1(rowspan="2") {{ $t('hr.Salary') }}

                  th.col-1(rowspan="2") {{ $t('template.start_time_before_lunch') }}
                  th.col-1(rowspan="2") {{ $t('template.end_time_before_lunch') }}
                  th.col-1(rowspan="2") {{ $t('template.start_time_after_lunch') }}
                  th.col-1(rowspan="2") {{ $t('template.end_time_after_lunch') }}

                  th.col-4(colspan="4") {{ $t('client.Tasks') }}

                tr.d-flex
                  th.col-2
                  th.col-1
                  th.col-1
                  th.col-1
                  th.col-1
                  th.col-1
                  th.col-1
                  th.col-1
                  th.col-1 {{ $t('estimate.Unidades') }}
                  th.col-1 {{ $t('dashboard.quantity') }}
              tbody
                template(v-for="current_worker in current_workers")
                  tr.d-flex
                    th.col-2 
                      template(v-if="current_worker.user_id === 0")
                        v-select.w-100.mb-3(
                          :debounce="250",
                          v-model="selectedUser",
                          :on-change="worker_select(current_worker)",
                          :options="usersOptions",
                          :placeholder="$t('template.Search')"
                        )
                          template(slot="no-options") {{ $t('template.No_matching_options') }}
                      span(v-else="") {{ usersById[current_worker.user_id].name }}

                    td.col-1 
                      template(v-if="current_worker.user_id > 0") {{ usersById[current_worker.user_id].salary }} {{ $root.current_currency.symbol }}

                    td.col-1 
                      TimePicker(
                        placeholder="",
                        v-model="current_worker.date_start_before_lunch",
                        :picker-options="{ start: '00:00', step: '00:15', end: '24:00' }",
                        size="mini",
                        style="width: 100px !important"
                      )
                    td.col-1 
                      TimePicker(
                        placeholder="",
                        v-model="current_worker.date_end_before_lunch",
                        :picker-options="{ start: '00:00', step: '00:15', end: '24:00' }",
                        size="mini",
                        style="width: 100px !important"
                      )
                    td.col-1 
                      TimePicker(
                        placeholder="",
                        v-model="current_worker.date_start_after_lunch",
                        :picker-options="{ start: '00:00', step: '00:15', end: '24:00' }",
                        size="mini",
                        style="width: 100px !important"
                      )
                    td.col-1 
                      TimePicker(
                        placeholder="",
                        v-model="current_worker.date_end_after_lunch",
                        :picker-options="{ start: '00:00', step: '00:15', end: '24:00' }",
                        size="mini",
                        style="width: 100px !important"
                      )
                    td.col-4
                      button.btn.btn-diga(
                        v-on:click="add_activity(current_worker)",
                        style="margin-left: 10px"
                      ) {{ $t('client.Adicionar_tarefa') }}
                    td.col-1 
                      button.btn.btn-danger(
                        v-on:click="remove(current_worker)",
                        style="margin-left: 10px"
                      ) {{ $t('estimate.Delete') }}
                  tr.d-flex(
                    v-if="current_worker.estimate_group_workers_activities.length > 0",
                    v-for="activity in current_worker.estimate_group_workers_activities"
                  )
                    td.col-2
                    td.col-1
                    td.col-1
                    td.col-1
                    td.col-1
                    td.col-1
                    td.col-2
                      select.form-control(
                        v-model="activity.estimate_line_category_id"
                      )
                        option(
                          v-for="c in estimate_line_categories",
                          :value="c.id"
                        ) {{ c.name }}
                    td.col-1 
                      select.form-control(v-model="activity.estimate_unit_id")
                        option(v-for="unit in units", :value="unit.id") {{ unit.measure }}
                    td.col-1
                      input.form-control(
                        type="number",
                        v-model="activity.quantity"
                      ) 
                    td
                      button.btn.btn-danger(
                        v-on:click="remove_activity(current_worker, activity)",
                        style="margin-left: 10px"
                      ) X
                tr
                  button.btn.btn-diga(
                    v-on:click="add_worker()",
                    style="margin-left: 10px"
                  ) {{ $t('client.Add') }}
</template>

<script>
import moment from "moment";
import { mapGetters } from "vuex";
import TimePicker from "element-ui/lib/time-select";

require("element-ui/lib/theme-chalk/index.css");

export default {
  props: ["estimate", "group_id"],
  components: { TimePicker },
  data() {
    return {
      date: moment().startOf("day"),
      current_workers: [],
      estimate_line_categories: [],
      usersOptions: [],
      selectedUser: null,
    };
  },
  mounted() {
    this.getEstimateLineCategories();
    this.getResults();
    this.get_users_options();
  },
  methods: {
    remove_activity(current_worker, activity) {
      if (
        confirm(this.$root.$t("estimate_fork.Are_you_sure_want_to_delete_this"))
      ) {
        let index =
          current_worker.estimate_group_workers_activities.indexOf(activity);
        current_worker.estimate_group_workers_activities.splice(index, 1);
      }
    },
    add_activity(current_worker) {
      current_worker.estimate_group_workers_activities.push({
        estimate_group_worker_id: current_worker.id,
        estimate_line_category_id:
          this.estimate_line_categories.length > 0
            ? this.estimate_line_categories[0].id
            : null,
        estimate_unit_id: this.units.length > 0 ? this.units[0].id : null,
        quantity: 0,
        resource_id: null,
      });
    },
    getResults() {
      this.$root.global_loading = true;
      this.$http
        .get(
          "/api/estimate_group_workers?" +
            "estimate_id=" +
            this.estimate.id +
            "&group_id=" +
            this.group_id +
            "&date=" +
            encodeURIComponent(moment(this.date).format("YYYY-MM-DD"))
        )
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.current_workers = data.rows;

              if (this.current_workers.length === 0) {
                this.groupsById[this.group_id].users_ids.forEach((uid) => {
                  this.current_workers.push({
                    estimate_group_workers_activities: [],
                    id: 0,
                    user_id: uid,
                    date: moment(this.date),
                    date_start_before_lunch: moment(this.date)
                      .set("hour", 8)
                      .set("minute", 0)
                      .format("HH:mm"),
                    date_end_before_lunch: moment(this.date)
                      .set("hour", 13)
                      .set("minute", 0)
                      .format("HH:mm"),
                    date_start_after_lunch: moment(this.date)
                      .set("hour", 14)
                      .set("minute", 0)
                      .format("HH:mm"),
                    date_end_after_lunch: moment(this.date)
                      .set("hour", 18)
                      .set("minute", 0)
                      .format("HH:mm"),
                    estimate_line_category_id: 0 /*this.estimate_line_categories[0].id*/,
                    estimate_unit_id: this.units[0].id,
                    quantity: 0,
                    estimate_id: null,
                  });
                });
              } else {
                this.current_workers.forEach((cw) => {
                  cw.date_start_before_lunch = moment(
                    cw.date_start_before_lunch
                  ).format("HH:mm");
                  cw.date_end_before_lunch = moment(
                    cw.date_end_before_lunch
                  ).format("HH:mm");
                  cw.date_start_after_lunch = moment(
                    cw.date_start_after_lunch
                  ).format("HH:mm");
                  cw.date_end_after_lunch = moment(
                    cw.date_end_after_lunch
                  ).format("HH:mm");
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
    getEstimateLineCategories() {
      this.$http
        .get(
          "/api/estimate_line_categories?" + "estimate_id=" + this.estimate.id
        )
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.estimate_line_categories = data.rows;
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
    save_day() {
      this.$root.global_loading = true;

      var payload = JSON.parse(JSON.stringify(this.current_workers));
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
          group_id: this.group_id,
          estimate_id: this.estimate.id,
          estimate_group_workers: payload,
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

              this.getResults();
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
    add_worker() {
      this.current_workers.push({
        id: 0,
        user_id: 0,
        date: moment(this.date),
        date_start_before_lunch: moment(this.date).format("HH:mm"),
        date_end_before_lunch: moment(this.date).format("HH:mm"),
        date_start_after_lunch: moment(this.date).format("HH:mm"),
        date_end_after_lunch: moment(this.date).format("HH:mm"),
        estimate_line_category_id: 0 /*this.estimate_line_categories[0].id*/,
        estimate_unit_id: this.units[0].id,
        quantity: 0,
        estimate_id: null,
      });
    },
    remove(worker) {
      if (
        confirm(this.$root.$t("estimate_fork.Are_you_sure_want_to_delete_this"))
      ) {
        let index = this.current_workers.indexOf(worker);
        this.current_workers.splice(index, 1);
      }
    },
    get_users_options(search, loading) {
      this.usersOptions = this.users.map((u) => {
        return {
          label: u.name,
          value: u.id,
        };
      });
    },
    worker_select(val) {
      if (this.selectedUser !== null) {
        val.user_id = this.selectedUser.value;
        this.selectedUser = null;
      }
    },
  },
  computed: {
    ...mapGetters({
      usersById: "getUsersById",
      units: "getEstimateUnits",
      units_by_id: "getEstimateUnitsById",
      users: "getNotRemovedUsers",
      users_by_id: "getUsersById",
      groups: "getGroups",
      groupsById: "getGroupsById",
    }),
  },
  watch: {
    date: {
      handler(new_date) {
        //if (confirm(this.$root.$t("template.do_you_want_to_save"))){
        //this.save_day();
        //}

        this.getResults();
      },
    },
  },
};
</script>