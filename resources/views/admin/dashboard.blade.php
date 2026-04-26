@extends('admin.layout')

@section('title', 'Admin Dashboard - DisasterResponseHub')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Overview of users, disasters, volunteers, resources, donations, and affected people.')

@section('content')
    <section class="metrics-grid">
        <article class="metric-card">
            <span>Total users</span>
            <strong>{{ $stats['total_users'] }}</strong>
            <small>All registered accounts</small>
        </article>
        <article class="metric-card">
            <span>Total disasters</span>
            <strong>{{ $stats['total_disasters'] }}</strong>
            <small>Registered disaster records</small>
        </article>
        <article class="metric-card">
            <span>Total volunteers</span>
            <strong>{{ $stats['total_volunteers'] }}</strong>
            <small>Volunteer accounts and profiles</small>
        </article>
        <article class="metric-card">
            <span>Total resources</span>
            <strong>{{ $stats['total_resources'] }}</strong>
            <small>Units in stock</small>
        </article>
        <article class="metric-card">
            <span>Total donations</span>
            <strong>{{ number_format($stats['total_donations'], 2) }}</strong>
            <small>Sum of fundraising records</small>
        </article>
        <article class="metric-card">
            <span>Affected people</span>
            <strong>{{ $stats['total_affected_people'] }}</strong>
            <small>Registered beneficiary records</small>
        </article>
    </section>

    <section class="panel-grid">
        <article class="panel-card">
            <div class="panel-header">
                <div>
                    <h3>Weather update</h3>
                    <p class="muted">Search a city to see the current conditions.</p>
                </div>
                <span class="status-pill status-pending" data-weather-status>Live data</span>
            </div>

            <form class="weather-search" data-weather-form>
                <div class="form-group form-wide">
                    <label for="weather-query">City or location</label>
                    <input id="weather-query" name="query" type="text" value="{{ $weatherQuery ?: 'Dhaka' }}" placeholder="Search a city, district, or country">
                </div>

                <button class="primary-action" type="submit">Search weather</button>
            </form>

            <div class="weather-feedback muted" data-weather-feedback>
                {{ $weather ? 'Showing live weather for ' . $weather['location'] . '.' : 'Search a city to load live weather.' }}
            </div>

            <div class="weather-card-body" data-weather-card-body>
                @if ($weather)
                    <div class="weather-summary">
                        <span class="weather-location">{{ $weather['location'] }}</span>
                        <strong class="weather-temperature">{{ number_format((float) $weather['temperature'], 1) }}&deg;C</strong>
                        <small class="weather-condition">{{ $weather['condition'] }}</small>
                    </div>

                    <div class="weather-details">
                        <div class="list-row">
                            <div>
                                <strong>Wind speed</strong>
                                <p>{{ number_format((float) $weather['wind_speed'], 1) }} km/h</p>
                            </div>
                            <div>
                                <strong>Observed at</strong>
                                <p>{{ !empty($weather['time']) ? \Illuminate\Support\Carbon::parse($weather['time'])->format('M d, H:i') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="empty-state" data-weather-empty>Weather data is unavailable right now. Search another city to try again.</p>
                @endif
            </div>
        </article>


        <!--
        <article class="panel-card">
            <div class="panel-header">
                <h3>Recent users</h3>
                <a href="{{ route('admin.users') }}">Open user management</a>
            </div>

            @forelse ($recentUsers as $user)
                <div class="list-row">
                    <div>
                        <strong>{{ $user->name }}</strong>
                        <p>{{ $user->email }}</p>
                    </div>
                    <span class="status-pill status-{{ $user->role }}">{{ $user->role }}</span>
                </div>
            @empty
                <p class="empty-state">No users found.</p>
            @endforelse
        </article>
        -->

        <article class="panel-card">
            <div class="panel-header">
                <h3>Recent disasters</h3>
                <a href="{{ route('admin.disasters') }}">Open disaster management</a>
            </div>

            @forelse ($recentDisasters as $disaster)
                <div class="list-row">
                    <div>
                        <strong>{{ $disaster->type }}</strong>
                        <p>{{ $disaster->city ?? 'Unknown city' }}, {{ $disaster->district ?? 'Unknown district' }}</p>
                    </div>
                    <span class="status-pill status-{{ $disaster->status }}">{{ $disaster->status }}</span>
                </div>
            @empty
                <p class="empty-state">No disasters recorded yet.</p>
            @endforelse
        </article>
    </section>


    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Recent resource stock</h3>
            <a href="{{ route('admin.resources') }}">Open inventory</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Expiry</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($recentResources as $resource)
                    <tr>
                        <td>{{ $resource->name }}</td>
                        <td>{{ $resource->category }}</td>
                        <td>{{ $resource->quantity }}</td>
                        <td>{{ $resource->expiry_date ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-state">No inventory recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel-grid">
        <article class="panel-card">
            <div class="panel-header">
                <h3>Policy broadcasts</h3>
                <span class="muted">Published by NGO officials</span>
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

        <article class="panel-card">
            <div class="panel-header">
                <h3>Recent alerts</h3>
                <span class="muted">Latest system-wide notifications</span>
            </div>

            @forelse ($alerts as $alert)
                <div class="list-row">
                    <div>
                        <strong>{{ $alert->title }}</strong>
                        <p>{{ $alert->message }}</p>
                    </div>
                </div>
            @empty
                <p class="empty-state">No alerts available.</p>
            @endforelse
        </article>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.querySelector('[data-weather-form]');
            var status = document.querySelector('[data-weather-status]');
            var feedback = document.querySelector('[data-weather-feedback]');
            var body = document.querySelector('[data-weather-card-body]');
            var queryInput = document.getElementById('weather-query');
            var endpoint = @json(route('admin.weather'));

            function formatWeatherTime(time) {
                if (!time) {
                    return 'N/A';
                }

                var date = new Date(time);

                if (Number.isNaN(date.getTime())) {
                    return 'N/A';
                }

                return date.toLocaleString([], {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            }

            function escapeHtml(value) {
                return String(value === undefined || value === null ? '' : value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function formatNumber(value) {
                var number = Number(value);

                if (Number.isNaN(number)) {
                    return 'N/A';
                }

                return number.toFixed(1);
            }

            function renderWeather(weather) {
                if (!weather) {
                    body.innerHTML = '<p class="empty-state" data-weather-empty>Weather data is unavailable right now. Search another city to try again.</p>';
                    return;
                }

                body.innerHTML = [
                    '<div class="weather-summary">',
                    '  <span class="weather-location">' + escapeHtml(weather.location) + '</span>',
                    '  <strong class="weather-temperature">' + formatNumber(weather.temperature) + '&deg;C</strong>',
                    '  <small class="weather-condition">' + escapeHtml(weather.condition) + '</small>',
                    '</div>',
                    '<div class="weather-details">',
                    '  <div class="list-row">',
                    '    <div>',
                    '      <strong>Wind speed</strong>',
                    '      <p>' + formatNumber(weather.wind_speed) + ' km/h</p>',
                    '    </div>',
                    '    <div>',
                    '      <strong>Observed at</strong>',
                    '      <p>' + formatWeatherTime(weather.time) + '</p>',
                    '    </div>',
                    '  </div>',
                    '</div>'
                ].join('');
            }

            if (!form) {
                return;
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                var query = queryInput ? queryInput.value.trim() : '';

                if (!query) {
                    feedback.textContent = 'Enter a city or location first.';
                    status.textContent = 'Waiting for input';
                    return;
                }

                status.textContent = 'Loading...';
                feedback.textContent = 'Searching for live weather...';

                fetch(endpoint + '?query=' + encodeURIComponent(query), {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            return {
                                ok: response.ok,
                                payload: payload
                            };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            throw new Error(result.payload.message || 'Unable to load weather data.');
                        }

                        renderWeather(result.payload.weather);
                        feedback.textContent = result.payload.message;
                        status.textContent = result.payload.weather ? 'Live data' : 'Unavailable';
                    })
                    .catch(function (error) {
                        feedback.textContent = error.message;
                        status.textContent = 'Unavailable';
                        renderWeather(null);
                    });
            });
        });
    </script>
@endsection