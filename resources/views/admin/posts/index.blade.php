@extends('admin.dashboard.index')
@section('contents')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header">
                     <div class="row">
                        <div class="col-md-4 card_title_part">
                            <i class="fab fa-gg-circle"></i> All Posts
                        </div>
                        <div class="col-md-8 text-end">
                            <form action="{{ url('dashboard/posts') }}" method="GET" class="d-flex">
                                <input type="text" name="search" class="form-control me-2" placeholder="Search..."
                                    value="{{ request('search') }}">
                                <select name="status" class="form-select me-2">
                                    <option value="">All Statuses</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Approved</option>
                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Declined</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Pending</option>
                                </select>
                                <select name="availability" class="form-select me-2">
                                    <option value="">All Availability</option>
                                    <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="not_available" {{ request('availability') == 'not_available' ? 'selected' : '' }}>Not Available</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Filter</button>
                            </form>
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

                    <table class="table table-bordered table-striped table-hover custom_table">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Room Type</th>
                                <th>Price</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>City</th>
                                <th>Email</th>
                                <th>Approval</th> 
                                <th>Manage</th>
                            </tr>
                        </thead>
                        <tbody> 
                            <?php $i = ($posts->currentPage() - 1) * $posts->perPage() + 1; ?>
                            @foreach ($posts as $data)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    
                                    <td>
                                        @php
                                            $firstImage = \App\Models\RoomImage::where('post_id', $data->id)->first();
                                        @endphp
                                        @if($firstImage)
                                            <img height="100" src="{{ asset('storage/' . $firstImage->image_url) }}" alt="Room Image" width="100">
                                        @else
                                            No image available
                                        @endif
                                    </td> 
                                    <td>{{ $data->room_type }}</td>
                                    <td>{{ $data->price }}</td>
                                    <td>{{ $data->title }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->city }}</td>
                                    
                                    <td>{{ $data->user->email ?? 'N/A' }}</td>
                                  
                                    <td> 
                                    @if($data->approval == 1)
                                        Approved
                                    @elseif($data->approval == 2)
                                        Declined
                                    @else
                                        Pending
                                    @endif
                                    </td> 
                                    <td>
                                    <div class="btn-group btn_group_manage" role="group" aria-label="Manage">
                                        <button type="button" class="btn btn-sm btn-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            Manage
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="{{ url('dashboard/post/view/' . $data->id) }}">View</a></li> 
                                            @if($data->approval == 0 || $data->approval == 2) <!-- Only show Approve for Pending -->
                                                <li><a class="dropdown-item" href="{{ url('dashboard/post/approve/' . $data->id) }}">Approve</a></li>
                                            @endif
                                            @if($data->approval == 0 || $data->approval == 1) <!-- Show Decline for Pending or Approved -->
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#declineModal" data-id="{{ $data->id }}">Decline</a></li>
                                            @endif
                                            <li><a class="dropdown-item" href="#" id="softDelete" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{$data->id}}">Delete</a></li>
                                        </ul>
                                    </div>
                                </td>


                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center">
                        {{ $posts->links() }}
                    </div>
                </div>
                
            </div>
        </div>
    </div>
     <!-- Decline Modal -->
     <div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="post" action="">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="declineModalLabel">Decline Post</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="decline_id" name="post_id">
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason for Decline</label>
                                    <textarea class="form-control" id="reason" name="decline_reason" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-danger">Decline</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
    <!-- Delete Modal part start -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">

            <form method="post" action="{{url('dashboard/post/delete')}}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Confirm Message</h5>
                    </div>
                    <div class="modal-body modal_body">
                        <input type="hidden" id="modal_id" name="modal_id"/>
                        Are you sure want to delete this data item?
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Confirm</button>
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
    <!-- Delete Modal part end -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var declineModal = document.getElementById('declineModal');

        declineModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var postId = button.getAttribute('data-id'); // Extract the post ID

            // Update the form action with the specific post ID
            var form = declineModal.querySelector('form');
            form.action = "{{ url('dashboard/post/decline') }}/" + postId;
        });
    });
</script>

@endsection
