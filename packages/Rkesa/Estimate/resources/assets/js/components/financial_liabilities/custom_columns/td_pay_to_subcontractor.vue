<template lang="pug">
    ul
        li(v-for="eg in row.estimate_groups") {{parseFloat(calculate_for_estimate_group(eg)).toFixed(2)}}
        b {{parseFloat(calculate_total()).toFixed(2)}}
</template>

<script>
export default {
    props: ['row'],
    methods:{
        calculate_for_estimate_group(estimate_group){
            var total_to_pay = estimate_group.estimate.price * (estimate_group.percent / 100);
            var already_paid = 0.0;
            estimate_group.estimate_group_pay_stages.forEach(p => {
                already_paid += p.fact_paid;
            });

            return total_to_pay - already_paid;
        },
        calculate_total(){

            var total_to_pay = 0.0;
            var total_already_paid = 0.0;

            this.row.estimate_groups.forEach(estimate_group => {
                var total_to_pay_eg = estimate_group.estimate.price * (estimate_group.percent / 100);
                var already_paid = 0.0;
                estimate_group.estimate_group_pay_stages.forEach(p => {
                    already_paid += p.fact_paid;
                });

                total_to_pay += total_to_pay_eg;
                total_already_paid += already_paid;
            });

            return total_to_pay - total_already_paid;
        }
    },
}
</script>