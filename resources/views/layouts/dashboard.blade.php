<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="csrf-token" content="" />
    <title>helo</title>
    @include('company.partials.default_head')
</head>
<body>
<a href="#content" class="visually-hidden skip-link">Skip to main content</a>

<script src="{{asset('dist/js/tabler-theme.min.js?1779974228')}}"></script>

<div class="page">
    <div class="page-wrapper">



        <!-- BEGIN PAGE BODY -->
        <main id="content" class="page-body">
            <div class="container-fluid px-">

                {!! $content !!}


            </div>
        </main>

    </div>
</div>


@include('company.partials.theme_settings')
<script>
    function formatNumber(value) {
        return Number(value).toLocaleString('en-US');
    }

    document.querySelectorAll('.format-number').forEach(el => {
        let raw = el.textContent.trim();

        // убираем всё кроме цифр
        raw = raw.replace(/[^\d]/g, '');

        if (raw) {
            el.textContent = '₽' + formatNumber(raw);
        }
    });
</script>
@include('company.partials.scripts')

</body>
</html>
