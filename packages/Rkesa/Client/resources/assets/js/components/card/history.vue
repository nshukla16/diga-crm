<style>
.general-item-list > .item > .item-head > .item-status {
  top: inherit;
}
.input-cont {
  padding: 0 14px;
}
.comment_text {
  float: left;
  font-size: 14px;
  height: 50px;
  font-style: italic;
}
.btn-cont {
  height: 39px;
  color: #ffffff;
  border-radius: 4px;
  margin-right: 13px;
}
.general-item-list {
  padding: 7px 12px;
  margin-top: 9px;
}
.item-head {
  text-align: right;
}
.item-name.primary-link {
  float: left;
}
.item-label {
  float: right;
  opacity: 0.3;
  font-size: 11pt;
}
.item-status {
  float: right;
  opacity: 0.5;
  clear: both;
  font-size: 11pt;
}
.item-body {
  padding-top: 27px;
  font-size: 11pt;
  line-height: 16px;
}
#history_block .modal {
  padding-right: 0 !important;
  color: #000000;
}
#history_block .btn {
  color: #ffffff;
}
</style>

<template lang="pug">
div
  #history_block.diga-container(style="height: 550px")
    .portlet-title
      .caption(style="padding-left: 25px; padding-right: 10px")
        span.caption-subject.bold.uppercase {{ $t('client.History') }}
    .portlet-body
      .chat-form
        .d-flex(v-if="main_contact_id", style="padding-top: 10px")
          .col.input-cont
            input.form-control.with-gradient.comment_text(
              type="text",
              v-model="comment_text",
              v-on:keyup.enter="send_message()",
              v-bind:placeholder="$t('client.Type_a_message_here')"
            )
          .btn-cont(style="max-width: 45px")
            a(
              v-bind:class="{ btn: true, 'btn-diga': true, 'color: #ffffff': !loading, 'grey-silver': loading }",
              v-on:click="send_message()",
              style="margin: 0; float: right; height: 50px; width: 45px; padding-top: 12px"
            )
              i.fa.fa-check(v-show="!loading")
              div(v-show="loading")
                .loader.sm-loader
        .general-item-list
          div(v-bar="", style="height: 410px")
            div
              div(v-if="history_entities.length > 0")
                .item.with-gradient(v-for="h in history_entities")
                  .item-head
                    .item-details
                      a.item-name.primary-link(
                        v-if="[1, 3, 20, 21, 22, 23, 24].indexOf(h.type_id) !== -1 && h.user_id",
                        href="#",
                        v-text="users[h.user_id].name"
                      )
                      a.item-name.primary-link(
                        v-if="[2, 18].indexOf(h.type_id) !== -1 || h.user_id == null",
                        href="#",
                        v-text="$t('client.system')"
                      )
                      a.item-name.primary-link(
                        v-if="[4, 5, 19, 6].indexOf(h.type_id) !== -1",
                        href="#",
                        v-text="$root.fullName(h.client_contact)"
                      )
                      span.item-label(v-text="h.created_at")
                    span.item-status.ml-2 {{ get_type(h) }}
                  .item-body(
                    v-if="[1, 3, 4, 5, 19, 20, 6, 21, 22, 23, 24].indexOf(h.type_id) !== -1"
                  )
                    div(v-if="[23, 24].indexOf(h.type_id) !== -1")
                      span {{ message_text(h) }}
                    div(v-if="[1, 5, 19, 20, 6].indexOf(h.type_id) !== -1")
                      span(v-if="h.message == ''") &nbsp;
                      span(v-else, v-text="h.message")
                    div(v-if="[3, 4].indexOf(h.type_id) !== -1")
                      button.btn.btn-diga.btn-sm(
                        type="button",
                        @click="load_message(h)"
                      )
                        | {{ get_client_or_user_name(h) }}
                      .modal.fade(
                        :id="'history-' + h.id",
                        tabindex="-1",
                        role="dialog",
                        aria-hidden="true"
                      )
                        .modal-dialog.modal-lg(role="document")
                          .modal-content
                            .modal-header
                              h5.modal-title {{ get_client_or_user_name(h) }}
                              button.close(
                                type="button",
                                data-dismiss="modal",
                                aria-label="Close"
                              )
                                span(aria-hidden="true") ×
                            .modal-body(v-html="h.message")
                    div(v-if="[21, 22].indexOf(h.type_id) !== -1")
                      audio-player(
                        v-if="historyAudioCondition(h)",
                        :file="h.call.record_link"
                      )
                      span(v-else) {{ $t('template.Call_recording_unavailable') }}
                    a(
                      v-bind:href="h.service_attachment.file",
                      v-if="h.type_id == 5 && h.service_attachment"
                    )
                      div(style="width: 60px; text-align: center")
                        div(style="height: 10px; margin: 10px")
                          i.fa.fa-file-text-o(
                            style="font-size: 40px; line-height: 40px"
                          )
                        div {{ h.service_attachment.name }}
                  .item-body(
                    v-if="h.type_id == 2",
                    v-text="change_status_text(h)"
                  )
                  .item-body(v-if="h.type_id == 18", v-text="finished_task(h)")
                infinite-loading(@infinite="infiniteHandler")

              .empty-filler(v-else) {{ $t('client.There_is_no_messages') }}
