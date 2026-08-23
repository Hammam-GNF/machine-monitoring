<script>
    document.addEventListener('DOMContentLoaded', () => {
        const refreshInterval = 5000;
        const realtimeElement = document.getElementById('dashboard-realtime');

        if (! realtimeElement) {
            return;
        }

        let isRefreshing = false;

        const refreshDashboard = async () => {
            if (isRefreshing || document.hidden) {
                return;
            }

            isRefreshing = true;

            try {
                const response = await fetch('{{ route('dashboard.data') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (! response.ok) {
                    throw new Error(`HTTP error: ${response.status}`);
                }

                const data = await response.json();

                const parser = new DOMParser();
                const document = parser.parseFromString(data.html, 'text/html');

                const newRealtimeElement =
                    document.getElementById('dashboard-realtime');

                if (newRealtimeElement) {
                    realtimeElement.innerHTML =
                        newRealtimeElement.innerHTML;
                }
            } catch (error) {
                console.error('Dashboard polling failed:', error);
            } finally {
                isRefreshing = false;
            }
        };

        setInterval(refreshDashboard, refreshInterval);
    });
</script>
