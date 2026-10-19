{{-- [PARTIAL] sparepart_master.modals.preview — bagian dari sparepart_master.blade.php (baris asli 423-505) — Modal preview hasil upload --}}
<!-- ==========================================================
     MODAL PREVIEW
========================================================== -->

<div class="modal fade"
    id="smPreviewModal"
    tabindex="-1">


    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Preview Data Sparepart
                </h5>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>


            <div class="modal-body">

                <p id="smSummaryText"
                    class="mb-3">
                </p>


                <div id="smPreviewTableWrap">

                    <table class="table table-bordered">

                        <thead>

                            <tr>
                                <th>No.</th>
                                <th>Part Number</th>
                                <th>Deskripsi</th>
                                <th>Divisi</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                            </tr>

                        </thead>

                        <tbody id="smPreviewTbody">
                        </tbody>

                    </table>

                </div>

            </div>


            <div class="modal-footer">

                <button type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Batal
                </button>

                <button type="button"
                    class="btn btn-primary"
                    id="smConfirmBtn">
                    ✅ Konfirmasi &amp; Simpan Part Baru
                </button>

            </div>

        </div>

    </div>


</div>