</template>
<script>
import moment from "moment";
import audioPlayer from "@/components/audio_player.vue";
import { mapGetters } from "vuex";
import InfiniteLoading from "vue-infinite-loading";

export default {
  props: ["company_id", "main_contact_id"],
  components: {
    "audio-player": audioPlayer,
    InfiniteLoading,
  },
  data() {
    return {
      history_entities: [],
      comment_text: "",
      loading: false,
      page: 1,
      offset: 0,
      limit: 10,
    };
  },
  created() {
    document.onkeydown = function (evt) {
      evt = evt || window.event;
      var isEscape = false;
      if ("key" in evt) {
        isEscape = evt.key == "Escape" || evt.key == "Esc";
      } else {
        isEscape = evt.keyCode == 27;
      }
      if (isEscape) {
        let popups = document.querySelectorAll("#history_block .item .modal");
        Array.prototype.forEach.call(popups, function (el) {
          let style = window.getComputedStyle(el);
          let display = style.getPropertyValue("display");
          if (display == "block") {
            el.querySelector(".close").click();
          }
        });
      }
    };
  },
  methods: {
    get_history() {
      let url = "/api/contacts/history/" + this.main_contact_id;
      if (this.company_id > 0) {
        url = "/api/clients/history/" + this.company_id;
      }

      this.offset = (this.page - 1) * this.limit;

      return this.$http.get(
        url + "?offset=" + this.offset + "&limit=" + this.limit
      );
    },
    infiniteHandler($state) {
      this.get_history()
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              if (data.rows.length) {
                this.page += 1;
                this.history_entities.push(...data.rows);
                $state.loaded();
              } else {
                $state.complete();
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
    historyAudioCondition(h) {
      let output = false;

      if (typeof h.call != "undefined" && h.call.is_recorded == 1)
        output = true;

      return output;
    },
    get_type(type) {
      switch (type.type_id) {
        case 1:
          return this.$root.$t("client.comment");
        case 2: // changed service state
          return this.$root.$t("client.system");
        case 3:
          return this.$root.$t("client.outgoing_mail");
        case 4:
          return this.$root.$t("client.incoming_mail");
        case 5:
          return this.sites_by_id[type.site_id].domain;
        case 6:
          return this.$root.$t("client.Integration");
        case 18: // finished task
          return this.$root.$t("client.system");
        case 19:
          return this.$root.$t("client.Fb_incoming");
        case 20:
          return this.$root.$t("client.Fb_outgoing");
        case 21:
          return this.$root.$t("zadarma.Call_incomming");
        case 22:
          return this.$root.$t("zadarma.Call_outgoing");
        case 23:
          return this.$root.$t("client.Subcontractors");
        case 24:
          return this.$root.$t("client.Finances");
      }
    },
    change_status_text(message) {
      let s = this.$root.get_status_by_id(message.service_state_id);
      return (
        this.users[message.user_id].name +
        " " +
        this.$root.$t("client.has_changed_status_of_estimate") +
        this.$root.service_number(message.service) +
        ": " +
        (s ? s.name : "Undefined") +
        (message.message
          ? ". " + this.$root.$t("client.comment") + ": " + message.message
          : "")
      );
    },
    finished_task(message) {
      if (message.event !== null) {
        return (
          this.users[message.user_id].name +
          " " +
          this.$root.$t("client.has_finished_task") +
          ' "' +
          message.event.event_type.title +
          '" ' +
          this.$root.$t("client.scheduled_at") +
          " " +
          message.event.start
        );
      } else {
        return (
          this.users[message.user_id].name +
          " " +
          this.$root.$t("client.has_finished_task") +
          ' "' +
          this.$root.$t("template.task_was_removed") +
          '" '
        );
      }
    },
    message_text(h) {
      let text = "";
      switch (h.type_id) {
        case 23:
          text =
            this.users[h.user_id].name +
            " " +
            this.$root.$t("client.has_changed_subcontractor");
          break;
        case 24:
          text =
            this.users[h.user_id].name +
            " " +
            this.$root.$t("client.has_changed_finance");
          break;
      }
      return text;
    },
    get_client_or_user_name(h) {
      return (
        (h.type_id == 3
          ? this.users[h.user_id].name
          : this.$root.fullName(h.client_contact)) +
        " " +
        this.$root.$t("client.Sent_message")
      );
    },
    send_message() {
      if (!this.loading) {
        if (this.comment_text) {
          this.loading = true;
          let com_text = this.comment_text;
          this.$http
            .post("/api/clients/" + this.main_contact_id + "/history/", {
              message: com_text,
            })
            .then(
              (res) => {
                if (res.data.errcode == 1) {
                  this.$toastr.e(
                    this.$root.$t("template.Something_bad_happened"),
                    this.$root.$t("template.Error")
                  );
                } else {
                  let comment = {
                    type_id: 1,
                    user_id: this.$root.user.id,
                    created_at: moment(new Date()).format(
                      "YYYY-MM-DD HH:mm:ss"
                    ),
                    message: com_text,
                  };
                  this.history_entities.unshift(comment);
                  this.comment_text = "";
                  this.$toastr.s(
                    this.$root.$t("client.Comment_saved"),
                    this.$root.$t("template.Success")
                  );
                }
                this.loading = false;
              },
              (res) => {
                this.$toastr.e(
                  this.$root.$t("template.Something_bad_happened"),
                  this.$root.$t("template.Error")
                );
                this.loading = false;
              }
            );
        } else {
          this.$toastr.w(
            this.$root.$t("client.Message_can_not_be_empty"),
            this.$root.$t("template.Warning")
          );
        }
      } else {
        this.$toastr.w(
          "client.Wait_until_the_previous_comment_is_saved",
          this.$root.$t("template.Warning")
        );
      }
    },
    add_system_message(message) {
      this.history_entities.unshift(message);
    },
    load_message(history) {
      this.$http
        .get("/api/history/" + history.id + "/message")
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              history.message = data.message;
              jQuery("#history-" + history.id).modal("show");
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
      users: "getUsersById",
      sites_by_id: "getSitesById",
    }),
  },
  mounted() {
    this.$bus.$on("system_message", this.add_system_message);
    this.get_history()
      .then((res) => {
        return res.json();
      })
      .then(
        (data) => {
          if (data.errcode == 1) {
            this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
          } else {
            if (data.rows.length) {
              this.page += 1;
              this.history_entities.push(...data.rows);
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
  beforeDestroy: function () {
    this.add_system_message &&
      this.$bus.$off("system_message", this.add_system_message);
  },
};
</script>