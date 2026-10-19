{{-- [PARTIAL] bulk.assets.summernote — bagian dari bulk.blade.php (baris asli 2184-2249) — CSS/JS Summernote + jQuery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">

    <style>
        /* Dropdown Summernote di dalam modal */
        .modal .note-editor .dropdown-menu,
        .modal .note-editor .note-dropdown-menu {
            z-index: 3000 !important;
            max-height: 250px !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        /* Khusus Font Name */
        .modal .note-fontname .dropdown-menu {
            max-height: 250px !important;
            overflow-y: auto !important;
        }

        /* Khusus Font Size */
        .modal .note-fontsize .dropdown-menu {
            max-height: 220px !important;
            overflow-y: auto !important;
        }

        /* Scrollbar */
        .modal .dropdown-menu::-webkit-scrollbar {
            width: 8px;
        }

        .modal .dropdown-menu::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .modal .dropdown-menu::-webkit-scrollbar-thumb {
            background: #999;
            border-radius: 10px;
        }

        .modal .dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: #666;
        }

        .modal-body {
            overflow: visible !important;
        }

        .modal .note-editor {
            position: relative;
            z-index: 1;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>

    <style>
        /* Default editor tanpa spacing */
        .note-editor .note-editable p {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .note-editor .note-editable {
            line-height: 1.2;
        }
    </style>

