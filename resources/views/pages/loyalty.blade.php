@extends('navigation.master')

@section('kredo','active')
@section('reward','active')

@section('content')
    <!-- Page Content  -->

    <div id="content-page" class="content-page">
        <div class="container-fluid">

            @if(session()->get('message') != '')
                <div class="alert text-white bg-{{ session()->get('status') }}" role="alert">
                    <div class="iq-alert-text">
                        <p>{{ session()->get('message') }}</p>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            @endif

            <div class="row">

                <div class="col-sm-12">
                    <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                        <div class="iq-card-header d-flex justify-content-between">
                            <div class="iq-header-title">
                                <h4 class="card-title">Customer Reward</h4>
                            </div>

                        </div>
                        <div class="iq-card-body">
                            <div class="table-responsive">
                                <table class="table mb-0  display table table-responsive-sm table-bordered table-sm" id="dataTable">
                                    <thead>
                                    <tr>
                                        {{-- <th scope="col">Name</th> --}}
                                        <th scope="col">Phone Number</th>
                                        <th scope="col">Total Airtime Purchased</th>
                                        <th scope="col">Bonus</th>
                                        <th scope="col">Action</th>

                                    </tr>
                                    </thead>
                                    <tbody>

                                        @foreach($table as $key => $data)
                                            <tr>
                                                {{-- <td>{{$data->msisdn}}</td>
                                                <td>{{number_format($data->total)}}</td>
                                                <td>{{$data->total*0.02}}</td>
                                                <td> <div class="badge badge-pill badge-success">Reward</div></td> --}}
                                            </tr>
                                        @endforeach


                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>



@endsection
