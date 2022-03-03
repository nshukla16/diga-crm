<?php
return [
    'stage' => [
        'rows'=>  [
            'count'=> 5
        ]
    ],
    'entity' => [
        'fields' => [
            'columns' => [
                [ 'text' => 'status_date', 'value' => 1 ],
                [ 'text' => 'service_ovp', 'value' => 2 ],
                [ 'text' => 'service_data_inicio', 'value' => 3 ],
                [ 'text' => 'master_sum', 'value' => 4 ],
                [ 'text' => 'master_number', 'value' => 5 ],
                [ 'text' => 'client_name', 'value' => 6 ],
                [ 'text' => 'client_referer', 'value' => 7 ],
                [ 'text' => 'service_responsible', 'value' => 8 ],
                [ 'text' => 'service_region', 'value' => 9 ],
                [ 'text' => 'responsible_first_task', 'value' => 10 ],
                [ 'text' => 'work_final_date', 'value' => 11 ],
                [ 'text' => 'task_date', 'value' => 12 ],
                [ 'text' => 'task_responsible', 'value' => 13 ],
            ]
        ]
    ],
//    'widgets' => [
//        [
//            'type' => 1,
//            'text' => 'Bar chart',
//        ],
//        [
//            'type' => 2,
//            'text' => 'Line chart'
//        ],
//        [
//            'type' => 3,
//            'text' => 'Pie chart'
//        ],
//        [
//            'type' => 4,
//            'text' => 'Table'
//        ]
//    ],
    "widhet_types" => [
        array('type' => 'bar'),
        array('type' => 'line'),
        array('type' => 'pie'),
        array('type' => 'table'),
    ],
    'max_widgets' => 10
];
