@extends('official.layout')

@section('title', 'Policies - DisasterResponseHub')
@section('page-title', 'Policies')
@section('page-subtitle', 'Generate guidance and broadcast it to field volunteers.')

@section('content')
    <section class="panel-grid">
        <article class="panel-card">
            <div class="panel-header">
                <h3>Create policy</h3>
            </div>

            <form method="POST" action="{{ route('official.policies.store') }}" class="stack-form">
                @csrf
                <div class="form-group">
                    <label for="title">Policy title</label>
                    <input id="title" name="title" type="text" required>
                </div>
                <div class="form-group">
                    <label for="description">Policy description</label>
                    <textarea id="description" name="description" rows="6" required></textarea>
                </div>
                <button type="submit" class="primary-action">Publish policy</button>
            </form>
        </article>

        <article class="panel-card">
            <div class="panel-header">
                <h3>Published policies</h3>
                <span class="muted">Latest guidance entries</span>
            </div>

            @forelse ($policies as $policy)
                <div class="list-row">
                    <div>
                        <strong>{{ $policy->title }}</strong>
                        <p>{{ $policy->description }}</p>
                    </div>
                </div>
            @empty
                <p class="empty-state">No policies have been published yet.</p>
            @endforelse
        </article>
    </section>
@endsection