<style>
    #estimate_new_table td{
        border-right: 1px solid #CCC;
        border-bottom: 1px solid #CCC;
        height: 22px;
        empty-cells: show;
        line-height: 21px;
        /*background-color: #FFF;*/
        vertical-align: top;
        overflow: hidden;
        outline-width: 0;
        background-clip: padding-box;
        padding: 5px;
    }
    #estimate_new_table textarea{
        border: 1px solid #a9a9a9;
    }
    #estimate_new_table select{
        height: 25px;
        background-color: white;
    }
    #estimate_new_table .category{
        font-weight: bold;
        background-color: #ececec;
    }
    #estimate_new_table .actions{
        float: none;
        text-align: center;
        vertical-align: middle;
    }
    #estimate_new_table .actions i{
        cursor: pointer;
    }
    #estimate_new_table .change-order{
        height: 10px;
        display: block;
    }
    #estimate_new_table .separator,
    #estimate_new_table .total{
        text-align: center;
    }
    #estimate_new_table .active td{
        background-color: #eee;
    }
    #estimate_new_table thead td{
        padding: 5px;
    }
    #estimate_new_table input{
        padding: 0;
    }
    #estimate_new_table textarea {
        width: 100%;
        -webkit-box-sizing: border-box; /* <=iOS4, <= Android  2.3 */
        -moz-box-sizing: border-box; /* FF1+ */
        box-sizing: border-box; /* Chrome, IE8, Opera, Safari 5.1*/
    }
    #estimate_new_table tr:first-child td {
        border-top: 1px solid #CCC;
    }
    #estimate_new_table td:first-of-type{
        border-left: 1px solid #CCC;
    }
    .buttons-for-table{
        margin-top: 5px;
        text-align: center;
    }
    /* WHY NOT WORKING??? */
    .fade-enter-active, .fade-leave-active {
        transition: opacity 1.5s
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
        opacity: 0
    }
    .table.table-light > tbody > tr > td {
        color: #333333;
    }
    .attention td{
        background-color: #b3b3b3;
    }
</style>

