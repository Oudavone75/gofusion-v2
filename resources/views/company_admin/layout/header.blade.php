<div class="header">
    <div class="header-left">
        <div class="menu-icon dw dw-menu"></div>
        <div class="company-code-container">
            <span class="company-code">Company Code: </span>
            <span id="companyCode">{{ Auth::user()->company->code }}</span>
            <button class="copy-code-btn ml-1" onclick="copyCompanyCode()" title="Copy Company Code">
                <i class="dw dw-copy"></i>
            </button>

            <span class="company-code ml-4">Company Join Link: </span>
            <button class="copy-code-btn manage-join-links ml-1" data-company-id="{{ Auth::user()->company_id }}"
                data-company-name="{{ Auth::user()->company->name }}" title="Manage Join Links">
                <i class="dw dw-link" style="font-size: 15px;"></i>
            </button>
        </div>
    </div>
    <div class="header-right">
        <div class="user-info-dropdown">
            <div class="dropdown">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <span class="user-icon">
                        <img class="profile-image"
                            src="{{ asset(auth('web')->user()->company->image ?? 'vendors/images/admin/emptyuser.jpg') }}"
                            alt="">
                    </span>
                    <span class="user-name">{{ auth('web')->user()->full_name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                    <a class="dropdown-item" href="{{ route('company_admin.profile.index') }}">
                        <i class="dw dw-user1"></i> Profile
                    </a>
                    <a class="dropdown-item" href="{{ route('company_admin.change.index') }}">
                        <i class="icon-copy dw dw-password"></i> Change Password
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('company_admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="dw dw-logout"></i> Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Manage Join Links Modal --}}
@include('modals.join-links-modal')

{{-- QR Code Download Modal --}}
@include('modals.qr-code-modal')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="{{ asset('vendors/scripts/company-join-token.js') }}"></script>
<script>
    function copyCompanyCode() {
        const companyCode = document.getElementById('companyCode');
        const range = document.createRange();
        range.selectNode(companyCode);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
        suceessMessage('Company code copied to clipboard: ' + companyCode.textContent);
    }

    $(document).ready(function() {
        initJoinTokenManager({
            isSuperAdmin: false,
            generateUrl: "{{ route('company_admin.join-links.generate') }}",
            listUrl: "{{ route('company_admin.join-links.list') }}",
            revokeUrl: '/company/join-links/revoke/:id',
            csrfToken: "{{ csrf_token() }}"
        });
    });
</script>
@endpush
