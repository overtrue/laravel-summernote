<?php
$lang = str_replace('_', '-', config('app.locale'));
?>
<link rel="stylesheet" type="text/css" href="{{ asset('vendor/summernote/summernote.css') }}">
<script type="text/javascript" src="{{ asset('vendor/summernote/summernote.js') }}"></script>
<script type="text/javascript" src="{{ asset('vendor/summernote/lang/summernote-'.$lang.'.js') }}"></script>

<script>
    var summerUploadedImages = [], summernoteUploadedLength = 0, summernoteTotalFiles = 0;
    var summernoteOptions = {
        lang: '{{ $lang  }}',
        callbacks: {
            onImageUpload: function(files) {
                summernoteTotalFiles = files.length;
                for (var i = files.length - 1; i >= 0; i--) {
                    summernoteUploadImage(files[i], i, this);
                }
            }
        }
    };
    var summernoteUploadImage = function(image, index, el) {
        var data = new FormData();
        data.append("image", image);

        $.ajax({
            type: "POST",
            url: "{{ url(config('summernote.route.url', '/summernote/server')) }}",
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                // 这了保证上传顺序，先存起来
                summerUploadedImages[index] = {
                    url: response.relative_url,
                    filename: response.original
                };
            },
            error: function(error) {
              alert('图片上传失败');
            },
            complete: function() {
                summernoteUploadedLength++;
                if (summernoteUploadedLength == summernoteTotalFiles) {
                    summerUploadedImages.forEach(function(image) {
                        setTimeout(function(){
                            $(el).summernote('insertImage', image.url, image.filename);
                        }, 20);
                    });
                    summernoteUploadedLength = summernoteTotalFiles = 0;
                    summerUploadedImages = [];
                }
            }
        });
    };
</script>