<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $title; ?></title>
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        body{ background: #fff; font-family: "Times New Roman"; color: #333; font-size: 16px; text-align:center }
        .system-message{ padding: 24px 78px; }
        .system-message h1{ font-size: 100px; font-weight: normal; line-height: 120px; margin-bottom: 12px; }
        .system-message .jump{ padding-top: 10px}
        .system-message .jump a{ color: #333;}
        .system-message .success,.system-message .error{ line-height: 1.8em; font-size: 36px }
        .system-message .detail{ font-size: 12px; line-height: 20px; margin-top: 12px; display:none}
    </style>
</head>
<body>
<div class="system-message">
    <?php if($status) {?>
        <h1>^_^</h1>
        <p class="success"><?php echo $message; ?></p>
    <?php }else{?>
        <h1>@_@</h1>
        <p class="error"><?php echo $message; ?></p>
    <?php }?>
    <p class="detail"></p>
    <p class="jump">
        页面自动 <a id="href" href="<?php  echo $jumpurl; ?>">跳转</a>
        等待时间： <b id="wait"><?php echo $wait; ?></b>
    </p>
</div>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),href = document.getElementById('href').href;
        var interval = setInterval(function(){
            if(parseInt(--wait.innerHTML) < 1) {
                location.href = href;
                clearInterval(interval);
            }
        }, 1000);
    })();
</script>
</body>
</html>
