<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>

    <!-- Bootstrap CSS -->
    {{-- <link href="{{ asset('assets/css/bootstrap5.min.css') }}" rel="stylesheet" /> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('assets/dropify/dist/css/dropify.min.css') }}" />
    <style>
        .dropify-wrapper {
            border: 2px dashed #2196f3;
        }

        .videoPreviewPanel,
        .progress {
            display: none;
        }

    </style>
</head>

<body>

<div class="container pt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5>Laravel Chunk File Upload System</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success alert-dismissible fade show" role="alert" id='alertBox'
                         style="display: none">
                        <strong>Video Uploaded!</strong> Please save this form to redirect section page.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" id='dropifyWrapper'>
                                <label class="form-label">Choose File <b class="text-danger" style="font-size: 10px">Note: Please choose file first before save form</b></label>
                                <input type="file" name="video" class="form-control dropify" id="video">
                            </div>
                            <div class=videoPreviewPanel p-4">
                            <video id="videoPreview" src="" controls style="width: 100%; height: auto"></video>
                        </div>
                        <div class="progress mt-3" style="height: 25px">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"
                                 style="width: 75%; height: 100%">75%</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <form action="{{ route('upload.update') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title"
                                       value="{{ old('title') }}" placeholder="Enter video title" required>
                            </div>

                            <div class="form-group mt-3">
                                <label class="form-label text-primary">Video Privacy</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="video_privacy"
                                           id="public" value="0">
                                    <label class="form-check-label" for="public">
                                        Public / Preview video
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="video_privacy"
                                           id="private" value="1" checked>
                                    <label class="form-check-label" for="private">
                                        Private
                                    </label>
                                </div>
                            </div>
                            <input type="hidden" class="form-control" id="lastId" name="lastId">
                            <input type="hidden" class="form-control" id="sectionId" name="section_id"
                                   value="1">
                            <input type="hidden" class="form-control" name="course_name" value="web-design">
                            <div class="form-group mt-2 text-center">
                                <input type="submit" value="Save" class="btn btn-primary">
                            </div>
                        </form>
                    </div>

                    <div class="mt-2">
                        <hr>
                        <table class="table table-border">
                            <tr>
                                <th>Title</th>
                                <th>File</th>
                                <th>Section</th>
                                <th>Duration</th>
                                <th>Privacy</th>
                            </tr>
                            @forelse($videos as $video)
                                <tr>
                                    <td>{{ $video->title }}</td>
                                    <td>
                                        {{--                                        <video id="" src="{{ 'storage/web-design/'.$video->file }}" controls style="width: 50%; height: auto"></video>--}}
                                        <video id="" src="{{ Storage::disk('public')->url('web-design/'.$video->file)}}" controls style="width: 50%; height: auto"></video>
                                    </td>
                                    <td>{{ $video->section_id }}</td>
                                    <td>{{ $video->duration }}</td>
                                    <td>{{ $video->video_privacy }}</td>
                                </tr>
                            @empty
                            @endforelse
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- jQuery -->
{{-- <script src="{{ asset('assets/js/jQuery.min.js') }}" ></script> --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
</script>
{{-- <script src="{{ asset('assets/js/bootstrap5-bundle.min.js') }}" ></script> --}}
<script src="{{ asset('assets/dropify/dist/js/dropify.min.js') }}"></script>
<!-- Resumable JS -->
<script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>

<script type="text/javascript">
    $('.dropify').dropify()

    let browseFile = $('#video');
    let resumable = new Resumable({
        target: '{{ route('files.upload.large') }}',
        query: {
            _token: '{{ csrf_token() }}',

        }, // CSRF token
        fileType: ['mp4'],
        maxFileSize:200*1024*1024,
        headers: {
            'Accept': 'application/json'
        },
        testChunks: false,
        forceChunkSize: true,
        throttleProgressCallbacks: 1,
    });

    resumable.assignBrowse(browseFile[0]);

    resumable.on('fileAdded', function(file) { // trigger when file picked
        showProgress();
        resumable.upload() // to actually start uploading.
    });

    resumable.on('fileProgress', function(file) { // trigger when file progress update
        updateProgress(Math.floor(file.progress() * 100));
    });

    resumable.on('fileSuccess', function(file, response) { // trigger when file upload complete
        response = JSON.parse(response)
        console.log(response.path)
        if (response.success) {
            $('#lastId').val(response.last_insert_id)
            $('#alertBox').show(1000)
            $('#dropifyWrapper').hide();
            $('.videoPreviewPanel').show();
            $('#videoPreview').attr('src', response.path);
        }
    });

    resumable.on('fileError', function(file, response) { // trigger when there is any error
        alert('file uploading error.')
    });


    let progress = $('.progress');

    function showProgress() {
        progress.find('.progress-bar').css('width', '0%');
        progress.find('.progress-bar').html('0%');
        progress.find('.progress-bar').removeClass('bg-success');
        progress.show();
    }

    function updateProgress(value) {
        progress.find('.progress-bar').css('width', `${value}%`)
        progress.find('.progress-bar').html(`${value}%`)
    }

    function hideProgress() {
        progress.hide();
    }
</script>
</body>

</html>
