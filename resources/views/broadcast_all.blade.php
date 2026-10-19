@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <h3 class="fw-bold mb-1">
        Broadcast All
    </h3>

    <small class="text-muted d-block mb-4">
        Kirim email custom ke company mana pun yang Anda pilih
    </small>

    <!-- ★★★ DIUBAH CLAUDE: alert success duplikat DIHAPUS dari sini — layout (app.blade.php)
         sudah otomatis menampilkan session('success') untuk semua halaman, jadi sebelumnya
         muncul 2x (satu di layout, satu di sini). ★★★ -->

    @if(session('error'))

    <div class="alert alert-danger alert-dismissible fade show shadow-sm" style="border-radius:12px; font-weight:500;">
        ❌ {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    @endif

    <form id="broadcastForm" action="/broadcast-all/send" method="POST" enctype="multipart/form-data">

        @csrf

        <!-- FORM PESAN -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius:16px;">

            <div class="card-body">

                <h5 class="fw-bold mb-4">
                    Compose Pesan
                </h5>

                <!-- SUBJEK -->
                <div class="mb-3">

                    <label class="form-label">
                        Subjek Email
                    </label>

                    <input
                        type="text"
                        name="subject"
                        id="broadcastSubject"
                        class="form-control"
                        placeholder="Tulis subjek email di sini..."
                        autocomplete="off"
                        required>

                </div>

                <!-- BODY -->
                <div class="mb-3">

                    <label class="form-label">
                        Body Email
                    </label>

                    <textarea
                        name="body"
                        id="broadcastBody"
                        class="form-control"
                        rows="10"></textarea>

                </div>

                <!-- ★★★ DIUBAH CLAUDE: attachment diganti jadi custom uploader — bisa nambah
                     file bertahap (klik "Tambah File" berkali-kali, tidak menimpa file
                     sebelumnya seperti input file bawaan browser), dan tiap file punya
                     tombol ✕ buat dihapus satu-satu. ★★★ -->
                <div class="mb-3">

                    <label class="form-label">
                        Lampiran (Attachment)
                    </label>

                    <div class="border rounded p-3" style="background:#f8f9fa;">

                        <button type="button" id="btnAddAttachment" class="btn btn-outline-primary btn-sm mb-2">
                            📎 Tambah File
                        </button>

                        <input type="file" id="attachmentPicker" multiple style="display:none;">
                        <input type="file" name="attachments[]" id="attachmentsRealInput" multiple style="display:none;">

                        <div id="attachmentList" class="list-group">
                            <div class="text-muted small px-1" id="attachmentEmptyText">
                                Belum ada file dilampirkan.
                            </div>
                        </div>

                    </div>

                    <small class="text-muted d-block mt-1">
                        Bisa upload lebih dari satu file — foto, video, PDF, Word, Excel, ZIP, dll. Maks 20MB per file.
                    </small>

                </div>

                <!-- ★★★ BARU: filter kategori penerima, bentuk checkbox (mirip "Kirim Melalui").
                     Kosongkan semua = kirim ke SEMUA kategori. Centang satu/lebih untuk
                     membatasi penerima hanya ke kategori yang dicentang. Nilai dikirim
                     langsung sebagai kategori[] ke server, lalu divalidasi ulang di
                     BroadcastController::send() supaya benar-benar membatasi penerima. ★★★ -->
                <div class="mb-3">

                    <label class="form-label">
                        Filter Kategori Penerima
                    </label>

                    <div class="d-flex gap-4 flex-wrap">

                        <div class="form-check">
                            <input class="form-check-input kategori-checkbox" type="checkbox" name="kategori[]" value="apo" id="filterApo">
                            <label class="form-check-label" for="filterApo">APO</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input kategori-checkbox" type="checkbox" name="kategori[]" value="apo1" id="filterApo1">
                            <label class="form-check-label" for="filterApo1">APO1</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input kategori-checkbox" type="checkbox" name="kategori[]" value="finance" id="filterFinance">
                            <label class="form-check-label" for="filterFinance">Finance</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input kategori-checkbox" type="checkbox" name="kategori[]" value="kla" id="filterKla">
                            <label class="form-check-label" for="filterKla">KLA</label>
                        </div>

                    </div>

                    <small class="text-muted d-block mt-1">
                        Kosongkan semua = kirim ke semua kategori. Centang satu atau lebih untuk membatasi penerima.
                    </small>

                </div>

                <div class="d-flex gap-2">

                    <button
                        type="button"
                        id="btnPreviewBroadcast"
                        class="btn btn-info text-white">

                        Preview

                    </button>

                    <button
                        type="submit"
                        id="btnSendBroadcast"
                        class="btn btn-primary">

                        Kirim

                    </button>

                </div>

            </div>

        </div>

        <!-- ★★★ DIUBAH CLAUDE: sebelumnya checklist (bisa pilih company mana saja), sekarang
             cuma tabel INFO — broadcast otomatis dikirim ke SEMUA company di bawah ini,
             tidak perlu dipilih satu-satu lagi. ★★★ -->
        <div class="card shadow-sm border-0" style="border-radius:16px;">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                    <h5 class="fw-bold mb-0">
                        Daftar Company Penerima
                    </h5>

                    <div class="d-flex gap-2">
                        <span class="badge bg-secondary px-3 py-2" id="totalCompanyBadge">
                            {{ $companies->count() }} company
                        </span>
                        <span class="badge bg-primary px-3 py-2" id="selectedCompanyBadge">
                            0 dipilih
                        </span>
                    </div>

                </div>

                <input
                    type="text"
                    id="searchCompany"
                    class="form-control mb-3"
                    placeholder="Cari nama company / kode... (cuma buat lihat, bukan filter kirim)">

                <div style="max-height:480px; overflow:auto;">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light" style="position:sticky; top:0; z-index:1;">

                            <tr>

                                {{-- ★★★ DIUBAH CLAUDE: checkbox "Pilih Semua" dihapus — sekarang seleksi
                                     company otomatis mengikuti checkbox Filter Kategori Penerima
                                     (APO/Finance/KLA) di atas, tidak perlu tombol select-all manual lagi.
                                     Kolom dikosongkan (bukan dihapus) supaya lebar kolom checkbox per
                                     baris tetap sejajar. ★★★ --}}
                                <th width="45"></th>
                                <th width="100">Kode</th>
                                <th>Company</th>
                                <th width="110">Kategori</th>
                                <th>Email</th>

                            </tr>

                        </thead>

                        <tbody id="companyTableBody">

                            @forelse($companies as $item)

                            <tr class="company-row" data-search="{{ strtolower($item->company_name.' '.$item->kode_company) }}" data-kategori="{{ strtolower($item->kategori ?? '') }}">

                                <td>
                                    <input
                                        type="checkbox"
                                        name="selected_company[]"
                                        value="{{ $item->kode_company }}"
                                        class="form-check-input company-checkbox">
                                </td>

                                <td>
                                    <span class="badge bg-dark">
                                        {{ $item->kode_company }}
                                    </span>
                                </td>

                                <td>{{ $item->company_name }}</td>

                                <td>
                                    @if(($item->kategori ?? '') === 'finance')
                                    <span class="badge bg-info text-dark">Finance</span>
                                    @elseif(($item->kategori ?? '') === 'apo')
                                    <span class="badge bg-secondary">APO</span>
                                    @elseif(($item->kategori ?? '') === 'apo1')
                                    <span class="badge bg-secondary">APO1</span>
                                    @elseif(($item->kategori ?? '') === 'kla')
                                    <span class="badge bg-warning text-dark">KLA</span>
                                    @else
                                    <span class="badge bg-light text-muted border">-</span>
                                    @endif
                                </td>

                                <td>

                                    @if(!empty($item->email))

                                    @foreach(explode(',', $item->email) as $email)

                                    <span class="badge bg-success me-1 mb-1">
                                        {{ trim($email) }}
                                    </span>

                                    @endforeach

                                    @else

                                    <span class="text-danger small">⚠ Belum ada email (akan dilewati saat kirim)</span>

                                    @endif

                                </td>

                            </tr>

                            @empty

                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Belum ada data company
                                </td>
                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </form>

</div>

<!-- MODAL PREVIEW -->
<div class="modal fade" id="broadcastPreviewModal" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content border-0" style="border-radius:18px;">

            <div class="modal-header bg-dark text-white">

                <h5 class="modal-title">
                    Preview Email
                </h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body" id="broadcastPreviewBody">
                <div class="text-center text-muted py-4">Memuat preview...</div>
            </div>

        </div>

    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>

<style>
    .modal .note-editor .dropdown-menu,
    .modal .note-editor .note-dropdown-menu {
        z-index: 3000 !important;
    }

    .modal-body {
        overflow: visible !important;
    }
</style>

<script>
    $(function() {

        // ===== SUMMERNOTE BODY =====
        $('#broadcastBody').summernote({
            height: 280,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph', 'height']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview']]
            ],
            callbacks: {
                onChange: function(contents) {
                    $('#broadcastBody').val(contents);
                }
            }
        });

        // Workaround dropdown Summernote di dalam modal (Font/Color) — sama seperti
        // yang dipasang di Template Master & Footer Master.
        document.addEventListener('click', function(e) {

            const toggle = e.target.closest('.note-editor .dropdown-toggle');

            if (!toggle) {
                if (!e.target.closest('.note-editor .dropdown-menu')) {
                    document.querySelectorAll('.note-editor .dropdown-menu.show')
                        .forEach(function(m) {
                            m.classList.remove('show');
                        });
                }
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            const menu = toggle.nextElementSibling;

            if (!menu || !menu.classList.contains('dropdown-menu')) return;

            const isOpen = menu.classList.contains('show');

            document.querySelectorAll('.note-editor .dropdown-menu.show')
                .forEach(function(m) {
                    m.classList.remove('show');
                });

            if (!isOpen) menu.classList.add('show');

        }, true);

        // ★★★ DIUBAH CLAUDE (BARU): kelola attachment manual — file dipilih ditampung di
        // array JS (tidak ke-replace tiap kali klik "Tambah File" lagi), tiap file bisa
        // dihapus satu-satu, lalu disinkronkan ke hidden input sebelum submit. ★★★
        let selectedFiles = [];
        const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        function renderAttachmentList() {

            const $list = $('#attachmentList');
            $list.find('.attachment-item').remove();

            if (selectedFiles.length === 0) {
                $('#attachmentEmptyText').show();
            } else {
                $('#attachmentEmptyText').hide();
            }

            selectedFiles.forEach(function(file, index) {

                // ★★★ BARU: kalau filenya gambar, tampilkan thumbnail asli (bukan
                // ikon 📄 generik) — pakai file.previewUrl yang sudah dibuat sekali
                // waktu file ditambahkan (lihat handler #attachmentPicker change). ★★★
                const iconHtml = file.previewUrl ?
                    `<img src="${file.previewUrl}" alt="${file.name}" style="width:40px; height:40px; object-fit:cover; border-radius:6px; margin-right:10px; flex-shrink:0;">` :
                    `<span style="display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; margin-right:10px; font-size:20px; flex-shrink:0;">📄</span>`;

                const $item = $(`
                    <div class="list-group-item d-flex justify-content-between align-items-center attachment-item">
                        <span class="d-flex align-items-center">${iconHtml}<span>${file.name} <span class="text-muted small">(${formatFileSize(file.size)})</span></span></span>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-attachment" data-index="${index}">
                            ✕ Hapus
                        </button>
                    </div>
                `);

                $list.append($item);
            });

            syncFilesToRealInput();
        }

        function syncFilesToRealInput() {

            const dataTransfer = new DataTransfer();

            selectedFiles.forEach(function(file) {
                dataTransfer.items.add(file);
            });

            document.getElementById('attachmentsRealInput').files = dataTransfer.files;
        }

        $('#btnAddAttachment').on('click', function() {
            $('#attachmentPicker').val('').click();
        });

        $('#attachmentPicker').on('change', function(e) {

            const newFiles = Array.from(e.target.files);

            newFiles.forEach(function(file) {

                if (file.size > MAX_FILE_SIZE) {
                    alert(`File "${file.name}" lebih dari 20MB, dilewati.`);
                    return;
                }

                // hindari duplikat nama+ukuran yang sama persis
                const isDuplicate = selectedFiles.some(function(f) {
                    return f.name === file.name && f.size === file.size;
                });

                if (!isDuplicate) {

                    // ★★★ BARU: kalau gambar, buat URL preview sekali di sini saja
                    // (dipakai renderAttachmentList untuk thumbnail) ★★★
                    if (file.type.startsWith('image/')) {
                        file.previewUrl = URL.createObjectURL(file);
                    }

                    selectedFiles.push(file);
                }
            });

            renderAttachmentList();
        });

        $(document).on('click', '.btn-remove-attachment', function() {

            const index = $(this).data('index');

            // ★★★ BARU: lepas URL preview gambar yang dihapus, supaya tidak
            // menumpuk di memory browser ★★★
            if (selectedFiles[index] && selectedFiles[index].previewUrl) {
                URL.revokeObjectURL(selectedFiles[index].previewUrl);
            }

            selectedFiles.splice(index, 1);
            renderAttachmentList();
        });

        // ===== FILTER KATEGORI (checkbox) + SEARCH COMPANY (gabungan) =====
        // Kategori benar-benar membatasi PENERIMA (checkbox dikirim langsung sebagai
        // kategori[] ke server), sedangkan search cuma bantu melihat/scroll (tidak
        // pengaruh ke kirim). Kosongkan semua checkbox = semua kategori tampil/terkirim.
        function getSelectedKategori() {
            return $('.kategori-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
        }

        function applyCompanyFilters() {

            const keyword = $('#searchCompany').val().toLowerCase();
            const selectedKategori = getSelectedKategori();

            let visibleCount = 0;

            $('.company-row').each(function() {

                const matchSearch = $(this).data('search').toString().includes(keyword);
                const rowKategori = ($(this).data('kategori') || '').toString();
                const matchKategori = (selectedKategori.length === 0) || selectedKategori.includes(rowKategori);

                const show = matchSearch && matchKategori;

                $(this).toggle(show);

                if (show) visibleCount++;
            });

            $('#totalCompanyBadge').text(visibleCount + ' company');

            // ★★★ DIUBAH CLAUDE: baris "auto-uncheck checkbox yang tersembunyi"
            // DIHAPUS. Sebelumnya ini bikin checkbox ikut ter-uncheck permanen
            // begitu barisnya tersembunyi (misal waktu ngetik di kolom pencarian),
            // dan TIDAK pernah tercentang lagi walau barisnya muncul lagi setelah
            // kata kunci dihapus. Sekarang kolom pencarian murni cuma
            // menyembunyikan/menampilkan baris, sama sekali tidak menyentuh status
            // checkbox — supaya bisa cari company tertentu buat di-uncheck tanpa
            // bikin seleksi company lain ikut hilang. ★★★

            updateSelectedCount();
        }

        // ★★★ BARU: hitung & tampilkan berapa company yang benar-benar dicentang
        // (ini yang menentukan siapa saja penerima broadcast) ★★★
        function updateSelectedCount() {
            const count = $('.company-checkbox:checked').length;
            $('#selectedCompanyBadge').text(count + ' dipilih');
        }

        $('#searchCompany').on('keyup', applyCompanyFilters);

        // ★★★ DIUBAH CLAUDE: checkbox "Pilih Semua" dihapus, digantikan checkbox
        // Filter Kategori Penerima ini — sekarang tiap kali salah satu kategori
        // (APO/Finance/KLA) dicentang, SEMUA baris company dengan kategori itu
        // otomatis ikut tercentang (jadi penerima). Kalau kategorinya di-uncheck
        // lagi, baris-baris kategori itu ikut ke-uncheck. User tetap bisa uncheck
        // manual satu-satu baris kalau ada company tertentu yang tidak mau
        // dikirimi, tanpa mempengaruhi baris lain. ★★★
        $('.kategori-checkbox').on('change', function() {

            const kategori = $(this).val();
            const isChecked = $(this).is(':checked');

            $('.company-row[data-kategori="' + kategori + '"] .company-checkbox')
                .prop('checked', isChecked);

            applyCompanyFilters();
        });

        // ★★★ DIUBAH CLAUDE: tiap kali 1 checkbox company diklik manual, update
        // badge jumlah dipilih saja (status "Pilih Semua" sudah tidak ada lagi) ★★★
        $(document).on('change', '.company-checkbox', function() {
            updateSelectedCount();
        });

        // ===== PREVIEW =====
        $('#btnPreviewBroadcast').on('click', function() {

            $('#broadcastPreviewModal').modal('show');
            $('#broadcastPreviewBody').html('<div class="text-center text-muted py-4">Memuat preview...</div>');

            $.post('/broadcast-all/preview', {
                _token: $('input[name="_token"]').val(),
                subject: $('#broadcastSubject').val(),
                body: $('#broadcastBody').val()
            }, function(html) {

                // ★★★ DIUBAH CLAUDE (BARU): tempel daftar lampiran di bawah preview.
                // Ini murni dari data browser (selectedFiles), tidak perlu upload dulu
                // ke server cuma buat preview. ★★★
                let attachmentHtml = '';

                if (selectedFiles.length > 0) {

                    attachmentHtml += `
                        <div style="border:1px solid #dee2e6; border-radius:12px; margin-top:14px; padding:14px 18px; background:#f8f9fa;">
                            <strong>📎 Lampiran (${selectedFiles.length} file):</strong>
                            <ul style="margin:8px 0 0; padding-left:20px;">
                    `;

                    selectedFiles.forEach(function(file) {
                        attachmentHtml += `<li>${file.name} <span class="text-muted small">(${formatFileSize(file.size)})</span></li>`;
                    });

                    attachmentHtml += `</ul></div>`;

                } else {

                    attachmentHtml = `
                        <div style="border:1px solid #dee2e6; border-radius:12px; margin-top:14px; padding:14px 18px; background:#f8f9fa; color:#6c757d;">
                            📎 Tidak ada lampiran
                        </div>
                    `;
                }

                $('#broadcastPreviewBody').html(html + attachmentHtml);

            }).fail(function() {
                $('#broadcastPreviewBody').html('<div class="alert alert-danger">Gagal memuat preview.</div>');
            });

        });

        // ★★★ DIUBAH CLAUDE: sekarang WAJIB pilih minimal 1 company (checkbox)
        // sebelum bisa kirim — tidak lagi otomatis kirim ke semua company yang
        // cocok kategori. Pesan konfirmasi menyebutkan jumlah yang BENAR-BENAR
        // dicentang, bukan cuma yang terlihat. ★★★
        $('#broadcastForm').on('submit', function(e) {

            const selectedCount = $('.company-checkbox:checked').length;

            if (selectedCount === 0) {
                alert('Pilih minimal 1 company penerima dulu (centang di tabel, atau klik "Pilih Semua").');
                e.preventDefault();
                return false;
            }

            if (!confirm('Kirim broadcast ini ke ' + selectedCount + ' company yang dicentang? Aksi ini tidak bisa dibatalkan.')) {
                e.preventDefault();
                return false;
            }
        });

        // ★★★ BARU: waktu halaman pertama dibuka, semua kategori (APO/Finance/KLA)
        // otomatis tercentang, sehingga SEMUA company juga otomatis langsung
        // tercentang duluan sebagai penerima. User tinggal uncheck kategori atau
        // baris company tertentu kalau tidak mau dikirimi kesana. ★★★
        $('.kategori-checkbox').prop('checked', true);
        $('.company-checkbox').prop('checked', true);

        // Inisialisasi saat halaman dimuat
        applyCompanyFilters();

    });
</script>

@endsection