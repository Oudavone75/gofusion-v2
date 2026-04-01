<div class="left-side-bar">
    <div class="brand-logo">
        <a href="{{ route('admin.dashboard') }}">
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
                    <a href="{{ route('admin.dashboard') }}"
                        class="dropdown-toggle no-arrow {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="micon icon-copy dw dw-home"></span><span class="mtext">Dashboard</span>
                    </a>
                </li>
                @if (auth('admin')->user()->hasDirectPermission('view citizens'))
                    <li class="dropdown">
                        <a href="{{ route('admin.citizens.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('admin.citizens.*') ? 'active' : '' }}">
                            <span class="micon icon-copy dw dw-user2"></span><span class="mtext">Citizens</span>
                        </a>
                    </li>
                @endif
                @if (auth('admin')->user()->hasDirectPermission('view companies'))
                    <li class="dropdown">
                        <a href="javascript:;"
                            class="dropdown-toggle {{ request()->routeIs('admin.company.*') ? 'active' : '' }}">
                            <span class="micon dw dw-building1"></span><span class="mtext">Company</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="{{ route('admin.company.index') }}"
                                    class="{{ request()->routeIs('admin.company.*') ? 'active' : '' }}">Company List</a>
                            </li>
                            @if (auth('admin')->user()->hasDirectPermission('view departments'))
                                <li><a href="{{ route('admin.department.index') }}"
                                        class="{{ request()->routeIs('admin.department.*') ? 'active' : '' }}">Departments
                                        List</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (auth('admin')->user()->hasDirectPermission('view campaigns'))
                    <li class="dropdown">
                        <a href="{{ route('admin.campaign.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('admin.campaign.*') ? 'active' : '' }}">
                            <span class="micon dw dw-speaker-1"></span><span class="mtext">Campaigns / Season</span>
                        </a>
                    </li>
                @endif
                @if (auth('admin')->user()->hasDirectPermission('view sessions'))
                    <li class="dropdown">
                        <a href="{{ route('admin.sessions.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}">
                            <span class="micon icon-copy dw dw-browser1"></span><span class="mtext">Sessions</span>
                        </a>
                    </li>
                @endif
                @if (auth('admin')->user()->hasDirectPermission('manage imports'))
                    <li class="dropdown">
                        <a href="{{ route('admin.import-file.index') }}"
                            class="dropdown-toggle no-arrow {{ request()->routeIs('admin.import-file.*') ? 'active' : '' }}">
                            <span class="micon icon-copy dw dw-file"></span><span class="mtext">Import</span>
                        </a>
                    </li>
                @endif
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle {{ request()->routeIs('admin.performance.*') ? 'active' : '' }}">
                        <span class="micon icon-copy dw dw-analytics-16"></span><span class="mtext">Performance</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{ route('admin.dashboard') }}#performance">Dashboard</a></li>
                        <li><a href="{{ route('admin.performance.export.page') }}">Export</a></li>
                    </ul>
                </li>
                @if (auth('admin')->user()->hasDirectPermission('view steps'))
                    <li class="dropdown">
                        <a href="javascript:;"
                            class="dropdown-toggle {{ request()->routeIs('admin.steps.*') ? 'active' : '' }}">
                            <span class="micon icon-copy dw dw-left-indent"></span><span class="mtext">Steps</span>
                        </a>
                        <ul class="submenu">
                            @if (auth('admin')->user()->hasDirectPermission('view quiz'))
                                <li>
                                    <a href="{{ route('admin.quiz.index') }}"
                                        class="{{ request()->routeIs('admin.quiz.*') ? 'active' : '' }}">Quiz</a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasDirectPermission('view challenges'))
                                <li>
                                    <a href="{{ route('admin.images.index') }}"
                                        class="{{ request()->routeIs('admin.images.*') ? 'active' : '' }}">Challenges
                                        to
                                        Complete</a>
                                </li>
                            @endif
                            {{-- <li>
                                <a href="{{ route('admin.events.index') }}"
                                    class="{{ request()->routeIs('admin.events.*') ? 'active' : '' }}">Event</a>
                            </li> --}}
                            {{-- <li>
                                <a href="{{ route('admin.challenges-step.index') }}"
                                    class="{{ request()->routeIs('admin.challenges-step.*') ? 'active' : '' }}">Challenge</a>
                            </li> --}}
                            @if (auth('admin')->user()->hasDirectPermission('view spinwheel'))
                                <li>
                                    <a href="{{ route('admin.spin.index') }}"
                                        class="{{ request()->routeIs('admin.spin.*') ? 'active' : '' }}">SpinWheel</a>
                                </li>
                            @endif
                    </li>
                    @if (auth('admin')->user()->hasDirectPermission('view survey feedback'))
                        <li>
                            <a href="{{ route('admin.survey-feedback.index') }}"
                                class="{{ request()->routeIs('admin.survey-feedback.*') ? 'active' : '' }}">Survey /
                                Feedback</a>
                        </li>
                    @endif
                @endif
            </ul>
            @if (auth('admin')->user()->hasDirectPermission('view inspiration challenges'))
                <li>
                    <a href="{{ route('admin.inspiration-challenges.index') }}"
                        class="dropdown-toggle no-arrow {{ request()->routeIs('admin.inspiration-challenges.*') ||
                        request()->routeIs('admin.inspiration-challenges.pending') ||
                        request()->routeIs('admin.inspiration-challenges.pending.*')
                            ? 'active'
                            : '' }}">
                        <span class="micon dw dw-startup"></span><span class="mtext">Inspiration Challenges</span>
                    </a>
                </li>
            @endif
            {{-- @if (auth('admin')->user()->hasDirectPermission('view news feeds'))
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon icon-copy dw dw-newspaper"></span><span class="mtext">News</span>
                    </a>
                    <ul class="submenu">
                        @if (auth('admin')->user()->hasDirectPermission('view news categories'))
                            <li>
                                <a href="{{ route('admin.news-category.index') }}"
                                    class="{{ request()->routeIs('admin.news-category.*') ? 'active' : '' }}">
                                    Category
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('admin.news-feed.index') }}"
                                class="{{ request()->routeIs('admin.news-feed.*') ? 'active' : '' }}">
                                Feed
                            </a>
                        </li>
                    </ul>
                </li>
            @endif --}}
            @if (auth('admin')->user()->hasDirectPermission('view rewards'))
                <li class="dropdown">
                    <a href="{{ route('admin.rewards.index') }}"
                        class="dropdown-toggle no-arrow {{ request()->routeIs('admin.rewards.*') ? 'active' : '' }}">
                        <span class="micon icon-copy dw dw-money-1"></span><span class="mtext">Rewards</span>
                    </a>
                </li>
            @endif
            @if (auth('admin')->user()->hasDirectPermission('view contact requests'))
                @php
                    $unreadContactCount = \App\Models\CompanyContact::where('mark_as_read', false)->count();
                @endphp
                <li class="dropdown">
                    <a href="{{ route('admin.company-contact.index') }}"
                        class="dropdown-toggle no-arrow {{ request()->routeIs('admin.company-contact.*') ? 'active' : '' }}">
                        <span class="micon icon-copy dw dw-chat-1"></span><span class="mtext">Contact Requests</span>
                        @if ($unreadContactCount > 0)
                            <span class="badge badge-danger ml-1" style="border-radius: 50%; width: 10px; height: 10px; display: inline-block; padding: 0;"></span>
                        @endif
                    </a>
                </li>
            @endif
            @if (auth('admin')->user()->hasDirectPermission('view gallery'))
                <li class="dropdown">
                    <a href="{{ route('admin.gallery.index') }}"
                        class="dropdown-toggle no-arrow {{ request()->routeIs('admin.gallery.*') ? 'active' : '' }}">
                        <span class="micon icon-copy dw dw-image"></span><span class="mtext">Gallery</span>
                    </a>
                </li>
            @endif
            @if (auth('admin')->user()->hasDirectPermission('view notifications'))
                <li class="dropdown">
                    <a href="{{ route('admin.notifications.index') }}"
                        class="dropdown-toggle no-arrow {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                        <span class="micon icon-copy dw dw-notification"></span><span
                            class="mtext">Notifications</span>
                    </a>
                </li>
            @endif
            @if (auth('admin')->user()->hasRole('Admin'))
                <li class="dropdown">
                    <a href="{{ route('admin.sub-admins.list') }}"
                        class="dropdown-toggle no-arrow {{ request()->routeIs('admin.sub-admins.*') ? 'active' : '' }}">
                        <span class="micon icon-copy dw dw-user-13"></span><span class="mtext">Sub-Admins</span>
                    </a>
                </li>
            @endif
            @if (auth('admin')->user()->hasDirectPermission('view posts'))
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon icon-copy dw dw-meeting"></span><span class="mtext">Social Feed</span>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="{{ route('admin.social-feed.list') }}"
                                class="dropdown-toggle no-arrow {{ request()->routeIs('admin.social-feed.list')
                                || request()->routeIs('admin.social-feed.create')
                                || request()->routeIs('admin.social-feed.edit')
                                || request()->routeIs('admin.social-feed.view')
                                ? 'active' : '' }}">
                                Posts
                            </a>
                        </li>
                        @if (auth('admin')->user()->hasDirectPermission('view posts reports'))
                            <li>
                                <a href="{{ route('admin.social-feed.reported-posts-list') }}"
                                    class="{{ request()->routeIs('admin.social-feed.reported-posts-list') ? 'active' : '' }}">
                                    Reports
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if (auth('admin')->user()->hasDirectPermission('view carbon assessments'))
                <li class="dropdown">
                    <a href="{{ route('admin.carbon-assessment.index') }}"
                        class="dropdown-toggle no-arrow {{ request()->routeIs('admin.carbon-assessment.*') ? 'active' : '' }}">
                        <span class="micon icon-copy dw dw-chat-1"></span><span class="mtext">Carbon Assessments</span>
                    </a>
                </li>
            @endif
            </ul>
        </div>
    </div>
</div>
