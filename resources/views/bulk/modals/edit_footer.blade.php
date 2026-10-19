{{-- [PARTIAL] bulk.modals.edit_footer — bagian dari bulk.blade.php (baris asli 1624-1684) — Modal edit footer --}}
    <!-- MODAL EDIT FOOTER -->
    <div class="modal fade" id="footerModal" tabindex="-1">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <form method="POST" id="editFooterForm">

                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">
                            Edit Footer
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">
                                Nama Footer
                            </label>

                            <input type="text" name="footer_name" id="edit_footer_name" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Isi Footer
                            </label>

                            <textarea name="footer_html" id="edit_footer_html" rows="10"
                                class="form-control"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>

                        <button type="submit" class="btn btn-success">
                            Simpan Perubahan
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
