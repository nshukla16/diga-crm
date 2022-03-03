<style>
.mt-element-list .list-default.mt-list-container .mt-list-item {
  list-style: none;
  border-bottom: 1px solid;
  border-color: #e7ecf1;
  padding: 25px 0;
  min-height: 45px;
}
.mt-element-list
  .list-default.mt-list-container
  .mt-list-item
  > .list-datetime {
  text-align: right;
  float: right;
  width: 60px;
  line-height: 1.2;
}
.mt-element-list
  .list-default.mt-list-container
  .mt-list-item
  > .list-icon-container.done {
  border-color: #26c281;
}
.mt-element-list
  .list-default.mt-list-container
  .mt-list-item
  > .list-icon-container {
  border: 1px solid #e7ecf1;
  border-radius: 50% !important;
  padding: 0.9em;
  float: left;
  width: 45px;
  height: 45px;
}
.mt-element-list
  .list-default.mt-list-container
  .mt-list-item
  > .list-item-content {
  padding: 0 75px 0 60px;
}
.mt-element-list
  .list-default.mt-list-container
  .mt-list-item
  > .list-item-content
  > h3 {
  line-height: 25px;
  margin-bottom: 0;
  font-size: 16px;
}
.mt-element-list
  .list-default.mt-list-container
  .mt-list-item
  > .list-item-content
  > h3
  > a {
  color: #34495e;
}
.mt-element-list
  .list-default.mt-list-container
  .mt-list-item
  > .list-item-content
  > p {
  margin: 0;
}
.mt-element-list .list-default.mt-list-container .mt-list-item:last-child {
  border: none;
}
.mt-list-container > div {
  min-height: 45px;
}
.w-24 {
  width: 24%;
}
</style>

<template lang="pug">
section(v-if="my_groups")
  h2 {{ $t('template.UserGroups') }}
  .d-flex.flex-wrap.justify-content-start
    template(v-for="group in my_groups")
      team(:team="group", v-on:remove_group="remove_group(group)")
    .align-self-start.m-1.w-24
      button.btn.btn-diga.w-100(@click="add_group") {{ $t('template.Add') }}
  button.btn.btn-diga(type="button", v-on:click="save") {{ $t('template.Save') }}
</template>

<script>
import team_card from "./_group.vue";
import { mapGetters } from "vuex";

export default {
  data: function () {
    return {
      removed_groups: [],
      my_groups: null,
    };
  },
  components: {
    team: team_card,
  },
  computed: {
    ...mapGetters({
      groups: "getGroups",
    }),
  },
  watch: {
    groups() {
      this.my_groups = JSON.parse(JSON.stringify(this.groups));
    },
  },
  created() {
    this.my_groups = JSON.parse(JSON.stringify(this.groups));
  },
  methods: {
    add_group: function () {
      let group = {
        id: -1,
        name: "",
        users_ids: [],
        service_scope_id: this.my_groups[0].id,
      };
      this.my_groups.push(group);
    },
    remove_group(group) {
      if (confirm(this.$root.$t("template.Sure_remove_group"))) {
        this.my_groups[0].users_ids = this.my_groups[0].users_ids.concat(
          group.users_ids
        );
        this.removed_groups.push(group.id);
        let index = this.my_groups.indexOf(group);
        this.my_groups.splice(index, 1);
      }
    },
    save: function () {
      this.$validator.validateAll().then((result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        }
        let payload = JSON.parse(JSON.stringify(this.$data));
        this.$http.patch("/api/groups", payload).then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.removed_groups = [];
              this.$toastr.s(
                this.$root.$t("template.Groups_saved"),
                this.$root.$t("template.Success")
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
      });
    },
  },
  mounted() {
    document.title =
      this.$root.global_settings.site_name +
      " | " +
      this.$root.$t("template.UserGroups");
  },
};
</script>