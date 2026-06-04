

<div class="card">
    <div class="card-body">
        <div id="chart-scatter_{{$widget->id}}" class="position-relative"></div>
    </div>
</div>


<style>
    :root {
        --chart-scatter-color-0: color-mix(in srgb, transparent, var(--tblr-primary) 100%);
        --chart-scatter-color-1: color-mix(in srgb, transparent, var(--tblr-pink) 100%);
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        window.ApexCharts &&
        new ApexCharts(document.getElementById("chart-scatter_{{$widget->id}}"), {
            chart: {
                type: "scatter",
                fontFamily: "inherit",
                height: 240,
                parentHeightOffset: 0,
                toolbar: {
                    show: false,
                },
                animations: {
                    enabled: false,
                },
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
                categories: {!! json_encode($categories, JSON_UNESCAPED_UNICODE) !!},
            },
            yaxis: {
                labels: {
                    padding: 4,
                },
            },
            colors: ["var(--chart-scatter-color-0)", "var(--chart-scatter-color-1)"],
            legend: {
                show: false,
            },
        }).render();
    });
</script>

