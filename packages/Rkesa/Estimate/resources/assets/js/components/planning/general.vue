<template lang="pug">
div.diga-container.p-4
    h2 {{ $t('estimate.General_information') }}
    .form-group.row
        label.col-4.control-label {{ $t('estimate.estimate_number') }}
        .col-8.input-group
            label.form-control
                | {{ $root.estimate_number(currentEstimate) }}
    .form-group.row
        label.col-4.control-label {{ $t('estimate.Assunto') }}
        .col-8.input-group
            textarea.form-control.estimate-subject(readonly='', v-model='currentEstimate.subject', rows='2', style='resize: vertical')
    .form-group.row
        label.col-4.control-label {{ $t('estimate.Obra_situada_em') }}
        .col-8.input-group
            label.form-control {{ currentEstimate.service.address }}    
</template>

<script>
export default {
    props: ['currentEstimate'],
}
</script>