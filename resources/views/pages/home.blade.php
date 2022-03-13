@extends('navigation.master')

@section('home','active')

@section('content')
    <!-- Page Content  -->
    <div id="content-page" class="content-page">
        <div class="container-fluid">
            <div class="row">

                <div class="col-sm-12 col-md-6 col-lg-3">
                    <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                        <div class="iq-card-body iq-box-relative">
                            <div class="iq-box-absolute icon iq-icon-box rounded-circle iq-bg-primary">
                                <i class="las la-users"></i>
                            </div>
                            <p class="text-secondary">Total Transactions (Airtime)</p>
                            <div class="d-flex align-items-center justify-content-between">

                                <h4><b> {{ number_format($total_trans) }} </b></h4>

                                <span class="text-danger">
                                  <a href="txn"> View
                                      <i class="ri-arrow-right-fill"></i>
                                  </a>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-3">
                    <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                        <div class="iq-card-body iq-box-relative">
                            <div class="iq-box-absolute icon iq-icon-box rounded-circle iq-bg-danger">
                                <i class="las la-user"></i>
                            </div>
                            <p class="text-secondary">Total Airtime Purchased</p>
                            <div class="d-flex align-items-center justify-content-between">

                                <h4><b>KES {{ number_format($total_air) }}</b></h4>
                                <span ><b><a href="#" class="text-danger"> View <i class="ri-arrow-right-fill"></i></a></b></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-3">
                    <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                        <div class="iq-card-body iq-box-relative">
                            <div class="iq-box-absolute icon iq-icon-box rounded-circle iq-bg-success">
                                <i class="ri-database-2-line"></i>
                            </div>
                            <p class="text-secondary">Today Transactions (MPESA)</p>
                            <div class="d-flex align-items-center justify-content-between">

                                <h4><b>{{ number_format($trans) }}</b></h4>
                                <span ><b><a href="mpesa" class="text-success"> View <i class="ri-arrow-right-fill"></i></a></b></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-3">
                    <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                        <div class="iq-card-body iq-box-relative">
                            <div class="iq-box-absolute icon iq-icon-box rounded-circle iq-bg-info">
                                <i class="las la-coins"></i>
                            </div>
                            <p class="text-secondary">Today Airtime</p>
                            <div class="d-flex align-items-center justify-content-between">

                                <h4><b>KES {{ number_format($air) }}</b></h4>
                                <span ><b><a href="transactions" class="text-info"> View <i class="ri-arrow-right-fill"></i></a></b></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                        <div class="iq-card-header d-flex justify-content-between">
                            <div class="iq-header-title">
                                <h4 class="card-title"> {{ $year.' - ' }} Summary </h4>
                            </div>
                        </div>
                        <div class="iq-card-body row m-0 align-items-center pb-0">
                            <div class="col-12">
                                {!! $chart->container() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {!! $chart->script() !!}

@endsection
