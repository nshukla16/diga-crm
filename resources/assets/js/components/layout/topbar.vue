<template lang="pug">
div(style="background-color: #ffffff")
  .container-fluid
    .page-header.navbar.navbar-fixed-top
      .page-header-inner
        button.btn.btn-diga(
          type="button",
          @click="$root.toogleSidebar = !$root.toogleSidebar"
        )
          i.fa.fa-align-left
        .page-logo
          .menu-toggler.sidebar-toggler
            span
      a.menu-toggler.responsive-toggler(
        href="javascript:;",
        data-toggle="collapse",
        data-target=".navbar-collapse"
      )
        span
      .top-menu
        ul.nav.navbar-nav.navbar-expand.pull-right
          li
            router-link(:to="{ name: 'profile_settings' }")
              span#username.username {{ user.name }}
          li
            router-link(:to="{ name: 'profile_settings' }")
              img.img-circle(
                alt="",
                style="max-height: 38px; max-width: 38px",
                :src="user.photo"
              )
          li
            .notification-button(@click="$root.notifications_toggle_click")
              i.fa.fa-bell-o.not-bell
              span.badge.badge-pill.badge-danger(
                v-if="$root.not_count != 0",
                v-text="$root.not_count"
              )
          li
            a(href="#", style="font-size: 22px", @click.prevent="logout")
              i.fa.fa-sign-out
  notifications_popup(v-if="$root.show_notifications")
</template>

<script>
import notifications_popup from "./../home/notifications_popup.vue";

export default {
  data() {
    return {};
  },
  components: {
    notifications_popup,
  },
  computed: {
    user() {
      return this.$store.getters.getUser;
    },
  },
  methods: {
    logout: function () {
      const url = new URL("https://diga.eu.auth0.com/v2/logout");

      url.searchParams.append("client_id", "n2hhgGIZIjwoMxI8BOZeaU2bA2DJR4jM");
      url.searchParams.append("returnTo", window.location.origin + "/signoff");

      location.href = url.href;
    },
  },
};
</script>