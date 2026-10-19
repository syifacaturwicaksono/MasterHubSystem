{{-- [PARTIAL] import.scripts.01_labor_helpers — bagian dari import.blade.php (baris asli 1400-1693) — Master kategori labor + helper HTML/harga labor (fungsi biasa, valid mandiri) --}}
        // ============================================================
        // MASTER KATEGORI LABOR
        // ============================================================

        const laborCategories = @json($laborCategories ?? []);

        // [DITAMBAHKAN] Kategori tanpa Labor: "Onsite CISS (no part)" (satu
        // kategori gabungan) dan "SARC". Muncul di
        // dropdown Kategori, harga Labor selalu kosong ("-"). Kalau tabel
        // labor_categories sudah punya kategori dengan nama yang sama, yang
        // itu yang dipakai (tetap tanpa Labor).
        const NO_LABOR_CATEGORY_NAMES = ['Onsite CISS (no part)', 'SARC'];

        NO_LABOR_CATEGORY_NAMES.forEach(name => {
            if (!laborCategories.some(
                    c => String(c.name ?? '').trim().toLowerCase() === name.toLowerCase()
                )) {
                laborCategories.push({
                    id: 'no-labor-' + name.toLowerCase(),
                    name: name,
                    psg_price: null,
                    ipg_price: null
                });
            }
        });

        // Nama kategori tanpa Labor (kalau id kategori tsb) -> nama aslinya, atau null.
        function noLaborCategoryName(categoryId) {
            const cat = laborCategories.find(
                c => String(c.id) === String(categoryId)
            );
            if (!cat) {
                return null;
            }
            const lower = String(cat.name ?? '').trim().toLowerCase();
            return NO_LABOR_CATEGORY_NAMES.find(
                n => n.toLowerCase() === lower
            ) ?? null;
        }

        // [DITAMBAHKAN] Kategori "Void Case" (tanpa Labor) ditambahkan di
        // paling bawah dropdown Kategori. Kalau di tabel labor_categories
        // sudah ada kategori bernama "Void Case", yang itu yang dipakai.
        if (!laborCategories.some(
                c => String(c.name ?? '').trim().toLowerCase() === 'void case'
            )) {
            laborCategories.push({
                id: 'void-case',
                name: 'Void Case',
                psg_price: null,
                ipg_price: null
            });
        }

        function isVoidCategoryId(categoryId) {

            const cat = laborCategories.find(
                c => String(c.id) === String(categoryId)
            );

            return !!cat &&
                String(cat.name ?? '').trim().toLowerCase() === 'void case';
        }

        // [DITAMBAHKAN] Warranty Status (kolom F sheet Transaction Report).
        // Aturan disamakan dengan ApoImport.php: Out Warranty TIDAK dapat
        // Labor, KECUALI case awalan 511 (CV. Sinar Putra Persada) yang
        // statusnya Sales Delay ("Out Warranty-SD"). Case OW lain tidak
        // dimasukkan ke popup (Langkah 1 maupun 2).
        // Ditaruh di scope atas supaya bisa dipakai parsing sheet, renderer
        // Langkah 1, dan renderer Langkah 2.
        function isOutWarrantyStatus(status) {
            return String(status || '').trim().toUpperCase()
                .startsWith('OUT WARRANTY');
        }

        function isSalesDelayWarranty(status) {
            return isOutWarrantyStatus(status) &&
                String(status || '').toUpperCase().includes('SD');
        }

        function isCaseAllowedByWarranty(caseId, status) {
            if (!isOutWarrantyStatus(status)) {
                return true;
            }
            return String(caseId || '').trim().startsWith('511') &&
                isSalesDelayWarranty(status);
        }

        function warrantyStatusHtml(status) {
            const text = String(status || '').trim();
            if (!text) {
                return '-';
            }
            const safe = text.replace(/[&<>"']/g, ch => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;',
                '"': '&quot;', "'": '&#39;'
            }[ch]));
            return isOutWarrantyStatus(text)
                ? `<span class="badge bg-warning text-dark">${safe}</span>`
                : safe;
        }

        // [DITAMBAHKAN] Case Type (kolom index 10 sheet Transaction Report)
        function safeTextHtml(value) {
            const text = String(value || '').trim();
            if (!text) {
                return '-';
            }
            return text.replace(/[&<>"']/g, ch => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;',
                '"': '&quot;', "'": '&#39;'
            }[ch]));
        }

        function caseTypeHtml(value) {
            return safeTextHtml(value);
        }

        // [DITAMBAHKAN] Case ID jadi link ke halaman service (tab baru) +
        // Serial No kecil di bawahnya (dipakai juga oleh kolom pencarian).
        function caseLinkHtml(caseId) {

            const id = String(caseId ?? '').trim();

            if (!id) {
                return '-';
            }

            return '<a href="https://myscs.co.id/app/isd/view_case.php?q=' +
                encodeURIComponent(id) +
                '" target="_blank" rel="noopener noreferrer"' +
                ' class="fw-semibold" title="Buka Case ' + id + ' di halaman service">' +
                id + '</a>';
        }

        function caseSerialSmallHtml(serial) {

            const sn = String(serial ?? '').trim();

            if (!sn) {
                return '';
            }

            const safe = sn
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            return '<div class="small text-muted">SN: ' + safe + '</div>';
        }


        // ============================================================
        // RESOLVE PART DARI DATABASE
        // ============================================================

        async function checkPartsKnown(partsList) {

            if (!partsList || partsList.length === 0) {
                return {};
            }

            const response = await fetch(
                '{{ route("upload.resolveParts") }}', {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector(
                            'meta[name="csrf-token"]'
                        )?.content ?? ''
                    },

                    body: JSON.stringify({
                        parts: partsList
                    })
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Gagal mengambil data part dari server.'
                );
            }

            return await response.json();
        }


        // ============================================================
        // HITUNG HARGA LABOR
        // ============================================================

        function computeLaborPrice(categoryId, productTower, caseId) {

            // [DITAMBAHKAN] Void Case = tanpa Labor, apapun Product Tower /
            // awalan Case ID-nya (termasuk 511).
            if (isVoidCategoryId(categoryId)) {
                return null;
            }

            // [DITAMBAHKAN] Onsite / CISS / SARC = tanpa Labor juga
            // (termasuk case awalan 511).
            if (noLaborCategoryName(categoryId)) {
                return null;
            }

            // [DITAMBAHKAN] CV. SINAR PUTRA PERSADA (case ID berawalan "511") -
            // Labor selalu flat Rp170.000, apapun kategori/Product Tower-nya.
            // Disamakan dengan aturan server di ApoImport.php supaya preview di
            // popup ini gak beda sama hasil yang benar-benar tersimpan.
            if (
                caseId &&
                String(caseId).trim().startsWith('511')
            ) {
                return 170000;
            }

            const cat = laborCategories.find(
                c => String(c.id) === String(categoryId)
            );

            if (!cat) {
                return null;
            }

            const categoryName = String(
                cat.name ?? ''
            ).trim().toLowerCase();

            // ==========================================
            // OTHER PART = Rp50.000
            // ==========================================
            if (categoryName === 'other part') {
                return 50000;
            }

            const tower = String(
                productTower ?? ''
            ).trim().toUpperCase();

            const price =
                tower === 'IPG' ?
                cat.ipg_price :
                cat.psg_price;

            return price != null ?
                Number(price) :
                null;
        }


        // ============================================================
        // FORMAT LABOR
        // ============================================================

        function formatLabor(price) {

            if (
                price === null ||
                price === undefined ||
                price === '' ||
                isNaN(Number(price))
            ) {
                return '-';
            }

            return Number(price).toLocaleString('id-ID');
        }


        // ============================================================
        // [DITAMBAHKAN] OPSI DROPDOWN KETERANGAN DI LANGKAH RR
        // Dropdown ini cuma muncul di Case ID terakhir tiap Serial No.
        // Tambah opsi baru cukup dengan menambah teks di array ini
        // (rencananya total sekitar 5 opsi).
        // ============================================================

        // [DITAMBAHKAN] Opsi "Dapat Labor": case tetap RR (hijau di Langkah 2)
        // tapi TETAP dapat Labor. Dipakai mis. kalau case pertama Cancel dan
        // case berikutnya order part / tidak Cancel.
        const RR_LABOR_OPTION = 'Dapat Labor';

        const RR_KETERANGAN_OPTIONS = [
            'Intermittent Case',
            'Customer Environment',
            'Different Issue',
            'DOA Part',
            'Cancel Repair/void',
            RR_LABOR_OPTION
        ];


