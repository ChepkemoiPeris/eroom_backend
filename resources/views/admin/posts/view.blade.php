@extends('admin.dashboard.index')
@section('contents')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 card_title_part">
                            <i class="fab fa-gg-circle"></i>View Post Information
                        </div>
                        <div class="col-md-6 card_button_part">
                            <a href="{{ url('dashboard/post/approve/' . $data->id) }}" class="btn btn-sm btn-success mx-2">Approve</a>
                            <!-- <a href="{{ url('dashboard/post/decline/' . $data->id) }}" class="btn btn-sm btn-danger mx-2">Decline</a> -->
                            <a href="#" class="btn btn-sm btn-danger mx-2" data-bs-toggle="modal" data-bs-target="#declineModal">Decline</a>

                            <a href="{{ url('dashboard/post/edit/' . $data->id) }}" class="btn btn-sm btn-dark mx-2"><i class="fas fa-pen"></i>Edit</a>
                            <a href="{{url('dashboard/posts')}}" class="btn btn-sm btn-dark"><i class="fas fa-th"></i>All Posts</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <div class="row text-center">
                        <div class="col-md-3"></div>
                        <div class="col-md-7">

                            @if (Session::has('success'))
                                <div class="alert alert-success">
                                    <strong>Success ! </strong>{{ Session::get('success') }}
                                </div>
                            @endif

                            @if (Session::has('error'))
                                <div class="alert alert-danger">
                                    <strong>Opps ! </strong>{{ Session::get('error') }}
                                </div>
                            @endif

                        </div>
                        <div class="col-md-2"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <table class="table table-bordered table-striped table-hover custom_view_table">
                               <tr>
                                    <td>Room Type</td>
                                    <td>:</td>
                                    <td>{{$data->room_type}}</td>
                                </tr>
                                <tr>
                                    <td>Price</td>
                                    <td>:</td>
                                    <td>{{$data->price}}</td>
                                </tr>
                                <tr>
                                    <td>Post Title</td>
                                    <td>:</td>
                                    <td>{{$data->title}}</td>
                                </tr>
                                <tr>
                                    <td>Post Description</td>
                                    <td>:</td>
                                    <td>{{$data->description}}</td>
                                </tr>
                                <tr>
                                    <td>Post User Email</td>
                                    <td>:</td>
                                    <td>{{$data->user->email}}</td>
                                </tr>
                                <tr>
                                    <td>Province</td>
                                    <td>:</td>
                                    <td>{{$data->province}}</td>
                                </tr>
                                <tr>
                                    <td>City</td>
                                    <td>:</td>
                                    <td>{{$data->city}}</td>
                                </tr>
                                <tr>
                                    <td>Suburb</td>
                                    <td>:</td>
                                    <td>{{$data->suburb}}</td>
                                </tr>
                                 <tr>
                                    <td>Availability</td>
                                    <td>:</td>
                                    <td>{{$data->status}}</td>
                                </tr>
                                <tr>
                                    <td>Approval status</td>
                                    <td>:</td>
                                    <td>
                                    @if($data->approval == 1)
                                        Approved
                                    @elseif($data->approval == 2)
                                        Declined
                                    @else
                                        Pending
                                    @endif
                                    </td>
                                </tr> 
                                @if($data->approval == 2)
                                <tr>
                                <td>Decline Reason</td>
                                <td>:</td>
                                <td>
                                    @if($data->approval == 2 && $data->decline_reason)
                                        {{ $data->decline_reason }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            @endif
                                <tr>
                                    <td>Created Time</td>
                                    <td>:</td>
                                    <td>{{$data->created_at->format('d-M-y | D | h:i:s A')}}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-2"></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <h5>Room Images</h5>
                            <div class="row">
                                @foreach ($data->roomImages as $image)
                                    <div class="col-md-3 mb-3">
                                        <img src="{{ asset('storage/' . $image->image_url) }}" alt="Room Image" class="img-fluid" style="max-height: 200px;">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-2"></div>
                    </div>
                </div>
                 <!-- Decline Modal -->
            <div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="declineForm" action="{{ url('dashboard/post/decline/' . $data->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="declineModalLabel">Decline Post</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="declineReason">Decline Reason</label>
                                    <textarea name="decline_reason" id="declineReason" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            </div>
        </div>
    </div>
    @endsection
