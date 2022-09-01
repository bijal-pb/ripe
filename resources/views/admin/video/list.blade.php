@extends('layouts.admin.master') 
@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class='subheader-icon fal fa-play-circle'></i> Videos <span class='fw-300'></span> <sup class='badge badge-primary fw-500'></sup>
        {{-- <small>
            Insert page description or punch line
        </small> --}}
    </h1>
</div>
<!-- Your main content goes below here: -->
<div class="row">
    <div class="col-xl-12">
        <div id="panel-1" class="panel">
            <div class="panel-hdr">
                <h2>
                Videos <span class="fw-300"><i></i></span>
                </h2>
                <div class="panel-toolbar">
                    <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
                    <button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
                    {{-- <button class="btn btn-panel" data-action="panel-close" data-toggle="tooltip" data-offset="0,10" data-original-title="Close"></button> --}}
                </div>
            </div>
            <div class="panel-container show">
                 <!-- <button type="button" id="btn-add" class="btn btn-primary float-right m-3" data-toggle="modal" data-target="#default-example-modal">Add Wholeseller</button> -->
                <div class="panel-content" >
                    <div id="categoryData">
                        {{-- <div class="category-filter">
                            <select id="categoryFilter" class="form-control">
                              <option value="">All</option>
                              <option value="1">Published</option>
                              <option value="0">Unpublished</option>
                            </select>
                        </div> --}}
                        <table id="video-table" class="table table-bordered table-hover table-striped w-100 dataTable dtr-inline">
                            <thead class="bg-primary-600">
                                <tr>
                                    <th>Id </th>
                                    <th>Title </th>
                                    <th>Videos</th>
                                    <th>Chef Name</th>
                                    <th>Food Method</th>
                                    <th>Course</th>
                                    <th>Country</th>
                                    <th>Preparation Time</th>
                                    <th>Serves</th>
                                    <th><select id="publishFilter" class="form-control">
                                        <option value="">All</option>
                                        <option value="1">Published</option>
                                        <option value="0">Unpublished</option>
                                      </select>
                                    </th>
                                    <!-- <th>Action</th> -->
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade modal-fullscreen example-modal-fullscreen" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content h-100 border-0 shadow-0 bg-fusion-800">
            <button type="button" class="close p-sm-2 p-md-4 text-white fs-xxl position-absolute pos-right mr-sm-2 mt-sm-1 z-index-space" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true"><i class="fal fa-times"></i></span>
            </button>
            <div class="modal-body p-0 text-center" id="vid">
                {{-- <video src="https://ripe-objects.s3-eu-west-2.amazonaws.com/%2Fpexels-vivaan-rupani-7351722.mp4" controls autoplay/> --}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('page_js')
<script type="text/javascript">
    $(document).ready(function(){
        var table =  $('#video-table').DataTable(
                {
                    responsive: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('admin.video.list') }}",
                        data: {
                            is_published: function() { return $('#publishFilter').val() },
                        }
                    },
                    columns: [
                        {data: 'id', name: 'Id'},
                        {data: 'title', name: 'Title'},
                        {data: 'videos', name: 'Videos', orderable: false, searchable: false},
                        {data: 'chef_name', name: 'Chef Name'},
                        {data: 'food_category_name', name: 'Food Method'},
                        {data: 'course', name: 'Course',  orderable: false, searchable: true },
                        {data: 'country_id', name: 'Country'},
                        {data: 'preparation_time', name: 'Preparation Time'},
                        {data: 'serves', name: 'Serves'},
                        {data: 'is_published', name: 'is_published', orderable: false, searchable: true},
                        //{data: 'action', name: 'Action', orderable: false, searchable: false},
                    ],
                    order: [0, 'desc'],
                    lengthChange: true,
                    dom: '<"float-left"B><"float-right"f>rt<"row"<"col-sm-4"l><"col-sm-4"i><"col-sm-4"p>>',
                        // "<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'f><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'lB>>" +
                        // "<'row'<'col-sm-12'tr>>" +
                        // "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    buttons: [
                        {
                            extend: 'pdfHtml5',
                            text: 'PDF',
                            titleAttr: 'Generate PDF',
                            className: 'btn-outline-primary btn-sm mr-1'
                        },
                        {
                            extend: 'excelHtml5',
                            text: 'Excel',
                            titleAttr: 'Generate Excel',
                            className: 'btn-outline-primary btn-sm mr-1'
                        },
                        {
                            extend: 'csvHtml5',
                            text: 'CSV',
                            titleAttr: 'Generate CSV',
                            className: 'btn-outline-primary btn-sm mr-1'
                        },
                        {
                            extend: 'copyHtml5',
                            text: 'Copy',
                            titleAttr: 'Copy to clipboard',
                            className: 'btn-outline-primary btn-sm mr-1'
                        },
                        {
                            extend: 'print',
                            text: 'Print',
                            titleAttr: 'Print Table',
                            className: 'btn-outline-primary btn-sm'
                        }
                    ]
                });
                $('#btn-add').click(function () {
            $('#catForm').trigger("reset");
        });

        $('#publishFilter').change(function(){
            table.ajax.reload(null,false);
        });
        // Clicking the save button on the open modal for both CREATE and UPDATE
        $("#save").click(function (e) {
            var formData = {
                flavour_id: $('#cat_id').val(),
                video_id: $('#video_id').val(),
            };
            var ajaxurl = "{{ route('admin.video.store') }}";
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': "{{csrf_token()}}"
                },
                success: function (data) {
                    if(data.status == 'success')
                    {
                        $('#videos').val('');
                        toastr['success']('Save successfully!');
                        $('#default-example-modal').modal('hide');
                        $('#catForm').trigger("reset");
                        table.ajax.reload( null, false);
                    }else{
                        toastr['error'](data.message);
                    }
                },
                error: function (data) {
                    toastr['error']('Something went wrong, Please try again!');
                    console.log('Error:', data);
                }
            });
        });

        $(document).on("click", ".vid-btn" , function () {
            var vidUrl = $(this).data('url');
            let vidTag = "<video src='"+vidUrl+"' controls autoplay height='600' width='600'/>"
            $('#vid').html('');
            $('#vid').html(vidTag);

        });

        $(document).on("click", ".edit-cat" , function () {
            var ajaxurl = $(this).data('url');
            $.ajax({
                type: "GET",
                url: ajaxurl,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': "{{csrf_token()}}"
                },
                success: function (data) {
                    $('#catForm').trigger("reset");
                    $('#cat_id').val(data.data.id);
                    $('#name').val(data.data.name);
                    $('#email').val(data.data.email);
        
                },
                error: function (data) {
                    console.log('Error:', data);
                }
            });
            
        });

        $(document).on('click', '.active', function(){
            var ajaxurl = $(this).data('url');
            $.ajax({
                type: "GET",
                url: ajaxurl,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': "{{csrf_token()}}"
                },
                success: function (data) {
                    if(data.status == 'success')
                    {
                        toastr['success']('Video published status is changed successfully!!');
                        table.ajax.reload( null, false);
                    }
                },
                error: function (data) {
                    console.log('Error:', data);
                }
            });
        });

        $(document).on("click", ".detail-video" , function () {
          
          var ajaxurl =  $(this).data('url');
          $.ajax({
              type: "GET",
              url: ajaxurl,
              dataType: 'json',
              headers: {
                  'X-CSRF-TOKEN': "{{csrf_token()}}"
              },
              success: function (data) {
                  $('#user_id').val(data.data.chef_name);
                  $('#food_category_id').val(data.data.food_category_name);
                  $('#country_id').val(data.data.country_name);
                  $('#title').val(data.data.title);
                  $('#preparation_time').val(data.data.preparation_time);
                  $('#serves').val(data.data.serves);
                  $('#difficulty').val(data.data.difficulty);
                  $('#ingredients').val(data.data.ingredients);
              },
              error: function (data) {
                  console.log('Error:', data);
              }
          });
          
      });
       
    });
</script>
@endsection