{{-- Обернули весь виджет в уникальный ID, чтобы скрипты не пересекались --}}
<div class="card" id="widget-container-{{ $widget->id }}">
    <div class="card-header d-flex align-items-center justify-content-between py-3 px-4 bg-transparent border-bottom">
        <h3 class="card-title m-0" style="font-size: 1.1rem; font-weight: 600; color: #1e293b;">{{$widget->title}}</h3>
        <div class="search-box" style="position: relative; max-width: 300px; width: 100%;">
            <input type="text" class="table-search" placeholder="Поиск..." style="
                width: 100%;
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                outline: none;
                transition: border-color 0.2s, box-shadow 0.2s;
            " onfocus="this.style.borderColor='#a5b4fc'; this.style.boxShadow='0 0 0 3px rgba(165, 180, 252, 0.2)'"
                   onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0;">
                <thead>
                <tr>
                    @foreach($data["headers"] as $index => $header)
                        <th>
                            <button class="table-sort" data-column="{{ $index }}" data-order="asc" style="
                                background: none;
                                border: none;
                                padding: 0.75rem 1rem;
                                font-weight: 600;
                                font-size: 0.85rem;
                                color: #64748b;
                                cursor: pointer;
                                text-align: left;
                                width: 100%;
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                            ">
                                {{ $header }}
                                <span class="sort-icon" style="opacity: 0.3; font-size: 0.7rem; margin-left: 0.5rem;">↕</span>
                            </button>
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody class="table-tbody">
                @foreach($data["rows"] as $row)
                    <tr class="table-row">
                        @foreach($row as $col)
                            <td class="format-number" style="padding: 1rem; font-size: 0.875rem; color: #334155; border-bottom: 1px solid #f1f5f9;">
                                {{ $col }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex align-items-center justify-content-between py-3 px-4 bg-transparent border-top" style="border-top: 1px solid #f1f5f9;">
        <div class="pagination-info" style="font-size: 0.85rem; color: #64748b;">
            Показано 0-0 из 0
        </div>
        {{-- Контейнер для кнопок пагинации --}}
        <div class="pagination-buttons btn-group" style="display: flex; gap: 0.25rem;">
        </div>
    </div>
</div>

<script>
    (function () {
        document.addEventListener("DOMContentLoaded", function () {
            const container = document.getElementById('widget-container-{{ $widget->id }}');
            if (!container) return;

            const tbody = container.querySelector('.table-tbody');
            const rows = Array.from(tbody.querySelectorAll('.table-row'));

            const searchInput = container.querySelector('.table-search');
            const paginationButtonsContainer = container.querySelector('.pagination-buttons');
            const paginationInfo = container.querySelector('.pagination-info');
            const sortButtons = container.querySelectorAll('.table-sort');

            const ITEMS_PER_PAGE = 5;

            let currentPage = 1;
            let filteredRows = [...rows];

            // ===== ПАГИНАЦИЯ И ОТРИСОВКА =====

            function renderTable() {
                const totalItems = filteredRows.length;
                const totalPages = Math.max(1, Math.ceil(totalItems / ITEMS_PER_PAGE));

                if (currentPage > totalPages) {
                    currentPage = totalPages;
                }

                rows.forEach(row => {
                    row.style.display = 'none';
                });

                const start = (currentPage - 1) * ITEMS_PER_PAGE;
                const end = start + ITEMS_PER_PAGE;

                const pageRows = filteredRows.slice(start, end);

                pageRows.forEach(row => {
                    tbody.appendChild(row);
                    row.style.display = '';
                });

                const shownStart = totalItems ? start + 1 : 0;
                const shownEnd = Math.min(end, totalItems);

                paginationInfo.textContent = `Показано ${shownStart}-${shownEnd} из ${totalItems}`;

                renderPagination(totalPages);
            }

            // ===== КНОПКИ ПАГИНАЦИИ =====

            function renderPagination(totalPages) {
                paginationButtonsContainer.innerHTML = '';

                if (totalPages <= 1) {
                    return;
                }

                const createButton = (text, page, active = false) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.innerText = text;

                    // Назначаем классы в зависимости от активности кнопки
                    if (active) {
                        btn.className = 'btn btn-primary';
                    } else {
                        // btn-white или btn-light — дефолтные минималистичные кнопки в Bootstrap/Tabler
                        btn.className = 'btn btn-white';
                    }

                    // Небольшая корректировка отступов, чтобы пагинация оставалась аккуратной
                    btn.style.padding = '6px 12px';
                    btn.style.fontSize = '14px';
                    btn.style.borderRadius = '6px';

                    btn.addEventListener('click', function () {
                        currentPage = page;
                        renderTable();
                    });

                    paginationButtonsContainer.appendChild(btn);
                };

                if (currentPage > 1) {
                    createButton('←', currentPage - 1);
                }

                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, currentPage + 2);

                for (let i = startPage; i <= endPage; i++) {
                    createButton(i, i, i === currentPage);
                }

                if (currentPage < totalPages) {
                    createButton('→', currentPage + 1);
                }
            }

            // ===== ПОИСК =====

            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();

                filteredRows = rows.filter(row =>
                    row.innerText.toLowerCase().includes(query)
                );

                currentPage = 1;
                renderTable();
            });

            // ===== СОРТИРОВКА =====

            sortButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const column = parseInt(this.dataset.column);
                    const order = this.dataset.order;

                    sortButtons.forEach(btn => {
                        if (btn !== this) {
                            btn.dataset.order = 'asc';
                            const icon = btn.querySelector('.sort-icon');
                            if (icon) {
                                icon.innerHTML = '↕';
                                icon.style.opacity = '0.3';
                            }
                        }
                    });

                    filteredRows.sort((a, b) => {
                        const aText = a.children[column].innerText.trim();
                        const bText = b.children[column].innerText.trim();

                        const isNumA = !isNaN(aText) && aText !== '';
                        const isNumB = !isNaN(bText) && bText !== '';

                        if (isNumA && isNumB) {
                            const aNum = Number(aText);
                            const bNum = Number(bText);
                            return order === 'asc' ? aNum - bNum : bNum - aNum;
                        }

                        return order === 'asc'
                            ? aText.localeCompare(bText, 'ru', { numeric: true, sensitivity: 'base' })
                            : bText.localeCompare(aText, 'ru', { numeric: true, sensitivity: 'base' });
                    });

                    const nextOrder = order === 'asc' ? 'desc' : 'asc';
                    this.dataset.order = nextOrder;

                    const currentIcon = this.querySelector('.sort-icon');
                    if (currentIcon) {
                        currentIcon.innerHTML = nextOrder === 'asc' ? '↑' : '↓';
                        currentIcon.style.opacity = '1';
                    }

                    currentPage = 1;
                    renderTable();
                });
            });

            renderTable();
        });
    })();
</script>
