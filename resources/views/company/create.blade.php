<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Company — {{ config('app.name', 'CCTV Management') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1a1c2e 0%, #2d2f45 50%, #4e73df 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .create-container {
            width: 100%;
            max-width: 640px;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
            color: #fff;
        }

        .brand-header h2 {
            font-weight: 700;
            letter-spacing: .5px;
        }

        .brand-header p {
            opacity: .7;
            margin: 0;
        }

        .create-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
            overflow: hidden;
        }

        .create-card .card-header-custom {
            background: #f8f9fc;
            padding: 1.5rem 2rem 1rem;
            border-bottom: 1px solid #e3e6ec;
        }

        .create-card .card-body-custom {
            padding: 1.5rem 2rem 2rem;
        }
    </style>
</head>
<body>

    <div class="create-container">
        <div class="brand-header">
            <h2><i class="bi bi-camera-video-fill me-2"></i>CCTV Management</h2>
            <p>Create a new company</p>
        </div>

        <div class="create-card">
            <div class="card-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-building-add fs-4 text-primary"></i>
                    <div>
                        <h6 class="mb-0">Company details</h6>
                        <small class="text-muted">Fill in the required fields to create your company</small>
                    </div>
                </div>
            </div>

            <div class="card-body-custom">
                <form action="{{ route('company.store') }}" method="POST">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name"
                                   value="{{ old('name') }}" required placeholder="e.g. My Security Co.">
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
                                       value="{{ old('phone') }}" placeholder="9876543210">
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
                                       value="{{ old('email') }}" placeholder="info@company.com">
                            </div>
                            @error('email')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="2" placeholder="Full company address...">{{ old('address') }}</textarea>
                            @error('address')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="website" class="form-label">Website</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                <input type="text" class="form-control @error('website') is-invalid @enderror"
                                       id="website" name="website"
                                       value="{{ old('website') }}" placeholder="www.company.com">
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
                                       value="{{ old('gstin') }}" maxlength="15" placeholder="22AAAAA0000A1Z5">
                            </div>
                            @error('gstin')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label d-block">GST Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="gst_enabled" name="gst_enabled" value="1"
                                       {{ old('gst_enabled') ? 'checked' : '' }}>
                                <label class="form-check-label" for="gst_enabled">GST Enabled</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="invoice_prefix" class="form-label">Invoice prefix</label>
                            <input type="text" class="form-control @error('invoice_prefix') is-invalid @enderror"
                                   id="invoice_prefix" name="invoice_prefix"
                                   value="{{ old('invoice_prefix', 'INV') }}" placeholder="INV">
                            @error('invoice_prefix')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="warranty_default_months" class="form-label">Default warranty (months)</label>
                            <input type="number" class="form-control @error('warranty_default_months') is-invalid @enderror"
                                   id="warranty_default_months" name="warranty_default_months"
                                   value="{{ old('warranty_default_months', 12) }}" min="0">
                            @error('warranty_default_months')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Create Company
                        </button>
                        <a href="{{ route('company.select') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
