<div class="modal fade" id="joinLinksModal" tabindex="-1" role="dialog" aria-labelledby="joinLinksModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(181deg, #e866ea, #4ba28e); color: #fff;">
                <h5 class="modal-title" id="joinLinksModalLabel">
                    <i class="fa fa-link"></i> Manage Join Links — <span id="joinLinksCompanyName"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                {{-- Generate New Link Section --}}
                <div class="card mb-4" style="border: 1px solid #e0e0e0; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <div class="card-body p-3">
                        <h6 class="card-title mb-3" style="font-weight: 700; color: #444;">
                            <i class="fa fa-plus-circle text-success mr-1"></i> Generate New Link
                        </h6>

                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Label <small>(optional)</small></label>
                                    <input type="text" class="form-control form-control-sm" id="tokenLabel" placeholder="e.g. March Promo" style="border-radius: 6px;">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Usage Limit <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control form-control-sm" id="tokenUsageLimit" placeholder="Max" min="1" required style="border-radius: 6px;">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Expiry Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control form-control-sm" id="tokenExpiryDate" min="{{ date('Y-m-d') }}" required style="border-radius: 6px;">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <button type="button" class="btn btn-success btn-sm btn-block font-weight-bold" id="generateTokenBtn" style="height: 38px; border-radius: 6px; box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);">
                                        <i class="fa fa-bolt"></i> Generate
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Active Links --}}
                <div class="mb-2 d-flex align-items-center justify-content-between">
                    <h6 style="font-weight: 700; color: #28a745;" class="mb-0">
                        <i class="fa fa-check-circle"></i> Active Links
                    </h6>
                </div>
                <div id="activeTokensContainer" class="pr-1" style="max-height: 300px; overflow-y: auto; border: 1px solid #f0f0f0; border-radius: 8px; background: #fafafa; padding: 10px;">
                    <div class="text-center text-muted py-3" id="activeTokensLoading">
                        <i class="fa fa-spinner fa-spin"></i> Loading...
                    </div>
                    <div id="activeTokensList"></div>
                </div>

                {{-- Expired / Revoked Links --}}
                <div class="mt-4">
                    <a data-toggle="collapse" href="#inactiveTokensCollapse" role="button" aria-expanded="false"
                        class="d-flex align-items-center justify-content-between text-muted" style="font-weight: 700; text-decoration: none; background: #eee; padding: 8px 12px; border-radius: 6px;">
                        <span><i class="fa fa-history"></i> Revoked / Expired</span>
                        <i class="fa fa-chevron-down small"></i>
                    </a>
                    <div class="collapse mt-2" id="inactiveTokensCollapse">
                        <div id="inactiveTokensList" class="pr-1" style="max-height: 200px; overflow-y: auto; padding: 5px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
