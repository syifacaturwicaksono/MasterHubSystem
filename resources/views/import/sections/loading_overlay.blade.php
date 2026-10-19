{{-- [PARTIAL] import.sections.loading_overlay — bagian dari import.blade.php (baris asli 881-915) — Overlay loading 'Processing Excel' --}}
    <!-- LOADING -->
    <div id="loadingOverlay" style="
                                    display:none;
                                    position:fixed;
                                    top:0;
                                    left:0;
                                    width:100%;
                                    height:100%;
                                    background:rgba(0,0,0,0.5);
                                    z-index:9999;
                                    justify-content:center;
                                    align-items:center;
                                ">

        <div style="
                                        background:white;
                                        padding:30px;
                                        border-radius:16px;
                                        text-align:center;
                                        width:300px;
                                    ">

            <div class="spinner-border text-primary"></div>

            <h5 class="mt-3">
                Processing Excel...
            </h5>

            <small class="text-muted">
                Mohon tunggu sebentar
            </small>

        </div>

    </div>
