const project_conditions_of_delivery = ['DDP', 'DAP', 'FCA', 'FOB', 'EXW', 'CIF'];

const manufacturer_conditions_of_delivery = ['FCA', 'FOB', 'EXW', 'CPT', 'CIP', 'CFR', 'CIF', 'DAP'];

const inner_conditions_of_delivery = ['FCA', 'FOB', 'EXW'];

// if you change shipping order conditions, be sure you changed them also in backend
// in ManufacturerOrderController@get_condition_name_from_id
const shipping_order_conditions_of_delivery = ['DDP', 'DAP', 'FCA', 'FOB', 'EXW', 'CIF', 'CPT'];

export {
    project_conditions_of_delivery,
    manufacturer_conditions_of_delivery,
    inner_conditions_of_delivery,
    shipping_order_conditions_of_delivery,
}