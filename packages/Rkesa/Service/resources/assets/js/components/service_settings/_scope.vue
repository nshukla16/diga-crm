<style>
    .vdr{
        opacity: 0.6;
    }
</style>

<template lang="pug">
    vue-draggable-resizable(:x="scope.start_x", :w="scope.start_width", :h="h", :z="1", :handles="['mr', 'ml']" v-on:dragging="onDrag", v-on:resizing="onResize", :parent="true", axis="x", :style="{backgroundColor: scope.color}")
</template>

<script>
import VueDraggableResizable from 'vue-draggable-resizable'

export default {
    props: {
        scope: { type: Object },
        h: { type: Number },
    },
    components: {
        VueDraggableResizable,
    },
    data() {
        return {
            x: 0,
            width: 0,
        }
    },
    mounted(){
        this.$bus.$on("rerender_scopes", this.calculate_overlay);
    },
    beforeDestroy: function() {
        this.calculate_overlay && this.$bus.$off("rerender_scopes", this.calculate_overlay);
    },
    methods: {
        calculate_overlay: function(){
            this.$emit('scope_changed', [this.x, this.width]);
        },
        onResize: function (x, y, width, height) {
            this.x = x;
            this.width = width;
            this.calculate_overlay();
        },
        onDrag: function (x, y) {
            this.x = x;
            this.calculate_overlay();
        },
    },
}
</script>