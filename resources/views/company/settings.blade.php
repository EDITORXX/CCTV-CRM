@extends('layouts.app')

@section('title', 'Company Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-building-gear me-2"></i>Company Settings</h4>
        <p class="text-muted mb-0">Manage your company profile, contact details and billing preferences</p>
    </div>
</div>

<form action="{{ route('company.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Company Profile --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold"><i class="bi bi-building me-2 text-primary"></i>Company Profile</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name"
                           value="{{ old('name', $company->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                               id="phone" name="phone"
                               value="{{ old('phone', $company->phone) }}" placeholder="9876543210">
                    </div>
                    @error('phone')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email"
                               value="{{ old('email', $company->email) }}" placeholder="info@company.com">
                    </div>
                    @error('email')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="website" class="form-label">Website</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-globe"></i></span>
                        <input type="text" class="form-control @error('website') is-invalid @enderror"
                               id="website" name="website"
                               value="{{ old('website', $company->website ?? '') }}" placeholder="www.company.com">
                    </div>
                    @error('website')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="gstin" class="form-label">GSTIN</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-receipt-cutoff"></i></span>
                        <input type="text" class="form-control @error('gstin') is-invalid @enderror"
                               id="gstin" name="gstin"
                               value="{{ old('gstin', $company->gstin) }}" maxlength="15" placeholder="22AAAAA0000A1Z5">
                    </div>
                    @error('gstin')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="logo" class="form-label">Company Logo</label>
                    <input type="file" class="form-control @error('logo') is-invalid @enderror"
                           id="logo" name="logo" accept="image/*">
                    @error('logo')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    @if($company->logo_path)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $company->logo_path) }}" alt="Logo"
                                 class="img-thumbnail" style="max-height: 60px;">
                            <small class="text-muted ms-2">Upload new file to replace</small>
                        </div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label for="signature" class="form-label">Digital Signature</label>
                    <input type="file" class="form-control @error('signature') is-invalid @enderror"
                           id="signature" name="signature" accept="image/*">
                    @error('signature')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    @if($company->signature_path)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $company->signature_path) }}" alt="Signature"
                                 class="img-thumbnail" style="max-height: 70px;">
                            <small class="text-muted ms-2">Upload new file to replace</small>
                        </div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label for="stamp" class="form-label">Company Stamp</label>
                    <input type="file" class="form-control @error('stamp') is-invalid @enderror"
                           id="stamp" name="stamp" accept="image/*">
                    @error('stamp')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    @if($company->stamp_path)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $company->stamp_path) }}" alt="Stamp"
                                 class="img-thumbnail" style="max-height: 90px;">
                            <small class="text-muted ms-2">Upload new file to replace</small>
                        </div>
                    @endif
                </div>

                <div class="col-12">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror"
                              id="address" name="address" rows="3" placeholder="Full company address...">{{ old('address', $company->address) }}</textarea>
                    @error('address')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Billing & Invoice Settings --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold"><i class="bi bi-gear me-2 text-primary"></i>Billing & Invoice Settings</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label d-block">GST Status</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="gst_enabled" name="gst_enabled" value="1"
                               {{ old('gst_enabled', $company->gst_enabled) ? 'checked' : '' }} style="width:3em;height:1.5em;">
                        <label class="form-check-label ms-2 fw-medium" for="gst_enabled">GST Enabled</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="invoice_prefix" class="form-label">Invoice Number Prefix</label>
                    <input type="text" class="form-control @error('invoice_prefix') is-invalid @enderror"
                           id="invoice_prefix" name="invoice_prefix"
                           value="{{ old('invoice_prefix', $company->invoice_prefix) }}" placeholder="GS">
                    <small class="text-muted">Invoice numbers will be: {{ $company->invoice_prefix }}-00001</small>
                    @error('invoice_prefix')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="warranty_default_months" class="form-label">Default Warranty (Months)</label>
                    <input type="number" class="form-control @error('warranty_default_months') is-invalid @enderror"
                           id="warranty_default_months" name="warranty_default_months"
                           value="{{ old('warranty_default_months', $company->warranty_default_months) }}" min="0">
                    @error('warranty_default_months')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="invoice_terms" class="form-label">Terms &amp; Conditions <span class="text-muted small">(Invoice / Bill of Supply)</span></label>
                    <textarea class="form-control @error('invoice_terms') is-invalid @enderror"
                              id="invoice_terms" name="invoice_terms" rows="5"
                              placeholder="1. Goods once sold will not be taken back.&#10;2. Warranty as per warranty card.&#10;3. Payment due within 15 days.">{{ old('invoice_terms', $company->invoice_terms) }}</textarea>
                    <small class="text-muted">Each line will appear as a separate point on the invoice PDF. Leave blank to use default terms.</small>
                    @error('invoice_terms')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Payment QR Code --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold"><i class="bi bi-qr-code me-2 text-primary"></i>Payment QR Code</h6>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <label for="payment_qr" class="form-label">Upload QR Code Image</label>
                    <input type="file" class="form-control @error('payment_qr') is-invalid @enderror"
                           id="payment_qr" name="payment_qr" accept="image/*">
                    @error('payment_qr')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    <small class="text-muted d-block mt-1">This QR code will be shown to customers when they click "Pay Now" on invoices.</small>
                </div>
                <div class="col-md-6 text-center">
                    @if($company->payment_qr_path)
                        <img src="{{ asset('storage/' . $company->payment_qr_path) }}" alt="Payment QR"
                             class="img-thumbnail" style="max-height: 180px;">
                        <small class="text-muted d-block mt-1">Current QR code. Upload new to replace.</small>
                    @else
                        <div class="border rounded p-4 text-muted">
                            <i class="bi bi-qr-code fs-1 d-block mb-2"></i>
                            No QR code uploaded yet
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Document Signature / Stamp Display --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold"><i class="bi bi-pen me-2 text-primary"></i>Document Signature / Stamp Settings</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Choose per document: show stamp/sign only, both (overlap/separate), or none.</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Estimate</label>
                    <select class="form-select @error('document_layouts.estimate.mode') is-invalid @enderror" name="document_layouts[estimate][mode]">
                        <option value="stamp_only" {{ old('document_layouts.estimate.mode', $documentModes['estimate'] ?? 'stamp_only') === 'stamp_only' ? 'selected' : '' }}>Stamp only</option>
                        <option value="sign_only" {{ old('document_layouts.estimate.mode', $documentModes['estimate'] ?? 'stamp_only') === 'sign_only' ? 'selected' : '' }}>Signature only</option>
                        <option value="both_separate" {{ old('document_layouts.estimate.mode', $documentModes['estimate'] ?? 'stamp_only') === 'both_separate' ? 'selected' : '' }}>Both (Separate)</option>
                        <option value="both_overlap" {{ old('document_layouts.estimate.mode', $documentModes['estimate'] ?? 'stamp_only') === 'both_overlap' ? 'selected' : '' }}>Both (Overlap)</option>
                        <option value="none" {{ old('document_layouts.estimate.mode', $documentModes['estimate'] ?? 'stamp_only') === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                    @error('document_layouts.estimate.mode')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Invoice</label>
                    <select class="form-select @error('document_layouts.invoice.mode') is-invalid @enderror" name="document_layouts[invoice][mode]">
                        <option value="stamp_only" {{ old('document_layouts.invoice.mode', $documentModes['invoice'] ?? 'stamp_only') === 'stamp_only' ? 'selected' : '' }}>Stamp only</option>
                        <option value="sign_only" {{ old('document_layouts.invoice.mode', $documentModes['invoice'] ?? 'stamp_only') === 'sign_only' ? 'selected' : '' }}>Signature only</option>
                        <option value="both_separate" {{ old('document_layouts.invoice.mode', $documentModes['invoice'] ?? 'stamp_only') === 'both_separate' ? 'selected' : '' }}>Both (Separate)</option>
                        <option value="both_overlap" {{ old('document_layouts.invoice.mode', $documentModes['invoice'] ?? 'stamp_only') === 'both_overlap' ? 'selected' : '' }}>Both (Overlap)</option>
                        <option value="none" {{ old('document_layouts.invoice.mode', $documentModes['invoice'] ?? 'stamp_only') === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                    @error('document_layouts.invoice.mode')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Advance Receipt</label>
                    <select class="form-select @error('document_layouts.advance_receipt.mode') is-invalid @enderror" name="document_layouts[advance_receipt][mode]">
                        <option value="stamp_only" {{ old('document_layouts.advance_receipt.mode', $documentModes['advance_receipt'] ?? 'stamp_only') === 'stamp_only' ? 'selected' : '' }}>Stamp only</option>
                        <option value="sign_only" {{ old('document_layouts.advance_receipt.mode', $documentModes['advance_receipt'] ?? 'stamp_only') === 'sign_only' ? 'selected' : '' }}>Signature only</option>
                        <option value="both_separate" {{ old('document_layouts.advance_receipt.mode', $documentModes['advance_receipt'] ?? 'stamp_only') === 'both_separate' ? 'selected' : '' }}>Both (Separate)</option>
                        <option value="both_overlap" {{ old('document_layouts.advance_receipt.mode', $documentModes['advance_receipt'] ?? 'stamp_only') === 'both_overlap' ? 'selected' : '' }}>Both (Overlap)</option>
                        <option value="none" {{ old('document_layouts.advance_receipt.mode', $documentModes['advance_receipt'] ?? 'stamp_only') === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                    @error('document_layouts.advance_receipt.mode')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i> Save Settings
        </button>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection
