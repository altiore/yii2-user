<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
</head>
<body>
    <?=\yii\helpers\Html::a('test link', ['user/auth/oauth', 'authclient' => 'linkedin'], ['target' => '_blank']); ?>

    <script>
        function listener(event) {
            if (event.origin != 'http://dm.loc') {
                // что-то прислали с неизвестного домена - проигнорируем..
                return;
            }

            alert( "получено: " + event.data );
        }

        if (window.addEventListener) {
            window.addEventListener("message", listener);
        } else {
            // IE8
            window.attachEvent("onmessage", listener);
        }
    </script>
</body>
</html>
