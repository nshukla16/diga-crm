<style>
    .line,
    .vertical-subline,
    .horizontal-subline{
        background-color: #9e9e9e;
    }
    .line-wrapper{
        display:inline-block;
        vertical-align: top;
    }
    .line-point{
        -webkit-border-radius: 50% !important;
        -moz-border-radius: 50% !important;
        width: 11px;
        height: 11px;
        cursor: pointer;
        padding: 2px;
        display: inline-block;
    }
    .line-point-active{
        -webkit-border-radius: 50% !important;
        -moz-border-radius: 50% !important;
        border-radius: 50% !important;
        width: 100%;
        height: 100%;
        background-color: #ffffff;
    }
    .line-button{
        color: white;
        padding: 3px 7px;
        display: inline-block;
        cursor: pointer;
        border-radius: 7px;
    }
    /* horizontal line styles */
    .horizontal-line{
        min-width: 100px;
        z-index: 1;
        position: relative;
    }
    .h-line-header{
        text-align: center;
        line-height: 20px;
        cursor: pointer;
        margin: 10px 20px 0;
    }
    .line{
        margin-top: 10px;
        width: 100%;
        height: 2px;
    }
    .red-line{
        margin-top: -2px;
        height: 2px;
    }
    .h-line-point{
        text-align: center;
        margin-top: -12px;
    }
    .h-line-button-wrapper{
        text-align: center;
        margin: -12px 20px 0;
    }
    .cell-button{
        padding-top: 30px;
    }
    /* vertical line styles */
    .vertical-line{
        position: absolute;
        z-index: 2;
    }
    .vertical-subline{
        height: 35px;
        width: 2px;
        display: inline-block;
        margin-top: -15px;
        margin-left: -2px;
    }
    .red-vertical-subline{
        height: 35px;
        width: 2px;
        margin-top: -15px;
        margin-left: -2px;
        display: inline-block;
    }
    .horizontal-subline{
        height: 2px;
        width: 50px;
        display: inline-block;
    }
    .v-line-point{
        display: inline-block;
        vertical-align: bottom;
        margin-bottom: -5px;
    }
    .v-line-header{
        display: inline-block;
        cursor: pointer;
        vertical-align: bottom;
        margin-bottom: -3px;
        margin-left: 10px;
    }
    .v-line-button-wrapper{
        margin-top: -18px;
        margin-left: 50px;
    }
</style>

<template lang="pug">
    div.line-wrapper(v-bind:class="{'active': this.service_state.selected}")
        div.horizontal-line(v-bind:class="{'cell-button': service_state.type == 1}" v-if="service_state.horizontal")
            div.h-line-header(v-if="service_state.type == 0" v-on:click="click") {{ service_state.name }}
            div.line
            div.red-line.color3(v-bind:style="{'width': service_state.selected ? '50%' : (selected_order > service_state.order ? '100%' : '0%') }")
            div.h-line-point(v-if="service_state.type == 0")
                div.line-point.color3(v-on:click="click")
                    div.line-point-active(v-if="service_state.selected")
            div.h-line-button-wrapper(v-if="service_state.type == 1")
                div.line-button(v-bind:style="{'background-color': color_or_object(service_state.color)}" v-on:click="click")
                    i(v-bind:class="['fa', service_state.icon]")
                    | &nbsp;{{ service_state.name }}
        div.vertical-line(v-else v-bind:style="{'margin-top': (20+vertical_order*35)+'px'}")
            div.vertical-subline
            div.red-vertical-subline.color3(v-if="next_vertical_ids.indexOf(selected_id) !== -1")
            div.horizontal-subline(v-bind:class="{'active': service_state.selected}")
            div.v-line-point(v-if="service_state.type == 0")
                div.line-point.color3(v-on:click="click")
                    div.line-point-active(v-if="service_state.selected")
            div.v-line-header(v-if="service_state.type == 0" v-on:click="click") {{ service_state.name }}
            div.v-line-button-wrapper(v-if="service_state.type == 1")
                div.line-button(v-bind:style="{'background-color': color_or_object(service_state.color)}" v-on:click="click")
                    i(v-bind:class="['fa', service_state.icon]")
                    | &nbsp;{{ service_state.name }}
</template>

<script>
export default {
    props: ['service_state', 'selected_order', 'vertical_order', 'next_vertical_ids', 'selected_id'],
    data: function () {
        return {
        }
    },
    methods: {
        click(){
            this.$emit('activate', this.service_state);
        },
        color_or_object(i){
            if (typeof i === 'object'){
                return i.hex;
            } else {
                return i;
            }
        },
    },
}
</script>