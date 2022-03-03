<template lang="pug">
.modal.fade(
  :id="'modal-contractors_' + service.id",
  tabindex="-1",
  aria-hidden="true"
)
  .modal-dialog.modal-dialog-centered(role="document")
    .modal-content
      .modal-header {{ $t('template.add_service_to_contractor') }}
      .modal-body
        .row
          .col-12
            .form-group
              label {{ $t('client.Estimate') }}
              select.form-control(v-model="selected_estimate_id")
                option(value="0") {{ $t('hr.Without_estimate') }}
                option(v-for="es in service.estimates", :value="es.id") 
                  template(v-if="es.id === service.master_estimate_id") {{ $t('client.Master') }} {{ estimate_number(es) }}
                  template(v-else) {{ estimate_number(es) }}
            .form-group
              label(for="groupId") {{ $t('template.contractor') }}
              select#groupId.form-control(v-model="selected_group_id")
                option(value="0", disabled="true") {{ $t('template.Choose') }}
                option(v-for="group in filteredGroups", :value="group.id") {{ group.name }}

        .row.mt-3
          .col-12.text-center
            button.btn.btn-diga(
              :disabled="selected_group_id === 0",
              v-on:click="add_service_to_contractor",
              style="cursor: pointer"
            ) {{ $t('template.Add') }}
</template>

<script>
import { mapGetters } from "vuex";

export default {
  data() {
    return {
      selected_group_id: 0,
      selected_estimate_id: this.service.master_estimate_id || 0,
    };
  },
  props: ["service", "groupsToExclude"],
  computed: {
    ...mapGetters({
      states: "getNotRemovedServiceStates",
      users_by_id: "getUsersById",
      groups: "getGroups",
      groupsById: "getGroupsById",
    }),
    filteredGroups() {
      if (!this.groupsToExclude) return this.groups;
      if (this.groupsToExclude.length === 0) return this.groups;
      return this.groups.filter((g) => {
        return this.groupsToExclude.indexOf(g.id) < 0;
      });
    },
  },
  methods: {
    estimate_number(estimate) {
      return (
        this.$root.service_number(this.service) +
        (estimate.option != null
          ? " " + this.$t("template.option") + estimate.option
          : "") +
        (estimate.revision != null
          ? " " + this.$t("template.revision") + estimate.revision
          : "") +
        (estimate.fork_id != null
          ? " " + this.$root.get_estimate_fork(estimate)
          : "")
      );
    },
    add_service_to_contractor() {
      this.$root.global_loading = true;
      this.$http
        .post("/api/estimate_groups", {
          group_id: this.selected_group_id,
          estimate_id: this.selected_estimate_id,
          service_id: this.service.id,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              jQuery("#modal-contractors_" + this.service.id).modal("hide");
              this.$emit("fetch_services_from_child");
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
  },
};
</script>

<style lang="scss" scoped>
</style>