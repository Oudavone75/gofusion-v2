document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.getElementById('file-input');
    const dropzone = document.getElementById('dropzone-area');
    const filePreview = document.getElementById('file-preview');
    let selectedFiles = []; // Store files in array
    let isProcessing = false; // Prevent double trigger

    // Preview existing attachments
    document.querySelectorAll(".preview-btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.stopPropagation();
            e.preventDefault();
            let card = this.closest(".attachment-card");
            let type = card.dataset.type;
            let path = card.dataset.path;
            let name = card.dataset.name;

            showPreview(type, path, name);
        });
    });

    // Dropzone click to open file dialog
    dropzone.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (!isProcessing) {
            fileInput.click();
        }
    });

    // Drag and drop events
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('dragover');

        const files = Array.from(e.dataTransfer.files);
        handleFiles(files);
    });

    // File input change event
    fileInput.addEventListener('change', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (isProcessing) return;

        isProcessing = true;
        const files = Array.from(this.files);

        if (files.length > 0) {
            handleFiles(files);
        }

        // Don't reset the input value - it will be managed by updateFileInput
        setTimeout(() => {
            isProcessing = false;
        }, 500);
    });

    // Handle files function
    function handleFiles(files) {
        let addedCount = 0;

        files.forEach(file => {
            // Check file size (50MB limit)
            if (file.size > 50 * 1024 * 1024) {
                alert(`File "${file.name}" exceeds 50MB limit`);
                return;
            }

            // Create a unique identifier for the file using name, size, type, and lastModified
            const fileId = `${file.name}_${file.size}_${file.type}_${file.lastModified}`;

            // Check if this exact file already exists
            const exists = selectedFiles.some(f => {
                const existingId = `${f.name}_${f.size}_${f.type}_${f.lastModified}`;
                return existingId === fileId;
            });

            if (!exists) {
                selectedFiles.push(file);
                addedCount++;
            } else {
                console.log(`File "${file.name}" already selected, skipping...`);
            }
        });

        if (addedCount > 0) {
            updateFileInput();
            renderFilePreviews();
        }
    }

    // Update the actual file input with selected files
    function updateFileInput() {
        try {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            fileInput.files = dataTransfer.files;
            console.log('Files updated in input:', fileInput.files.length);
        } catch (error) {
            console.error('Error updating file input:', error);
        }
    }

    // Render file previews
    function renderFilePreviews() {
        if (selectedFiles.length === 0) {
            filePreview.innerHTML = '';
            return;
        }

        filePreview.innerHTML = `
                    <div class="alert alert-success d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fa fa-check-circle mr-2"></i>
                            <strong>${selectedFiles.length}</strong> file(s) selected for upload
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAllFiles()">
                            <i class="fa fa-times mr-1"></i>Clear All
                        </button>
                    </div>
                    <div class="row" id="preview-container"></div>
                `;

        const previewContainer = document.getElementById('preview-container');

        selectedFiles.forEach((file, index) => {
            const fileType = getFileType(file);
            const fileIcon = getFileIcon(fileType);
            const fileBadge = getFileBadge(fileType);

            const col = document.createElement('div');
            col.className = 'col-lg-3 col-md-4 col-sm-6 mb-4';
            col.setAttribute('data-file-index', index);

            col.innerHTML = `
                        <div class="card shadow-sm border-0 file-preview-card h-100">
                            <span class="remove-file-btn" onclick="removeFile(${index})">
                                <i class="fa fa-times"></i>
                            </span>
                            <div class="card-body text-center p-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <div class="icon-wrapper mb-3">
                                    <i class="${fileIcon}" style="font-size: 4rem;"></i>
                                </div>
                                <span class="badge ${fileBadge} mb-2">${fileType.toUpperCase()}</span>
                                <h6 class="text-dark font-weight-bold mt-2 mb-1 text-truncate"
                                    title="${file.name}" style="font-size: 0.9rem;">
                                    ${file.name}
                                </h6>
                                <p class="text-muted small mb-0">${formatFileSize(file.size)}</p>
                            </div>
                        </div>
                    `;

            previewContainer.appendChild(col);
        });
    }

    // Remove file function
    window.removeFile = function (index) {
        const fileName = selectedFiles[index].name;
        selectedFiles.splice(index, 1);
        updateFileInput();
        renderFilePreviews();
        console.log(`"${fileName}" removed`);
    };

    // Clear all files function
    window.clearAllFiles = function () {
        if (confirm('Are you sure you want to remove all selected files?')) {
            selectedFiles = [];
            updateFileInput();
            renderFilePreviews();
            console.log('All files cleared');
        }
    };

    // Get file type
    function getFileType(file) {
        const type = file.type;
        if (type.startsWith('image/')) return 'image';
        if (type.startsWith('video/')) return 'video';
        if (type === 'application/pdf') return 'pdf';
        return 'document';
    }

    // Get file icon
    function getFileIcon(type) {
        switch (type) {
            case 'image': return 'fa fa-file-image-o text-warning';
            case 'video': return 'fa fa-file-video-o text-info';
            case 'pdf': return 'fa fa-file-pdf-o text-danger';
            default: return 'fa fa-file-text-o text-primary';
        }
    }

    // Get file badge
    function getFileBadge(type) {
        switch (type) {
            case 'image': return 'badge-warning';
            case 'video': return 'badge-info';
            case 'pdf': return 'badge-danger';
            default: return 'badge-primary';
        }
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Show preview in modal
    function showPreview(type, path, name) {
        document.getElementById("previewFileName").innerText = name;
        document.getElementById("downloadLink").href = path;
        document.getElementById("downloadLink").download = name;

        let previewArea = document.getElementById("previewContent");

        // Show loading
        previewArea.innerHTML = `
                    <div class="d-flex justify-content-center align-items-center" style="height: 500px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                `;

        $('#previewModal').modal('show');

        // Load content
        setTimeout(() => {
            if (type === 'image') {
                previewArea.innerHTML = `
                            <div class="text-center p-4 img-modal-wrapper">
                                <div class="modal-img-container">
                                    <img src="${path}" class="img-fluid rounded" alt="${name}">
                                </div>
                            </div>
                        `;
            } else if (type === 'video') {
                previewArea.innerHTML = `
                            <div class="p-4">
                                <video controls autoplay class="w-100 rounded" style="max-height: 600px;">
                                    <source src="${path}">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        `;
            } else if (type === 'pdf') {
                previewArea.innerHTML = `
                            <iframe src="${path}" class="w-100 border-0" style="height:600px;"></iframe>
                        `;
            } else {
                previewArea.innerHTML = `
                            <div class="text-center p-5">
                                <i class="fa fa-file-o fa-5x text-muted mb-4"></i>
                                <p class="text-muted h5">Preview not available for this file type.</p>
                                <a href="${path}" target="_blank" class="btn btn-primary btn-lg mt-4">
                                    <i class="fa fa-download mr-2"></i>Download File
                                </a>
                            </div>
                        `;
            }
        }, 300);
    }

    // Clear modal on close
    $('#previewModal').on('hidden.bs.modal', function () {
        document.getElementById("previewContent").innerHTML = `
                    <div class="d-flex justify-content-center align-items-center" style="height: 500px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                `;
    });

    // Debug: Log before form submit
    document.getElementById('post-form').addEventListener('submit', function (e) {
        console.log('Form submitting with files:', fileInput.files.length);
        console.log('Selected files array:', selectedFiles.length);
    });
});
