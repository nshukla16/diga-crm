<template lang="pug">
    section.h-100(style="padding:0 10px 0 10px;")
        div.table-title(style="text-align:center") {{ title }}
        div.table-subtitle(style="text-align:center; color: #666666;") {{ use_range }}
        table.text-center.h-100.w-100(v-if="widget.loading")
            tr
                td
                    div.loader.sm-loader
        template(v-else)
            table-table(:rows="rows.slice(0, 5)", :colors="colors")
            table(style="width: 100%;")
                tr
                    td
                        span(v-text="$t('dashboard.quantity') + ': '")
                        span(v-text="rows.length")
                    td(style="text-align: right;")
                        button.btn-diga.btn-sm(v-text="$t('dashboard.check_all')", v-on:click="full_list()")
</template>

<script>
import table_table from './table-table.stage.vue';

export default {
    props: ['widget'],
    components: {
        'table-table': table_table,
    },
    data() {
        return {

        }
    },
    methods: {
        full_list(){
            this.$emit('popup_table', this.rows, this.title, this.colors);
        },
    },
    computed: {
        rows(){
            return this.$store.getters['stage/widget'](this.widget.id).series;
        },
        title(){
            // return this.$store.getters['stage/getChartTitle'](this.widget.id);
            return this.$root.getChartTitle(this.widget);
        },
        colors(){
            return {
                'color1': this.widget.color1,
                'color2': this.widget.color2,
                'color3': this.widget.color3,
                'color4': this.widget.color4,
            }
        },
        use_range(){
            return this.$store.getters['stage/w_use_range'] ? this.$root.$t('dashboard.for_services_created_in_range') : '';
        },
    },
}

</script>