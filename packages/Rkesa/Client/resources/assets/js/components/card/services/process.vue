<style>
.table-states-wrapper {
  position: relative;
  overflow: hidden;
}
.states-wrapper {
  overflow: hidden;
  white-space: nowrap;
  position: absolute;
  top: 0;
  padding-left: 2px;
}
.arrows {
  width: 35px;
}
.arrow-button {
  width: 30px;
  text-align: center;
  -webkit-border-radius: 50% !important;
  -moz-border-radius: 50% !important;
  border-radius: 50% !important;
  cursor: pointer;
}
.arrow-button:hover {
  background-color: #eeeeee;
}
.arrow-button i {
  line-height: 26px !important;
}
.no-pointer-events .line-wrapper {
  pointer-events: none;
}
</style>

<template lang="pug">
table(style="width: 100%; table-layout: fixed")
  tr
    td.arrows
      .arrow-button(v-on:click="to_left()")
        i.fa.fa-chevron-left
    td(
      :class="{ 'table-states-wrapper': true, 'no-pointer-events': p_editable }",
      v-bind:style="{ height: get_process_height + 'px' }",
      ref="line_wrapper"
    )
      .states-wrapper(
        :style="{ left: animated_x + 'px', height: get_process_height + 'px' }",
        ref="line"
      )
        template(v-if="scopes_loaded", v-for="scope in scopes")
          scope(
            :scope="scope",
            :h="get_process_height",
            @scope_changed="scope_changed(scope, $event)"
          )
        template(v-for="state in mystates")
          state(
            :id="'state-' + state.id",
            :service_state="state",
            :selected_order="current_order",
            :vertical_order="get_vertical_order(state)",
            :next_vertical_ids="get_next_vertical_ids(state)",
            :selected_id="current_id",
            v-on:activate="activate_state"
          )
    td.arrows(style="padding-left: 10px")
      .arrow-button(v-on:click="to_right()")
        i.fa.fa-chevron-right
</template>

<script>
import state from "./_state.vue";
import scope from "./../../../../../../../Service/resources/assets/js/components/service_settings/_scope.vue";
import { mapGetters } from "vuex";

export default {
  props: ["mystates", "current_order", "current_id", "p_editable", "scopes"],
  data() {
    return {
      x: 0,
      animated_x: 0,
      scroll_amount: 200,
      scopes_loaded: false,
    };
  },
  components: {
    state,
    scope,
  },
  watch: {
    x: function (newValue, oldValue) {
      var $this = this;
      function animate() {
        if (TWEEN.update()) {
          requestAnimationFrame(animate);
        }
      }
      new TWEEN.Tween({ tmp_x: oldValue })
        .easing(TWEEN.Easing.Quadratic.Out)
        .to({ tmp_x: newValue }, 500)
        .onUpdate(function () {
          $this.animated_x = this.tmp_x.toFixed(0);
        })
        .start();
      animate();
    },
  },
  mounted() {
    if (this.scopes) {
      let $this = this;
      this.scopes.forEach(function (scope) {
        scope.start_x =
          $this.get_state_x(
            $this.service_states_by_id[scope.start_service_state_id]
          ) + 2;
        scope.start_width =
          $this.get_state_x(
            $this.service_states_by_id[scope.end_service_state_id]
          ) +
          $this.get_state_width(
            $this.service_states_by_id[scope.end_service_state_id]
          ) -
          $this.get_state_x(
            $this.service_states_by_id[scope.start_service_state_id]
          ) -
          5;
      });
      // wait while states and rendered so jquery can get theirs positions
      setTimeout(function () {
        $this.scopes_loaded = true;
      }, 500);
    }
  },
  methods: {
    m(e) {
      this.current_section = e;
      this.$emit("update-recipe-clicked", e);
    },
    scope_changed(scope, [x, width]) {
      let a = [];
      this.mystates.forEach(function (state) {
        let stx = jQuery("#state-" + state.id).position().left;
        let stwidth = jQuery("#state-" + state.id).width();
        if (stx + stwidth >= x && stx <= x + width) {
          a.push(state);
        }
      });
      scope.start_service_state_id = a[0].id;
      scope.end_service_state_id = a[a.length - 1].id;
    },
    get_state_x(state) {
      if (state != null) {
        return jQuery("#state-" + state.id).position().left;
      } else {
        return 50;
      }
    },
    get_state_width(state) {
      if (state != null) {
        return jQuery("#state-" + state.id).width();
      } else {
        return 50;
      }
    },
    activate_state(service_state) {
      this.$emit("activate_state", service_state);
    },
    get_vertical_order: function (state) {
      let order = 0;
      if (!state.horizontal) {
        let index = this.mystates.indexOf(state);
        for (let i = index; i >= 0; i--) {
          if (!this.mystates[i].horizontal) {
            order++;
          } else {
            break;
          }
        }
      }
      return order;
    },
    get_next_vertical_ids: function (state) {
      let index = this.mystates.indexOf(state);
      let ids = [];
      for (let i = index; i < this.mystates.length; i++) {
        if (!this.mystates[i].horizontal) {
          ids.push(this.mystates[i].id);
        } else {
          break;
        }
      }
      return ids;
    },
    status_to_center: function () {
      let $this = this;
      Vue.nextTick(function () {
        setTimeout(function () {
          let active_x = $this.$el
            .querySelector(".line-wrapper.active .line-point")
            .getBoundingClientRect().left;
          let border_x = $this.$refs.line.getBoundingClientRect().left;
          let active_x_from_border = active_x - border_x;
          let prefinal_x =
            $this.$refs.line_wrapper.getBoundingClientRect().width / 2 -
            active_x_from_border;
          if (prefinal_x > 0) {
            prefinal_x = 0;
          }
          if (
            prefinal_x <
            -1 *
              ($this.$refs.line.clientWidth -
                $this.$refs.line_wrapper.clientWidth)
          ) {
            prefinal_x =
              -1 *
              ($this.$refs.line.clientWidth -
                $this.$refs.line_wrapper.clientWidth);
          }
          $this.x = prefinal_x;
        }, 100);
      });
    },
    to_left: function () {
      if (this.x <= -1 * this.scroll_amount) {
        this.x += this.scroll_amount;
      } else {
        this.x = 0;
      }
    },
    to_right: function () {
      if (
        this.x - this.scroll_amount >
        -1 * (this.$refs.line.clientWidth - this.$refs.line_wrapper.clientWidth)
      ) {
        this.x -= this.scroll_amount;
      } else {
        this.x =
          -1 *
          (this.$refs.line.clientWidth - this.$refs.line_wrapper.clientWidth);
      }
    },
  },
  computed: {
    ...mapGetters({
      service_states_by_id: "getServiceStatesById",
    }),
    get_process_height() {
      let $this = this;
      let max_vertical_states = 0;
      let tmp_vertical_state = 0;
      this.mystates.forEach(function (state) {
        if (!state.horizontal) {
          tmp_vertical_state++;
        } else {
          tmp_vertical_state = 0;
        }
        if (tmp_vertical_state > max_vertical_states) {
          max_vertical_states = tmp_vertical_state;
        }
      });
      return 100 + 25 * max_vertical_states;
    },
  },
};
</script>