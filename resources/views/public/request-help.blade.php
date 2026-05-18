@extends('public.layout')

@section('title', 'Request Help - Disaster Response Hub')
@section('page-title', 'Request Help')
@section('page-subtitle', 'Tell us what aid and support you need')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/request-help.css') }}">
@endpush

@section('content')
    <section class="panel-card form-card">
        <div class="panel-header">
            <h3>Request Help</h3>
        </div>

        <form method="POST" action="{{ route('public.request-help.store') }}" class="stack-form">
            @csrf

            <div class="form-group">
                <label for="name">Your Name *</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                @error('name') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                @error('email') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}">
                @error('phone') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="location_id">Your Location *</label>
                <input id="location_id" name="location_id" type="text" value="{{ old('location_id') }}" placeholder="e.g., Dhaka, Ward 5" required>

                <!--
                <select id="location_id" name="location_id" required>
                    <option value="">Select your location</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(old('location_id') == $location->id)>
                            {{ $location->city }}, {{ $location->district }}
                        </option>
                    @endforeach
                </select>
                -->
                
                @error('location_id') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Types of Aid Needed * <small>(Select all that apply)</small></label>
                <div class="checkbox-grid">
                    @foreach ($aidTypes as $aidType)
                        <div class="checkbox-item">
                            <input 
                                type="checkbox"
                                id="aid_{{ $aidType->id }}"
                                name="aid_type_ids[]"
                                value="{{ $aidType->id }}"
                                {{ in_array($aidType->id, old('aid_type_ids', [])) ? 'checked' : '' }}
                            >
                            <label for="aid_{{ $aidType->id }}">{{ $aidType->name }}</label>
                        </div>
                    @endforeach
                </div>
                @error('aid_type_ids') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="description">Description of Your Need *</label>
                <textarea id="description" name="description" rows="5" placeholder="Explain your situation and what help you need..." required>{{ old('description') }}</textarea>
                @error('description') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="primary-action">Submit Request</button>
                <a href="{{ route('public.home') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
