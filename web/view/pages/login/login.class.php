<?php
namespace View\Pages\Login;

use View\AbstractView;


class Auth extends AbstractView {

    protected function renderHTMLBody()
    {

?>
<body class="login">
    <div class="main-login col-sm-4 col-sm-offset-4">
        <div class="logo">
            <img src="assets/images/paylogic-logo.png" width="173" height="64" alt="PayLogic Networks Inc."/>
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
    <?php

    }

}

