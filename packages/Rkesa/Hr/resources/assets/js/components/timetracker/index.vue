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
  section#timetracker-view.diga-container.p-4
    section.filters.mb-3
      .form-row
        //- input.col-1.form-control.dates#start_date(v-model="filters.date.start" v-bind:placeholder="$t('hr.Start_date')")
        //- input.col-1.form-control.dates#finish_date(v-model="filters.date.end" v-bind:placeholder="$t('hr.Finish_date')" style="margin-left: 20px;")
        date-picker#dashboard-range(
          v-model="filters.w_range",
          :first-day-of-week="$root.global_settings.first_day_of_week",
          range,
          type="date",
          :lang="$root.locale == 'pt' ? 'pt-br' : $root.locale"
        ) 
        select.form-control.ml-3(v-model="filters.group_id")
          option(value="0") {{ $t('hr.Groups') }}
          option(v-for="group in groups", :value="group.id") {{ group.name }}
        select.form-control(
          v-model="filters.worker",
          style="margin-left: 20px",
          v-on:change="select_user"
        )
          option(value="0") {{ $t('hr.All_users') }}
          option(
            v-for="option in filtered_users",
            v-bind:value="option.id",
            v-text="option.name"
          )
        .form-check.m-2
          input#checkphotos.form-check-input(
            type="checkbox",
            v-model="show_photos"
          )
          label.form-check-label(for="checkphotos") {{ $t('template.show_photos') }}
        .form-check.m-2
          input#checklocation.form-check-input(
            type="checkbox",
            v-model="show_location"
          )
          label.form-check-label(for="checklocation") {{ $t('template.show_location') }}

        //- v-select.col-2.ml-3(
        //-     :debounce='250',
        //-     :on-search='get_estimates_options',
        //-     :on-change='estimate_select',
        //-     :options='estimates',
        //-     :placeholder="$t('gantt.Choose_estimate')")
        //-     template(slot="no-options") {{ $t('template.No_matching_options') }}            
        //- v-select.col-2(
        //-     :debounce='250',
        //-     :on-search='get_services_options',
        //-     :on-change='services_select',
        //-     :options='services',
        //-     :placeholder="$t('client.Service')")
        //-     template(slot="no-options") {{ $t('template.No_matching_options') }}            
        //- v-select.col-2(
        //-     :debounce='250',
        //-     :on-search='get_client_contact_options',
        //-     :on-change='client_contact_select',
        //-     :options='client_contacts',
        //-     :placeholder="$t('client.Contact')")
        //-     template(slot="no-options") {{ $t('template.No_matching_options') }}

    section#timetracker-results
      .table-responsive
        table.table.table-striped.table-hover(style="width: 100%")
          thead
            tr
              th(style="text-align: center") {{ $t('hr.Worker') }}
              th(style="text-align: center") {{ $t('hr.Estimate') }}
              th(style="text-align: center") {{ $t('client.Service') }}
              th(style="text-align: center") {{ $t('calendar.Contact') }}
              th(style="text-align: center") {{ $t('template.start_time_before_lunch') }}
              th(style="text-align: center") {{ $t('template.end_time_before_lunch') }}
              th(style="text-align: center") {{ $t('template.start_time_after_lunch') }}
              th(style="text-align: center") {{ $t('template.end_time_after_lunch') }}
          tbody
            tr(v-for="interval in result.intervals")
              td(style="text-align: center")
                router-link(
                  v-if="$root.can_do('users', 'update') == 1",
                  :to="{ name: 'user_edit', params: { id: interval.user_id } }"
                ) {{ $root.fullName(interval.user) }}
                template(v-else) {{ $root.fullName(interval.user) }}
              td(style="text-align: center") {{ interval.estimate ? $root.estimate_number(interval.estimate) : '' }}
              td(style="text-align: center") {{ interval.service ? interval.service.name + ' ' + interval.service.estimate_number : '' }}
              td(style="text-align: center") {{ interval.client_contact ? interval.client_contact.name : '' }}
              td(style="text-align: center") 
                span {{ interval.date_start_before_lunch }}
                template(v-if="interval.auth_photos.length > 0")
                  br
                  img(
                    v-if="show_photos",
                    style="width: 100px",
                    :src="interval.auth_photos[0].url"
                  )
                  gmap-map(
                    v-if="show_location",
                    :center="pair_to_latlng(interval.auth_photos[0].lat, interval.auth_photos[0].lng)",
                    :zoom="map.zoom",
                    map-type-id="terrain",
                    style="height: 100px"
                  )
                    gmap-marker(
                      :position="pair_to_latlng(interval.auth_photos[0].lat, interval.auth_photos[0].lng)"
                    )
                  a(
                    v-if="show_location",
                    target="_blank",
                    v-bind:href="'https://www.google.com/maps/place/' + interval.auth_photos[0].lat + ',' + interval.auth_photos[0].lng"
                  ) {{ $t('estimate.Open') }}

              td(style="text-align: center") 
                span {{ interval.date_end_before_lunch }}
                template(v-if="interval.auth_photos.length > 1")
                  br
                  img(
                    v-if="show_photos",
                    style="width: 100px",
                    :src="interval.auth_photos[1].url"
                  )
                  gmap-map(
                    v-if="show_location",
                    :center="pair_to_latlng(interval.auth_photos[1].lat, interval.auth_photos[1].lng)",
                    :zoom="map.zoom",
                    map-type-id="terrain",
                    style="height: 100px"
                  )
                    gmap-marker(
                      :position="pair_to_latlng(interval.auth_photos[1].lat, interval.auth_photos[1].lng)"
                    )
                  a(
                    v-if="show_location",
                    target="_blank",
                    v-bind:href="'https://www.google.com/maps/place/' + interval.auth_photos[1].lat + ',' + interval.auth_photos[1].lng"
                  ) {{ $t('estimate.Open') }}
              td(style="text-align: center") 
                span {{ interval.date_start_after_lunch }}
                template(v-if="interval.auth_photos.length > 2")
                  br
                  img(
                    v-if="show_photos",
                    style="width: 100px",
                    :src="interval.auth_photos[2].url"
                  )
                  gmap-map(
                    v-if="show_location",
                    :center="pair_to_latlng(interval.auth_photos[2].lat, interval.auth_photos[2].lng)",
                    :zoom="map.zoom",
                    map-type-id="terrain",
                    style="height: 100px"
                  )
                    gmap-marker(
                      :position="pair_to_latlng(interval.auth_photos[2].lat, interval.auth_photos[2].lng)"
                    )
                  a(
                    v-if="show_location",
                    target="_blank",
                    v-bind:href="'https://www.google.com/maps/place/' + interval.auth_photos[2].lat + ',' + interval.auth_photos[2].lng"
                  ) {{ $t('estimate.Open') }}
              td(style="text-align: center") 
                span {{ interval.date_end_after_lunch }}
                template(v-if="interval.auth_photos.length > 3")
                  br
                  img(
                    v-if="show_photos",
                    style="width: 100px",
                    :src="interval.auth_photos[3].url"
                  )
                  gmap-map(
                    v-if="show_location",
                    :center="pair_to_latlng(interval.auth_photos[3].lat, interval.auth_photos[3].lng)",
                    :zoom="map.zoom",
                    map-type-id="terrain",
                    style="height: 100px"
                  )
                    gmap-marker(
                      :position="pair_to_latlng(interval.auth_photos[3].lat, interval.auth_photos[3].lng)"
                    )
                  a(
                    v-if="show_location",
                    target="_blank",
                    v-bind:href="'https://www.google.com/maps/place/' + interval.auth_photos[3].lat + ',' + interval.auth_photos[3].lng"
                  ) {{ $t('estimate.Open') }}
              //- td
              //-     span(v-if="interval.lat == null") {{ $t("hr.No_data") }}
              //-     template(v-else)
              //-         gmap-map(:center="pair_to_latlng(interval.lat, interval.lng)", :zoom="map.zoom", map-type-id="terrain", style="width: 200px; height: 100px")
              //-             gmap-marker(:position="pair_to_latlng(interval.lat, interval.lng)")
              //-         a(target="_blank" v-bind:href="'https://www.google.com/maps/place/'+interval.lat+','+interval.lng") Open on Google Maps
              //- td
              //-     img(:src="interval.photo")
