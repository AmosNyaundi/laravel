<!-- Sidebar  -->
<div class="iq-sidebar">
    <div class="iq-navbar-logo d-flex justify-content-between">
        <a href="{{ url('/') }}" class="header-logo">
            <img src="{{ asset('user/images/logo.png') }}" class="img-fluid rounded" alt="">
            <span style="font-size: 15px">{{ config('app.name') }}</span>
        </a>
        <div class="iq-menu-bt align-self-center">
            <div class="wrapper-menu">
                <div class="main-circle"><i class="ri-menu-line"></i></div>
                <div class="hover-circle"><i class="ri-close-fill"></i></div>
            </div>
        </div>
    </div>
    <div id="sidebar-scrollbar">
        <nav class="iq-sidebar-menu">
            <ul id="iq-sidebar-toggle" class="iq-menu">

                <li class="@yield('home')">
                    <a href="{{ route('home') }}" class="iq-waves-effect">
                        <i class="las la-home iq-arrow-left"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                 <li aria-expanded="true" class="@yield('transactions')">
                    <a href="#transactions" class="iq-waves-effect" data-toggle="collapse" aria-expanded="false">
                        <i class="las la-comment iq-arrow-left"></i>
                        <span>Transactions</span>
                        <i class="ri-arrow-right-s-line iq-arrow-right"></i>
                    </a>
                    <ul id="transactions" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                        <li class="@yield('all')">
                            <a href="{{ route('txn')}}">
                                <i class="las la-exchange-alt"></i>
                                Purchase
                            </a>
                        </li>
                        <li class="@yield('airtime')">
                            <a href="{{ route('airtime_txn')}}">
                                <i class="las la-paper-plane"></i>
                                Airtime
                            </a>
                        </li>
                        <li class="@yield('mpesa')">
                            <a href="mpesa">
                                <i class="las la-level-up-alt"></i>
                                MPESA
                            </a>
                        </li>

                    </ul>
                </li>


                <li aria-expanded="true" class="@yield('kredo')">
                    <a href="#kredo" class="iq-waves-effect" data-toggle="collapse" aria-expanded="false">
                        <i class="ri-arrow-go-forward-line iq-arrow-left"></i>
                        <span>Airtime</span>
                        <i class="ri-arrow-right-s-line iq-arrow-right"></i>
                    </a>
                    <ul id="kredo" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                        <li class="@yield('nunua')">
                            <a href="{{ route ('buy_airtime') }}">
                                <i class="las la-exchange-alt"></i>
                                Buy for Customer
                            </a>
                        </li>
                        <li class="@yield('bal')">
                            <a href="{{ route('balance')}}">
                                <i class="las la-level-up-alt"></i>
                                Check Balance
                            </a>
                        </li>

                    </ul>
                </li>

                <li aria-expanded="true" class="@yield('submanage')">
                    <a href="#subscribers" class="iq-waves-effect" data-toggle="collapse" aria-expanded="false">
                        <i class="ri-contacts-line iq-arrow-left"></i>
                        <span>Contacts</span>
                        <i class="ri-arrow-right-s-line iq-arrow-right"></i>
                    </a>
                    <ul id="subscribers" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                        <li class="@yield('request')">
                            <a href="{{ route('submanage.sms')}}">
                                <i class="ri-wechat-line"></i>
                                Bought Airtime
                            </a>
                        </li>
                        <li class="@yield('outbox')">
                            <a href="{{ route('submanage.outbox')}}">
                                <i class="ri-arrow-up-circle-line"></i>
                                Never Bought
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- <li aria-expanded="true" class="@yield('services')">
                    <a href="#service" class="iq-waves-effect" data-toggle="collapse" aria-expanded="false">
                        <i class="ri-file-list-line iq-arrow-left"></i>
                        <span>Services</span>
                        <i class="ri-arrow-right-s-line iq-arrow-right"></i>
                    </a>
                    <ul id="service" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                        <li class="@yield('create')">
                            <a href="">
                                <i class="fa fa-plus"></i>
                                Create Service
                            </a>
                        </li>
                        <li class="@yield('manage')">
                            <a href="">
                                <i class="ri-list-settings-line"></i>
                                Manage Services
                            </a>
                        </li>
                    </ul>
                </li> --}}

            </ul>
        </nav>
        <div class="p-3"></div>
    </div>
</div>
