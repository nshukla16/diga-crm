<template lang="pug">
    div.diga-container.p-4
        .row(v-if="documents")
            .col-12.mb-3
                button.btn.mr-3(v-on:click='uncheck_all()')
                    i.fa.fa-square-o
                    |  {{ $t('estimate.Uncheck_all') }}
                button.btn(v-on:click='check_all()')
                    i.fa.fa-check-square-o
                    |  {{ $t('estimate.Check_all') }}
            .col-12
                .row
                    document(v-for='document in documents', :key='document.url', :document='document')
                form#print_form(method='post', v-bind:action="'/estimates/'+id+'/documents'")
                    input#print_token(type='hidden', name='_token')
                    input#docs(type='hidden', name='docs')
                    button.btn.btn-diga(v-on:click='print()') {{ $t('estimate.Print') }}
                .clearfix
</template>

<script>
import doc from "./document.vue"

export default {
    data: function() {
        return {
            documents: null,
            tmpCount: 0,
        }
    },
    props: ['id'],
    components: {
        'document': doc,
    },
    created() {
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('estimate.Documents');

        this.$http.get('/api/estimates/' + this.id + '/documents').then(res => {
            this.documents = res.data;
        }, res => {
            this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
        });
    },
    methods: {
        print: function(){
            document.getElementById('print_token').value = document.head.querySelector('meta[name="csrf-token"]').content;
            document.getElementById('docs').value = JSON.stringify(this.documents);
            document.getElementById('print_form').submit();
        },
        cloneCanvas: function (oldCanvas) {
            var newCanvas = document.createElement('canvas');
            var context = newCanvas.getContext('2d');
            newCanvas.width = oldCanvas.width;
            newCanvas.height = oldCanvas.height;
            context.drawImage(oldCanvas, 0, 0);
            return newCanvas;
        },
        check_all: function(){
            this.documents.forEach(function(d){
                d.checked = true;
            });
        },
        uncheck_all: function(){
            this.documents.forEach(function(d){
                d.checked = false;
            });
        },
    },
}
</script>