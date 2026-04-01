/**
 * Company Join Token Management System
 * Handles generation, listing, revoking, and copying of secure join tokens.
 */

// Robust Copy function that works in both secure (HTTPS) and non-secure (HTTP/Localhost) contexts
function copyToClipboard(text) {
    if (!text) return Promise.reject("Empty text");

    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
    } else {
        let textArea = document.createElement("textarea");
        textArea.value = text;

        // Styling: Invisible but technically 'visible' to browser
        textArea.style.position = "fixed";
        textArea.style.left = "0";
        textArea.style.top = "0";
        textArea.style.width = "2em";
        textArea.style.height = "2em";
        textArea.style.padding = "0";
        textArea.style.border = "none";
        textArea.style.outline = "none";
        textArea.style.boxShadow = "none";
        textArea.style.background = "transparent";
        textArea.style.zIndex = "-9999"; // Behind everything

        // Append inside the modal to avoid focus-trapping issues
        let container = document.querySelector('.modal.show .modal-body') || document.body;
        container.appendChild(textArea);

        textArea.focus();
        textArea.select();
        textArea.setSelectionRange(0, 99999);

        return new Promise((res, rej) => {
            try {
                let successful = document.execCommand('copy');
                successful ? res() : rej("Copy command failed");
            } catch (err) {
                rej(err);
            }
            container.removeChild(textArea);
        });
    }
}

// Global variable to hold the current company ID (set by Blade)
var joinTokenCurrentCompanyId = null;
var joinTokenConfig = {
    isSuperAdmin: false,
    generateUrl: '',
    listUrl: '',
    revokeUrl: '',
    csrfToken: ''
};

function initJoinTokenManager(config) {
    joinTokenConfig = Object.assign(joinTokenConfig, config);
}

// Open Manage Join Links Modal
$(document).on('click', '.manage-join-links', function(e) {
    e.preventDefault();
    var btn = $(this);
    joinTokenCurrentCompanyId = btn.data('company-id');
    var companyName = btn.data('company-name');

    $('#joinLinksCompanyName').text(companyName || '');
    $('#tokenLabel').val('');
    $('#tokenUsageLimit').val('');
    $('#tokenExpiryDate').val('');

    loadTokens();
    $('#joinLinksModal').modal('show');
});

// Generate Token
$('#generateTokenBtn').on('click', function() {
    var btn = $(this);
    var label = $('#tokenLabel').val();
    var usageLimit = $('#tokenUsageLimit').val();
    var expiry = $('#tokenExpiryDate').val();

    // Basic Validation
    if (!usageLimit || !expiry) {
        var msg = 'Please provide both usage limit and expiry date.';
        if (typeof errorMessage === 'function') errorMessage(msg);
        else if (typeof Swal !== 'undefined') Swal.fire('Wait!', msg, 'warning');
        return;
    }

    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    var url = joinTokenConfig.generateUrl;
    if (joinTokenConfig.isSuperAdmin) {
        url = url.replace(':id', joinTokenCurrentCompanyId);
    }

    $.ajax({
        url: url,
        method: 'POST',
        data: {
            _token: joinTokenConfig.csrfToken,
            label: label,
            usage_limit: usageLimit,
            expiry: expiry
        },
        success: function(response) {
            if (response.success) {
                if (typeof suceessMessage === 'function') suceessMessage('Join link generated!');
                else if (typeof Swal !== 'undefined') {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Join link generated!', showConfirmButton: false, timer: 2000 });
                }
                $('#tokenLabel').val('');
                $('#tokenUsageLimit').val('');
                $('#tokenExpiryDate').val('');
                loadTokens();
            }
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Failed to generate token';
            if (typeof errorMessage === 'function') errorMessage(msg);
            else if (typeof Swal !== 'undefined') Swal.fire('Error', msg, 'error');
        },
        complete: function() {
            btn.prop('disabled', false).html('<i class="fa fa-bolt"></i> Generate');
        }
    });
});

// Load Tokens
function loadTokens() {
    $('#activeTokensLoading').show();
    $('#activeTokensList').empty();
    $('#inactiveTokensList').empty();

    var url = joinTokenConfig.listUrl;
    if (joinTokenConfig.isSuperAdmin) {
        url = url.replace(':id', joinTokenCurrentCompanyId);
    }

    $.ajax({
        url: url,
        method: 'GET',
        success: function(response) {
            $('#activeTokensLoading').hide();
            if (response.success) {
                renderTokens(response.data.active, '#activeTokensList', true);
                renderTokens(response.data.inactive, '#inactiveTokensList', false);

                if (response.data.active.length === 0) {
                    $('#activeTokensList').html('<div class="text-center text-muted py-2">No active links yet.</div>');
                }
                if (response.data.inactive.length === 0) {
                    $('#inactiveTokensList').html('<div class="text-center text-muted py-2">No history.</div>');
                }
            }
        },
        error: function() {
            $('#activeTokensLoading').hide();
            $('#activeTokensList').html('<div class="text-center text-danger py-2">Failed to load tokens.</div>');
        }
    });
}

