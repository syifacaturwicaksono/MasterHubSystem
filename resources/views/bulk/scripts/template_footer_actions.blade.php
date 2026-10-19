{{-- [PARTIAL] bulk.scripts.template_footer_actions — bagian dari bulk.blade.php (baris asli 1931-2182) — Script edit/hapus template & footer, kirim email --}}
    <script>
        document
            .getElementById('btnEditTemplate')
            .addEventListener('click', function() {

                let select =
                    document.getElementById('templateSelect');

                if (select.value === '') {

                    alert('Pilih template terlebih dahulu');
                    return;

                }

                let option =
                    select.options[select.selectedIndex];

                document.getElementById(
                        'edit_nama_template'
                    ).value =
                    option.dataset.nama;

                document.getElementById(
                        'edit_jenis_monitoring'
                    ).value =
                    option.dataset.monitoring;

                document.getElementById(
                        'edit_subject'
                    ).value =
                    option.dataset.subject;

                document.getElementById(
                        'edit_body'
                    ).value =
                    option.dataset.body;

                if (window.jQuery && $('#edit_body').next('.note-editor').length) {
                    $('#edit_body').summernote('code', option.dataset.body);
                }

                document.getElementById(
                        'editTemplateForm'
                    ).action =
                    '/template-master/update/' +
                    option.value;

                let modal =
                    new bootstrap.Modal(
                        document.getElementById(
                            'editTemplateModal'
                        )
                    );

                modal.show();

            });
    </script>
    <script>
        document.getElementById('filterMonitoring')
            ?.addEventListener('change', function() {

                let monitoring = this.value;

                document.querySelectorAll(
                    '#templateSelect option'
                ).forEach(function(option) {

                    if (option.value === '') {
                        option.hidden = false;
                        return;
                    }

                    let jenis =
                        option.dataset.monitoring;

                    option.hidden =
                        monitoring &&
                        jenis !== monitoring;

                });

            });
    </script>
    <script>
        document.getElementById('jenisMonitoring')
            ?.addEventListener('change', function() {

                let monitoring = this.value;

                let templateSelect =
                    document.getElementById('templateSelect');

                Array.from(templateSelect.options).forEach(function(opt) {

                    if (opt.value === '') {
                        opt.hidden = false;
                        return;
                    }

                    let templateMonitoring =
                        opt.dataset.monitoring;

                    opt.hidden =
                        templateMonitoring !== monitoring;
                });

                templateSelect.value = '';
            });
    </script>

    <script>
        document
            .getElementById('btnEditFooter')
            .addEventListener('click', function() {

                let select =
                    document.getElementById('footerSelect');

                if (select.value === '') {

                    alert('Pilih footer terlebih dahulu');
                    return;
                }

                let option =
                    select.options[select.selectedIndex];

                document.getElementById(
                        'edit_footer_name'
                    ).value =
                    option.dataset.name;

                document.getElementById(
                        'edit_footer_html'
                    ).value =
                    option.dataset.footer;

                if (window.jQuery && $('#edit_footer_html').next('.note-editor').length) {
                    $('#edit_footer_html').summernote('code', option.dataset.footer);
                }

                document.getElementById(
                        'editFooterForm'
                    ).action =
                    '/footer-master/update/' +
                    option.value;

                let modal =
                    new bootstrap.Modal(
                        document.getElementById(
                            'footerModal'
                        )
                    );

                modal.show();

            });
    </script>
    <script>
        function filterTemplateByMonitoring() {

            let jenisUpload =
                document.getElementById('jenis_upload').value;

            let mapping = {

                'W001-WIP': 'W001-WIP',

                'W002-TAT14D (Pending case 14 days)': 'W002-TAT14D (Pending case 14 days)',

                'W003-TAT5D (Pending case 5 days)': 'W003-TAT5D (Pending case 5 days)',

                'W004-KCI': 'W004-KCI',

                'W005-FR (Finish Repair)': 'W005-FR (Finish Repair)',

                'M001-MPR': 'M001-MPR',

                'M002-Reminder Invoice': 'M002-Reminder Invoice',

                'M003-Pembayaran': 'M003-Pembayaran',

                'D001-KLA': 'D001-KLA',

                'M004-PO Labor': 'M004-PO Labor',

            };

            let monitoring =
                mapping[jenisUpload] || '';

            let templateSelect =
                document.getElementById('templateSelect');

            Array.from(templateSelect.options)
                .forEach(function(option) {

                    if (option.value === '') {
                        option.hidden = false;
                        return;
                    }

                    option.hidden =
                        monitoring &&
                        option.dataset.monitoring !== monitoring;

                });

            templateSelect.value = '';

        }

        document
            .getElementById('jenis_upload')
            .addEventListener(
                'change',
                filterTemplateByMonitoring
            );

        document.addEventListener(
            'DOMContentLoaded',
            filterTemplateByMonitoring
        );
    </script>
    <script>
        document.getElementById('jenis_upload').addEventListener('change', function() {

            let jenis = this.value;

            fetch('/get-batches?jenis_upload=' + encodeURIComponent(jenis))
                .then(response => response.json())
                .then(data => {

                    let batch = document.getElementById('kode_upload');

                    batch.innerHTML = '<option value="">Semua Batch</option>';

                    data.forEach(function(item) {

                        batch.innerHTML +=
                            `<option value="${item.kode_upload}">
                            ${item.kode_upload}
                        </option>`;

                    });

                });

        });
    </script>
