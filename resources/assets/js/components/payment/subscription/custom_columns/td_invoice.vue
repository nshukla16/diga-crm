<template lang="pug">
    div 
        a(v-if="row.invoice_file_name" v-on:click="download()" href="#") {{ row.invoice_file_name }}
</template>

<script>

export default {
    props: ['row'],
    methods: {
        download(){
            this.$http.get('/api/payments/download_invoice?filename=' + this.row.invoice_file,
            {responseType: 'blob'}).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.forceFileDownload(res);
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });            
        },
        forceFileDownload(response){
            const url = window.URL.createObjectURL(new Blob([response.data]))
            const link = document.createElement('a')
            link.href = url
            link.setAttribute('download', 'invoice.pdf') //or any other extension
            document.body.appendChild(link)
            link.click()
        },
    },
}
</script>