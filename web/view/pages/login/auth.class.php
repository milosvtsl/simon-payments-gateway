<html lang="en" class="no-js">
<head>
    <title><g:message code="login"/></title>
    <!-- start: META -->
    <meta charset="utf-8">
    <meta name="layout" content="login">
    <!--[if IE]>
        <meta http-equiv='X-UA-Compatible' content="IE=edge,IE=9,IE=8,chrome=1" />
    <![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta content="" name="description">
    <meta content="" name="author">

    <r:require modules="theme"/>
</head>
<body class="login">
    <div class="main-login col-sm-4 col-sm-offset-4">
        <div class="logo">
            %{--<img alt="PayLogic Networks Inc." src="${createLinkTo(dir: 'images', file: 'paylogic-logo.png')}">--}%
            <r:img dir="images" file="paylogic-logo.png" width="173" height="64" alt="PayLogic Networks Inc."/>
        </div>

        <g:render template="login"/>

        <div class="copyright">
            <g:message code="company.copy"/>
        </div>
    </div>
    <script>
        jQuery(document).ready(function() {
            Main.init();
            Login.init();
        });
    </script>
</body>
</html>