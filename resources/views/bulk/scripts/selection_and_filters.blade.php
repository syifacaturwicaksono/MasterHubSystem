{{-- [PARTIAL] bulk.scripts.selection_and_filters — bagian dari bulk.blade.php (baris asli 1686-1789) — Script hitung checkbox terpilih, filter & aksi dasar --}}
    <script>
        // 🔥 HITUNG TOTAL CHECKBOX
        function updateSelectedCount() {

            let total =
                document.querySelectorAll(
                    '.bulk-checkbox:checked'
                ).length;

            document.getElementById('selectedCount')
                .innerText = total;
        }

        // 🔥 EVENT REALTIME
        document.addEventListener('change', function(e) {

            if (e.target.classList.contains('bulk-checkbox')) {

                updateSelectedCount();

            }

        });
    </script>

    <script>
        document.getElementById('templateSelect')
            .addEventListener('change', function() {

                let selected =
                    this.options[this.selectedIndex];

                let subject =
                    selected.getAttribute('data-subject');

                let body =
                    selected.getAttribute('data-body');

                document.getElementById('emailSubject')
                    .value = subject ?? '';

                document.getElementById('emailBody')
                    .value = body ?? '';

            });

        document.getElementById('checkAll')
            .addEventListener('change', function() {

                document.querySelectorAll('.bulk-checkbox')
                    .forEach(function(cb) {

                        cb.checked = document.getElementById('checkAll').checked;

                    });

                updateSelectedCount();

            });

        document.addEventListener('DOMContentLoaded', function() {

            updateSelectedCount();

        });
    </script>
    <script>
        document.getElementById('bulkForm')
            .addEventListener('submit', function(e) {

                let checked =
                    document.querySelectorAll(
                        '.bulk-checkbox:checked'
                    );

                let template =
                    document.getElementById(
                        'templateSelect'
                    ).value;

                if (checked.length === 0) {

                    e.preventDefault();

                    alert(
                        'Pilih minimal 1 company'
                    );

                    return;
                }

                if (template === '') {

                    e.preventDefault();

                    alert(
                        'Pilih template email'
                    );

                    return;
                }

            });
    </script>
