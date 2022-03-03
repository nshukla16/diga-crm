<style>
.modal-body {
  max-width: 100%;
  overflow-x: auto;
}
.scoll-tree {
  max-width: inherit;
  /* width:5000px; */
}
</style>

<template lang="pug">
div
  section.diga-container.p-4
    h2 {{ $t('client.summary') }}
    .form(v-if="service")
      .form-group.row
        label.col-sm-3.col-form-label {{ $t('client.Estimate') }}
        .col-sm-3
          input.form-control(
            disabled="disabled",
            :value="service.estimate_number"
          )
    .row
      .col-3
        p {{ $t('gantt.Start_date') }}
        date-picker(
          format="YYYY-MM-DD",
          v-model="date_start",
          :lang="$root.locale",
          :width="'100%'"
        )
      .col-6
      .col-3
        p {{ $t('gantt.End_date') }}
        date-picker(
          format="YYYY-MM-DD",
          v-model="date_end",
          :lang="$root.locale",
          :width="'100%'"
        )
    br

    table.table.table-striped
      thead
        tr.d-flex
          th.col-2(
            rowspan="2",
            style="vertical-align: middle; text-align: center"
          ) {{ $t('client.Date') }}
          th.col-4(colspan="2", style="text-align: center") {{ $t('client.expences') }}
          th.col-6(colspan="3", style="text-align: center") {{ $t('client.production') }}
        tr.d-flex
          th.col-2
          th.col-2 {{ $t('client.salary') }} {{ $root.current_currency.symbol }}
          th.col-2 {{ $t('expences.expences') }} {{ $root.current_currency.symbol }}
          th.col-2 {{ $t('client.volume') }}
          th.col-2 {{ $t('client.task') }}
          th.col-2 {{ $t('client.finances') }} {{ $root.current_currency.symbol }}

    .table-responsive.container-fluid(style="overflow: auto; height: 500px")
      table.table.table-striped
        tbody
          tr.d-flex(v-for="dr in date_ranges")
            td.col-2 {{ dr.format('YYYY-MM-DD') }}
            td.col-2 {{ $root.formatFinanceValue(salary_expense(dr)) }}
            //- td.col-2 {{$root.formatFinanceValue(materials_expense(dr))}}
            td.col-2 {{ expense(dr) }}
            td.col-2 
              ul
                li(v-for="p in production_volume(dr)") {{ p.volume }} {{ p.measure }}
            td.col-2 
              ul
                li(v-for="p in production_tasks(dr)") {{ p.name }}
            td.col-2 {{ $root.formatFinanceValue(production_finance(dr)) }}
    table.table.table-striped
      tbody
        tr.d-flex.table-success
          td.col-2 {{ $t('estimate.Total') }}
          td.col-4(colspan="2", style="text-align: center") {{ $root.formatFinanceValue(total_expence()) }} {{ $root.current_currency.symbol }}
          td.col-6(colspan="3", style="text-align: center") {{ $root.formatFinanceValue(total_production()) }} {{ $root.current_currency.symbol }}

    br
    h2 {{ $t('client.billing_summary') }}
    .row
      .col-6
        table.table.table-striped
          tbody
            tr.d-flex
              td.col-6 {{ $t('client.proposal_amount') }}
              td.col-6 {{ estimate.price }} {{ $root.current_currency.symbol }}
            tr.d-flex
              td.col-6 {{ $t('client.previous_billed') }}
              td.col-6 {{ $root.formatFinanceValue(previously_billed) }} {{ $root.current_currency.symbol }}
            tr.d-flex
              td.col-6 {{ $t('client.amount_to_be_invoiced') }}
              td.col-6 {{ $root.formatFinanceValue(estimate.price - previously_billed) }} {{ $root.current_currency.symbol }}
            tr.d-flex
              td.col-6 {{ $t('client.accumulated_value') }}
              td.col-6 {{ $root.formatFinanceValue(accumulated) }} {{ $root.current_currency.symbol }}
            tr.d-flex
              td.col-6 {{ $t('client.balance') }}
              td.col-6 {{ $root.formatFinanceValue(estimate.price - accumulated) }} {{ $root.current_currency.symbol }}
            tr.d-flex
              td.col-6 {{ $t('client.paid_to_subcontractors') }}
              td.col-6 {{ $root.formatFinanceValue(paid_to_subcontractors) }} {{ $root.current_currency.symbol }}
            tr.d-flex
              td.col-6 {{ $t('client.left_to_pay_to_subcontractors') }}
              td.col-6(v-if="need_to_pay_to_subcontractors > 0") {{ $root.formatFinanceValue(need_to_pay_to_subcontractors - paid_to_subcontractors) }} {{ $root.current_currency.symbol }}
            tr.d-flex
              td.col-6 {{ $t('client.profit_in_the_moment') }}
              template(v-if="need_to_pay_to_subcontractors > 0") 
                td.col-6(v-if="need_to_pay_to_subcontractors > 0") {{ $root.formatFinanceValue(accumulated - paid_to_subcontractors) }} {{ $root.current_currency.symbol }}
              template(v-else)
                td.col-6(v-if="need_to_pay_to_subcontractors > 0") {{ $root.formatFinanceValue(accumulated - total_expence) }} {{ $root.current_currency.symbol }}
        br
        table.table.table-striped
          thead
            tr.d-flex
              th.col-4 {{ $t('client.work_positioning') }}
              th.col-4 {{ $t('client.on_the_date') }}
              th.col-4 {{ $t('client.lack') }}
            tr.d-flex
              td.col-4 {{ $t('client.billing') }}
              td.col-4 {{ $root.formatFinanceValue((previously_billed / estimate.price) * 100) }} %
              td.col-4 {{ $root.formatFinanceValue(100 - (previously_billed / estimate.price) * 100) }} %
            tr.d-flex
              td.col-4 {{ $t('client.term') }}
              td.col-4 {{ $root.formatFinanceValue(term_left) }} %
              td.col-4(v-if="term_left <= 100") {{ $root.formatFinanceValue(100 - term_left) }} %
            tr.d-flex
              td.col-4 {{ $t('client.profit') }}
              template(v-if="need_to_pay_to_subcontractors > 0") 
                td.col-4 {{ $root.formatFinanceValue(((accumulated - paid_to_subcontractors) / (estimate.price - need_to_pay_to_subcontractors)) * 100) }} %
                td.col-4 {{ $root.formatFinanceValue(100 - ((accumulated - paid_to_subcontractors) / (estimate.price - need_to_pay_to_subcontractors)) * 100) }} %
              template(v-else) 
                td.col-4 {{ $root.formatFinanceValue(((accumulated - total_expence) / estimate.price) * 100) }} %
                td.col-4 {{ $root.formatFinanceValue(100 - ((accumulated - total_expence) / estimate.price) * 100) }} %
    .text-center(style="margin-top: 10px")
      router-link.btn.btn-diga(
        v-if="service && service.client_contact_id",
        :to="{ name: this.$root.contact_or_client_show_route(), params: { id: service.client_contact_id } }"
      ) {{ $t('estimate.Open_client_card') }}
