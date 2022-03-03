<style>
.empty_team {
  height: 45px;
  width: 100%;
  text-align: center;
}
.user-photo {
  width: 45px;
  height: 45px;
  float: left;
}
</style>

<template lang="pug">
.align-self-start.m-1.w-24
  .mt-element-list.diga-container.p-4(v-if="team")
    .mt-list-head.list-default(
      :class="{ 'green-haze': team.id != 1, 'red-haze': team.id == 1 }"
    )
      .row
        .col-8
          .list-head-title-container
            h4.uppercase(v-if="team.id == 1") {{ $t('template.Without_group') }}
            .d-flex.w-100(
              v-else,
              :class="{ 'has-error': errors.has('group_name_' + _uid) }"
            )
              h4.uppercase(
                style="line-height: 38px; margin-bottom: 0; margin-right: 10px"
              ) {{ $t('template.Group') }}
              input.form-control(
                v-validate="'required'",
                v-model="team.name",
                :name="'group_name_' + _uid"
              ) 
            .list-date {{ users.length }} {{ $tc('hr.Employees', users.length) }}
        .col-4
          .list-head-summary-container
            .list-done
              .list-datetime(style="text-align: right")
                span(style="font-size: 20px") {{ average_salary }}
                br
                |
                | {{ $root.current_currency.symbol }}/{{ $t('hr.Hour') }}
    .mt-list-container.list-default
      draggable(v-model="team.users_ids", :options="{ group: 'teams' }")
        template(v-if="users.length != 0")
          .mt-list-item(v-for="element in users")
            img.img-circle.user-photo(:src="element.photo")
            .list-datetime
              span(style="font-size: 20px") {{ element.salary }}
              br
              |
              | {{ $root.current_currency.symbol }}/{{ $t('hr.Hour') }}
            .list-item-content
              h3.uppercase.bold
                router-link(
                  :to="{ name: 'user_edit', params: { id: element.id } }"
                ) {{ element.name }}
              router-link(
                :to="{ name: 'user_permissions', params: { id: element.id } }"
              ) {{ $t('hr.Permission_settings') }}
        .empty_team(v-else="") {{ $t('hr.No_participants') }}
    div(v-if="team.id != 1")
      button.btn.btn-danger.w-100(@click="remove_group") {{ $t('template.Remove') }}
    .mt-2
      div {{ $t('service.Scope') }}
      select.form-control(v-model="team.service_scope_id")
        option(v-for="scope in scopes", :value="scope.id") {{ scope.name }}
    .mt-2
      div {{ $t('template.Head_of_group') }}
      select.form-control(v-model="team.head_user_id")
        option(v-for="user_id in team.users_ids", :value="user_id") {{ users_by_id[user_id].name }}
    .mt-2
      div {{ $t('template.Department_or_company') }}
      select.form-control(v-model="team.type")
        option(value="1") {{ $t('template.Department') }}
        option(value="2") {{ $t('template.Company') }}
    .mt-2.text-center(v-if="team.type === 2")
      router-link(
        v-if="team.client_id > 0",
        :to="{ name: 'company_show', params: { id: team.client_id } }"
      ) {{ $t('client.Perfil') }}
      router-link(
        v-else,
        :to="{ name: 'company_create_group', params: { group_id: team.id } }"
      ) {{ $t('template.create_profile') }}
</template>

<script>
import draggable from "vuedraggable";
import { mapGetters } from "vuex";

export default {
  data: function () {
    return {
      //
    };
  },
  inject: ["$validator"],
  components: {
    draggable,
  },
  props: {
    team: Object,
  },
  methods: {
    round10: function (num) {
      return Math.round(num * 100) / 100;
    },
    remove_group: function () {
      this.$emit("remove_group");
    },
  },
  watch: {
    "team.users_ids": function (newIds, oldIds) {
      if (oldIds.length == 0 && newIds.length != 0) {
        this.team.head_user_id = newIds[0];
      }
      if (newIds.indexOf(this.team.head_user_id) === -1) {
        this.team.head_user_id = newIds[0];
      }
    },
  },
  computed: {
    ...mapGetters({
      scopes: "getServiceScopes",
      all_users: "getUsers",
      users_by_id: "getUsersById",
    }),
    users() {
      let $this = this;
      return this.all_users.filter(function (e) {
        return $this.team.users_ids.includes(e.id);
      });
    },
    average_salary: function () {
      if (this.users.length === 0) {
        return 0;
      } else {
        let as = 0;
        this.users.forEach(function (user) {
          as += user.salary;
        });
        return this.round10(as);
      }
    },
  },
};
</script>