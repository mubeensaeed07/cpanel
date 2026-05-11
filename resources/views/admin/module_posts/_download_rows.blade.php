<div id="download-rows" class="mb-3">
    @foreach($rows as $i => $row)
        <div class="download-row card custom-card mb-2 p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Label</label>
                    <input type="text" name="downloads[{{ $i }}][label]" value="{{ old('downloads.'.$i.'.label', $row['label'] ?? '') }}" class="form-control" placeholder="e.g. 1080p, Mirror 1">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Download URL <span class="text-danger">*</span></label>
                    <input type="text" name="downloads[{{ $i }}][file_url]" value="{{ old('downloads.'.$i.'.file_url', $row['file_url'] ?? '') }}" class="form-control" placeholder="https://... or direct link">
                </div>
                <div class="col-md-3">
                    <label class="form-label">File name (optional)</label>
                    <input type="text" name="downloads[{{ $i }}][file_name]" value="{{ old('downloads.'.$i.'.file_name', $row['file_name'] ?? '') }}" class="form-control" placeholder="file name">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-download-row" title="Remove">&times;</button>
                </div>
            </div>
        </div>
    @endforeach
</div>
<button type="button" class="btn btn-outline-secondary btn-sm" id="add-download-row">
    <i class="bx bx-plus"></i> Add download link
</button>

<script>
(function () {
    const container = document.getElementById('download-rows');
    const addBtn = document.getElementById('add-download-row');
    if (!container || !addBtn) return;

    function nextIndex() {
        return container.querySelectorAll('.download-row').length;
    }

    addBtn.addEventListener('click', function () {
        const i = nextIndex();
        const div = document.createElement('div');
        div.className = 'download-row card custom-card mb-2 p-3';
        div.innerHTML = `
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Label</label>
                    <input type="text" name="downloads[${i}][label]" class="form-control" placeholder="e.g. 1080p, Mirror 1">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Download URL <span class="text-danger">*</span></label>
                    <input type="text" name="downloads[${i}][file_url]" class="form-control" placeholder="https://... or direct link">
                </div>
                <div class="col-md-3">
                    <label class="form-label">File name (optional)</label>
                    <input type="text" name="downloads[${i}][file_name]" class="form-control" placeholder="file name">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-download-row" title="Remove">&times;</button>
                </div>
            </div>`;
        container.appendChild(div);
    });

    container.addEventListener('click', function (e) {
        if (e.target.closest('.remove-download-row')) {
            const rows = container.querySelectorAll('.download-row');
            if (rows.length <= 1) return;
            e.target.closest('.download-row').remove();
        }
    });
})();
</script>