</template>

<script>
import moment from "moment";
import { mapGetters } from "vuex";

export default {
  data() {
    return {
      show_photos: true,
      show_location: true,
      filters: {
        worker: 0,
        estimate_id: 0,
        service_id: 0,
        client_contact_id: 0,
        w_range: [
          moment().format("YYYY-MM-DD"),
          moment().add(1, "days").format("YYYY-MM-DD"),
        ],
        group_id: 0,
      },
      result: {
        intervals: [],
        total: 0,
      },
      map: {
        zoom: 17,
      },
      estimates: [],
      services: [],
      client_contacts: [],
    };
  },
  methods: {
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
        this.client_contact_id = null;
      }
      if (typeof res === "object" && res !== null) {
        this.filters.client_contact_id = res.value;
      }
    },
    get_services_options(search, loading) {
      loading(true);
      var url = "/api/services?query=" + search;
      if (this.client_contact_id > 0) {
        url += "&client_contact_id=" + this.client_contact_id;
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
        this.service_id = null;
      }
      if (typeof res === "object" && res !== null) {
        this.filters.service_id = res.value;
      }
    },
    get_estimates_options(search, loading) {
      loading(true);
      this.$http.get("/api/estimates?limit=20&query=" + search).then(
        (res) => {
          var processedData = [];
          let $this = this;
          res.data.rows.forEach(function (i) {
            processedData.push({
              label: $this.$root.estimate_number(i),
              value: i.id,
            });
          });
          this.estimates = processedData;
          loading(false);
        },
        (res) => {
          this.$toastr.e(
            this.$t("template.Something_bad_happened"),
            this.$t("template.Error")
          );
        }
      );
    },
    estimate_select(res) {
      if (res === null) {
        this.estimate_id = null;
      }
      if (typeof res === "object" && res !== null) {
        this.filters.estimate_id = res.value;
      }
    },
    filtersAreFilled() {
      return moment(this.filters.w_range[0]).isBefore(this.filters.w_range[1]);
    },
    select_user() {
      this.$http.post("/api/timetracker/user_estimates", this.filters).then(
        (res) => {
          if (res.ok) {
            this.estimates = res.data.estimates;
            this.sendData();
          } else {
            this.$toastr.e(
              this.$root.$t("hr.Retrieving_estimates_data_error"),
              this.$root.$t("template.Error")
            );
          }
        },
        (response) => {
          this.$toastr.e(
            this.$root.$t("template.Something_bad_happened"),
            this.$root.$t("template.Error")
          );
        }
      );
    },
    sendData() {
      if (this.filtersAreFilled()) {
        this.$http.post("/api/timetracker/search", this.filters).then(
          (res) => {
            if (res.ok) {
              this.result.intervals = res.body.intervals;
              this.result.total = res.body.total;
            } else {
              this.$toastr.e(
                this.$root.$t("hr.Retrieving_employee_data_error"),
                this.$root.$t("template.Error")
              );
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
    pair_to_latlng(lat, lng) {
      return { lat: parseFloat(lat), lng: parseFloat(lng) };
    },
  },
  computed: {
    ...mapGetters({
      users: "getUsers",
      groups: "getGroups",
      groupsById: "getGroupsById",
    }),
    filtered_users() {
      if (this.filters.group_id > 0) {
        var group = this.groupsById[this.filters.group_id];
        return this.users.filter((u) => group.users_ids.includes(u.id));
      } else {
        return this.users;
      }
    },
  },
  mounted() {
    // jQuery('.dates').datetimepicker({
    //     format: "YYYY-MM-DD",
    // });
    // let $this = this;
    // jQuery('#start_date').on('dp.change', function(e){
    //     $this.filters.date.start = e.date.format('YYYY-MM-DD');
    //     $this.sendData();
    // });
    // jQuery('#finish_date').on('dp.change', function(e){
    //     $this.filters.date.finish = e.date.format('YYYY-MM-DD');
    //     $this.sendData();
    // });
    this.sendData();
    document.title =
      this.$root.global_settings.site_name +
      " | " +
      this.$root.$t("hr.Timetracker_report");
  },
  watch: {
    filters: {
      handler(query) {
        this.sendData();
      },
      deep: true,
    },
  },
};
</script>