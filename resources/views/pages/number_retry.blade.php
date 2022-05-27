@extends('navigation.master')

@section('retry','active')
@section('namba','active')

@section('content')
    <!-- Page Content  -->

     <!-- Page Content -->
     <div id="content-page" class="content-page ">
        <div class="container-fluid ">
            <div class="row iq-header-title">

                {{-- <div class="col-sm-12 col-lg-1"></div> --}}
                <div class="col-lg-4">
                    <div class="card iq-mb-3">
                       <div class="card-header">
                        <b>INVALID NUMBER - FAILURE</b>
                       </div>
                       <div class="card-body">
                          {{-- <h4 class="card-title">Special title treatment</h4> --}}
                          <p class="card-text">Buy airtime when the customer inputs a wrong number.</p>

                          @if(session()->get('message') != '')

                          <div class="text-{{ session()->get('status') }}">

                              {{ session()->get('message') }}

                          </div>
                          @endif

                          <form class="form-horizontal" method="POST" action="{{ route('namba')}}">
                            @csrf
                           <div class="form-group">
                             <label for="furl"><b>
                                MPESA Code:</b></label>
                             <input type="text" class="form-control" name="mpesa_receipt" id="furl" placeholder="Transaction Code">

                             <div class="text-danger">
                                 @error('mpesa_receipt')
                                 {{ $message }}
                                 @enderror
                             </div>
                          </div>

                          <div class="form-group">
                            <label for="furl"><b>Phone Number:</b></label>
                            <input type="number" class="form-control" name="number"  placeholder="Phone Number">

                            <div class="text-danger">
                                @error('number')
                                {{ $message }}
                                @enderror
                            </div>
                         </div>

                           <div class="form-group">
                              <button type="submit" class="btn btn-primary">Submit</button>
                              <button type="reset" class="btn iq-bg-danger">Reset</button>
                           </div>
                        </form>
                       </div>
                    </div>
                 </div>



        </div>
    </div>



@endsection
