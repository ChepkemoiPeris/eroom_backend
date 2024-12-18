<style>
    @import url(https://unpkg.com/@webpixels/css@1.1.5/dist/index.css);
    @import url("https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.4.0/font/bootstrap-icons.min.css");
</style> 
<div class="h-screen flex-grow-1 overflow-y-lg-auto">
        <!-- Header -->
        <header class="bg-surface-primary border-bottom pt-6">
            <div class="container-fluid">
                <div class="mb-npx"> 
                    <!-- Nav -->
                    <ul class="nav nav-tabs mt-4 overflow-x border-0">
                        <li class="nav-item ">
                            <a href="#" class="nav-link active">User Data</a>
                        </li> 
                    </ul>
                </div>
            </div>
        </header>
        <!-- Main -->
        <main class="py-6 bg-surface-secondary">
            <div class="container-fluid">
                <!-- Card stats -->
                <div class="row g-6 mb-6">
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <span class="h6 font-semibold text-muted text-sm d-block mb-2">Users</span>
                                        <span class="h3 font-bold mb-0">{{ $currentWeekUsers }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-tertiary text-white text-lg rounded-circle">
                                            <i class="bi bi-people"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 mb-0 text-sm"> 
                                    <span class="text-nowrap text-xs text-muted">Current Week</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <span class="h6 font-semibold text-muted text-sm d-block mb-2">Users</span>
                                        <span class="h3 font-bold mb-0">{{ $currentMonthUsers }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-primary text-white text-lg rounded-circle">
                                            <i class="bi bi-people"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 mb-0 text-sm"> 
                                    <span class="text-nowrap text-xs text-muted">Current Month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <span class="h6 font-semibold text-muted text-sm d-block mb-2">Users</span>
                                        <span class="h3 font-bold mb-0">{{ $totalUsers }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-info text-white text-lg rounded-circle">
                                            <i class="bi bi-people"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 mb-0 text-sm"> 
                                    <span class="text-nowrap text-xs text-muted">Total Users</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <span class="h6 font-semibold text-muted text-sm d-block mb-2">Posts</span>
                                        <span class="h3 font-bold mb-0">{{ $totalPosts }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-warning text-white text-lg rounded-circle">
                                            <i class="bi bi-clock-history"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 mb-0 text-sm"> 
                                    <span class="text-nowrap text-xs text-muted">Total Posts</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-6 mb-6">
                  <canvas id="userChart"></canvas>
                </div>
                <div class="card shadow border-0 mb-7">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Pending Posts</h5>
                        <a href="{{ url('dashboard/posts') }}">View All Posts</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Image</th>
                                    <th scope="col">Room Type</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Title</th> 
                                    <th scope="col">City</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($pendingPosts as $post)
                                <tr>
                                    <td>
                                        @php
                                            $firstImage = \App\Models\RoomImage::where('post_id', $post->id)->first();
                                        @endphp
                                        @if($firstImage) 
                                             <img alt="..." src="{{ asset('storage/' . $firstImage->image_url ) }}" class="avatar avatar-sm rounded-circle me-2">
                                        @else
                                            No image available
                                        @endif
                                       
                                    </td>
                                    <td>{{ $post->room_type }}</td>
                                    <td>{{ $post->price }}</td>
                                    <td>{{ $post->title }}</td>
                                    <td>{{ $post->city }}</td>
                                    <td>{{ $post->user->email }}</td>
                                    <td>
                                        <span class="badge badge-lg badge-dot">
                                            <i class="bg-danger"></i>Pending
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-sm btn-neutral dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Manage</a> 
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item"
                                                    href="{{ url('dashboard/post/view/' . $post->id) }}">View</a>
                                                </li> 
                                                <li><a class="dropdown-item"
                                                    href="{{ url('dashboard/post/approve/' . $post->id) }}">Approve</a>
                                                </li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" 
                                                       data-bs-target="#declineModal" 
                                                       data-id="{{ $post->id }}">Decline</a></li>
                                              
                                               </li>
                                                <li><a class="dropdown-item" href="#" id="softDelete"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{$post->id}}">Delete</a></li>
                                            </ul> 
                                    </td>
                                </tr>
                                @endforeach 
                            </tbody>
                        </table>
                    </div> 
                </div>
            </div>
        </main>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  var ctx = document.getElementById('userChart').getContext('2d');
    var userChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($dates), // Dates for the current month
            datasets: [{
                label: 'Users Registered',
                data: @json($userCounts), // User counts corresponding to the dates
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                    title: {
                        display: true,
                        text: 'Dates ({{ $currentMonthName }})'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'User Registrations - {{ $currentMonthName }}'
                }
            }
        }
    });
</script>
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