<style>
</style>

<template lang="pug">
.row.clearfix
  // main_contact_id for service create link
  services.col-12.mb-3(
    v-if="($root.module_enabled('project') && current_section == 1) || !$root.module_enabled('project')",
    :class="{ 'col-xl-4': !services_maximized }",
    :main_contact_id="contact ? contact.id : null",
    :editable="true",
    :maximized="services_maximized",
    :company_id="company_id",
    :selected_service="selected_service",
    :current_section.sync="current_section",
    :is_group="is_group",
    @maximize_minimize_click="maximize_minimize_click"
  )
  template(v-if="company_id")
    projects.col-12.col-xl-4.mb-3(
      v-if="$root.module_enabled('project') && current_section == 2",
      :projects="projects",
      :current_section.sync="current_section"
    )
    equipment.col-12.col-xl-4.mb-3(
      v-if="$root.module_enabled('project') && current_section == 3",
      :equipment="equipment",
      :client_id="company_id",
      :current_section.sync="current_section"
    )
    delivery.col-12.col-xl-4.mb-3(
      v-if="current_section == 4",
      :equipment="equipment",
      :client_id="company_id",
      :current_section.sync="current_section",
      :calculations="calculations"
    )
  events.col-12.mb-3(
    :class="{ 'col-md-6 col-xl-4': !services_maximized, 'col-md-6': services_maximized }",
    :events="events",
    :main_contact="contact",
    :projects="projects",
    :company_id="company_id"
  )
  history.col-12.mb-3(
    :class="{ 'col-md-6 col-xl-4': !services_maximized, 'col-md-6': services_maximized }",
    :company_id="company_id",
    :main_contact_id="contact ? contact.id : null"
  )
</template>

<script>
import events from "./events/index.vue";
import history from "./history.vue";
import services from "./services/index.vue";
import projects from "./projects/index.vue";
import equipment from "./equipment/index.vue";
import delivery from "./delivery/index.vue";

export default {
  props: [
    "events",
    "contact",
    "history_entities",
    "selected_service",
    "projects",
    "equipment",
    "company_id",
    "calculations",
    "is_group",
  ],
  data: function () {
    return {
      services_maximized: false,
      current_section: 1,
    };
  },
  components: {
    events,
    history,
    services,
    projects,
    equipment,
    delivery,
  },
  mounted() {
    this.$bus.$on("update_current_section", this.update_current_section);
  },
  beforeDestroy: function () {
    this.update_current_section &&
      this.$bus.$off("update_current_section", this.update_current_section);
  },
  methods: {
    maximize_minimize_click() {
      this.services_maximized = !this.services_maximized;
    },
    update_current_section(e) {
      this.current_section = e;
    },
  },
};
</script>