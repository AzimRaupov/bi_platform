<div>
    <style>
        /* ── Wrapper ── */
        .dashboard-wrapper {
            display: flex;
            height: calc(100vh - var(--tblr-navbar-height, 56px) - var(--tblr-content-header-height, 56px));
            overflow: hidden;
        }

        /* ── Dashboard main scroll ── */
        .dashboard-main {
            flex: 1 1 0;
            min-width: 0;
            overflow-y: auto;
        }

        /* ── Chat sidebar – desktop ── */
        .ai-chat-sidebar {
            width: 360px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            border-left: 1px solid var(--tblr-border-color);
            background: var(--tblr-bg-surface);
            transition: width .3s ease, opacity .3s ease;
            overflow: hidden;
        }
        .ai-chat-sidebar.chat-collapsed {
            width: 0;
            border-left: none;
            opacity: 0;
            pointer-events: none;
        }

        /* ── Chat sidebar – mobile: offcanvas over content, below navbar ── */
        @media (max-width: 991.98px) {
            .ai-chat-sidebar {
                position: fixed;
                top: var(--tblr-navbar-height, 56px);
                right: 0;
                bottom: 0;
                width: 100% !important;
                max-width: 420px;
                z-index: 1045;
                box-shadow: -4px 0 24px rgba(0,0,0,.15);
                opacity: 1;
                pointer-events: all;
                transition: transform .3s ease, opacity .3s ease;
            }
            .ai-chat-sidebar.chat-collapsed {
                transform: translateX(100%);
                opacity: 0;
                pointer-events: none;
                width: 100% !important;
                border-left: 1px solid var(--tblr-border-color);
            }
            /* Backdrop */
            .chat-backdrop {
                display: block !important;
            }
        }
        .chat-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1044;
            background: rgba(0,0,0,.4);
            top: var(--tblr-navbar-height, 56px);
        }
        .chat-backdrop.d-none { display: none !important; }

        /* ── Chat messages scroll ── */
        .chat-messages {
            flex: 1 1 0;
            overflow-y: auto;
            scroll-behavior: smooth;
        }
        .chat-messages::-webkit-scrollbar { width: 4px; }
        .chat-messages::-webkit-scrollbar-thumb {
            background: var(--tblr-border-color);
            border-radius: 4px;
        }

        /* ── Message bubbles ── */
        .msg-user .msg-bubble {
            background: var(--tblr-primary);
            color: #fff;
            border-radius: 14px 14px 4px 14px;
        }
        .msg-ai .msg-bubble {
            background: var(--tblr-bg-surface-secondary, #f1f5f9);
            border: 1px solid var(--tblr-border-color);
            border-radius: 14px 14px 14px 4px;
            color: var(--tblr-body-color);
        }
        .msg-bubble {
            max-width: 88%;
            padding: .55rem .85rem;
            font-size: .855rem;
            line-height: 1.55;
            word-break: break-word;
        }

        /* ── Typing dots ── */
        .typing-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--tblr-muted);
            animation: typingBounce 1.2s infinite;
        }
        .typing-dot:nth-child(2) { animation-delay: .2s; }
        .typing-dot:nth-child(3) { animation-delay: .4s; }
        @keyframes typingBounce {
            0%,80%,100% { transform: translateY(0); opacity: .4; }
            40%          { transform: translateY(-6px); opacity: 1; }
        }

        /* ── Online dot ── */
        .status-online {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #2fb344;
            animation: onlinePulse 2s infinite;
        }
        @keyframes onlinePulse {
            0%,100% { opacity: 1; }
            50%      { opacity: .35; }
        }

        /* ── Chat textarea ── */
        .chat-textarea {
            resize: none;
            overflow-y: hidden;
            max-height: 120px;
            field-sizing: content;
        }

        /* ── Suggestion chips ── */
        .suggestion-chip {
            cursor: pointer;
            transition: background .15s, border-color .15s, color .15s;
        }
        .suggestion-chip:hover {
            background: rgba(var(--tblr-primary-rgb, 6,111,209),.08) !important;
            border-color: var(--tblr-primary) !important;
            color: var(--tblr-primary) !important;
        }

        /* ── FAB toggle (mobile) ── */
        .chat-fab {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 1043;
            width: 52px; height: 52px;
            border-radius: 50%;
            border: none;
            background: var(--tblr-primary);
            color: #fff;
            box-shadow: 0 4px 20px rgba(6,111,209,.4);
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform .2s;
        }
        .chat-fab:hover { transform: scale(1.08); }
        @media (max-width: 991.98px) {
            .chat-fab { display: flex; }
        }
    </style>

    {{-- ════════════════════════════════════════════════════════ --}}
    {{--                   MAIN WRAPPER                         --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div class="dashboard-wrapper">

        {{-- ──────────────────────────────────────────────────── --}}
        {{--               DASHBOARD CONTENT                     --}}
        {{-- ──────────────────────────────────────────────────── --}}
        <div class="dashboard-main p-3" id="dashboardMain">
            <div class="row row-deck row-cards g-3">

                {{-- Welcome card --}}
                <div class="col-12 col-lg-6">
                    <div class="card card-gradient h-100">
                        <div class="card-body">
                            <div class="row gy-3 h-100">
                                <div class="col-12 col-sm-7 d-flex flex-column">
                                    <h3 class="h2 mb-1">Welcome back, Paweł</h3>
                                    <p class="text-secondary mb-auto">You have <strong>5</strong> new messages and <strong>2</strong> notifications.</p>
                                    <div class="row g-4 mt-2">
                                        <div class="col-auto">
                                            <div class="subheader">Today's Sales</div>
                                            <div class="d-flex align-items-baseline gap-1">
                                                <div class="h3 mb-0">6,782</div>
                                                <span class="text-green d-inline-flex align-items-center lh-1 small">
                                                7%<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon ms-0"><path d="M12 5l0 14"/><path d="M18 11l-6 -6"/><path d="M6 11l6 -6"/></svg>
                                            </span>
                                            </div>
                                            <div class="progress progress-sm mt-1" style="width:140px">
                                                <div class="progress-bar bg-success" style="width:75%" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" aria-label="75%"><span class="visually-hidden">75%</span></div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="subheader">Growth Rate</div>
                                            <div class="d-flex align-items-baseline gap-1">
                                                <div class="h3 mb-0">78.4%</div>
                                                <span class="text-red d-inline-flex align-items-center lh-1 small">
                                                -1%<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon ms-0"><path d="M12 5l0 14"/><path d="M18 13l-6 6"/><path d="M6 13l6 6"/></svg>
                                            </span>
                                            </div>
                                            <div class="progress progress-sm mt-1" style="width:140px">
                                                <div class="progress-bar bg-danger" style="width:78%" role="progressbar" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100" aria-label="78%"><span class="visually-hidden">78%</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-5 d-flex flex-column align-items-center justify-content-center text-center">
                                    <div class="display-6 fw-bold text-primary">$42,580</div>
                                    <div class="text-secondary small">Total Revenue This Month</div>
                                    <div class="mt-2">
                                        <span class="badge bg-success-lt">+8% vs last month</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Users --}}
                <div class="col-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="subheader">Total Users</div>
                            <div class="d-flex align-items-baseline gap-2 mt-1">
                                <div class="h1 mb-0">75,782</div>
                                <span class="text-green d-inline-flex align-items-center lh-1 small">2%<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon ms-0"><path d="M12 5l0 14"/><path d="M18 11l-6 -6"/><path d="M6 11l6 -6"/></svg></span>
                            </div>
                            <div class="text-secondary mt-1 small">24,635 users from last month</div>
                        </div>
                        <div id="chart-visitors" class="position-relative"></div>
                    </div>
                </div>

                {{-- Active Users --}}
                <div class="col-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="subheader">Active Users</div>
                            <div class="d-flex align-items-baseline gap-2 mt-1 mb-2">
                                <div class="h1 mb-0">25,782</div>
                                <span class="text-red d-inline-flex align-items-center lh-1 small">-1%<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon ms-0"><path d="M12 5l0 14"/><path d="M18 13l-6 6"/><path d="M6 13l6 6"/></svg></span>
                            </div>
                            <div id="chart-active-users-3" class="position-relative"></div>
                        </div>
                    </div>
                </div>

                {{-- Sales --}}
                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-1">
                                <div class="subheader mb-0">Sales</div>
                                <div class="ms-auto">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle text-secondary small" href="#" data-bs-toggle="dropdown">Last 7 days</a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item active" href="#">Last 7 days</a>
                                            <a class="dropdown-item" href="#">Last 30 days</a>
                                            <a class="dropdown-item" href="#">Last 3 months</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="h1 mb-2">75%</div>
                            <div class="d-flex align-items-center mb-2 small">
                                <span>Conversion rate</span>
                                <span class="ms-auto text-green d-inline-flex align-items-center lh-1">7%<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon ms-0"><path d="M12 5l0 14"/><path d="M18 11l-6 -6"/><path d="M6 11l6 -6"/></svg></span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary" style="width:75%" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" aria-label="75%"><span class="visually-hidden">75%</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Revenue --}}
                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-1">
                                <div class="subheader mb-0">Revenue</div>
                                <div class="ms-auto">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle text-secondary small" href="#" data-bs-toggle="dropdown">Last 7 days</a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item active" href="#">Last 7 days</a>
                                            <a class="dropdown-item" href="#">Last 30 days</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-baseline gap-2">
                                <div class="h1 mb-0">$4,300</div>
                                <span class="text-green d-inline-flex align-items-center lh-1 small">8%<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon ms-0"><path d="M12 5l0 14"/><path d="M18 11l-6 -6"/><path d="M6 11l6 -6"/></svg></span>
                            </div>
                        </div>
                        <div id="chart-revenue-bg" class="position-relative rounded-bottom-3 chart-sm"></div>
                    </div>
                </div>

                {{-- New Clients --}}
                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="subheader">New clients</div>
                            <div class="d-flex align-items-baseline gap-2 mt-1 mb-2">
                                <div class="h1 mb-0">6,782</div>
                                <span class="text-muted d-inline-flex align-items-center lh-1 small">0%<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon ms-0"><path d="M5 12l14 0"/></svg></span>
                            </div>
                            <div id="chart-new-clients" class="position-relative chart-sm"></div>
                        </div>
                    </div>
                </div>

                {{-- Active Subscriptions --}}
                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="subheader">Active subscriptions</div>
                            <div class="d-flex align-items-baseline gap-2 mt-1 mb-2">
                                <div class="h1 mb-0">2,986</div>
                                <span class="text-green d-inline-flex align-items-center lh-1 small">4%<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon ms-0"><path d="M12 5l0 14"/><path d="M18 11l-6 -6"/><path d="M6 11l6 -6"/></svg></span>
                            </div>
                            <div id="chart-active-users" class="position-relative chart-sm"></div>
                        </div>
                    </div>
                </div>

                {{-- Social stat mini cards --}}
                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-6 col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="bg-primary text-white avatar avatar-square"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2"/><path d="M12 3v3m0 12v3"/></svg></span>
                                        </div>
                                        <div class="col">
                                            <div class="fw-medium">132 Sales</div>
                                            <div class="text-secondary small">12 waiting payments</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="bg-green text-white avatar avatar-square"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M4 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M15 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg></span>
                                        </div>
                                        <div class="col">
                                            <div class="fw-medium">78 Orders</div>
                                            <div class="text-secondary small">32 shipped</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="bg-x text-white avatar avatar-square"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M4 4l11.733 16h4.267l-11.733 -16l-4.267 0"/><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"/></svg></span>
                                        </div>
                                        <div class="col">
                                            <div class="fw-medium">623 Shares</div>
                                            <div class="text-secondary small">16 today</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="bg-facebook text-white avatar avatar-square"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3"/></svg></span>
                                        </div>
                                        <div class="col">
                                            <div class="fw-medium">132 Likes</div>
                                            <div class="text-secondary small">21 today</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Traffic + Map --}}
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="card-title">Traffic summary</h3>
                            <div id="chart-mentions" class="position-relative chart-lg"></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="card-title">Locations</h3>
                            <div class="ratio ratio-21x9"><div><div id="map-world" class="w-100 h-100"></div></div></div>
                        </div>
                    </div>
                </div>

                {{-- Most Visited Pages --}}
                <div class="col-12 col-lg-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Most Visited Pages</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                <tr>
                                    <th>Page name</th>
                                    <th>Visitors</th>
                                    <th>Unique</th>
                                    <th colspan="2">Bounce rate</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr><td>/</td><td class="text-secondary">4,896</td><td class="text-secondary">3,654</td><td class="text-secondary">82.54%</td><td class="text-end w-1"><div class="chart-sparkline chart-sparkline-sm" id="sparkline-bounce-rate-1"></div></td></tr>
                                <tr><td>/form-elements.html</td><td class="text-secondary">3,652</td><td class="text-secondary">3,215</td><td class="text-secondary">76.29%</td><td class="text-end w-1"><div class="chart-sparkline chart-sparkline-sm" id="sparkline-bounce-rate-2"></div></td></tr>
                                <tr><td>/index.html</td><td class="text-secondary">3,256</td><td class="text-secondary">2,865</td><td class="text-secondary">72.65%</td><td class="text-end w-1"><div class="chart-sparkline chart-sparkline-sm" id="sparkline-bounce-rate-3"></div></td></tr>
                                <tr><td>/icons.html</td><td class="text-secondary">986</td><td class="text-secondary">865</td><td class="text-secondary">44.89%</td><td class="text-end w-1"><div class="chart-sparkline chart-sparkline-sm" id="sparkline-bounce-rate-4"></div></td></tr>
                                <tr><td>/docs/</td><td class="text-secondary">912</td><td class="text-secondary">822</td><td class="text-secondary">41.12%</td><td class="text-end w-1"><div class="chart-sparkline chart-sparkline-sm" id="sparkline-bounce-rate-5"></div></td></tr>
                                <tr><td>/accordion.html</td><td class="text-secondary">855</td><td class="text-secondary">798</td><td class="text-secondary">32.65%</td><td class="text-end w-1"><div class="chart-sparkline chart-sparkline-sm" id="sparkline-bounce-rate-6"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Social Traffic --}}
                <div class="col-12 col-lg-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Social Media Traffic</h3>
                        </div>
                        <table class="table card-table table-vcenter">
                            <thead><tr><th>Network</th><th colspan="2">Visitors</th></tr></thead>
                            <tbody>
                            <tr><td>Instagram</td><td>3,550</td><td class="w-50"><div class="progress progress-xs"><div class="progress-bar bg-primary" style="width:71%"></div></div></td></tr>
                            <tr><td>Twitter</td><td>1,798</td><td class="w-50"><div class="progress progress-xs"><div class="progress-bar bg-primary" style="width:36%"></div></div></td></tr>
                            <tr><td>Facebook</td><td>1,245</td><td class="w-50"><div class="progress progress-xs"><div class="progress-bar bg-primary" style="width:25%"></div></div></td></tr>
                            <tr><td>TikTok</td><td>986</td><td class="w-50"><div class="progress progress-xs"><div class="progress-bar bg-primary" style="width:20%"></div></div></td></tr>
                            <tr><td>Pinterest</td><td>854</td><td class="w-50"><div class="progress progress-xs"><div class="progress-bar bg-primary" style="width:17%"></div></div></td></tr>
                            <tr><td>VK</td><td>650</td><td class="w-50"><div class="progress progress-xs"><div class="progress-bar bg-primary" style="width:13%"></div></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tasks --}}
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Tasks</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-selectable card-table table-vcenter">
                                <tbody>
                                <tr>
                                    <td class="w-1 pe-0"><input type="checkbox" class="form-check-input m-0 align-middle" aria-label="Select task" checked /></td>
                                    <td class="w-100"><a href="#" class="text-reset">Extend the data model.</a></td>
                                    <td class="text-nowrap text-secondary small">декабрь 08, 2024</td>
                                    <td class="text-nowrap"><a href="#" class="text-secondary small"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M5 12l5 5l10 -10"/></svg> 2/7</a></td>
                                    <td><span class="avatar avatar-sm" style="background-image:url(./static/avatars/000m.jpg)"></span></td>
                                </tr>
                                <tr>
                                    <td class="w-1 pe-0"><input type="checkbox" class="form-check-input m-0 align-middle" aria-label="Select task" /></td>
                                    <td class="w-100"><a href="#" class="text-reset">Verify the event flow.</a></td>
                                    <td class="text-nowrap text-secondary small">январь 01, 2024</td>
                                    <td class="text-nowrap"><a href="#" class="text-secondary small"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M5 12l5 5l10 -10"/></svg> 0/5</a></td>
                                    <td><span class="avatar avatar-sm" style="background-image:url(./static/avatars/052f.jpg)"></span></td>
                                </tr>
                                <tr>
                                    <td class="w-1 pe-0"><input type="checkbox" class="form-check-input m-0 align-middle" aria-label="Select task" /></td>
                                    <td class="w-100"><a href="#" class="text-reset">Database backup and maintenance</a></td>
                                    <td class="text-nowrap text-secondary small">январь 01, 2024</td>
                                    <td class="text-nowrap"><a href="#" class="text-secondary small"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M5 12l5 5l10 -10"/></svg> 0/5</a></td>
                                    <td><span class="avatar avatar-sm" style="background-image:url(./static/avatars/002m.jpg)"></span></td>
                                </tr>
                                <tr>
                                    <td class="w-1 pe-0"><input type="checkbox" class="form-check-input m-0 align-middle" aria-label="Select task" checked /></td>
                                    <td class="w-100"><a href="#" class="text-reset">Identify the implementation team.</a></td>
                                    <td class="text-nowrap text-secondary small">сентябрь 01, 2024</td>
                                    <td class="text-nowrap"><a href="#" class="text-secondary small"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M5 12l5 5l10 -10"/></svg> 6/10</a></td>
                                    <td><span class="avatar avatar-sm" style="background-image:url(./static/avatars/003m.jpg)"></span></td>
                                </tr>
                                <tr>
                                    <td class="w-1 pe-0"><input type="checkbox" class="form-check-input m-0 align-middle" aria-label="Select task" checked /></td>
                                    <td class="w-100"><a href="#" class="text-reset">Check Pull Requests</a></td>
                                    <td class="text-nowrap text-secondary small">июль 17, 2024</td>
                                    <td class="text-nowrap"><a href="#" class="text-secondary small"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M5 12l5 5l10 -10"/></svg> 2/9</a></td>
                                    <td><span class="avatar avatar-sm" style="background-image:url(./static/avatars/001f.jpg)"></span></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Invoices --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Invoices</h3>
                        </div>
                        <div class="card-body border-bottom py-3">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="text-secondary d-flex align-items-center gap-2">
                                    Show
                                    <input type="text" class="form-control form-control-sm" value="8" style="width:60px" aria-label="Invoices count" />
                                    entries
                                </div>
                                <div class="ms-auto d-flex align-items-center gap-2">
                                    <span class="text-secondary">Search:</span>
                                    <input type="text" class="form-control form-control-sm" style="width:160px" aria-label="Search invoice" />
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-selectable card-table table-vcenter text-nowrap datatable">
                                <thead>
                                <tr>
                                    <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" aria-label="Select all" /></th>
                                    <th class="w-1">No.</th>
                                    <th>Invoice Subject</th>
                                    <th>Client</th>
                                    <th>VAT No.</th>
                                    <th>Created</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><input class="form-check-input m-0 align-middle" type="checkbox" aria-label="Select" /></td>
                                    <td><span class="text-secondary">001401</span></td>
                                    <td><a href="#" class="text-reset">Design Works</a></td>
                                    <td><span class="flag flag-xs flag-country-us me-1"></span>Carlson Limited</td>
                                    <td>87956621</td><td>15 Dec 2017</td>
                                    <td><span class="badge bg-success me-1"></span> Paid</td>
                                    <td>$887</td>
                                    <td class="text-end"><div class="dropdown"><button class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown">Actions</button><div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Action</a><a class="dropdown-item" href="#">Another action</a></div></div></td>
                                </tr>
                                <tr>
                                    <td><input class="form-check-input m-0 align-middle" type="checkbox" aria-label="Select" /></td>
                                    <td><span class="text-secondary">001402</span></td>
                                    <td><a href="#" class="text-reset">UX Wireframes</a></td>
                                    <td><span class="flag flag-xs flag-country-gb me-1"></span>Adobe</td>
                                    <td>87956421</td><td>12 Apr 2017</td>
                                    <td><span class="badge bg-warning me-1"></span> Pending</td>
                                    <td>$1,200</td>
                                    <td class="text-end"><div class="dropdown"><button class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown">Actions</button><div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Action</a></div></div></td>
                                </tr>
                                <tr>
                                    <td><input class="form-check-input m-0 align-middle" type="checkbox" aria-label="Select" /></td>
                                    <td><span class="text-secondary">001403</span></td>
                                    <td><a href="#" class="text-reset">New Dashboard</a></td>
                                    <td><span class="flag flag-xs flag-country-de me-1"></span>Bluewolf</td>
                                    <td>87952621</td><td>23 Oct 2017</td>
                                    <td><span class="badge bg-warning me-1"></span> Pending</td>
                                    <td>$534</td>
                                    <td class="text-end"><div class="dropdown"><button class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown">Actions</button><div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Action</a></div></div></td>
                                </tr>
                                <tr>
                                    <td><input class="form-check-input m-0 align-middle" type="checkbox" aria-label="Select" /></td>
                                    <td><span class="text-secondary">001404</span></td>
                                    <td><a href="#" class="text-reset">Logo & Print</a></td>
                                    <td><span class="flag flag-xs flag-country-us me-1"></span>Apple</td>
                                    <td>87956621</td><td>22 Mar 2018</td>
                                    <td><span class="badge bg-success me-1"></span> Paid Today</td>
                                    <td>$2,500</td>
                                    <td class="text-end"><div class="dropdown"><button class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown">Actions</button><div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Action</a></div></div></td>
                                </tr>
                                <tr>
                                    <td><input class="form-check-input m-0 align-middle" type="checkbox" aria-label="Select" /></td>
                                    <td><span class="text-secondary">001405</span></td>
                                    <td><a href="#" class="text-reset">Marketing Templates</a></td>
                                    <td><span class="flag flag-xs flag-country-pl me-1"></span>Printic</td>
                                    <td>87956621</td><td>29 Jan 2018</td>
                                    <td><span class="badge bg-danger me-1"></span> Paid Today</td>
                                    <td>$648</td>
                                    <td class="text-end"><div class="dropdown"><button class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown">Actions</button><div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Action</a></div></div></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <p class="m-0 text-secondary small">Showing <strong>1 to 8</strong> of <strong>16 entries</strong></p>
                                </div>
                                <div class="col-auto ms-auto">
                                    <ul class="pagination m-0">
                                        <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 6l-6 6l6 6"/></svg></a></li>
                                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                                        <li class="page-item active"><a class="page-link" href="#">3</a></li>
                                        <li class="page-item"><a class="page-link" href="#">4</a></li>
                                        <li class="page-item"><a class="page-link" href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 6l6 6l-6 6"/></svg></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /row --}}
        </div>{{-- /dashboard-main --}}


        {{-- ──────────────────────────────────────────────────── --}}
        {{--               AI CHAT SIDEBAR                       --}}
        {{-- ──────────────────────────────────────────────────── --}}

        <aside class="ai-chat-sidebar {{ $collapsed ? 'd-none' : '' }}" id="aiChatSidebar" aria-label="AI Assistant">

            {{-- Header --}}
            <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom flex-shrink-0">
                <div class="avatar avatar-sm rounded-2 bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8a4 4 0 0 1 4 4"/><path d="M12 4a8 8 0 0 1 8 8"/><path d="M12 20a8 8 0 0 1 -8 -8"/><circle cx="12" cy="12" r="1"/></svg>
                </div>
                <div class="flex-fill overflow-hidden">
                    <div class="fw-semibold lh-1 small">AI Assistant</div>
                    <div class="text-secondary d-flex align-items-center gap-1 mt-1" style="font-size:.7rem">
                        <span class="status-online flex-shrink-0"></span>Ready to help
                    </div>
                </div>
                <div class="d-flex gap-1 ms-auto flex-shrink-0">
                    <button class="btn btn-sm btn-ghost-secondary px-2" wire:click="clearChat" title="Clear chat" aria-label="Clear chat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                    </button>
                    <button class="btn btn-sm btn-ghost-secondary px-2" wire:click="toggleCollapse" title="Close" aria-label="Close chat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="d-flex gap-2 px-3 py-2 border-bottom flex-shrink-0 flex-wrap bg-body-tertiary">
                <button class="btn btn-sm btn-outline-secondary px-2 py-1 d-flex align-items-center gap-1" wire:click="quickAsk('Покажи статистику продаж')" style="font-size:.72rem">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 16l4 -7l4 4l4 -7"/></svg>
                    Продажи
                </button>
                <button class="btn btn-sm btn-outline-secondary px-2 py-1 d-flex align-items-center gap-1" wire:click="quickAsk('Сколько активных пользователей?')" style="font-size:.72rem">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
                    Пользователи
                </button>
                <button class="btn btn-sm btn-outline-secondary px-2 py-1 d-flex align-items-center gap-1" wire:click="quickAsk('Какой текущий доход?')" style="font-size:.72rem">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2"/><path d="M12 3v3m0 12v3"/></svg>
                    Доход
                </button>
                <button class="btn btn-sm btn-outline-secondary px-2 py-1 d-flex align-items-center gap-1" wire:click="quickAsk('Покажи задачи которые нужно выполнить')" style="font-size:.72rem">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3l8 -8"/><path d="M20 12v6a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h9"/></svg>
                    Задачи
                </button>
            </div>

            {{-- Messages --}}
            <div class="chat-messages p-3 d-flex flex-column gap-3 overflow-auto" id="chatMessages" wire:poll.10s>
                @if ($chat->messages->isEmpty())
                    {{-- Welcome --}}
                    <div id="chatWelcome" class="d-flex flex-column align-items-center justify-content-center text-center py-4 px-2 flex-grow-1">
                        <div class="avatar avatar-lg rounded-3 bg-primary text-white mb-3 d-flex align-items-center justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8a4 4 0 0 1 4 4"/><path d="M12 4a8 8 0 0 1 8 8"/><path d="M12 20a8 8 0 0 1 -8 -8"/><circle cx="12" cy="12" r="1"/></svg>
                        </div>
                        <h5 class="fw-bold mb-1">AI Ассистент</h5>
                        <p class="text-secondary small mb-3">Задайте вопрос о данных вашего дашборда — анализ метрик, отчёты, тренды.</p>
                        <div class="d-flex flex-column gap-2 w-100">
                            <button class="btn btn-outline-secondary text-start suggestion-chip d-flex align-items-center gap-2 px-3 py-2" wire:click="quickAsk('Проанализируй текущие показатели продаж и дай рекомендации')" style="font-size:.8rem">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 text-muted"><path d="M3 3v18h18"/><path d="M7 16l4 -7l4 4l4 -7"/></svg>
                                Анализ продаж и рекомендации
                            </button>
                            <button class="btn btn-outline-secondary text-start suggestion-chip d-flex align-items-center gap-2 px-3 py-2" wire:click="quickAsk('Какие метрики показывают отрицательную динамику?')" style="font-size:.8rem">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 text-muted"><path d="M12 5l0 14"/><path d="M18 13l-6 6"/><path d="M6 13l6 6"/></svg>
                                Проблемные метрики
                            </button>
                            <button class="btn btn-outline-secondary text-start suggestion-chip d-flex align-items-center gap-2 px-3 py-2" wire:click="quickAsk('Составь краткий отчёт по ключевым показателям дашборда')" style="font-size:.8rem">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 text-muted"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2"/><path d="M9 9l1 0"/><path d="M9 13l6 0"/><path d="M9 17l6 0"/></svg>
                                Сводный отчёт по KPI
                            </button>
                        </div>
                    </div>
                @else
                    @foreach ($chat->messages as $item)
                        <div class="mb-3 d-flex {{ $item->status === 'send' ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="card {{ $item->status === 'send' ? 'bg-primary text-white' : 'bg-body-tertiary' }} mb-0 mw-100">
                                <div class="card-body py-2 px-3">
                                    <div class="small" style="white-space: pre-line;">{{ $item->message }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Input --}}
            <div class="p-3 border-top flex-shrink-0">
                <form wire:submit.prevent="sendMessage" class="input-group">
            <textarea
                class="form-control chat-textarea"
                id="chatInput"
                placeholder="Спросите о ваших данных…"
                rows="1"
                wire:model.live="message"
                wire:keydown.enter.prevent="sendMessage"
                aria-label="Chat input"
            ></textarea>
                    <button type="submit" class="btn btn-primary px-3" id="chatSendBtn" title="Отправить (Enter)" aria-label="Send">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14l11 -11"/><path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5"/></svg>
                    </button>
                </form>
                <div class="text-center text-muted mt-1" style="font-size:.68rem">Enter — отправить &nbsp;·&nbsp; Shift+Enter — новая строка</div>
            </div>

        </aside>

        <script>
            document.addEventListener('livewire:init', () => {
                const el = document.getElementById('chatMessages');
                const scrollDown = () => { if(el) el.scrollTop = el.scrollHeight; };
                scrollDown();
                Livewire.on('scroll-chat', () => setTimeout(scrollDown, 50));
            });
        </script>


    </div>{{-- /dashboard-wrapper --}}

    {{-- Backdrop (mobile) --}}
    <div class="chat-backdrop d-none" id="chatBackdrop" onclick="toggleChat()"></div>

    {{-- FAB (mobile only, shown via CSS) --}}
    <button class="chat-fab" id="chatFab" onclick="toggleChat()" aria-label="Open AI Assistant">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8a4 4 0 0 1 4 4"/><path d="M12 4a8 8 0 0 1 8 8"/><path d="M12 20a8 8 0 0 1 -8 -8"/><circle cx="12" cy="12" r="1"/></svg>
    </button>

</div>
