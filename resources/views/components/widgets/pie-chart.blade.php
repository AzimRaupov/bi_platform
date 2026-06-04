<div class="card">
    <div class="card-body">
        <div id="chart-demo-pie_{{$id}}" class="position-relative"></div>
    </div>
</div>

<style>
    :root {
        --chart-demo-pie-color-0: color-mix(in srgb, transparent, var(--tblr-primary) 100%);
        --chart-demo-pie-color-1: color-mix(in srgb, transparent, var(--tblr-primary) 80%);
        --chart-demo-pie-color-2: color-mix(in srgb, transparent, var(--tblr-primary) 60%);
        --chart-demo-pie-color-3: color-mix(in srgb, transparent, var(--tblr-gray-300) 100%);
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        window.ApexCharts &&
        new ApexCharts(document.getElementById("chart-demo-pie_{{$id}}"), {
            chart: {
                type: "pie",
                fontFamily: "inherit",
                height: 240,
                sparkline: {
                    enabled: true,
                },
                animations: {
                    enabled: false,
                },
            },
            series: {!! json_encode($series, JSON_UNESCAPED_UNICODE) !!},
            labels: {!! json_encode($labels, JSON_UNESCAPED_UNICODE) !!},
            tooltip: {
                theme: "dark",
            },
            grid: {
                strokeDashArray: 4,
            },
            colors: ["var(--chart-demo-pie-color-0)", "var(--chart-demo-pie-color-1)", "var(--chart-demo-pie-color-2)", "var(--chart-demo-pie-color-3)"],
            legend: {
                show: true,
                position: "bottom",
                offsetY: 12,
                markers: {
                    width: 10,
                    height: 10,
                    radius: 100,
                },
                itemMargin: {
                    horizontal: 8,
                    vertical: 8,
                },
            },
            tooltip: {
                fillSeriesColor: false,
            },
        }).render();
    });
</script>
