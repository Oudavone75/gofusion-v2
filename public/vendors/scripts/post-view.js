document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".preview-btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.stopPropagation();
            let card = this.closest(".attachment-card");
            let type = card.dataset.type;
            let path = card.dataset.path;
            let name = card.dataset.name;

            document.getElementById("previewFileName").innerText = name;
            document.getElementById("downloadLink").href = path;
            document.getElementById("downloadLink").download = name;

            let previewArea = document.getElementById("previewContent");

            // Show loading spinner
            previewArea.innerHTML = `
                        <div class="d-flex justify-content-center align-items-center" style="height: 500px;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    `;

            $('#previewModal').modal('show');

            // Load content after modal is shown
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
        });
    });

    // Also allow clicking on the entire card
    document.querySelectorAll(".attachment-card").forEach(card => {
        card.addEventListener("click", function (e) {
            if (!e.target.closest('.preview-btn')) {
                this.querySelector('.preview-btn').click();
            }
        });
    });

    // Stop video and clear content when modal closes
    $('#previewModal').on('hidden.bs.modal', function () {
        document.getElementById("previewContent").innerHTML = `
                    <div class="d-flex justify-content-center align-items-center" style="height: 500px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                `;
    });
});
