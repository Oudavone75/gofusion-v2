<div class="left-side-bar">
    <div class="brand-logo">
        <a href="{{ route('company_admin.dashboard') }}">
            <img src="{{ asset('vendors/images/admin/desktop-icon.svg') }}" alt="" class="dark-logo">
            <img src="{{ asset('vendors/images/admin/desktop-icon.svg') }}" alt="" class="light-logo">
        </a>
        <div class="close-sidebar" data-toggle="left-sidebar-close">
            <i class="ion-close-round"></i>
        </div>
    </div>
    <div class="menu-block customscroll">
        <div class="sidebar-menu">
            <ul id="accordion-menu">
                <li>
                    <a href="{{ route('company_admin.dashboard') }}"
                        class="dropdown-toggle no-arrow {{ request()->routeIs('company_admin.dashboard') ? 'active' : '' }}">
                        <span class="micon icon-copy dw dw-home"></span><span class="mtext">Dashboard</span>
                    </a>
                </li>
                @if (auth('web')->user()->hasDirectPermission('view employees'))
                    <li class="dropdown">
                        <a href="{{ route('company_admin.employees.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('company_admin.employees.*') ? 'active' : '' }}">
                            <span class="micon icon-copy dw dw-user2"></span><span class="mtext">Employees</span>
                        </a>
                    </li>
                @endif
                @if (auth('web')->user()->hasDirectPermission('view departments'))
                    <li class="dropdown">
                        <a href="{{ route('company_admin.departments.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('company_admin.departments.*') ? 'active' : '' }}">
                            <span class="micon dw dw-library"></span><span class="mtext">Departments</span>
                        </a>
                    </li>
                @endif
                @if (auth('web')->user()->hasDirectPermission('view campaigns'))
                    <li class="dropdown">
                        <a href="{{ route('company_admin.campaigns.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('company_admin.campaigns.*') ? 'active' : '' }}">
                            <span class="micon dw dw-speaker-1"></span><span class="mtext">Campaigns</span>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle {{ request()->routeIs('company_admin.performance.*') ? 'active' : '' }}">
                            <span class="micon dw dw-analytics-21"></span><span class="mtext">Performance</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="{{ route('company_admin.dashboard') }}#performance">Dashboard</a></li>
                            <li><a href="{{ route('company_admin.performance.export.page') }}">Export</a></li>
                        </ul>
                    </li>
                @endif
                @if (auth('web')->user()->hasDirectPermission('view sessions'))
                    <li class="dropdown">
                        <a href="{{ route('company_admin.sessions.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('company_admin.sessions.*') ? 'active' : '' }}">
                            <span class="micon icon-copy dw dw-browser1"></span><span class="mtext">Sessions</span>
                        </a>
                    </li>
                @endif
                @if (auth('web')->user()->hasDirectPermission('manage imports'))
                    <li class="dropdown">
                        <a href="{{ route('company_admin.import-file.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('company_admin.import-file.*') ? 'active' : '' }}">
                            <span class="micon icon-copy dw dw-file"></span><span class="mtext">Import</span>
                        </a>
                    </li>
                @endif
                @if (auth('web')->user()->hasDirectPermission('view steps'))
                    <li class="dropdown">
                        <a href="javascript:;"
                            class="dropdown-toggle {{ request()->routeIs('company_admin.steps.*') ? 'active' : '' }}">
                            <span class="micon icon-copy dw dw-left-indent"></span><span class="mtext">Steps</span>
                        </a>
                        <ul class="submenu">
                            @if (auth('web')->user()->hasDirectPermission('view quiz'))
                                <li>
                                    <a href="{{ route('company_admin.steps.quiz.index') }}"
                                        class="{{ request()->routeIs('company_admin.steps.quiz.*') ? 'active' : '' }}">Quiz</a>
                                </li>
                            @endif
                            @if (auth('web')->user()->hasDirectPermission('view challenges'))
                                <li>
                                    <a href="{{ route('company_admin.steps.images.index') }}"
                                        class="{{ request()->routeIs('company_admin.steps.images.*') ? 'active' : '' }}">Challenges
                                        to Complete</a>
                                </li>
                            @endif
                            {{-- <li>
                                <a href="{{ route('company_admin.steps.events.index') }}"
                                    class="{{ request()->routeIs('company_admin.steps.events.*') ? 'active' : '' }}">Event</a>
                            </li>
                            <li>
                                <a href="{{ route('company_admin.steps.challenges-step.index') }}"
                                    class="{{ request()->routeIs('company_admin.steps.challenges-step.*') ? 'active' : '' }}">Challenge</a>
                            </li> --}}
                            @if (auth('web')->user()->hasDirectPermission('view spinwheel'))
                                <li>
                                    <a href="{{ route('company_admin.steps.spin-wheel.index') }}"
                                        class="{{ request()->routeIs('company_admin.steps.spin-wheel.*') ? 'active' : '' }}">SpinWheel</a>
                                </li>
                            @endif
                            @if (auth('web')->user()->hasDirectPermission('view survey feedback'))
                                <li>
                                    <a href="{{ route('company_admin.steps.survey-feedback.index') }}"
                                        class="{{ request()->routeIs('company_admin.steps.survey-feedback.*') ? 'active' : '' }}">
                                        Survey / Feedback
                                    </a>
                                </li>
                            @endif
                    </li>
                @endif
            </ul>
            </li>
            @if (auth('web')->user()->hasDirectPermission('view inspiration challenges'))
                <li class="dropdown">
                    <a href="{{ route('company_admin.inspiration-challenges.index') }}"
                        class="dropdown-toggle no-arrow {{ request()->routeIs('company_admin.inspiration-challenges.*') ||
                        request()->routeIs('company_admin.inspiration-challenges.pending') ||
                        request()->routeIs('company_admin.inspiration-challenges.pending.*')
                            ? 'active'
                            : '' }} ? 'active' : '' }}">
                        <span class="micon dw dw-startup"></span><span class="mtext">Inspiration Challenges</span>
                    </a>
                </li>
            @endif
            {{-- @if (auth('web')->user()->hasDirectPermission('view news feeds'))
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon icon-copy dw dw-newspaper"></span><span class="mtext">News</span>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="{{ route('company_admin.news-category.index') }}"
                                class="{{ request()->routeIs('company_admin.news-category.*') ? 'active' : '' }}">
                                Category
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('company_admin.news-feed.index') }}"
                                class="{{ request()->routeIs('company_admin.news-feed.*') ? 'active' : '' }}">
                                Feed
                            </a>
                        </li>
                    </ul>
                </li>
            @endif --}}
            @if (auth('web')->user()->hasDirectPermission('view rewards'))
                <li class="dropdown">
                    <a href="{{ route('company_admin.rewards.index') }}"
                        class="dropdown-toggle no-arrow {{ request()->routeIs('company_admin.rewards.*') ? 'active' : '' }}">
                        <span class="micon icon-copy dw dw-money-1"></span><span class="mtext">Rewards</span>
                    </a>
                </li>
            @endif
            @if (auth('web')->user()->hasDirectPermission('view gallery'))
                <li class="dropdown">
                    <a href="{{ route('company_admin.gallery.index', ['company_id' => auth('web')->user()->company->id]) }}"
                        class="dropdown-toggle no-arrow {{ request()->routeIs('company_admin.gallery.*') ? 'active' : '' }}">
                        <span class="micon icon-copy dw dw-image"></span>
                        <span class="mtext">Gallery</span>
                    </a>
                </li>
            @endif
            @if (auth('web')->user()->hasRole('Company Admin'))
                <li class="dropdown">
                    <a href="{{ route('company_admin.sub-admins.list') }}"
                        class="dropdown-toggle no-arrow {{ request()->routeIs('company_admin.sub-admins.*') ? 'active' : '' }}">
                        <span class="micon icon-copy dw dw-user-13"></span><span class="mtext">Sub-Admins</span>
                    </a>
                </li>
            @endif
            @if (auth('web')->user()->hasDirectPermission('view posts'))
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon icon-copy dw dw-meeting"></span><span class="mtext">Social Feed</span>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="{{ route('company_admin.social-feed.list') }}"
                                class="dropdown-toggle no-arrow {{ request()->routeIs('company_admin.social-feed.list') ||
                                request()->routeIs('company_admin.social-feed.create') ||
                                request()->routeIs('company_admin.social-feed.edit') ||
                                request()->routeIs('company_admin.social-feed.view')
                                    ? 'active'
                                    : '' }}">
                                Posts
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('company_admin.social-feed.reported-posts-list') }}"
                                class="{{ request()->routeIs('company_admin.social-feed.reported-posts-list') ? 'active' : '' }}">
                                Reports
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
        </div>
    </div>
</div>
