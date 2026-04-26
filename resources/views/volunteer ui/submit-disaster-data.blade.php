@extends('volunteer ui.layout')

@section('title', 'Submit Disaster Data - DisasterResponseHub')
@section('page-title', 'Submit Disaster Data')
@section('page-subtitle', 'Share field observations, incident reports, damage assessments, and other critical data from your relief work.')

@section('content')
    <section class="form-section">
        <div class="form-header">
            <h2>New Disaster Report</h2>
            <p>Submit detailed information about your work on disaster relief operations.</p>
        </div>

        <form method="POST" action="{{ route('volunteer.disaster-submissions.store') }}" class="form-layout">
            @csrf

            <div class="form-group">
                <label for="disaster_id" class="form-label">Select Disaster *</label>
                <select id="disaster_id" name="disaster_id" class="form-input" required>
                    <option value="">Choose a disaster</option>
                    @foreach ($disasters as $disaster)
                        <option value="{{ $disaster->id }}" {{ old('disaster_id') == $disaster->id ? 'selected' : '' }}>
                            {{ $disaster->type }} - {{ $disaster->city }}, {{ $disaster->district }} ({{ $disaster->disaster_date }})
                        </option>
                    @endforeach
                </select>
                @error('disaster_id')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="submission_type" class="form-label">Report Type *</label>
                <select id="submission_type" name="submission_type" class="form-input" required>
                    <option value="">Choose report type</option>
                    <option value="incident_report" {{ old('submission_type') == 'incident_report' ? 'selected' : '' }}>Incident Report</option>
                    <option value="damage_assessment" {{ old('submission_type') == 'damage_assessment' ? 'selected' : '' }}>Damage Assessment</option>
                    <option value="resource_need" {{ old('submission_type') == 'resource_need' ? 'selected' : '' }}>Resource Need</option>
                    <option value="population_data" {{ old('submission_type') == 'population_data' ? 'selected' : '' }}>Population Data</option>
                    <option value="other" {{ old('submission_type') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('submission_type')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="title" class="form-label">Title *</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="form-input" 
                    placeholder="e.g., Critical water shortage in sector 5"
                    value="{{ old('title') }}"
                    required
                >
                @error('title')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Detailed Description *</label>
                <textarea 
                    id="description" 
                    name="description" 
                    class="form-input textarea" 
                    rows="8"
                    placeholder="Provide detailed information about what you observed or documented..."
                    required
                >{{ old('description') }}</textarea>
                <small class="form-hint">Include specific details, locations, times, and any other relevant information.</small>
                @error('description')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="primary-action">Submit Report</button>
                <a href="{{ route('volunteer.disaster-submissions') }}" class="secondary-action">View Submissions</a>
            </div>
        </form>
    </section>

    @if ($errors->any())
        <div class="error-panel submission-error-panel">
            <strong>Please fix the following errors:</strong>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
@endsection
