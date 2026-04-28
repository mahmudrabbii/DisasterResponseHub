// Weather functionality for admin dashboard
(function() {
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

    function renderWeather(weather, body) {
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

    function initWeather() {
        var form = document.querySelector('[data-weather-form]');
        var status = document.querySelector('[data-weather-status]');
        var feedback = document.querySelector('[data-weather-feedback]');
        var body = document.querySelector('[data-weather-card-body]');
        var queryInput = document.getElementById('weather-query');
        var endpoint = form ? form.getAttribute('data-endpoint') : null;

        if (!form || !endpoint) {
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

                    renderWeather(result.payload.weather, body);
                    feedback.textContent = result.payload.message;
                    status.textContent = result.payload.weather ? 'Live data' : 'Unavailable';
                })
                .catch(function (error) {
                    feedback.textContent = error.message;
                    status.textContent = 'Unavailable';
                    renderWeather(null, body);
                });
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWeather);
    } else {
        initWeather();
    }
})();
