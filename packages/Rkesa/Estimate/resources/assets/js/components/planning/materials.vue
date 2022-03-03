<template lang="pug">
div.diga-container.p-4
    h2 {{ $t('estimate.Material_information') }}
    div.table-responsive
        table.table.table-striped(style='width: 100%;')
            thead
                tr
                    td(colspan='2') {{ $t('estimate.Material') }}
                    td {{ $t('estimate.Price') }}
                    td(style='width:100px;') {{ $t('estimate.Quantity') }}
                    td {{ $t('estimate.Total') }}
            tbody
                tr(v-for='mm in materials')
                    td
                        template(v-if='mm.search')
                            v-select(v-model='mm.name', :debounce='250', :on-search='get_material_options', :on-change='material_select(mm)', :options='tmp_materials', :placeholder="$t('estimate.Enter_title')")
                                template(slot="no-options") {{ $t('template.No_matching_options') }}
                        span(v-else='') {{ mm.name }}
                    td(style='width: 38px;')
                        button.btn.red(type='button', style='float: right;', v-on:click='delete_material(mm)')
                            i.fa.fa-times
                    td {{ mm.price }}
                    td
                        input.form-control(type='number', v-model='mm.quantity', style='width:100%;')
                    td {{ round10(mm.price*mm.quantity) }} {{ $root.current_currency.symbol }}
                tr
                    td(style='font-weight: bold;text-align: right;', colspan='4') {{ $t('estimate.TOTAL') }}:
                    td {{ total_material() }} {{ $root.current_currency.symbol }}
                tr
                    td(colspan='6')
                        button.btn.green(type='button', v-on:click='add_material()') {{ $t('template.Add') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['currentEstimate', 'lines'],
    data(){
        return {
            materials: [],
            tmp_materials: [],
            all_materials: [],
        }
    },
    created(){
        if (this.currentEstimate.estimate_materials !== null && this.currentEstimate.estimate_materials.length > 0){
            this.currentEstimate.estimate_materials.forEach(m => {
                let material = {
                    id: m.id,
                    name: m.resource.name,
                    search: false,
                    price: m.price,
                    quantity: m.quantity,
                    resource_id: m.resource_id,
                };
                this.materials.push(material);
            });
        }
        this.$emit('updateMaterials', this.materials);
        this.get_all_materials();

        if (this.materials.length == 0){
            var ficha_materials = this.eject_material_from_fichas(this.lines);
            ficha_materials.forEach(m => {
                let material = {
                    id: null,
                    name: m.name,
                    search: false,
                    price: m.price,
                    quantity: m.quantity,
                    resource_id: m.id,
                };
                this.materials.push(material);
            });
        }        
    },    
    methods:{
        add_material(){            
            let $material = {
                id: null,
                name: null,
                search: true,
                price: 0,
                quantity: 0,
                resource_id: null,
            };
            this.materials.splice(this.materials.length, 0, $material);
        },
        delete_material(material){
            let $i = this.materials.indexOf(material);
            this.materials.splice($i, 1);
            this.$emit('updateMaterials', this.materials);
        },
        get_all_materials(){
            this.$http.get('/api/ficha_resources').then(res => {
                this.all_materials = res.data;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            })
        },
        get_material_options(search, loading) {
            loading(true);
            this.$http.get('/api/ficha_resources?search=' + search + '&type=1').then(res => {
                var processedData = [];
                res.data.forEach(function(i){
                    processedData.push({'label': i.name, 'value': i.id, 'material': i});
                });
                this.tmp_materials = [];
                processedData.forEach(d => {
                    if (!this.materials.some(m => m.resource_id === d.value)){
                        this.tmp_materials.push(d);
                    }
                });
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            })
        },
        material_select(val){            
            if (typeof val.name === 'object' && val.name !== null){
                val.price = val.name.material.price;
                val.id = null;
                val.resource_id = val.name.material.id;
                val.name = val.name.material.name;
                val.search = false;
            }
            this.$emit('updateMaterials', this.materials);
        },
        round10(num){
            return Math.round(num * 100) / 100;
        },
        total_material(){
            let p = 0;
            let $this = this;
            this.materials.forEach(function(mm){
                p += $this.round10(mm.price * mm.quantity);
            });
            let result = this.round10(p);
            this.$emit('total', result);
            return result;
        },
        eject_material_from_fichas(lines_d){
            var ficha_materials = [];
            if (lines_d && lines_d.length > 0){                
                lines_d.forEach(l => {
                    //if (l.lineable_type === "\App\EstimateLineFicha"){
                        
                        if (l.materials && l.materials.list && l.materials.list.length > 0){
                            l.materials.list.forEach(m => {
                                ficha_materials.push({ 
                                    id: m.resource_id, 
                                    quantity: m.quantity,
                                    name: m.name,
                                    price: m.price,
                                    });
                            });
                        }
                        var child_resources = this.eject_material_from_fichas(l.children);
                        child_resources.forEach(c =>{
                            ficha_materials.push(c);
                        });                        
                    //}
                });
            }
            return ficha_materials;
        },
    },
    computed:{

    }
}
</script>