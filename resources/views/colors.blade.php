.color1 { background-color: {{ $color1 }}; }
.color2 { background-color: {{ $color2 }}; }
.color2-text{ color: {{ $color2 }}; }
.color2-border{ border: 1px solid {{ $color2 }}; }
.hoverable:hover{ color: {{ $color2 }}; }
.envelope-button:hover, .envelope-button:focus { background-color: {{ $color2 }}; }
body a { color: {{ $color2 }}; }
body .form-control:focus {
border-color: {{ $color3 }};
box-shadow: 0 0 0 0.2rem {{ $color2 }};
}
.color3 { background-color: {{ $color3 }}; }
body { color: {{ $color3 }} !important; }
body a:hover, body a:focus { color: {{ $color3 }}; }
.color4 { background-color: {{ $color4 }}; }
.color5 { background-color: {{ $color5 }}; }
.g-core-image-upload-btn{ background-color: {{ $color3 }}; }
/* MENU */
nav li{ border-bottom: 2px solid {{ $color1 }}; }
nav li a{ color: {{ $color1 }}; }
nav li.active a{ color: {{ $color2 }}; }
nav li a.dropdown-item:active{ background-color: {{ $color3 }}; }
a.dropdown-item.active { background-color: {{ $color3 }}; }
/* CALENDAR */
#calendar div.fc-widget-header{ background-color: {{ $color3 }}; }
#calendar td.fc-today{ background-color: {{ $color4 }}; }
#calendar .fc-day-number{ color: {{ $color3 }}; }
.calendar-title{ color: {{ $color3 }}; }
#dashboard-range .mx-calendar-month>a.current, #dashboard-range .mx-calendar-year>a.current, #dashboard-range .mx-calendar-table td.current { background-color: {{ $color3 }}; }
#dashboard-range .mx-calendar-table td.inrange, #dashboard-range .mx-calendar-table td:hover, #dashboard-range .mx-calendar-month>a:hover, #dashboard-range .mx-calendar-year>a:hover { background-color: {{ $color4 }}; }
#dashboard-range .mx-datepicker-top>span:hover, #dashboard-range .mx-calendar-table .today { color: {{ $color3 }}; }
.stage-arrow{ color: {{ $color3 }}; }
/* btn-primary & toggle */ 
.btn-primary.toggle-on, .btn.btn-primary.toggle-on:not([disabled]):not(.disabled):active, .btn-primary.toggle-on:hover { background-color: {{ $color4 }}; }
.btn.btn-primary, .btn.btn-primary:not([disabled]):not(.disabled):active, .btn.btn-primary:hover { color: {{ $color3 }}; }
.btn-primary.toggle:hover, .btn-primary.toggle:not([disabled]):not(.disabled):active, .btn-primary.toggle-on:not([disabled]):not(.disabled):active { border-color: {{ $color3 }}; }
.toggle-handle { background-color: {{ $color3 }}; }
/* DASHBOARD */
.highcharts-title{ fill: {{ $color3 }} !important; }
/* MAIL */
.panels .panel.folders .items_list .item.selected > .title{ background: {{ $color1 }}; }
.panels .panel.folders .items_list .item.selected > .title .count,
.panels .panel.folders .items_list .item.selected > .title .count.unseen,
.panels .panel.folders .items_list .item.selected > .title .count.unseen:hover{ background: {{ $color2 }}; }
.panels .panel.folders .toolbar .content .item:hover:not(.disabled):not(.passive),
.panels .panel.groups .items_list .item.selected > .title{ background: {{ $color1 }}; }
#selenium_new_message_button,
#selenium_contacts_new_button{ background: {{ $color3 }}; }
.panel_center .items_list .item.selected,
.panel_center .items_list .item.checked.selected{ background: {{ $color3 }}; }
.panel_center .items_list .item.checked{ background: {{ $color4 }}; }
.messages .panel_content .items_list .item.selected .flag:not(.partial):before,
.messages .panel_content .items_list .item.checked.selected .flag:not(.partial):before{ color: {{ $color2 }}; }
.messages .panel_content .items_list .item.checked .flag:not(.partial):before{ color: {{ $color3 }}; }
.manage_folders a{ color: {{ $color3 }} !important; }
.panel_bottom .volumer .used{ background: {{ $color3 }}; }
.pSevenMain .buttons .button,
.pSevenMain .buttons .button:hover,
.pSevenMain .toolbar .content > .item.send,
.pSevenMain .toolbar .content .group > .item.send,
.pSevenMain .toolbar .content > .item.mail_to,
.pSevenMain .toolbar .content .group > .item.mail_to{
background: {{ $color3 }} !important;
border: none;
}
.pSevenMain .buttons .button.secondary,
.pSevenMain .buttons .button.secondary:hover{
background: {{ $color1 }};
border: none;
}
.panel_helper .message_viewer .message_header .from{ color: {{ $color3 }}; }
.item_viewer .title .add_contact,
.item_viewer .message_header .add_contact{ color: {{ $color3 }} !important; }
.message_viewer .panel_content .notice{
color: {{ $color1 }};
background: {{ $color4 }};
}
.pSevenMain .link{ color: {{ $color3 }} !important; }
.pSevenMain .report.report_panel .content{ background: {{ $color3 }}; }
.panel.item_viewer .panel_content .attachments .download_menu .link .icon,
.pSevenMain .attachments .item .buttons .button{ color: #ffffff; }
.screen .settings .accounts_list .item .link.fetcher{ color: {{ $color3 }}; }
.screen .settings .panel.navigation .items_list .item.selected > .title{ background: {{ $color3 }}; }
.pSevenMain .panel.contacts .item .data .email{ color: {{ $color3 }}; }
.pSevenMain .panel.contact_viewer .title.email{ color: {{ $color3 }}; }
.pSevenMain .screen .toolbar .content > .item.enable:hover.send{ background: {{ $color3 }}; }
.pSevenMain .inputosaurus-container li a,
.pSevenMain .inputosaurus-moving-container li a,
.pSevenMain .inputosaurus-container li span{ color: {{ $color3 }}; }
.pSevenMain .panel.compose table.fields .inputosaurus-container li.ui-draggable,
.pSevenMain .panel.compose table.fields .inputosaurus-moving-container li{ background: {{ $color4 }}; }
/* PAGINATION */
ul.pagination .page-item.active .page-link{
background-color: {{ $color3 }};
border-color: {{ $color3 }};
}
ul.pagination .page-link { color: {{ $color3 }}; }
/* DATETIMEPICKER */
.bootstrap-datetimepicker-widget .datepicker td.active,
.bootstrap-datetimepicker-widget .datepicker td.active:hover,
.bootstrap-datetimepicker-widget .datepicker-months .month.active,
.bootstrap-datetimepicker-widget .datepicker-years .year.active,
.bootstrap-datetimepicker-widget .datepicker-decades .decade.active{
background-color: {{ $color3 }};
}
.bootstrap-datetimepicker-widget .datepicker-days td.today:before{
border-bottom-color: {{ $color3 }};
}
/* CLIENT */
.contacts-list.active{ color: {{ $color2 }}; }
.with-gradient{ background-image: linear-gradient(300deg, rgba({{ $color3rgb['red'] }}, {{ $color3rgb['green'] }}, {{ $color3rgb['blue'] }}, 0.25), rgba({{ $color1rgb['red'] }}, {{ $color1rgb['green'] }}, {{ $color1rgb['blue'] }}, 0.05)); }
.horizontal-subline.active{ background-color: {{ $color3 }}; }
.arrow-button{ border: 2px solid {{ $color3 }}; }
/* OTHER */
.btn-diga, #history .btn{ background-color: {{ $color3 }}; }
.loader{ border-top: 8px solid {{ $color3 }} !important; }
.sm-loader{ border-top: 2px solid {{ $color3 }} !important; }
{{-- EXTENDED CALENDAR --}}
@if (config('modules_enabled')['calendar_extended'])
@foreach (\Rkesa\CalendarExtended\Models\EventGroup::all() as $event_group)
.event-group-{{ $event_group->id }}{
background-color: {{ $event_group->color }};
}
@endforeach
@endif
body #buorgul{ background-color: {{ $color3 }}; }
body #buorgig{ background-color: {{ $color2 }}; }
/* vue2-datepicker */
.mx-calendar-content .cell.actived { background-color: {{ $color3 }} !important; }
.mx-panel-date td.today { color: {{ $color3 }} !important; }
.mx-calendar-content .cell:hover { background-color: {{ $color4 }} !important; }
.mx-calendar-content .cell.inrange { background-color: {{ $color4 }} !important; }
.mx-shortcuts-wrapper .mx-shortcuts:hover { color: {{ $color3 }} !important; }
.mx-calendar-content .cell.actived { color: #fff !important; }
.vdatetime-calendar__month__day--selected > span > span, .vdatetime-calendar__month__day--selected:hover > span > span{ background: {{ $color3 }} !important; }
.vdatetime-popup__header{ background: {{ $color3 }} !important; }
.multiselect__option--highlight{ background: {{ $color3 }} !important; }
.multiselect__option--highlight:after{ background: {{ $color3 }} !important; }
.multiselect__tag{ background: {{ $color3 }} !important; }
