<?php $swaggerDir = 'assets/libs/swagger/dist' ?>

    <!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swagger UI</title>
    <link rel="stylesheet" type="text/css" href="{{asset($swaggerDir . '/swagger-ui.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset($swaggerDir . '/index.css')}}"/>
    <link rel="icon" type="image/png" href="{{asset($swaggerDir . '/favicon-32x32.png')}}" sizes="32x32"/>
    <link rel="icon" type="image/png" href="{{asset($swaggerDir . '/favicon-16x16.png')}}" sizes="16x16"/>
</head>

<body>
<div id="swagger-ui"></div>
<script src="{{asset($swaggerDir . '/swagger-ui-bundle.js')}}" charset="UTF-8"></script>
<script src="{{asset($swaggerDir . '/swagger-ui-standalone-preset.js')}}" charset="UTF-8"></script>
{{--<script src="{{asset($swaggerDir . '/swagger-initializer.js')}}" charset="UTF-8"> </script>--}}
<script>
    window.onload = function () {
        //<editor-fold desc="Changeable Configuration Block">

        // the following lines will be replaced by docker/configurator, when it runs in a docker-container
        window.ui = SwaggerUIBundle({
            url: "{{ isset($auth) && $auth == 'auth' ? asset('assets/libs/swagger/swagger_auth.yaml') : asset('assets/libs/swagger/swagger.yaml') }}",
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            layout: "StandaloneLayout",
            persistAuthorization: true,
        });


        (() => {
            const i = setInterval(() => {
                const modelsElements = document.querySelectorAll('.models');
                let allExpanded = true;
                modelsElements.forEach((el) => {
                    const ariaExpandedElements = el.querySelectorAll('[aria-expanded]');
                    ariaExpandedElements.forEach((ariaEl) => {
                        if (ariaEl.getAttribute('aria-expanded') === 'false') {
                            allExpanded = false;
                            ariaEl.click();
                        }
                    });
                });
                if (allExpanded) {
                    clearInterval(i);
                }
            }, 1000);
        })();

        //</editor-fold>
    };
</script>
</body>
</html>
