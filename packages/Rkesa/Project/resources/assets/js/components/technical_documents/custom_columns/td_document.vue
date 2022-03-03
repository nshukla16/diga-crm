<template lang="pug">
    span.clickable.clickable-link(v-on:click="download_files(row.document_file, row.document_file_name)") {{ row.document_file_name }}
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    props: ['row'],
    computed: {

    },
    methods: {
        forceFileDownload(response, name){
            let link_to_format = response.url.split('.');
            let format = link_to_format.pop();
            let file_name = name + '.' + format;
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', file_name);
            document.body.appendChild(link);
            link.click();
        },
        download_files(url, file_name) {
            this.$http({
                method: 'get',
                url: url,
                responseType: 'arraybuffer',
            })
                .then(response => {
                    this.forceFileDownload(response, file_name)
                })
                .catch(() => console.log('error occurred'))
        },
    }
}
</script>