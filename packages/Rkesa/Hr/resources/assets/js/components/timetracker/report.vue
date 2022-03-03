<style>
#timetracker-view .filters input {
  display: inline-block;
}

#timetracker-view .filters select {
  display: inline-block;
  width: 200px;
}
.filters {
  position: relative;
}
</style>

<template lang="pug">
div
  h3 {{ $t('hr.timetracker_user_report') }}
  section.diga-container.p-4
    datatable.datatable-wrapper(v-bind="table")
      section.filters.mb-3
        .form-row
          date-picker#dashboard-range(
            v-model="filters.w_range",
            :first-day-of-week="$root.global_settings.first_day_of_week",
            range,
            type="date",
            :lang="$root.locale == 'pt' ? 'pt-br' : $root.locale"
          ) 
          select.ml-3(v-model="filters.group_id")
            option(value="0") {{ $t('hr.Groups') }}
            option(v-for="group in groups", :value="group.id") {{ group.name }}
          //- .form-check.m-2
          //-   input#checkphotos.form-check-input(
          //-     type="checkbox",
          //-     v-model="show_photos"
          //-   )
          //-   label.form-check-label(for="checkphotos") {{ $t('template.show_photos') }}

          //- .form-check.m-2
          //-   input#checklocation.form-check-input(
          //-     type="checkbox",
          //-     v-model="show_location"
          //-   )
          //-   label.form-check-label(for="checklocation") {{ $t('template.show_location') }}
</template>

<script>
import moment from "moment";
import { mapGetters } from "vuex";

import name_column from "./custom_columns/td_name.vue";
import group_column from "./custom_columns/td_group.vue";
import dates_column from "./custom_columns/td_dates.vue";
import salary_column from "./custom_columns/td_salary.vue";
import hours_column from "./custom_columns/td_hours.vue";
import money_column from "./custom_columns/td_money.vue";
import photo_column from "../hr/custom_columns/td_photo.vue";

export default {
  props: ["offset"],
  data() {
    return {
      show_photos: true,
      show_location: true,
      table: {
        columns: [
          {
            title: this.$root.$t("hr.Photo"),
            tdComp: photo_column,
            tdStyle: "width: 75px;",
          },
          {
            title: this.$root.$t("hr.Name"),
            field: "name",
            tdComp: name_column,
            sortable: true,
          },
          {
            title: this.$root.$t("hr.KPI_Group"),
            field: "group_id",
            tdComp: group_column,
            sortable: true,
          },
          {
            title: this.$root.$t("hr.date_range"),
            tdComp: dates_column,
            sortable: false,
          },
          {
            title: this.$root.$t("hr.Salary"),
            tdComp: salary_column,
            sortable: false,
          },
          {
            title: this.$root.$t("hr.worked_hours"),
            tdComp: hours_column,
            sortable: false,
          },
          {
            title: this.$root.$t("hr.earned_money"),
            tdComp: money_column,
            sortable: false,
          },
        ],
        data: [],
        total: 0,
        query: {
          offset: this.offset || 0,
        },
      },
      filters: {
        query: "",
        active: 1,
        w_range: [moment().format("YYYY-MM-01"), moment().format("YYYY-MM-DD")],
        group_id: 0,
      },
    };
  },
  methods: {
    getResults() {
      this.$http
        .get(
          "/api/timetracker/report/?" +
            this.$root.params(this.table.query) +
            "&from=" +
            moment(this.filters.w_range[0]).format("YYYY-MM-DD") +
            "&to=" +
            moment(this.filters.w_range[1]).format("YYYY-MM-DD") +
            "&group_id=" +
            this.filters.group_id
        )
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.table.data = data.rows;
              this.table.total = data.total;
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
  },
  computed: {
    ...mapGetters({
      users: "getUsers",
      groups: "getGroups",
      groupsById: "getGroupsById",
    }),
  },
  mounted() {
    document.title =
      this.$root.global_settings.site_name +
      " | " +
      this.$root.$t("hr.Timetracker_report");
    this.$bus.$on("get_results", this.getResults);
  },
  beforeDestroy: function () {
    this.getResults && this.$bus.$off("get_results", this.getResults);
  },
  watch: {
    "table.query": {
      handler(query) {
        this.getResults();
      },
      deep: true,
    },
    "filters.query": function () {
      this.table.query.offset = 0;
      this.getResults();
    },
    "filters.w_range": function () {
      this.table.query.offset = 0;
      this.getResults();
    },
    "filters.group_id": function () {
      this.table.query.offset = 0;
      this.getResults();
    },
  },
};
</script>