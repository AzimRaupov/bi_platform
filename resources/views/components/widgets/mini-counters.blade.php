<div class="row row-deck row-cards">
    @foreach($counters as $counter)

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">{{ $counter['name'] }}</div>

                        <div class="ms-auto">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                onclick="copyCounter('{{ $counter['value'] }}', this)"
                            >
                                Копировать
                            </button>
                        </div>
                    </div>

                    <div class="h1 mb-1 mt-2 format-number">
                        {{ ($counter['prefix'] ?? '') . $counter['value'] . ($counter['suffix'] ?? '') }}
                    </div>

                    <div class="text-secondary">
                        Счётчик
                    </div>
                </div>
            </div>
        </div>

    @endforeach
</div>

<script>
    function copyCounter(value, btn) {
        navigator.clipboard.writeText(value).then(() => {
            const oldText = btn.innerText;
            btn.innerText = '✓ Скопировано';

            setTimeout(() => {
                btn.innerText = oldText;
            }, 1500);
        });
    }
</script>
