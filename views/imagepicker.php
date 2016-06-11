<?php $this->preventAccess()?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
        <title><?php echo $title?></title>
        <style type="text/css">
            #message {
                background: #ffa7a7;
            }
            #imagepicker {}
            #imagepicker img {
                float: left;
                height: 200px;
                margin: 10px;
                cursor: pointer;
            }
            #upload {
                clear: both;
            }
        </style>
        <script type="text/javascript" src="<?php echo $tinymce_popup?>">
        </script>
        <script type="text/javascript">
            function init() {
                var picker = document.getElementById("imagepicker"),
                    images = picker.getElementsByTagName("img"),
                    i, len, image;

                for (i = 0, len = images.length; i < len; i++) {
                    image = images[i];
                    image.onclick = (function (image) {
                        return function() {
                            pick(image);
                        };
                    })(image);
                }
            }
            function pick(image) {
                var re = new RegExp('^' + location.protocol + "//"
                                    + location.host + location.pathname),
                    path = image.src.replace(re, "./"),
                    win = tinyMCEPopup.getWindowArg("window"),
                    inputId = tinyMCEPopup.getWindowArg("input"),
                    input = win.document.getElementById(inputId);

                input.value = path;
                if (input.onchange) {
                    input.onchange();
                }
                tinyMCEPopup.close();
            }
        </script>
    </head>
    <body onload="init()">
        <div id="message"><p><?php echo $message?></p></div>
        <div id="imagepicker">
<?php if (empty($images)):?>
            <p><?php echo $no_images?></p>
<?php else:?>
<?php foreach ($images as $image => $url):?>
            <img src="<?php echo $url?>" alt="<?php echo $image?>" title="<?php
            echo $image?>">
<?php endforeach?>
<?php endif?>
        </div>
        <form id="upload" action="<?php echo $upload_url?>" method="POST" enctype="multipart/form-data">
            <input name="extedit_file" type="file">
            <button><?php echo $upload?></button>
        </form>
    </body>
</html>
