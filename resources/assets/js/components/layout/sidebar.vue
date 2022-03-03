<template lang="pug">
nav#sidebar(:class="{ active: $root.toogleSidebar }")
  .sidebar-header.text-center
    router-link(:to="{ name: 'my_dashboard' }")
      img#site_logo.logo-default(:src="$root.logo", alt="logo", height="30")
  ul.list-unstyled.components
    p.text-center {{ $t('template.main_menu') }}
    li
      router-link.nav-link(:to="{ name: 'my_dashboard' }") {{ $t('template.Dashboard') }}
    li(
      v-if="$root.can_do('clients', 'read') != 0 || $root.can_do('clients', 'create') == 1 || $root.can_do('services', 'read') != 0"
    )
      a.dropdown-toggle(
        href="#clientsDropdown",
        data-toggle="collapse",
        aria-expanded="false"
      ) {{ $t('template.Clients') }}
      ul#clientsDropdown.collapse.list-unstyled
        template(v-if="$root.enable_companies")
          router-link.dropdown-item(
            v-if="$root.can_do('clients', 'read') != 0",
            :to="{ name: 'companies_index' }"
          ) {{ $t('template.All_companies') }}
          router-link.dropdown-item(
            v-if="$root.can_do('clients', 'read') != 0",
            :to="{ name: 'contacts_index' }"
          ) {{ $t('template.All_contacts') }}
        template(v-else)
          router-link.dropdown-item(
            v-if="$root.can_do('clients', 'read') != 0",
            :to="{ name: 'clients_index' }"
          ) {{ $t('template.All_clients') }}
        router-link.dropdown-item(
          v-if="$root.can_do('services', 'read') != 0",
          :to="{ name: 'services_index' }"
        ) {{ $t('template.All_services') }}
        router-link.dropdown-item(
          v-if="$root.user.can_view_calls != 0",
          :to="{ name: 'calls_index' }"
        ) {{ $t('template.All_calls') }}
    li(
      v-if="$root.module_enabled('estimate') && ($root.can_do('estimates', 'read') != 0 || $root.can_do('fichas', 'read') != 0 || $root.can_do('resources', 'read') != 0)"
    )
      a.dropdown-toggle(
        href="#estimatesDropdown",
        data-toggle="collapse",
        aria-expanded="false"
      ) {{ $t('template.Estimates') }}
      ul#estimatesDropdown.collapse.list-unstyled
        router-link.dropdown-item(
          v-if="$root.can_do('estimates', 'read') != 0",
          :to="{ name: 'estimates_index' }"
        ) {{ $t('template.All_estimates') }}
        router-link.dropdown-item(
          v-if="$root.can_do('fichas', 'read') != 0",
          :to="{ name: 'fichas_index' }"
        ) {{ $t('template.Fichas') }}
        router-link.dropdown-item(
          v-if="$root.can_do('resources', 'read') != 0",
          :to="{ name: 'resources_index' }"
        ) {{ $t('template.Resources') }}
        router-link.dropdown-item(
          v-if="$root.can_do('estimates', 'read') != 0 && $root.user.can_see_financial_calendar === true",
          :to="{ name: 'financial_liabilities_index' }"
        ) {{ $t('estimate.financial_liabilities') }}
    li(
      v-if="($root.module_enabled('gantt') || $root.module_enabled('project_schedules')) && $root.can_do('plannings', 'read') != 0"
    )
      a.dropdown-toggle(
        href="#ganttDropdown",
        data-toggle="collapse",
        aria-expanded="false"
      ) {{ $t('template.Planning') }}
      ul#ganttDropdown.collapse.list-unstyled
        router-link.dropdown-item(
          v-if="$root.module_enabled('gantt')",
          :to="{ name: 'estimate_plannings_index' }"
        ) {{ $t('template.Estimate_planning') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('project_schedules')",
          :to="{ name: 'user_plannings_index' }"
        ) {{ $t('template.User_planning') }}
    li(
      v-if="$root.user.can_view_results_of_timetracker || $root.user.can_use_timetracker || $root.can_do('users', 'read') != 0"
    )
      a.dropdown-toggle(
        href="#hrDropdown",
        data-toggle="collapse",
        aria-expanded="false"
      ) {{ $t('template.HR') }}
      ul#hrDropdown.collapse.list-unstyled
        router-link.dropdown-item(
          v-if="$root.can_do('users', 'create') != 0",
          :to="{ name: 'user_create' }"
        ) {{ $t('hr.New_worker') }}
        router-link.dropdown-item(
          v-if="$root.can_do('users', 'read') != 0",
          :to="{ name: 'users_index' }"
        ) {{ $t('template.All_workers') }}
        .dropdown-divider
        router-link.dropdown-item(
          v-if="$root.module_enabled('kpi') && $root.can_do('users', 'read') != 0",
          :to="{ name: 'groups_index' }"
        ) {{ $t('template.UserGroups') }} {{ $t('hr.Kpi') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('kpi') && $root.can_do('users', 'edit') != 0",
          :to="{ name: 'kpi_users_and_groups' }"
        ) {{ $t('template.Kpi') }}
        .dropdown-divider
        router-link.dropdown-item(
          v-if="$root.module_enabled('time_tracker') && $root.user.can_view_results_of_timetracker",
          :to="{ name: 'timetracker_reports' }"
        ) {{ $t('template.Timetracker_reports') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('time_tracker') && $root.user.can_view_results_of_timetracker",
          :to="{ name: 'timetracker_report' }"
        ) {{ $t('hr.timetracker_user_report') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('time_tracker') && $root.user.can_view_results_of_timetracker",
          :to="{ name: 'timetracker_settings' }"
        ) {{ $t('template.timetracker_settings') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('time_tracker') && $root.user.can_use_timetracker",
          :to="{ name: 'timetracker_engine' }"
        ) {{ $t('template.Timetracker') }}
    li(
      v-if="($root.module_enabled('expences') && $root.can_do('expences', 'read') != 0) || ($root.module_enabled('invoices') && $root.can_do('invoices', 'read') != 0)"
    )
      a.dropdown-toggle(
        href="#expencesDropdown",
        data-toggle="collapse",
        aria-expanded="false"
      ) {{ $t('template.Finances') }}
      ul#expencesDropdown.collapse.list-unstyled
        router-link.dropdown-item(
          v-if="$root.module_enabled('expences') && $root.can_do('expences', 'read') != 0",
          :to="{ name: 'expences_index' }"
        ) {{ $t('expences.all_expences') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('invoices') && $root.can_do('invoices', 'read') != 0",
          :to="{ name: 'invoice_index' }"
        ) {{ $t('template.invoices') }}
    li(
      v-if="$root.can_do('events', 'read') != 0 || $root.user.can_see_financial_calendar === true"
    )
      a.dropdown-toggle(
        href="#calendarDropdown",
        data-toggle="collapse",
        aria-expanded="false"
      ) {{ $t('template.Calendar') }}
      ul#calendarDropdown.collapse.list-unstyled
        router-link.dropdown-item(
          v-if="$root.module_enabled('calendar') && $root.can_do('events', 'read') != 0",
          :to="{ name: 'calendar_index' }"
        ) {{ $t('template.Tasks') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('hr')",
          :to="{ name: 'vacations_calendar_index' }"
        ) {{ $t('template.vacations_calendar') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('financial_calendar') && $root.user.can_see_financial_calendar === true",
          :to="{ name: 'finances_index' }"
        ) {{ $t('template.Finances') }}
    li(v-if="$root.is_user_in_company() === true")
      router-link.nav-link(:to="{ name: 'my_works_index' }") {{ $t('template.My_works') }}
    li(v-if="$root.module_enabled('email')")
      router-link.nav-link(:to="{ name: 'email_index' }") {{ $t('template.Mail') }}
    li
      router-link.nav-link(:to="{ name: 'contractors_index' }") {{ $t('template.contractors_and_teams') }}
    li(v-if="$root.module_enabled('project')")
      a.dropdown-toggle(
        href="#projectDropdown",
        data-toggle="collapse",
        aria-expanded="false"
      ) {{ $t('template.Projects') }}
      ul#projectDropdown.collapse.list-unstyled
        router-link.dropdown-item(
          v-if="can_read_anything_in_projects",
          :to="{ name: 'projects_index' }"
        ) {{ $t('template.All_projects') }}
        router-link.dropdown-item(:to="{ name: 'specifications_index' }") {{ $t('template.All_specifications') }}
        router-link.dropdown-item(:to="{ name: 'equipments_index' }") {{ $t('template.All_equipments') }}
        router-link.dropdown-item(:to="{ name: 'technical_documents_index' }") {{ $t('project.Technical_documentation') }}
    li(v-if="$root.module_enabled('project')")
      a.dropdown-toggle(
        href="#accountsDropdown",
        data-toggle="collapse",
        aria-expanded="false"
      ) {{ $t('template.Accounts') }}
      ul#accountsDropdown.collapse.list-unstyled
        router-link.dropdown-item(:to="{ name: 'manufacturers_index' }") {{ $t('template.All_manufacturers') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('project') && $root.can_do('legal_entities', 'read') !== 0",
          :to="{ name: 'legal_entities_index' }"
        ) {{ $t('project.Legal_entities') }}
        router-link.dropdown-item(:to="{ name: 'carriers_index' }") {{ $t('template.All_carriers') }}
    li(
      v-if="$root.module_enabled('analytics') && this.$root.user.dashboard_id"
    )
      a.dropdown-toggle(
        href="#analytics",
        data-toggle="collapse",
        aria-expanded="false"
      ) {{ $t('template.analytics') }}
      ul#analytics.collapse.list-unstyled
        router-link.dropdown-item(:to="{ name: 'analytics_dashboards_index' }") {{ $t('dashboard.Widgets') }}
        router-link.dropdown-item(:to="{ name: 'google_ads_index' }") Google Ads
    li(v-if="$root.module_enabled('invoices')")
      a.dropdown-toggle(
        href="#products",
        data-toggle="collapse",
        aria-expanded="false"
      ) {{ $t('template.Products') }}
      ul#products.collapse.list-unstyled
        router-link.dropdown-item(
          v-if="$root.can_do('products', 'read') !== 0",
          :to="{ name: 'products_index' }"
        ) {{ $t('template.all_products') }}
    li(v-if="$root.user.is_admin")
      a.dropdown-toggle(
        href="#settingsDropdown",
        data-toggle="collapse",
        aria-expanded="false"
      ) {{ $t('template.Settings') }}
      ul#settingsDropdown.collapse.list-unstyled
        router-link.dropdown-item(:to="{ name: 'global_settings' }") {{ $t('template.GlobalSettings') }}
        router-link.dropdown-item(:to="{ name: 'client_settings' }") {{ $t('template.ClientSettings') }}
        router-link.dropdown-item(:to="{ name: 'service_settings' }") {{ $t('template.ServiceSettings') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('email')",
          :to="{ name: 'email_settings' }"
        ) {{ $t('template.EmailSettings') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('calendar')",
          :to="{ name: 'calendar_settings' }"
        ) {{ $t('template.CalendarSettings') }}
        router-link.dropdown-item(:to="{ name: 'checklists_settings' }") {{ $t('template.Checklists') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('estimate')",
          :to="{ name: 'estimate_settings' }"
        ) {{ $t('template.EstimateSettings') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('project')",
          :to="{ name: 'project_settings' }"
        ) {{ $t('template.ProjectSettings') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('forms_integration')",
          :to="{ name: 'site_settings' }"
        ) {{ $t('template.SiteSettings') }}
        router-link.dropdown-item(:to="{ name: 'integration_settings' }") {{ $t('template.IntegrationSettings') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('dashboard') || $root.module_enabled('analytics')",
          :to="{ name: 'dashboards_settings' }"
        ) {{ $t('template.DashboardSettings') }}
        router-link.dropdown-item(:to="{ name: 'users_groups' }") {{ $t('template.UserGroups') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('kpi')",
          :to="{ name: 'kpi_users_and_groups' }"
        ) {{ $t('template.Kpi') }}
        router-link.dropdown-item(:to="{ name: 'hr_settings' }") {{ $t('template.hr_settings') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('estimate_fork')",
          :to="{ name: 'estimate_forks_settings' }"
        ) {{ $t('template.EstimateForks') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('gantt')",
          :to="{ name: 'planning_settings' }"
        ) {{ $t('template.PlanningSettings') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('invoices')",
          :to="{ name: 'company_information' }"
        ) {{ $t('template.Company_information') }}
        router-link.dropdown-item(
          v-if="$root.module_enabled('invoices')",
          :to="{ name: 'invoice_settings' }"
        ) {{ $t('template.invoice_settings') }}
        router-link.dropdown-item(:to="{ name: 'iva_settings' }") {{ $t('template.iva_settings') }}
        router-link.dropdown-item(:to="{ name: 'general_contractors' }") {{ $t('template.general_contractors') }}
        a.dropdown-item(
          v-if="$root.settings.external_token",
          target="_blank",
          :href="'http://new.diga.pt/personalArea/index#/auth/' + $root.settings.external_token"
        ) {{ $t('template.Payments') }}
</template>

<script>
export default {
  computed: {
    can_read_anything_in_projects() {
      return (
        this.$root.can_with_project("read", 0) ||
        this.$root.can_with_project("read", 1) ||
        this.$root.can_do("shipping_orders", "read") ||
        this.$root.can_with_project("read", 2) ||
        this.$root.can_with_project("read", 3) ||
        this.$root.can_with_project("read", 4) ||
        this.$root.can_with_project("read", 5)
      );
    },
  },
};
</script>

<style>
</style>