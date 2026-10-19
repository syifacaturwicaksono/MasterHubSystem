{{-- [PARTIAL] sparepart_master.sections.upload — bagian dari sparepart_master.blade.php (baris asli 190-223) — Area upload Excel & pesan error --}}
<!-- ==========================================================
     UPLOAD
========================================================== -->

Tamplate upload file part klik 
<a href="https://docs.google.com/spreadsheets/d/1zxcZHst82nisUg3mNP0Ae4wLPH1x3bff/edit?usp=sharing&ouid=109389621746043914440&rtpof=true&sd=true" target="_blank" rel="noopener noreferrer">disini</a>
<div class="sm-upload-card">
    
    <div class="flex-grow-1">
        <input type="file"
            id="smFileInput"
            class="form-control"
            accept=".xlsx,.xls">
        

        <div class="text-muted small mt-1">
            Upload file Excel (data harus ada di sheet bernama
            <strong>"Sheet3"</strong>,
            kolom Part Number / Part Description / Product tower / Kategori).
        </div>
        <button type="button"
            class="btn btn-primary"
            id="smUploadBtn">
            Proses &amp; Preview
        </button>
    </div>




</div>

<div id="smUploadError"
    class="alert alert-danger d-none"></div>
