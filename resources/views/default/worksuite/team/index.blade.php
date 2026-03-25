@extends('panel.layout.app')

@section('title', worksuite_label('team', 'Team'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ worksuite_label('team', 'Team') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ __('This module is being integrated. Full functionality coming soon.') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