<template lang="pug">
    #estimate-form.bg-grey-steel(v-if="currentEstimate")
        .row
            .col-12
                // END PAGE HEADER
                .col-12
                    .row.mb-3
                        section.col-12
                            div.diga-container.p-4
                                    h2 {{ $t('estimate.General_information') }}
                                    .row
                                        .col-7
                                            .row
                                                .form-group.col-4
                                                    .row
                                                        label.col-6.control-label(style="margin-top: 5px;") {{ $t('estimate.estimate_number') }}
                                                        .input-group.col-6(style='float: left; min-width: 80px;')
                                                            label.form-control
                                                                | {{ $root.service_number(service) + (estimate_fork != null ? ' (' + estimate_fork.name + ')' : '') }}
                                                .col-8
                                                    .row
                                                        .form-group.col-4(v-if='currentEstimate.revision != null')
                                                            .row
                                                                label.col-8.control-label(style='margin-top: 5px; float: left') {{ $t('estimate.Revision') }}
                                                                .input-group.col-4(style='float: left')
                                                                    label.form-control {{ currentEstimate.revision }}
                                                        .form-group.col-4(v-if='currentEstimate.option != null')
                                                            .row
                                                                label.col-8.control-label(style='margin-top: 5px; float: left')  {{ $t('estimate.Option') }}
                                                                .input-group.col-4(style='float: left')
                                                                    label.form-control {{ currentEstimate.option }}
                                        .col-5
                                            .row
                                                .form-group.col-6
                                                    .row(v-if='id == null')
                                                        label.control-label.col-5(style='text-align: right; max-width: 100%; margin-top: 5px; float: left') {{ $t('estimate.A_base_de') }}
                                                        .input-group.col-7(style='float: left; max-width: 90%;')
                                                            v-select(style="width: 100%;", :debounce='250', :on-search='get_base_options', :on-change='base_select', :options='bases', :placeholder="$t('estimate.Choose_estimate')")
                                                                template(slot="no-options") {{ $t('template.No_matching_options') }}
                                                .form-group.col-6(style='padding-right:15px;')
                                                    .row
                                                        label.col-8.control-label(style="margin-top: 5px;") {{ $t('estimate.Valor_adicional') }}
                                                        .col-4.input-group
                                                            input.form-control.additional-price(v-model='currentEstimate.additional_price', type='number', min="0")
                                    .form-group(style='clear: both;padding-top: 10px;')
                                        .row
                                            label.col-2.control-label {{ $t('estimate.Assunto') }}
                                            .col-10.input-group
                                                textarea.form-control.estimate-subject(v-model='currentEstimate.subject', rows='2', style='resize: vertical')
                                    .form-group
                                        .row
                                            label.col-2.control-label(style="margin-top: 5px;") {{ $t('estimate.Obra_situada_em') }}
                                            .col-10.input-group
                                                label.form-control {{ service.address }}
                                    .form-group(v-if="service.region")
                                        .row
                                            label.col-2.control-label(style="margin-top: 5px;") {{ $t('estimate.ARU') }}
                                            .col-10.input-group
                                                label.form-control {{ service.region.zone }}
                .col-12
                    div.diga-container.p-4
                        div.table-responsive
                            table#estimate_new_table.table.table-hover.table-light(style='width: 100%; border: none;')
                                thead
                                    tr
                                        td Nº
                                        td {{ $t('estimate.Description') }}
                                        td {{ $t('estimate.Un') }}
                                        td {{ $t('estimate.PU') }}
                                        td {{ $t('estimate.Quant') }}
                                        td {{ $t('estimate.Valor') }}
                                        td {{ $t('estimate.Comments') }}
                                        td(colspan='4') {{ $t('estimate.Actions') }}
                                tfoot
                                    tr.total.category
                                        td(colspan='6', style='text-align: right;') {{ $t('estimate.TOTAL_DO_ORCAMENTO') }}
                                        td(colspan='5') {{ currentEstimate.price }} {{ $root.current_currency.symbol }}
                                    tr.total.category(v-if="currentEstimate.discount != null && currentEstimate.discount != 0")
                                        td(colspan='6', style='text-align: right;') {{ $t('estimate.The_total_value_of_the_above_described_commercially_discounted_jobs_is', {percent: currentEstimate.discount}) }}
                                        td(colspan='5') {{ round10(multiply(currentEstimate.price,(100-currentEstimate.discount)/100)) }} {{ $root.current_currency.symbol }}
                                    tr
                                        td(colspan='24')
                                            .buttons-for-table
                                                .col-md-12
                                                    div(style='float: left;')
                                                        button.btn.green-seagreen.add_category(v-on:click='add_category_row()', type='button', style='margin-right: 10px')
                                                            i.fa.fa-plus
                                                            |  {{ $t('estimate.Capitulo') }}
                                                        button.btn.green-meadow.add_separator(v-on:click='add_separator_row()', type='button', style='margin-right: 10px')
                                                            i.fa.fa-plus
                                                            |  {{ $t('estimate.Separador') }}
                                                    div(style='float: right;')
                                                        button.btn.green-seagreen.add_subcategory(v-on:click='add_subcategory_row()', v-if='this.currentEstimate.lines.length!=0', type='button', style='margin-right: 10px')
                                                            i.fa.fa-plus
                                                            |  {{ $t('estimate.Subcapitulo') }}
                                                        button.btn.green-meadow.add_dataline(v-on:click='add_data_row()', v-if='this.currentEstimate.lines.length!=0', type='button', style='margin-right: 10px')
                                                            i.fa.fa-plus
                                                            |  {{ $t('estimate.Artigo') }}
                                                        button.btn.green-meadow.add_fichaline(v-on:click='add_ficha_row()', v-if='this.currentEstimate.lines.length!=0', type='button', style='margin-right: 10px')
                                                            i.fa.fa-plus
                                                            |  {{ $t('estimate.Ficha') }}
                                tbody
                                    // FIRST LEVEL
                                    template(v-for='(line1, index1) in currentEstimate.lines')
                                        template(v-if="is_category_or_subcategory(line1)")
                                            tr.category(@mousedown="row_mouse_down($event,line1)", @mouseup="row_mouse_up($event,line1)", @dblclick="select_row(line1)")
                                                td {{ line1.line_number }}
                                                td(colspan='6')
                                                    template(v-if='line1.active')
                                                        input(v-model='line1.category_name', type='text', style='width: 100%;')
                                                    template(v-else)
                                                        | {{ line1.category_name }}
                                                td.actions
                                                    i.fa.fa-pencil(v-on:click='select_row(line1)')
                                                td.actions
                                                    i.change-order.fa.fa-sort-up(v-on:click='to_up(line1)')
                                                    i.change-order.fa.fa-sort-desc(v-on:click='to_down(line1)')
                                                td.actions
                                                    i.fa.fa-times(v-on:click='remove_row(line1)')
                                                td.actions
                                                    i.fa.fa.fa-level-down(v-on:click='add_row(line1)')
                                            tr(@mousedown='row_mouse_down($event,line1)', @mouseup='row_mouse_up($event,line1)', v-if='line1.show_insert_after')
                                                td(colspan='13')
                                                    .buttons-for-table
                                                        .col-md-12
                                                            button.btn.green-seagreen.add_subcategory(v-on:click='add_subcategory_row(line1)', type='button', style='margin-right: 10px')
                                                                i.fa.fa-plus
                                                                |  {{ $t('estimate.Subcapitulo') }}
                                                            button.btn.green-meadow.add_separator(v-on:click='add_separator_row(line1)', type='button', style='margin-right: 10px')
                                                                i.fa.fa-plus
                                                                |  {{ $t('estimate.Separador') }}
                                                            button.btn.green-meadow.add_dataline(v-on:click='add_data_row(line1)', type='button', style='margin-right: 10px')
                                                                i.fa.fa-plus
                                                                |  {{ $t('estimate.Artigo') }}
                                                            button.btn.green-meadow.add_fichaline(v-on:click='add_ficha_row(line1)', type='button', style='margin-right: 10px')
                                                                i.fa.fa-plus
                                                                |  {{ $t('estimate.Ficha') }}
                                        template(v-if="is_separator(line1)")
                                            tr(@mousedown='row_mouse_down($event,line1)', @mouseup='row_mouse_up($event,line1)', @dblclick='select_row(line1)')
                                                td.separator(colspan='7')
                                                    template(v-if='line1.active')
                                                        input(v-model='line1.separator_name', type='text', style='width: 100%;')
                                                    template(v-else)
                                                        | {{ line1.separator_name }}
                                                td.actions
                                                    i.fa.fa-pencil(v-on:click='select_row(line1)')
                                                td.actions
                                                    i.change-order.fa.fa-sort-up(v-on:click='to_up(line1)')
                                                    i.change-order.fa.fa-sort-desc(v-on:click='to_down(line1)')
                                                td.actions
                                                    i.fa.fa-times(v-on:click='remove_row(line1)')
                                                td.actions
                                                    i.fa.fa.fa-level-down(v-on:click='add_row(line1)')
                                            tr(@mousedown='row_mouse_down($event,line1)', @mouseup='row_mouse_up($event,line1)', v-if='line1.show_insert_after')
                                                td(colspan='13')
                                                    .buttons-for-table
                                                        .col-md-12
                                                            button.btn.green-seagreen.add_category(v-on:click='add_category_row(line1)', type='button', style='margin-right: 10px')
                                                                i.fa.fa-plus
                                                                |  {{ $t('estimate.Capitulo') }}
                                                            button.btn.green-meadow.add_separator(v-on:click='add_separator_row(line1)', type='button', style='margin-right: 10px')
                                                                i.fa.fa-plus
                                                                |  {{ $t('estimate.Separador') }}
                                        // SECOND LEVEL
                                        template(v-for='(line2, index2) in line1.children')
                                            template(v-if="is_category_or_subcategory(line2)")
                                                tr.category(@mousedown='row_mouse_down($event,line2)', @mouseup='row_mouse_up($event,line2)', @dblclick='select_row(line2)', v-bind:class="{'attention': line2.attention}")
                                                    td {{ line2.line_number }}
                                                    td(colspan='6')
                                                        template(v-if='line2.active')
                                                            input(v-model='line2.category_name', type='text', style='width: 100%;')
                                                        template(v-else)
                                                            | {{ line2.category_name }}
                                                    td.actions
                                                        i.fa.fa-pencil(v-on:click='select_row(line2)')
                                                    td.actions
                                                        i.change-order.fa.fa-sort-up(v-on:click='to_up(line2)')
                                                        i.change-order.fa.fa-sort-desc(v-on:click='to_down(line2)')
                                                    td.actions
                                                        i.fa.fa-times(v-on:click='remove_row(line2)')
                                                    td.actions
                                                        i.fa.fa.fa-level-down(v-on:click='add_row(line2)')
                                                tr(@mousedown='row_mouse_down($event,line2)', @mouseup='row_mouse_up($event,line2)', v-if='line2.show_insert_after')
                                                    td(colspan='13')
                                                        .buttons-for-table
                                                            .col-md-12
                                                                button.btn.green-seagreen.add_subcategory(v-on:click='add_subcategory_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Subcapitulo') }}
                                                                button.btn.green-meadow.add_separator(v-on:click='add_separator_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Separador') }}
                                                                button.btn.green-meadow.add_dataline(v-on:click='add_data_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Artigo') }}
                                                                button.btn.green-meadow.add_fichaline(v-on:click='add_ficha_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Ficha') }}
                                            template(v-if="is_data_or_subdata(line2)")
                                                tr(@mousedown='row_mouse_down($event,line2)', @mouseup='row_mouse_up($event,line2)', @dblclick='select_row(line2)', v-bind:class="{'attention': line2.attention}")
                                                    td {{ line2.line_number }}
                                                    td
                                                        template(v-if='line2.active')
                                                            textarea(v-model='line2.data_description')
                                                        template(v-else)
                                                            | {{ line2.data_description }}
                                                    td
                                                        template(v-if='line2.active')
                                                            select(v-model='line2.data_measure')
                                                                template(v-for='unit in units')
                                                                    option(:value='unit.id') {{ unit.measure }}
                                                        div(v-else, v-html='unitize(line2.data_measure)')
                                                    td
                                                        template(v-if='line2.active')
                                                            input(v-model='line2.data_ppu', type='number', min='0', style='width: 100%;', v-on:change='calculate_price(line2)')
                                                        template(v-else)
                                                            | {{ data_with_additional(line2.data_ppu) }}
                                                    td
                                                        template(v-if='line2.active')
                                                            input(v-model='line2.data_quantity', type='number', min='0', style='width: 100%;', v-on:change='calculate_price(line2)')
                                                        template(v-else)
                                                            | {{ zero_and_null_checker(line2.data_quantity) }}
                                                    td {{ zero_and_null_checker(line2.data_price) }}
                                                    td
                                                        template(v-if='line2.active')
                                                            textarea(v-model='line2.data_note')
                                                        template(v-else)
                                                            | {{ line2.data_note }}
                                                    td.actions
                                                        i.fa.fa-pencil(v-on:click='select_row(line2)')
                                                    td.actions
                                                        i.change-order.fa.fa-sort-up(v-on:click='to_up(line2)')
                                                        i.change-order.fa.fa-sort-desc(v-on:click='to_down(line2)')
                                                    td.actions
                                                        i.fa.fa-times(v-on:click='remove_row(line2)')
                                                    td.actions
                                                        i.fa.fa.fa-level-down(v-on:click='add_row(line2)')
                                                tr(@mousedown='row_mouse_down($event,line2)', @mouseup='row_mouse_up($event,line2)', v-if='line2.show_insert_after')
                                                    td(colspan='13')
                                                        .buttons-for-table
                                                            .col-md-12
                                                                button.btn.green-seagreen.add_subcategory(v-on:click='add_subcategory_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Subcapitulo') }}
                                                                button.btn.green-meadow.add_separator(v-on:click='add_separator_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Separador') }}
                                                                button.btn.green-meadow.add_dataline(v-on:click='add_data_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Artigo') }}
                                                                button.btn.green-meadow.add_datasubline(v-on:click='add_subdata_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Subartigo') }}
                                                                button.btn.green-meadow.add_fichaline(v-on:click='add_ficha_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Ficha') }}

                                            template(v-if="is_ficha(line2)")
                                                tr(@mousedown='row_mouse_down($event,line2)', @mouseup='row_mouse_up($event,line2)', @dblclick='select_row(line2)', v-bind:class="{'attention': line2.attention}")
                                                    td {{ line2.line_number }}
                                                    td {{ line2.ficha_description }}
                                                    td(v-html='unitize(line2.ficha_measure)')
                                                    td {{ data_with_additional(line2.ficha_ppu) }}
                                                    td
                                                        template(v-if='line2.active')
                                                            input(v-model='line2.ficha_quantity', type='number', min='0', style='width: 100%;', v-on:change='calculate_price(line2)')
                                                        template(v-else)
                                                            | {{ zero_and_null_checker(line2.ficha_quantity) }}
                                                    td {{ zero_and_null_checker(line2.ficha_price)  }}
                                                    td
                                                        template(v-if='line2.active')
                                                            textarea(v-model='line2.ficha_note')
                                                        template(v-else)
                                                            | {{ line2.ficha_note }}
                                                        i.fa.fa-pencil(style='cursor: pointer;', v-on:click='select_row(line2, false)')
                                                    td.actions
                                                        i.fa.fa-pencil(v-on:click='select_row(line2)')
                                                    td.actions
                                                        i.change-order.fa.fa-sort-up(v-on:click='to_up(line2)')
                                                        i.change-order.fa.fa-sort-desc(v-on:click='to_down(line2)')
                                                    td.actions
                                                        i.fa.fa-times(v-on:click='remove_row(line2)')
                                                    td.actions
                                                        i.fa.fa.fa-level-down(v-on:click='add_row(line2)')
                                                tr(@mousedown='row_mouse_down($event,line2)', @mouseup='row_mouse_up($event,line2)', v-if='line2.show_insert_after')
                                                    td(colspan='13')
                                                        .buttons-for-table
                                                            .col-md-12
                                                                button.btn.green-seagreen.add_subcategory(v-on:click='add_subcategory_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Subcapitulo') }}
                                                                button.btn.green-meadow.add_separator(v-on:click='add_separator_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Separador') }}
                                                                button.btn.green-meadow.add_dataline(v-on:click='add_data_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Artigo') }}
                                                                button.btn.green-meadow.add_fichaline(v-on:click='add_ficha_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Ficha') }}
                                            template(v-if="is_separator(line2)")
                                                tr(@mousedown='row_mouse_down($event,line2)', @mouseup='row_mouse_up($event,line2)', @dblclick='select_row(line2)')
                                                    td.separator(colspan='7')
                                                        template(v-if='line2.active')
                                                            input(v-model='line2.separator_name', type='text', style='width: 100%;')
                                                        template(v-else)
                                                            | {{ line2.separator_name }}
                                                    td.actions
                                                        i.fa.fa-pencil(v-on:click='select_row(line2)')
                                                    td.actions
                                                        i.change-order.fa.fa-sort-up(v-on:click='to_up(line2)')
                                                        i.change-order.fa.fa-sort-desc(v-on:click='to_down(line2)')
                                                    td.actions
                                                        i.fa.fa-times(v-on:click='remove_row(line2)')
                                                    td.actions
                                                        i.fa.fa.fa-level-down(v-on:click='add_row(line2)')
                                                tr(@mousedown='row_mouse_down($event,line2)', @mouseup='row_mouse_up($event,line2)', v-if='line2.show_insert_after')
                                                    td(colspan='13')
                                                        .buttons-for-table
                                                            .col-md-12
                                                                button.btn.green-seagreen.add_subcategory(v-on:click='add_subcategory_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Subcapitulo') }}
                                                                button.btn.green-meadow.add_separator(v-on:click='add_separator_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Separador') }}
                                                                button.btn.green-meadow.add_dataline(v-on:click='add_data_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Artigo') }}
                                                                button.btn.green-meadow.add_fichaline(v-on:click='add_ficha_row(line2)', type='button', style='margin-right: 10px')
                                                                    i.fa.fa-plus
                                                                    |  {{ $t('estimate.Ficha') }}
                                            // THIRD LEVEL
                                            template(v-for='(line3, index3) in line2.children')
                                                template(v-if="is_data_or_subdata(line3)")
                                                    tr(@mousedown='row_mouse_down($event,line3)', @mouseup='row_mouse_up($event,line3)', @dblclick='select_row(line3)', v-bind:class="{'attention': line3.attention}")
                                                        td {{ line3.line_number }}
                                                        td
                                                            template(v-if='line3.active')
                                                                textarea(v-model='line3.data_description')
                                                            template(v-else)
                                                                | {{ line3.data_description }}
                                                        td
                                                            template(v-if='line3.active')
                                                                select(v-model='line3.data_measure')
                                                                    template(v-for='unit in units')
                                                                        option(:value='unit.id') {{ unit.measure }}
                                                            div(v-else, v-html='unitize(line3.data_measure)')
                                                        td
                                                            template(v-if='line3.active')
                                                                input(v-model='line3.data_ppu', type='number', min='0', style='width: 100%;', v-on:change='calculate_price(line3)')
                                                            template(v-else)
                                                                | {{ data_with_additional(line3.data_ppu) }}
                                                        td
                                                            template(v-if='line3.active')
                                                                input(v-model='line3.data_quantity', type='number', min='0', style='width: 100%;', v-on:change='calculate_price(line3)')
                                                            template(v-else)
                                                                | {{ zero_and_null_checker(line3.data_quantity) }}
                                                        td {{ zero_and_null_checker(line3.data_price) }}
                                                        td
                                                            template(v-if='line3.active')
                                                                textarea(v-model='line3.data_note')
                                                            template(v-else)
                                                                | {{ line3.data_note }}
                                                        td.actions
                                                            i.fa.fa-pencil(v-on:click='select_row(line3)')
                                                        td.actions
                                                            i.change-order.fa.fa-sort-up(v-on:click='to_up(line3)')
                                                            i.change-order.fa.fa-sort-desc(v-on:click='to_down(line3)')
                                                        td.actions
                                                            i.fa.fa-times(v-on:click='remove_row(line3)')
                                                        td.actions
                                                            i.fa.fa.fa-level-down(v-on:click='add_row(line3)')
                                                    tr(@mousedown='row_mouse_down($event,line3)', @mouseup='row_mouse_up($event,line3)', v-if='line3.show_insert_after')
                                                        td(colspan='13')
                                                            .buttons-for-table
                                                                .col-md-12
                                                                    button.btn.green-seagreen.add_subcategory(v-on:click='add_subcategory_row(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Subcapitulo') }}
                                                                    button.btn.green-meadow.add_separator(v-on:click='add_separator_row(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Separador') }}
                                                                    button.btn.green-meadow.add_dataline(v-on:click='add_data_row(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Artigo') }}
                                                                    button.btn.green-meadow.add_datasubline(v-on:click='add_subdata_row(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Subartigo') }}
                                                                    button.btn.green-meadow.add_fichaline(v-on:click='add_ficha_row(line3)', v-if='is_data(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Ficha') }}
                                                template(v-if="is_ficha(line3)")
                                                    tr(@mousedown='row_mouse_down($event,line3)', @mouseup='row_mouse_up($event,line3)', @dblclick='select_row(line3)', v-bind:class="{'attention': line3.attention}")
                                                        td {{ line3.line_number }}
                                                        td {{ line3.ficha_description }}
                                                        td(v-html='unitize(line3.ficha_measure)')
                                                        td {{ data_with_additional(line3.ficha_ppu) }}
                                                        td
                                                            template(v-if='line3.active')
                                                                input(v-model='line3.ficha_quantity', type='number', min='0', style='width: 100%;', v-on:change='calculate_price(line3)')
                                                            template(v-else)
                                                                | {{ zero_and_null_checker(line3.ficha_quantity) }}
                                                        td {{ zero_and_null_checker(line3.ficha_price) }}
                                                        td
                                                            i.fa.fa-pencil(style='cursor: pointer;float:right;', v-on:click='select_row(line3, false)')
                                                            template(v-if='line3.active')
                                                                textarea(v-model='line3.ficha_note')
                                                            template(v-else)
                                                                | {{ line3.ficha_note }}
                                                        td.actions
                                                            i.fa.fa-pencil(v-on:click='select_row(line3)')
                                                        td.actions
                                                            i.change-order.fa.fa-sort-up(v-on:click='to_up(line3)')
                                                            i.change-order.fa.fa-sort-desc(v-on:click='to_down(line3)')
                                                        td.actions
                                                            i.fa.fa-times(v-on:click='remove_row(line3)')
                                                        td.actions
                                                            i.fa.fa.fa-level-down(v-on:click='add_row(line3)')
                                                    tr(@mousedown='row_mouse_down($event,line3)', @mouseup='row_mouse_up($event,line3)', v-if='line3.show_insert_after')
                                                        td(colspan='13')
                                                            .buttons-for-table
                                                                .col-md-12
                                                                    button.btn.green-seagreen.add_subcategory(v-on:click='add_subcategory_row(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Subcapitulo') }}
                                                                    button.btn.green-meadow.add_separator(v-on:click='add_separator_row(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Separador') }}
                                                                    button.btn.green-meadow.add_dataline(v-on:click='add_data_row(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Artigo') }}
                                                                    button.btn.green-meadow.add_fichaline(v-on:click='add_ficha_row(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Ficha') }}
                                                template(v-if="is_separator(line3)")
                                                    tr(@mousedown='row_mouse_down($event,line3)', @mouseup='row_mouse_up($event,line3)', @dblclick='select_row(line3)')
                                                        td.separator(colspan='7')
                                                            template(v-if='line3.active')
                                                                input(v-model='line3.separator_name', type='text', style='width: 100%;')
                                                            template(v-else)
                                                                | {{ line3.separator_name }}
                                                        td.actions
                                                            i.fa.fa-pencil(v-on:click='select_row(line3)')
                                                        td.actions
                                                            i.change-order.fa.fa-sort-up(v-on:click='to_up(line3)')
                                                            i.change-order.fa.fa-sort-desc(v-on:click='to_down(line3)')
                                                        td.actions
                                                            i.fa.fa-times(v-on:click='remove_row(line3)')
                                                        td.actions
                                                            i.fa.fa.fa-level-down(v-on:click='add_row(line3)')
                                                    tr(@mousedown='row_mouse_down($event,line3)', @mouseup='row_mouse_up($event,line3)', v-if='line3.show_insert_after')
                                                        td(colspan='13')
                                                            .buttons-for-table
                                                                .col-md-12
                                                                    button.btn.green-seagreen.add_subcategory(v-on:click='add_subcategory_row(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Subcapitulo') }}
                                                                    button.btn.green-meadow.add_separator(v-on:click='add_separator_row(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Separador') }}
                                                                    button.btn.green-meadow.add_dataline(v-on:click='add_data_row(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Artigo') }}
                                                                    button.btn.green-meadow.add_datasubline(v-on:click='add_subdata_row(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Subartigo') }}
                                                                    button.btn.green-meadow.add_fichaline(v-on:click='add_ficha_row(line3)', type='button', style='margin-right: 10px')
                                                                        i.fa.fa-plus
                                                                        |  {{ $t('estimate.Ficha') }}
                                                // FOURTH LEVEL
                                                template(v-for='(line4, index4) in line3.children')
                                                    template(v-if="is_data_or_subdata(line4)")
                                                        tr(@mousedown='row_mouse_down($event,line4)', @mouseup='row_mouse_up($event,line4)', @dblclick='select_row(line4)', v-bind:class="{'attention': line4.attention}")
                                                            td {{ line4.line_number }}
                                                            td
                                                                template(v-if='line4.active')
                                                                    textarea(v-model='line4.data_description')
                                                                template(v-else)
                                                                    | {{ line4.data_description }}
                                                            td
                                                                template(v-if='line4.active')
                                                                    select(v-model='line4.data_measure')
                                                                        template(v-for='unit in units')
                                                                            option(:value='unit.id') {{ unit.measure }}
                                                                div(v-else, v-html='unitize(line4.data_measure)')
                                                            td
                                                                template(v-if='line4.active')
                                                                    input(v-model='line4.data_ppu', type='number', min='0', style='width: 100%;', v-on:change='calculate_price(line4)')
                                                                template(v-else)
                                                                    | {{ data_with_additional(line4.data_ppu)  }}
                                                            td
                                                                template(v-if='line4.active')
                                                                    input(v-model='line4.data_quantity', type='number', min='0', style='width: 100%;', v-on:change='calculate_price(line4)')
                                                                template(v-else)
                                                                    | {{ zero_and_null_checker(line4.data_quantity) }}
                                                            td {{ zero_and_null_checker(line4.data_price)  }}
                                                            td
                                                                template(v-if='line4.active')
                                                                    textarea(v-model='line4.data_note')
                                                                template(v-else)
                                                                    | {{ line4.data_note }}
                                                            td.actions
                                                                i.fa.fa-pencil(v-on:click='select_row(line4)')
                                                            td.actions
                                                                i.change-order.fa.fa-sort-up(v-on:click='to_up(line4)')
                                                                i.change-order.fa.fa-sort-desc(v-on:click='to_down(line4)')
                                                            td.actions
                                                                i.fa.fa-times(v-on:click='remove_row(line4)')
                                                            td.actions
                                                                i.fa.fa.fa-level-down(v-on:click='add_row(line4)')
                                                        tr(@mousedown='row_mouse_down($event,line4)', @mouseup='row_mouse_up($event,line4)', v-if='line4.show_insert_after')
                                                            td(colspan='13')
                                                                .buttons-for-table
                                                                    .col-md-12
                                                                        button.btn.green-seagreen.add_subcategory(v-on:click='add_subcategory_row(line4)', type='button', style='margin-right: 10px')
                                                                            i.fa.fa-plus
                                                                            |  {{ $t('estimate.Subcapitulo') }}
                                                                        button.btn.green-meadow.add_separator(v-on:click='add_separator_row(line4)', type='button', style='margin-right: 10px')
                                                                            i.fa.fa-plus
                                                                            |  {{ $t('estimate.Separador') }}
                                                                        button.btn.green-meadow.add_dataline(v-on:click='add_data_row(line4)', type='button', style='margin-right: 10px')
                                                                            i.fa.fa-plus
                                                                            |  {{ $t('estimate.Artigo') }}
                                                                        button.btn.green-meadow.add_datasubline(v-on:click='add_subdata_row(line4)', type='button', style='margin-right: 10px')
                                                                            i.fa.fa-plus
                                                                            |  {{ $t('estimate.Subartigo') }}
                                                    template(v-if="is_separator(line4)")
                                                        tr(@mousedown='row_mouse_down($event,line4)', @mouseup='row_mouse_up($event,line4)', @dblclick='select_row(line4)')
                                                            td.separator(colspan='7')
                                                                template(v-if='line4.active')
                                                                    input(v-model='line4.separator_name', type='text', style='width: 100%;')
                                                                template(v-else)
                                                                    | {{ line4.separator_name }}
                                                            td.actions
                                                                i.fa.fa-pencil(v-on:click='select_row(line4)')
                                                            td.actions
                                                                i.change-order.fa.fa-sort-up(v-on:click='to_up(line4)')
                                                                i.change-order.fa.fa-sort-desc(v-on:click='to_down(line4)')
                                                            td.actions
                                                                i.fa.fa-times(v-on:click='remove_row(line4)')
                                                            td.actions
                                                                i.fa.fa.fa-level-down(v-on:click='add_row(line4)')
                                                        tr(@mousedown='row_mouse_down($event,line4)', @mouseup='row_mouse_up($event,line4)', v-if='line4.show_insert_after')
                                                            td(colspan='13')
                                                                .buttons-for-table
                                                                    .col-md-12
                                                                        button.btn.green-seagreen.add_subcategory(v-on:click='add_subcategory_row(line4)', type='button', style='margin-right: 10px')
                                                                            i.fa.fa-plus
                                                                            |  {{ $t('estimate.Subcapitulo') }}
                                                                        button.btn.green-meadow.add_separator(v-on:click='add_separator_row(line4)', type='button', style='margin-right: 10px')
                                                                            i.fa.fa-plus
                                                                            |  {{ $t('estimate.Separador') }}
                                                                        button.btn.green-meadow.add_dataline(v-on:click='add_data_row(line4)', type='button', style='margin-right: 10px')
                                                                            i.fa.fa-plus
                                                                            |  {{ $t('estimate.Artigo') }}
                                                                        button.btn.green-meadow.add_datasubline(v-on:click='add_subdata_row(line4)', type='button', style='margin-right: 10px')
                                                                            i.fa.fa-plus
                                                                            |  {{ $t('estimate.Subartigo') }}
                                        template(v-if="is_category_or_subcategory(line1)")
                                            tr.total
                                                td(colspan='6') {{ $t('estimate.TOTAL') }}
                                                td(colspan='18') {{ line1.children_total_price }} {{ $root.current_currency.symbol }}
                .col-12.mt-3
                    .row(style='height: auto;')
                        section.col-12.col-md-4.mb-3
                            div.diga-container.p-4
                                    h2 {{ $t('estimate.Other_info') }}
                                    .row.mb-2(v-if='currentEstimate.vat_type != 4')
                                        label.col-6.control-label(style='margin-top: 10px;') {{ $t('estimate.IVA') }}
                                        .col-6
                                            select.form-control(v-model='currentEstimate.vat_type')
                                                option(value='1') {{ $t('estimate.Privado') }}
                                                option(value='2') {{ $t('estimate.Auto_liquidacao') }}
                                                option(value='3') {{ $t('estimate.Empresa') }}
                                                option(value='5') {{ $t('estimate.Intra_community') }}
                                    .row.mb-2
                                        label.col-6.control-label(style="line-height: 34px;") {{ $t('estimate.Auto_liquidacao') }} (%)
                                        .col-6
                                            input.form-control.discount(v-model='currentEstimate.vat_custom', style='min-width: 50px;', type='number', min="0")
                                    template(v-if='currentEstimate.vat_type == 1')
                                        .row.mb-2
                                            .col-6 {{ $t('estimate.Labor') }} (6%)
                                            .col-6(style='min-width: 70px;')
                                                input.form-control(v-model='currentEstimate.vat_maodeobra', style='min-width: 70px;', type="number")
                                        .row.mb-2
                                            .col-6 {{ $t('estimate.Material') }} (23%)
                                            .col-6(style='min-width: 70px;')
                                                input.form-control(v-model='currentEstimate.vat_material', style='min-width: 70px;', type="number")
                                    .row.mb-2(v-if='currentEstimate.vat_type == 4')
                                        label.col-6.control-label {{ $t('estimate.IVA') }}
                                        .col-6.input-group
                                            textarea.form-control.vat(v-model='currentEstimate.vat_text', rows='2', style='resize: vertical')
                                                | Aos valores apresentados acresce IVA à taxa legal em vigor no momento da faturação.
                                    .row.mb-2
                                        label.col-6.control-label(style="line-height: 34px;") {{ $t('estimate.Desconto') }} (%)
                                        .col-6
                                            input.form-control.discount(:disabled="$root.user.can_give_discount === false" v-model='currentEstimate.discount', style='min-width: 50px;', type='number', min="0")
                                    .row
                                        label.col-6.control-label(style="line-height: 34px;") {{ $t('estimate.Prazo') }} ({{ $t('estimate.dias') }})
                                        .col-6
                                            input.form-control.deadline(v-model='currentEstimate.deadline', type='number', style='min-width: 50px;')

                        section.col-12.col-md-4.mb-3
                            div.diga-container.p-4
                                    h2 {{ $t('estimate.Pagamento_de_prestacoes') }}
                                    table.table.table-hover.table-light
                                        thead
                                            tr
                                                th {{ $t('estimate.Percent') }}
                                                th {{ $t('estimate.Description') }}
                                                th {{ $t('client.vat') }}
                                                th {{ $t('estimate.Actions') }}
                                        tbody
                                            tr(v-for='(pay_stage, index) in currentEstimate.estimate_pay_stages')
                                                td
                                                    input.form-control(v-model='pay_stage.percent', min='1', max='100', type='number')
                                                td
                                                    input.form-control(v-model='pay_stage.text', type='text')
                                                td
                                                    select.form-control(v-model='pay_stage.vat_type')
                                                        option(value='1') 0 %
                                                        option(value='2') 6 %
                                                        option(value='3') 23 %
                                                        option(value='4') 6 & 23 %
                                                td
                                                    button.btn.btn-sm.red(v-on:click='remove_pay_stage(index, pay_stage.id)')
                                                        i.fa.fa-times
                                    button.btn.btn-sm.btn-diga(v-on:click='add_pay_stage', style='margin-top: 20px;')
                                        i.fa.fa-plus
                                        |  {{ $t('estimate.Adicionar') }}
                        section.col-12.col-md-4.mb-3
                            div.diga-container.p-4
                                h2 {{ $t('estimate.Changes') }}
                                div(style="height: 300px;overflow-y: scroll;")
                                    table.table.table-hover.table-striped
                                        thead
                                            tr
                                                th {{ $t('estimate.User') }}
                                                th.text-right {{ $t('estimate.Change_date') }}
                                        tbody
                                            tr(v-for='change in currentEstimate.changes')
                                                td {{ usersById[change.user_id] ? usersById[change.user_id].name : $t('template.Unknown') }}
                                                td.text-right {{ change.created_at }}
                                            tr(v-if="currentEstimate.changes.length == 0")
                                                td(colspan=3 style="text-align: center;") {{ $t('estimate.No_changes_yet') }}
                .col-12
                    button.btn.btn-diga(type='button', v-on:click='update_estimate(false)') {{ $t('estimate.Guardar') }}
                    button.btn.green.ml-3(type='button', v-on:click='update_estimate(true)') {{ $t('estimate.Finish') }}
                    button.btn.green.ml-3(v-if="currentEstimate.fork_id == null && id != null && $root.can_do('forks', 'create') != 0" type='button', v-on:click='gen_forks') {{ $t('estimate.Gen_forks') }}
                    router-link.btn.btn-diga.float-right(v-if='service.client_contact_id', :to="{name: this.$root.contact_or_client_show_route(), params: {id: service.client_contact_id }}") {{ $t('estimate.Open_client_card') }}
        my-ficha(v-on:save='save_ficha()', v-on:close='close_ficha()', :mydata='current_ficha')
