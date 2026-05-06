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
            <span>Total transactions</span>
            <strong>{{ $stats['total_transactions'] }}</strong>
            <small>Completed payment transactions</small>
        </article>
        <article class="metric-card">
            <span>Transaction revenue</span>
            <strong>৳{{ number_format($stats['total_transaction_amount'], 2) }}</strong>
            <small>From all completed transactions</small>
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

            <form class="weather-search" data-weather-form data-endpoint="{{ route('admin.weather') }}">
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

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Recent transactions</h3>
            <a href="{{ route('admin.transactions') }}">View all transactions</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Donor</th>
                    <th>Amount</th>
                    <th>Campaign</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Transaction ID</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($recentTransactions as $transaction)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y H:i') }}</td>
                        <td>
                            <strong>{{ $transaction->donor_name }}</strong>
                            <p>{{ $transaction->donor_email }}</p>
                        </td>
                        <td><strong>৳{{ number_format($transaction->amount, 2) }}</strong></td>
                        <td>{{ $transaction->campaign_title ?? 'N/A' }}</td>
                        <td>{{ ucfirst($transaction->payment_method) }}</td>
                        <td><span class="status-pill status-{{ $transaction->status }}">{{ ucfirst($transaction->status) }}</span></td>
                        <td><code style="font-size: 11px;">{{ substr($transaction->order_id, -8) }}</code></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">No transactions recorded yet.</td></tr>
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
@endsection