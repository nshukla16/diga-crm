const tour = {
    getTourInstance($this, jquery_doc){
        let tmp_tour = new Tour({
            backdrop: true,
            template: '<div class="popover" role="tooltip">\n' +
            '          <div class="arrow"></div>\n' +
            '          <h3 class="popover-header"></h3>\n' +
            '          <div class="popover-body"></div>\n' +
            '          <div class="popover-navigation">\n' +
            '            <div class="btn-group">\n' +
            '              <button class="btn btn-sm btn-secondary" data-role="prev">&laquo; ' + $this.$t('template.Prev') + '</button>\n' +
            '              <button class="btn btn-sm btn-secondary" data-role="next">' + $this.$t('template.Next') + ' &raquo;</button>\n' +
            '              <button class="btn btn-sm btn-secondary"\n' +
            '                      data-role="pause-resume"\n' +
            '                      data-pause-text="Pause"\n' +
            '                      data-resume-text="Resume">Pause</button>\n' +
            '            </div>\n' +
            '            <button class="btn btn-sm btn-secondary" data-role="end">' + $this.$t('template.End_tour') + '</button>\n' +
            '          </div>\n' +
            '        </div>',
            onEnd: function (tour) {
                $this.$http.post('/api/end_tour').then(res => {

                }, res => {
                    $this.$toastr.e(res.data.errmess, $this.$t("template.Error"));
                });
            },
            onRedirectError: function(tour) {
                tour.start(true);
            },
            steps: [
                {
                    title: $this.$t('template.Step0'),
                    content: $this.$t('template.Step0_info'),
                    backdrop: false,
                    orphan: true,
                    path: "/welcome",
                },
                {
                    element: "#menu-wrapper",
                    title: $this.$t('template.Step1'),
                    content: $this.$t('template.Step1_info'),
                    placement: "bottom",
                    path: "/welcome",
                },
            ],
        });

        if ($this.can_do('clients', 'read') != 0){
            tmp_tour.addSteps([
                {
                    element: "#clientsDropdown",
                    title: $this.$t('template.Step2'),
                    content: $this.$t('template.Step2_info'),
                    placement: "right",
                    backdrop: false,
                    path: "/welcome",
                },
                {
                    title: $this.$t('template.Step3'),
                    content: $this.$t('template.Step3_info'),
                    backdrop: false,
                    orphan: true,
                    path: "/clients",
                },
                {
                    title: $this.$t('template.Step4'),
                    content: $this.$t('template.Step4_info'),
                    backdrop: false,
                    orphan: true,
                    path: "/clients/1",
                },
                {
                    element: "#contact_info",
                    title: $this.$t('template.Step5'),
                    content: $this.$t('template.Step5_info'),
                    placement: "bottom",
                    path: "/clients/1",
                },
                {
                    element: "#services_block",
                    title: $this.$t('template.Step6'),
                    content: $this.$t('template.Step6_info'),
                    placement: "right",
                    path: "/clients/1",
                },
                {
                    element: "#tasks_block",
                    title: $this.$t('template.Step7'),
                    content: $this.$t('template.Step7_info'),
                    placement: "top",
                    path: "/clients/1",
                },
                {
                    element: "#history_block",
                    title: $this.$t('template.Step8'),
                    content: $this.$t('template.Step8_info'),
                    placement: "left",
                    path: "/clients/1",
                },
            ]);

            jquery_doc.on('click', '#all_clients', function (e) {
                if (tmp_tour.getCurrentStep() == 2) {
                    e.preventDefault();
                    tmp_tour.next();
                }
            });
            jquery_doc.on('click', '#test_client', function (e) {
                if (tmp_tour.getCurrentStep() == 3) {
                    e.preventDefault();
                    tmp_tour.next();
                }
            });
        }

        return tmp_tour;
    },
};

export default tour;