</template>

<script>
import ficha from "./ficha.vue"
import {mapGetters} from "vuex";

export default {
    data: function (){
        return {
            close_confirm_enabled: true,
            timer: '',
            estimate_fork: null,
            bases: [],
            current_ficha: null,
            current_active: null,
            old_ficha: null,
            selection_start_element: null,
            selection_finish_element: null,
            selection_start_element_y: null,
            selection_finish_element_y: null,
            selection: [],
            selection_started: false,
            deleted_ids: [],
            loading: false,
            current_show_insert_after: null,
            //
            isCreating: true,
            currentEstimate: null,
            service: null,
            //
            newEstimate: {
                // initial: true,
                subject: '',
                discount: 0,
                vat_maodeobra: 70,
                vat_material: 30,
                vat_custom: 0,
                vat_text: null,
                vat_type: 1,
                deadline: 0,
                price: 0,
                additional_price: 0,
                estimate_pay_stages: [
                    {
                        percent: 100, text: '', vat_type: null, invoice_file: null, 
                        invoice_file_name: null, recibo_file: null, 
                        recibo_file_name: null, paid: null, invoice_number: null,
                        fact_paid: null, email_template_id: null,
                        proof_file: null, proof_file_name: null
                    },
                ],
                lines: [],
                fork_id: null,
                option: null,
                revision: null,
                changes: [],
            },
            deleted_pay_stages: [],
        }
    },
    // create new - service_id set
    // create revision or option - base_estimate_id and action set
    // edit - id set
    props: ['id', 'service_id', 'base_estimate_id', 'action'],
    components: {
        'my-ficha': ficha,
    },
    created() {
        this.load_all();

        let $this = this;
        // Handle escape
        // https://stackoverflow.com/a/3369743
        document.onkeydown = function(evt) {
            evt = evt || window.event;
            var isEscape = false;
            if ("key" in evt) {
                isEscape = (evt.key == "Escape" || evt.key == "Esc");
            } else {
                isEscape = (evt.keyCode == 27);
            }
            if (isEscape) {
                $this.close_current_row();
            }
            if (evt.keyCode == 46 && document.activeElement.tagName != 'INPUT' && document.activeElement.tagName != 'TEXTAREA'){
                $this.remove_rows();
            }
            //                if((evt.ctrlKey) && ((evt.keyCode == 0xA)||(evt.keyCode == 0xD))){
            //                    $this.select_next_row();
            //                }
        };

        //this.timer = setInterval(this.update_estimate, 300000);
    },
    beforeDestroy() {
        clearInterval(this.timer);
        if (this.close_confirm_enabled === true){
            if (confirm(this.$root.$t('estimate.Are_you_sure_close_estimate'))) {
                this.update_estimate();
            }
        }
    },
    computed: {
        ...mapGetters({
            units: 'getEstimateUnits',
            unitsById: 'getEstimateUnitsById',
            usersById: 'getUsersById',
            global_settings: 'getGlobalSettings',
        }),
    },
    methods: {
        load_all(){
            if (this.id) {
                document.title = this.global_settings.site_name + ' | ' + this.$root.$t('estimate.Edit_estimate');

                this.$http.get('/api/estimates/' + this.id).then(res => {
                    this.service = res.data.service;
                    this.currentEstimate = Object.assign({}, this.newEstimate);
                    Object.assign(this.currentEstimate, res.data);
                    this.generate_tree();
                    this.calculate_total_price();

                    this.isCreating = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            } else {
                document.title = this.global_settings.site_name + ' | ' + this.$root.$t('estimate.New_estimate');
                if (this.base_estimate_id && this.action){
                    this.$http.get('/api/estimates/' + this.base_estimate_id).then(res => {
                        this.load_service(res.data.service_id).then(() => {
                            this.currentEstimate = Object.assign({}, this.newEstimate);
                            Object.assign(this.currentEstimate, res.data);
                            this.currentEstimate.id = null;
                            if (this.action == 'rev') {
                                if (this.currentEstimate.revision == null){
                                    this.currentEstimate.revision = 1;
                                } else {
                                    this.currentEstimate.revision++;
                                }
                            }
                            if (this.action == 'opt') {
                                if (this.currentEstimate.option == null){
                                    this.currentEstimate.option = 1;
                                } else {
                                    this.currentEstimate.option++;
                                }
                            }
                            this.old_ficha = null;
                            this.current_ficha = null;
                            this.generate_tree();
                            this.calculate_total_price();
                            this.$toastr.s(this.$root.$t("estimate.Estimate_loaded"), this.$root.$t("template.Success"));
                        });
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    });
                } else {
                    this.load_service(this.service_id).then(() => {
                        if (this.service.has_estimate){
                            this.$toastr.e(this.$root.$t("estimate.This_service_has_estimate"), this.$root.$t("template.Error"));
                            this.$router.push({name: 'my_dashboard'});
                        }

                        this.currentEstimate = Object.assign({}, this.newEstimate);
                        //
                        this.generate_tree();
                        this.calculate_total_price();
                    });
                }
            }
        },
        load_service(service_id){
            this.$root.global_loading = true;
            return new Promise((resolve, reject) => {
                this.$http.get('/api/services/' + service_id).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                        reject();
                    } else {
                        this.$root.global_loading = false;
                        this.service = res.data;
                        resolve();
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                    reject();
                });
            })
        },
        gen_forks(){
            if (this.id != null) {
                this.$root.global_loading = true;
                this.$http.post('/api/estimates/' + this.id + '/generate_forks', {}).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t('estimate.Fork_generated'), this.$root.$t("template.Success"));
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$root.global_loading = false;
                    this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                });
            }
        },
        get_base_options(search, loading) {
            loading(true);
            this.$http.get('/api/estimates?search=' + search).then(res => {
                var processedData = [];
                let $this = this;
                res.data.forEach(function(i){
                    let estimate = {
                        revision: i.revision ? parseInt(i.revision, 10) : null,
                        option: i.option ? parseInt(i.option, 10) : null,
                        fork_id: i.fork_id ? parseInt(i.fork_id, 10) : null,
                        service: {
                            estimate_number: i.estimate_number,
                            additional: i.additional ? parseInt(i.additional, 10) : null,
                        },
                    };
                    processedData.push({'label': $this.$root.estimate_number(estimate), 'value': i.id});
                });
                this.bases = processedData;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            })
        },
        base_select(res){
            if (res != null) {
                this.$http.get('/api/estimates/' + res.value).then(res => {
                    res.data.id = null;
                    res.data.service = {
                        estimate_number: this.service.estimate_number,
                        additional: this.service.additional,
                        address: this.service.address,
                    };
                    res.data.service_id = this.currentEstimate.service_id;
                    res.data.revision = this.currentEstimate.revision;
                    res.data.option = this.currentEstimate.option;
                    res.data.additional = this.additional;
                    Object.assign(this.currentEstimate, res.data);
                    this.old_ficha = null;
                    this.current_ficha = null;
                    this.generate_tree();
                    this.calculate_total_price();
                    this.$toastr.s(this.$root.$t("estimate.Estimate_loaded"), this.$root.$t("template.Success"));
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            }
        },
        update_estimate: function(final = false) {
            if (!this.loading) {
                if (final === true){
                    this.close_confirm_enabled = false;
                }
                let payload = Object.assign({}, this.currentEstimate);
                payload.lines = this.convert_tree_to_array(this.currentEstimate.lines);
                payload.service_id = this.service.id;
                payload.blocked = final;
                payload.deleted_ids = this.deleted_ids;
                payload.deleted_pay_stages = this.deleted_pay_stages;
                this.loading = true;
                this.$root.global_loading = true;
                if (this.isCreating) {
                    this.$http.post('/api/estimates', payload).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.deleted_ids = [];
                            this.$toastr.s(this.$root.$t("estimate.Estimate_saved"), this.$root.$t("template.Success"));
                            if (payload.blocked && this.service.client_contact_id) {
                                this.$router.push({name: 'contact_show', params: {id: this.service.client_contact_id}});
                            } else {
                                this.isCreating = false;
                                this.$router.push({name: 'estimate_edit', params: {id: res.data['id']}});
                            }
                        }
                        this.loading = false;
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.loading = false;
                        this.$root.global_loading = false;
                    });
                } else {
                    this.$http.patch('/api/estimates/' + this.currentEstimate.id, payload).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.deleted_ids = [];
                            this.$toastr.s(this.$root.$t("estimate.Estimate_saved"), this.$root.$t("template.Success"));
                            // if finishing
                            if (payload.blocked && this.service.client_contact_id) {
                                this.$router.push({name: 'contact_show', params: {id: this.service.client_contact_id}});
                            } else {
                                this.load_all();
                            }
                        }
                        this.loading = false;
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.loading = false;
                        this.$root.global_loading = false;
                    });
                }
            }
        },
        // calculates price of CHANGED section (and total estimate price) according to prices and quantities
        // this function executes after every change made
        // calculates upstairs from specified element to root element
        calculate_price: function (element){
            if (element.parent == null){
                let tmp_price = 0.0;
                this.currentEstimate.lines.forEach(function (item) {
                    tmp_price += item.children_total_price;
                });
                this.currentEstimate.price = this.round10(tmp_price);
            } else {
                let tmp_price = 0.0;
                let $this = this;
                element.parent.children.forEach(function (item) {
                    let tmp = 0.0;
                    if ($this.is_data(item) || $this.is_subdata(item)) {
                        tmp = $this.round10($this.multiply($this.data_with_additional(item.data_ppu), item.data_quantity));
                        item.data_price = tmp;
                    } else if ($this.is_ficha(item)) {
                        tmp = $this.round10($this.multiply($this.data_with_additional(item.ficha_ppu), item.ficha_quantity));
                        item.ficha_price = tmp;
                    }
                    tmp_price += item.children_total_price + tmp;
                });
                element.parent.children_total_price = this.round10(tmp_price);
                this.calculate_price(element.parent);
            }
        },
        // calculates price of ALL sections (and total estimate price) according to prices and quantities
        // this function executes after estimate (or base estimate) is loaded or when removing rows
        // calculates downstairs from root element to each children
        calculate_total_price: function (element) {
            let tmp_price = 0.0;
            if (element == null){
                if (this.currentEstimate.lines.length > 0) {
                    let $this = this;
                    this.currentEstimate.lines.forEach(function (item) {
                        tmp_price += $this.calculate_total_price(item);
                    });
                }
                this.currentEstimate.price = this.round10(tmp_price);
            } else {
                let tmp = 0.0;
                if (this.is_data(element) || this.is_subdata(element)) {
                    tmp = this.round10(this.multiply(this.data_with_additional(element.data_ppu), element.data_quantity));
                    element.data_price = tmp;
                } else if (this.is_ficha(element)) {
                    tmp = this.round10(this.multiply(this.data_with_additional(element.ficha_ppu), element.ficha_quantity));
                    element.ficha_price = tmp;
                }
                if ('children' in element) {
                    let $this = this;
                    element.children.forEach(function (item) {
                        tmp_price += $this.calculate_total_price(item);
                    });
                }
                element.children_total_price = this.round10(tmp_price);
                return this.round10(tmp_price + tmp);
            }
        },
        // Ficha
        open_ficha: function(element){
            this.old_ficha = Object.assign({}, element);
            this.old_ficha.maodeobra = JSON.parse(JSON.stringify(element.maodeobra));
            this.old_ficha.materials = JSON.parse(JSON.stringify(element.materials));
            this.old_ficha.subs = JSON.parse(JSON.stringify(element.subs));
            this.old_ficha.equipment = JSON.parse(JSON.stringify(element.equipment));
            this.current_ficha = element;
            $('#estimate-ficha').modal('show');
        },
        save_ficha: function(){
            this.calculate_price(this.current_ficha);
            this.select_row(this.current_ficha);
            $('#estimate-ficha').modal('hide');
        },
        close_ficha: function(){
            Object.assign(this.current_ficha, this.old_ficha);
            this.current_ficha.maodeobra = this.old_ficha.maodeobra;
            this.current_ficha.materials = this.old_ficha.materials;
            this.current_ficha.subs = this.old_ficha.subs;
            this.current_ficha.equipment = this.old_ficha.equipment;
            this.calculate_price(this.current_ficha);
            this.select_row(this.current_ficha);
            $('#estimate-ficha').modal('hide');
        },
        // Pay stages
        add_pay_stage: function () {
            this.currentEstimate.estimate_pay_stages.push({
                id: 0,
                percent: "", text: "", 
                vat_type: null, invoice_file: null, 
                invoice_file_name: null, recibo_file: null, 
                recibo_file_name: null, paid: null, invoice_number: null,
                fact_paid: null, email_template_id: null, 
                proof_file: null, proof_file_name: null});
        },
        remove_pay_stage: function (index, id) {
            this.currentEstimate.estimate_pay_stages.splice(index, 1);
            if (id > 0){
                this.deleted_pay_stages.push(id);
            }
        },
        // Rows
        select_row: function (element, not_inline = true) {
            if (this.current_active == null) {
                element.active = true;
                if (this.is_ficha(element) && not_inline){
                    this.open_ficha(element);
                }
                this.current_active = element;
            } else {
                this.current_active.active = false;
                if (this.current_active === element) {
                    this.current_ficha = null;
                    this.current_active = null;
                } else {
                    element.active = true;
                    if (this.is_ficha(element) && not_inline){
                        this.open_ficha(element);
                    } else {
                        this.current_ficha = null;
                    }
                    this.current_active = element;
                }
            }
        },
        add_row: function (element) {
            if (element != null) {
                if (element.show_insert_after) {
                    element.show_insert_after = false;
                    this.current_show_insert_after = null;
                } else {
                    element.show_insert_after = true;
                    if (this.current_show_insert_after != null) {
                        this.current_show_insert_after.show_insert_after = false;
                    }
                    this.current_show_insert_after = element;
                }
            }
        },
        remove_row: function (element) {
            if (confirm(this.$root.$t('estimate.Are_you_sure_want_to_delete_rows'))) {
                this.deleted_ids.push(element.id);
                this.deleted_ids = this.deleted_ids.concat(this.all_children(element).map(function (e) {
                    return e.id;
                }));
                if (element.parent != null) {
                    let $parent = element.parent;
                    let $del_index = $parent.children.indexOf(element);
                    $parent.children.splice($del_index, 1);
                    for (var i = $del_index; i < $parent.children.length; i++) {
                        this.generate_line_number($parent.children[i]);
                    }
                } else {
                    let $del_index = this.currentEstimate.lines.indexOf(element);
                    this.currentEstimate.lines.splice($del_index, 1);
                    for (var j = $del_index; j < this.currentEstimate.lines.length; j++) {
                        this.generate_line_number(this.currentEstimate.lines[j]);
                    }
                }
                this.calculate_price(element);
            }
        },
        close_current_row: function (){
            if (this.current_active != null) {
                this.current_active.active = false;
                this.current_ficha = null;
                this.current_active = null;
            }
        },
        //            select_next_row: function (){
        //                alert('qwer');
        //            },
        row_mouse_down: function($event, element){
            this.selection_start_element = element;
            this.selection_start_element_y = $event.pageY;
        },
        row_mouse_up: function($event, element){
            this.selection_finish_element = element;
            this.selection_finish_element_y = $event.pageY;
        },
        remove_rows: function(){
            let $this = this;
            if (this.selection_start_element_y && this.selection_finish_element_y && this.selection_start_element_y > this.selection_finish_element_y){
                let kek = this.selection_start_element;
                this.selection_start_element = this.selection_finish_element;
                this.selection_finish_element = kek;
            }
            this.find_selection(this.currentEstimate.lines);
            // console.log(this.selection);
            if (this.selection.length > 0 && this.valid_selection(this.selection)){
                if (confirm(this.$root.$t('estimate.Are_you_sure_want_to_delete_rows'))) {
                    let parent_of_selection = this.selection_parent(this.selection);
                    this.selection.forEach(function (e) {
                        $this.deleted_ids.push(e.id);
                        if (e.parent == null) {
                            let $del_index = $this.currentEstimate.lines.indexOf(e);
                            $this.currentEstimate.lines.splice($del_index, 1);
                        } else {
                            let $del_index = e.parent.children.indexOf(e);
                            e.parent.children.splice($del_index, 1);
                        }
                    });
                    if (parent_of_selection !== null) {
                        this.generate_line_number(parent_of_selection);
                    } else {
                        for (var i = 0; i < this.currentEstimate.lines.length; i++) {
                            this.generate_line_number(this.currentEstimate.lines[i]);
                        }
                    }
                    this.calculate_total_price();
                    this.selection_start_element = null;
                    this.selection_finish_element = null;
                    this.clear_text_selection();
                }
            } else {
                this.$toastr.w(this.$root.$t('estimate.Invalid_selection'), this.$root.$t("template.Warning"));
            }
            this.selection = [];
        },
        to_up: function (element){
            let p_c = element.parent == null ? this.currentEstimate.lines : element.parent.children;
            let index = p_c.indexOf(element);
            if (index > 0) {
                if ((!('children' in element) || element.children.length == 0) && (!('children' in p_c[index - 1]) || p_c[index - 1].children.length == 0)){
                    p_c.splice(index - 1, 0, p_c.splice(index, 1)[0]);
                    this.generate_line_number(p_c[index - 1]);
                    this.generate_line_number(p_c[index]);
                }
            }
        },
        to_down: function (element){
            let p_c = element.parent == null ? this.currentEstimate.lines : element.parent.children;
            let index = p_c.indexOf(element);
            if (index < p_c.length - 1) {
                if ((!('children' in element) || element.children.length == 0) && (!('children' in p_c[index + 1]) || p_c[index + 1].children.length == 0)){
                    p_c.splice(index + 1, 0, p_c.splice(index, 1)[0]);
                    this.generate_line_number(p_c[index + 1]);
                    this.generate_line_number(p_c[index]);
                }
            }
        },
        // Custom adds
        add_category_row: function (element = null) {
            let $category_line = {
                lineable_type: "\\App\\EstimateLineCategory",
                parent: null,
                category_name: null,
                line_number: null,
                active: false,
                show_insert_after: false,
                children_total_price: 0.0,
                // Random id
                id: (new Date()).getTime(),
            };
            Vue.set($category_line, 'children', []);
            this.currentEstimate.lines.push($category_line);
            this.generate_line_number($category_line);
            this.select_row($category_line);
        },
        add_subcategory_row: function (element = null) {
            if (element === null) {
                element = this.find_last_element();
            }
            let $subcategory_line = {
                lineable_type: "\\App\\EstimateLineCategory",
                parent: null,
                category_name: null,
                line_number: null,
                active: false,
                show_insert_after: false,
                children_total_price: 0.0,
                // Random id
                id: (new Date()).getTime(),
            };
            Vue.set($subcategory_line, 'children', []);
            let upplace = null;
            let downplace = null;
            if (this.is_separator(element)) {
                if (element.parent === null) {
                    // if first level separator
                    // Not possible
                    return;
                } else {
                    if (this.is_category(element.parent)){
                        $subcategory_line.parent = element.parent;
                    } else if (this.is_subcategory(element.parent)){
                        $subcategory_line.parent = element.parent.parent;
                    } else if (this.is_category(element.parent.parent)){
                        $subcategory_line.parent = element.parent.parent;
                    } else {
                        $subcategory_line.parent = element.parent.parent.parent
                    }
                    if (this.is_category(element.parent)) {
                        // if second level separator
                        upplace = element.parent;
                        downplace = element;
                    } else {
                        this.transfer_sublings(element, $subcategory_line);
                        if (this.is_subcategory(element.parent)) {
                            // if third level separator
                            upplace = element.parent.parent;
                            downplace = element.parent;
                        } else if (this.is_category(element.parent.parent)) {
                            // if third level separator
                            upplace = element.parent.parent;
                            downplace = element.parent;
                        } else {
                            // if fourth level separator
                            this.transfer_sublings(element.parent, $subcategory_line);
                            upplace = element.parent.parent.parent;
                            downplace = element.parent.parent;
                        }
                    }
                }
            } else if (this.is_category(element)) {
                $subcategory_line.parent = element;
                upplace = element;
                downplace = null;
                // indexOf(downplace) in bottom will return -1 and all will be ok
            } else if (this.is_subcategory(element)) {
                $subcategory_line.parent = element.parent;
                this.transfer_children(element, $subcategory_line);
                upplace = element.parent;
                downplace = element;
            } else if (this.is_data(element) || this.is_ficha(element)) {
                // second or third level data
                $subcategory_line.parent = this.is_category(element.parent) ? element.parent : element.parent.parent;
                this.transfer_children(element, $subcategory_line);
                if (this.is_category(element.parent)) {
                    // if second level data
                    upplace = element.parent;
                    downplace = element;
                } else {
                    // if third level data
                    this.transfer_sublings(element, $subcategory_line);
                    upplace = element.parent.parent;
                    downplace = element.parent;
                }
            } else if (this.is_subdata(element)) {
                // third or fourth level data
                $subcategory_line.parent = this.is_category(element.parent.parent) ? element.parent.parent : element.parent.parent.parent;
                this.transfer_sublings(element, $subcategory_line);
                if (this.is_category(element.parent.parent)) {
                    // if third level subdata
                    upplace = element.parent.parent;
                    downplace = element.parent;
                } else {
                    // if fourth level data
                    this.transfer_sublings(element.parent, $subcategory_line);
                    upplace = element.parent.parent.parent;
                    downplace = element.parent.parent;
                }
            }
            this.add_line_with_gen_number_and_select(upplace, downplace, $subcategory_line);
        },
        add_data_row: function (element = null) {
            if (element === null) {
                element = this.find_last_element();
            }
            let $data_line = {
                lineable_type: "\\App\\EstimateLineData",
                parent: null,
                data_description: null,
                data_measure: this.units[0].id,
                data_ppu: null,
                data_quantity: null,
                data_note: "",
                data_price: null,
                line_number: null,
                active: false,
                show_insert_after: false,
                children_total_price: 0.0,
                // Random id
                id: (new Date()).getTime(),
            };
            Vue.set($data_line, 'children', []);
            let upplace = null;
            let downplace = null;
            if (this.is_separator(element)) {
                if (element.parent === null) {
                    // if first level separator
                    // Not possible
                    return;
                } else {
                    $data_line.parent = this.is_category(element.parent) || this.is_subcategory(element.parent) ? element.parent : element.parent.parent;
                    if (this.is_category(element.parent) || this.is_subcategory(element.parent)) {
                        upplace = element.parent;
                        downplace = element;
                    } else {
                        this.transfer_sublings(element, $data_line);
                        upplace = element.parent.parent;
                        downplace = element.parent;
                    }
                }
            } else if (this.is_category(element)) {
                $data_line.parent = element;
                upplace = element;
                downplace = null;
            } else if (this.is_subcategory(element)) {
                $data_line.parent = element;
                upplace = element;
                downplace = null;
            } else if (this.is_data(element) || this.is_ficha(element)) {
                // if second or third level data
                $data_line.parent = element.parent;
                this.transfer_children(element, $data_line);
                upplace = element.parent;
                downplace = element;
            } else if (this.is_subdata(element)) {
                $data_line.parent = element.parent.parent;
                this.transfer_sublings(element, $data_line);
                upplace = element.parent.parent;
                downplace = element.parent;
            }
            this.add_line_with_gen_number_and_select(upplace, downplace, $data_line);
        },
        add_subdata_row: function (element = null) {
            if (element === null) {
                element = this.find_last_element();
            }
            let $subdata_line = {
                lineable_type: "\\App\\EstimateLineData",
                parent: null,
                data_description: null,
                data_measure: this.units[0].id,
                data_ppu: null,
                data_quantity: null,
                data_note: "",
                data_price: null,
                line_number: null,
                active: false,
                show_insert_after: false,
                children_total_price: 0.0,
                // Random id
                id: (new Date()).getTime(),
            };
            let upplace = null;
            let downplace = null;
            if (this.is_separator(element)) {
                if (this.is_data(element.parent)) {
                    $subdata_line.parent = element.parent;
                    upplace = element.parent;
                    downplace = element;
                } else {
                    // Not possible
                    return;
                }
            } else if (this.is_data(element)) {
                $subdata_line.parent = element;
                upplace = element;
                downplace = null;
            } else if (this.is_subdata(element)) {
                $subdata_line.parent = element.parent;
                upplace = element.parent;
                downplace = element;
            } else {
                // Not possible
                return;
            }
            this.add_line_with_gen_number_and_select(upplace, downplace, $subdata_line);
        },
        add_ficha_row: function (element = null) {
            if (element === null) {
                element = this.find_last_element();
            }
            let $ficha_line = {
                lineable_type: "\\App\\EstimateLineFicha",
                parent: null,
                ficha_description: null,
                ficha_measure: this.units[0].id,
                ficha_ppu: null,
                ficha_quantity: null,
                ficha_note: "",
                maodeobra: { list: [], total_price: 0},
                materials: { list: [], total_price: 0},
                subs: { list: [], total_price: 0},
                equipment: { list: [], total_price: 0},
                line_number: null,
                active: false,
                show_insert_after: false,
                children_total_price: 0.0,
                // Random id
                id: (new Date()).getTime(),
            };
            let upplace = null;
            let downplace = null;
            if (this.is_separator(element)) {
                if (element.parent === null) {
                    // if first level separator
                    // Not possible
                    return;
                } else {
                    $ficha_line.parent = this.is_category(element.parent) || this.is_subcategory(element.parent) ? element.parent : element.parent.parent;
                    if (this.is_category(element.parent) || this.is_subcategory(element.parent)) {
                        upplace = element.parent;
                        downplace = element;
                    } else {
                        this.transfer_sublings(element, $ficha_line);
                        upplace = element.parent.parent;
                        downplace = element.parent;
                    }
                }
            } else if (this.is_category(element)) {
                $ficha_line.parent = element;
                upplace = element;
                downplace = null;
            } else if (this.is_subcategory(element)) {
                $ficha_line.parent = element;
                upplace = element;
                downplace = null;
            } else if (this.is_data(element) || this.is_ficha(element)) {
                // if second or third level data
                $ficha_line.parent = element.parent;
                upplace = element.parent;
                downplace = element;
            }
            this.add_line_with_gen_number_and_select(upplace, downplace, $ficha_line);
        },
        add_separator_row: function (element = null) {
            let $separator_line = {
                lineable_type: "\\App\\EstimateLineSeparator",
                parent: null,
                separator_name: null,
                line_number: null,
                active: false,
                show_insert_after: false,
                children_total_price: 0.0,
                // Random id
                id: (new Date()).getTime(),
            };
            if (element === null) {
                this.currentEstimate.lines.push($separator_line);
            } else if ('children' in element && element.children.length !== 0) {
                $separator_line.parent = element;
                element.children.unshift($separator_line);
            } else {
                $separator_line.parent = element.parent;
                let place = this.is_category(element) ? this.currentEstimate.lines : element.parent.children;
                place.splice(place.indexOf(element) + 1, 0, $separator_line);
            }
            this.select_row($separator_line);
        },
        // Checks
        is_category_or_subcategory: function (element) {
            return element.lineable_type === "\\App\\EstimateLineCategory"
        },
        is_category: function (element) {
            return element.lineable_type === "\\App\\EstimateLineCategory" && element.parent === null
        },
        is_subcategory: function (element) {
            return element.lineable_type === "\\App\\EstimateLineCategory" && element.parent !== null && element.parent.lineable_type === "\\App\\EstimateLineCategory"
        },
        is_separator: function (element) {
            return element.lineable_type === "\\App\\EstimateLineSeparator"
        },
        is_data_or_subdata: function (element) {
            return element.lineable_type === "\\App\\EstimateLineData"
        },
        is_data: function (element) {
            return element.lineable_type === "\\App\\EstimateLineData" && element.parent !== null && element.parent.lineable_type !== "\\App\\EstimateLineData"
        },
        is_subdata: function (element) {
            return element.lineable_type === "\\App\\EstimateLineData" && element.parent !== null && element.parent.lineable_type === "\\App\\EstimateLineData"
        },
        is_ficha: function (element) {
            return element.lineable_type === "\\App\\EstimateLineFicha"
        },
        // Helpers
        generate_tree: function () {
            // lines MUST BE ordered by parent_id
            var map = {};
            var line;
            var roots = [];
            for (var i = 0; i < this.currentEstimate.lines.length; i += 1) {
                line = this.currentEstimate.lines[i];
                Vue.set(line, 'children', []);
                map[line.id] = i;
                if (line.parent_id !== null) {
                    this.currentEstimate.lines[map[line.parent_id]].children.push(line);
                    line.parent = this.currentEstimate.lines[map[line.parent_id]];
                } else {
                    roots.push(line);
                    line.parent = null;
                }
            }
            this.currentEstimate.lines = roots;
            // NOT NEED because ordering in query
            // this.sort_tree(this.currentEstimate.lines);
        },
        sort_tree: function (element){
            if (Array.isArray(element)){
                this.currentEstimate.lines.sort(function(a, b) {
                    return parseFloat(a.order) - parseFloat(b.order);
                });
                for (var i = 0; i < element.length; i++) {
                    this.sort_tree(element[i]);
                }
            } else if ('children' in element){
                element.children.sort(function(a, b) {
                    return parseFloat(a.order) - parseFloat(b.order);
                });
                for (var j = 0; j < element.children.length; j++) {
                    this.sort_tree(element.children[j]);
                }
            }
        },
        convert_tree_to_array: function (element) {
            let my_array = [];
            if (Array.isArray(element)){
                for (var i = 0; i < element.length; i++) {
                    element[i].order = i + 1;
                    my_array = my_array.concat(this.convert_tree_to_array(element[i]));
                }
            } else {
                if (element.parent != null){
                    element.parent_id = element.parent.id
                }
                // Copy object and push it into array
                let new_element = Object.assign({}, element);

                new_element.parent = null;
                new_element.children = null;
                my_array.push(new_element);
                if ('children' in element) {
                    for (var j = 0; j < element.children.length; j++) {
                        element.children[j].order = j + 1;
                        my_array = my_array.concat(this.convert_tree_to_array(element.children[j]));
                    }
                }
            }
            return my_array;
        },
        generate_line_number: function (element) {
            let $order = 0;
            if (element.parent !== null) {
                let $par = element.parent;
                for (var i = 0; i < element.parent.children.length; i++) {
                    if (!this.is_separator($par.children[i])) {
                        $order++;
                    }
                    if ($par.children[i] === element) {
                        break;
                    }
                }
            } else {
                for (var j = 0; j < this.currentEstimate.lines.length; j++) {
                    if (!this.is_separator(this.currentEstimate.lines[j])) {
                        $order++;
                    }
                    if (this.currentEstimate.lines[j] === element) {
                        break;
                    }
                }
            }
            if (element.parent !== null) {
                element.line_number = element.parent.line_number + '.' + $order;
            } else {
                element.line_number = $order;
            }
            let $this = this;
            if ('children' in element) {
                element.children.forEach(function (item) {
                    $this.generate_line_number(item);
                });
            }
        },
        unitize: function (value) {
            if (value != null) {
                let $un = this.unitsById[value].measure;
                switch ($un) {
                case 'm2':
                    $un = "m<sup>2</sup>";
                    break;
                case 'm3':
                    $un = "m<sup>3</sup>";
                    break;
                case 'm2/ml':
                    $un = "m<sup>2</sup>/ml";
                    break;
                }
                return $un;
            } else {
                return "";
            }
        },
        find_category_in_parents: function (element) {
            if (this.is_category(element)) {
                return element;
            } else {
                return this.find_category_in_parents(element.parent);
            }
        },
        find_last_element: function (element = null) {
            if (element === null) {
                return this.find_last_element(this.currentEstimate.lines[this.currentEstimate.lines.length - 1]);
            } else if ('children' in element && element.children.length !== 0) {
                return this.find_last_element(element.children[element.children.length - 1]);
            } else {
                return element;
            }
        },
        transfer_sublings: function (element, line) {
            let $index = element.parent.children.indexOf(element);
            for (var i = $index + 1; i < element.parent.children.length;) {
                line.children.push(element.parent.children[i]);
                element.parent.children[i].parent = line;
                element.parent.children.splice(i, 1);
            }
        },
        transfer_children: function (element, line) {
            // transfer children
            if ('children' in element) {
                line.children = element.children;
                line.children.forEach(function (child) {
                    child.parent = line;
                });
                element.children = [];
            }
        },
        add_line_with_gen_number_and_select: function (upplace, downplace, line) {
            let $index = upplace.children.indexOf(downplace);
            upplace.children.splice($index + 1, 0, line);
            this.generate_line_number(line);
            if ('children' in upplace) {
                for (var i = $index + 1; i < upplace.children.length; i++) {
                    this.generate_line_number(upplace.children[i]);
                }
            }
            this.add_row(this.current_show_insert_after);
            this.select_row(line);
        },
        round10: function (num){
            return this.$root.roundNumber(num, 2);
        },
        multiply: function(v1, v2){
            return ((v1 * 100) * (v2 * 100)) / 10000;
        },
        data_with_additional: function (data){
            if (data != null && data != 0){
                return this.round10(data * (1 + this.currentEstimate.additional_price / 100));
            } else {
                return '';
            }
        },
        zero_and_null_checker: function (data){
            if (data != null && data != 0){
                return data;
            } else {
                return '';
            }
        },
        find_selection: function(e){
            let $this = this;
            e.forEach(function(i){
                if (i == $this.selection_start_element){
                    $this.selection_started = true;
                }
                if ($this.selection_started){
                    $this.selection.push(i);
                }
                if (i == $this.selection_finish_element){
                    $this.selection_started = false;
                }
                if ('children' in i && i.children.length > 0){
                    $this.find_selection(i.children);
                }
            })
        },
        // if selection includes all nodes with children nodes - valid
        valid_selection: function(a){
            let $this = this;
            // forEach cannot use return, so it continue loop
            for (let j = 0; j < a.length; j++) {
                let i = a[j];
                if ($this.selection.indexOf(i) === -1){
                    return false;
                }
                if ('children' in i && i.children.length > 0){
                    if (!$this.valid_selection(i.children)){
                        return false;
                    }
                }
            }
            return true;
        },
        clear_text_selection: function(){
            if (window.getSelection) {
                if (window.getSelection().empty) { // Chrome
                    window.getSelection().empty();
                } else if (window.getSelection().removeAllRanges) { // Firefox
                    window.getSelection().removeAllRanges();
                }
            } else if (document.selection) { // IE?
                document.selection.empty();
            }
        },
        selection_parent: function(a){
            for (let j = 0; j < a.length; j++) {
                let i = a[j];
                if (a.indexOf(i.parent) === -1) {
                    return i.parent;
                }
            }
        },
        all_children: function(e){
            let $this = this;
            if ('children' in e && e.children.length > 0){
                let res = [];
                e.children.forEach(function(i){
                    res.push(i);
                    res = res.concat($this.all_children(i));
                });
                return res;
            } else {
                return [];
            }
        },
    },
    watch: {
        'currentEstimate.vat_maodeobra': function(val){
            this.currentEstimate.vat_material = 100 - this.currentEstimate.vat_custom - val;
        },
        'currentEstimate.vat_material': function(val){
            this.currentEstimate.vat_maodeobra = 100 - this.currentEstimate.vat_custom - val;
        },
        'currentEstimate.additional_price': function(val){
            this.calculate_total_price();
        },
        '$route' (to, from) {
            this.load_all();
        },
        // 'currentEstimate': {
        //     deep: true,
        //     handler: function(newEstimate, currentEstimate){
        //         if (!currentEstimate.initial || (newEstimate.initial && currentEstimate.initial)){
        //             this.update_estimate();
        //         }
        //     }
        // },
    },
}
</script>