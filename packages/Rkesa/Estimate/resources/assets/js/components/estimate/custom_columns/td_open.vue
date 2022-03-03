<template lang="pug">
    div
        router-link(style="margin-right: 7px;" data-toggle="tooltip" data-placement="top" :title="$t('estimate.Edit_estimate')" v-if="$root.can_do('estimates', 'update') != 0 && (!row.blocked || $root.user.id === row.user_id || $root.user.id === $root.global_settings.unlocker_user_id)", :to="{name: 'estimate_edit', params: {id: row.id}}")
            i.fa.fa-pencil
        router-link(data-toggle="tooltip" data-placement="top" :title="$t('estimate.Planning')" v-if="$root.can_do('plannings', 'update') != 0 && $root.module_enabled('gantt')", :to="{name: 'planning_edit', params: {id: row.id}}")
            i.fa.fa-file-text-o
        router-link(data-toggle="tooltip" data-placement="top" :title="$t('estimate.Documents')" :to="{name: 'estimate_documents', params: {id: row.id}}" style="margin-left: 5px;")
            i.fa.fa-briefcase
</template>

<script>
export default {
    props: ['row'],
}
</script>