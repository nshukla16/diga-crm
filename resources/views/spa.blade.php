<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>{{ \App\GlobalSettings::first()->site_name }}</title>
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="" name="description" />
    <meta content="" name="author" />
    <link rel="shortcut icon" href="/ico.png" />
    <link rel="stylesheet" href="{{ mix('/css/loader.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="/css/colors.css">
    <link rel="stylesheet" href="{{ mix('/css/application.css') }}">
    <link rel="stylesheet" href="{{ mix('/css/style.css') }}">
    {!! \App\GlobalSettings::first()->head !!}
</head>

<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white">
    <div id="loading-bar">
        <table>
            <tbody>
                <tr>
                    <td style="text-align: center;">
                        <svg id="logo" class="child logo" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="100px" height="100px" viewBox="0 0 200 200" style="enable-background:new 0 0 200 200;" xml:space="preserve">
                            <path id="nd" class="st0" d="M70,143.16l47.4,26.99l-26.02,14.98L46,159.29v-54.11l2.08-1.18C68.72,92.33,89.36,81.67,110,70
                                c0,10,0,20,0,30c-13,7-26.67,13.9-40,21.35V143.16z" />
                            <path id="st" class="st0 st" d="M135.78,9.86v149.47l-18.38,10.81l-0.32-0.18c-0.03,0.01-0.05,0.03-0.08,0.04c-8.33-4.67-15.67-9.33-24-14
                            c5.93-3.42,10.85-6.84,16.78-10.26V24.83L135.78,9.86z" />
                        </svg>
                        <div class="child shadow"></div>
                        <div class="lds-ellipsis">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="erp-application" class="page-wrapper">
        <div v-show="global_loading || $store.getters.getAllSettingsStatus == 'loading'">
            <loading></loading><!-- loading on ajax's and webpack's lazy loading between pages -->
        </div>
        <router-view></router-view>
    </div>
    <script>
        // let anim_d = document.getElementById("nd");
        // let logo_end = new Promise(function (resolve) {
        //     anim_d.addEventListener('animationend', function(){
        //         resolve();
        //     });
        // });

        let dom_loaded = new Promise(function(resolve) {
            document.addEventListener("DOMContentLoaded", function() {
                resolve();
            });
        });

        //Promise.all([logo_end, dom_loaded]).then(function(){
        Promise.all([dom_loaded]).then(function() {
            var el = document.getElementById('loading-bar');
            el.remove();
        });
    </script>
    <script src="{{ mix('/js/application.js') }}"></script>
    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        var Tawk_API = Tawk_API || {},
            Tawk_LoadStart = new Date();
        (function() {
            var s1 = document.createElement("script"),
                s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/5f171dce7258dc118beeabc0/default';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>
    <!--End of Tawk.to Script-->
</body>

</html>