// Render Token Cards
function renderTokens(tokens, containerSelector, isActive) {
    var container = $(containerSelector);
    tokens.forEach(function(token) {
        var statusBadge = '';
        if (token.status === 'active') {
            statusBadge = '<span class="badge badge-success">Active</span>';
        } else if (token.status === 'expired') {
            statusBadge = '<span class="badge badge-warning">Expired</span>';
        } else {
            statusBadge = '<span class="badge badge-danger">Revoked</span>';
        }

        var expiryText = token.expires_at ? token.expires_at : 'Never';
        var labelText = token.label ? token.label : '<span class="text-muted">No label</span>';

        // Usage Stats & Registration Tracking
        var statsHtml = `<i class="fa fa-mouse-pointer" title="Clicks"></i> ${token.usage_count}`;
        statsHtml += ` &nbsp;|&nbsp; <i class="fa fa-user-plus" title="Registrations"></i> ${token.registration_count || 0}`;
        
        if (token.usage_limit) {
            statsHtml += ` / ${token.usage_limit}`;
            // Limit applies to registered users
            if ((token.registration_count || 0) >= token.usage_limit) {
                statusBadge = '<span class="badge badge-secondary">Limit Reached</span>';
            }
        }

        var actionsHtml = '';
        if (isActive && !(token.usage_limit && (token.registration_count || 0) >= token.usage_limit)) {
            actionsHtml = `
                <button class="btn btn-sm btn-outline-primary copy-token-link" data-url="${token.url}" title="Copy Link">
                    <i class="fa fa-copy"></i>
                </button>
                <button class="btn btn-sm btn-outline-info show-qr-code" data-url="${token.url}" data-label="${token.label || 'Join Link'}" title="QR Code">
                    <i class="fa fa-qrcode"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger revoke-token" data-id="${token.id}" title="Revoke">
                    <i class="fa fa-ban"></i>
                </button>
            `;
        } else if (isActive) {
             // Limit reached but still active
             actionsHtml = `
                <button class="btn btn-sm btn-outline-danger revoke-token" data-id="${token.id}" title="Revoke">
                    <i class="fa fa-ban"></i>
                </button>
            `;
        }

        var card = `
            <div class="d-flex align-items-center justify-content-between p-2 mb-2" style="background: ${isActive ? '#f8f9fa' : '#fff5f5'}; border-radius: 6px; border: 1px solid #eee;">
                <div style="flex: 1; min-width: 0;">
                    <div class="d-flex align-items-center mb-1">
                        ${statusBadge}
                        <strong class="ml-2" style="font-size: 13px;">${labelText}</strong>
                    </div>
                    <div class="text-muted" style="font-size: 11px;">
                        <i class="fa fa-clock-o"></i> ${expiryText}
                        &nbsp;|&nbsp; <i class="fa fa-bar-chart"></i> ${statsHtml}
                    </div>
                </div>
                <div class="ml-2 d-flex" style="gap: 4px;">
                    ${actionsHtml}
                </div>
            </div>
        `;
        container.append(card);
    });
}

// Copy Token Link Handler
$(document).on('click', '.copy-token-link', function(e) {
    e.preventDefault();
    var btn = $(this).closest('.copy-token-link');
    var url = btn.attr('data-url');

    copyToClipboard(url).then(function() {
        if (typeof suceessMessage === 'function') suceessMessage('Join link copied!');
        else if (typeof Swal !== 'undefined') {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Join link copied!', showConfirmButton: false, timer: 2000 });
        }
    }).catch(function(err) {
        var msg = 'Could not copy to clipboard. Please copy it manually.';
        if (typeof errorMessage === 'function') errorMessage(msg);
        else if (typeof Swal !== 'undefined') Swal.fire('Error', msg, 'error');
    });
});

// Show QR Code Handler
$(document).on('click', '.show-qr-code', function() {
    var url = $(this).attr('data-url');
    var label = $(this).attr('data-label');
    $('#qrCodeContainer').empty();
    $('#qrCodeLabel').text(label);

    if (typeof QRCode !== 'undefined') {
        new QRCode(document.getElementById('qrCodeContainer'), {
            text: url,
            width: 200,
            height: 200,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel ? QRCode.CorrectLevel.H : 1
        });
        $('#qrCodeModal').modal('show');
    } else {
        console.error('QRCode library not loaded!');
    }
});

// Download QR Code as PNG
$('#downloadQrBtn').on('click', function() {
    var canvas = $('#qrCodeContainer canvas')[0];
    if (!canvas) {
        var img = $('#qrCodeContainer img')[0];
        if (img) {
            var link = document.createElement('a');
            link.download = 'join-link-qr.png';
            link.href = img.src;
            link.click();
        }
        return;
    }
    var link = document.createElement('a');
    link.download = 'join-link-qr.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
});

// Revoke Token Handler
$(document).on('click', '.revoke-token', function() {
    var tokenId = $(this).data('id');
    var confirmMsg = 'Revoke this link? This link will stop working immediately. This cannot be undone.';

    var performRevoke = function() {
        $.ajax({
            url: joinTokenConfig.revokeUrl.replace(':id', tokenId),
            method: 'POST',
            data: { _token: joinTokenConfig.csrfToken },
            success: function(response) {
                if (response.success) {
                    if (typeof suceessMessage === 'function') suceessMessage('Token revoked!');
                    else if (typeof Swal !== 'undefined') {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Token revoked!', showConfirmButton: false, timer: 2000 });
                    }
                    loadTokens();
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'Failed to revoke token';
                if (typeof errorMessage === 'function') errorMessage(msg);
                else if (typeof Swal !== 'undefined') Swal.fire('Error', msg, 'error');
            }
        });
    };

    if (typeof Swal !== 'undefined' && joinTokenConfig.isSuperAdmin) {
        Swal.fire({
            title: 'Revoke this link?',
            text: confirmMsg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, revoke it!'
        }).then(function(result) {
            if (result.isConfirmed) performRevoke();
        });
    } else {
        if (confirm(confirmMsg)) performRevoke();
    }
});
