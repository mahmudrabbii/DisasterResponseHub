@extends('public.layout')

@section('title', 'Report a Disaster - Disaster Response Hub')
@section('page-title', 'Report a Disaster')
@section('page-subtitle', 'Submit details about the disaster you witnessed')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/report-disaster.css') }}">
@endpush

@section('content')
    <section class="panel-card form-card">
        <div class="panel-header">
            <h3>Report a Disaster</h3>
        </div>

        <form method="POST" action="{{ route('public.report-disaster.store') }}" class="stack-form" enctype="multipart/form-data">
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
                <label for="location_id">Location *</label>
                <select id="location_id" name="location_id" required>
                    <option value="">Select a location</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(old('location_id') == $location->id)>
                            {{ $location->city }}, {{ $location->district }}
                        </option>
                    @endforeach
                </select>
                @error('location_id') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="disaster_id">Disaster Type *</label>
                <select id="disaster_id" name="disaster_id" required>
                    <option value="">Select disaster type</option>
                    @foreach ($disasters as $disaster)
                        <option value="{{ $disaster->id }}" @selected(old('disaster_id') == $disaster->id)>
                            {{ $disaster->type }}
                        </option>
                    @endforeach
                </select>
                @error('disaster_id') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="title">Title *</label>
                <input id="title" name="title" type="text" value="{{ old('title') }}" placeholder="Brief title of the incident" required>
                @error('title') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="5" placeholder="Describe what happened, affected areas, casualties, damage..." required>{{ old('description') }}</textarea>
                @error('description') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="severity">Severity Level *</label>
                <select id="severity" name="severity" required>
                    <option value="">Select severity</option>
                    <option value="low" @selected(old('severity') === 'low')>Low - Minor impact</option>
                    <option value="medium" @selected(old('severity') === 'medium')>Medium - Moderate impact</option>
                    <option value="high" @selected(old('severity') === 'high')>High - Severe impact</option>
                </select>
                @error('severity') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="image">Upload Photo/Evidence (Optional)</label>
                <input id="image" name="image" type="file" accept="image/*" >
                <small class="file-help">Accepted formats: JPG, PNG, GIF (Max 20MB)</small>
                @error('image') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="primary-action">Submit Report</button>
                <a href="{{ route('public.home') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
