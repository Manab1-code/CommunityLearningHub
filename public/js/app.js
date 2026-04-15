/* Community Learning Hub - vanilla JS */
document.addEventListener('DOMContentLoaded', function () {
    // Photo preview (update profile)
    var photoInput = document.getElementById('photo-input');
    if (photoInput) {
        photoInput.addEventListener('change', function (e) {
            var file = e.target.files && e.target.files[0];
            if (file) {
                var url = URL.createObjectURL(file);
                var preview = document.getElementById('photo-preview');
                if (preview) preview.src = url;
            }
        });
    }
    // Status character count
    var statusField = document.querySelector('textarea[name="status"]');
    if (statusField) {
        statusField.addEventListener('input', function () {
            var countEl = document.getElementById('status-count');
            if (countEl) countEl.textContent = this.value.length;
        });
    }
});
