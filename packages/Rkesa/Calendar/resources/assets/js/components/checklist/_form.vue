<style>
.picker-container {
  position: relative;
}
</style>

<template lang="pug">
.diga-container.p-4(v-if="currentChecklist")
  h2 {{ $t('calendar.Checklist') }}
  fieldset.form-group(:class="{ 'has-error': errors.has('name') }")
    label.control-label {{ $t('calendar.Title') }}
    input.form-control(
      v-model="currentChecklist.name",
      name="name",
      v-validate="'required'",
      v-bind:data-vv-as="$t('calendar.Title').toLowerCase()"
    )
    span.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
  table.table.table-striped.table-hover
    thead
      th(style="width: 1px") №
      th(style="width: 50px") {{ $t('calendar.Order') }}
      th {{ $t('calendar.Question') }}
      th(style="width: 160px") {{ $t('calendar.Background_color') }}
      th(style="width: 1px") {{ $t('template.Remove') }}
    tbody
      tr(v-for="question in currentChecklist.checklist_entities")
        td {{ question.order }}
        td
          i.fa.fa-chevron-up(
            style="cursor: pointer",
            v-on:click="to_up(question)"
          )
          i.fa.fa-chevron-down(
            style="cursor: pointer",
            v-on:click="to_down(question)"
          )
        td
          input.form-control(v-model="question.name")
        td.picker-container
          sketch-picker(
            v-if="selected[question.order - 1]",
            v-model="question.color",
            v-on-clickaway="hide_picker"
          )
          .color-icon.color(
            v-bind:style="{ 'background-color': question.color.hex }",
            v-on:click="show_picker(question.order - 1)"
          )
          input.form-control.settings-inputs(
            style="width: 100px",
            v-model="question.color.hex"
          )
        td
          button.btn.red(v-on:click="remove_question(question)") {{ $t('template.Remove') }}
    tfoot
      td(colspan="5")
        button.btn.blue(v-on:click="add_question") {{ $t('calendar.Add_question') }}

  h3 {{ $t('calendar.WorkStages') }}
  table.table.table-striped.table-hover
    thead
      th(style="width: 1px") №
      th(style="width: 50px") {{ $t('calendar.Order') }}
      th {{ $t('calendar.WorkStages') }}
      th(style="width: 1px") {{ $t('template.Remove') }}
    tbody
      tr(v-for="work in currentChecklist.checklist_works")
        td {{ work.order }}
        td
          i.fa.fa-chevron-up(
            style="cursor: pointer",
            v-on:click="to_up_work(work)"
          )
          i.fa.fa-chevron-down(
            style="cursor: pointer",
            v-on:click="to_down_work(work)"
          )
        td
          input.form-control(v-model="work.text")
        td
          button.btn.red(v-on:click="remove_work(work)") {{ $t('template.Remove') }}
    tfoot
      td(colspan="5")
        button.btn.blue(v-on:click="add_work") {{ $t('calendar.Add') }}

  h3 {{ $t('calendar.WorkAreas') }}
  table.table.table-striped.table-hover
    thead
      th(style="width: 1px") №
      th(style="width: 50px") {{ $t('calendar.Order') }}
      th {{ $t('calendar.WorkAreas') }}
      th(style="width: 1px") {{ $t('template.Remove') }}
    tbody
      tr(v-for="area in currentChecklist.checklist_areas")
        td {{ area.order }}
        td
          i.fa.fa-chevron-up(
            style="cursor: pointer",
            v-on:click="to_up_area(area)"
          )
          i.fa.fa-chevron-down(
            style="cursor: pointer",
            v-on:click="to_down_area(area)"
          )
        td
          input.form-control(v-model="area.text")
        td
          button.btn.red(v-on:click="remove_area(area)") {{ $t('template.Remove') }}
    tfoot
      td(colspan="5")
        button.btn.blue(v-on:click="add_area") {{ $t('calendar.Add') }}

  fieldset.form-group
    label.control-label {{ $t('calendar.Description') }}
    textarea.form-control(v-model="currentChecklist.description")
  fieldset.form-group
    label.control-label {{ $t('calendar.Footer') }}
    input.form-control(v-model="currentChecklist.footer")
  button.btn.btn-diga(v-on:click="save_checklist") {{ $t('template.Save') }}
</template>

