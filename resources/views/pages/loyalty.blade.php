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
                            {{-- <a href="{{ route('reward')}}" class="btn btn-info">Reward</a> --}}
                        </div>
                        <div class="iq-card-body">
                            <div class="table-responsive">
                                <table class="table mb-0  display table table-responsive-sm table-bordered table-sm" id="dataTable">
                                    <thead>
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Phone Number</th>
                                        <th scope="col">Total Airtime Purchased</th>
                                        <th scope="col">Bonus</th>
                                        <th scope="col">Status</th>

                                    </tr>
                                    </thead>
                                    <tbody>

                                        @foreach($table as $key => $data)
                                            <tr>
                                                <td>{{$data->fname}}</td>
                                                <td>{{$data->phone}}</td>
                                                <td>{{number_format($data->amount)}}</td>
                                                <td>{{number_format($data->bonus)}}</td>
                                                <td>
                                                    @if($data->status == '0')
                                                    <div class="badge badge-pill badge-success">Rewarded</div>
                                                    @elseif ($data->status !='0')
                                                    <div class="badge badge-pill badge-warning">Pending</div>
                                                    @endif
                                                    {{-- <div class="badge badge-pill badge-warning">{{$data->status}}</div> --}}
                                                </td>
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