</template>

<script>
import moment from "moment";
import { mapGetters } from "vuex";

export default {
  data() {
    return {
      service: null,
      estimate: {
        price: 0,
      },
      estimate_groups: [],
      date_start: moment().subtract(7, "days"),
      date_end: moment(),
      current_workers: [],
      current_materials: [],
      resources: [],
      estimate_line_categories: [],
      estimate_line_fichas: [],
      user_planning_user_tasks: [],
      expences: [],
    };
  },
  props: ["id"],
  mounted() {
    this.getService();
  },
  methods: {
    getService() {
      this.$root.global_loading = true;
      this.$http.get("/api/services/" + this.id).then(
        (res) => {
          this.service = res.data;
          if (this.service.master_estimate_id !== null) {
            this.load_estimate(this.service.master_estimate_id);
            // this.load_estimate_groups(this.service.master_estimate_id);
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
    load_estimate(estimate_id) {
      this.$http.get("/api/estimates/" + estimate_id).then(
        (res) => {
          this.estimate = res.data;

          this.getEstimateLineCategories();
          this.get_estimate_group_workers();
          this.get_estimate_group_material_consumption();
          this.getEstimateFichas();
          this.load_estimate_groups();
          this.load_user_planning_user_tasks();
          this.load_expences();
        },
        (res) => {
          this.$toastr.e(
            this.$root.$t("template.Server_error"),
            this.$root.$t("template.Error")
          );
        }
      );
    },
    get_estimate_group_workers() {
      this.$http
        .get(
          "/api/estimate_group_workers/by_estimate?" +
            "estimate_id=" +
            this.estimate.id
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

              if (this.current_workers.length > 0) {
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
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
          }
        );
    },
    get_estimate_group_material_consumption() {
      this.$root.global_loading = true;

      this.$http
        .get(
          "/api/estimate_group_material_consumption/by_estimate?" +
            "estimate_id=" +
            this.estimate.id
        )
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.current_materials = data.rows;
            }
            this.getEstimateResources();
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
    load_user_planning_user_tasks() {
      this.$root.global_loading = true;

      this.$http
        .get(
          "/api/user_planning_user_tasks/by_estimate?" +
            "estimate_id=" +
            this.estimate.id
        )
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.user_planning_user_tasks = data.rows;
              if (this.user_planning_user_tasks.length > 0) {
                this.date_start = moment(
                  this.user_planning_user_tasks[0].start
                );
                this.date_end = moment(this.user_planning_user_tasks[0].end);
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
    getEstimateResources() {
      this.$http
        .get("/api/estimate_resources?" + "estimate_id=" + this.estimate.id)
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.resources = data.rows;
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
    getEstimateFichas() {
      this.$http
        .get("/api/estimate_line_fichas?" + "estimate_id=" + this.estimate.id)
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.estimate_line_fichas = data.rows;
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
    load_estimate_groups() {
      this.$http.get("/api/estimate_group_pay_stages/" + this.estimate.id).then(
        (res) => {
          this.estimate_groups = res.data.data;
        },
        (res) => {
          this.$toastr.e(
            this.$root.$t("template.Server_error"),
            this.$root.$t("template.Error")
          );
        }
      );
    },
    load_expences() {
      this.$http
        .get("/api/expences?" + "estimate_id=" + this.estimate.id)
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.expences = data.rows;
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

      return result;
    },
    salary_expense(date) {
      var result = 0;
      if (this.current_workers.length > 0) {
        this.current_workers.forEach((cw) => {
          if (moment(cw.date).isSame(date, "day")) {
            if (this.usersById[cw.user_id].salary_type === true) {
              result +=
                this.hours_count(cw) * this.usersById[cw.user_id].salary;
            } else {
              result += this.usersById[cw.user_id].salary;
            }
          }
        });
      }
      return result;
    },
    materials_expense(date) {
      var result = 0;

      if (this.current_materials.length > 0) {
        this.current_materials.forEach((cm) => {
          if (moment(cm.date).isSame(date, "day")) {
            this.resources.forEach((r) => {
              if (cm.resource_id === r.id) {
                result += r.price * cm.quantity;
              }
            });
          }
        });
      }
      return result;
    },
    expense(date) {
      var result = 0;

      if (this.expences.length > 0) {
        this.expences.forEach((cm) => {
          if (moment(cm.date).isSame(date, "day")) {
            result += cm.total;
          }
        });
      }
      return result;
    },
    production_volume(date) {
      var result = [];

      var estimate_group_workers = [];

      if (this.current_workers.length > 0) {
        this.current_workers.forEach((cw) => {
          if (moment(cw.date).isSame(date, "day")) {
            estimate_group_workers.push(cw);
          }
        });
      }

      if (estimate_group_workers.length > 0) {
        var mapped = [];
        estimate_group_workers.forEach((egw) => {
          egw.estimate_group_workers_activities.forEach((a) => {
            mapped.push(a);
          });
        });

        var grouping = mapped.reduce(function (r, a) {
          r[a.estimate_unit_id] = r[a.estimate_unit_id] || [];
          r[a.estimate_unit_id].push(a);
          return r;
        }, Object.create(null));

        for (let [key, value] of Object.entries(grouping)) {
          if (this.units_by_id[key]) {
            var new_obj = {
              measure: this.units_by_id[key].measure,
              volume: value.reduce((a, b) => a + (b["quantity"] || 0), 0),
            };

            result.push(new_obj);
          }
        }
      }

      return result;
    },
    production_tasks(date) {
      var result = [];

      var estimate_group_workers = [];

      if (this.current_workers.length > 0) {
        this.current_workers.forEach((cw) => {
          if (moment(cw.date).isSame(date, "day")) {
            estimate_group_workers.push(cw);
          }
        });
      }

      if (estimate_group_workers.length > 0) {
        var mapped = [];
        estimate_group_workers.forEach((egw) => {
          egw.estimate_group_workers_activities.forEach((a) => {
            mapped.push(a);
          });
        });

        var grouping = mapped.reduce(function (r, a) {
          r[a.estimate_line_category_id] = r[a.estimate_line_category_id] || [];
          r[a.estimate_line_category_id].push(a);
          return r;
        }, Object.create(null));

        for (let [key, value] of Object.entries(grouping)) {
          if (key > 0) {
            if (this.estimate_line_categories.length > 0) {
              var new_obj = {
                name: this.estimate_line_categories.find(
                  (el) => parseInt(el.id) === parseInt(key)
                ).name,
              };
              result.push(new_obj);
            }
          }
        }
      }

      return result;
    },
    production_finance(date) {
      var result = 0;

      var estimate_group_workers = [];

      if (this.current_workers.length > 0) {
        this.current_workers.forEach((cw) => {
          if (moment(cw.date).isSame(date, "day")) {
            estimate_group_workers.push(cw);
          }
        });
      }

      if (estimate_group_workers.length > 0) {
        var grouping = this.groupBy(estimate_group_workers, function (item) {
          return [item.estimate_line_category_id, item.estimate_unit_id];
        });

        grouping.forEach((g) => {
          if (g.length > 0) {
            g.forEach((item) => {
              if (this.estimate_line_fichas.length > 0) {
                var ficha = this.estimate_line_fichas.find(
                  (f) =>
                    parseInt(f.parent_estimate_line_category_id) ===
                      parseInt(item.estimate_line_category_id) &&
                    parseInt(f.estimate_unit_id) ===
                      parseInt(item.estimate_unit_id)
                );

                if (ficha != null) {
                  if (ficha.price != null) {
                    result += ficha.ppu * item.quantity;
                  }
                }
              }
            });
          }
        });
      }

      return result;
    },

    arrayFromObject(obj) {
      var arr = [];
      for (var i in obj) {
        arr.push(obj[i]);
      }
      return arr;
    },

    groupBy(list, fn) {
      var groups = {};
      for (var i = 0; i < list.length; i++) {
        var group = JSON.stringify(fn(list[i]));
        if (group in groups) {
          groups[group].push(list[i]);
        } else {
          groups[group] = [list[i]];
        }
      }
      return this.arrayFromObject(groups);
    },

    total_expence() {
      var result = 0;
      this.date_ranges.forEach((d) => {
        result += this.salary_expense(d);
        // result += this.materials_expense(d);
        result += this.expense(d);
      });

      return result;
    },

    total_production() {
      var result = 0;
      this.date_ranges.forEach((d) => {
        result += this.production_finance(d);
      });

      return result;
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
    date_ranges() {
      var result = [];

      var days = moment
        .duration(moment(this.date_end).diff(moment(this.date_start)))
        .asDays();

      for (var i = 0; i <= days; i++) {
        var dt = JSON.parse(JSON.stringify(this.date_start));
        dt = moment(dt);
        dt.add(i, "days");
        result.push(dt);
      }

      return result;
    },

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

    previously_billed() {
      var result = 0;
      if (this.estimate != null && this.estimate.estimate_pay_stages != null) {
        this.estimate.estimate_pay_stages.forEach((eps) => {
          if (eps.paid === true || eps.invoice_file !== null) {
            result += (this.estimate.price * eps.percent) / 100;
          }
        });
      }
      return result;
    },

    accumulated() {
      var result = 0;
      if (this.estimate != null && this.estimate.estimate_pay_stages != null) {
        this.estimate.estimate_pay_stages.forEach((eps) => {
          if (eps.paid === true) {
            result += eps.fact_paid;
          }
        });
      }
      return result;
    },
    paid_to_subcontractors() {
      var result = 0;
      if (this.estimate != null && this.estimate_groups != null) {
        this.estimate_groups.forEach((eg) => {
          if (eg.estimate_group_pay_stages != null) {
            eg.estimate_group_pay_stages.forEach((ps) => {
              if (ps.paid === true) {
                result += ps.fact_paid;
              }
            });
          }
        });
      }
      return result;
    },
    need_to_pay_to_subcontractors() {
      var result = 0;
      if (this.estimate != null && this.estimate_groups != null) {
        this.estimate_groups.forEach((eg) => {
          if (eg.percent > 0) {
            result += (this.estimate.price * eg.percent) / 100;
          }
        });
      }
      return result;
    },
    term_left() {
      var result = 0;
      if (this.user_planning_user_tasks.length > 0) {
        var start = moment(this.user_planning_user_tasks[0].start);
        var end = moment(this.user_planning_user_tasks[0].end);

        var plan_days = moment.duration(end.diff(start)).asDays();
        var fact_days = moment.duration(moment().diff(start)).asDays();

        return (fact_days / plan_days) * 100;
      }
      return result;
    },
  },
};
</script>