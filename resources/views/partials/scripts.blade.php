<script>
    document.addEventListener('DOMContentLoaded', () => {
        const refreshInterval = 5000;
        const realtimeElement = document.getElementById('dashboard-realtime');

        if (! realtimeElement) {
            return;
        }

        const dashboardUrl = realtimeElement.dataset.dashboardUrl;
        const machineRows = document.getElementById('dashboard-machine-rows');
        const totalStat = document.getElementById('dashboard-stat-total');
        const activeStat = document.getElementById('dashboard-stat-active');
        const maintenanceStat = document.getElementById('dashboard-stat-maintenance');

        if (! dashboardUrl || ! machineRows) {
            return;
        }

        let isRefreshing = false;

        const escapeHtml = (value) => {
            const element = document.createElement('div');

            element.textContent = value ?? '';

            return element.innerHTML;
        };

        const renderStatus = (machine) => {
            if (! machine.is_active) {
                return '<span class="inline-flex items-center rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-700 dark:bg-red-400/10 dark:text-red-400">Inactive</span>';
            }

            if (! machine.status) {
                return '<span class="inline-flex items-center rounded-md bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-400/10 dark:text-yellow-400">No Data</span>';
            }

            if (machine.status === 'ON') {
                return '<span class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-400/10 dark:text-green-400">ON</span>';
            }

            return '<span class="inline-flex items-center rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-700 dark:bg-red-400/10 dark:text-red-400">OFF</span>';
        };

        const renderMaintenance = (machine) => {
            if (machine.maintenance) {
                return '<span class="inline-flex items-center rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-700 dark:bg-red-400/10 dark:text-red-400">Needs Maintenance</span>';
            }

            return '<span class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-400/10 dark:text-green-400">Normal</span>';
        };

        const renderMachineRows = (machines) => {
            if (! machines.length) {
                return `
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="text-neutral-400">
                                    No machines found
                                </div>

                                <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                    No machines match the current filters.
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
            }

            return machines.map((machine) => {
                const temperature = machine.temperature !== null
                    ? `${Number(machine.temperature).toFixed(2)} °C`
                    : '<span class="font-normal text-neutral-400">-</span>';

                return `
                    <tr class="transition-colors hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-neutral-900 dark:text-white">
                                ${escapeHtml(machine.code)}
                            </div>

                            <div class="mt-0.5 text-neutral-500 dark:text-neutral-400">
                                ${escapeHtml(machine.name)}
                            </div>
                        </td>

                        <td class="px-6 py-4 text-neutral-700 dark:text-neutral-300">
                            ${escapeHtml(machine.location)}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4">
                            ${renderStatus(machine)}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 font-medium text-neutral-700 dark:text-neutral-300">
                            ${temperature}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4">
                            ${renderMaintenance(machine)}
                        </td>
                    </tr>
                `;
            }).join('');
        };

        const getQueryString = () => {
            return window.location.search;
        };

        const refreshDashboard = async () => {
            if (isRefreshing || document.hidden) {
                return;
            }

            isRefreshing = true;

            try {
                const queryString = getQueryString();

                const response = await fetch(
                    queryString
                        ? `${dashboardUrl}${queryString}`
                        : dashboardUrl,
                    {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    }
                );

                if (! response.ok) {
                    throw new Error(`HTTP error: ${response.status}`);
                }

                const data = await response.json();

                if (totalStat) {
                    totalStat.textContent = data.stats.total;
                }

                if (activeStat) {
                    activeStat.textContent = data.stats.active;
                }

                if (maintenanceStat) {
                    maintenanceStat.textContent = data.stats.maintenance;
                }

                machineRows.innerHTML = renderMachineRows(data.machines);
            } catch (error) {
                console.error('Dashboard polling failed:', error);
            } finally {
                isRefreshing = false;
            }
        };

        setInterval(refreshDashboard, refreshInterval);
    });
</script>
