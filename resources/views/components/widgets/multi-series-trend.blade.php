<div class="card">
    <div class="card-body">
        <div class="d-flex">
            <h3 class="card-title">Active users</h3>
            <div class="ms-auto">
                <div class="dropdown">
                    <a
                        class="dropdown-toggle text-secondary"
                        id="active-users-dropdown"
                        href="#"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-label="Select time range for active users"
                    >Last 7 days</a
                    >
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="active-users-dropdown">
                        <a class="dropdown-item active" href="#" aria-current="true">Last 7 days</a>
                        <a class="dropdown-item" href="#">Last 30 days</a>
                        <a class="dropdown-item" href="#">Last 3 months</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div id="chart-active-users-2_{{$widget->id}}" class="position-relative"></div>
            </div>
            <div class="col-md-auto">
                <div class="divide-y divide-y-fill">
                    <div class="px-3">
                        <div class="text-secondary"><span class="status-dot bg-primary"></span> Mobile</div>
                        <div class="h2">11,425</div>
                    </div>
                    <div class="px-3">
                        <div class="text-secondary"><span class="status-dot bg-azure"></span> Desktop</div>
                        <div class="h2">6,458</div>
                    </div>
                    <div class="px-3">
                        <div class="text-secondary"><span class="status-dot bg-green"></span> Tablet</div>
                        <div class="h2">3,985</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<style>
    :root {
        --chart-active-users-2-color-0: color-mix(in srgb, transparent, var(--tblr-primary) 100%);
        --chart-active-users-2-color-1: color-mix(in srgb, transparent, var(--tblr-azure) 100%);
        --chart-active-users-2-color-2: color-mix(in srgb, transparent, var(--tblr-green) 100%);
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        window.ApexCharts &&
        new ApexCharts(document.getElementById("chart-active-users-2_{{$widget->id}}"), {
            chart: {
                type: "line",
                fontFamily: "inherit",
                height: 288,
                parentHeightOffset: 0,
                toolbar: {
                    show: false,
                },
                animations: {
                    enabled: false,
                },
            },
            stroke: {
                width: 2,
                lineCap: "round",
                curve: "smooth",
            },
            series: {!! json_encode($series, JSON_UNESCAPED_UNICODE) !!},
            tooltip: {
                theme: "dark",
            },
            grid: {
                padding: {
                    top: -20,
                    right: 0,
                    left: -4,
                    bottom: -4,
                },
                strokeDashArray: 4,
            },
            xaxis: {
                labels: {
                    padding: 0,
                },
                tooltip: {
                    enabled: false,
                },
                type: "string",
            },
            yaxis: {
                labels: {
                    padding: 4,
                },
            },
            labels: {!! json_encode($labels, JSON_UNESCAPED_UNICODE) !!},
            colors: ["var(--chart-active-users-2-color-0)", "var(--chart-active-users-2-color-1)", "var(--chart-active-users-2-color-2)"],
            legend: {
                show: false,
            },
        }).render();
    });
</script>