<script>
export default {
  props: ["id"],
  data: function () {
    return {
      isCreating: true,
      currentChecklist: null,
      selected: [],
      removed: [],
      removed_works: [],
      removed_areas: [],
    };
  },
  methods: {
    load_checklist: function () {
      this.$root.global_loading = true;
      this.$http.get("/api/checklists/" + this.id + "?format=json").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            this.$root.global_loading = false;
          } else {
            this.currentChecklist = res.data;
            this.isCreating = false;

            for (
              let i = 0;
              i < this.currentChecklist.checklist_entities.length;
              i++
            ) {
              this.selected.push(false);
              this.currentChecklist.checklist_entities[i].color = {
                hex: this.currentChecklist.checklist_entities[i].color,
              };
            }

            this.$root.global_loading = false;
          }
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
    save_checklist: function () {
      this.$validator.validateAll().then((result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        }
        this.$root.global_loading = true;
        let payload = JSON.parse(JSON.stringify(this.currentChecklist));
        payload.removed = this.removed;
        payload.removed_works = this.removed_works;
        payload.removed_areas = this.removed_areas;
        for (let i = 0; i < payload.checklist_entities.length; i++) {
          payload.checklist_entities[i].color =
            payload.checklist_entities[i].color.hex;
        }
        if (this.isCreating) {
          this.$http.post("/api/checklists", payload).then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
              } else {
                this.$toastr.s(
                  this.$root.$t("calendar.Checklist_saved"),
                  this.$root.$t("template.Success")
                );
                this.$router.push({ name: "checklists_settings" });
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
        } else {
          this.$http
            .patch("/api/checklists/" + this.currentChecklist.id, payload)
            .then(
              (res) => {
                if (res.data.errcode == 1) {
                  this.$toastr.e(
                    res.data.errmess,
                    this.$root.$t("template.Error")
                  );
                } else {
                  this.$toastr.s(
                    this.$root.$t("calendar.Checklist_updated"),
                    this.$root.$t("template.Success")
                  );
                  this.$router.push({ name: "checklists_settings" });
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
        }
      });
    },
    show_picker(i) {
      Vue.set(this.selected, i, true);
    },
    hide_picker() {
      for (
        let i = 0;
        i < this.currentChecklist.checklist_entities.length;
        i++
      ) {
        Vue.set(this.selected, i, false);
      }
    },
    add_question: function () {
      let question = {
        id: 0,
        name: "",
        order: this.currentChecklist.checklist_entities.length + 1,
        color: { hex: "#ffffff" },
      };
      this.currentChecklist.checklist_entities.push(question);
    },
    remove_question: function (question) {
      if (
        confirm(this.$root.$t("calendar.Are_you_sure_want_to_delete_question"))
      ) {
        this.removed.push(question.id);
        let index = this.currentChecklist.checklist_entities.indexOf(question);
        this.currentChecklist.checklist_entities.splice(index, 1);
        this.selected.splice(index, 1);
        for (
          var i = index;
          i < this.currentChecklist.checklist_entities.length;
          i++
        ) {
          this.currentChecklist.checklist_entities[i].order--;
        }
      }
    },
    to_up(question) {
      if (question.order > 1) {
        this.currentChecklist.checklist_entities.splice(question.order - 1, 1);
        this.currentChecklist.checklist_entities.splice(
          question.order - 2,
          0,
          question
        );
        this.currentChecklist.checklist_entities[question.order - 1].order++;
        question.order--;
      }
    },
    to_down(question) {
      if (question.order < this.currentChecklist.checklist_entities.length) {
        this.currentChecklist.checklist_entities.splice(question.order - 1, 1);
        this.currentChecklist.checklist_entities.splice(
          question.order,
          0,
          question
        );
        this.currentChecklist.checklist_entities[question.order - 1].order--;
        question.order++;
      }
    },
    add_work: function () {
      let work = {
        id: 0,
        text: "",
        order: this.currentChecklist.checklist_works.length + 1,
      };
      this.currentChecklist.checklist_works.push(work);
    },
    remove_work: function (work) {
      if (confirm(this.$root.$t("calendar.AreYouSure"))) {
        this.removed_works.push(work.id);
        let index = this.currentChecklist.checklist_works.indexOf(work);
        this.currentChecklist.checklist_works.splice(index, 1);
        for (
          var i = index;
          i < this.currentChecklist.checklist_works.length;
          i++
        ) {
          this.currentChecklist.checklist_works[i].order--;
        }
      }
    },
    to_up_work(work) {
      if (work.order > 1) {
        this.currentChecklist.checklist_works.splice(work.order - 1, 1);
        this.currentChecklist.checklist_works.splice(work.order - 2, 0, work);
        this.currentChecklist.checklist_works[work.order - 1].order++;
        work.order--;
      }
    },
    to_down_work(work) {
      if (work.order < this.currentChecklist.checklist_works.length) {
        this.currentChecklist.checklist_works.splice(work.order - 1, 1);
        this.currentChecklist.checklist_works.splice(work.order, 0, work);
        this.currentChecklist.checklist_works[work.order - 1].order--;
        work.order++;
      }
    },
    add_area: function () {
      let area = {
        id: 0,
        text: "",
        order: this.currentChecklist.checklist_areas.length + 1,
      };
      this.currentChecklist.checklist_areas.push(area);
    },
    remove_area: function (area) {
      if (confirm(this.$root.$t("calendar.AreYouSure"))) {
        this.removed_areas.push(area.id);
        let index = this.currentChecklist.checklist_areas.indexOf(area);
        this.currentChecklist.checklist_areas.splice(index, 1);
        for (
          var i = index;
          i < this.currentChecklist.checklist_areas.length;
          i++
        ) {
          this.currentChecklist.checklist_areas[i].order--;
        }
      }
    },
    to_up_area(area) {
      if (area.order > 1) {
        this.currentChecklist.checklist_areas.splice(area.order - 1, 1);
        this.currentChecklist.checklist_areas.splice(area.order - 2, 0, area);
        this.currentChecklist.checklist_areas[area.order - 1].order++;
        area.order--;
      }
    },
    to_down_area(area) {
      if (area.order < this.currentChecklist.checklist_areas.length) {
        this.currentChecklist.checklist_areas.splice(area.order - 1, 1);
        this.currentChecklist.checklist_areas.splice(area.order, 0, area);
        this.currentChecklist.checklist_areas[area.order - 1].order--;
        area.order++;
      }
    },
  },
  mounted() {
    if (this.id) {
      document.title =
        this.$root.global_settings.site_name +
        " | " +
        this.$root.$t("calendar.Edit_checklist");
      this.load_checklist();
    } else {
      document.title =
        this.$root.global_settings.site_name +
        " | " +
        this.$root.$t("calendar.New_checklist");
      let newChecklist = {
        name: "",
        checklist_entities: [],
        checklist_works: [],
        checklist_areas: [],
        footer: "",
        description: "",
      };
      this.currentChecklist = Object.assign({}, newChecklist);
    }
  },
};
</script>