import $ from 'jquery';
window.$ = window.jQuery = $;

$(function () {
    // Attach CSRF token to every AJAX request
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
    });

    const $input    = $('#file-input');
    const $zone     = $('#upload-zone');
    const $progress = $('#upload-progress');
    const $bar      = $('#upload-bar');
    const $percent  = $('#upload-percent');
    const $filename = $('#upload-filename');
    const $error    = $('#upload-error');

    function resetUI() {
        $progress.addClass('hidden');
        $bar.css('width', '0%');
        $percent.text('0%');
        $filename.text('');
        $error.addClass('hidden').text('');
        $input.val('');
    }

    function showError(message) {
        $error.removeClass('hidden').text(message);
        $progress.addClass('hidden');
    }

    function uploadFile(file) {
        resetUI();
        $filename.text(file.name);
        $progress.removeClass('hidden');

        const formData = new FormData();
        formData.append('file', file);

        $.ajax({
            url: '/files',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            xhr: function () {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        const pct = Math.round((e.loaded / e.total) * 100);
                        $bar.css('width', pct + '%');
                        $percent.text(pct + '%');
                    }
                });
                return xhr;
            },
            success: function () {
                window.location.href = '/files';
            },
            error: function (xhr) {
                const json = xhr.responseJSON;
                let msg = 'Upload failed. Please try again.';
                if (json && json.errors && json.errors.file && json.errors.file[0]) {
                    msg = json.errors.file[0];
                } else if (json && json.message) {
                    msg = json.message;
                }
                showError(msg);
            },
        });
    }

    // Trigger upload when user selects a file via the button
    $input.on('change', function () {
        if (this.files && this.files[0]) {
            uploadFile(this.files[0]);
        }
    });

    // Drag-and-drop support
    $zone.on('dragover', function (e) {
        e.preventDefault();
        $(this).addClass('border-indigo-400 bg-indigo-50');
    });

    $zone.on('dragleave', function (e) {
        e.preventDefault();
        $(this).removeClass('border-indigo-400 bg-indigo-50');
    });

    $zone.on('drop', function (e) {
        e.preventDefault();
        $(this).removeClass('border-indigo-400 bg-indigo-50');
        const file = e.originalEvent.dataTransfer.files[0];
        if (file) {
            uploadFile(file);
        }
    });
});